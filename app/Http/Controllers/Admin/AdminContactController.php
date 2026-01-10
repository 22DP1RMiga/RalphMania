<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AdminContactController extends Controller
{
    /**
     * Display a listing of contact messages for admin.
     */
    public function index(Request $request)
    {
        $query = ContactMessage::with('user');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by read status
        if ($request->has('is_read') && $request->is_read !== '') {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by replied status
        if ($request->has('is_replied') && $request->is_replied !== '') {
            $query->where('is_replied', $request->boolean('is_replied'));
        }

        // Sort - unread first, then by date
        $query->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc');

        // Paginate
        $messages = $query->paginate(20)->through(function ($message) {
            return [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'country_code' => $message->country_code,
                'phone' => $message->phone,
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'is_replied' => $message->is_replied,
                'replied_at' => $message->replied_at,
                'created_at' => $message->created_at,
                'user' => $message->user ? [
                    'id' => $message->user->id,
                    'username' => $message->user->username,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Contacts/Index', [
            'messages' => $messages,
            'filters' => $request->only(['search', 'is_read', 'is_replied']),
        ]);
    }

    /**
     * Display the specified contact message.
     */
    public function show($id)
    {
        $message = ContactMessage::with('user')->findOrFail($id);

        // Mark as read automatically when viewing
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return Inertia::render('Admin/Contacts/Show', [
            'message' => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'country_code' => $message->country_code,
                'phone' => $message->phone,
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'is_replied' => $message->is_replied,
                'replied_at' => $message->replied_at,
                'created_at' => $message->created_at,
                'user' => $message->user ? [
                    'id' => $message->user->id,
                    'username' => $message->user->username,
                    'email' => $message->user->email,
                ] : null,
            ],
        ]);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return back()->with('success', 'Ziņojums atzīmēts kā izlasīts!');
    }

    /**
     * Mark message as replied.
     */
    public function reply(Request $request, $id)
    {
        $message = ContactMessage::findOrFail($id);

        $message->update([
            'is_read' => true,
            'is_replied' => true,
            'replied_at' => now(),
            'replied_by' => auth()->id(),
        ]);

        return back()->with('success', 'Ziņojums atzīmēts kā atbildēts!');
    }

    /**
     * Delete a contact message.
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return back()->with('success', 'Ziņojums veiksmīgi dzēsts!');
    }
}
