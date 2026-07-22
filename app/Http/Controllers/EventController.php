<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest\StoreEventRequest;
use App\Http\Requests\EventRequest\UpdateEventRequest;
use App\Http\Requests\EventRequest\StoreParticipationRequest;
use App\Models\Event;
use App\Models\Resident;
use App\Models\Village;
use App\Services\EventService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        $events = $this->eventService->getEventsForUser(
            $user,
            request('search'),
            request('status'),
            request('category'),
            request('sort')
        );

        $village = $user->village_id ? Village::find($user->village_id) : null;
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $categories = [
            'Barangay Assembly', 'Community Service', 'Health Program', 'Sports',
            'Cultural', 'Youth', 'Senior Citizens', 'Women', 'Environment', 'Emergency Drill', 'Other',
        ];

        return view('events.index', compact('events', 'village', 'villages', 'categories', 'role'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $user->village_id;

        $village = $villageId ? Village::find($villageId) : null;
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $categories = [
            'Barangay Assembly', 'Community Service', 'Health Program', 'Sports',
            'Cultural', 'Youth', 'Senior Citizens', 'Women', 'Environment', 'Emergency Drill', 'Other',
        ];

        return view('events.create', compact('village', 'villages', 'categories', 'role'));
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role !== 'super_admin' && !$data['village_id']) {
            $data['village_id'] = $user->village_id;
        }

        $event = $this->eventService->create($data);

        AuditLogService::log('Create Event', 'Events', "Created event: {$event->title}");

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $event->load('village', 'creator', 'responses.resident', 'participations.resident', 'participations.recorder');

        $role = auth()->user()->role?->role_slug;

        $categories = [
            'Barangay Assembly', 'Community Service', 'Health Program', 'Sports',
            'Cultural', 'Youth', 'Senior Citizens', 'Women', 'Environment', 'Emergency Drill', 'Other',
        ];

        $participationStats = $this->eventService->getParticipationStats($event->village_id);

        return view('events.show', compact('event', 'categories', 'role', 'participationStats'));
    }

    public function edit(Event $event): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $event->village_id;

        $village = Village::find($villageId);
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $categories = [
            'Barangay Assembly', 'Community Service', 'Health Program', 'Sports',
            'Cultural', 'Youth', 'Senior Citizens', 'Women', 'Environment', 'Emergency Drill', 'Other',
        ];

        return view('events.edit', compact('event', 'village', 'villages', 'categories', 'role'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        $event = $this->eventService->update($event, $data);

        AuditLogService::log('Update Event', 'Events', "Updated event: {$event->title}");

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $title = $event->title;
        $this->eventService->delete($event);

        AuditLogService::log('Delete Event', 'Events', "Deleted event: {$title}");

        return redirect()->route('events.index')->with('success', "Event \"{$title}\" has been deleted.");
    }

    public function respond(Event $event, Request $request): RedirectResponse
    {
        $request->validate([
            'response' => 'required|in:going,maybe,not_going',
        ]);

        $resident = auth()->user()->household?->residents()->first();

        if (!$resident) {
            return back()->with('error', 'No resident profile linked to your account.');
        }

        $event->responses()->updateOrCreate(
            ['resident_id' => $resident->id],
            [
                'response' => $request->input('response'),
                'note' => $request->input('note'),
            ]
        );

        AuditLogService::log('Event Response', 'Events', "Responded to event: {$event->title} - {$request->input('response')}");

        return redirect()->route('events.show', $event)->with('success', 'Your response has been recorded.');
    }

    public function recordParticipation(Event $event, StoreParticipationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->eventService->recordParticipation(
            $event,
            $data['resident_id'],
            $data['status'],
            $data['remarks'] ?? null
        );

        AuditLogService::log('Record Participation', 'Events', "Recorded participation for event: {$event->title}");

        return redirect()->route('events.show', $event)->with('success', 'Participation recorded successfully.');
    }

    public function removeParticipation(Event $event, int $participationId): RedirectResponse
    {
        $participation = $event->participations()->findOrFail($participationId);
        $participation->delete();

        AuditLogService::log('Remove Participation', 'Events', "Removed participation record from event: {$event->title}");

        return redirect()->route('events.show', $event)->with('success', 'Participation record removed.');
    }

    public function autoGenerate(Event $event): RedirectResponse
    {
        $this->eventService->autoGenerateParticipations($event);

        AuditLogService::log('Auto Generate Participations', 'Events', "Auto-generated participations from Going responses for event: {$event->title}");

        return redirect()->route('events.show', $event)->with('success', 'Participations auto-generated from Going responses.');
    }
}
