<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KuisTemplateExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Kuis';
    }

    public function array(): array
    {
        return [
            [
                'JUDUL KUIS',
                'DESKRIPSI',
                'KATEGORI',
                'WAKTU (menit)',
                'ISTIRAHAT (detik)',
                'AKSES (publik/private)',
            ],
            [
                'Ujian Matematika Dasar',
                'Ujian untuk mengukur pemahaman matematika',
                'Matematika',
                60,
                0,
                'private',
            ],
            [
                'PERTANYAAN',
                'POIN',
                'JAWABAN A',
                'JAWABAN B',
                'JAWABAN C',
                'JAWABAN D',
                'JAWABAN BENAR (a/b/c/d)',
            ],
            ['Berapa hasil dari 2 + 2?',   1, '2',  '4',  '6',  '8',  'b'],
            ['Berapa hasil dari 5 x 3?',   1, '10', '12', '15', '20', 'c'],
            ['Berapa hasil dari 10 - 4?',  1, '4',  '6',  '8',  '14', 'b'],
            ['Berapa hasil dari 20 / 4?',  2, '4',  '5',  '6',  '8',  'b'],
            ['Berapa hasil dari 7 x 8?',   2, '54', '56', '64', '72', 'b'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 25,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        $sheet->getStyle('A2:F2')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        $sheet->getStyle('A3:G3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']]],
        ]);

        $sheet->getStyle('A4:G8')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']]],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        return [];
    }
}