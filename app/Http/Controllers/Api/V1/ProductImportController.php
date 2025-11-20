<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Product Import",
 *     description="Product Bulk Import Endpoints"
 * )
 */
class ProductImportController extends Controller
{
    public function __construct(private ProductImportService $importService) {}

    /**
     * @OA\Post(
     *     path="/api/v1/products/import",
     *     summary="Import products from CSV file",
     *     tags={"Product Import"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"csv_file"},
     *                 @OA\Property(
     *                     property="csv_file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV file containing product data"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products imported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Import completed"),
     *             @OA\Property(
     *                 property="results",
     *                 type="object",
     *                 @OA\Property(property="success", type="integer", example=5),
     *                 @OA\Property(
     *                     property="errors",
     *                     type="array",
     *                     @OA\Items(type="string", example="Row error: The sku field must be unique.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Vendor or Admin role required"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or import failed"
     *     )
     * )
     */
    public function import(Request $request): JsonResponse
    {
        if (auth()->user()->isCustomer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $filePath = $request->file('csv_file')->store('csv_imports');
            $results = $this->importService->importFromCsv(auth()->user(), storage_path('app/' . $filePath));

            Storage::delete($filePath);

            return response()->json([
                'message' => 'Import completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 422);
        }
    }
}
