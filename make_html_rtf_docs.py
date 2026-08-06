import os
import re

def markdown_to_html(md_path, html_path, title):
    if not os.path.exists(md_path):
        return

    with open(md_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    html_content = f"""<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{title}</title>
    <style>
        body {{
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 40px;
            background-color: #f8f9fa;
            color: #212529;
        }}
        .container {{
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }}
        h1 {{ color: #1f497d; font-size: 24px; border-bottom: 2px solid #1f497d; padding-bottom: 8px; }}
        h2 {{ color: #1f497d; font-size: 18px; margin-top: 24px; }}
        h3 {{ color: #333333; font-size: 15px; }}
        table {{
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
            font-size: 13px;
        }}
        th {{
            background-color: #1f497d;
            color: white;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #1f497d;
            font-weight: bold;
        }}
        td {{
            padding: 8px;
            border: 1px solid #dce4ec;
            vertical-align: middle;
        }}
        tr:nth-child(even) {{ background-color: #f2f5f9; }}
        tr:hover {{ background-color: #e9ecef; }}
        .center {{ text-align: center; }}
        p {{ line-height: 1.6; margin-bottom: 10px; }}
    </style>
</head>
<body>
<div class="container">
"""

    in_table = False
    table_lines = []

    def render_table(lines_chunk):
        nonlocal html_content
        rows_data = []
        for l in lines_chunk:
            l_str = l.strip()
            if not l_str.startswith('|') or re.match(r'^\|[\s:\-|\+]+\|$', l_str):
                continue
            cols = [c.strip() for c in l_str.split('|')[1:-1]]
            if cols:
                rows_data.append(cols)

        if not rows_data:
            return

        html_content += "<table>\n"
        for r_idx, row in enumerate(rows_data):
            html_content += "  <tr>\n"
            for c_idx, val in enumerate(row):
                tag = "th" if r_idx == 0 else "td"
                cls = " class='center'" if (c_idx in [0, 1] and r_idx > 0) else ""
                clean_val = val.replace('**', '').replace('`', '')
                html_content += f"    <{tag}{cls}>{clean_val}</{tag}>\n"
            html_content += "  </tr>\n"
        html_content += "</table>\n"

    for line in lines:
        line_str = line.strip()

        if line_str.startswith('|'):
            table_lines.append(line_str)
            in_table = True
            continue
        else:
            if in_table:
                render_table(table_lines)
                table_lines = []
                in_table = False

        if not line_str:
            continue

        if line_str.startswith('# '):
            html_content += f"<h1>{line_str[2:]}</h1>\n"
        elif line_str.startswith('## '):
            html_content += f"<h2>{line_str[3:]}</h2>\n"
        elif line_str.startswith('### '):
            html_content += f"<h3>{line_str[4:]}</h3>\n"
        elif line_str.startswith('---'):
            html_content += "<hr style='border: none; border-top: 1px solid #ccc; margin: 20px 0;'>\n"
        else:
            clean_p = line_str.replace('**', '<b>').replace('**', '</b>')
            html_content += f"<p>{clean_p}</p>\n"

    if in_table and table_lines:
        render_table(table_lines)

    html_content += "</div>\n</body>\n</html>"

    with open(html_path, 'w', encoding='utf-8') as f:
        f.write(html_content)
    print(f"Generated HTML: {html_path}")

if __name__ == '__main__':
    base_dir = r"c:\Users\pogoi\Herd\digiper"
    files = [
        ("LOGBOOK_BE1_RIFA_REZA_FAHLEVI.md", "LOGBOOK_BE1_RIFA_REZA_FAHLEVI.html", "Logbook BE 1 - Rifa Reza Fahlevi"),
        ("LOGBOOK_BE2.md", "LOGBOOK_BE2.html", "Logbook BE 2"),
        ("LOGBOOK_FE1.md", "LOGBOOK_FE1.html", "Logbook FE 1"),
        ("LOGBOOK_FE2.md", "LOGBOOK_FE2.html", "Logbook FE 2"),
        ("LAPORAN_MAGANG_DIGIPER.md", "LAPORAN_MAGANG_DIGIPER.html", "Laporan Magang DIGIPER")
    ]
    for md, html_name, title in files:
        md_p = os.path.join(base_dir, md)
        html_p = os.path.join(base_dir, html_name)
        markdown_to_html(md_p, html_p, title)
