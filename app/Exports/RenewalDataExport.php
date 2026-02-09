<?php

namespace App\Exports;

use App\Models\RenewalData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RenewalDataExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected int $batchId;
    protected string $type;

    public function __construct(int $batchId, string $type = 'uw')
    {
        $this->batchId = $batchId;
        $this->type = $type;
    }

    public function query()
    {
        $query = RenewalData::where('batch_id', $this->batchId);

        if ($this->type === 'marketing') {
            $query->whereIn('workflow_status', [
                RenewalData::STATUS_PENDING_MARKETING,
                RenewalData::STATUS_MARKETING_APPROVED,
                RenewalData::STATUS_SENT,
                RenewalData::STATUS_SUCCESS,
                RenewalData::STATUS_FAILED,
            ]);
        }

        return $query->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'No Kontrak',
            'No Polis',
            'Nama Tertanggung',
            'No HP',
            'Email',
            'Plat Nomor',
            'Merk Kendaraan',
            'Tahun Kendaraan',
            'Jenis Coverage',
            'TSI',
            'Premi',
            'Start Date',
            'End Date',
            'Agent',
            'Cabang',
            'Workflow Status',
            'UW Notes',
            'UW Rejection Reason',
            'Partner Request ID',
            'Partner Error',
            'Partner Sent At',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->no_kontrak,
            $row->no_polis,
            $row->nama_tertanggung,
            $row->no_hp,
            $row->email,
            $row->ano,
            $row->merk_kendaraan,
            $row->tahun_kendaraan,
            $row->jenis_coverage,
            $row->tsi,
            $row->premi,
            $row->start_date?->format('Y-m-d'),
            $row->end_date?->format('Y-m-d'),
            $row->agent,
            $row->cabang,
            $row->workflow_status_label,
            $row->uw_notes,
            $row->uw_rejection_reason,
            $row->partner_request_id,
            $row->partner_error_message,
            $row->partner_sent_at?->format('Y-m-d H:i:s'),
        ];
    }
}
