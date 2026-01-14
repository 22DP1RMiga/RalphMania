<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Mail\ContactReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true)->where('is_replied', false);
            } elseif ($request->status === 'replied') {
                $query->where('is_replied', true);
            }
        }

        // Sort - unread first, then by date
        $query->orderBy('is_read', 'asc')
            ->orderBy('is_replied', 'asc')
            ->orderBy('created_at', 'desc');

        // Paginate
        $messages = $query->paginate(15)->through(function ($message) {
            return [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'country_code' => $message->country_code,
                'phone' => $message->phone,
                'full_phone' => $message->full_phone,
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'is_replied' => $message->is_replied,
                'replied_at' => $message->replied_at,
                'created_at' => $message->created_at,
                'user' => $message->user ? [
                    'id' => $message->user->id,
                    'username' => $message->user->username,
                    'profile_picture' => $message->user->profile_picture,
                ] : null,
            ];
        });

        // Get stats
        $stats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('is_read', false)->count(),
            'read' => ContactMessage::where('is_read', true)->where('is_replied', false)->count(),
            'replied' => ContactMessage::where('is_replied', true)->count(),
        ];

        return Inertia::render('Admin/Contacts/Index', [
            'messages' => $messages,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    }

    /**
     * Display the specified contact message.
     */
    public function show($id)
    {
        $message = ContactMessage::with(['user', 'repliedBy'])->findOrFail($id);

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
                'full_phone' => $message->full_phone,
                'subject' => $message->subject,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'is_replied' => $message->is_replied,
                'reply_text' => $message->reply_text,
                'replied_at' => $message->replied_at,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'user' => $message->user ? [
                    'id' => $message->user->id,
                    'username' => $message->user->username,
                    'email' => $message->user->email,
                    'profile_picture' => $message->user->profile_picture,
                ] : null,
                'replied_by' => $message->repliedBy ? [
                    'id' => $message->repliedBy->id,
                    'username' => $message->repliedBy->username,
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
     * Mark message as unread.
     */
    public function markAsUnread($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => false]);

        return back()->with('success', 'Ziņojums atzīmēts kā nelasīts!');
    }

    /**
     * Reply to message.
     */
    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'reply_text' => 'required|string|min:10|max:5000',
        ]);

        $message = ContactMessage::findOrFail($id);

        // Try to send email
        $emailSent = false;
        try {
            Mail::to($message->email)->send(new ContactReply($message, $validated['reply_text']));
            $emailSent = true;

            Log::info('Contact reply email sent', [
                'contact_message_id' => $message->id,
                'to' => $message->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send contact reply email', [
                'contact_message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Update message status
        $message->update([
            'is_read' => true,
            'is_replied' => true,
            'reply_text' => $validated['reply_text'],
            'replied_at' => now(),
            'replied_by' => auth()->id(),
        ]);

        if ($emailSent) {
            return back()->with('success', 'Atbilde veiksmīgi nosūtīta uz ' . $message->email . '!');
        } else {
            return back()->with('warning', 'Atbilde saglabāta, bet e-pastu neizdevās nosūtīt. Lūdzu, sazinieties manuāli.');
        }
    }

    /**
     * Delete a contact message.
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Ziņojums veiksmīgi dzēsts!');
    }
}
