<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="Permission management"
 * )
 */
class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/permissions",
     *     summary="Get list of permissions",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="view_users"),
     *             @OA\Property(property="label", type="string", example="View Users")
     *         ))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index() { return response()->json(Permission::all()); }

        /**
        * @OA\Post(
        *     path="/api/permissions",
        *     summary="Create a new permission",
        *     tags={"Permissions"},
        *     security={{"sanctum":{}}},
        *     @OA\RequestBody(
        *         required=true,
        *         @OA\JsonContent(
        *             required={"name"},
        *             @OA\Property(property="name", type="string", example="edit_users"),
        *             @OA\Property(property="label", type="string", example="Edit Users")
        *         )
        *     ),
        *     @OA\Response(
        *         response=201,
        *         description="Permission created",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Permission created"),
        *             @OA\Property(property="permission", type="object",
        *                 @OA\Property(property="id", type="integer", example=2),
        *                 @OA\Property(property="name", type="string", example="edit_users"),
        *                 @OA\Property(property="label", type="string", example="Edit Users")
        *             )
        *         )
        *     ),
        *     @OA\Response(response=400, description="Bad Request"),
        *     @OA\Response(response=401, description="Unauthorized")
        * )
        */
    public function store(Request $request) {
        $request->validate(['name'=>'required|unique:permissions,name','label'=>'nullable|string']);
        $p = Permission::create($request->only('name','label'));
        return response()->json(['message'=>'Permission created','permission'=>$p],201);
    }

    /**
     * @OA\Get(
     *     path="/api/permissions/{id}",
     *     summary="Get permission details",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Permission ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="view_users"),
     *             @OA\Property(property="label", type="string", example="View Users")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(Permission $permission) { return response()->json($permission); }

        /**
        * @OA\Put(
        *     path="/api/permissions/{id}",
        *     summary="Update a permission",
        *     tags={"Permissions"},
        *     security={{"sanctum":{}}},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,
        *         @OA\Schema(type="integer"),
        *         description="Permission ID"
        *     ),
        *     @OA\RequestBody(
        *         required=true,
        *         @OA\JsonContent(
        *             required={"name"},
        *             @OA\Property(property="name", type="string", example="edit_users"),
        *             @OA\Property(property="label", type="string", example="Edit Users")
        *         )
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Permission updated",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Permission updated"),
        *             @OA\Property(property="permission", type="object",
        *                 @OA\Property(property="id", type="integer", example=2),
        *                 @OA\Property(property="name", type="string", example="edit_users"),
        *                 @OA\Property(property="label", type="string", example="Edit Users")
        *             )
        *         )
        *     ),
        *     @OA\Response(response=400, description="Bad Request"),
        *     @OA\Response(response=401, description="Unauthorized"),
        *     @OA\Response(response=404, description="Not Found")
        * )
        */
    public function update(Request $request, Permission $permission) {
        $request->validate(['name'=>'required|unique:permissions,name,'.$permission->id,'label'=>'nullable|string']);
        $permission->update($request->only('name','label'));
        return response()->json(['message'=>'Permission updated','permission'=>$permission]);
    }

    /**
     * @OA\Delete(
     *     path="/api/permissions/{id}",
     *     summary="Delete a permission",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Permission ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permission deleted")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(Permission $permission) { $permission->delete(); return response()->json(['message'=>'Permission deleted']); }
}
