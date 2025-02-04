<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::select('id','name')
                ->when($request->type, function ($query) use ($request){
                    $query->where('type',$request->type);
                })
                ->get();

        $extraCategory = [
            'id' => 0,
            'name' => $request->type == 'candidates'
                ? 'What area are you interested in?'
                : 'What type of contract are you interested in?'
        ];

        $categories->push($extraCategory);

        // Sort categories by ID
        $sortedCategories = $categories->sortBy('id')->values();

        return success('Fetched Categories Successfully',$sortedCategories);
    }
}
