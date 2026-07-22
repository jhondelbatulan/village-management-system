<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Village;
use App\Models\Resident;
use App\Models\Household;
use App\Services\EventService;
use Illuminate\View\View;

class ParticipationController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $user->village_id;

        if ($role === 'super_admin') {
            $stats = $this->eventService->getVillageComparisonStats();
            $villages = Village::where('status', 'active')->get();

            return view('participation.index', compact('stats', 'villages', 'role'));
        }

        $village = Village::find($villageId);
        $participationStats = $this->eventService->getParticipationStats($villageId);
        $householdStats = $this->eventService->getHouseholdParticipationStats($villageId);

        $recentEvents = Event::where('village_id', $villageId)
            ->withCount(['participations', 'responses'])
            ->latest()
            ->take(10)
            ->get();

        $activeHouseholds = collect($householdStats)->filter(fn($s) => $s['participation_pct'] >= 50);
        $inactiveHouseholds = collect($householdStats)->filter(fn($s) => $s['participation_pct'] < 50);

        return view('participation.index', compact('village', 'participationStats', 'householdStats', 'recentEvents', 'activeHouseholds', 'inactiveHouseholds', 'role'));
    }

    public function household(int $householdId): View
    {
        $household = Household::with(['village', 'residents'])->findOrFail($householdId);
        $residentIds = $household->residents->pluck('id');

        $participations = \App\Models\Participation::whereIn('resident_id', $residentIds)
            ->with(['event', 'resident'])
            ->latest()
            ->paginate(20);

        $totalEvents = Event::where('village_id', $household->village_id)->where('status', 'completed')->count();
        $attendedEvents = \App\Models\Participation::whereIn('resident_id', $residentIds)
            ->where('status', 'present')
            ->distinct('event_id')
            ->count('event_id');

        $stats = [
            'total_events' => $totalEvents,
            'attended' => $attendedEvents,
            'participation_pct' => $totalEvents > 0 ? round(($attendedEvents / $totalEvents) * 100, 1) : 0,
            'member_count' => $household->residents()->where('status', 'active')->count(),
        ];

        return view('participation.household', compact('household', 'participations', 'stats'));
    }

    public function residentHistory(int $residentId): View
    {
        $resident = Resident::with(['village', 'household'])->findOrFail($residentId);

        $participations = $this->eventService->getResidentParticipationHistory($residentId);

        return view('participation.history', compact('resident', 'participations'));
    }
}
