<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;


class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $receiverId = $request->receiver_id;

        $chats = Chat::where(function ($query) use ($userId, $receiverId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($query) use ($userId, $receiverId) {
                $query->where('sender_id', $receiverId)
                      ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($chats);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        if ($validator->fails()) {
            return validationError('Validation failed.', $validator->errors());
        }


        try {
            $fileName = null;
            if ($request->hasFile('file')) {
                $fileName = '/files/' . $request->file('file')->store('chats', 'public');
            }

            $chat = Chat::create([
                'sender_id' => $request->user()->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'file' => $fileName,
                'is_admin' => $request->is_admin ? true : false,
            ]);

            $options = [
                'cluster' => env('PUSHER_APP_CLUSTER', 'ap2'),
                'useTLS' => true
            ];
            $pusher = new Pusher(
                env('PUSHER_APP_KEY', 'ad012c372ed42153296c'),
                env('PUSHER_APP_SECRET', '6e031404468eef803332'),
                env('PUSHER_APP_ID', '1921061'),
                $options
            );

            User::where('id', $request->receiver_id)->update(['updated_at' => now()]);

            $users = User::where('role', '!=', 'admin')
                ->with('chats')->latest('updated_at')->limit(10)->get()->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'unseen' => $user->chats->where('is_seen', false)->count() ?? '',
                        'avatar' => asset($user->avatar ? $user->avatar : 'avatars/profile.png'),
                    ];
                });

            $chat['is_file'] = $fileName ? true : false;
            $pusher->trigger('chatChannel.' . $request->receiver_id, 'chatEvent', $chat);
            $pusher->trigger('chatChannel.admin123', 'chatEvent', $users);

            return response()->json($chat);
        } catch (\Exception $e) {
            Log::error('Chat storing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    // public function store(Request $request)
    // {


    //     $fileName = null;
    //     if ($request->hasFile('file')) {
    //         $fileName = '/files/'.$request->file('file')->store('chats', 'public');
    //     }

    //     $chat = Chat::create([
    //         'sender_id' =>$request->user()->id,
    //         'receiver_id' => $request->receiver_id,
    //         'message' => $request->message,
    //         'file'=>  $fileName ,
    //         'is_admin'=> $request->is_admin ? true : false,
    //     ]);

    //     $options = array(
    //         'cluster' => env('PUSHER_APP_CLUSTER','ap2'),
    //         'useTLS' => true
    //     );
    //     $pusher = new Pusher(
    //         env('PUSHER_APP_KEY','ad012c372ed42153296c'),
    //         env('PUSHER_APP_SECRET','6e031404468eef803332'),
    //         env('PUSHER_APP_ID','1921061'),
    //         $options
    //     );


    //     User::where('id', $request->receiver_id)->update(['updated_at' => now()]);
    //     $users = User::where('role', '!=', 'admin')
    //             ->with('chats')->latest('updated_at')->limit(10)->get()->map(function($user){
    //                 return [
    //                     'id' => $user->id,
    //                     'name' => $user->name,
    //                     'unseen' => $user->chats->where('is_seen', false)->count() ?? '',
    //                     'avatar' => asset($user->avatar ?  $user->avatar : 'avatars/profile.png') ,
    //                 ];
    //             });

    //             $chat['is_file'] = $fileName ? true : false;

    //     $pusher->trigger('chatChannel.'.$request->receiver_id, 'chatEvent', $chat);
    //     $pusher->trigger('chatChannel.admin123', 'chatEvent', $users);


    //     return response()->json($chat);
    // }


}
