<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * RenewalQueryService
 * 
 * Converted from SQL Server Stored Procedure: SP_BCAI_R_Renewable_Listing_ME2
 * and Execute Script: 3__execute.sql
 * 
 * This service queries renewal data from the policy database (SQL Server)
 * and returns the 87 columns needed for the renewal process.
 */
class RenewalQueryService
{
    /**
     * Database connection name for policy database
     */
    protected string $connection = 'policy_db';

    /**
     * Main method to query renewal data
     * 
     * @param string $startDate Format: Y-m-d
     * @param string $endDate Format: Y-m-d
     * @param string $branch Branch code (optional, empty for all)
     * @param string $toc Type of Coverage code (default: '02' for Motor)
     * @return Collection
     */
    public function queryRenewalData(
        string $startDate = '2012-01-01',
        string $endDate = '2025-12-31',
        string $branch = '',
        string $toc = '02'
    ): Collection {
        // Step 1: Get base acceptance data
        $acceptanceData = $this->getAcceptanceData($startDate, $endDate, $branch, $toc);
        
        if ($acceptanceData->isEmpty()) {
            return collect([]);
        }

        $anos = $acceptanceData->pluck('ANO')->toArray();
        $oanos = $acceptanceData->pluck('OANO')->toArray();
        $cnos = $acceptanceData->pluck('CNO')->toArray();

        // Step 2: Get related data (parallel queries for performance)
        $premiumPaid = $this->getPremiumPaid($oanos);
        $osClaim = $this->getOSClaim($oanos);
        $incurredClaim = $this->getIncurredClaim($oanos);
        $businessSource = $this->getBusinessSource($acceptanceData);
        
        // Step 3: Build renewable listing (main query)
        $renewableListing = $this->buildRenewableListing(
            $acceptanceData,
            $premiumPaid,
            $osClaim,
            $incurredClaim,
            $businessSource
        );

        // Step 4: Get claim count
        $claimCount = $this->getClaimCount($anos);

        // Step 5: Build final result with all 87 columns
        $result = $this->buildFinalResult($renewableListing, $claimCount, $anos);

        return $result;
    }

    /**
     * Step 1: Get base acceptance data
     * Equivalent to #Acceptance_Renewable_Listing temp table
     */
    protected function getAcceptanceData(
        string $startDate,
        string $endDate,
        string $branch,
        string $toc
    ): Collection {
        $query = DB::connection($this->connection)
            ->table('sea2014.dbo.Acceptance as A')
            ->join('sea2014.dbo.Cover as C', function ($join) {
                $join->on('C.CNO', '=', 'A.CNO')
                    ->whereColumn('A.ANO', '<>', 'A.LANO');
            })
            ->select([
                'A.ANO',
                'A.OANO',
                'A.LANO',
                'A.CNO',
                'A.Source'
            ])
            ->whereMonth('A.EDATE', '=', 1) // month(A.EDATE) = 01
            ->whereYear('A.EDATE', '>', 2025)
            ->where('A.ASTATUS', '<>', 'W')
            ->where('A.POLICYNO', '<>', '')
            ->where('A.ATYPE', '=', 'N')
            ->whereIn('C.Segment', [
                'BCA-UMGK', 'BCA-KK', 'BCAF', 'BCAF-R2', 'BCAF-L',
                'BMF-R2', 'BMF-R2R', 'BMF-R4', 'DIR-02REN', 'LS-NONGRP',
                'BCAF-INW', 'BCA-DCM', 'BCA-CTX'
            ]);

        // Apply branch filter if provided
        if (!empty($branch)) {
            $query->where('A.Branch', 'like', $branch . '%');
        }

        // Apply TOC filter
        if (!empty($toc)) {
            $query->where('C.TOC', 'like', $toc . '%');
        }

        return $query->get();
    }

