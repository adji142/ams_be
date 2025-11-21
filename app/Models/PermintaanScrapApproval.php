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
        return $this->belongsTo(Employee::class, 'ApproverID','id')->with('user');
    }
    public function getApproverUserAttribute()
    {
        return \DB::table('users')
            ->join('employees', 'users.KaryawanID', '=', 'employees.id')
            ->where('employees.id', $this->ApproverID)
            ->select('users.*')
            ->first();
    }

    public function approverUser()
    {
        return $this->hasOneThrough(
            User::class,        // model akhir
            Employee::class,    // model perantara
            'id',               // employee.id (local key)
            'KaryawanID',       // users.KaryawanID
            'ApproverID',       // approval.ApproverID
            'id'                // employee.id
        );
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
