<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanScrapApproval extends Model
{
    use HasFactory;

    protected $table = 'permintaan_scrap_approval';

    protected $fillable = [
        'NoTransaksi',
        'Level',
        'ApproverID',
        'Status',
        'Keterangan',
        'ApprovedAt',
    ];

    protected $casts = [
        'ApprovedAt' => 'datetime',
    ];

    /**
     * Relasi ke tabel employees
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'ApproverID');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('Status', $status);
    }

    /**
     * Scope untuk filter berdasarkan nomor transaksi
     */
    public function scopeNoTransaksi($query, $noTransaksi)
    {
        return $query->where('NoTransaksi', $noTransaksi);
    }
}
