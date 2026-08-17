import * as XLSX from 'xlsx';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

/**
 * columns: [{ key: 'nama_satuan', label: 'Satuan' }, ...]
 * rows: array of plain objects (hasil fetch, bukan reactive proxy)
 */
export function exportToExcel(columns, rows, filename) {
    const header = columns.map((c) => c.label);
    const body = rows.map((row) => columns.map((c) => row[c.key] ?? ''));

    const sheet = XLSX.utils.aoa_to_sheet([header, ...body]);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, sheet, 'Data');
    XLSX.writeFile(workbook, `${filename}.xlsx`);
}

export function exportToPdf(columns, rows, filename, title) {
    const doc = new jsPDF();

    if (title) {
        doc.setFontSize(14);
        doc.text(title, 14, 15);
    }

    autoTable(doc, {
        startY: title ? 22 : 10,
        head: [columns.map((c) => c.label)],
        body: rows.map((row) => columns.map((c) => String(row[c.key] ?? ''))),
        styles: { fontSize: 9 },
        headStyles: { fillColor: [23, 23, 23] },
    });

    doc.save(`${filename}.pdf`);
}

export function printTable(columns, rows, title) {
    const header = columns.map((c) => `<th>${c.label}</th>`).join('');
    const body = rows
        .map((row) => `<tr>${columns.map((c) => `<td>${row[c.key] ?? ''}</td>`).join('')}</tr>`)
        .join('');

    const win = window.open('', '_blank');
    win.document.write(`
        <html>
        <head>
            <title>${title}</title>
            <style>
                body { font-family: sans-serif; padding: 24px; }
                h1 { font-size: 18px; margin-bottom: 16px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ccc; padding: 6px 10px; font-size: 12px; text-align: left; }
                th { background: #f3f4f6; }
            </style>
        </head>
        <body>
            <h1>${title}</h1>
            <table><thead><tr>${header}</tr></thead><tbody>${body}</tbody></table>
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
}
