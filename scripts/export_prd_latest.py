from __future__ import annotations

import re
import subprocess
from datetime import date
from pathlib import Path

import markdown


ROOT = Path(__file__).resolve().parents[1]
SOURCE_HTML = Path(r"C:\Users\ginte\Downloads\PRD_NEX_OSS_BSS_Platform_v3.4_ROUTER_ONLY.html")
EXPORT_DIR = ROOT / "exports"

DOCS = [
    ("Step 1 - Business Flow", ROOT / "docs" / "PRD_NEX_OSS_BSS_Flows.md"),
    ("Step 2 - Database / ERD", ROOT / "docs" / "PRD_NEX_OSS_BSS_Database_Design.md"),
    ("Step 3 - Role Matrix", ROOT / "docs" / "PRD_NEX_OSS_BSS_Role_Matrix.md"),
    ("Step 4 - API Contract", ROOT / "docs" / "PRD_NEX_OSS_BSS_API_Contract_MVP.md"),
    ("Step 5 - Development Roadmap", ROOT / "docs" / "PRD_NEX_OSS_BSS_Development_Roadmap.md"),
    ("Step 6 - Event Driven Architecture", ROOT / "docs" / "PRD_NEX_OSS_BSS_Event_Driven_Architecture.md"),
    ("Step 7 - State Machine", ROOT / "docs" / "PRD_NEX_OSS_BSS_State_Machine_Diagrams.md"),
    ("Step 8 - Wireframes", ROOT / "docs" / "PRD_NEX_OSS_BSS_Wireframes.md"),
    ("Step 9 - Acceptance Criteria", ROOT / "docs" / "PRD_NEX_OSS_BSS_MVP_Acceptance_Criteria.md"),
]


def slugify(value: str) -> str:
    value = value.lower()
    value = re.sub(r"[^a-z0-9]+", "-", value)
    return value.strip("-")


def render_markdown(path: Path) -> str:
    text = path.read_text(encoding="utf-8")
    return markdown.markdown(
        text,
        extensions=["extra", "tables", "fenced_code", "toc", "sane_lists"],
        output_format="html5",
    )


def build_appendix() -> str:
    today = date.today().isoformat()
    toc = "\n".join(
        f'<a href="#update-{slugify(title)}"><span>{idx:02d}</span><b>{title}</b></a>'
        for idx, (title, _) in enumerate(DOCS, 1)
    )
    sections = []
    for idx, (title, path) in enumerate(DOCS, 1):
        body = render_markdown(path)
        sections.append(
            f"""
<article class="card update-card" id="update-{slugify(title)}">
  <div class="card-head">
    <span class="num">U{idx:02d}</span>
    <h2>{title}</h2>
  </div>
  <div class="content update-content">
    {body}
  </div>
</article>
""".strip()
        )

    return f"""
<section class="latest-cover" id="latest-update">
  <div class="eyebrow">Update PRD Terbaru</div>
  <h1>NEX OSS/BSS ISP Cloud Platform v3.4 - Router-Centric Update</h1>
  <p class="meta">Generated: {today} | Source: PRD_NEX_OSS_BSS_Platform_v3.4_ROUTER_ONLY.html + Revisi Step 1-9</p>
  <p class="summary">Bagian ini adalah konsolidasi terbaru dari gap analysis dan revisi Step 1 sampai Step 9. Prinsip arsitektur yang dikunci: <strong>Customer -> Service -> Router -> Router Interface -> Radius NAS -> FreeRadius</strong>. POP dan BTS tidak menjadi modul/tabel utama; keduanya hanya Router Role.</p>
  <div class="badges">
    <span class="badge">Router-Centric</span>
    <span class="badge">FreeRadius AAA</span>
    <span class="badge">SNMP Monitoring</span>
    <span class="badge">Customer Impact Analysis</span>
    <span class="badge">RouterOS Script Generator</span>
  </div>
</section>
<article class="card" id="latest-summary">
  <div class="card-head"><span class="num">UP</span><h2>Kesimpulan Update Terbaru</h2></div>
  <div class="content">
    <ul>
      <li>Router menjadi single source of network topology.</li>
      <li>Customer tidak terhubung langsung ke POP/BTS; customer ditelusuri melalui Service -> Router.</li>
      <li>Service Internet wajib memiliki Router Mapping; service non-network tidak wajib.</li>
      <li>Radius NAS dan Radius User wajib dapat ditelusuri ke Router untuk layanan jaringan.</li>
      <li>Router Down wajib menampilkan affected customer, affected service, affected radius user, revenue impact, dan incident.</li>
      <li>Roadmap mengikuti Phase 0 sampai Phase 10 dengan Phase 4 sebagai Router Management, FreeRadius AAA, dan Suspend Engine.</li>
      <li>UI wajib memiliki menu Network: Router, Router Interface, Router Link, Capacity Dashboard, Impact Analysis, dan SNMP Monitoring.</li>
    </ul>
  </div>
</article>
<article class="card" id="latest-update-toc">
  <div class="card-head"><span class="num">TOC</span><h2>Daftar Update Step 1-9</h2></div>
  <div class="content"><nav class="toc latest-toc">{toc}</nav></div>
</article>
{''.join(sections)}
"""


