<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormSubmissionsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    protected $fieldKeys;

    public function __construct($query, array $fieldKeys)
    {
        $this->query = $query;
        $this->fieldKeys = $fieldKeys;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return array_merge([
            'ID',
            'Submitted At',
            'IP Address',
            'Status',
        ], $this->fieldKeys);
    }

    /**
     * @param  \App\Models\FormSubmission  $submission
     */
    public function map($submission): array
    {
        $row = [
            $submission->id,
            $submission->created_at->format('Y-m-d H:i:s'),
            $submission->ip_address,
            ucfirst($submission->status),
        ];

        foreach ($this->fieldKeys as $key) {
            $value = $submission->data[$key] ?? '';
            $row[] = is_array($value) ? implode(', ', $value) : $value;
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Indigo-600
                ],
            ],
        ];
    }
}
