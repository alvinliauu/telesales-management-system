<?php

namespace App\Exports;

use App\Models\RenewalData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PartnerResultExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected string $month;
    protected string $type;

    public function __construct(string $month, string $type = 'all')
    {
        $this->month = $month;
        $this->type = $type;
    }

    public function query()
    {
        $query = RenewalData::where('batch_month', $this->month)
            ->whereIn('workflow_status', [
                RenewalData::STATUS_SENT,
                RenewalData::STATUS_SUCCESS,
                RenewalData::STATUS_FAILED,
            ]);

        if ($this->type === 'success') {
            $query->where('workflow_status', RenewalData::STATUS_SUCCESS);
        } elseif ($this->type === 'failed') {
            $query->where('workflow_status', RenewalData::STATUS_FAILED);
        }

        return $query->orderBy('partner_sent_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'No Kontrak',
            'No Polis',
            'Nama Tertanggung',
            'No HP',
            'Plat Nomor',
            'Merk Kendaraan',
            'Tahun',
            'Coverage',
            'TSI',
            'Premi',
            'End Date',
            'Status',
            'Partner Request ID',
            'Error Code',
            'Error Message',
            'Sent At',
            'Retry Count',
        ];
    }

    public function map($row): array
    {
        return [
            $row->no_kontrak,
            $row->no_polis,
            $row->nama_tertanggung,
            $row->no_hp,
            $row->ano,
            $row->merk_kendaraan,
            $row->tahun_kendaraan,
            $row->jenis_coverage,
            $row->tsi,
            $row->premi,
            $row->end_date?->format('Y-m-d'),
            $row->workflow_status_label,
            $row->partner_request_id,
            $row->partner_error_code,
            $row->partner_error_message,
            $row->partner_sent_at?->format('Y-m-d H:i:s'),
            $row->partner_retry_count,
        ];
    }

    public function title(): string
    {
        $typeLabel = match($this->type) {
            'success' => 'Success',
            'failed' => 'Failed',
            default => 'All',
        };
        return "{$typeLabel} Results";
    }
}
