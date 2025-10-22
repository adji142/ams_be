<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeMasterImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $departmentCache = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $this->cacheDepartments($rows->pluck('kodedepartement')->filter()->unique());

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $departmentId = $this->departmentCache[$row['kodedepartement']] ?? null;

                Employee::create([
                    'nik' => $row['nik'],
                    'name' => $row['name'],
                    'department_id' => $departmentId,
                    'tgl_masuk' => $row['tgl_masuk'],
                    'tgl_resign' => $row['tgl_resign'],
                    'tempat_lahir' => $row['tempat_lahir'],
                    'tgl_lahir' => $row['tgl_lahir'],
                    'email' => $row['email'] ?? "",
                    'no_tlp' => $row['no_tlp'],
                ]);
            }
        });
    }

    public function rules(): array
    {
        return [
            '*.nik' => 'required|unique:employees,nik',
            '*.name' => 'required',
            '*.kodedepartement' => 'required|exists:departments,code',
            '*.tgl_masuk' => 'nullable',
            '*.tgl_resign' => 'nullable',
            '*.tempat_lahir' => 'nullable',
            '*.tgl_lahir' => 'nullable',
            '*.email' => 'nullable|email',
            '*.no_tlp' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nik.unique' => 'NIK sudah ada.',
            '*.kodedepartement.exists' => 'Kode Department :input tidak ditemukan.',
        ];
    }

    private function cacheDepartments(Collection $codes)
    {
        $this->departmentCache = Department::whereIn('code', $codes)->pluck('id', 'code')->toArray();
    }
}
