<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function search(Request $request)
    {
        $query = Property::query();

        $query->when($request->filled('name'), function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->name . '%');
        });

        $rangeFields = ['price', 'bedrooms', 'bathrooms', 'storeys', 'garages'];

        foreach ($rangeFields as $field) {
            $query->when($request->filled("{$field}_from"), function ($q) use ($request, $field) {
                $q->where($field, '>=', $request->input("{$field}_from"));
            });

            $query->when($request->filled("{$field}_to"), function ($q) use ($request, $field) {
                $q->where($field, '<=', $request->input("{$field}_to"));
            });
        }

        $properties = $query->paginate(10)->withQueryString();

        return view('form', compact('properties'));
    }
}
