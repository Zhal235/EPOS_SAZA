<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Return sample data for template
     */
    public function array(): array
    {
        return [
            [
                'Laptop ASUS ROG Strix',
                'Electronics',
                'PT Tech Supplier',
                8000000,
                12000000,
                5,
                'ASUS',
                'pcs',
                'Gaming laptop with high performance specifications'
            ],
            [
                'Mouse Wireless Logitech',
                'Electronics',
                'PT Tech Supplier',
                150000,
                250000,
                20,
                'Logitech',
                'pcs',
                'Wireless mouse with ergonomic design'
            ],
            [
                'Kopi Arabica Premium 1kg',
                'Food & Beverage',
                'CV Coffee Supply',
                45000,
                65000,
                100,
                'Premium Coffee',
                'kg',
                'High quality arabica coffee beans from mountain region'
            ],
            [
                'Kertas A4 80gsm',
                'Office Supplies',
                'PT Office Pro',
                25000,
                35000,
                50,
                'Paper Pro',
                'dus',
                'High quality printing paper for office use'
            ]
        ];
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Product Name *',
            'Category Name *',
            'Supplier Name *',
            'Cost Price *',
            'Selling Price *',
            'Stock Quantity *',
            'Brand',
            'Unit',
            'Description'
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30, // Product Name
            'B' => 20, // Category Name
            'C' => 25, // Supplier Name
            'D' => 15, // Cost Price
            'E' => 15, // Selling Price
            'F' => 15, // Stock Quantity
            'G' => 15, // Brand
            'H' => 10, // Unit
            'I' => 40, // Description
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4F46E5'] // Indigo background
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style for data rows
        $sheet->getStyle('A2:I' . (count($this->array()) + 1))->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Make price columns right-aligned
        $sheet->getStyle('D2:F' . (count($this->array()) + 1))->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
            'numberFormat' => [
                'formatCode' => '#,##0'
            ]
        ]);

        // Add borders to all cells
        $sheet->getStyle('A1:I' . (count($this->array()) + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        return $sheet;
    }
}
