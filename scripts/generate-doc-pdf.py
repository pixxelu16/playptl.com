#!/usr/bin/env python3
"""Generate PTL-DOCUMENTATION.pdf from markdown."""

from pathlib import Path

import markdown
from xhtml2pdf import pisa

ROOT = Path(__file__).resolve().parent.parent
MD_FILE = ROOT / "PTL-DOCUMENTATION.md"
PDF_FILE = ROOT / "PTL-DOCUMENTATION.pdf"

CSS = """
@page {
    size: A4;
    margin: 2cm;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 11pt;
    line-height: 1.45;
    color: #222;
}
h1 {
    color: #1a5c2e;
    font-size: 22pt;
    margin-bottom: 0.3em;
}
h2 {
    color: #2d7a45;
    font-size: 15pt;
    margin-top: 1.4em;
    border-bottom: 1px solid #ccc;
    padding-bottom: 4px;
}
h3 {
    color: #333;
    font-size: 12pt;
    margin-top: 1em;
}
p, li {
    margin: 0.4em 0;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 0.8em 0;
    font-size: 10pt;
}
th, td {
    border: 1px solid #bbb;
    padding: 6px 8px;
    text-align: left;
    vertical-align: top;
}
th {
    background-color: #e8f5e9;
}
code, pre {
    font-family: Courier, monospace;
    font-size: 9pt;
    background: #f5f5f5;
}
pre {
    padding: 10px;
    white-space: pre-wrap;
}
hr {
    border: none;
    border-top: 1px solid #ddd;
    margin: 1.5em 0;
}
strong {
    color: #111;
}
"""


def main() -> None:
    md_text = MD_FILE.read_text(encoding="utf-8")
    body = markdown.markdown(
        md_text,
        extensions=["tables", "fenced_code", "nl2br", "sane_lists"],
    )
    html = f"""<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>PLAYPTL Project Flow Document</title>
    <style>{CSS}</style>
</head>
<body>
{body}
</body>
</html>"""

    with PDF_FILE.open("wb") as pdf_file:
        status = pisa.CreatePDF(html, dest=pdf_file, encoding="utf-8")

    if status.err:
        raise SystemExit(f"PDF generation failed with {status.err} error(s)")

    print(f"Created: {PDF_FILE}")


if __name__ == "__main__":
    main()
