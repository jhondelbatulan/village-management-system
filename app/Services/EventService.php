<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class EventService
{
    /**
     * Remarks used on participation rows created automatically from a "Going" RSVP.
     */
    public const AUTO_GENERATED_REMARK = 'Auto-generated from Going response';

    public function getEventsForUser($user, ?string $search = null, ?string $status = null, ?string $category = null, ?string $sort = null, ?string $creator = null)
    {
        $query = Event::with(['village', 'creator.role']);

        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            // Super Admin sees ALL events
        } elseif (in_array($role, ['admin', 'secretary', 'treasurer'])) {
            $query->forVillage($user->village_id);
        } elseif ($role === 'resident') {
            $query->forVillage($user->village_id)
                  ->whereIn('status', ['upcoming', 'ongoing']);
        }

        if ($creator === 'super_admin' || $creator === 'admin') {
            $query->whereHas('creator.role', fn ($q) => $q->where('role_slug', $creator));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($sort === 'az') {
            $query->orderBy('title');
        } elseif ($sort === 'za') {
            $query->orderByDesc('title');
        } else {
            $query->latest();
        }

        return $query->paginate(15)->withQueryString();
    }

    public function getVillageEvents(int $villageId, ?string $search = null, ?string $status = null, ?string $category = null)
    {
        $query = Event::with(['village', 'creator.role'])
            ->forVillage($villageId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    public function getUpcomingEvents(int $villageId)
    {
        return Event::forVillage($villageId)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->get();
    }

    public function create(array $data): Event
    {
        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            $data['cover_image'] = $data['cover_image']->store('events', 'public');
        }

        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $data['attachment'] = $data['attachment']->store('events', 'public');
        }

        $data['status'] = $this->computeAutomaticStatus($data);
        $data['created_by'] = auth()->id();

        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $data['cover_image'] = $data['cover_image']->store('events', 'public');
        } else {
            unset($data['cover_image']);
        }

        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            if ($event->attachment) {
                Storage::disk('public')->delete($event->attachment);
            }
            $data['attachment'] = $data['attachment']->store('events', 'public');
        } else {
            unset($data['attachment']);
        }

        // Recompute the status from the schedule unless the event was
        // manually cancelled or completed (both stick until changed through
        // the dedicated button flow).
        if (! isset($data['status']) && ! in_array($event->status, ['cancelled', 'completed'], true)) {
            $data['status'] = $this->computeAutomaticStatus(array_merge([
                'event_date' => $event->event_date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
            ], $data));
        }

        $event->update($data);

        if (($data['status'] ?? null) === 'completed' && $event->wasChanged('status')) {
            $this->autoGenerateParticipations($event);
        }

        return $event;
    }

    /**
     * Compute the status from the event schedule: future date (or today
     * before start_time) => upcoming. Once start_time has arrived (or the
     * date has passed) the event stays ongoing until the creator manually
     * marks it completed.
     */
    public function computeAutomaticStatus(array $data): string
    {
        $date = Carbon::parse($data['event_date'])->startOfDay();
        $today = Carbon::today();
        $now = Carbon::now();

        if ($date->gt($today)) {
            return 'upcoming';
        }

        if ($date->eq($today)
            && ! empty($data['start_time'])
            && $now->lt(Carbon::parse($data['start_time']))
        ) {
            return 'upcoming';
        }

        return 'ongoing';
    }

    public function markCompleted(Event $event): Event
    {
        if ($event->status !== 'completed') {
            $event->update(['status' => 'completed']);
            $this->autoGenerateParticipations($event);
        }

        return $event;
    }

    public function cancel(Event $event): Event
    {
        $event->update(['status' => 'cancelled']);

        return $event;
    }

    public function delete(Event $event): void
    {
        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }
        if ($event->attachment) {
            Storage::disk('public')->delete($event->attachment);
        }

        $event->delete();
    }

    /**
     * Participation is tracked per household: one record per household per
     * event, regardless of how many members attend or RSVP.
     */
    public function recordParticipation(Event $event, int $householdId, string $status, ?string $remarks = null): Participation
    {
        return Participation::updateOrCreate(
            ['event_id' => $event->id, 'household_id' => $householdId],
            [
                'resident_id' => null,
                'status' => $status,
                'remarks' => $remarks,
                'recorded_by' => auth()->id(),
            ]
        );
    }

    /**
     * Keep the household participation record in sync with a household's RSVP so
     * participation monitoring always reflects "Going" responses. The whole
     * household participates as soon as it responds "Going".
     */
    public function syncParticipationWithResponse(Event $event, int $householdId, string $response): void
    {
        $participation = Participation::where('event_id', $event->id)
            ->where('household_id', $householdId)
            ->first();

        if ($response === 'going') {
            if ($participation !== null) {
                return;
            }

            Participation::create([
                'event_id' => $event->id,
                'household_id' => $householdId,
                'resident_id' => null,
                'status' => 'present',
                'remarks' => self::AUTO_GENERATED_REMARK,
                'recorded_by' => auth()->id(),
            ]);

            return;
        }

        // Only drop the auto-generated record; manually recorded attendance stays.
        if ($participation === null || $participation->remarks !== self::AUTO_GENERATED_REMARK) {
            return;
        }

        $participation->delete();
    }

    public function autoGenerateParticipations(Event $event): void
    {
        $goingResponses = $event->responses()->where('response', 'going')->get()
            ->filter(fn ($response) => $response->household_id)
            ->groupBy(fn ($response) => $response->household_id);

        foreach ($goingResponses as $householdId => $responses) {
            $exists = Participation::where('event_id', $event->id)
                ->where('household_id', $householdId)
                ->exists();

            if (!$exists) {
                Participation::create([
                    'event_id' => $event->id,
                    'household_id' => $householdId,
                    'resident_id' => null,
                    'status' => 'present',
                    'remarks' => self::AUTO_GENERATED_REMARK,
                    'recorded_by' => auth()->id(),
                ]);
            }
        }
    }

    public function getParticipationStats(int $villageId): array
    {
        $totalEvents = Event::forVillage($villageId)->count();
        $totalResidents = \App\Models\Resident::where('village_id', $villageId)->where('status', 'active')->count();

        $scoped = fn ($query) => $this->scopeParticipationsForVillage($query, $villageId);

        $totalParticipations = $scoped(Participation::query())->count();
        $presentCount = $scoped(Participation::query())->where('status', 'present')->count();
        $absentCount = $scoped(Participation::query())->where('status', 'absent')->count();
        $excusedCount = $scoped(Participation::query())->where('status', 'excused')->count();
        $lateCount = $scoped(Participation::query())->where('status', 'late')->count();

        $attendanceRate = $totalParticipations > 0 ? round(($presentCount / $totalParticipations) * 100, 1) : 0;

        $upcomingEvents = Event::forVillage($villageId)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->count();

        $completedEvents = Event::forVillage($villageId)
            ->where('status', 'completed')
            ->count();

        return [
            'total_events' => $totalEvents,
            'upcoming_events' => $upcomingEvents,
            'completed_events' => $completedEvents,
            'total_participants' => $totalParticipations,
            'present' => $presentCount,
            'absent' => $absentCount,
            'excused' => $excusedCount,
            'late' => $lateCount,
            'attendance_rate' => $attendanceRate,
            'total_residents' => $totalResidents,
        ];
    }

    public function getHouseholdParticipationStats(int $villageId): array
    {
        $households = \App\Models\Household::where('village_id', $villageId)
            ->with('residents')
            ->get();

        $totalEvents = Event::forVillage($villageId)->where('status', 'completed')->count();

        $stats = [];
        foreach ($households as $household) {
            $memberCount = $household->residents()->where('status', 'active')->count();
            $participatedCount = Participation::where('household_id', $household->id)
                ->where('status', 'present')
                ->distinct('event_id')
                ->count('event_id');
            $lastAttendance = Participation::where('household_id', $household->id)
                ->where('status', 'present')
                ->latest()
                ->first();

            $participationPct = $totalEvents > 0
                ? round(($participatedCount / $totalEvents) * 100, 1)
                : 0;

            $stats[] = [
                'household' => $household,
                'member_count' => $memberCount,
                'participated_count' => $participatedCount,
                'total_events' => $totalEvents,
                'participation_pct' => $participationPct,
                'last_attendance' => $lastAttendance?->created_at,
                'is_active' => $participatedCount > 0,
            ];
        }

        return collect($stats)->sortByDesc('participation_pct')->values()->all();
    }

    /**
     * Participation belongs to the household, so a member's history is the
     * household's participation history.
     */
    public function getResidentParticipationHistory(int $residentId)
    {
        $householdId = \App\Models\Resident::find($residentId)?->household_id;

        if (! $householdId) {
            return Participation::whereNull('id')
                ->with(['event', 'event.village'])
                ->paginate(15);
        }

        return Participation::where('household_id', $householdId)
            ->with(['event', 'event.village'])
            ->latest()
            ->paginate(15);
    }

    public function getVillageComparisonStats(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $stats = [];

        foreach ($villages as $village) {
            $villageStats = $this->getParticipationStats($village->id);
            $villageStats['village'] = $village;
            $stats[] = $villageStats;
        }

        return collect($stats)->sortByDesc('attendance_rate')->values()->all();
    }

    /**
     * A participation belongs to a village when its event targets that village
     * OR when the event is an "All Villages" event and the participating
     * household lives in that village. Used so per-village dashboards split
     * all-village events correctly.
     */
    private function scopeParticipationsForVillage($query, int $villageId)
    {
        return $query->where(function ($q) use ($villageId) {
            $q->whereHas('event', fn ($e) => $e->where('village_id', $villageId))
              ->orWhere(function ($all) use ($villageId) {
                  $all->whereHas('event', fn ($e) => $e->whereNull('village_id'))
                      ->whereHas('household', fn ($h) => $h->where('village_id', $villageId));
              });
        });
    }
}
