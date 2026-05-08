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
     * Store contact message (publisks — gan pieteicies, gan viesi)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|max:100',
            'country_code' => 'required|string|max:5',
            'phone'        => 'required|string|max:20',
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string|max:1000',
        ], [
            'name.required'         => 'Vārds ir obligāts.',
            'email.required'        => 'E-pasts ir obligāts.',
            'email.email'           => 'Ievadiet derīgu e-pasta adresi.',
            'country_code.required' => 'Valsts kods ir obligāts.',
            'phone.required'        => 'Tālruņa numurs ir obligāts.',
            'subject.required'      => 'Tēma ir obligāta.',
            'message.required'      => 'Ziņojums ir obligāts.',
            'message.max'           => 'Ziņojums nedrīkst pārsniegt 1000 rakstzīmes.',
        ]);

        // user_id neobligāts — viesi var arī rakstīt
        $userId = $request->user()?->id;

        $contactMessage = ContactMessage::create([
            'user_id'      => $userId,
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'country_code' => $validated['country_code'],
            'phone'        => $validated['phone'],
            'subject'      => $validated['subject'],
            'message'      => $validated['message'],
            'is_read'      => false,
            'is_replied'   => false,
        ]);

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
        }

        return back()->with('success', 'Paldies! Jūsu ziņojums ir veiksmīgi nosūtīts. Mēs ar jums sazināsimies pēc iespējas ātrāk!');
    }

    /**
     * Admin: Get all contact messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query()->with('user');

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        if ($request->has('is_replied')) {
            $query->where('is_replied', $request->boolean('is_replied'));
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhere('subject', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Admin/Contacts/Index', [
            'messages' => $messages,
            'filters' => $request->only(['is_read', 'is_replied', 'search']),
        ]);
    }

    public function show($id)
    {
        $message = ContactMessage::with('user')->findOrFail($id);

        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return Inertia::render('Admin/Contacts/Show', [
            'message' => $message,
        ]);
    }

    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);
        return back()->with('success', 'Ziņojums atzīmēts kā izlasīts.');
    }

    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'reply_text' => 'required|string',
        ]);

        $message = ContactMessage::findOrFail($id);
        $message->update([
            'is_replied' => true,
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Atbilde nosūtīta.');
    }
}
