<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Participation;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

class GisDashboardService
{
    public function getComprehensiveVillageData(int $villageId): array
    {
        $village = Village::with([
            'officers' => fn($q) => $q->where('status', 'active'),
            'paymentConfiguration',
        ])->find($villageId);

        if (!$village) {
            return ['error' => 'Village not found'];
        }

        return [
            'village_profile' => $this->getVillageProfile($village),
            'population' => $this->getPopulationStats($villageId),
            'demographics' => $this->getDemographics($villageId),
            'socio_economic' => $this->getSocioEconomicStats($villageId),
            'healthcare' => $this->getHealthcareStats($villageId),
            'government_assistance' => $this->getGovernmentAssistanceStats($villageId),
            'participation' => $this->getParticipationStats($villageId),
            'payment' => $this->getPaymentStats($villageId, $village),
            'households' => $this->getHouseholdMarkers($villageId, $village),
        ];
    }

    public function getSystemOverviewData(): array
    {
        return [
            'villages_summary' => $this->getAllVillagesSummary(),
            'population' => $this->getSystemPopulationStats(),
            'payment' => $this->getSystemPaymentStats(),
        ];
    }

    public function getVillageProfile(Village $village): array
    {
        $president = $village->officers()->where('position', 'like', '%President%')->first();
        $secretary = $village->officers()->where('position', 'like', '%Secretary%')->first();
        $treasurer = $village->officers()->where('position', 'like', '%Treasurer%')->first();

        return [
            'id' => $village->id,
            'name' => $village->village_name,
            'code' => $village->village_code,
            'address' => $village->address,
            'established_date' => $village->established_date?->format('M d, Y'),
            'latitude' => $village->latitude,
            'longitude' => $village->longitude,
            'president' => $president?->full_name ?? 'N/A',
            'secretary' => $secretary?->full_name ?? 'N/A',
            'treasurer' => $treasurer?->full_name ?? 'N/A',
            'logo_url' => $village->logo_url,
            'description' => $village->description,
            'payment_enabled' => $village->isPaymentEnabled(),
        ];
    }

    public function getPopulationStats(int $villageId): array
    {
        $totalResidents = Resident::where('village_id', $villageId)->where('status', 'active')->count();
        $totalHouseholds = Household::where('village_id', $villageId)->count();
        $activeHouseholds = Household::where('village_id', $villageId)->where('status', 'active')->count();
        $avgHouseholdSize = $activeHouseholds > 0 ? round($totalResidents / $activeHouseholds, 1) : 0;

        return [
            'total_population' => $totalResidents,
            'registered_residents' => $totalResidents,
            'resident_accounts' => Resident::where('village_id', $villageId)->count(),
            'households' => $totalHouseholds,
            'average_household_size' => $avgHouseholdSize,
            'pending_residents' => Resident::where('village_id', $villageId)->whereIn('approval_status', ['pending', 'admin_approved'])->count(),
            'approved_residents' => Resident::where('village_id', $villageId)->where('approval_status', 'approved')->count(),
            'rejected_residents' => Resident::where('village_id', $villageId)->where('approval_status', 'rejected')->count(),
            'pending_households' => Household::where('village_id', $villageId)->whereIn('approval_status', ['pending', 'admin_approved'])->count(),
            'approved_households' => Household::where('village_id', $villageId)->where('approval_status', 'approved')->count(),
            'rejected_households' => Household::where('village_id', $villageId)->where('approval_status', 'rejected')->count(),
        ];
    }

    public function getDemographics(int $villageId): array
    {
        $base = Resident::where('village_id', $villageId)->where('status', 'active');

        return [
            'male' => (clone $base)->where('gender', 'Male')->count(),
            'female' => (clone $base)->where('gender', 'Female')->count(),
            'children' => (clone $base)->whereRaw('age < 15')->count(),
            'youth' => (clone $base)->whereBetween('age', [15, 30])->count(),
            'adults' => (clone $base)->whereBetween('age', [31, 59])->count(),
            'senior_citizens' => (clone $base)->where('is_senior_citizen', true)->count(),
            'pwd' => (clone $base)->where('is_pwd', true)->count(),
            'solo_parents' => (clone $base)->where('is_solo_parent', true)->count(),
            'pregnant_women' => 0,
            'indigenous_people' => (clone $base)->where('is_indigenous', true)->count(),
        ];
    }

