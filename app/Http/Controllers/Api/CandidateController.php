<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:45',
            'first_name'    => 'required|string|max:45',
            'email' => 'required|email|max:255',
            'phone'         => 'required|string|max:50',
            'category_id'   => 'required|exists:categories,id',
            'description'   => 'required|string',
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:8192', // 8MB
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }

        try {

            $data = $validator->validate();
            if ($data['cv_file']) {
                $cvPath = $request->file('cv_file')->store('cvs', 'public');
                $data['cv_file'] = $cvPath;
            }
            Candidate::create($data);
            return success('Application created successfully.');

        } catch (\Exception $e) {
            return failure($e->getMessage());
        }


    }
}
