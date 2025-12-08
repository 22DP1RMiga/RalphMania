<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Store contact message (public)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:1000',
        ]);

        $contactMessage = ContactMessage::create([
            'user_id' => auth()->id() ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'is_read' => false,
            'is_replied' => false,
        ]);

        return response()->json([
            'message' => 'Paldies! Jūsu ziņojums ir nosūtīts.',
            'contact_message' => $contactMessage
        ], 201);
    }

    /**
     * Admin: Get all contact messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by replied status
        if ($request->has('is_replied')) {
            $query->where('is_replied', $request->boolean('is_replied'));
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhere('subject', 'LIKE', "%{$request->search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * Admin: Mark as read
     */
    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json([
            'message' => 'Atzīmēts kā izlasīts',
            'contact_message' => $message
        ]);
    }

    /**
     * Admin: Reply to message
     */
    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'reply_text' => 'required|string',
        ]);

        $message = ContactMessage::findOrFail($id);

        // Here you would typically send an email
        // For now, just mark as replied
        $message->update([
            'is_replied' => true,
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Atbilde nosūtīta',
            'contact_message' => $message
        ]);
    }
}
