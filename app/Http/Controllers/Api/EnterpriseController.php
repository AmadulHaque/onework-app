<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnterpriseController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }

        $data = $validator->validate();
        Enterprise::create($data);

        return success('Enterprise created successfully.');

    }
}
