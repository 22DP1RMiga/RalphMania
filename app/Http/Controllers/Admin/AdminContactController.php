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
     * Parāda administratora kontaktinformācijas sarakstu
     */
    public function index(Request $request)
    {
        $query = ContactMessage::with('user');

        // Meklēšanai
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

        // Filtrē pēc statusa "izlasīts"
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true)->where('is_replied', false);
            } elseif ($request->status === 'replied') {
                $query->where('is_replied', true);
            }
        }

        // Kārto vispirms pēc nelasītiem, pēc tam pēc datuma
        $query->orderBy('is_read', 'asc')
            ->orderBy('is_replied', 'asc')
            ->orderBy('created_at', 'desc');

        // Lappusēm (for pagination)
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
                'locale' => $message->locale,
                'created_at' => $message->created_at,
                'user' => $message->user ? [
                    'id' => $message->user->id,
                    'username' => $message->user->username,
                    'profile_picture' => $message->user->profile_picture,
                ] : null,
            ];
        });

        // Iegūst statistiku
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
     * Parāda norādīto kontaktziņojumu
     */
    public function show($id)
    {
        $message = ContactMessage::with(['user', 'repliedBy'])->findOrFail($id);

        // Automātiski atzīmē kā izlasītu, kad apskata
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
                'locale' => $message->locale,
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
     * Atzīmē ziņojumu kā izlasītu
     */
    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return back()->with('success', 'Ziņojums atzīmēts kā izlasīts!');
    }

    /**
     * Atzīmē ziņojumu kā neizlasītu
     */
    public function markAsUnread($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => false]);

        return back()->with('success', 'Ziņojums atzīmēts kā nelasīts!');
    }

    /**
     * Atbild uz ziņojumu
     */
    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'reply_text' => 'required|string|min:10|max:5000',
        ]);

        $message = ContactMessage::findOrFail($id);

        // Mēģina nosūtīt e-pastu
        $emailSent = false;
        try {
            Mail::to($message->email)->send(new ContactReply($message, $validated['reply_text'], $message->locale ?? 'lv'));
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

        // Atjaunina ziņojuma statusu
        $message->update([
            'is_read' => true,
            'is_replied' => true,
            'reply_text' => $validated['reply_text'],
            'replied_at' => now(),
            'replied_by' => auth()->user()?->administrator?->id,
        ]);

        if ($emailSent) {
            return back()->with('success', 'Atbilde veiksmīgi nosūtīta uz ' . $message->email . '!');
        } else {
            return back()->with('warning', 'Atbilde saglabāta, bet e-pastu neizdevās nosūtīt. Lūdzu, sazinieties manuāli.');
        }
    }

    /**
     * Dzēš kontaktpersonas ziņojumu
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Ziņojums veiksmīgi dzēsts!');
    }
}
