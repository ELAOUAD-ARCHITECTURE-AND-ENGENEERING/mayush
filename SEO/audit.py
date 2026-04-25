#!/usr/bin/env python3
import argparse
import json
import re
import sys
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from html.parser import HTMLParser


AI_AND_SEARCH_BOTS = [
    "Googlebot",
    "Bingbot",
    "GPTBot",
    "ChatGPT-User",
    "ClaudeBot",
    "PerplexityBot",
    "Google-Extended",
    "CCBot",
]


class PageParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.title = ""
        self._in_title = False
        self._h1_depth = 0
        self._h1_buffer = []
        self.h1s = []
        self.meta = []
        self.links = []
        self.json_ld = []
        self._script_type = ""
        self._script_buffer = []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        tag = tag.lower()
        if tag == "title":
            self._in_title = True
        elif tag == "h1":
            self._h1_depth += 1
            self._h1_buffer = []
        elif tag == "meta":
            self.meta.append({k.lower(): v for k, v in attrs.items()})
        elif tag == "link":
            self.links.append({k.lower(): v for k, v in attrs.items()})
        elif tag == "script":
            self._script_type = attrs.get("type", "").lower()
            self._script_buffer = []

    def handle_endtag(self, tag):
        tag = tag.lower()
        if tag == "title":
            self._in_title = False
        elif tag == "h1" and self._h1_depth:
            self._h1_depth -= 1
            text = normalize(" ".join(self._h1_buffer))
            if text:
                self.h1s.append(text)
            self._h1_buffer = []
        elif tag == "script":
            if self._script_type == "application/ld+json":
                self.json_ld.append("".join(self._script_buffer).strip())
            self._script_type = ""
            self._script_buffer = []

    def handle_data(self, data):
        if self._in_title:
            self.title += data
        if self._h1_depth:
            self._h1_buffer.append(data)
        if self._script_type:
            self._script_buffer.append(data)


def normalize(value):
    return re.sub(r"\s+", " ", value or "").strip()


def fetch(url, timeout):
    request = urllib.request.Request(url, headers={"User-Agent": "MayushSeoAudit/1.0"})
    with urllib.request.urlopen(request, timeout=timeout) as response:
        body = response.read()
        content_type = response.headers.get("Content-Type", "")
        charset = response.headers.get_content_charset() or "utf-8"
        return response.status, content_type, body.decode(charset, errors="replace")


def result(results, ok, label, detail):
    results.append((ok, label, detail))
    status = "PASS" if ok else "FAIL"
    print(f"[{status}] {label}: {detail}")


def meta_content(parser, name=None, prop=None):
    for item in parser.meta:
        if name and item.get("name", "").lower() == name.lower():
            return normalize(item.get("content", ""))
        if prop and item.get("property", "").lower() == prop.lower():
            return normalize(item.get("content", ""))
    return ""


def canonical(parser):
    for item in parser.links:
        if item.get("rel", "").lower() == "canonical":
            return normalize(item.get("href", ""))
    return ""


def audit_page(base_url, timeout, results):
    page_url = urllib.parse.urljoin(base_url, "/")
    status, content_type, html = fetch(page_url, timeout)
    parser = PageParser()
    parser.feed(html)

    title = normalize(parser.title)
    description = meta_content(parser, name="description")
    can = canonical(parser)
    og_count = sum(1 for item in parser.meta if item.get("property", "").lower().startswith("og:"))
    twitter_count = sum(1 for item in parser.meta if item.get("name", "").lower().startswith("twitter:"))

    result(results, status == 200, "Home HTTP status", f"{status} {content_type}")
    result(results, 30 <= len(title) <= 70, "Title length", f"{len(title)} chars: {title or 'missing'}")
    result(results, 80 <= len(description) <= 170, "Meta description length", f"{len(description)} chars")
    result(results, bool(can) and can.startswith(base_url.rstrip("/")), "Canonical URL", can or "missing")
    result(results, len(parser.h1s) == 1, "Exactly one H1", f"{len(parser.h1s)} found: {parser.h1s}")
    newsletter_h1 = any("newsletter" in h1.lower() or "abonnez" in h1.lower() for h1 in parser.h1s)
    result(results, not newsletter_h1, "Homepage H1 meaning", parser.h1s[0] if parser.h1s else "missing")
    result(results, og_count >= 4, "Open Graph tags", f"{og_count} found")
    result(results, twitter_count >= 4, "Twitter card tags", f"{twitter_count} found")

    valid_ld = 0
    invalid_ld = []
    for index, block in enumerate(parser.json_ld, start=1):
        try:
            json.loads(block)
            valid_ld += 1
        except json.JSONDecodeError as exc:
            invalid_ld.append(f"script {index}: {exc}")
    result(results, bool(parser.json_ld) and not invalid_ld, "JSON-LD parses", f"{valid_ld} valid, {len(invalid_ld)} invalid")
    for error in invalid_ld:
        print(f"       {error}")


