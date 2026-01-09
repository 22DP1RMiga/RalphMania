<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Mail\ContactMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Store contact message (authenticated users only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:1000',
        ], [
            'name.required' => 'Vārds ir obligāts.',
            'email.required' => 'E-pasts ir obligāts.',
            'email.email' => 'Ievadiet derīgu e-pasta adresi.',
            'country_code.required' => 'Valsts kods ir obligāts.',
            'phone.required' => 'Tālruņa numurs ir obligāts.',
            'subject.required' => 'Tēma ir obligāta.',
            'message.required' => 'Ziņojums ir obligāts.',
            'message.max' => 'Ziņojums nedrīkst pārsniegt 1000 rakstzīmes.',
        ]);

        // Get authenticated user
        $user = $request->user();

        // Create contact message
        $contactMessage = ContactMessage::create([
            'user_id' => $user->id,
            'name' => $validated['username'],
            'email' => $validated['email'],
            'country_code' => $validated['country_code'],
            'phone' => $validated['phone'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'is_read' => false,
            'is_replied' => false,
        ]);

        // Send email notification to admin
        try {
            Mail::to('ralphmania.roltonslv@gmail.com')
                ->send(new ContactMessageReceived($contactMessage));

            Log::info('Contact message email sent successfully', [
                'contact_message_id' => $contactMessage->id,
                'from' => $contactMessage->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send contact message email', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if email fails - message is still saved
        }

        // Return Inertia response with flash message
        return back()->with('success', 'Paldies! Jūsu ziņojums ir veiksmīgi nosūtīts. Mēs ar jums sazināsimies pēc iespējas ātrāk!');
    }

    /**
     * Admin: Get all contact messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query()->with('user');

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
                    ->orWhere('subject', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Contacts/Index', [
            'messages' => $messages,
            'filters' => $request->only(['is_read', 'is_replied', 'search']),
        ]);
    }

    /**
     * Admin: Show single contact message
     */
    public function show($id)
    {
        $message = ContactMessage::with('user')->findOrFail($id);

        // Mark as read
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return Inertia::render('Admin/Contacts/Show', [
            'message' => $message,
        ]);
    }

    /**
     * Admin: Mark as read
     */
    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return back()->with('success', 'Ziņojums atzīmēts kā izlasīts.');
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

        return back()->with('success', 'Atbilde nosūtīta.');
    }
}
