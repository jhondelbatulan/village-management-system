<?php

namespace App\Services\Analytics;

use App\Models\Event;
use App\Models\Participation;
use App\Models\Resident;
use App\Models\Village;

class ParticipationAnalyticsService
{
    public function getVillageStats(int $villageId): array
    {
        // Participations belong to the village when their event does, or when
        // the event targets all villages and the household lives here.
        $scoped = fn ($query) => $query->where(function ($q) use ($villageId) {
            $q->whereHas('event', fn ($e) => $e->where('village_id', $villageId))
              ->orWhere(function ($all) use ($villageId) {
                  $all->whereHas('event', fn ($e) => $e->whereNull('village_id'))
                      ->whereHas('household', fn ($h) => $h->where('village_id', $villageId));
              });
        });

        $totalEvents = Event::forVillage($villageId)->count();
        $completedEvents = Event::forVillage($villageId)->where('status', 'completed')->count();
        $totalParticipations = $scoped(Participation::query())->count();
        $presentCount = $scoped(Participation::query())->where('status', 'present')->count();
        $absentCount = $scoped(Participation::query())->where('status', 'absent')->count();
        $lateCount = $scoped(Participation::query())->where('status', 'late')->count();
        $excusedCount = $scoped(Participation::query())->where('status', 'excused')->count();
        $totalResidents = Resident::where('village_id', $villageId)->where('status', 'active')->count();
        $attendanceRate = $totalParticipations > 0 ? round(($presentCount / $totalParticipations) * 100, 1) : 0;

        return [
            'total_events' => $totalEvents,
            'completed_events' => $completedEvents,
            'total_participants' => $totalParticipations,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'excused' => $excusedCount,
            'total_residents' => $totalResidents,
            'attendance_rate' => $attendanceRate,
        ];
    }

    public function getSystemStats(): array
    {
        $totalEvents = Event::count();
        $totalParticipations = Participation::count();
        $presentCount = Participation::where('status', 'present')->count();
        $attendanceRate = $totalParticipations > 0 ? round(($presentCount / $totalParticipations) * 100, 1) : 0;

        return [
            'total_events' => $totalEvents,
            'total_participants' => $totalParticipations,
            'present' => $presentCount,
            'attendance_rate' => $attendanceRate,
        ];
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();

        return $villages->map(function ($village) {
            $stats = $this->getVillageStats($village->id);
            return [
                'village' => $village,
                'total_events' => $stats['total_events'],
                'attendance_rate' => $stats['attendance_rate'],
                'total_participants' => $stats['total_participants'],
            ];
        })->toArray();
    }
}
