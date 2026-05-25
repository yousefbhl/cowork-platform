<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ThreadResource;
use App\Models\Space;
use App\Models\Thread;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function threads(Request $request)
    {
        $threads = Thread::with(['space', 'messages.sender'])
            ->where('customer_id', $request->user()->id)
            ->latest('last_message_at')
            ->paginate(20);

        return ThreadResource::collection($threads);
    }

    public function hostThreads(Request $request)
    {
        $threads = Thread::with(['space', 'messages.sender'])
            ->where('host_id', $request->user()->id)
            ->latest('last_message_at')
            ->paginate(20);

        return ThreadResource::collection($threads);
    }

    public function show(Thread $thread)
    {
        $thread->load(['space', 'messages.sender']);
        return new ThreadResource($thread);
    }

    public function send(SendMessageRequest $request, Thread $thread)
    {
        $message = $thread->messages()->create([
            'sender_id' => $request->user()->id,
            'body'      => $request->body,
        ]);

        $thread->update(['last_message_at' => now()]);

        $message->load('sender');

        return new MessageResource($message);
    }

    public function enquire(Request $request, Space $space)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $thread = Thread::firstOrCreate([
            'space_id'    => $space->id,
            'customer_id' => $request->user()->id,
            'host_id'     => $space->host_id,
        ]);

        $message = $thread->messages()->create([
            'sender_id' => $request->user()->id,
            'body'      => $request->body,
        ]);

        $thread->update(['last_message_at' => now()]);

        $message->load('sender');

        return new MessageResource($message);
    }
}
