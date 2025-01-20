<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:45',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'message'=> 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }
        try{
            $data = $validator->validate();
            Contact::create($data);
            return success('Contact created successfully.');
        } catch (\Exception $e){
            return failure($e->getMessage());
        }
    }
}
