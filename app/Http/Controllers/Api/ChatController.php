<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;

class ChatController extends Controller
{
    public function index()
    {
        $messages = Chat::whereAny(['sender_id','receiver_id'], Auth::user()->id)->latest('created_at')->take(100)->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'created_at' => $message->created_at,
                'type' => $message->is_admin ? 'admin' : 'user',
            ];
        });
        return response()->json($messages);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'message'      => 'required|string|min:2|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }
        $userId = $request->user()->id;

        $adminId = User::where('role', 'admin')->first()->id;
        $chat = Chat::create([
            'sender_id' =>$userId,
            'receiver_id' => $adminId,
            'message' => $request->message,
            'is_admin'=> false,
        ]);

        $options = array(
            'cluster' => env('PUSHER_APP_CLUSTER','ap2'),
            'useTLS' => true
        );

        $pusher = new Pusher(
        env('PUSHER_APP_KEY','ad012c372ed42153296c'),
        env('PUSHER_APP_SECRET','6e031404468eef803332'),
        env('PUSHER_APP_ID','1921061'),
        $options
        );

        User::where('id', $userId)->update(['updated_at' => now()]);
        $users = User::where('role', '!=', 'admin')
                ->with('chats')->latest('updated_at')->limit(10)->get()->map(function($user){
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'unseen' => $user->chats->where('is_seen', false)->count() ?? '',
                        'avatar' => asset($user->avatar ?  $user->avatar : 'avatars/profile.png') ,
                    ];
                });

        $pusher->trigger('chatChannel.'.$userId, 'chatEvent', $chat);
        $pusher->trigger('chatChannel.admin123', 'chatEvent', $users);


        return response()->json($chat);
    }





}
