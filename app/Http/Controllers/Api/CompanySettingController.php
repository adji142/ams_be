<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Validator;

class CompanySettingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/company-settings",
     *     summary="Get company settings",
     *     tags={"Company Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanySetting")
     *     )
     * )
     */
    public function index()
    {
        $settings = CompanySetting::get();
        return response()->json($settings);
    }

    /**
     * @OA\Post(
     *     path="/api/company-settings",
     *     summary="Create or update company settings",
     *     tags={"Company Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/CompanySetting"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings saved",
     *         @OA\JsonContent(ref="#/components/schemas/CompanySetting")
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NamaPerusahaan' => 'nullable|string|max:255',
            'Alamat1' => 'nullable|string|max:255',
            'Alamat2' => 'nullable|string|max:255',
            'Email' => 'nullable|email|max:255',
            'NoTlp' => 'nullable|string|max:50',
            'Icon' => 'nullable|string', // Validasi untuk base64 string
            'LabelWidth' => 'nullable|numeric',
            'LabelHeight' => 'nullable|numeric',
            'H1Size' => 'nullable|numeric',
            'H2Size' => 'nullable|numeric',
            'PSize' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $settings = CompanySetting::updateOrCreate(['id' => 1], $request->all());

        return response()->json(['message' => 'Company settings saved successfully.', 'settings' => $settings]);
    }
}
