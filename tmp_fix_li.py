import os
import re

templates = [
    r"c:\xampp\htdocs\mayush\resources\views\backend\website_settings\pages\minima\home_page_edit.blade.php",
    r"c:\xampp\htdocs\mayush\resources\views\backend\website_settings\pages\metro\home_page_edit.blade.php",
    r"c:\xampp\htdocs\mayush\resources\views\backend\website_settings\pages\megamart\home_page_edit.blade.php",
    r"c:\xampp\htdocs\mayush\resources\views\backend\website_settings\pages\classic\home_page_edit.blade.php",
]

# The broken pattern: the promotional <a> tag was injected inside the Category li
broken = """</a>
\t\t\t\t\t\t<a class="nav-link" id="promotional-category-tab" href="#promotional_category"
                            data-toggle="tab" data-target="#promotional_category" type="button" role="tab" aria-controls="promotional_category" aria-selected="false">
                            {{ translate('Promotional Category') }}
                        </a>
\t\t\t\t\t</li>"""

fixed = """</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li class="nav-item">
\t\t\t\t\t\t<a class="nav-link" id="promotional-category-tab" href="#promotional_category"
\t\t\t\t\t\t\tdata-toggle="tab" data-target="#promotional_category" type="button" role="tab" aria-controls="promotional_category" aria-selected="false">
\t\t\t\t\t\t\t{{ translate('Promotional Category') }}
\t\t\t\t\t\t</a>
\t\t\t\t\t</li>"""

for path in templates:
    if not os.path.exists(path):
        print(f"  SKIP: {path}")
        continue
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()
    if broken in content:
        content = content.replace(broken, fixed)
        with open(path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"  FIXED: {path}")
    else:
        print(f"  PATTERN NOT FOUND (may already be fixed or different): {os.path.basename(path)}")
