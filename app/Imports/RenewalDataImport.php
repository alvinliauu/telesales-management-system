<?php

namespace App\Imports;

use App\Models\RenewalData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Carbon\Carbon;

class RenewalDataImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    private int $renewalEventId;
    private int $uploadLogId;
    private int $rowCount = 0;
    private int $successCount = 0;
    private int $failedCount = 0;
    private array $errors = [];

    public function __construct(int $renewalEventId, int $uploadLogId)
    {
        $this->renewalEventId = $renewalEventId;
        $this->uploadLogId = $uploadLogId;
    }

    public function model(array $row)
    {
        $this->rowCount++;

        try {
            $data = [
                'renewal_event_id' => $this->renewalEventId,
                'no_kontrak' => $this->getValue($row, ['no_kontrak', 'no kontrak', 'kontrak']),
                'ano' => $this->getValue($row, ['ano']),
                'end_date' => $this->parseDate($row, ['end_date_ddmmyyyy', 'end date (dd/mm/yyyy)', 'end_date', 'end date']),
                'nama_tertanggung' => $this->getValue($row, ['nama_tertanggung', 'nama tertanggung']),
                'merk' => $this->getValue($row, ['merk', 'brand']),
                'tipe' => $this->getValue($row, ['tipe', 'type']),
                'tahun_kendaraan' => $this->getNumericValue($row, ['tahun_kendaraan', 'tahun kendaraan']),
                'nilai_pertanggungan' => $this->getDecimalValue($row, ['nilai_pertanggungan_telah_didepresiasi', 'nilai pertanggungan (telah didepresiasi)', 'nilai_pertanggungan']),
                'premi_comprehensive' => $this->getDecimalValue($row, ['premi_comprehensive_authorized_workshop_usia_maks_5_th', 'premi comprehensive + authorized workshop (usia maks 5 th)']),
                'premi_tlo' => $this->getDecimalValue($row, ['premi_tlo_tjh_5_jt_pad_5_jt', 'premi tlo + tjh 5 jt + pad 5 jt']),
                'premi_comprehensive_extended' => $this->getDecimalValue($row, ['premi_comprehensive_authorized_workshop_usia_maks_5_th_eqvet_tshfl_rscc_tjh_25_jt']),
                'no_polis' => $this->getValue($row, ['no_polis', 'no polis']),
                'wilayah' => $this->getValue($row, ['wilayah', 'region']),
                'jumlah_klaim' => $this->getNumericValue($row, ['jumlah_klaim', 'jumlah klaim']) ?? 0,
                'tahun_renewal' => $this->getNumericValue($row, ['tahun_renewal', 'tahun renewal']) ?? 1,
                'jaminan_existing' => $this->getValue($row, ['jaminan_existing_polis', 'jaminan existing polis']),
            ];

            if (empty($data['no_kontrak']) || empty($data['ano'])) {
                throw new \Exception('Missing required field: no_kontrak or ano');
            }

            $existing = RenewalData::where('renewal_event_id', $this->renewalEventId)
                ->where('no_kontrak', $data['no_kontrak'])
                ->first();

            if ($existing) {
                $existing->update($data);
                $this->successCount++;
                return null;
            }

            $this->successCount++;
            return new RenewalData($data);

        } catch (\Exception $e) {
            $this->failedCount++;
            $this->errors[] = ['row' => $this->rowCount + 1, 'error' => $e->getMessage()];
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function getValue(array $row, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = $this->normalizeKey($key);
            foreach ($row as $rowKey => $value) {
                if ($this->normalizeKey($rowKey) === $normalizedKey && !empty($value)) {
                    return trim((string) $value);
                }
            }
        }
        return null;
    }

    private function getNumericValue(array $row, array $possibleKeys): ?int
    {
        $value = $this->getValue($row, $possibleKeys);
        return $value !== null ? (int) $value : null;
    }

    private function getDecimalValue(array $row, array $possibleKeys): float
    {
        $value = $this->getValue($row, $possibleKeys);
        if ($value === null) return 0;
        $value = str_replace([',', ' '], ['', ''], $value);
        return (float) $value;
    }

    private function parseDate(array $row, array $possibleKeys): ?string
    {
        $value = $this->getValue($row, $possibleKeys);
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp(($value - 25569) * 86400)->format('Y-m-d');
            }
            
            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeKey(string $key): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $key));
    }

    public function getRowCount(): int { return $this->rowCount; }
    public function getSuccessCount(): int { return $this->successCount; }
    public function getFailedCount(): int { return $this->failedCount; }
    public function getErrors(): array { return $this->errors; }
}
