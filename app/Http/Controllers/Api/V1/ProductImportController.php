<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    public function __construct(private ProductImportService $importService) {}

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
