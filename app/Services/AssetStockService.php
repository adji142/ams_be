<?php

namespace App\Services;

use App\Models\MasterAsset;
use App\Models\AssetLocationHistory;
use App\Models\AssetMovingHistory;
use Illuminate\Support\Facades\DB;

class AssetStockService
{
    /**
     * Tambahkan stok ke lokasi tertentu
     */
    public function addStock($kodeAsset, $kodeLokasi, $jumlah, $noReff, $baseReff)
    {
        return DB::transaction(function () use ($kodeAsset, $kodeLokasi, $jumlah, $noReff, $baseReff) {
            // ✅ Update jumlah di master_assets
            $asset = MasterAsset::where('KodeAsset', $kodeAsset)->first();
            if ($asset) {
                $asset->increment('Jumlah', $jumlah);
            }

            // ✅ Update atau buat record di AssetLocationHistory
            $loc = AssetLocationHistory::firstOrNew([
                'KodeAsset' => $kodeAsset,
                'KodeLokasi' => $kodeLokasi,
            ]);
            $loc->Jumlah = ($loc->Jumlah ?? 0) + $jumlah;
            $loc->save();

            // ✅ Catat ke AssetMovingHistory
            AssetMovingHistory::create([
                'KodeAsset' => $kodeAsset,
                'KodeLokasi' => $kodeLokasi,
                'NoReff' => $noReff,
                'BaseReff' => $baseReff,
                'Jumlah' => $jumlah, // positif artinya barang masuk
            ]);
        });
    }

    /**
     * Kurangi stok dari lokasi tertentu
     */
    public function removeStock($kodeAsset, $kodeLokasi, $jumlah, $noReff, $baseReff)
    {
        return DB::transaction(function () use ($kodeAsset, $kodeLokasi, $jumlah, $noReff, $baseReff) {
            // ✅ Kurangi master_assets
            $asset = MasterAsset::where('KodeAsset', $kodeAsset)->first();
            if ($asset) {
                $asset->decrement('Jumlah', $jumlah);
            }

            // ✅ Kurangi dari AssetLocationHistory
            $loc = AssetLocationHistory::where('KodeAsset', $kodeAsset)
                ->where('KodeLokasi', $kodeLokasi)
                ->first();

            if ($loc) {
                $loc->Jumlah = max(0, $loc->Jumlah - $jumlah);
                $loc->save();
            }

            // ✅ Catat ke AssetMovingHistory (nilai negatif)
            AssetMovingHistory::create([
                'KodeAsset' => $kodeAsset,
                'KodeLokasi' => $kodeLokasi,
                'NoReff' => $noReff,
                'BaseReff' => $baseReff,
                'Jumlah' => -$jumlah,
            ]);
        });
    }

    /**
     * (Opsional) Pindahkan stok antar lokasi
     */
    public function transferStock($kodeAsset, $lokasiAsal, $lokasiTujuan, $jumlah, $noReff, $baseReff)
    {
        return DB::transaction(function () use ($kodeAsset, $lokasiAsal, $lokasiTujuan, $jumlah, $noReff, $baseReff) {
            // Kurangi dari lokasi asal
            $this->removeStock($kodeAsset, $lokasiAsal, $jumlah, $noReff, $baseReff . ' (Transfer Out)');

            // Tambahkan ke lokasi tujuan
            $this->addStock($kodeAsset, $lokasiTujuan, $jumlah, $noReff, $baseReff . ' (Transfer In)');
        });
    }
}