    /**
     * Step 2a: Get Premium Paid data
     * Equivalent to #PremiumPaid temp table
     */
    protected function getPremiumPaid(array $oanos): Collection
    {
        if (empty($oanos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.nVoucher as Voucher')
            ->join('sea2014.dbo.Admlink', function ($join) {
                $join->on('Voucher.Voucher', '=', 'Admlink.Voucher')
                    ->where('Admlink.Subject', '=', 'Premium')
                    ->whereIn('Admlink.Type', ['DI', 'IC', 'IR']);
            })
            ->join('sea2014.dbo.AccAss', function ($join) {
                $join->on('Admlink.AdmNo', '=', 'AccAss.AdmNo')
                    ->where('AccAss.Code', '=', 'P');
            })
            ->join('sea2014.dbo.Acceptance', 'AccAss.ANO', '=', 'Acceptance.ANO')
            ->whereIn('Acceptance.OANO', $oanos)
            ->select([
                'Acceptance.ANO',
                DB::raw("SUM(CASE WHEN Voucher.DebtorF = 1 THEN (Nominal_CC - Diff_CC) ELSE -(Nominal_CC - Diff_CC) END * Voucher.Rate) as Premium"),
                DB::raw("SUM(CASE WHEN Voucher.DebtorF = 1 THEN Payment_CC ELSE -Payment_CC END * Voucher.Rate) as Paid")
            ])
            ->groupBy('Acceptance.ANO')
            ->havingRaw('SUM(CASE WHEN Voucher.DebtorF = 1 THEN (Nominal_CC - Diff_CC) ELSE -(Nominal_CC - Diff_CC) END * Voucher.Rate) <> 0')
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Step 2b: Get Outstanding Claim data
     * Equivalent to #OS_Claim_Renewable_Listing temp table
     */
    protected function getOSClaim(array $oanos): Collection
    {
        if (empty($oanos)) {
            return collect([]);
        }

        // Note: OS_Claim_By_Detail is a table-valued function in SQL Server
        // You may need to adjust this based on your actual implementation
        return DB::connection($this->connection)
            ->table(DB::raw('sea2014.dbo.OS_Claim_By_Detail(GETDATE()) as OS_Claim'))
            ->join('sea2014.dbo.Acceptance', 'OS_Claim.ANO', '=', 'Acceptance.ANO')
            ->whereIn('Acceptance.OANO', $oanos)
            ->select([
                'Acceptance.ANO',
                DB::raw('COUNT(DISTINCT OS_Claim.OCNO) as NOfClaimOS'),
                DB::raw('SUM(OS_Claim.Gross_OS * OS_Claim.Rate) as OS_Claim')
            ])
            ->groupBy('Acceptance.ANO')
            ->havingRaw('SUM(OS_Claim.Gross_OS * OS_Claim.Rate) <> 0')
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Step 2c: Get Incurred Claim data
     * Equivalent to #Incurred_Claim temp table
     */
    protected function getIncurredClaim(array $oanos): Collection
    {
        if (empty($oanos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.Claim')
            ->join('sea2014.dbo.ClaAss', function ($join) {
                $join->on('ClaAss.CNO', '=', 'Claim.CNO')
                    ->whereIn('ClaAss.Type', ['DI', 'IC', 'IR', 'OP']);
            })
            ->join('sea2014.dbo.Admlink', 'Admlink.AdmNo', '=', 'ClaAss.AdmNo')
            ->join('sea2014.dbo.nVoucher as Voucher', 'Voucher.Voucher', '=', 'Admlink.Voucher')
            ->join('sea2014.dbo.Acceptance', 'Claim.ANO', '=', 'Acceptance.ANO')
            ->whereIn('Acceptance.OANO', $oanos)
            ->select([
                'Acceptance.ANO',
                DB::raw('COUNT(DISTINCT Claim.OCNO) as NOfClaimIncurred'),
                DB::raw('SUM(-ClaAss.Amount * Voucher.Rate) as Incurred_Claim')
            ])
            ->groupBy('Acceptance.ANO')
            ->havingRaw('SUM(-ClaAss.Amount * Voucher.Rate) <> 0')
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Step 2d: Get Business Source data
     * Equivalent to #Business_Source temp table
     */
    protected function getBusinessSource(Collection $acceptanceData): Collection
    {
        if ($acceptanceData->isEmpty()) {
            return collect([]);
        }

        $sourceIds = $acceptanceData->pluck('Source')->unique()->toArray();
        $anos = $acceptanceData->pluck('ANO')->toArray();
        $lanos = $acceptanceData->pluck('LANO')->toArray();
        $allAnos = array_unique(array_merge($anos, $lanos));

        return DB::connection($this->connection)
            ->table('sea2014.dbo.AccBS')
            ->join('sea2014.dbo.Profile', 'Profile.ID', '=', 'AccBS.ID')
            ->whereIn('AccBS.ID', $sourceIds)
            ->whereIn('AccBS.ANO', $allAnos)
            ->select([
                'AccBS.ANO',
                DB::raw('MAX(Profile.Name) as Name'),
                DB::raw('SUM(Fee) as Fee')
            ])
            ->groupBy('AccBS.ANO')
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Get claim count (JUMLAH_KLAIM)
     */
    protected function getClaimCount(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.Claim as B')
            ->whereIn('B.ANO', $anos)
            ->where('B.CType', '<>', 'C')
            ->select([
                'B.ANO',
                DB::raw('COUNT(DISTINCT B.ClaimNo) as JUMLAH_KLAIM')
            ])
            ->groupBy('B.ANO')
            ->get()
            ->keyBy('ANO');
    }

    // Continue in Part 2...
}

    /**
     * Step 3: Build Renewable Listing
     * Equivalent to #Renewable_Listing temp table
     */
    protected function buildRenewableListing(
        Collection $acceptanceData,
        Collection $premiumPaid,
        Collection $osClaim,
        Collection $incurredClaim,
        Collection $businessSource
    ): Collection {
        if ($acceptanceData->isEmpty()) {
            return collect([]);
        }

        $anos = $acceptanceData->pluck('ANO')->toArray();
        $cnos = $acceptanceData->pluck('CNO')->toArray();

        // Get PCalc data
        $pcalcData = DB::connection($this->connection)
            ->table('sea2014.dbo.PCalc')
            ->whereIn('ANO', $anos)
            ->where('TSI', '<>', 0)
            ->select([
                'ANO',
                DB::raw('MAX(OurShare / TSI * 100) as PCTShare'),
                DB::raw('SUM(OurShare * Rate) as OurShare'),
                DB::raw('SUM(Gross * OurShare / TSI * Rate) as Gross')
            ])
            ->groupBy('ANO')
            ->get()
            ->keyBy('ANO');

        // Get RArr Header (Facultative share)
        $rarrData = DB::connection($this->connection)
            ->table('sea2014.dbo.RArrHeader')
            ->whereIn('ANO', $anos)
            ->select(['ANO', 'FShare'])
            ->get()
            ->keyBy('ANO');

        // Get RCover Main (Category M)
        $rcoverMain = DB::connection($this->connection)
            ->table('sea2014.dbo.RCover')
            ->whereIn('ANO', $anos)
            ->whereIn('Unit', ['C', 'M'])
            ->where('Category', '=', 'M')
            ->select([
                'ANO',
                DB::raw("MAX(CAST(Remark as VARCHAR(255)) + ' Rate : ' + STR(Rate, 5, 2) + CASE WHEN Unit = 'C' THEN '/00' ELSE CASE WHEN Unit = 'M' THEN '/000' ELSE 'flat' END END) as Coverage"),
                DB::raw('MAX(Rate) as Rate')
            ])
            ->groupBy('ANO')
            ->get()
            ->keyBy('ANO');

        // Get RCover Perluasan (Category A)
        $rcoverPerluasan = DB::connection($this->connection)
            ->table('sea2014.dbo.RCover')
            ->whereIn('ANO', $anos)
            ->whereIn('Unit', ['C', 'M'])
            ->where('Category', '=', 'A')
            ->select([
                'ANO',
                DB::raw("MAX(CAST(Remark as VARCHAR(255)) + ' Rate : ' + STR(Rate, 5, 2) + CASE WHEN Unit = 'C' THEN '/00' ELSE CASE WHEN Unit = 'M' THEN '/000' ELSE 'flat' END END) as Coverage"),
                DB::raw('MAX(Rate) as Rate')
            ])
            ->groupBy('ANO')
            ->get()
            ->keyBy('ANO');

        // Get Payor discount
        $payorDiscount = DB::connection($this->connection)
            ->table('sea2014.dbo.Payor')
            ->whereIn('ANO', array_merge($anos, $acceptanceData->pluck('LANO')->toArray()))
            ->select([
                'ANO',
                DB::raw('SUM(GDiscount + (1 - GDiscount / 100) * Discount) as Discount')
            ])
            ->groupBy('ANO')
            ->get()
            ->keyBy('ANO');

        // Get APayor discount
        $apayorDiscount = DB::connection($this->connection)
            ->table('sea2014.dbo.APayor')
            ->whereIn('ANO', $anos)
            ->select([
                'ANO',
                DB::raw('SUM(Discount) as Discount')
            ])
            ->groupBy('ANO')
            ->get()
            ->keyBy('ANO');

        // Get main acceptance details
        $acceptanceDetails = DB::connection($this->connection)
            ->table('sea2014.dbo.Acceptance as A')
            ->leftJoin('sea2014.dbo.Cover as C', 'C.CNO', '=', 'A.CNO')
            ->leftJoin('sea2014.dbo.Profile as D', 'C.PHOLDER', '=', 'D.ID')
            ->whereIn('A.ANO', $anos)
            ->select([
                'A.ANO',
                'A.LANO',
                'A.CNO',
                'A.PolicyNo',
                'A.CertificateNo',
                'C.PHOLDER',
                'D.Name as PHOLDER_NAME',
                'A.SDate',
                'A.EDate',
                'A.ADate',
                'A.AName',
                'A.ESeqNo',
                'A.Branch',
                'C.TOC',
                'C.MO',
                'C.CoverNo',
                'C.Segment',
                'C.ISTYPE',
                'A.REMARKS',
                'A.Notes',
                'A.RENEWAL',
                'A.Location',
                'A.VALUEID',
                'A.REFNO'
            ])
            ->get()
            ->keyBy('ANO');

        // Combine all data
        $result = collect([]);

        foreach ($acceptanceData as $acc) {
            $ano = $acc->ANO;
            $detail = $acceptanceDetails->get($ano);
            
            if (!$detail) continue;

            $pcalc = $pcalcData->get($ano);
            $rarr = $rarrData->get($ano);
            $rcMain = $rcoverMain->get($ano);
            $rcPerl = $rcoverPerluasan->get($ano);
            $premium = $premiumPaid->get($ano);
            $osCl = $osClaim->get($ano);
            $incCl = $incurredClaim->get($ano);
            $bsrc = $businessSource->get($ano);
            $payor = $payorDiscount->get($ano);
            $apayor = $apayorDiscount->get($ano);

            $discount = $apayor->Discount ?? $payor->Discount ?? 0;
            $gross = $pcalc->Gross ?? 0;
            $premiumAmount = $premium->Premium ?? 0;
            $paid = $premium->Paid ?? 0;

            $premiumPaidCalc = 0;
            if ($premiumAmount != 0) {
                $premiumPaidCalc = $gross * (1 - ($discount / 100)) * $paid / $premiumAmount;
            }

            $result->push((object)[
                'ANO' => $ano,
                'LANO' => $detail->LANO,
                'CNO' => $detail->CNO,
                'PolicyNo' => $detail->PolicyNo,
                'CertificateNo' => $detail->CertificateNo,
                'PHOLDER' => $detail->PHOLDER,
                'PHOLDER_NAME' => $detail->PHOLDER_NAME,
                'SDate' => $detail->SDate,
                'EDate' => $detail->EDate,
                'ADate' => $detail->ADate,
                'AName' => $detail->AName,
                'ESeqNo' => $detail->ESeqNo,
                'Branch' => $detail->Branch,
                'TOC' => $detail->TOC,
                'MO' => $detail->MO,
                'OurShare' => $pcalc->OurShare ?? 0,
                'Gross' => $gross,
                'PCTShare' => $pcalc->PCTShare ?? 0,
                'Facultative' => (($pcalc->OurShare ?? 0) * ($rarr->FShare ?? 0)) / 100,
                'FShare' => $rarr->FShare ?? 0,
                'Coverage_Rate' => $rcMain->Coverage ?? '',
                'Coverage_Perluasan_Rate' => $rcPerl->Coverage ?? '',
                'Rate' => $rcMain->Rate ?? 0,
                'Rate_Perluasan' => $rcPerl->Rate ?? 0,
                'BSName' => $bsrc->Name ?? '',
                'Fee' => $bsrc->Fee ?? 0,
                'Discount' => $discount,
                'NOfClaimIncurred' => $incCl->NOfClaimIncurred ?? 0,
                'Incurred_Claim' => $incCl->Incurred_Claim ?? 0,
                'NOfClaimOS' => $osCl->NOfClaimOS ?? 0,
                'OS_Claim' => $osCl->OS_Claim ?? 0,
                'PremiumPaid' => $premiumPaidCalc,
                'PremiumOutstanding' => $premiumAmount - $paid,
                'CoverNo' => $detail->CoverNo,
                'Segment' => $detail->Segment,
                'ISTYPE' => $detail->ISTYPE,
                'REMARKS' => $detail->REMARKS,
                'Notes' => $detail->Notes,
                'RENEWAL' => $detail->RENEWAL,
                'Location' => $detail->Location,
                'VALUEID' => $detail->VALUEID,
                'REFNO' => $detail->REFNO,
            ]);
        }

        return $result;
    }

    /**
     * Step 5: Build Final Result with all 87 columns
     */
    protected function buildFinalResult(Collection $renewableListing, Collection $claimCount, array $anos): Collection
    {
        if ($renewableListing->isEmpty()) {
            return collect([]);
        }

        // Get additional data needed for final columns
        $tocDescriptions = $this->getTocDescriptions($renewableListing->pluck('TOC')->unique()->toArray());
        $ainfoData = $this->getAinfoData($anos);
        $pcalcTSI = $this->getPcalcTSI($anos);
        $rcoverRates = $this->getRcoverRates($anos);
        $profileData = $this->getProfileData($anos);
        $branchNames = $this->getBranchNames($renewableListing->pluck('Branch')->unique()->toArray());
        $marketingNames = $this->getMarketingNames($renewableListing->pluck('MO')->unique()->toArray());
        $claimDetails = $this->getClaimDetails($anos);
        $deductibles = $this->getDeductibles($anos);
        $bcaiShare = $this->getBcaiShare($anos, $renewableListing);
        $icoverData = $this->getIcoverData($anos);
        $taxVatData = $this->getTaxVatData($anos);
        $wilayahData = $this->getWilayahData($renewableListing);
        $coverRefNo = $this->getCoverRefNo($renewableListing->pluck('CNO')->unique()->toArray());
        $loadingPremi = $this->getLoadingPremi($anos);

        $result = collect([]);

        foreach ($renewableListing as $r) {
            $ano = $r->ANO;
            $ainfo = $ainfoData->get($ano);
            $pcalc = $pcalcTSI->get($ano);
            $rcover = $rcoverRates->get($ano) ?? (object)[];
            $profile = $profileData->get($ano);
            $claim = $claimCount->get($ano);
            $claimDetail = $claimDetails->get($ano);
            $deductible = $deductibles->get($ano);
            $share = $bcaiShare->get($ano);
            $icover = $icoverData->get($ano);
            $taxVat = $taxVatData->get($ano);
            $wilayah = $wilayahData->get(substr($r->REFNO ?? '', 0, 4));
            $coverRef = $coverRefNo->get($r->CNO);
            $loading = $loadingPremi->get($ano);

            // Calculate Loss Ratio
            $lossRatio = 0;
            if ($r->Gross != 0) {
                $lossRatio = (($r->Incurred_Claim ?? 0) + ($r->OS_Claim ?? 0)) / $r->Gross * 100;
            }

            // Build Policy_No
            $policyNo = empty($r->CertificateNo) 
                ? $r->PolicyNo 
                : $r->PolicyNo . '-' . $r->CertificateNo;

            // Determine Member status
            $member = ($r->ISTYPE === 'C') ? 'Yes' : 'No';

            // Status Backup
            $statusBackup = match($r->ISTYPE) {
                'C' => 'COINSURANCE MEMBER',
                'I' => 'DIRECT INSURANCE',
                'L' => 'COINSURANCE LEADER',
                'R' => 'INWARD FACULTATIVE',
                'T' => 'INWARD TREATY',
                default => ''
            };

            // EV Type
            $evType = '';
            if (substr($r->TOC ?? '', 0, 2) === '02') {
                if (!in_array($r->TOC, ['0205', '0215'])) {
                    $valueid2 = $ainfo->VALUEID2 ?? '';
                    $evCode = substr(substr($valueid2, -4), 1, 3);
                    $evType = match($evCode) {
                        'HYB' => 'Hybrid',
                        'ELE' => 'Listrik',
                        default => 'Konvensional'
                    };
                }
            }

            $result->push([
                'Policy_No' => $policyNo,
                'Start_Date' => $r->SDate ? Carbon::parse($r->SDate)->format('Y-m-d') : null,
                'End_Date' => $r->EDate ? Carbon::parse($r->EDate)->format('Y-m-d') : null,
                'Different_in_Month' => $r->SDate && $r->EDate 
                    ? Carbon::parse($r->SDate)->diffInMonths(Carbon::parse($r->EDate)) 
                    : 0,
                'Insured_Name' => $r->AName,
                'Policy_Holder' => $r->PHOLDER_NAME,
                'Coverage_Rate' => $r->Coverage_Rate,
                'Coverage_Perluasan_Rate' => $r->Coverage_Perluasan_Rate,
                'Rate' => $r->Rate,
                'Perluasan_Rate' => $r->Rate_Perluasan,
                'TSI100' => $pcalc->TSI ?? 0,
                'TSI100Currency' => $pcalc->CURRENCY ?? 'IDR',
                'TSI100CurrencyRate' => $pcalc->RATE ?? 1,
                'Premi' => $r->Gross,
                'Comm_%' => $r->Fee,
                'Commision' => ($r->Gross * $r->Fee) / 100,
                'Discount' => ($r->Gross * $r->Discount) / 100,
                'Incurred_Claim' => $r->Incurred_Claim ?? 0,
                'OS_Claim' => $r->OS_Claim ?? 0,
                'Premium_Paid' => $r->PremiumPaid ?? 0,
                'Premium_Outstanding' => $r->PremiumOutstanding ?? 0,
                'Loss_Ratio' => round($lossRatio, 2),
                'TOC' => $r->TOC . '-' . ($tocDescriptions->get($r->TOC) ?? ''),
                'Branch' => $r->Branch,
                'PHOLDER' => $r->PHOLDER,
                'ANO' => $ano,
                'Member' => $member,
                'PCTShare' => $r->PCTShare,
                'Facultative' => $r->Facultative,
                'FSHARE' => $r->FShare,
                'ISTYPE' => $r->ISTYPE,
                'OURSHARE' => $r->OurShare,
                'Status_Backup' => $statusBackup,
                'REMARKS' => $r->REMARKS,
                'Notes' => $r->Notes,
                'Segment' => $r->Segment,
                'Business_Source' => $r->BSName,
                'Total_Akumulasi' => '',
                'Gross' => $r->Gross,
                'Disc_%' => $r->Discount,
                'MAIN_COVER_CMP' => $rcover->CMP ?? 0,
                'MAIN_COVER_TLO' => $rcover->TLO ?? 0,
                'RSCC' => $rcover->RSCC ?? 0,
                'RSCCTS' => $rcover->RSCCTS ?? 0,
                'EQVET' => $rcover->EQVET ?? 0,
                'TS' => $rcover->TS ?? 0,
                'TSHFL' => $rcover->TSHFL ?? 0,
                'TPL' => $rcover->TPL ?? 0,
                'TPL_PENUMPANG' => $rcover->TPL_PENUMPANG ?? 0,
                'PA_PENUMPANG' => $rcover->PA_PENUMPANG ?? 0,
                'PA_PENGEMUDI' => $rcover->PA_PENGEMUDI ?? 0,
                'BENGKEL_AUTHORIZED' => $rcover->BENGKEL_AUTHORIZED ?? 0,
                'THEFT' => $rcover->THEFT ?? 0,
                'HE_TLO' => $rcover->HE_TLO ?? 0,
                'HE_COMPRE' => $rcover->HE_COMPRE ?? 0,
                'Deductible_Existing' => $deductible->Deductible ?? '',
                'Bcai_Share' => $share->Share ?? 100,
                'Total_Renewal' => $r->RENEWAL ?? 0,
                'Telp_Tertanggung' => $profile->Phone_1 ?? '',
                'HP_Tertanggung' => $profile->Mobile_1 ?? '',
                'Tanggal_Lahir' => $profile->Birthdate ?? null,
                'Metode_Bayar' => $profile->RefType ?? '',
                'Nama_Marketing' => $marketingNames->get($r->MO) ?? '',
                'VALUEID' => $r->VALUEID,
                'Nama_Cabang' => $branchNames->get($r->Branch) ?? '',
                'Merk' => $ainfo->Merk ?? '',
                'Model' => $ainfo->Model ?? '',
                'No_Polisi' => $ainfo->No_Polisi ?? '',
                'Type_Kendaraan' => $ainfo->Type_Kendaraan ?? '',
                'JUMLAH_KLAIM' => $claim->JUMLAH_KLAIM ?? 0,
                'LossDate' => $claimDetail->LossDate ?? '',
                'LossPlace' => $claimDetail->LossPlace ?? '',
                'Nilai_Claim' => $claimDetail->Nilai_Claim ?? 0,
                'Loading_Premi' => $loading->Loading ?? 0,
                'Wilayah' => $wilayah->Wilayah ?? null,
                'Kategori' => '',
                'Tahun_Pembuatan' => $ainfo->Tahun_Pembuatan ?? '',
                'TSI_PA_PASSANGER' => $icover->TSI_PA_PASSANGER ?? 0,
                'TSI_PA_DRIVER' => $icover->TSI_PA_DRIVER ?? 0,
                'TSI_TPL' => $icover->TSI_TPL ?? 0,
                'Location' => $r->Location,
                'Jenis_EV' => $evType,
                'AMOUNT_VAT' => $taxVat->VAT ?? 0,
                'AMOUNT_TAX' => $taxVat->TAX ?? 0,
                'OriginaldocNo' => $coverRef->RefNo ?? '',
                'Function' => $ainfo->Function ?? '',
                'ReferenceNo' => $r->REFNO,
            ]);
        }

        return $result;
    }

    /**
     * Get TOC descriptions
     */
    protected function getTocDescriptions(array $tocs): Collection
    {
        return DB::connection($this->connection)
            ->table('sea2014.dbo.TOC')
            ->whereIn('TOC', $tocs)
            ->pluck('DESCRIPTION', 'TOC');
    }

    /**
     * Get AInfo data (vehicle information)
     */
    protected function getAinfoData(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.AInfo as ai')
            ->whereIn('ai.ANO', $anos)
            ->select([
                'ai.ANO',
                'ai.VALUEID2',
                // Merk (V01)
                DB::raw("CASE 
                    WHEN ai.FLDID1 = 'V01' THEN ai.VALUEDESC1
                    WHEN ai.FLDID2 = 'V01' THEN ai.VALUEDESC2
                    WHEN ai.FLDID3 = 'V01' THEN ai.VALUEDESC3
                    WHEN ai.FLDID4 = 'V01' THEN ai.VALUEDESC4
                    WHEN ai.FLDID5 = 'V01' THEN ai.VALUEDESC5
                    WHEN ai.FLDID6 = 'V01' THEN ai.VALUEDESC6
                    WHEN ai.FLDID7 = 'V01' THEN ai.VALUEDESC7
                    WHEN ai.FLDID8 = 'V01' THEN ai.VALUEDESC8
                    WHEN ai.FLDID9 = 'V01' THEN ai.VALUEDESC9
                    WHEN ai.FLDID10 = 'V01' THEN ai.VALUEDESC10
                    ELSE '' END as Merk"),
                // Model (V31)
                DB::raw("CASE 
                    WHEN ai.FLDID1 = 'V31' THEN ai.VALUEDESC1
                    WHEN ai.FLDID2 = 'V31' THEN ai.VALUEDESC2
                    WHEN ai.FLDID3 = 'V31' THEN ai.VALUEDESC3
                    WHEN ai.FLDID4 = 'V31' THEN ai.VALUEDESC4
                    WHEN ai.FLDID5 = 'V31' THEN ai.VALUEDESC5
                    WHEN ai.FLDID6 = 'V31' THEN ai.VALUEDESC6
                    WHEN ai.FLDID7 = 'V31' THEN ai.VALUEDESC7
                    WHEN ai.FLDID8 = 'V31' THEN ai.VALUEDESC8
                    WHEN ai.FLDID9 = 'V31' THEN ai.VALUEDESC9
                    WHEN ai.FLDID10 = 'V31' THEN ai.VALUEDESC10
                    ELSE '' END as Model"),
                // No_Polisi (V04)
                DB::raw("CASE 
                    WHEN ai.FLDID1 = 'V04' THEN ai.VALUEDESC1
                    WHEN ai.FLDID2 = 'V04' THEN ai.VALUEDESC2
                    WHEN ai.FLDID3 = 'V04' THEN ai.VALUEDESC3
                    WHEN ai.FLDID4 = 'V04' THEN ai.VALUEDESC4
                    WHEN ai.FLDID5 = 'V04' THEN ai.VALUEDESC5
                    WHEN ai.FLDID6 = 'V04' THEN ai.VALUEDESC6
                    WHEN ai.FLDID7 = 'V04' THEN ai.VALUEDESC7
                    WHEN ai.FLDID8 = 'V04' THEN ai.VALUEDESC8
                    WHEN ai.FLDID9 = 'V04' THEN ai.VALUEDESC9
                    WHEN ai.FLDID10 = 'V04' THEN ai.VALUEDESC10
                    ELSE '' END as No_Polisi"),
                // Type_Kendaraan (V06)
                DB::raw("CASE 
                    WHEN ai.FLDID1 = 'V06' THEN ai.VALUEDESC1
                    WHEN ai.FLDID2 = 'V06' THEN ai.VALUEDESC2
                    WHEN ai.FLDID3 = 'V06' THEN ai.VALUEDESC3
                    WHEN ai.FLDID4 = 'V06' THEN ai.VALUEDESC4
                    WHEN ai.FLDID5 = 'V06' THEN ai.VALUEDESC5
                    WHEN ai.FLDID6 = 'V06' THEN ai.VALUEDESC6
                    WHEN ai.FLDID7 = 'V06' THEN ai.VALUEDESC7
                    WHEN ai.FLDID8 = 'V06' THEN ai.VALUEDESC8
                    WHEN ai.FLDID9 = 'V06' THEN ai.VALUEDESC9
                    WHEN ai.FLDID10 = 'V06' THEN ai.VALUEDESC10
                    ELSE '' END as Type_Kendaraan"),
                // Tahun_Pembuatan (V16)
                DB::raw("CASE 
                    WHEN ai.FLDID1 = 'V16' THEN ai.VALUEDESC1
                    WHEN ai.FLDID2 = 'V16' THEN ai.VALUEDESC2
                    WHEN ai.FLDID3 = 'V16' THEN ai.VALUEDESC3
                    WHEN ai.FLDID4 = 'V16' THEN ai.VALUEDESC4
                    WHEN ai.FLDID5 = 'V16' THEN ai.VALUEDESC5
                    WHEN ai.FLDID6 = 'V16' THEN ai.VALUEDESC6
                    WHEN ai.FLDID7 = 'V16' THEN ai.VALUEDESC7
                    WHEN ai.FLDID8 = 'V16' THEN ai.VALUEDESC8
                    WHEN ai.FLDID9 = 'V16' THEN ai.VALUEDESC9
                    WHEN ai.FLDID10 = 'V16' THEN ai.VALUEDESC10
                    ELSE '' END as Tahun_Pembuatan"),
                // Function (V26)
                DB::raw("CASE 
                    WHEN ai.FLDID1 = 'V26' THEN ai.VALUEDESC1
                    WHEN ai.FLDID2 = 'V26' THEN ai.VALUEDESC2
                    WHEN ai.FLDID3 = 'V26' THEN ai.VALUEDESC3
                    WHEN ai.FLDID4 = 'V26' THEN ai.VALUEDESC4
                    WHEN ai.FLDID5 = 'V26' THEN ai.VALUEDESC5
                    WHEN ai.FLDID6 = 'V26' THEN ai.VALUEDESC6
                    WHEN ai.FLDID7 = 'V26' THEN ai.VALUEDESC7
                    WHEN ai.FLDID8 = 'V26' THEN ai.VALUEDESC8
                    WHEN ai.FLDID9 = 'V26' THEN ai.VALUEDESC9
                    WHEN ai.FLDID10 = 'V26' THEN ai.VALUEDESC10
                    ELSE '' END as [Function]"),
            ])
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Get PCalc TSI data
     */
    protected function getPcalcTSI(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.PCalc')
            ->whereIn('ANO', $anos)
            ->select(['ANO', 'TSI', 'CURRENCY', 'RATE'])
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Get RCover rates for all coverage types
     */
    protected function getRcoverRates(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        $result = collect([]);

        foreach ($anos as $ano) {
            $rates = (object)[
                'CMP' => 0,
                'TLO' => 0,
                'RSCC' => 0,
                'RSCCTS' => 0,
                'EQVET' => 0,
                'TS' => 0,
                'TSHFL' => 0,
                'TPL' => 0,
                'TPL_PENUMPANG' => 0,
                'PA_PENUMPANG' => 0,
                'PA_PENGEMUDI' => 0,
                'BENGKEL_AUTHORIZED' => 0,
                'THEFT' => 0,
                'HE_TLO' => 0,
                'HE_COMPRE' => 0,
            ];

            $rcoverData = DB::connection($this->connection)
                ->table('sea2014.dbo.RCover')
                ->where('ANO', $ano)
                ->select(['Code', 'Rate', 'EDATE'])
                ->orderBy('SDate', 'desc')
                ->get();

            foreach ($rcoverData as $rc) {
                $code = strtoupper($rc->Code);
                
                if (str_contains($code, 'CMP') && $rates->CMP == 0) $rates->CMP = $rc->Rate;
                if (str_contains($code, 'TLO') && !str_contains($code, 'HE') && $rates->TLO == 0) $rates->TLO = $rc->Rate;
                if (str_contains($code, 'RSCC') && !str_contains($code, 'TS') && $rates->RSCC == 0) $rates->RSCC = $rc->Rate;
                if (str_contains($code, 'RSCCTS') && $rates->RSCCTS == 0) $rates->RSCCTS = $rc->Rate;
                if (str_contains($code, 'EQ') && $rates->EQVET == 0) $rates->EQVET = $rc->Rate;
                if ($code === 'TS' && $rates->TS == 0) $rates->TS = $rc->Rate;
                if (str_starts_with($code, 'FL-') && $rates->TSHFL == 0) $rates->TSHFL = $rc->Rate;
                if (str_contains($code, 'TPL') && !str_contains($code, '09') && $rates->TPL == 0) $rates->TPL = $rc->Rate;
                if (str_contains($code, 'TPL-09') && $rates->TPL_PENUMPANG == 0) $rates->TPL_PENUMPANG = $rc->Rate;
                if (str_contains($code, 'PA-01') && $rates->PA_PENUMPANG == 0) $rates->PA_PENUMPANG = $rc->Rate;
                if (str_contains($code, 'PA-02') && $rates->PA_PENGEMUDI == 0) $rates->PA_PENGEMUDI = $rc->Rate;
                if (str_contains($code, 'AW') && $rates->BENGKEL_AUTHORIZED == 0) $rates->BENGKEL_AUTHORIZED = $rc->Rate;
                if (str_contains($code, 'TFT') && $rates->THEFT == 0) $rates->THEFT = $rc->Rate;
                if (str_contains($code, 'HE-02') && $rates->HE_TLO == 0) $rates->HE_TLO = $rc->Rate;
                if (str_contains($code, 'HE-01') && $rates->HE_COMPRE == 0) $rates->HE_COMPRE = $rc->Rate;
            }

            $result->put($ano, $rates);
        }

        return $result;
    }

    /**
     * Get Profile data (phone, email, birthdate)
     */
    protected function getProfileData(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.Acceptance as a')
            ->leftJoin('sea2014.dbo.Profile as p', 'p.ID', '=', 'a.AID')
            ->leftJoin('sea2014.dbo.Profile_Ext as pe', 'p.ID', '=', 'pe.ID')
            ->leftJoin('sea2014.dbo.Payor as py', 'py.ANO', '=', 'a.ANO')
            ->whereIn('a.ANO', $anos)
            ->select([
                'a.ANO',
                'p.Phone_1',
                'p.Mobile_1',
                'pe.Birthdate',
                'py.RefType'
            ])
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Get Branch names
     */
    protected function getBranchNames(array $branches): Collection
    {
        return DB::connection($this->connection)
            ->table('sea2014.dbo.Branch')
            ->whereIn('Branch', $branches)
            ->pluck('Name', 'Branch');
    }

    /**
     * Get Marketing names
     */
    protected function getMarketingNames(array $mos): Collection
    {
        return DB::connection($this->connection)
            ->table('sea2014.dbo.SysUser')
            ->whereIn('ID', $mos)
            ->pluck('Name', 'ID');
    }

    /**
     * Get Claim details (LossDate, LossPlace, Nilai_Claim)
     */
    protected function getClaimDetails(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        $result = collect([]);

        foreach ($anos as $ano) {
            $claim = DB::connection($this->connection)
                ->table('sea2014.dbo.Claim as c')
                ->where('c.ANO', $ano)
                ->orderBy('c.CNO', 'desc')
                ->select(['c.LossDate', 'c.LossPlace'])
                ->first();

            $nilaiClaim = DB::connection($this->connection)
                ->table('sea2014.dbo.CCalc as cl')
                ->join('sea2014.dbo.Claim as c', 'c.CNO', '=', 'cl.CNO')
                ->where('c.ANO', $ano)
                ->sum('cl.Claim');

            $result->put($ano, (object)[
                'LossDate' => $claim->LossDate ?? '',
                'LossPlace' => $claim->LossPlace ?? '',
                'Nilai_Claim' => $nilaiClaim ?? 0,
            ]);
        }

        return $result;
    }

    /**
     * Get Deductibles
     */
    protected function getDeductibles(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.AccDed')
            ->whereIn('ANO', $anos)
            ->select([
                'ANO',
                DB::raw("STRING_AGG(CAST(REMARKS as NVARCHAR(MAX)), ' + ') as Deductible")
            ])
            ->groupBy('ANO')
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Get BCAI Share
     */
    protected function getBcaiShare(array $anos, Collection $renewableListing): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        $result = collect([]);

        foreach ($renewableListing as $r) {
            $ano = $r->ANO;
            $share = 100; // Default

            if ($r->ISTYPE === 'C') {
                // Coinsurance member - get from isource
                $isource = DB::connection($this->connection)
                    ->table('sea2014.dbo.ISource')
                    ->where('CNO', $r->CNO)
                    ->value('SHARE');
                $share = $isource ?? 100;
            } elseif ($r->ISTYPE === 'L') {
                // Coinsurance leader
                $coinsSum = DB::connection($this->connection)
                    ->table('sea2014.dbo.Coinsurance')
                    ->where('CNO', $r->CNO)
                    ->sum('Share');
                $share = 100 - ($coinsSum ?? 0);
            } elseif ($r->ISTYPE === 'R') {
                // Inward facultative
                $rarr = DB::connection($this->connection)
                    ->table('sea2014.dbo.RArrHeader')
                    ->where('ANO', $ano)
                    ->value('PCTShare');
                $share = $rarr ?? 100;
            }

            $result->put($ano, (object)['Share' => $share]);
        }

        return $result;
    }

    /**
     * Get ICover data (TSI for PA and TPL)
     */
    protected function getIcoverData(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        $result = collect([]);

        foreach ($anos as $ano) {
            $paPassenger = DB::connection($this->connection)
                ->table('sea2014.dbo.ICover as i')
                ->join('sea2014.dbo.TOI as t', 'i.TOI', '=', 't.TOI')
                ->where('i.ANO', $ano)
                ->whereIn('t.TOI', ['P04', 'P13', 'P17'])
                ->value('i.SI') ?? 0;

            $paDriver = DB::connection($this->connection)
                ->table('sea2014.dbo.ICover as i')
                ->join('sea2014.dbo.TOI as t', 'i.TOI', '=', 't.TOI')
                ->where('i.ANO', $ano)
                ->where('t.TOI', 'D06')
                ->value('i.SI') ?? 0;

            $tpl = DB::connection($this->connection)
                ->table('sea2014.dbo.ICover as i')
                ->join('sea2014.dbo.TOI as t', 'i.TOI', '=', 't.TOI')
                ->where('i.ANO', $ano)
                ->whereIn('t.TOI', ['T02', 'T08', 'T09', 'T12', 'T15', 'T16', 'T17', 'T18', 'T19'])
                ->value('i.SI') ?? 0;

            $result->put($ano, (object)[
                'TSI_PA_PASSANGER' => $paPassenger,
                'TSI_PA_DRIVER' => $paDriver,
                'TSI_TPL' => $tpl,
            ]);
        }

        return $result;
    }

    /**
     * Get Tax and VAT data
     */
    protected function getTaxVatData(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.Acceptance as a')
            ->leftJoin(DB::raw('(SELECT ANO, AdmNo, SUM(AMOUNT) as x FROM sea2014.dbo.AccAss WHERE Code = \'C\' GROUP BY ANO, AdmNo) as aa'), 'aa.ANO', '=', 'a.ANO')
            ->leftJoin('sea2014.dbo.AdmLink as al', 'aa.AdmNo', '=', 'al.AdmNo')
            ->leftJoin('sea2014.dbo.nVoucher as nv', 'nv.Voucher', '=', 'al.Voucher')
            ->whereIn('a.ANO', $anos)
            ->select([
                'a.ANO',
                DB::raw('CASE WHEN al.AMOUNT_2 <> 0 THEN ISNULL((aa.x / al.AMOUNT_2 * al.AMOUNT_13) * nv.Rate, 0) ELSE 0 END as VAT'),
                DB::raw('CASE WHEN al.AMOUNT_2 <> 0 THEN ISNULL((aa.x / al.AMOUNT_2 * al.AMOUNT_14) * nv.Rate, 0) ELSE 0 END as TAX')
            ])
            ->get()
            ->keyBy('ANO');
    }

    /**
     * Get Wilayah data
     */
    protected function getWilayahData(Collection $renewableListing): Collection
    {
        $refnos = $renewableListing->map(fn($r) => substr($r->REFNO ?? '', 0, 4))->unique()->toArray();

        if (empty($refnos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.CSI_CabangBCAF')
            ->whereIn('KodeCabang', $refnos)
            ->pluck('Wilayah', 'KodeCabang');
    }

    /**
     * Get Cover RefNo
     */
    protected function getCoverRefNo(array $cnos): Collection
    {
        if (empty($cnos)) {
            return collect([]);
        }

        return DB::connection($this->connection)
            ->table('sea2014.dbo.Cover')
            ->whereIn('CNO', $cnos)
            ->select(['CNO', 'RefNo'])
            ->get()
            ->keyBy('CNO');
    }

    /**
     * Get Loading Premi
     */
    protected function getLoadingPremi(array $anos): Collection
    {
        if (empty($anos)) {
            return collect([]);
        }

        $result = collect([]);

        foreach ($anos as $ano) {
            $loading = DB::connection($this->connection)
                ->table('sea2014.dbo.RCover')
                ->where('ANO', $ano)
                ->where('CATEGORY', 'M')
                ->value('Loading');

            $result->put($ano, (object)['Loading' => $loading ?? 0]);
        }

        return $result;
    }
}
