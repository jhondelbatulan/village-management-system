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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            request('sort'),
            request('creator')
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

        // "all" targets every village and is stored with a null village_id.
        if (($data['village_id'] ?? null) === 'all') {
            $data['village_id'] = null;
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
        $event->load('village', 'creator', 'responses.household', 'participations.household.village', 'participations.recorder');

        $user = auth()->user();
        $role = $user->role?->role_slug;

        $categories = [
            'Barangay Assembly', 'Community Service', 'Health Program', 'Sports',
            'Cultural', 'Youth', 'Senior Citizens', 'Women', 'Environment', 'Emergency Drill', 'Other',
        ];

        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $participationVillageId = $event->village_id;
        $participationVillage = null;
        $totalParticipations = $event->participations->count();

        // "All Villages" events split participations per village: village
        // staff only ever see their own village, the super admin can switch
        // between villages (or leave it unfiltered).
        if ($event->village_id === null) {
            if (in_array($role, ['admin', 'secretary'])) {
                $participationVillageId = $user->village_id;
            } else {
                $requested = (int) request('village');
                $participationVillageId = $requested > 0 && $villages->contains('id', $requested)
                    ? $requested
                    : null;
            }

            $participationVillage = $participationVillageId
                ? $villages->firstWhere('id', $participationVillageId)
                : null;

            $event->setRelation('participations', $event->participations
                ->filter(fn ($p) => $participationVillageId === null
                    || (int) ($p->household->village_id ?? 0) === $participationVillageId)
                ->values());
        }

        $participationStats = $event->village_id !== null
            ? $this->eventService->getParticipationStats($event->village_id)
            : null;

        return view('events.show', compact(
            'event',
            'categories',
            'role',
            'participationStats',
            'villages',
            'participationVillageId',
            'participationVillage',
            'totalParticipations'
        ));
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

    /**
     * Secretaries edit participation rows (status/remarks); super admins and
     * village admins only view them.
     */
    public function updateParticipation(Request $request, Event $event, int $participationId): RedirectResponse
    {
        $participation = $event->participations()->findOrFail($participationId);

        $validated = $request->validate([
            'status' => ['required', 'in:present,absent,excused'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $participation->update($validated + ['recorded_by' => auth()->id()]);

        AuditLogService::log('Update Participation', 'Events', "Updated participation record for event: {$event->title}");

        return redirect()->route('events.show', $event)->with('success', 'Participation record updated.');
    }

    /**
     * Downloadable attendance report (block/lot + household head of every
     * attendee). Village staff get their own village out of all-village
     * events; the super admin gets everyone.
     */
    public function attendancePdf(Event $event)
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        $event->load([
            'village',
            'creator',
            'participations.household',
            'participations.recorder',
            'participations.household.village',
        ]);

        $participations = $event->participations;
        $scopeVillage = $event->village?->village_name;

        if ($event->village_id === null) {
            if ($role !== 'super_admin') {
                $participations = $participations
                    ->filter(fn ($p) => (int) ($p->household->village_id ?? 0) === (int) $user->village_id)
                    ->values();
                $scopeVillage = Village::find($user->village_id)?->village_name;
            } else {
                $scopeVillage = 'All Villages';
            }
        }

        $pdf = Pdf::loadView('events.attendance-pdf', [
            'event' => $event,
            'participations' => $participations,
            'scopeVillage' => $scopeVillage,
            'generatedAt' => now(),
        ]);

        $filename = 'attendance_' . Str::slug($event->title, '_') . '_' . $event->event_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
