<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

/**
 * @OA\Tag(
 *     name="Employees",
 *     description="API for managing employees"
 * )
 */
class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/employees",
     *     summary="List employees",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="department_id",
     *         in="query",
     *         description="Filter by department ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, NIK, or email",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Employee"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Employee::join('departments', 'employees.department_id', '=', 'departments.id')
            ->select('employees.*', 'departments.name as department_name');

        if ($request->filled('department_id')) {
            $query->where('employees.department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('employees.name', 'like', "%{$searchTerm}%")
                  ->orWhere('employees.nik', 'like', "%{$searchTerm}%")
                  ->orWhere('employees.email', 'like', "%{$searchTerm}%");
            });
        }

        return $query->get();
    }

    /**
     * @OA\Post(
     *     path="/api/employees",
     *     summary="Create employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nik","name","department_id","tgl_masuk","email"},
     *             @OA\Property(property="nik", type="string", example="EMP001"),
     *             @OA\Property(property="name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="department_id", type="integer", example=2),
     *             @OA\Property(property="tgl_masuk", type="string", format="date", example="2020-01-15"),
     *             @OA\Property(property="tgl_resign", type="string", format="date", nullable=true),
     *             @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     *             @OA\Property(property="tgl_lahir", type="string", format="date", example="1995-04-20"),
     *             @OA\Property(property="email", type="string", example="budi@example.com"),
     *             @OA\Property(property="no_tlp", type="string", example="+628123456789")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Employee created", @OA\JsonContent(ref="#/components/schemas/Employee")),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nik' => 'required|unique:employees,nik',
            'name' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'tgl_masuk' => 'required|date',
            'tgl_resign' => 'nullable|date',
            'tempat_lahir' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'email' => 'required|email|unique:employees,email',
            'no_tlp' => 'nullable|string',
        ]);

        $employee = Employee::create($data);
        return response()->json($employee->load('department'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/employees/{id}",
     *     summary="Get employee detail",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/Employee")),
     *     @OA\Response(response=404, description="Employee not found")
     * )
     */
    public function show(Employee $employee)
    {
        return $employee->load('department');
    }

    /**
     * @OA\Put(
     *     path="/api/employees/{id}",
     *     summary="Update employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Employee")
     *     ),
     *     @OA\Response(response=200, description="Employee updated", @OA\JsonContent(ref="#/components/schemas/Employee")),
     *     @OA\Response(response=404, description="Employee not found")
     * )
     */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'nik' => 'required|string|unique:employees,nik,'.$employee->id,
            'name' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'tgl_masuk' => 'required|date',
            'tgl_resign' => 'nullable|date',
            'tempat_lahir' => 'nullable|string',
            'tgl_lahir' => 'nullable|date',
            'email' => 'required|email|unique:employees,email,'.$employee->id,
            'no_tlp' => 'nullable|string',
        ]);

        $employee->update($data);
        return response()->json($employee->load('department'));
    }

    /**
     * @OA\Delete(
     *     path="/api/employees/{id}",
     *     summary="Delete employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Employee deleted"),
     *     @OA\Response(response=404, description="Employee not found")
     * )
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
