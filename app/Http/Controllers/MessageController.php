<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {

        // Validate the incoming request
        $validatedData = $request->validate([
            'message' => 'required|string|max:255',
            // Additional validation rules as per your requirements
        ]);

        // Store the message in the database
        $message = new Message();
        $message->user_id = auth()->id(); // Assuming you want to associate the message with the authenticated user
        $message->text = $validatedData['message'];
        // Additional fields assignment as per your requirements
        $message->save();

        // Return a success response
        return response()->json(['message' => 'Message sent successfully'], 200);
    }
}
