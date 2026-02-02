<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyControllerApi extends ApiController
{
    public function getStartData(Request $request): JsonResponse
    {
        return $this->apiResponse(function () {
            return [
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
            ];
        });
    }

    public function suggestions(Request $request): JsonResponse
    {
        return $this->apiResponse(function () use ($request) {
            $query = trim($request->input('q', ''));

            if ($query === '' || strlen($query) > 100) {
                return [];
            }

            return Property::where('name', 'LIKE', "%{$query}%")
                ->distinct()
                ->limit(10)
                ->pluck('name')
                ->toArray();
        });
    }

    public function search(Request $request): JsonResponse
    {
        return $this->apiResponse(function () use ($request) {
            $query = Property::query();

            if ($request->filled('name')) {
                $name = $request->input('name');
                if (is_string($name) && strlen($name) <= 255) {
                    $query->where('name', 'LIKE', $name . '%');
                }
            }

            // Фильтры (приводим к числу безопасно)
            if ($request->filled('price_min')) {
                $query->where('price', '>=', (float)$request->input('price_min'));
            }
            if ($request->filled('price_max')) {
                $query->where('price', '<=', (float)$request->input('price_max'));
            }

            $numericFields = ['bedrooms', 'bathrooms', 'storeys', 'garages'];
            foreach ($numericFields as $field) {
                if ($request->filled("{$field}_min")) {
                    $query->where($field, '>=', (int)$request->input("{$field}_min"));
                }
                if ($request->filled("{$field}_max")) {
                    $query->where($field, '<=', (int)$request->input("{$field}_max"));
                }
            }

            // whitelist
            $allowedSort = ['price', 'bedrooms', 'bathrooms', 'storeys', 'garages', 'name', 'id'];
            $sort = $request->input('sort');
            $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            if ($sort && in_array($sort, $allowedSort, true)) {
                $query->orderBy($sort, $dir);
            } else {
                $query->orderBy('id', 'asc');
            }

            // Pagination
            $offset = max(0, (int)$request->input('offset', 0));
            $limit = (int)$request->input('limit', 12);
            $limit = ($limit > 0 && $limit <= 100) ? $limit : 12;

            $total = $query->count();
            $items = $query->skip($offset)->take($limit)->get();

            return [
                'total'   => $total,
                'items'   => $items,
                'hasMore' => ($offset + $limit) < $total,
            ];
        });
    }
}
