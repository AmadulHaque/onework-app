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
        if ($request->type == 'candidates') {
            $categories->push([
                'id' => 0,
                'name' => 'What area are you interested in? '
            ]);
        }else{
            $categories->push([
                'id' => 0,
                'name' => 'What type of contract are you interested in? '
            ]);
        }
        return success('Fetched Categories Successfully',$categories);
    }
}
