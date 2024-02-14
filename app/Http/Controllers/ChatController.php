<?php

namespace App\Http\Controllers;

use App\Events\Message;
use App\Models\Message as ModelsMessage;
use Exception;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $message = ModelsMessage::create([
                'content' => $request->content,
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->receiver_id,
            ]);
    
            // Broadcast event for real-time updates
            event(new Message($message));
    
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // public function message(Request $request){
    //     event(new Message($request->input('username'), $request->input('message')));

    //     return ModelsMessage::all();
    //     // return [];
    // }
}
