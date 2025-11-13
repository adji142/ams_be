<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;

/**
 * @OA\Tag(
 *     name="Menu",
 *     description="Menu management"
 * )
 */
class MenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/menus",
     *     summary="Get menu structure",
     *     tags={"Menu"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Dashboard"),
     *             @OA\Property(property="url", type="string", example="/dashboard"),
     *             @OA\Property(property="icon", type="string", example="dashboard-icon"),
     *             @OA\Property(property="children", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Submenu"),
     *                 @OA\Property(property="url", type="string", example="/dashboard/submenu"),
     *                 @OA\Property(property="icon", type="string", example="submenu-icon")
     *             ))
     *         ))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userPermissions = $user->allPermissions()->pluck('name')->toArray();

        $menus = Menu::with(['children.permission'])
            ->whereNull('parent_id')
            ->orderBy('order','desc')
            ->get()
            ->filter(function ($menu) use ($userPermissions) {
                // var_dump($menu->name . ' ' . in_array($menu->permission->name, $userPermissions) . ' '. json_encode($userPermissions));
                return !$menu->permission_id || in_array($menu->permission->name, $userPermissions);
            })
            ->map(function ($menu) use ($userPermissions) {
                // Sort anak-anak berdasarkan order numerik
                $filteredChildren  = $menu->children = $menu->children
                    ->filter(function ($child) use ($userPermissions) {
                        // var_dump('Checking child menu: ' . $child->name . ' with permission_id: ' . $child->permission_id. ' and permission name: ' . ($child->permission ? $child->permission->name : 'N/A'));
                        return !$child->permission_id || in_array($child->permission->name, $userPermissions);
                    })
                    ->sortBy(function ($child) {
                        return (float) $child->order; // pastikan numerik
                    })
                    ->values();

                // Konversi order ke float supaya tampil rapi
                // var_dump(json_encode($filteredChildren));
                $menu->order = (float) $menu->order;
                $menu->children->transform(function ($child) {
                    $child->order = (float) $child->order;
                    return $child;
                });

                $menu->setRelation('children', $filteredChildren);
                return $menu;
            })
            ->sortBy(function ($menu) {
                return (float) $menu->order; // urut parent numerik juga
            })
            ->values();

        return response()->json($menus);
    }




    /**
     * @OA\Post(
     *     path="/api/menus",
     *     summary="Create menu",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Dashboard"),
     *             @OA\Property(property="url", type="string", example="/dashboard"),
     *             @OA\Property(property="icon", type="string", example="fas fa-home"),
     *             @OA\Property(property="permission_id", type="integer", example=1),
     *             @OA\Property(property="parent_id", type="integer", example=null),
     *             @OA\Property(property="order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Menu created", @OA\JsonContent(ref="#/components/schemas/Menu")),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'url'           => 'nullable|string|max:255',
            'icon'          => 'nullable|string|max:255',
            'permission_id' => 'nullable|integer|exists:permissions,id',
            'parent_id'     => 'nullable|integer|exists:menus,id',
            'order'         => 'nullable|integer',
        ]);

        $menu = Menu::create($data);
        return response()->json($menu->load('children'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/menus/{id}",
     *     summary="Get menu detail",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/Menu")),
     *     @OA\Response(response=404, description="Menu not found")
     * )
     */
    public function show(Menu $menu)
    {
        return $menu->load('children');
    }

    /**
     * @OA\Put(
     *     path="/api/menus/{id}",
     *     summary="Update menu",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Menu")
     *     ),
     *     @OA\Response(response=200, description="Menu updated", @OA\JsonContent(ref="#/components/schemas/Menu")),
     *     @OA\Response(response=404, description="Menu not found")
     * )
     */
    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'url'           => 'nullable|string|max:255',
            'icon'          => 'nullable|string|max:255',
            'permission_id' => 'nullable|integer|exists:permissions,id',
            'parent_id'     => 'nullable|integer|exists:menus,id',
            'order'         => 'nullable|integer',
        ]);

        $menu->update($data);
        return response()->json($menu->load('children'));
    }

    /**
     * @OA\Delete(
     *     path="/api/menus/{id}",
     *     summary="Delete menu",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Menu deleted"),
     *     @OA\Response(response=404, description="Menu not found")
     * )
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
