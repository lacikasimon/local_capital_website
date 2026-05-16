#!/usr/bin/env python3
"""Build the Romanian ANAF technical validation PDF from the markdown source."""

from __future__ import annotations

import html
import re
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    ListFlowable,
    ListItem,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "docs" / "anaf-validare-tehnica-acord-electronic.md"
OUTPUT = ROOT / "docs" / "anaf-validare-tehnica-acord-electronic.pdf"

FONT_REGULAR = "/System/Library/Fonts/Supplemental/Arial.ttf"
FONT_BOLD = "/System/Library/Fonts/Supplemental/Arial Bold.ttf"


def register_fonts() -> tuple[str, str]:
    if Path(FONT_REGULAR).is_file() and Path(FONT_BOLD).is_file():
        pdfmetrics.registerFont(TTFont("DocArial", FONT_REGULAR))
        pdfmetrics.registerFont(TTFont("DocArial-Bold", FONT_BOLD))
        return "DocArial", "DocArial-Bold"
    return "Helvetica", "Helvetica-Bold"


BASE_FONT, BOLD_FONT = register_fonts()


def styles():
    sheet = getSampleStyleSheet()
    sheet.add(
        ParagraphStyle(
            name="DocTitle",
            fontName=BOLD_FONT,
            fontSize=21,
            leading=25,
            textColor=colors.HexColor("#173D2F"),
            alignment=TA_CENTER,
            spaceAfter=12,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocMeta",
            fontName=BASE_FONT,
            fontSize=9.5,
            leading=13,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#3A3A3A"),
            spaceAfter=3,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocH1",
            fontName=BOLD_FONT,
            fontSize=14,
            leading=18,
            textColor=colors.HexColor("#2E6B4F"),
            spaceBefore=14,
            spaceAfter=7,
            keepWithNext=True,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocBody",
            fontName=BASE_FONT,
            fontSize=10.4,
            leading=14.2,
            alignment=TA_LEFT,
            spaceAfter=7,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocBullet",
            parent=sheet["DocBody"],
            leftIndent=12,
            firstLineIndent=0,
            spaceAfter=3,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocCell",
            fontName=BASE_FONT,
            fontSize=8.4,
            leading=10.7,
            spaceAfter=0,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocCellHead",
            fontName=BOLD_FONT,
            fontSize=8.6,
            leading=10.8,
            textColor=colors.HexColor("#173D2F"),
            spaceAfter=0,
        )
    )
    sheet.add(
        ParagraphStyle(
            name="DocFooter",
            fontName=BASE_FONT,
            fontSize=8,
            leading=10,
            textColor=colors.HexColor("#666666"),
        )
    )
    return sheet


STYLES = styles()


def inline_markup(text: str) -> str:
    text = html.escape(text)
    text = re.sub(r"`([^`]+)`", f'<font name="{BASE_FONT}">\\1</font>', text)
    text = re.sub(r"\*\*([^*]+)\*\*", r"<b>\1</b>", text)
    text = re.sub(r"(https?://[^\s<]+)", r'<a href="\1" color="#2E6B4F">\1</a>', text)
    return text


def paragraph(text: str, style_name: str = "DocBody") -> Paragraph:
    return Paragraph(inline_markup(text), STYLES[style_name])


def parse_table(lines: list[str]) -> Table:
    rows: list[list[Paragraph]] = []
    for idx, line in enumerate(lines):
        if idx == 1 and re.fullmatch(r"\|\s*:?-{3,}:?\s*(\|\s*:?-{3,}:?\s*)+\|?", line):
            continue
        cells = [cell.strip() for cell in line.strip().strip("|").split("|")]
        style = "DocCellHead" if not rows else "DocCell"
        rows.append([paragraph(cell, style) for cell in cells])

    if not rows:
        return Table([[""]])

    col_count = max(len(row) for row in rows)
    for row in rows:
        while len(row) < col_count:
            row.append(paragraph("", "DocCell"))

    usable_width = A4[0] - 3.6 * cm
    if col_count == 2:
        widths = [usable_width * 0.36, usable_width * 0.64]
    elif col_count == 3:
        widths = [usable_width * 0.23, usable_width * 0.37, usable_width * 0.40]
    else:
        widths = [usable_width / col_count] * col_count

    table = Table(rows, colWidths=widths, repeatRows=1, hAlign="LEFT")
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#EAF2ED")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#173D2F")),
                ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#B7C8BE")),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    return table


def build_story(markdown: str):
    story = []
    lines = markdown.splitlines()
    i = 0
    bullet_buffer: list[str] = []

    def flush_bullets():
        nonlocal bullet_buffer
        if not bullet_buffer:
            return
        items = [ListItem(paragraph(item, "DocBullet"), leftIndent=8) for item in bullet_buffer]
        story.append(ListFlowable(items, bulletType="bullet", start="circle", leftIndent=15))
        story.append(Spacer(1, 2))
        bullet_buffer = []

    while i < len(lines):
        line = lines[i].rstrip()

        if line == "":
            flush_bullets()
            i += 1
            continue

        if line.startswith("|"):
            flush_bullets()
            table_lines = []
            while i < len(lines) and lines[i].startswith("|"):
                table_lines.append(lines[i])
                i += 1
            story.append(parse_table(table_lines))
            story.append(Spacer(1, 10))
            continue

        if line.startswith("- "):
            bullet_buffer.append(line[2:].strip())
            i += 1
            continue

        flush_bullets()

        if line.startswith("# "):
            story.append(Spacer(1, 20))
            story.append(Paragraph(inline_markup(line[2:].strip()), STYLES["DocTitle"]))
        elif line.startswith("## "):
            heading = line[3:].strip()
            if heading == "12. Surse normative și tehnice consultate":
                story.append(PageBreak())
            story.append(Paragraph(inline_markup(heading), STYLES["DocH1"]))
        else:
            story.append(paragraph(line))
        i += 1

    flush_bullets()
    return story


def on_page(canvas, doc):
    canvas.saveState()
    width, height = A4
    canvas.setFillColor(colors.white)
    canvas.rect(0, 0, width, height, stroke=0, fill=1)
    canvas.setStrokeColor(colors.HexColor("#B7C8BE"))
    canvas.setLineWidth(0.5)
    canvas.line(doc.leftMargin, height - 1.25 * cm, width - doc.rightMargin, height - 1.25 * cm)
    canvas.setFont(BASE_FONT, 8)
    canvas.setFillColor(colors.HexColor("#666666"))
    canvas.drawString(doc.leftMargin, height - 0.95 * cm, "LOCAL CAPITAL IFN S.A. - Descriere tehnică acord ANAF")
    canvas.drawRightString(width - doc.rightMargin, 1.0 * cm, f"Pagina {doc.page}")
    canvas.restoreState()


def main() -> None:
    markdown = SOURCE.read_text(encoding="utf-8")
    doc = SimpleDocTemplate(
        str(OUTPUT),
        pagesize=A4,
        rightMargin=1.8 * cm,
        leftMargin=1.8 * cm,
        topMargin=1.65 * cm,
        bottomMargin=1.55 * cm,
        title="Descriere tehnică a metodei de validare a acordului ANAF în format electronic",
        author="LOCAL CAPITAL IFN S.A.",
        subject="Metodă tehnică de validare acord ANAF",
    )
    doc.build(build_story(markdown), onFirstPage=on_page, onLaterPages=on_page)
    print(OUTPUT)


if __name__ == "__main__":
    main()
