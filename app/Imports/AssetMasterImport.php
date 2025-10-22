<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\GrupAsset;
use App\Models\MasterAsset;
use App\Models\MasterStatusAsset;
use App\Models\SerahTerimaHeader;
use App\Models\SerahTerimaDetail;
use App\Services\AssetStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class AssetMasterImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $userId;
    private $defaultStatusId;
    private $grupAssetCache = [];
    private $employeeCache = [];
    private $serahTerimaHeader; // Property to hold the single header

    public function __construct($userId)
    {
        $this->userId = $userId;
        // Cache default status ID
        $this->defaultStatusId = MasterStatusAsset::where('isDefault', 1)->value('id');
        $this->serahTerimaHeader = null;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return; // No data to process
        }

        // Pre-load and cache necessary related data to avoid querying in a loop
        $this->cacheGrupAssets($rows->pluck('kode_grup')->filter()->unique());
        $this->cacheEmployees($rows->pluck('nik_pic')->filter()->unique());

        DB::transaction(function () use ($rows) {
            // --- Create Single Serah Terima Header --- 
            $firstRow = $rows->first();
            $penerimaIdForHeader = $this->employeeCache[$firstRow['nik_pic']] ?? null;

            if (!$penerimaIdForHeader) {
                // Optional: throw an exception if the first row must have a valid recipient
                return; // or handle error appropriately
            }

            $this->serahTerimaHeader = SerahTerimaHeader::create([
                'NoSerahTerima' => $this->generateNoSerahTerima(),
                'TglSerahTerima' => Carbon::now(),
                'NomorPermintaan' => '', // As requested
                'PenerimaID' => $penerimaIdForHeader, // Use recipient from the first row
                'Keterangan' => 'Bulk asset import on ' . Carbon::now()->toDateTimeString(),
                'DocStatus' => 1, // 1 = Open/Posted
                // 'created_by' => $this->userId,
            ]);

            // --- Process Each Row --- 
            foreach ($rows as $row) {
                $grupAssetId = $this->grupAssetCache[$row['kode_grup']] ?? null;
                $picId = $this->employeeCache[$row['nik_pic']] ?? null;
                $quantity = (int)$row['jumlah'];

                // 1. Import MasterAsset
                $masterAsset = MasterAsset::create([
                    'KodeAsset' => $row['kode_asset'],
                    'NamaAsset' => $row['nama_asset'],
                    'GrupAssetID' => $grupAssetId,
                    'PIC' => $picId,
                    'StatusID' => $this->defaultStatusId,
                    'TglBeli' => Carbon::parse($row['tgl_beli']),
                    'TglKapitalisasi' => Carbon::parse($row['tgl_kapitalisasi']),
                    'UmurPakai' => $row['umur_pakai'],
                    'Keterangan' => $row['keterangan'],
                    'Jumlah' => 0, // Master asset quantity is managed by stock, not a single field
                    // 'created_by' => $this->userId, // Assuming you have this field
                ]);

                // 2. Create SerahTerimaDetail linked to the single header
                if ($quantity > 0) {
                    $this->serahTerimaHeader->details()->create([
                        'NoSerahTerima' => $this->serahTerimaHeader->NoSerahTerima,
                        'NomorPermintaan' => '', // As requested
                        'NoUrutPermintaan' => -1, // As requested
                        'KodeAsset' => $masterAsset->KodeAsset,
                        'NamaAsset' => $masterAsset->NamaAsset,
                        'QtyDiterima' => $quantity, // Use 'jumlah' from Excel for quantity
                        'KodeLokasi' => $row['kode_lokasi_asset'], // Use new field name
                        'Keterangan' => 'Initial import',
                        // 'created_by' => $this->userId,
                    ]);

                    // 3. Update asset stock/location using AssetStockService
                    $assetStockService = app(AssetStockService::class);
                    $assetStockService->addStock(
                        $masterAsset->KodeAsset,
                        $row['kode_lokasi_asset'], // Use new field name
                        $quantity, // Use 'jumlah' from Excel for quantity
                        $this->serahTerimaHeader->NoSerahTerima,
                        'SerahTerimaAsset'
                    );
                }
            }
        });
    }

    /**
     * ---------------------------------------------------------------------------
     * NAMA FIELD DI FILE EXCEL
     * ---------------------------------------------------------------------------
     * Tentukan nama-nama kolom di file Excel Anda di sini.
     * Nama-nama ini HARUS SAMA PERSIS dengan header di file Excel.
     * 
     * Contoh header di file Excel:
     * | kode_asset | nama_asset | kode_grup | tgl_beli   | tgl_kapitalisasi | umur_pakai | keterangan | jumlah | nik_pic | kode_lokasi_asset |
     * |------------|------------|-----------|------------|------------------|------------|------------|--------|---------|-------------------|
     * | ASSET-001  | Laptop A   | ELK       | 2023-01-15 | 2023-01-20       | 5          | Pembelian  | 1      | 12345   | GUDANG-JKT        |
     * 
     */
    public function rules(): array
    {
        return [
            '*.kode_asset' => 'required|unique:master_assets,KodeAsset',
            '*.nama_asset' => 'required',
            '*.kode_grup' => 'required|exists:grup_assets,kode_Grup',
            '*.tgl_beli' => 'required',
            '*.tgl_kapitalisasi' => 'required',
            '*.umur_pakai' => 'required',
            '*.jumlah' => 'required|min:1',
            '*.nik_pic' => 'required|exists:employees,nik',
            '*.kode_lokasi_asset' => 'required|exists:lokasi_assets,kode_lokasi',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.kode_asset.unique' => 'Kode asset sudah ada.',
            '*.kode_grup.exists' => 'Kode grup :input tidak ditemukan.',
            '*.nik_pic.exists' => 'NIK PIC :input tidak ditemukan.',
            '*.kode_lokasi_asset.exists' => 'Kode Lokasi Asset :input tidak ditemukan.',
        ];
    }

    private function cacheGrupAssets(Collection $kodeGrups)
    {
        $this->grupAssetCache = GrupAsset::whereIn('kode_Grup', $kodeGrups)->pluck('id', 'kode_Grup')->toArray();
    }

    private function cacheEmployees(Collection $niks)
    {
        $this->employeeCache = Employee::whereIn('nik', $niks)->pluck('id', 'nik')->toArray();
    }

    private function generateNoSerahTerima()
    {
        $prefix = 'STA-IMPORT/' . date('Ym') . '/';
        $lastDoc = SerahTerimaHeader::where('NoSerahTerima', 'like', $prefix . '%')
                        ->orderBy('NoSerahTerima', 'desc')
                        ->first();
        
        $newNumber = 1;
        if ($lastDoc) {
            $newNumber = (int)substr($lastDoc->NoSerahTerima, -4) + 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
