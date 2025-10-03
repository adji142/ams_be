<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Role management"
 * )
 */
class RoleController extends Controller
{
        /**
        * @OA\Get(
        *     path="/api/roles",
        *     summary="Get list of roles",
        *     tags={"Roles"},
        *     security={{"sanctum":{}}},
        *     @OA\Response(
        *         response=200,
        *         description="Successful operation",
        *         @OA\JsonContent(type="array", @OA\Items(
        *             @OA\Property(property="id", type="integer", example=1),
        *             @OA\Property(property="name", type="string", example="admin"),
        *             @OA\Property(property="label", type="string", example="Administrator"),
        *             @OA\Property(property="permissions", type="array", @OA\Items(
        *                 @OA\Property(property="id", type="integer", example=1),
        *                 @OA\Property(property="name", type="string", example="view_users"),
        *                 @OA\Property(property="label", type="string", example="View Users")
        *             ))
        *         ))
        *     ),
        *     @OA\Response(response=401, description="Unauthorized")
        * )
        */
    public function index() { return response()->json(Role::with('permissions')->get()); }

     /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new role",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="editor"),
     *             @OA\Property(property="label", type="string", example="Editor"),
     *             @OA\Property(property="permission_ids", type="array", @OA\Items(type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role created"),
     *             @OA\Property(property="role", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="editor"),
     *                 @OA\Property(property="label", type="string", example="Editor")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */ 

    public function store(Request $request) {
        $request->validate([
            'name'=>'required|unique:roles,name',
            'label'=>'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);
        $role = Role::create($request->only('name','label'));

        if ($request->has('permission_ids')) {
            $role->permissions()->attach($request->permission_ids);
        }

        return response()->json(['message'=>'Role created','role'=>$role->load('permissions')],201);
    }

        /**
        * @OA\Get(
        *     path="/api/roles/{id}",
        *     summary="Get role details",
        *     tags={"Roles"},
        *     security={{"sanctum":{}}},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,
        *         @OA\Schema(type="integer"),
        *         description="Role ID"
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Successful operation",
        *         @OA\JsonContent(
        *             @OA\Property(property="id", type="integer", example=1),
        *             @OA\Property(property="name", type="string", example="admin"),
        *             @OA\Property(property="label", type="string", example="Administrator"),
        *             @OA\Property(property="permissions", type="array", @OA\Items(
        *                 @OA\Property(property="id", type="integer", example=1),
        *                 @OA\Property(property="name", type="string", example="view_users"),
        *                 @OA\Property(property="label", type="string", example="View Users")
        *             ))
        *         )
        *     ),
        *     @OA\Response(response=401, description="Unauthorized"),
        *     @OA\Response(response=404, description="Role not found")
        * )
        */

    public function show(Role $role) { return response()->json($role->load('permissions')); }

        /**
        * @OA\Put(
        *     path="/api/roles/{id}",
        *     summary="Update a role",
        *     tags={"Roles"},
        *     security={{"sanctum":{}}},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,
        *         @OA\Schema(type="integer"),
        *         description="Role ID"
        *     ),
        *     @OA\RequestBody(
        *         required=true,
        *         @OA\JsonContent(
        *             required={"name"},
        *             @OA\Property(property="name", type="string", example="editor"),
        *             @OA\Property(property="label", type="string", example="Editor"),
        *             @OA\Property(property="permission_ids", type="array", @OA\Items(type="integer", example=1))
        *         )
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Role updated",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Role updated"),
        *             @OA\Property(property="role", type="object",
        *                 @OA\Property(property="id", type="integer", example=2),
        *                 @OA\Property(property="name", type="string", example="editor"),
        *                 @OA\Property(property="label", type="string", example="Editor")
        *             )
        *         )
        *     ),
        *     @OA\Response(response=401, description="Unauthorized"),
        *     @OA\Response(response=404, description="Role not found")
        * )
        */
    public function update(Request $request, Role $role) {
        $request->validate([
            'name'=>'required|unique:roles,name,'.$role->id,
            'label'=>'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);
        $role->update($request->only('name','label'));

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        return response()->json(['message'=>'Role updated','role'=>$role->load('permissions')]);
    }

        /**
        * @OA\Delete(
        *     path="/api/roles/{id}",
        *     summary="Delete a role",
        *     tags={"Roles"},
        *     security={{"sanctum":{}}},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,
        *         @OA\Schema(type="integer"),
        *         description="Role ID"
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Role deleted",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Role deleted")
        *         )
        *     ),
        *     @OA\Response(response=401, description="Unauthorized"),
        *     @OA\Response(response=404, description="Role not found")
        * )
        */

    public function destroy(Role $role) { $role->delete(); return response()->json(['message'=>'Role deleted']); }

        /**
        * @OA\Post(
        *     path="/api/roles/{id}/permissions",
        *     summary="Attach permissions to a role",
        *     tags={"Roles"},
        *     security={{"sanctum":{}}},
        *     @OA\Parameter(
        *         name="id",
        *         in="path",
        *         required=true,
        *         @OA\Schema(type="integer"),
        *         description="Role ID"
        *     ),
        *     @OA\RequestBody(
        *         required=true,
        *         @OA\JsonContent(
        *             required={"permissions"},
        *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer", example=1))
        *         )
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Permissions updated",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Permissions updated"),
        *             @OA\Property(property="role", type="object",
        *                 @OA\Property(property="id", type="integer", example=1),
        *                 @OA\Property(property="name", type="string", example="admin"),
        *                 @OA\Property(property="label", type="string", example="Administrator"),
        *                 @OA\Property(property="permissions", type="array", @OA\Items(
        *                     @OA\Property(property="id", type="integer", example=1),
        *                     @OA\Property(property="name", type="string", example="view_users"),
        *                     @OA\Property(property="label", type="string", example="View Users")
        *                 ))
        *             )
        *         )
        *     ),
        *     @OA\Response(response=400, description="Bad Request"),
        *     @OA\Response(response=401, description="Unauthorized"),
        *     @OA\Response(response=404, description="Role not found")
        * )
        */

    public function attachPermissions(Request $request, Role $role) {
        $request->validate(['permissions'=>'required|array']);
        $role->permissions()->sync($request->permissions);
        return response()->json(['message'=>'Permissions updated','role'=>$role->load('permissions')]);
    }

    /**
     * @OA\Post(
     *     path="/api/roles/{id}/assign-permission",
     *     summary="Assign permissions to a role",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Role ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions assigned",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions assigned"),
     *             @OA\Property(property="role", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="admin"),
     *                 @OA\Property(property="label", type="string", example="Administrator"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="view_users"),
     *                     @OA\Property(property="label", type="string", example="View Users")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Role not found")
     * )
     */
    public function assignPermission(Request $request, Role $role) {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions);

        return response()->json([
            'message' => 'Permissions assigned successfully',
            'role' => $role->load('permissions'),
        ]);
    }
}