def add_print_styles(html: str) -> str:
    css = """
<style>
.latest-cover {
  background: linear-gradient(135deg, #071a46, #155eef);
  color: white;
  border-radius: 24px;
  padding: 34px;
  margin: 32px 0 24px;
  box-shadow: var(--shadow);
}
.latest-cover .summary,
.latest-cover .meta {
  color: rgba(255,255,255,.88);
}
.latest-cover .badge {
  background: rgba(255,255,255,.14);
  border-color: rgba(255,255,255,.24);
  color: white;
}
.update-content h1 {
  font-size: 24px;
  margin-top: 0;
}
.update-content h2 {
  font-size: 20px;
  margin-top: 26px;
}
.update-content h3 {
  font-size: 16px;
  margin-top: 20px;
}
.update-content pre {
  background: #0f172a;
  color: #e2e8f0;
  padding: 14px;
  border-radius: 12px;
  overflow-x: auto;
  white-space: pre-wrap;
}
.update-content code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
}
.update-content :not(pre) > code {
  background: #eef4ff;
  color: #123b8c;
  padding: 1px 5px;
  border-radius: 6px;
}
.latest-toc {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}
.latest-toc a {
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 10px 12px;
  background: #f8fbff;
}
@media print {
  .sidebar { display: none; }
  .shell { display: block; padding: 0; width: 100%; }
  .main { width: 100%; }
  .latest-cover { page-break-before: always; }
  .update-card { page-break-before: always; }
}
</style>
"""
    return html.replace("</head>", css + "\n</head>")


def build_html() -> Path:
    EXPORT_DIR.mkdir(parents=True, exist_ok=True)
    source = SOURCE_HTML.read_text(encoding="utf-8")
    source = source.replace(
        "Version: 3.4 | Owner: PT NEX SOLUSI TEKNOLOGI | Status: Master PRD Draft Updated | Date: 16 June 2026 | Update: Router-Centric Topology, SNMP Router Monitoring, MikroTik RADIUS Script Generator",
        "Version: 3.4 Updated | Owner: PT NEX SOLUSI TEKNOLOGI | Status: Consolidated PRD Update | Date: 19 June 2026 | Update: Router-Centric Step 1-9 Revision",
    )
    appendix = build_appendix()
    html = source.replace("</main>", appendix + "\n</main>")
    html = add_print_styles(html)
    output = EXPORT_DIR / "PRD_NEX_OSS_BSS_Platform_v3.4_UPDATED.html"
    output.write_text(html, encoding="utf-8")
    return output


def chrome_path() -> Path:
    candidates = [
        Path(r"C:\Program Files\Google\Chrome\Application\chrome.exe"),
        Path(r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"),
    ]
    for candidate in candidates:
        if candidate.exists():
            return candidate
    raise FileNotFoundError("Chrome or Edge executable not found.")


def build_pdf(html_path: Path) -> Path:
    pdf_path = EXPORT_DIR / "PRD_NEX_OSS_BSS_Platform_v3.4_UPDATED.pdf"
    browser = chrome_path()
    subprocess.run(
        [
            str(browser),
            "--headless",
            "--disable-gpu",
            "--no-pdf-header-footer",
            "--print-to-pdf=" + str(pdf_path),
            html_path.as_uri(),
        ],
        check=True,
    )
    return pdf_path


def main() -> None:
    html_path = build_html()
    pdf_path = build_pdf(html_path)
    print(f"HTML: {html_path}")
    print(f"PDF: {pdf_path}")


if __name__ == "__main__":
    main()
