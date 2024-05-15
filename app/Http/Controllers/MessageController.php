<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

    public function index($userId)
    {
        $authUserId = Auth::id();
        $messages = Message::where(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $authUserId)
                ->orWhere('receiver_id', $authUserId);
        })
            ->where(function ($query) use ($authUserId, $userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {

        // Validate the incoming request
        $validatedData = $request->validate([
            'message' => 'required|string|max:255',
            'receiver_id' => 'required|exists:users,id',
        ]);


        $message = new Message();
        $message->sender_id = auth()->id();
        $message->receiver_id = $validatedData['receiver_id'];
        $message->text = $validatedData['message'];
        $message->save();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => 'Message sent successfully'], 200);
    }
}
