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
        $totalEvents = Event::where('village_id', $villageId)->count();
        $completedEvents = Event::where('village_id', $villageId)->where('status', 'completed')->count();
        $totalParticipations = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->count();
        $presentCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'present')->count();
        $absentCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'absent')->count();
        $lateCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'late')->count();
        $excusedCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'excused')->count();
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