    public function getSocioEconomicStats(int $villageId): array
    {
        $base = Resident::where('village_id', $villageId)->where('status', 'active');
        $total = (clone $base)->count();

        $employed = (clone $base)->where('employment_status', 'employed')->count();
        $selfEmployed = (clone $base)->where('employment_status', 'self-employed')->count();
        $retired = (clone $base)->where('employment_status', 'retired')->count();
        $students = (clone $base)->where('employment_status', 'student')->count();
        $unemployed = $total - $employed - $selfEmployed - $retired - $students;
        if ($unemployed < 0) {
            $unemployed = (clone $base)->where('employment_status', 'unemployed')->count();
        }

        $employmentRate = $total > 0 ? round((($employed + $selfEmployed) / $total) * 100, 1) : 0;
        $unemploymentRate = $total > 0 ? round(($unemployed / $total) * 100, 1) : 0;

        $avgIncome = (clone $base)->whereNotNull('monthly_income')->where('monthly_income', '>', 0)->avg('monthly_income');

        $topOccupation = (clone $base)->whereNotNull('occupation')->where('occupation', '!=', '')
            ->select('occupation', DB::raw('count(*) as count'))
            ->groupBy('occupation')->orderByDesc('count')->first();

        $topSkill = (clone $base)->whereNotNull('primary_skill')->where('primary_skill', '!=', '')
            ->select('primary_skill', DB::raw('count(*) as count'))
            ->groupBy('primary_skill')->orderByDesc('count')->first();

        $topEducation = (clone $base)->whereNotNull('educational_attainment')->where('educational_attainment', '!=', '')
            ->select('educational_attainment', DB::raw('count(*) as count'))
            ->groupBy('educational_attainment')->orderByDesc('count')->first();

        return [
            'employment_rate' => $employmentRate,
            'unemployment_rate' => $unemploymentRate,
            'students' => $students,
            'self_employed' => $selfEmployed,
            'retired' => $retired,
            'average_monthly_income' => $avgIncome ? '₱' . number_format($avgIncome, 2) : 'N/A',
            'most_common_occupation' => $topOccupation?->occupation ?? 'N/A',
            'most_common_skill' => $topSkill?->primary_skill ?? 'N/A',
            'highest_educational_attainment' => $topEducation?->educational_attainment ?? 'N/A',
        ];
    }

    public function getHealthcareStats(int $villageId): array
    {
        $base = Resident::where('village_id', $villageId)->where('status', 'active');

        return [
            'philhealth_members' => (clone $base)->where('is_philhealth_member', true)->count(),
            'pwd' => (clone $base)->where('is_pwd', true)->count(),
            'senior_citizens' => (clone $base)->where('is_senior_citizen', true)->count(),
            'pregnant_women' => 0,
            'healthcare_assistance' => (clone $base)->where('is_philhealth_member', true)->count(),
        ];
    }

    public function getGovernmentAssistanceStats(int $villageId): array
    {
        $base = Resident::where('village_id', $villageId)->where('status', 'active');

        return [
            'four_ps' => (clone $base)->where('is_4ps_member', true)->count(),
            'scholarship' => (clone $base)->where('is_scholarship', true)->count(),
            'livelihood_program' => (clone $base)->where('is_tupad_beneficiary', true)->count(),
            'senior_pension' => (clone $base)->where('is_senior_pensioner', true)->count(),
            'pwd_assistance' => (clone $base)->where('is_akap_beneficiary', true)->count(),
            'other_assistance' => (clone $base)->where('other_gov_assistance', '!=', '')->whereNotNull('other_gov_assistance')->count(),
        ];
    }

    public function getParticipationStats(int $villageId): array
    {
        $totalEvents = \App\Models\Event::where('village_id', $villageId)->count();
        $completedEvents = \App\Models\Event::where('village_id', $villageId)->where('status', 'completed')->count();
        $totalParticipations = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->count();
        $presentCount = Participation::whereHas('event', fn($q) => $q->where('village_id', $villageId))->where('status', 'present')->count();
        $attendanceRate = $totalParticipations > 0 ? round(($presentCount / $totalParticipations) * 100, 1) : 0;

        $totalResidents = Resident::where('village_id', $villageId)->where('status', 'active')->count();
        $participationRate = $totalResidents > 0 ? round(($totalParticipations / $totalResidents) * 100, 1) : 0;

        $mostActiveHousehold = Household::where('village_id', $villageId)
            ->withCount(['participations as participation_count'])
            ->orderByDesc('participation_count')
            ->first();

        $mostActiveResident = Resident::where('village_id', $villageId)->where('status', 'active')
            ->withCount(['participations as participation_count'])
            ->orderByDesc('participation_count')
            ->first();

        return [
            'participation_rate' => $participationRate,
            'community_events' => $totalEvents,
            'attendance_rate' => $attendanceRate,
            'most_active_household' => $mostActiveHousehold ? $mostActiveHousehold->household_head_name : 'N/A',
            'most_active_resident' => $mostActiveResident ? $mostActiveResident->full_name : 'N/A',
            'total_participants' => $totalParticipations,
            'present' => $presentCount,
        ];
    }

