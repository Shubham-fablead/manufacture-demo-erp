<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Purchases;
use App\Models\TaxRate;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS1D;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function normalizeBranchId($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || in_array(strtolower($value), ['null', 'undefined'], true)) {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    public function getAllProduct(Request $request)
    {
        $user = Auth::guard('api')->user();
        $requestedBranchId = $this->normalizeBranchId($request->sub_branch_id);

        $branchId = match (strtolower($user->role)) {
            'admin'     => $requestedBranchId ?? $user->id,
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            default     => $user->id,
        };

        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        $search = trim((string) $request->input('search', ''));
        $shouldPaginate = $request->has('page') || $request->has('per_page') || $request->filled('search');

        $query = Product::query()
            ->select([
                'id',
                'name',
                'SKU',
                'price',
                'quantity',
                'unit_id',
                'category_id',
                'brand_id',
                'branch_id',
                'images',
            ])
            ->with([
                'category:id,name',
                'brand:id,name',
                'unit:id,unit_name',
            ])
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->unit_id, fn($q) => $q->where('unit_id', $request->unit_id))
            ->when($request->brand_id, fn($q) => $q->where('brand_id', $request->brand_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('SKU', 'LIKE', "%{$search}%")
                        ->orWhere('barcode', 'LIKE', "%{$search}%");
                });
            });

        $pagination = null;

        if ($shouldPaginate) {
            $paginatedProducts = $query
                ->latest('id')
                ->paginate($perPage, ['*'], 'page', $page);

            $products = $paginatedProducts->items();

            $pagination = [
                'current_page' => $paginatedProducts->currentPage(),
                'last_page' => $paginatedProducts->lastPage(),
                'per_page' => $paginatedProducts->perPage(),
                'total' => $paginatedProducts->total(),
                'next_page_url' => $paginatedProducts->nextPageUrl(),
                'prev_page_url' => $paginatedProducts->previousPageUrl(),
            ];
        } else {
            $products = $query
                ->latest('id')
                ->get();
        }

        $settingsQuery = DB::table('settings')
            ->select('low_stock', 'currency_symbol', 'currency_position');

        if ($branchId) {
            $settingsQuery->where('branch_id', $branchId);
        }

        $settings = $settingsQuery->first();

        if (! $settings) {
            $settings = DB::table('settings')
                ->select('low_stock', 'currency_symbol', 'currency_position')
                ->first();
        }

        return response()->json([
            'status'            => true,
            'data'              => $products,
            'pagination'        => $pagination,
            'currencySymbol'    => $settings->currency_symbol ?? '₹',
            'currencyPosition'  => $settings->currency_position ?? 'left',
            'lowStockThreshold' => $settings->low_stock ?? 0,
        ]);
    }

    public function export_product(Request $request)
    {
        $user = Auth::guard('api')->user();
        $requestedBranchId = $this->normalizeBranchId($request->selectedSubAdminId);

        /* -------------------------------------------------
     | 1️⃣ Resolve Branch ID (single, clean logic)
     -------------------------------------------------*/
        $branchId = match ($user->role) {
            'staff' => $user->branch_id,
            'admin' => $requestedBranchId ?? $user->id,
            default => $user->id,
        };

        /* -------------------------------------------------
     | 2️⃣ Fetch Products
     -------------------------------------------------*/
        $products = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('units','products.unit_id', '=', 'units.id')
            ->select([
                'products.name as product_name',
                'products.SKU',
                'products.barcode',
                'products.price',
                'products.quantity',
                'products.description',
                'products.created_at',
                'categories.name as category_name',
                'brands.name as brand_name',
                'units.unit_name'
            ])
            ->where('products.isDeleted', 0)
            ->where('products.branch_id', $branchId)
            ->when(
                $request->category_id,
                fn($q) =>
                $q->where('products.category_id', $request->category_id)
            )
            ->when(
                $request->brand_id,
                fn($q) =>
                $q->where('products.brand_id', $request->brand_id)
            )
            ->when(
                $request->unit_id,
                fn($q) =>
                $q->where('products.unit_id', $request->unit_id)
            )
            ->latest('products.id')
            ->get();

        /* -------------------------------------------------
     | 3️⃣ Currency Settings
     -------------------------------------------------*/
        $settings = DB::table('settings')
            ->where('branch_id', $branchId)
            ->first();

        $currencySymbol = trim(
            html_entity_decode($settings->currency_symbol ?? '₹', ENT_QUOTES | ENT_HTML5, 'UTF-8')
        );
        $currencyPosition = $settings->currency_position ?? 'left';

            // Helper function for Indian number formatting
    $formatIndianCurrency = function($amount) {
        if ($amount === null || $amount === '-') return '-';

        $amount = (float)$amount;
        $amount = number_format($amount, 2, '.', '');

        // Split into whole and decimal parts
        $parts = explode('.', $amount);
        $whole = $parts[0];
        $decimal = $parts[1];

        // Indian numbering system formatting
        $lastThree = substr($whole, -3);
        $otherNumbers = substr($whole, 0, -3);

        if ($otherNumbers != '') {
            $otherNumbers = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $otherNumbers);
            $formattedWhole = $otherNumbers . ',' . $lastThree;
        } else {
            $formattedWhole = $lastThree;
        }

        return $formattedWhole . '.' . $decimal;
    };


        /* -------------------------------------------------
     | 4️⃣ Create Excel
     -------------------------------------------------*/
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'A1' => 'Product Name',
            'B1' => 'SKU',
            'C1' => 'Barcode',
            'D1' => 'Category',
            'E1' => 'Brand',
            'F1' => 'Quantity',
            'G1' => 'Unit',
            'H1' => 'Price',
            'I1' => 'Description',
            'J1' => 'Created At',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

          // Set column widths for better readability
    $sheet->getColumnDimension('A')->setWidth(30); // Product Name
    $sheet->getColumnDimension('B')->setWidth(15); // SKU
    $sheet->getColumnDimension('C')->setWidth(15); // Barcode
    $sheet->getColumnDimension('D')->setWidth(15); // Category
    $sheet->getColumnDimension('E')->setWidth(15); // Brand
    $sheet->getColumnDimension('F')->setWidth(12); // Quantity
    $sheet->getColumnDimension('G')->setWidth(12); // Unit
    $sheet->getColumnDimension('H')->setWidth(18); // Price
    $sheet->getColumnDimension('I')->setWidth(30); // Description
    $sheet->getColumnDimension('J')->setWidth(15); // Created At

        // Data
        $row = 2;
        foreach ($products as $product) {
             // Format price with Indian number system
        $formattedPrice = $formatIndianCurrency($product->price);

              $price = $currencyPosition === 'left'
            ? "{$currencySymbol}{$formattedPrice}"
            : "{$formattedPrice}{$currencySymbol}";

            $sheet->fromArray([
                $product->product_name,
                $product->SKU,
                $product->barcode,
                $product->category_name ?? '-',
                $product->brand_name ?? '-',
                $product->quantity ?? '-',
                $product->unit_name ?? '-',
                $price,
                $product->description ?? '-',
                \Carbon\Carbon::parse($product->created_at)->format('Y-m-d'),
            ], null, "A{$row}");
             // Set the price column as text to preserve formatting
        $sheet->getStyle('H' . $row)->getNumberFormat()
              ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            $row++;
        }

        /* -------------------------------------------------
     | 5️⃣ Save File
     -------------------------------------------------*/
        $fileName     = 'Products_' . now()->format('Ymd_His') . '.xlsx';
        $folder       = 'product-exports';
        $relativePath = "{$folder}/{$fileName}";

        Storage::disk('public')->makeDirectory($folder);

        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path("app/public/{$relativePath}"));

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        /* -------------------------------------------------
     | 6️⃣ Response
     -------------------------------------------------*/
        return response()->json([
            'status'    => true,
            'message'   => 'Product Excel exported successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function export_product_pdf(Request $request)
    {
        $user = Auth::guard('api')->user();
        $requestedBranchId = $this->normalizeBranchId($request->selectedSubAdminId);

        /* -------------------------------------------------
     | 1️⃣ Resolve Branch ID (single source of truth)
     -------------------------------------------------*/
        $branchId = match ($user->role) {
            'staff' => $user->branch_id,
            'admin' => $requestedBranchId ?? $user->id,
            default => $user->id,
        };

        /* -------------------------------------------------
     | 2️⃣ Build Product Query
     -------------------------------------------------*/
        $products = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('units','products.unit_id', '=', 'units.id')
            ->select([
                'products.id',
                'products.name as product_name',
                'products.SKU',
                'products.barcode',
                'products.price',
                'products.quantity',
                'products.description',
                'products.created_at',
                'categories.name as category_name',
                'brands.name as brand_name',
                'units.unit_name'
            ])
            ->where('products.isDeleted', 0)
            ->where('products.branch_id', $branchId)
            ->when(
                $request->category_id,
                fn($q) =>
                $q->where('products.category_id', $request->category_id)
            )
            ->when(
                $request->brand_id,
                fn($q) =>
                $q->where('products.brand_id', $request->brand_id)
            )
            ->latest('products.id')
            ->get();
    // dd($products);
        /* -------------------------------------------------
     | 3️⃣ Settings (currency, GST, branding)
     -------------------------------------------------*/
        $settings = DB::table('settings')
            ->where('branch_id', $branchId)
            ->first();

        $currencySymbol = trim(
            html_entity_decode($settings->currency_symbol ?? '₹', ENT_QUOTES | ENT_HTML5, 'UTF-8')
        );

        /* -------------------------------------------------
     | 4️⃣ PDF Generation
     -------------------------------------------------*/
        $pdf = Pdf::loadView('product.product_pdf', [
            'products'         => $products,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $settings->currency_position ?? 'left',
            'settings'         => $settings,
            'userName'         => $user->name ?? 'N/A',
            'gstNum'           => $user->gst_number ?? 'N/A',
        ])->setPaper('A4', 'portrait');

        /* -------------------------------------------------
     | 5️⃣ Store PDF
     -------------------------------------------------*/
        $fileName     = 'Products_' . now()->format('Ymd_His') . '.pdf';
        $folder       = 'all-products';
        $relativePath = "{$folder}/{$fileName}";

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put($relativePath, $pdf->output());

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        /* -------------------------------------------------
     | 6️⃣ Response
     -------------------------------------------------*/
        return response()->json([
            'status'    => true,
            'message'   => 'Product PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function getProductById($id)
    {
        $product = Product::with(['category', 'brand'])->find($id);
        if ($product) {
            return response()->json(['status' => true, 'product' => $product], 200);
        } else {
            return response()->json(['status' => false, 'error' => 'Product not found'], 404);
        }
    }

    public function createProduct(Request $request)
    {
        /* -------------------------------------------------
     | 1️⃣ Validation
     -------------------------------------------------*/

        $user = Auth::guard('api')->user();

        $branchId = match (strtolower($user->role)) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $request->branch_id ?? $user->id,
            default     => $user->id,
        };

        // Optional override
        if (! empty($request->sub_admin_id) && strtolower($user->role) !== 'staff') {
            $branchId = $request->sub_admin_id;
        }

        $rules = [
            'vendor_id'     => 'nullable|numeric',
            'category_id'   => 'required|numeric',
            'brand_id'      => 'nullable|numeric',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|gt:0',
            'landing_cost'  => 'nullable|numeric|min:0',
            'cost_price'    => 'nullable|numeric|min:0',
            'SKU'           => 'nullable|string|max:255',
            'hsn_code'      => 'nullable',
            'barcode'       => 'nullable|string|max:255',
            'quantity'      => 'required|integer|gt:0',
            'unit_id'       => 'required|numeric',
            'status'        => 'nullable|in:active,inactive',
            'availablility' => 'nullable|in:in_stock,out_stock',
            'gst_option'    => 'nullable|in:with_gst,without_gst',
            'product_gst'   => 'nullable|array',
            'product_gst.*' => 'nullable|numeric|exists:taxes,id',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif,webp',
            'branch_id'     => 'nullable|numeric',
            'sub_admin_id'  => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules, [], [
            'category_id'   => 'category',
            'brand_id'      => 'brand',
            'SKU'           => 'SKU',
            'hsn_code'      => 'HSN Code',
            'availablility' => 'availability',
            'gst_option'    => 'GST option',
            'product_gst'   => 'GST rates',
            'unit_id'          => 'unit',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->gst_option === 'with_gst') {
                $productGst = $request->product_gst;
                if (empty($productGst) || !is_array($productGst) || count(array_filter($productGst)) === 0) {
                    $validator->errors()->add('product_gst', 'Please select at least one GST rate when "With GST" is selected.');
                }
            }
        });

        $validated = $validator->validate();

        /* -------------------------------------------------
     | 2️⃣ Upload Images
     -------------------------------------------------*/
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('img/product', 'public');
            }
        }

        /* -------------------------------------------------
     | 3️⃣ Resolve Branch ID (clean & safe)
     -------------------------------------------------*/
        $user = Auth::guard('api')->user();

        $branchId = match (strtolower($user->role)) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $validated['branch_id'] ?? $user->id,
            default     => $user->id,
        };

        // Optional override (admin / sub-admin only)
        if (! empty($validated['sub_admin_id']) && strtolower($user->role) !== 'staff') {
            $branchId = $validated['sub_admin_id'];
        }

        /* -------------------------------------------------
     | 4️⃣ Availability Logic
     -------------------------------------------------*/
        $availability = $validated['quantity'] == 0
            ? 'out_stock'
            : ($validated['availablility'] ?? 'in_stock');

        /* -------------------------------------------------
     | 5️⃣ Create Product
     -------------------------------------------------*/
        $gstData = [];
        if (!empty($validated['product_gst']) && is_array($validated['product_gst'])) {
            foreach ($validated['product_gst'] as $taxId) {
                $tax = TaxRate::find($taxId);
                if ($tax) {
                    $gstData[] = [
                        'tax_id'   => $taxId,
                        'tax_name' => $tax->tax_name,
                        'tax_rate' => $tax->tax_rate,
                    ];
                }
            }
        }

        $product = Product::create([
            'vendor_id'     => $validated['vendor_id'] ?? 1,
            'category_id'   => $validated['category_id'],
            'brand_id'      => $validated['brand_id'],
            'branch_id'     => $branchId,
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'price'         => $validated['price'],
            'landing_cost'  => $validated['landing_cost'] ?? null,
            'cost_price'    => $validated['cost_price'] ?? null,
            'SKU'           => $validated['SKU'],
            'hsn_code'      => $validated['hsn_code'],
            'barcode'       => $validated['barcode'] ?? null,
            'quantity'      => $validated['quantity'],
            'unit_id'       => $validated['unit_id'],
            'status'        => $validated['status'] ?? 'active',
            'availablility' => $availability,
            'gst_option'    => $validated['gst_option'] ?? 'without_gst',
            'product_gst'   => !empty($gstData) ? json_encode($gstData) : null,
            'images'        => json_encode($imagePaths),
        ]);

        /* -------------------------------------------------
     | 6️⃣ Inventory Log
     -------------------------------------------------*/
        ProductInventory::create([
            'product_id'    => $product->id,
            'initial_stock' => $validated['quantity'],
            'current_stock' => $validated['quantity'],
            'branch_id'     => $branchId,
            'create_by'     => $user->id,
            'type'          => 'Create',
            'date'          => now(),
        ]);

        /* -------------------------------------------------
     | 7️⃣ Response
     -------------------------------------------------*/
        return response()->json([
            'status'  => true,
            'message' => 'Product created successfully',
            'product' => $product->fresh(),
        ], 200);
    }

    public function updateProduct(Request $request)
    {
        /* -------------------------------------------------
     | 1️⃣ Validation
     -------------------------------------------------*/

        $user = Auth::guard('api')->user();

        $product = Product::findOrFail($request->product_id);

        $branchId = match (strtolower($user->role)) {
            'sub-admin' => $user->id,
            'admin'     => $request->branch_id ?? $product->branch_id,
            'staff'     => $product->branch_id,
            default     => $product->branch_id,
        };

        $rules = [
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'nullable|numeric',
            'category_id'   => 'required|numeric',
            'brand_id'      => 'nullable|numeric',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|gt:0',
            'landing_cost'  => 'nullable|numeric|min:0',
            'cost_price'    => 'nullable|numeric|min:0',
            'SKU'           => 'nullable|string|max:255',
            'hsn_code'      => 'nullable',
            'barcode'       => 'nullable|string|max:255',
            'quantity'      => 'required|integer|gt:0',
            'unit_id' => 'required|numeric',
            'status'        => 'nullable|in:active,inactive',
            'availablility' => 'nullable|in:in_stock,out_stock',
            'gst_option'    => 'nullable|in:with_gst,without_gst',
            'product_gst'   => 'nullable|array',
            'product_gst.*' => 'nullable|numeric|exists:taxes,id',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif,webp',
            'capacity'      => 'nullable|string',
            'voltage'       => 'nullable|string',
            'warranty'      => 'nullable|string',
            'expiry_date'   => 'nullable|date',
        ];

        $validator = Validator::make($request->all(), $rules, [], [
            'category_id'   => 'category',
            'brand_id'      => 'brand',
            'SKU'           => 'SKU',
            'hsn_code'      => 'HSN Code',
            'availablility' => 'availability',
            'gst_option'    => 'GST option',
            'product_gst'   => 'GST rates',
            'unit_id'       => 'unit',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->gst_option === 'with_gst') {
                $productGst = $request->product_gst;
                if (empty($productGst) || !is_array($productGst) || count(array_filter($productGst)) === 0) {
                    $validator->errors()->add('product_gst', 'Please select at least one GST rate when "With GST" is selected.');
                }
            }
        });

        $validated = $validator->validate();

        /* -------------------------------------------------
     | 2️⃣ Fetch Product
     -------------------------------------------------*/
        // $product = Product::findOrFail($validated['product_id']);

        /* -------------------------------------------------
     | 3️⃣ Handle Images
     -------------------------------------------------*/
        $existingImages = json_decode($product->images, true) ?? [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $existingImages[] = $image->store('img/product', 'public');
            }
        }

        /* -------------------------------------------------
     | 4️⃣ Resolve Branch ID
     -------------------------------------------------*/
        $user = Auth::guard('api')->user();

        $branchId = match ($user->role) {
            'sub-admin' => $user->id,
            'admin'     => $request->branch_id ?? $product->branch_id,
            default     => $product->branch_id,
        };

        /* -------------------------------------------------
     | 5️⃣ Availability Auto-Logic
     -------------------------------------------------*/
        $availability = $validated['quantity'] == 0
            ? 'out_stock'
            : ($validated['availablility'] ?? 'in_stock');

        /* -------------------------------------------------
     | 6️⃣ Prepare GST Data
     -------------------------------------------------*/
        $gstData = [];
        if (!empty($validated['product_gst']) && is_array($validated['product_gst'])) {
            foreach ($validated['product_gst'] as $taxId) {
                $tax = TaxRate::find($taxId);
                if ($tax) {
                    $gstData[] = [
                        'tax_id'   => $taxId,
                        'tax_name' => $tax->tax_name,
                        'tax_rate' => $tax->tax_rate,
                    ];
                }
            }
        }

        /* -------------------------------------------------
     | 7️⃣ Update Product
     -------------------------------------------------*/
        $product->update([
            'vendor_id'     => $validated['vendor_id'] ?? 1,
            'category_id'   => $validated['category_id'],
            'brand_id'      => $validated['brand_id'],
            'branch_id'     => $branchId,
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'price'         => $validated['price'],
            'landing_cost'  => $validated['landing_cost'] ?? null,
            'cost_price'    => $validated['cost_price'] ?? null,
            'SKU'           => $validated['SKU'],
            'hsn_code'      => $validated['hsn_code'],
            'barcode'       => $validated['barcode'] ?? null,
            'quantity'      => $validated['quantity'],
            'unit_id'       => $validated['unit_id'],
            'status'        => $validated['status'] ?? $product->status,
            'availablility' => $availability,
            'gst_option'    => $validated['gst_option'] ?? 'without_gst',
            'product_gst'   => !empty($gstData) ? json_encode($gstData) : null,
            'images'        => json_encode($existingImages),
            'capacity'      => $validated['capacity'] ?? null,
            'voltage'       => $validated['voltage'] ?? null,
            'warranty'      => $validated['warranty'] ?? null,
            'expiry_date'   => $validated['expiry_date'] ?? null,
        ]);

        /* -------------------------------------------------
     | 8️⃣ Inventory Log
     -------------------------------------------------*/
        ProductInventory::create([
            'product_id'    => $product->id,
            'initial_stock' => $validated['quantity'],
            'current_stock' => $validated['quantity'],
            'branch_id'     => $branchId,
            'create_by'     => $user->id,
            'type'          => 'Edit',
            'date'          => now(),
        ]);

        /* -------------------------------------------------
     | 9️⃣ Response
     -------------------------------------------------*/
        return response()->json([
            'status'  => true,
            'message' => 'Product updated successfully',
            'product' => $product->fresh(),
        ], 200);
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['status' => false, 'error' => 'Product not found'], 404);
        }

        $orderItemExists = OrderItem::where('product_id', $id)->where('isDeleted', 0)->exists();
        $purchaseExists  = Purchases::where('item', $id)->where('isDeleted', 0)->exists();

        if ($orderItemExists) {
            return response()->json([
                'status' => false,
                'error'  => 'Product is associated with existing orders and cannot be deleted.',
            ], 409);
        }

        if ($purchaseExists) {
            return response()->json([
                'status' => false,
                'error'  => 'Product is associated with existing purchases and cannot be deleted.',
            ], 409);
        }

        // Soft delete: mark isDeleted as 1
        $product->isDeleted = 1;
        $product->save();

        return response()->json(['status' => true, 'message' => 'Product deleted successfully'], 200);
    }

    public function removeProductImage(Request $request)
    {
        $product = Product::find($request->product_id);

        if (! $product) {
            return response()->json(["success" => false, "message" => "Product not found"]);
        }

        $images = json_decode($product->images, true);

        // Check if image exists
        if (($key = array_search($request->image, $images)) !== false) {
            // Remove image from storage
            Storage::delete("storage/img/product" . $request->image);

            // Remove image from array and update database
            unset($images[$key]);
            $product->images = json_encode(array_values($images)); // Re-index array
            $product->save();

            return response()->json(["success" => true, "message" => "Image deleted"]);
        }

        return response()->json(["success" => false, "message" => "Image not found"]);
    }

    public function importProducts(Request $request)
    {
        $user = Auth::guard('api')->user();
        $requestedBranchId = $this->normalizeBranchId($request->sub_admin_id);
        $branchId = match (strtolower($user->role ?? '')) {
            'sub-admin' => $user->id,
            'staff' => $user->branch_id,
            'admin' => $requestedBranchId ?? $user->id,
            default => $user->id,
        };

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $productAvailabilityColumn = Schema::hasColumn('products', 'availablility') ? 'availablility' : 'availability';
        $productCreatedByColumn = Schema::hasColumn('products', 'created_by') ? 'created_by' : 'create_by';
        $productBranchColumn = Schema::hasColumn('products', 'branch_id');
        $productVendorColumn = Schema::hasColumn('products', 'vendor_id');
        $productUnitColumn = Schema::hasColumn('products', 'unit_id');

        if (count($data) === 0) {
            return response()->json([
                'status' => false,
                'message' => 'CSV file is empty!',
            ]);
        }

        $header = array_shift($data);
        $normalizedHeader = array_map(function ($column) {
            $column = strtolower(trim((string) $column));
            $column = preg_replace('/[^a-z0-9]+/', '_', $column);
            return trim($column, '_');
        }, $header);

        $requiredColumns = [
            'product_name',
            'category_name',
            'sku',
            'quantity',
            'price',
            'status',
            'availability',
        ];

        $missingColumns = array_values(array_diff($requiredColumns, $normalizedHeader));

        if (! empty($missingColumns)) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required CSV columns: ' . implode(', ', $missingColumns),
            ], 422);
        }

        $columnIndex = array_flip($normalizedHeader);
        $getValue = static function (array $row, array $keys, $default = null) use ($columnIndex) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $columnIndex)) {
                    return $row[$columnIndex[$key]] ?? $default;
                }
            }

            return $default;
        };

        $insertedCount = 0;
        $updatedSKUs = [];
        $invalidSKUs = [];

        foreach ($data as $row) {
            if (count(array_filter($row, fn($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $name = trim((string) $getValue($row, ['product_name', 'name'], ''));
            $categoryName = strtolower(trim((string) $getValue($row, ['category_name', 'category'], '')));
            $brandName = strtolower(trim((string) $getValue($row, ['brand_name', 'brand'], '')));
            $sku = trim((string) $getValue($row, ['sku'], ''));
            $quantity = (int) $getValue($row, ['quantity'], 0);
            $unitName = strtolower(trim((string) $getValue($row, ['unit', 'unit_name'], '')));
            $price = trim((string) $getValue($row, ['price'], ''));
            $status = strtolower(trim((string) $getValue($row, ['status'], 'inactive')));
            $availability = strtolower(trim((string) $getValue($row, ['availability'], 'out_stock')));
            $description = trim((string) $getValue($row, ['description'], ''));

            if ($name === '' || $categoryName === '' || $sku === '' || $price === '') {
                $invalidSKUs[] = $sku !== '' ? $sku : 'missing-sku-row';
                continue;
            }

            if (! preg_match('/^\d+$/', $sku)) {
                $invalidSKUs[] = $sku;
                continue;
            }

            if (! is_numeric($price)) {
                $invalidSKUs[] = $sku;
                continue;
            }

            $status = in_array($status, ['active', 'inactive'], true) ? $status : 'inactive';
            $availability = str_replace([' ', '-'], '_', $availability);
            $availability = in_array($availability, ['in_stock', 'out_stock'], true) ? $availability : 'out_stock';

            $category = Category::firstOrCreate(
                ['name' => $categoryName, 'branch_id' => $branchId],
                ['isDeleted' => 0]
            );

            $brand = $brandName !== '' ? Brand::firstOrCreate(
                ['name' => $brandName, 'branch_id' => $branchId],
                ['isDeleted' => 0]
            ) : null;

            $unit = $unitName !== '' ? Unit::firstOrCreate(
                ['unit_name' => $unitName, 'created_by' => $branchId],
                ['is_delete' => 0]
            ) : null;

            $product = Product::where('SKU', $sku)
                ->where('branch_id', $branchId)
                ->first();

            if ($product) {
                $previousQuantity = (int) $product->quantity;
                $newQuantity = $previousQuantity + $quantity;

                $product->quantity = $newQuantity;
                $product->price = (float) $price;
                $product->status = $status;
                $product->{$productAvailabilityColumn} = $availability;
                $product->description = $description !== '' ? $description : $product->description;

                if ($unit && $productUnitColumn) {
                    $product->unit_id = $unit->id;
                }

                $product->save();

                ProductInventory::create([
                    'product_id'    => $product->id,
                    'initial_stock' => $previousQuantity,
                    'current_stock' => $newQuantity,
                    'branch_id'     => $branchId,
                    'create_by'     => $user->id,
                    'type'          => 'Stock Added',
                    'date'          => now(),
                ]);

                $updatedSKUs[] = $sku;
                continue;
            }

            $productData = [
                'category_id' => $category->id,
                'brand_id' => $brand?->id,
                'name' => $name,
                'SKU' => $sku,
                'description' => $description !== '' ? $description : null,
                'price' => (float) $price,
                'quantity' => $quantity,
                'isDeleted' => 0,
                'status' => $status,
                $productAvailabilityColumn => $availability,
                $productCreatedByColumn => $user->id,
            ];

            if ($productVendorColumn) {
                $productData['vendor_id'] = 1;
            }

            if ($productBranchColumn) {
                $productData['branch_id'] = $branchId;
            }

            if ($productUnitColumn) {
                $productData['unit_id'] = $unit?->id;
            }

            $product = Product::create($productData);

            ProductInventory::create([
                'product_id'    => $product->id,
                'initial_stock' => $quantity,
                'current_stock' => $quantity,
                'branch_id'     => $branchId,
                'create_by'     => $user->id,
                'type'          => 'Create',
                'date'          => now(),
            ]);

            $insertedCount++;
        }

        if ($insertedCount > 0 || count($updatedSKUs) > 0) {
            return response()->json([
                'status' => true,
                'message' => $insertedCount > 0
                    ? "$insertedCount product(s) imported successfully."
                    : 'Existing product(s) updated with additional quantity.',
                'updated_skus' => $updatedSKUs,
                'invalid_skus' => $invalidSKUs,
            ]);
        }

        if (! empty($invalidSKUs)) {
            return response()->json([
                'status' => false,
                'message' => 'No new products imported.',
                'invalid_skus' => $invalidSKUs,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'CSV file is empty or contains invalid data.',
        ]);
    }

    public function getProductDetails($id)
    {
        $product = Product::with(['category', 'brand', 'unit'])->findOrFail($id);

        // Fetch currency settings
        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $barcodeGenerator = new DNS1D();
        $barcodeHtml      = null;

        if (! empty($product->barcode)) {
            $barcodeHtml = $barcodeGenerator->getBarcodeHTML($product->barcode, 'C128');
        }

        return response()->json([
            'product'          => $product,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'barcode_html'     => $barcodeHtml,
        ]);
    }

    public function getQuantityHistory($productId)
    {
        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found.']);
        }

        $history = [];

        // Initial stock (e.g., when product added)
        $history[] = [
            'type'     => 'Added',
            'quantity' => $product->quantity,
            'note'     => 'Initial stock on product creation',
            'date'     => $product->created_at->format('d-M-Y h:i A'),
        ];

        // Sales data (reduce quantity)
        $orderItems = OrderItem::with('order')->where('product_id', $productId)->get();
        foreach ($orderItems as $item) {
            $orderNumber = $item->order ? $item->order->order_number : 'N/A';

            $history[] = [
                'type'     => 'Sold',
                'quantity' => $item->quantity,
                'note'     => 'Sold in order #' . $orderNumber,
                'date'     => $item->created_at->format('d-M-Y h:i A'),
            ];
        }

        return response()->json([
            'status'  => true,
            'product' => $product->name,
            'history' => $history,
        ]);
    }

    public function getCategory(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Agar request me sub_branch_id aaya hai to use karo, warna logged-in user ka branch_id lo
        $branchId = $request->sub_branch_id ?? $user->id;
        if ($user->role == 'staff') {
            $branchId = $user->branch_id;
        }
        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get();
        // dd($categories);

        return response()->json([
            'status' => true,
            'data'   => $categories,
        ], 200);
    }
      public function getProfitLossData(Request $request)
    {
        $user = Auth::guard('api')->user() ?? Auth::user();
        $branchId = $this->resolveBranchId();

        $productId = $request->get('product_id');
        $vendorId = $request->get('vendor_id');
        $customerId = $request->get('customer_id');
        $timePeriod = $request->get('time_period', 'all_time');
        $year = $request->get('year');
        $month = $request->get('month');

        [$startDate, $endDate] = $this->resolveProfitLossDateRange($timePeriod);

        return response()->json(
            $this->getProfitLossReportData($branchId, $productId, $vendorId, $customerId, $startDate, $endDate, $year, $month, $timePeriod, $user)
        );
    }


      public function profitLossPdf(Request $request)
    {
        $user = Auth::guard('api')->user() ?? Auth::user();
        $branchId = $this->resolveBranchId();

        $productId = $request->get('product_id');
        $vendorId = $request->get('vendor_id');
        $customerId = $request->get('customer_id');
        $timePeriod = $request->get('time_period', 'all_time');
        $year = $request->get('year');
        $month = $request->get('month');

        [$startDate, $endDate] = $this->resolveProfitLossDateRange($timePeriod);
        $reportData = $this->getProfitLossReportData($branchId, $productId, $vendorId, $customerId, $startDate, $endDate, $year, $month, $timePeriod, $user);
        $items = $reportData['pdf_items'];

        $settings = DB::table('settings')->where('branch_id', $branchId)->first();
        $selectedProduct = $productId ? Product::find($productId) : null;
        $selectedVendor = $vendorId ? User::find($vendorId) : null;
        $selectedCustomer = $customerId ? User::find($customerId) : null;

        $pdf = Pdf::loadView('reports.profit-loss-report-pdf', [
            'items' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'year' => $year,
            'month' => $month,
            'settings' => $settings,
            'selectedProduct' => $selectedProduct,
            'selectedVendor' => $selectedVendor,
            'selectedCustomer' => $selectedCustomer,
        ]);

        return $pdf->download('Profit_Loss_Report.pdf');
    }




    public function getBrand(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Branch ID decide karna (agar sub_branch_id aaya hai to use karo, otherwise user ka branch_id)
        $branchId = $request->sub_branch_id ?? $user->id;

        // Categories fetch
        if ($user->role == 'staff') {
            $branchId = $user->branch_id;
        }
        $Brands = Brand::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $Brands,
        ], 200);
    }

    public function getTaxRates(Request $request)
    {
        $user = Auth::guard('api')->user();

        $branchId = $request->sub_branch_id ?? $user->id;

        if ($user->role == 'staff') {
            $branchId = $user->branch_id;
        }

        $taxRates = TaxRate::where('isDeleted', 0)
            ->where('status', 'active')
            ->where('branch_id', $branchId)
            ->get(['id', 'tax_name', 'tax_rate']);

        return response()->json([
            'status' => true,
            'data'   => $taxRates,
        ], 200);
    }

    public function getUnits(Request $request)
    {
        $user = Auth::guard('api')->user();

        $branchId = $request->sub_branch_id ?? $user->id;

        if ($user->role == 'staff') {
            $branchId = $user->branch_id;
        }

        $units = Unit::where('is_delete', 0)
            ->where('created_by', $branchId)
            ->get(['id', 'unit_name']);

        return response()->json([
            'status' => true,
            'data'   => $units,
        ], 200);
    }

    public function edit_product(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // dd($product);
        // $units = Unit::find($product->unit_id);

        // dd($units);
        return response()->json([
            'status'  => true,
            'product' => $product,
            // 'units'    => $units,
        ]);
    }
}
