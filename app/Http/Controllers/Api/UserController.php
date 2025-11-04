<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Users (API)",
 *     description="API Endpoints for managing Users with Roles and Permissions"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/users",
     *      operationId="getApiUsersList",
     *      tags={"Users (API)"},
     *      summary="Get paginated list of users",
     *      description="Returns a paginated list of users including their roles and permissions.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John Doe"),
      *                  @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
      *                  @OA\Property(property="KaryawanID", type="integer", example=1, nullable=true),
      *                  @OA\Property(property="UseForMobile", type="boolean", example=false),
      *                  @OA\Property(property="employee", type="object", nullable=true,
      *                      @OA\Property(property="id", type="integer", example=1),
      *                      @OA\Property(property="nama", type="string", example="Nama Karyawan"),
      *                      @OA\Property(property="nik", type="string", example="12345"),
      *                      @OA\Property(property="department_id", type="integer", example=1)
      *                  ),     *                  @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time"),
     *                  @OA\Property(property="roles", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string"))),
     *                  @OA\Property(property="permissions", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *              )),
     *              @OA\Property(property="first_page_url", type="string", example="http://localhost/api/users?page=1"),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="last_page", type="integer", example=1),
     *              @OA\Property(property="last_page_url", type="string", example="http://localhost/api/users?page=1"),
     *              @OA\Property(property="next_page_url", type="string", example=null),
     *              @OA\Property(property="path", type="string", example="http://localhost/api/users"),
     *              @OA\Property(property="per_page", type="integer", example=15),
     *              @OA\Property(property="prev_page_url", type="string", example=null),
     *              @OA\Property(property="to", type="integer", example=10),
     *              @OA\Property(property="total", type="integer", example=10)
     *          )
     *      )
     * )
     */
    public function index() { return User::with(['roles','permissions', 'employee'])->get(); }

    /**
     * @OA\Get(
     *      path="/api/users/{id}",
     *      operationId="getApiUserById",
     *      tags={"Users (API)"},
     *      summary="Get a single user's information",
     *      description="Returns a single user's data including their roles and permissions.",
     *      @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="John Doe"),
      *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
      *              @OA\Property(property="KaryawanID", type="integer", example=1, nullable=true),
      *              @OA\Property(property="UseForMobile", type="boolean", example=false),
      *              @OA\Property(property="employee", type="object", nullable=true,
      *                  @OA\Property(property="id", type="integer", example=1),
      *                  @OA\Property(property="nama", type="string", example="Nama Karyawan"),
      *                  @OA\Property(property="nik", type="string", example="12345"),
      *                  @OA\Property(property="department_id", type="integer", example=1)
      *              ),     *              @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *              @OA\Property(property="created_at", type="string", format="date-time"),
     *              @OA\Property(property="updated_at", type="string", format="date-time"),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string"))),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *          )
     *      ),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function show(User $user) { return $user->load(['roles','permissions', 'employee']); }

    /**
     * @OA\Post(
     *      path="/api/users",
     *      operationId="storeApiUser",
     *      tags={"Users (API)"},
     *      summary="Create a new user",
     *      description="Creates a new user and assigns roles and permissions.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *          @OA\JsonContent(
     *              required={"name", "email", "password"},
     *              @OA\Property(property="name", type="string", example="Api User"),
     *              @OA\Property(property="email", type="string", format="email", example="api.user@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password"),
     *              @OA\Property(property="KaryawanID", type="integer", example=1, nullable=true, description="ID of the related employee"),
     *              @OA\Property(property="UseForMobile", type="boolean", example=false, description="Flag for mobile usage"),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="integer", example=1), description="Array of Role IDs"),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="integer", example=1), description="Array of Permission IDs")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User created successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Api User"),
     *              @OA\Property(property="email", type="string", format="email", example="api.user@example.com"),
     *              @OA\Property(property="UseForMobile", type="boolean", example=false),
     *              @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *              @OA\Property(property="created_at", type="string", format="date-time"),
     *              @OA\Property(property="updated_at", type="string", format="date-time"),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string"))),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *          )
     *      ),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request) {
        $data = $request->validate([
            'name'=>'required|string','email'=>'required|email|unique:users','password'=>'required|string|min:6',
            'roles'=>'array','permissions'=>'array',
            'UseForMobile' => 'boolean',
            'KaryawanID' => 'nullable|integer|exists:employees,id'
        ]);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        if(!empty($request->roles)) $user->roles()->sync($request->roles);
        if(!empty($request->permissions)) $user->permissions()->sync($request->permissions);
        return response()->json($user->load(['roles','permissions']),201);
    }

    /**
     * @OA\Put(
     *      path="/api/users/{id}",
     *      operationId="updateApiUser",
     *      tags={"Users (API)"},
     *      summary="Update an existing user",
     *      description="Updates an existing user's data, roles, and permissions.",
     *      @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Api User Updated"),
     *              @OA\Property(property="email", type="string", format="email", example="api.user.updated@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="newpassword"),
     *              @OA\Property(property="KaryawanID", type="integer", example=1, nullable=true, description="ID of the related employee"),
     *              @OA\Property(property="UseForMobile", type="boolean", example=true, description="Flag for mobile usage"),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="integer", example=2), description="Array of Role IDs"),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="integer", example=3), description="Array of Permission IDs")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User updated successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Api User Updated"),
     *              @OA\Property(property="email", type="string", format="email", example="api.user.updated@example.com"),
     *              @OA\Property(property="UseForMobile", type="boolean", example=true),
     *              @OA\Property(property="email_verified_at", type="string", format="date-time"),
     *              @OA\Property(property="created_at", type="string", format="date-time"),
     *              @OA\Property(property="updated_at", type="string", format="date-time"),
     *              @OA\Property(property="roles", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string"))),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *          )
     *      ),
     *      @OA\Response(response=404, description="User not found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, User $user) {
        $data = $request->validate([
            'name'=>'sometimes|string','email'=>"sometimes|email|unique:users,email,{$user->id}",'password'=>'nullable|string|min:6',
            'roles'=>'array','permissions'=>'array',
            'UseForMobile' => 'sometimes|boolean',
            'KaryawanID' => 'nullable|integer|exists:employees,id'
        ]);
        if(!empty($data['password'])) $data['password'] = bcrypt($data['password']); else unset($data['password']);
        $user->update($data);
        if($request->has('roles')) $user->roles()->sync($request->roles ?? []);
        if($request->has('permissions')) $user->permissions()->sync($request->permissions ?? []);
        return response()->json($user->load(['roles','permissions']));
    }

    /**
     * @OA\Delete(
     *      path="/api/users/{id}",
     *      operationId="deleteApiUser",
     *      tags={"Users (API)"},
     *      summary="Delete a user",
     *      description="Deletes a specific user from the database.",
     *      @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="User deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Deleted")
     *          )
     *      ),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy(User $user) {
        $user->delete();
        return response()->json(['message'=>'Deleted']);
    }
}