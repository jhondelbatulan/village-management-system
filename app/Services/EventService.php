<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventService
{
    public function getEventsForUser($user, ?string $search = null, ?string $status = null, ?string $category = null, ?string $sort = null)
    {
        $query = Event::with('village', 'creator');

        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            // Super Admin sees ALL events
        } elseif (in_array($role, ['admin', 'secretary', 'treasurer'])) {
            $query->where('village_id', $user->village_id);
        } elseif ($role === 'resident') {
            $query->where('village_id', $user->village_id)
                  ->whereIn('status', ['upcoming', 'ongoing']);
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
        $query = Event::with('village', 'creator')
            ->where('village_id', $villageId);

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
        return Event::where('village_id', $villageId)
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

        $event->update($data);

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

    public function recordParticipation(Event $event, int $residentId, string $status, ?string $remarks = null): Participation
    {
        $resident = \App\Models\Resident::find($residentId);

        return Participation::create([
            'event_id' => $event->id,
            'resident_id' => $residentId,
            'household_id' => $resident?->household_id,
            'status' => $status,
            'remarks' => $remarks,
            'recorded_by' => auth()->id(),
        ]);
    }

    public function autoGenerateParticipations(Event $event): void
    {
        $goingResponses = $event->responses()->where('response', 'going')->with('resident')->get();

        foreach ($goingResponses as $response) {
            $exists = Participation::where('event_id', $event->id)
                ->where('resident_id', $response->resident_id)
                ->exists();

            if (!$exists) {
                Participation::create([
                    'event_id' => $event->id,
                    'resident_id' => $response->resident_id,
                    'household_id' => $response->resident?->household_id,
                    'status' => 'present',
                    'remarks' => 'Auto-generated from Going response',
                    'recorded_by' => auth()->id(),
                ]);
            }
        }
    }

    public function getParticipationStats(int $villageId): array
    {
        $totalEvents = Event::where('village_id', $villageId)->count();
        $totalResidents = \App\Models\Resident::where('village_id', $villageId)->where('status', 'active')->count();
        $totalParticipations = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->count();
        $presentCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'present')->count();
        $absentCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'absent')->count();
        $excusedCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'excused')->count();
        $lateCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'late')->count();

        $attendanceRate = $totalParticipations > 0 ? round(($presentCount / $totalParticipations) * 100, 1) : 0;

        $upcomingEvents = Event::where('village_id', $villageId)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->count();

        $completedEvents = Event::where('village_id', $villageId)
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

        $totalEvents = Event::where('village_id', $villageId)->where('status', 'completed')->count();

        $stats = [];
        foreach ($households as $household) {
            $memberCount = $household->residents()->where('status', 'active')->count();
            $residentIds = $household->residents->pluck('id');
            $participatedCount = Participation::whereIn('resident_id', $residentIds)
                ->where('status', 'present')
                ->distinct('event_id')
                ->count('event_id');
            $lastAttendance = Participation::whereIn('resident_id', $residentIds)
                ->where('status', 'present')
                ->latest()
                ->first();

            $participationPct = $totalEvents > 0 && $memberCount > 0
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

    public function getResidentParticipationHistory(int $residentId)
    {
        return Participation::where('resident_id', $residentId)
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
}