    public function getPaymentStats(int $villageId, Village $village): array
    {
        if (!$village->isPaymentEnabled()) {
            return ['enabled' => false];
        }

        $totalBillings = Billing::where('village_id', $villageId)->count();
        $totalAmount = Billing::where('village_id', $villageId)->sum('total_amount');

        $paidBillings = Billing::where('village_id', $villageId)->where('status', 'paid')->count();
        $partialBillings = Billing::where('village_id', $villageId)->where('status', 'partially_paid')->count();
        $unpaidBillings = Billing::where('village_id', $villageId)->whereIn('status', ['pending', 'overdue'])->count();

        $collectedAmount = HouseholdPayment::where('village_id', $villageId)
            ->where('payment_status', '!=', 'cancelled')
            ->sum('total_paid');

        $outstandingBalance = max(0, (float) $totalAmount - (float) $collectedAmount);
        $collectionPercentage = $totalAmount > 0 ? round((($collectedAmount / $totalAmount) * 100), 1) : 0;

        $paidHouseholds = Billing::where('village_id', $villageId)->where('status', 'paid')
            ->distinct('household_id')->count('household_id');
        $partialHouseholds = Billing::where('village_id', $villageId)->where('status', 'partially_paid')
            ->distinct('household_id')->count('household_id');
        $totalUniqueBillHouseholds = Billing::where('village_id', $villageId)
            ->distinct('household_id')->count('household_id');
        $unpaidHouseholds = $totalUniqueBillHouseholds - $paidHouseholds - $partialHouseholds;
        if ($unpaidHouseholds < 0) {
            $unpaidHouseholds = 0;
        }

        $excellentPayers = Household::where('village_id', $villageId)->where('payment_behavior', 'excellent')->count();
        $goodPayers = Household::where('village_id', $villageId)->where('payment_behavior', 'good')->count();
        $moderatePayers = Household::where('village_id', $villageId)->where('payment_behavior', 'moderate')->count();
        $highRiskPayers = Household::where('village_id', $villageId)->where('payment_behavior', 'high_risk')->count();

        return [
            'enabled' => true,
            'collection_percentage' => $collectionPercentage,
            'collected_amount' => '₱' . number_format((float) $collectedAmount, 2),
            'outstanding_balance' => '₱' . number_format($outstandingBalance, 2),
            'paid_households' => $paidHouseholds,
            'partial_payments' => $partialHouseholds,
            'unpaid_households' => $unpaidHouseholds,
            'excellent_payers' => $excellentPayers,
            'good_payers' => $goodPayers,
            'moderate_payers' => $moderatePayers,
            'high_risk_payers' => $highRiskPayers,
        ];
    }

    public function getHouseholdMarkers(int $villageId, Village $village): array
    {
        $paymentEnabled = $village->isPaymentEnabled();

        $households = Household::where('village_id', $villageId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with([
                'village:id,village_name,village_code',
                'residents' => fn($q) => $q->where('status', 'active')->select('id', 'household_id', 'first_name', 'last_name'),
            ])
            ->select([
                'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                'village_id', 'address', 'purok', 'payment_behavior', 'payment_reliability_score',
                'prediction_confidence',
            ])
            ->orderBy('household_number')
            ->get();

        if ($paymentEnabled) {
            $billingStatuses = Billing::where('village_id', $villageId)
                ->select('household_id', 'status')
                ->get()
                ->groupBy('household_id')
                ->map(function ($billings) {
                    if ($billings->contains('status', 'paid')) return 'paid';
                    if ($billings->contains('status', 'partially_paid')) return 'partial';
                    return 'unpaid';
                });

            foreach ($households as $h) {
                $h->payment_status = $billingStatuses->get($h->id, 'no_billing');
                $h->member_count = $h->residents->count();
            }
        } else {
            foreach ($households as $h) {
                $h->payment_status = 'disabled';
                $h->member_count = $h->residents->count();
            }
        }

        return $households->toArray();
    }

    public function getAllVillagesSummary(): array
    {
        return Village::select(['id', 'village_name', 'village_code', 'latitude', 'longitude', 'address'])
            ->withCount([
                'households as household_count',
                'residents as resident_count' => fn($q) => $q->where('status', 'active'),
            ])
            ->orderBy('village_name')
            ->get()
            ->toArray();
    }

    public function getSystemPopulationStats(): array
    {
        $totalResidents = Resident::where('status', 'active')->count();
        $totalHouseholds = Household::count();
        $activeHouseholds = Household::where('status', 'active')->count();
        $avgSize = $activeHouseholds > 0 ? round($totalResidents / $activeHouseholds, 1) : 0;

        return [
            'total_population' => $totalResidents,
            'households' => $totalHouseholds,
            'average_household_size' => $avgSize,
        ];
    }

    public function getSystemPaymentStats(): array
    {
        $totalAmount = Billing::sum('total_amount');
        $collectedAmount = HouseholdPayment::where('payment_status', '!=', 'cancelled')->sum('total_paid');
        $outstanding = max(0, (float) $totalAmount - (float) $collectedAmount);
        $collectionPct = $totalAmount > 0 ? round((($collectedAmount / $totalAmount) * 100), 1) : 0;

        return [
            'collection_percentage' => $collectionPct,
            'collected_amount' => '₱' . number_format((float) $collectedAmount, 2),
            'outstanding_balance' => '₱' . number_format($outstanding, 2),
            'total_billings' => Billing::count(),
        ];
    }
}
