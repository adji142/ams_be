<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MutasiAssetHistory;
use App\Models\MasterAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MutasiAssetPICController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'TglTransaksi' => 'required|date',
            'PIC_Lama'     => 'required|integer',
            'PIC_Baru'     => 'required|integer',
            'Keterangan'   => 'nullable|string',
            'KodeAsset'    => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // ----------------------------------------------------
            // 1. Generate Running Number
            // ----------------------------------------------------
            $prefix = "MPIC-" . Carbon::now()->format("Ymd") . "-";

            $last = MutasiAssetHistory::where('NoTransaksi', 'like', $prefix . '%')
                    ->orderBy('NoTransaksi', 'desc')
                    ->first();

            if ($last) {
                $num = intval(substr($last->NoTransaksi, -4)) + 1;
            } else {
                $num = 1;
            }

            $noTransaksi = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

            // ----------------------------------------------------
            // 2. Simpan History
            // ----------------------------------------------------
            MutasiAssetHistory::create([
                'NoTransaksi' => $noTransaksi,
                'TglTransaksi' => $request->TglTransaksi,
                'PIC_Lama'     => $request->PIC_Lama,
                'PIC_Baru'     => $request->PIC_Baru,
                'Keterangan'   => $request->Keterangan,
                'KodeAsset'    => $request->KodeAsset,
            ]);

            // ----------------------------------------------------
            // 3. Update aset di MasterAsset
            // ----------------------------------------------------
            MasterAsset::where('KodeAsset', $request->KodeAsset)
                ->update([
                    'PIC' => $request->PIC_Baru
                ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Mutasi PIC asset berhasil.',
                'NoTransaksi' => $noTransaksi
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
