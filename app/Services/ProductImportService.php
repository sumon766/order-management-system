<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductImportService
{
    public function importFromCsv(User $user, string $filePath): array
    {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        $results = ['success' => 0, 'errors' => []];

        while (($row = fgetcsv($file)) !== FALSE) {
            $data = array_combine($headers, $row);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|unique:products,sku',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'low_stock_threshold' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                $results['errors'][] = "Row error: " . implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                DB::transaction(function () use ($user, $data, &$results) {
                    Product::create([
                        'user_id' => $user->id,
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'sku' => $data['sku'],
                        'price' => $data['price'],
                        'stock_quantity' => $data['stock_quantity'],
                        'low_stock_threshold' => $data['low_stock_threshold'],
                        'is_active' => true,
                    ]);
                    $results['success']++;
                });
            } catch (\Exception $e) {
                $results['errors'][] = "Failed to import: " . $e->getMessage();
            }
        }

        fclose($file);
        return $results;
    }
}
