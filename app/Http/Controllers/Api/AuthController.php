<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return failure('Invalid email or password.', 401);
        }

        // Revoke all existing tokens for the user
        $user->tokens()->delete();

        // Generate a new token for the user
        $tokenResult = $user->createToken('auth_token');
        $accessToken = $tokenResult->plainTextToken;

        // Return a success response with user and token data
        return success('Login successful.', [
            'user' => $user,
            'token' => $accessToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request)
    {
        // Define the validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:55',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|max:15',
            'password' => 'required|string|min:6',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }

        try {
            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user->tokens()->delete();
            $tokenResult = $user->createToken('auth_token');
            $accessToken = $tokenResult->plainTextToken;

            return success('User registered successfully.', [
                'user' => $user,
                'token' => $accessToken,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            // Return a failure response with the exception message
            return failure('User registration failed: '.$e->getMessage(), 500);
        }
    }

    public function user()
    {
        $user = Auth::user();
        return success('User data', $user);
    }

    public function userUpdate(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'phone'     => 'required',
            'avatar'    => 'nullable|string'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->phone = $request->phone;

        // Check if an avatar is uploaded
        if ($request->has('avatar') && $request->avatar !== null) {
            $base64Image = $request->input('avatar');
            $imageParts = explode(';base64,', $base64Image);
            if (count($imageParts) == 2) {
                $imageType = explode('image/', $imageParts[0])[1];
                $imageBase64 = base64_decode($imageParts[1]);

                if ($imageBase64 !== false) {
                    // Generate a unique file name
                    $fileName = time() . '_' . uniqid() . '.' . $imageType;
                    $filePath = public_path('avatars/' . $fileName);

                    // Delete the old avatar if it exists
                    if ($user->avatar && file_exists(public_path($user->avatar))) {
                        unlink(public_path($user->avatar));
                    }

                    // Save the file to the 'avatars' directory
                    file_put_contents($filePath, $imageBase64);

                    $user->avatar = 'avatars/' . $fileName;
                } else {
                    return failure('Invalid base64 format', 400);
                }
            } else {
                return failure('Invalid base64 format', 400);
            }
        }

        $user->save();

        return success('User update', $user);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return success('User logged out successfully.');

    }

    public function otpSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }


        // send otp this email
        $user = User::where('email', $request->email)->first();
        $otp = rand(10000, 99999);
        Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));
        $user->update(['opt_code' => $otp]);

        return success('OTP sent successfully.');

    }


    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|exists:users,opt_code',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:new_password',
        ]);
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }
        $user = User::where('opt_code', $request->otp)->first();
        $user->opt_code = null;
        $user->password = Hash::make($request->new_password);
        $user->save();
        return success('Password changed successfully.');
    }



}