def audit_robots(base_url, timeout, results):
    robots_url = urllib.parse.urljoin(base_url, "/robots.txt")
    status, content_type, robots = fetch(robots_url, timeout)
    result(results, status == 200, "robots.txt HTTP status", f"{status} {content_type}")

    lowered = robots.lower()
    for bot in AI_AND_SEARCH_BOTS:
        disallowed = re.search(rf"user-agent:\s*{re.escape(bot.lower())}\s*[\r\n]+disallow:\s*/", lowered)
        result(results, not disallowed, f"robots access for {bot}", "not blocked" if not disallowed else "blocked by Disallow: /")

    sitemap_lines = [line.strip() for line in robots.splitlines() if line.lower().startswith("sitemap:")]
    result(results, bool(sitemap_lines), "robots sitemap directive", ", ".join(sitemap_lines) or "missing")


def audit_sitemap(base_url, timeout, results, expected_sitemap_host=None):
    sitemap_url = urllib.parse.urljoin(base_url, "/sitemap.xml")
    status, content_type, xml = fetch(sitemap_url, timeout)
    result(results, status == 200, "sitemap HTTP status", f"{status} {content_type}")
    result(results, "xml" in content_type.lower() or xml.lstrip().startswith("<?xml"), "sitemap XML content", content_type or "content sniff")

    try:
        root = ET.fromstring(xml)
        urls = [loc.text.strip() for loc in root.findall(".//{*}loc") if loc.text]
    except ET.ParseError as exc:
        result(results, False, "sitemap XML parses", str(exc))
        return

    expected_host = expected_sitemap_host or urllib.parse.urlparse(base_url).netloc
    bad_hosts = [url for url in urls if urllib.parse.urlparse(url).netloc != expected_host]
    localhost = [url for url in urls if "localhost" in url.lower()]
    non_https = [url for url in urls if urllib.parse.urlparse(url).scheme != "https"]

    result(results, len(urls) > 0, "sitemap URL count", f"{len(urls)} URLs")
    result(results, not bad_hosts, "sitemap host consistency", f"{len(bad_hosts)} URLs outside {expected_host}")
    result(results, not localhost, "sitemap localhost check", f"{len(localhost)} localhost URLs")
    result(results, not non_https, "sitemap HTTPS check", f"{len(non_https)} non-HTTPS URLs")


def main():
    parser = argparse.ArgumentParser(description="Audit Mayush SEO/GEO essentials.")
    parser.add_argument("url", nargs="?", default="https://mayushdesign.com", help="Base URL to audit")
    parser.add_argument("--timeout", type=int, default=15, help="HTTP timeout in seconds")
    parser.add_argument("--expected-sitemap-host", help="Expected host for sitemap <loc> URLs, useful for local audits of production sitemaps")
    args = parser.parse_args()

    base_url = args.url.rstrip("/")
    if not base_url.startswith(("http://", "https://")):
        base_url = "https://" + base_url

    results = []
    print(f"SEO/GEO audit target: {base_url}\n")
    try:
        audit_page(base_url, args.timeout, results)
        print()
        audit_robots(base_url, args.timeout, results)
        print()
        audit_sitemap(base_url, args.timeout, results, args.expected_sitemap_host)
    except Exception as exc:
        result(results, False, "audit runtime", str(exc))

    passed = sum(1 for ok, _, _ in results if ok)
    failed = len(results) - passed
    print(f"\nSummary: {passed} passed, {failed} failed")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
