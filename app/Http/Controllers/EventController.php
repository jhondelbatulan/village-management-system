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
use App\Services\NotificationService;
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

        if (in_array($event->status, ['upcoming', 'ongoing'])) {
            app(NotificationService::class)->notifyResidents(
                $event->village_id,
                'event',
                'New Event',
                "New event: {$event->title} on {$event->event_date->format('M d, Y')}.",
                route('events.show', $event)
            );

            app(NotificationService::class)->notifyVillageStaff(
                $event->village_id,
                'event',
                'New Event',
                "New event created: {$event->title} on {$event->event_date->format('M d, Y')}.",
                route('events.show', $event),
                auth()->id()
            );
        }

        AuditLogService::log('Create Event', 'Events', "Created event: {$event->title}");

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $event->load('village', 'creator', 'responses.household', 'participations.household', 'participations.recorder');

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
        abort_unless($event->isManagedBy(auth()->user()), 403, 'Only the creator of this event can manage it.');

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
        abort_unless($event->isManagedBy(auth()->user()), 403, 'Only the creator of this event can manage it.');

        $data = $request->validated();

        $wasLive = in_array($event->status, ['upcoming', 'ongoing']);

        $event = $this->eventService->update($event, $data);

        if (! $wasLive && in_array($event->status, ['upcoming', 'ongoing'])) {
            app(NotificationService::class)->notifyResidents(
                $event->village_id,
                'event',
                'Event Scheduled',
                "Event is now scheduled: {$event->title} on {$event->event_date->format('M d, Y')}.",
                route('events.show', $event)
            );

            app(NotificationService::class)->notifyVillageStaff(
                $event->village_id,
                'event',
                'Event Scheduled',
                "Event is now scheduled: {$event->title} on {$event->event_date->format('M d, Y')}.",
                route('events.show', $event),
                auth()->id()
            );
        }

        AuditLogService::log('Update Event', 'Events', "Updated event: {$event->title}");

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function complete(Event $event): RedirectResponse
    {
        abort_unless($event->isManagedBy(auth()->user()), 403, 'Only the creator of this event can manage it.');

        if ($event->status === 'completed') {
            return back()->with('error', 'This event is already completed.');
        }

        $this->eventService->markCompleted($event);

        AuditLogService::log('Complete Event', 'Events', "Marked event as completed: {$event->title}");

        return redirect()->route('events.show', $event)
            ->with('success', "Event \"{$event->title}\" has been marked as completed.");
    }

    public function cancel(Event $event): RedirectResponse
    {
        abort_unless($event->isManagedBy(auth()->user()), 403, 'Only the creator of this event can manage it.');

        if ($event->status === 'cancelled') {
            return back()->with('error', 'This event is already cancelled.');
        }

        $this->eventService->cancel($event);

        AuditLogService::log('Cancel Event', 'Events', "Cancelled event: {$event->title}");

        return redirect()->route('events.show', $event)
            ->with('success', "Event \"{$event->title}\" has been cancelled.");
    }

    public function destroy(Event $event): RedirectResponse
    {
        abort_unless($event->isManagedBy(auth()->user()), 403, 'Only the creator of this event can manage it.');

        $title = $event->title;

        $respondentHouseholdIds = $event->responses()
            ->pluck('household_id')
            ->filter()
            ->unique()
            ->values();

        $this->eventService->delete($event);

        app(NotificationService::class)->notifyVillageStaff(
            $event->village_id,
            'event',
            'Event Deleted',
            "Event \"{$title}\" has been deleted.",
            route('events.index'),
            auth()->id()
        );

        app(NotificationService::class)->notifyHouseholdResidents(
            $respondentHouseholdIds->all(),
            'event',
            'Event Cancelled',
            "The event \"{$title}\" has been cancelled.",
            route('events.index')
        );

        AuditLogService::log('Delete Event', 'Events', "Deleted event: {$title}");

        return redirect()->route('events.index')->with('success', "Event \"{$title}\" has been deleted.");
    }

    public function respond(Event $event, Request $request): RedirectResponse
    {
        $request->validate([
            'response' => 'required|in:going,maybe,not_going',
        ]);

        $household = auth()->user()->household;

        if (!$household) {
            return back()->with('error', 'Your account is not linked to a household yet. Please contact the administration.');
        }

        $event->responses()->updateOrCreate(
            ['household_id' => $household->id],
            [
                'response' => $request->input('response'),
                'note' => $request->input('note'),
                'resident_id' => null,
            ]
        );

        $this->eventService->syncParticipationWithResponse($event, $household->id, $request->input('response'));

        AuditLogService::log('Event Response', 'Events', "Responded to event: {$event->title} - {$request->input('response')}");

        return redirect()->route('events.show', $event)->with('success', 'Your response has been recorded.');
    }

    public function recordParticipation(Event $event, StoreParticipationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->eventService->recordParticipation(
            $event,
            $data['household_id'],
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
