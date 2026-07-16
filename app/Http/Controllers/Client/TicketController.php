<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TicketAttachment;
use App\Models\TicketDepartment;
use App\Models\TicketMessage;
use App\Models\TicketThread;
use App\Models\User;
use App\Notifications\TicketReply;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->tickets()
            ->withCount('messages')
            ->with('department')
            ->latest();

        if ($request->filled('status') && in_array($request->status, ['open', 'answered', 'closed'])) {
            $query->where('status', $request->status);
        }

        $tickets = $query->get();

        return view('client.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $user = Auth::user();
        $services = $user->services()->where('status', 'active')->get();
        $departments = TicketDepartment::active()->orderBy('sort_order')->get();

        return view('client.tickets.create', compact('services', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'service_id' => 'nullable|exists:services,id',
            'department_id' => 'nullable|exists:ticket_departments,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,txt,zip',
        ]);

        $user = Auth::user();

        $ticket = TicketThread::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'service_id' => $request->service_id,
            'department_id' => $request->department_id,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        $message = TicketMessage::create([
            'ticket_thread_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        $this->handleAttachments($request, $message);

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Ticket created successfully');
    }

    public function show(TicketThread $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['messages.user', 'messages.attachments', 'department']);

        return view('client.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, TicketThread $ticket)
    {
        $this->authorize('update', $ticket);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Cannot reply to a closed ticket. Please reopen it first.');
        }

        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,txt,zip',
        ]);

        $message = TicketMessage::create([
            'ticket_thread_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_staff' => false,
        ]);

        $this->handleAttachments($request, $message);

        $ticket->update(['status' => 'answered']);

        app(NotificationService::class)->notifyAdmins(new TicketReply($ticket, $message));

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Reply sent successfully');
    }

    public function close(TicketThread $ticket)
    {
        $this->authorize('update', $ticket);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Ticket is already closed.');
        }

        $ticket->update(['status' => 'closed']);

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Ticket closed');
    }

    public function reopen(TicketThread $ticket)
    {
        $this->authorize('update', $ticket);

        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Ticket is not closed.');
        }

        $ticket->update(['status' => 'open']);

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', 'Ticket reopened');
    }

    private function handleAttachments(Request $request, TicketMessage $message): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('ticket-attachments', 'public');

            TicketAttachment::create([
                'ticket_message_id' => $message->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
