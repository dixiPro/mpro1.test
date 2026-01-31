<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyControllerApi extends Controller
{
    /**
     * Get initial data for the search form
     */
    public function getStartData(): JsonResponse
    {
        return response()->json([
            'total' => Property::count(),
            'price' => [
                'min' => Property::min('price') ?? 0,
                'max' => Property::max('price') ?? 0,
            ],
            'bedrooms' => [
                'min' => Property::min('bedrooms') ?? 0,
                'max' => Property::max('bedrooms') ?? 0,
            ],
            'bathrooms' => [
                'min' => Property::min('bathrooms') ?? 0,
                'max' => Property::max('bathrooms') ?? 0,
            ],
            'storeys' => [
                'min' => Property::min('storeys') ?? 0,
                'max' => Property::max('storeys') ?? 0,
            ],
            'garages' => [
                'min' => Property::min('garages') ?? 0,
                'max' => Property::max('garages') ?? 0,
            ],
        ]);
    }

    /**
     * Get autocomplete suggestions for property name
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json([]);
        }

        $suggestions = Property::where('name', 'LIKE', '%' . $query . '%')
            ->distinct()
            ->limit(10)
            ->pluck('name');

        return response()->json($suggestions);
    }

    /**
     * Property search
     * sort=price&dir=asc|desc
     */
    public function search(Request $request): JsonResponse
    {
        $query = Property::query();

        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', $request->input('name') . '%');
        }

        // Filter by price
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        // Filter by bedrooms
        if ($request->filled('bedrooms_min')) {
            $query->where('bedrooms', '>=', $request->input('bedrooms_min'));
        }
        if ($request->filled('bedrooms_max')) {
            $query->where('bedrooms', '<=', $request->input('bedrooms_max'));
        }

        // Filter by bathrooms
        if ($request->filled('bathrooms_min')) {
            $query->where('bathrooms', '>=', $request->input('bathrooms_min'));
        }
        if ($request->filled('bathrooms_max')) {
            $query->where('bathrooms', '<=', $request->input('bathrooms_max'));
        }

        // Filter by storeys
        if ($request->filled('storeys_min')) {
            $query->where('storeys', '>=', $request->input('storeys_min'));
        }
        if ($request->filled('storeys_max')) {
            $query->where('storeys', '<=', $request->input('storeys_max'));
        }

        // Filter by garages
        if ($request->filled('garages_min')) {
            $query->where('garages', '>=', $request->input('garages_min'));
        }
        if ($request->filled('garages_max')) {
            $query->where('garages', '<=', $request->input('garages_max'));
        }

        // ---- Sorting (whitelist) ----
        $allowedSort = ['price', 'bedrooms', 'bathrooms', 'storeys', 'garages', 'name', 'id'];
        $sort = $request->input('sort'); // e.g. "price"
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort && in_array($sort, $allowedSort, true)) {
            $query->orderBy($sort, $dir);
        } else {
            // Stable default (prevents pagination from "jumping")
            $query->orderBy('id', 'asc');
        }

        // Pagination via offset
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = (int) $request->input('limit', 12);
        $limit = ($limit > 0 && $limit <= 100) ? $limit : 12;

        $total = $query->count();
        $items = $query->skip($offset)->take($limit)->get();

        return response()->json([
            'total' => $total,
            'items' => $items,
            'hasMore' => ($offset + $limit) < $total,
        ]);
    }
}
