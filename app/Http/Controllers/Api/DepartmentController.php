<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;



class DepartmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/departments",
     *     summary="Get list of departments",
     *     tags={"Departments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Department"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Department::get());
    }

    /**
     * @OA\Post(
     *     path="/api/departments",
     *     summary="Create a new department",
     *     tags={"Departments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","name"},
     *             @OA\Property(property="code", type="string", example="IT"),
     *             @OA\Property(property="name", type="string", example="Information Technology"),
     *             @OA\Property(property="description", type="string", example="Handles IT infrastructure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Department created",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:departments,code',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($data);
        return response()->json(['message'=>'Department created','department'=>$department], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/departments/{id}",
     *     summary="Get department detail",
     *     tags={"Departments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(response=404, description="Department not found")
     * )
     */
    public function show(Department $department)
    {
        return response()->json($department);
    }

    /**
     * @OA\Put(
     *     path="/api/departments/{id}",
     *     summary="Update a department",
     *     tags={"Departments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","name"},
     *             @OA\Property(property="code", type="string", example="FIN"),
     *             @OA\Property(property="name", type="string", example="Finance"),
     *             @OA\Property(property="description", type="string", example="Handles company finances")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department updated",
     *         @OA\JsonContent(ref="#/components/schemas/Department")
     *     ),
     *     @OA\Response(response=404, description="Department not found")
     * )
     */
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:departments,code,'.$department->id,
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $department->update($data);
        return response()->json(['message'=>'Department updated','department'=>$department]);
    }

    /**
     * @OA\Delete(
     *     path="/api/departments/{id}",
     *     summary="Delete a department",
     *     tags={"Departments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Department deleted"),
     *     @OA\Response(response=404, description="Department not found")
     * )
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['message'=>'Department deleted']);
    }
}
