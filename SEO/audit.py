import urllib.request
import re
from xml.etree import ElementTree

url = 'https://mayushdesign.com'

def check_url(path):
    try:
        req = urllib.request.Request(f'{url}{path}', headers={'User-Agent': 'Mozilla/5.0'})
        return urllib.request.urlopen(req).read().decode('utf-8')
    except Exception as e:
        return str(e)

print('--- Basic SEO & Meta Tags ---')
html = check_url('/')
if html.startswith('HTTP Error') or html.startswith('URL'):
    print('Failed to fetch home page:', html)
else:
    title = re.search(r'<title>(.*?)</title>', html, re.IGNORECASE | re.DOTALL)
    print('Title:', title.group(1).strip() if title else 'Missing')
    
    desc = re.search(r'<meta[^>]*name=[\"\'\s]*description[\"\'\s]*content=[\"\'\s]*([^\"\']*)[\"\'\s]*>', html, re.IGNORECASE)
    if not desc:
        desc = re.search(r'<meta[^>]*content=[\"\'\s]*([^\"\']*)[\"\'\s]*name=[\"\'\s]*description[\"\'\s]*>', html, re.IGNORECASE)
    print('Description:', desc.group(1).strip() if desc else 'Missing')
    
    h1s = re.findall(r'<h1[^>]*>(.*?)</h1>', html, re.IGNORECASE | re.DOTALL)
    print('H1 tags:', [h1.strip() for h1 in h1s])
    
    ogs = re.findall(r'<meta[^>]*property=[\"\'\s]*og:([^\"\']*)[\"\'\s]*content=[\"\'\s]*([^\"\']*)[\"\'\s]*>', html, re.IGNORECASE)
    print('OG tags:')
    for og in ogs:
        print(f'  {og[0]}: {og[1]}')
        
    ld_jsons = re.findall(r'<script[^>]*type=[\"\'\s]*application/ld\+json[\"\'\s]*>(.*?)</script>', html, re.IGNORECASE | re.DOTALL)
    print(f'Found {len(ld_jsons)} application/ld+json scripts')

print('\n--- robots.txt ---')
print(check_url('/robots.txt'))

print('\n--- sitemap.xml ---')
sitemap = check_url('/sitemap.xml')
print(sitemap[:500] + '...' if len(sitemap) > 500 else sitemap)
