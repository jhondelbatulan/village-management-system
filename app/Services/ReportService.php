<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Billing;
use App\Models\Event;
use App\Models\GeneratedReport;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Participation;
use App\Models\Resident;
use App\Models\Village;
use App\Services\Analytics\AnnouncementAnalyticsService;
use App\Services\Analytics\DemographicAnalyticsService;
use App\Services\Analytics\EducationAnalyticsService;
use App\Services\Analytics\EmploymentAnalyticsService;
use App\Services\Analytics\ExecutiveIntelligenceService;
use App\Services\Analytics\GISAnalyticsService;
use App\Services\Analytics\GovernmentAssistanceAnalyticsService;
use App\Services\Analytics\GovernmentMembershipAnalyticsService;
use App\Services\Analytics\HealthcareAnalyticsService;
use App\Services\Analytics\HouseholdAnalyticsService;
use App\Services\Analytics\OccupationAnalyticsService;
use App\Services\Analytics\ParticipationAnalyticsService;
use App\Services\Analytics\PaymentAnalyticsService;
use App\Services\Analytics\PaymentBehaviorService;
use App\Services\Analytics\PaymentReliabilityService;
use App\Services\Analytics\PredictionAnalyticsService;
use App\Services\Analytics\PopulationAnalyticsService;
use App\Services\Analytics\SkillsAnalyticsService;
use App\Services\Analytics\SocioEconomicAnalyticsService;
use App\Services\Analytics\VoterRegistrationAnalyticsService;
use App\Services\Analytics\SocialWelfareAnalyticsService;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public const CATEGORIES = [
        'resident' => 'Resident Reports',
        'household' => 'Household Reports',
        'payment' => 'Payment Reports',
        'participation' => 'Participation Reports',
        'announcement' => 'Announcement Reports',
        'gis' => 'GIS Reports',
        'analytics' => 'Analytics Reports',
        'executive' => 'Executive Reports',
    ];

    public const REPORT_TYPES = [
        'resident_master_list' => ['name' => 'Resident Master List', 'category' => 'resident', 'icon' => 'bi-people', 'color' => 'primary', 'description' => 'Complete listing of all registered residents with personal details.'],
        'resident_profile' => ['name' => 'Resident Profile Report', 'category' => 'resident', 'icon' => 'bi-person-lines-fill', 'color' => 'info', 'description' => 'Detailed individual resident profiles.'],
        'resident_demographic' => ['name' => 'Resident Demographic Report', 'category' => 'resident', 'icon' => 'bi-diagram-3', 'color' => 'secondary', 'description' => 'Demographic breakdown of the resident population.'],
        'age_distribution' => ['name' => 'Age Distribution Report', 'category' => 'resident', 'icon' => 'bi-calendar3', 'color' => 'warning', 'description' => 'Population distribution by age groups.'],
        'gender_distribution' => ['name' => 'Gender Distribution Report', 'category' => 'resident', 'icon' => 'bi-gender-ambiguous', 'color' => 'info', 'description' => 'Gender distribution analysis.'],
        'civil_status' => ['name' => 'Civil Status Report', 'category' => 'resident', 'icon' => 'bi-heart', 'color' => 'danger', 'description' => 'Civil status distribution of residents.'],
        'education' => ['name' => 'Educational Attainment Report', 'category' => 'resident', 'icon' => 'bi-mortarboard', 'color' => 'primary', 'description' => 'Educational attainment levels of residents.'],
        'employment' => ['name' => 'Employment Report', 'category' => 'resident', 'icon' => 'bi-briefcase', 'color' => 'success', 'description' => 'Employment status and job analysis.'],
        'occupation' => ['name' => 'Occupation Report', 'category' => 'resident', 'icon' => 'bi-person-badge', 'color' => 'info', 'description' => 'Occupation distribution and rankings.'],
        'income' => ['name' => 'Income Report', 'category' => 'resident', 'icon' => 'bi-cash', 'color' => 'warning', 'description' => 'Income distribution and analysis.'],
        'skills' => ['name' => 'Skills Report', 'category' => 'resident', 'icon' => 'bi-tools', 'color' => 'secondary', 'description' => 'Skills inventory and distribution.'],
        'gov_assistance' => ['name' => 'Government Assistance Report', 'category' => 'resident', 'icon' => 'bi-building-check', 'color' => 'danger', 'description' => 'Government assistance program beneficiaries.'],
        'healthcare' => ['name' => 'Healthcare Coverage Report', 'category' => 'resident', 'icon' => 'bi-heart-pulse', 'color' => 'danger', 'description' => 'Healthcare coverage and membership.'],
        'pwd' => ['name' => 'PWD Report', 'category' => 'resident', 'icon' => 'bi-universal-access', 'color' => 'info', 'description' => 'Persons with disabilities report.'],
        'senior_citizen' => ['name' => 'Senior Citizen Report', 'category' => 'resident', 'icon' => 'bi-people', 'color' => 'secondary', 'description' => 'Senior citizen population report.'],
        'solo_parent' => ['name' => 'Solo Parent Report', 'category' => 'resident', 'icon' => 'bi-person', 'color' => 'warning', 'description' => 'Solo parent population report.'],
        'youth' => ['name' => 'Youth Report', 'category' => 'resident', 'icon' => 'bi-person-fill', 'color' => 'success', 'description' => 'Youth population report (15-30 years old).'],
        'household_master_list' => ['name' => 'Household Master List', 'category' => 'household', 'icon' => 'bi-house-door', 'color' => 'success', 'description' => 'Complete listing of all households.'],
        'block_lot' => ['name' => 'Block and Lot Report', 'category' => 'household', 'icon' => 'bi-grid-3x3', 'color' => 'info', 'description' => 'Household distribution by block and lot.'],
        'household_composition' => ['name' => 'Household Composition Report', 'category' => 'household', 'icon' => 'bi-people', 'color' => 'primary', 'description' => 'Household member composition analysis.'],
        'household_size' => ['name' => 'Household Size Report', 'category' => 'household', 'icon' => 'bi-bar-chart', 'color' => 'warning', 'description' => 'Household size distribution.'],
        'household_occupancy' => ['name' => 'Household Occupancy Report', 'category' => 'household', 'icon' => 'bi-house', 'color' => 'info', 'description' => 'Household occupancy and status.'],
        'household_payment_status' => ['name' => 'Household Payment Status Report', 'category' => 'household', 'icon' => 'bi-credit-card', 'color' => 'warning', 'description' => 'Household payment status overview.'],
        'household_payment_behavior' => ['name' => 'Household Payment Behavior Report', 'category' => 'household', 'icon' => 'bi-graph-up-arrow', 'color' => 'danger', 'description' => 'Household payment behavior and reliability.'],
        'collection' => ['name' => 'Collection Report', 'category' => 'payment', 'icon' => 'bi-cash-stack', 'color' => 'success', 'description' => 'Payment collection summary.'],
        'monthly_collection' => ['name' => 'Monthly Collection Report', 'category' => 'payment', 'icon' => 'bi-calendar-month', 'color' => 'primary', 'description' => 'Monthly payment collection breakdown.'],
        'annual_collection' => ['name' => 'Annual Collection Report', 'category' => 'payment', 'icon' => 'bi-calendar-year', 'color' => 'info', 'description' => 'Annual payment collection summary.'],
        'outstanding_balance' => ['name' => 'Outstanding Balance Report', 'category' => 'payment', 'icon' => 'bi-exclamation-circle', 'color' => 'danger', 'description' => 'Outstanding balances by household.'],
        'partial_payment' => ['name' => 'Partial Payment Report', 'category' => 'payment', 'icon' => 'bi-pie-chart', 'color' => 'warning', 'description' => 'Partial payment records and analysis.'],
        'payment_history' => ['name' => 'Payment History Report', 'category' => 'payment', 'icon' => 'bi-clock-history', 'color' => 'info', 'description' => 'Complete payment transaction history.'],
        'payment_reliability' => ['name' => 'Payment Reliability Report', 'category' => 'payment', 'icon' => 'bi-shield-check', 'color' => 'success', 'description' => 'Payment reliability scores and classification.'],
        'payment_prediction' => ['name' => 'Payment Prediction Report', 'category' => 'payment', 'icon' => 'bi-graph-up-arrow', 'color' => 'primary', 'description' => 'Payment behavior predictions and risk analysis.'],
        'delinquent' => ['name' => 'Delinquent Household Report', 'category' => 'payment', 'icon' => 'bi-exclamation-diamond', 'color' => 'danger', 'description' => 'Households with overdue or unpaid bills.'],
        'attendance' => ['name' => 'Attendance Report', 'category' => 'participation', 'icon' => 'bi-clipboard-check', 'color' => 'primary', 'description' => 'Event attendance records.'],
        'participation_summary' => ['name' => 'Participation Summary Report', 'category' => 'participation', 'icon' => 'bi-bar-chart-line', 'color' => 'success', 'description' => 'Overall participation statistics.'],
        'event_participation' => ['name' => 'Event Participation Report', 'category' => 'participation', 'icon' => 'bi-calendar-event', 'color' => 'info', 'description' => 'Per-event participation breakdown.'],
        'volunteer' => ['name' => 'Volunteer Participation Report', 'category' => 'participation', 'icon' => 'bi-hand-thumbs-up', 'color' => 'warning', 'description' => 'Volunteer activity and engagement.'],
        'community_engagement' => ['name' => 'Community Engagement Report', 'category' => 'participation', 'icon' => 'bi-people-fill', 'color' => 'secondary', 'description' => 'Community engagement metrics.'],
        'announcement_history' => ['name' => 'Announcement History Report', 'category' => 'announcement', 'icon' => 'bi-megaphone', 'color' => 'primary', 'description' => 'Complete announcement history.'],
        'announcement_engagement' => ['name' => 'Announcement Engagement Report', 'category' => 'announcement', 'icon' => 'bi-chat-dots', 'color' => 'info', 'description' => 'Announcement response and engagement.'],
        'announcement_archive' => ['name' => 'Announcement Archive Report', 'category' => 'announcement', 'icon' => 'bi-archive', 'color' => 'secondary', 'description' => 'Archived announcements.'],
        'gis_summary' => ['name' => 'Village GIS Summary', 'category' => 'gis', 'icon' => 'bi-map', 'color' => 'info', 'description' => 'GIS mapping summary and statistics.'],
        'gis_location' => ['name' => 'Village Location Report', 'category' => 'gis', 'icon' => 'bi-geo-alt', 'color' => 'success', 'description' => 'Village location and geographic data.'],
        'gis_analytics' => ['name' => 'GIS Analytics Summary', 'category' => 'gis', 'icon' => 'bi-pin-map', 'color' => 'primary', 'description' => 'GIS analytics and spatial data.'],
        'gis_comparison' => ['name' => 'Village Comparison Report', 'category' => 'gis', 'icon' => 'bi-geo', 'color' => 'warning', 'description' => 'Village comparison with geographic context.'],
        'analytics_population' => ['name' => 'Population Analytics Report', 'category' => 'analytics', 'icon' => 'bi-people', 'color' => 'primary', 'description' => 'Detailed population analytics.'],
        'analytics_household' => ['name' => 'Household Analytics Report', 'category' => 'analytics', 'icon' => 'bi-house-door', 'color' => 'success', 'description' => 'Detailed household analytics.'],
        'analytics_employment' => ['name' => 'Employment Analytics Report', 'category' => 'analytics', 'icon' => 'bi-briefcase', 'color' => 'info', 'description' => 'Employment analytics and trends.'],
        'analytics_income' => ['name' => 'Income Analytics Report', 'category' => 'analytics', 'icon' => 'bi-cash', 'color' => 'warning', 'description' => 'Income analytics and distribution.'],
        'analytics_skills' => ['name' => 'Skills Analytics Report', 'category' => 'analytics', 'icon' => 'bi-tools', 'color' => 'secondary', 'description' => 'Skills analytics and inventory.'],
        'analytics_gov_assistance' => ['name' => 'Gov. Assistance Analytics Report', 'category' => 'analytics', 'icon' => 'bi-building-check', 'color' => 'danger', 'description' => 'Government assistance analytics.'],
        'analytics_healthcare' => ['name' => 'Healthcare Analytics Report', 'category' => 'analytics', 'icon' => 'bi-heart-pulse', 'color' => 'danger', 'description' => 'Healthcare coverage analytics.'],
        'analytics_participation' => ['name' => 'Participation Analytics Report', 'category' => 'analytics', 'icon' => 'bi-people-fill', 'color' => 'secondary', 'description' => 'Participation analytics and trends.'],
        'analytics_executive' => ['name' => 'Executive Analytics Summary', 'category' => 'analytics', 'icon' => 'bi-lightbulb', 'color' => 'primary', 'description' => 'Executive-level analytics summary.'],
        'executive_summary' => ['name' => 'Executive Summary Report', 'category' => 'executive', 'icon' => 'bi-file-earmark-text', 'color' => 'primary', 'description' => 'Comprehensive executive summary across all modules.'],
    ];

    public function __construct(
        protected PopulationAnalyticsService $population,
        protected HouseholdAnalyticsService $householdAnalytics,
        protected GovernmentAssistanceAnalyticsService $govAssistance,
        protected PaymentAnalyticsService $paymentAnalytics,
        protected ParticipationAnalyticsService $participationAnalytics,
        protected PredictionAnalyticsService $predictionAnalytics,
        protected PaymentReliabilityService $reliabilityService,
        protected EmploymentAnalyticsService $employmentAnalytics,
        protected HealthcareAnalyticsService $healthcareAnalytics,
        protected EducationAnalyticsService $educationAnalytics,
        protected OccupationAnalyticsService $occupationAnalytics,
        protected SkillsAnalyticsService $skillsAnalytics,
        protected AnnouncementAnalyticsService $announcementAnalytics,
        protected GISAnalyticsService $gisAnalytics,
        protected DemographicAnalyticsService $demographicAnalytics,
        protected ExecutiveIntelligenceService $executiveIntelligence,
        protected SocioEconomicAnalyticsService $socioEconomic,
        protected GovernmentMembershipAnalyticsService $govMembership,
        protected VoterRegistrationAnalyticsService $voterRegistration,
        protected SocialWelfareAnalyticsService $socialWelfare,
    ) {}

    public function getReportTypes(): array
    {
        return self::REPORT_TYPES;
    }

    public function getCategories(): array
    {
        return self::CATEGORIES;
    }

    public function getReportsByCategory(): array
    {
        $grouped = [];
        foreach (self::CATEGORIES as $key => $label) {
            $grouped[$key] = [
                'label' => $label,
                'reports' => array_filter(self::REPORT_TYPES, fn($r) => $r['category'] === $key),
            ];
        }
        return $grouped;
    }

    public function getReportData(string $reportType, ?int $villageId, array $filters = []): array
    {
        $village = $villageId ? Village::findOrFail($villageId) : null;
        $villageName = $village?->village_name ?? 'All Villages';
        $generatedAt = now()->format('F d, Y h:i A');
        $user = auth()->user();
        $generatedBy = $user?->full_name ?? $user?->name ?? 'System';
        $reportInfo = self::REPORT_TYPES[$reportType] ?? ['name' => ucfirst(str_replace('_', ' ', $reportType))];

        $base = compact('village', 'villageName', 'generatedAt', 'generatedBy', 'reportType') + [
            'reportTitle' => $reportInfo['name'],
        ];

        return match ($reportType) {
            'resident_master_list' => $base + $this->residentMasterList($villageId, $filters),
            'resident_profile' => $base + $this->residentProfile($villageId, $filters),
            'resident_demographic' => $base + $this->residentDemographic($villageId),
            'age_distribution' => $base + $this->ageDistribution($villageId),
            'gender_distribution' => $base + $this->genderDistribution($villageId),
            'civil_status' => $base + $this->civilStatusDistribution($villageId),
            'education' => $base + $this->educationReport($villageId),
            'employment' => $base + $this->employmentReport($villageId),
            'occupation' => $base + $this->occupationReport($villageId),
            'income' => $base + $this->incomeReport($villageId),
            'skills' => $base + $this->skillsReport($villageId),
            'gov_assistance' => $base + $this->govAssistanceReport($villageId),
            'healthcare' => $base + $this->healthcareReport($villageId),
            'pwd' => $base + $this->pwdReport($villageId),
            'senior_citizen' => $base + $this->seniorCitizenReport($villageId),
            'solo_parent' => $base + $this->soloParentReport($villageId),
            'youth' => $base + $this->youthReport($villageId),
            'household_master_list' => $base + $this->householdMasterList($villageId, $filters),
            'block_lot' => $base + $this->blockLotReport($villageId),
            'household_composition' => $base + $this->householdComposition($villageId),
            'household_size' => $base + $this->householdSizeReport($villageId),
            'household_occupancy' => $base + $this->householdOccupancyReport($villageId),
            'household_payment_status' => $base + $this->householdPaymentStatusReport($villageId),
            'household_payment_behavior' => $base + $this->householdPaymentBehaviorReport($villageId),
            'collection' => $base + $this->collectionReport($villageId),
            'monthly_collection' => $base + $this->monthlyCollectionReport($villageId),
            'annual_collection' => $base + $this->annualCollectionReport($villageId),
            'outstanding_balance' => $base + $this->outstandingBalanceReport($villageId),
            'partial_payment' => $base + $this->partialPaymentReport($villageId),
            'payment_history' => $base + $this->paymentHistoryReport($villageId, $filters),
            'payment_reliability' => $base + $this->paymentReliabilityReport($villageId),
            'payment_prediction' => $base + $this->paymentPredictionReport($villageId),
            'delinquent' => $base + $this->delinquentReport($villageId),
            'attendance' => $base + $this->attendanceReport($villageId, $filters),
            'participation_summary' => $base + $this->participationSummaryReport($villageId),
            'event_participation' => $base + $this->eventParticipationReport($villageId),
            'volunteer' => $base + $this->volunteerReport($villageId),
            'community_engagement' => $base + $this->communityEngagementReport($villageId),
            'announcement_history' => $base + $this->announcementHistoryReport($villageId),
            'announcement_engagement' => $base + $this->announcementEngagementReport($villageId),
            'announcement_archive' => $base + $this->announcementArchiveReport($villageId),
            'gis_summary' => $base + $this->gisSummaryReport($villageId),
            'gis_location' => $base + $this->gisLocationReport($villageId),
            'gis_analytics' => $base + $this->gisAnalyticsReport($villageId),
            'gis_comparison' => $base + $this->gisComparisonReport(),
            'analytics_population' => $base + $this->analyticsPopulationReport($villageId),
            'analytics_household' => $base + $this->analyticsHouseholdReport($villageId),
            'analytics_employment' => $base + $this->analyticsEmploymentReport($villageId),
            'analytics_income' => $base + $this->analyticsIncomeReport($villageId),
            'analytics_skills' => $base + $this->analyticsSkillsReport($villageId),
            'analytics_gov_assistance' => $base + $this->analyticsGovAssistanceReport($villageId),
            'analytics_healthcare' => $base + $this->analyticsHealthcareReport($villageId),
            'analytics_participation' => $base + $this->analyticsParticipationReport($villageId),
            'analytics_executive' => $base + $this->analyticsExecutiveReport($villageId),
            'executive_summary' => $base + $this->executiveSummaryReport($villageId),
            default => $base + ['rows' => [], 'summary' => []],
        };
    }

    public function recordGeneration(string $reportType, ?int $villageId, string $format, array $filters = []): GeneratedReport
    {
        $info = self::REPORT_TYPES[$reportType] ?? ['name' => $reportType, 'category' => 'misc'];
        return GeneratedReport::create([
            'user_id' => auth()->id(),
            'report_name' => $info['name'],
            'report_type' => $reportType,
            'category' => $info['category'],
            'village_id' => $villageId,
            'format' => $format,
            'filters' => $filters,
        ]);
    }

    public function getReportHistory($userId = null, int $limit = 50)
    {
        $query = GeneratedReport::with('user', 'village')->latest();
        if ($userId) $query->where('user_id', $userId);
        return $query->paginate($limit);
    }

    public function getScheduledReports()
    {
        return GeneratedReport::where('is_scheduled', true)
            ->where('is_active', true)
            ->with('user', 'village')
            ->get();
    }

    public function getFilters(): array
    {
        return Village::where('status', 'active')->orderBy('village_name')->get();
    }

    public function getAllowedReportTypes(string $role): array
    {
        return match ($role) {
            'super_admin' => array_keys(self::REPORT_TYPES),
            'admin' => array_keys(self::REPORT_TYPES),
            'secretary' => [
                'resident_master_list', 'resident_profile', 'resident_demographic',
                'age_distribution', 'gender_distribution', 'civil_status', 'education',
                'household_master_list', 'block_lot', 'household_composition',
                'household_size', 'household_occupancy',
                'attendance', 'participation_summary', 'event_participation',
                'volunteer', 'community_engagement',
                'announcement_history', 'announcement_engagement',
                'gis_summary', 'gis_location',
            ],
            'treasurer' => [
                'collection', 'monthly_collection', 'annual_collection',
                'outstanding_balance', 'partial_payment', 'payment_history',
                'payment_reliability', 'payment_prediction', 'delinquent',
                'household_payment_status', 'household_payment_behavior',
                'resident_master_list', 'household_master_list',
            ],
            'resident' => [],
            default => [],
        };
    }

    public function canAccessReport(string $role, string $reportType): bool
    {
        return in_array($reportType, $this->getAllowedReportTypes($role));
    }

    public function exportCsv(string $reportType, array $data): void
    {
        $filename = $reportType . '-' . now()->format('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [$data['reportTitle'] ?? $reportType]);
        fputcsv($handle, ['Village: ' . ($data['villageName'] ?? 'All')]);
        fputcsv($handle, ['Generated: ' . ($data['generatedAt'] ?? '')]);
        fputcsv($handle, ['By: ' . ($data['generatedBy'] ?? '')]);
        fputcsv($handle, []);

        $rows = $data['rows'] ?? [];
        if (!empty($rows)) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, array_values($row));
            }
        }

        fclose($handle);
        exit;
    }

    public function exportExcel(string $reportType, array $data): void
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $reportType . '-' . now()->format('Y-m-d-His') . '.xls"');
        echo '<table border="1">';
        echo '<tr><td colspan="5"><b>' . htmlspecialchars($data['reportTitle'] ?? $reportType) . '</b></td></tr>';
        echo '<tr><td>Village:</td><td colspan="4">' . htmlspecialchars($data['villageName'] ?? 'All') . '</td></tr>';
        echo '<tr><td>Generated:</td><td colspan="4">' . htmlspecialchars($data['generatedAt'] ?? '') . '</td></tr>';
        echo '<tr><td>By:</td><td colspan="4">' . htmlspecialchars($data['generatedBy'] ?? '') . '</td></tr>';
        echo '<tr></tr>';

        $rows = $data['rows'] ?? [];
        if (!empty($rows)) {
            echo '<tr>';
            foreach (array_keys($rows[0]) as $h) {
                echo '<th><b>' . htmlspecialchars($h) . '</b></th>';
            }
            echo '</tr>';
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . htmlspecialchars((string) ($cell ?? '')) . '</td>';
                }
                echo '</tr>';
            }
        }
        echo '</table>';
        exit;
    }

    // ====== RESIDENT REPORTS ======

    protected function residentMasterList(?int $villageId, array $filters): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->with('village')->orderBy('last_name')->orderBy('first_name');
        if ($villageId) $query->where('village_id', $villageId);
        if (!empty($filters['gender'])) $query->where('gender', $filters['gender']);
        if (!empty($filters['civil_status'])) $query->where('civil_status', $filters['civil_status']);
        if (!empty($filters['age_group'])) $this->applyAgeGroup($query, $filters['age_group']);
        $residents = $query->get();
        return ['rows' => $residents->map(fn($r) => [
            'Code' => $r->resident_code, 'Name' => $r->full_name, 'Gender' => $r->gender,
            'Age' => $r->age, 'Civil Status' => $r->civil_status, 'Village' => $r->village?->village_name ?? '',
            'Occupation' => $r->occupation ?? '', 'Education' => $r->educational_attainment ?? '',
        ])->toArray(), 'total' => $residents->count()];
    }

    protected function residentProfile(?int $villageId, array $filters): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->with('village', 'household')->orderBy('last_name');
        if ($villageId) $query->where('village_id', $villageId);
        $residents = $query->get();
        return ['rows' => $residents->map(fn($r) => [
            'Code' => $r->resident_code, 'Name' => $r->full_name, 'Gender' => $r->gender,
            'Age' => $r->age, 'Birthdate' => $r->birthdate?->format('M d, Y') ?? '',
            'Civil Status' => $r->civil_status, 'Village' => $r->village?->village_name ?? '',
            'Household' => $r->household?->household_number ?? '', 'Occupation' => $r->occupation ?? '',
            'Education' => $r->educational_attainment ?? '', 'Contact' => $r->contact_number ?? '',
            'Employment' => $r->employment_status ?? '', 'Income' => $r->monthly_income_formatted ?? '',
        ])->toArray(), 'total' => $residents->count()];
    }

    protected function residentDemographic(?int $villageId): array
    {
        return [
            'summary' => $this->population->getPopulationSummary($villageId),
            'genderDistribution' => $this->population->getGenderDistribution($villageId),
            'ageDistribution' => $this->population->getAgeDistribution($villageId),
            'civilStatus' => $this->population->getCivilStatusDistribution($villageId),
        ];
    }

    protected function ageDistribution(?int $villageId): array
    {
        $dist = $this->population->getAgeDistribution($villageId);
        $rows = [];
        foreach ($dist as $group => $info) {
            $rows[] = ['Age Group' => $group, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->population->getPopulationSummary($villageId)];
    }

    protected function genderDistribution(?int $villageId): array
    {
        $dist = $this->population->getGenderDistribution($villageId);
        $rows = [];
        foreach ($dist as $gender => $info) {
            $rows[] = ['Gender' => $gender, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->population->getPopulationSummary($villageId)];
    }

    protected function civilStatusDistribution(?int $villageId): array
    {
        $dist = $this->population->getCivilStatusDistribution($villageId);
        $rows = [];
        foreach ($dist as $status => $info) {
            $rows[] = ['Civil Status' => $status, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->population->getPopulationSummary($villageId)];
    }

    protected function educationReport(?int $villageId): array
    {
        $dist = $this->educationAnalytics->getAttainmentDistribution($villageId);
        $rows = [];
        foreach ($dist as $level => $info) {
            $rows[] = ['Education Level' => $level, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->educationAnalytics->getSummaryStats($villageId)];
    }

    protected function employmentReport(?int $villageId): array
    {
        $dist = $this->employmentAnalytics->getEmploymentStatusDistribution($villageId);
        $rows = [];
        foreach ($dist as $status => $info) {
            $rows[] = ['Employment Status' => $status, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->employmentAnalytics->getSummaryStats($villageId)];
    }

    protected function occupationReport(?int $villageId): array
    {
        $dist = $this->occupationAnalytics->getOccupationDistribution($villageId);
        $rows = [];
        foreach ($dist as $occ => $info) {
            $rows[] = ['Occupation' => $occ, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->occupationAnalytics->getSummaryStats($villageId)];
    }

    protected function incomeReport(?int $villageId): array
    {
        $range = $this->employmentAnalytics->getIncomeRangeDistribution($villageId);
        $source = $this->employmentAnalytics->getIncomeSourceDistribution($villageId);
        $rows = [];
        foreach ($range as $label => $info) {
            $rows[] = ['Income Range' => $label, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'incomeSource' => $source, 'summary' => $this->employmentAnalytics->getSummaryStats($villageId)];
    }

    protected function skillsReport(?int $villageId): array
    {
        $dist = $this->skillsAnalytics->getCombinedSkillDistribution($villageId);
        $rows = [];
        foreach ($dist as $skill => $info) {
            $rows[] = ['Skill' => $skill, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->skillsAnalytics->getSummaryStats($villageId)];
    }

    protected function govAssistanceReport(?int $villageId): array
    {
        $counts = $this->govAssistance->getProgramCounts($villageId);
        $rows = [];
        foreach ($counts as $program => $count) {
            $rows[] = ['Program' => $program, 'Beneficiaries' => $count];
        }
        return ['rows' => $rows, 'summary' => $this->govAssistance->getSummaryStats($villageId)];
    }

    protected function healthcareReport(?int $villageId): array
    {
        $coverage = $this->healthcareAnalytics->getCoverageDistribution($villageId);
        $rows = [];
        foreach ($coverage as $program => $info) {
            $rows[] = ['Program' => $program, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->healthcareAnalytics->getSummaryStats($villageId)];
    }

    protected function pwdReport(?int $villageId): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->where('is_pwd', true)->with('village')->orderBy('last_name');
        if ($villageId) $query->where('village_id', $villageId);
        $residents = $query->get();
        return ['rows' => $residents->map(fn($r) => [
            'Code' => $r->resident_code, 'Name' => $r->full_name, 'Gender' => $r->gender,
            'Age' => $r->age, 'PWD Type' => $r->pwd_type ?? '', 'Village' => $r->village?->village_name ?? '',
        ])->toArray(), 'total' => $residents->count()];
    }

    protected function seniorCitizenReport(?int $villageId): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->where('is_senior_citizen', true)->with('village')->orderBy('last_name');
        if ($villageId) $query->where('village_id', $villageId);
        $residents = $query->get();
        return ['rows' => $residents->map(fn($r) => [
            'Code' => $r->resident_code, 'Name' => $r->full_name, 'Gender' => $r->gender,
            'Age' => $r->age, 'Village' => $r->village?->village_name ?? '',
        ])->toArray(), 'total' => $residents->count()];
    }

    protected function soloParentReport(?int $villageId): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->where('is_solo_parent', true)->with('village')->orderBy('last_name');
        if ($villageId) $query->where('village_id', $villageId);
        $residents = $query->get();
        return ['rows' => $residents->map(fn($r) => [
            'Code' => $r->resident_code, 'Name' => $r->full_name, 'Gender' => $r->gender,
            'Age' => $r->age, 'Village' => $r->village?->village_name ?? '',
        ])->toArray(), 'total' => $residents->count()];
    }

    protected function youthReport(?int $villageId): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->where('is_youth', true)->with('village')->orderBy('last_name');
        if ($villageId) $query->where('village_id', $villageId);
        $residents = $query->get();
        return ['rows' => $residents->map(fn($r) => [
            'Code' => $r->resident_code, 'Name' => $r->full_name, 'Gender' => $r->gender,
            'Age' => $r->age, 'Village' => $r->village?->village_name ?? '',
        ])->toArray(), 'total' => $residents->count()];
    }

    // ====== HOUSEHOLD REPORTS ======

    protected function householdMasterList(?int $villageId, array $filters): array
    {
        $query = Household::where('status', 'active')->with('village')->orderBy('household_number');
        if ($villageId) $query->where('village_id', $villageId);
        $households = $query->get();
        return ['rows' => $households->map(fn($h) => [
            'Household #' => $h->household_number, 'Head Name' => $h->household_head_name ?? '',
            'Block/Lot' => $h->block_number . '/' . $h->lot_number, 'Village' => $h->village?->village_name ?? '',
            'Members' => $h->member_count, 'Family Count' => $h->household_type ?? '',
            'Registration' => $h->registration_date?->format('M d, Y') ?? '',
        ])->toArray(), 'total' => $households->count()];
    }

    protected function blockLotReport(?int $villageId): array
    {
        $blockDist = $this->householdAnalytics->getBlockDistribution($villageId);
        $lotDist = $this->householdAnalytics->getLotDistribution($villageId);
        $rows = [];
        foreach ($blockDist as $block => $info) {
            $rows[] = ['Block' => $block, 'Households' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'lotDistribution' => $lotDist, 'summary' => $this->householdAnalytics->getBlockLotStats($villageId)];
    }

    protected function householdComposition(?int $villageId): array
    {
        $dist = $this->householdAnalytics->getCompositionDistribution($villageId);
        $rows = [];
        foreach ($dist as $comp => $info) {
            $rows[] = ['Composition' => $comp, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->householdAnalytics->getHouseholdSummary($villageId)];
    }

    protected function householdSizeReport(?int $villageId): array
    {
        $dist = $this->householdAnalytics->getSizeDistribution($villageId);
        $rows = [];
        foreach ($dist as $size => $info) {
            $rows[] = ['Size' => $size, 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->householdAnalytics->getHouseholdSummary($villageId)];
    }

    protected function householdOccupancyReport(?int $villageId): array
    {
        $occ = $this->householdAnalytics->getOccupancyStats($villageId);
        $rows = [];
        foreach ($occ as $key => $info) {
            $rows[] = ['Status' => ucfirst(str_replace('_', ' ', $key)), 'Count' => $info['count'] ?? 0, 'Percentage' => ($info['percentage'] ?? 0) . '%'];
        }
        return ['rows' => $rows, 'summary' => $this->householdAnalytics->getHouseholdSummary($villageId)];
    }

    protected function householdPaymentStatusReport(?int $villageId): array
    {
        $query = Household::where('status', 'active')->with('village', 'billings')->orderBy('household_number');
        if ($villageId) $query->where('village_id', $villageId);
        $households = $query->get();
        $rows = [];
        foreach ($households as $h) {
            $paidCount = $h->billings->where('status', 'paid')->count();
            $pendingCount = $h->billings->whereIn('status', ['pending', 'overdue'])->count();
            $rows[] = [
                'Household #' => $h->household_number, 'Head Name' => $h->household_head_name ?? '',
                'Village' => $h->village?->village_name ?? '', 'Total Billings' => $h->billings->count(),
                'Paid' => $paidCount, 'Pending' => $pendingCount, 'Outstanding' => $h->outstanding_balance,
            ];
        }
        return ['rows' => $rows, 'total' => $households->count()];
    }

    protected function householdPaymentBehaviorReport(?int $villageId): array
    {
        $query = Household::where('status', 'active')->where('approval_status', 'approved')
            ->whereNotNull('payment_behavior')->with('village')->orderByDesc('payment_reliability_score');
        if ($villageId) $query->where('village_id', $villageId);
        $households = $query->get();
        $rows = [];
        foreach ($households as $h) {
            $rows[] = [
                'Household #' => $h->household_number, 'Head Name' => $h->household_head_name ?? '',
                'Village' => $h->village?->village_name ?? '', 'Behavior' => PaymentBehaviorService::BEHAVIOR_LABELS[$h->payment_behavior] ?? $h->payment_behavior,
                'Score' => $h->payment_reliability_score, 'Confidence' => $h->prediction_confidence,
            ];
        }
        return ['rows' => $rows, 'total' => $households->count()];
    }

    // ====== PAYMENT REPORTS ======

    protected function collectionReport(?int $villageId): array
    {
        $stats = $villageId ? $this->predictionAnalytics->getVillageStats($villageId) : $this->predictionAnalytics->getSystemStats();
        $rows = [];
        foreach ($stats as $key => $value) {
            if (!is_array($value)) {
                $rows[] = ['Metric' => ucwords(str_replace('_', ' ', $key)), 'Value' => $value];
            }
        }
        return ['rows' => $rows, 'stats' => $stats];
    }

    protected function monthlyCollectionReport(?int $villageId): array
    {
        $trend = $this->paymentAnalytics->getMonthlyTrend($villageId);
        $rows = [];
        foreach ($trend as $month) {
            $rows[] = [
                'Month' => $month['month'] ?? '', 'Collected' => $month['collected'] ?? 0,
                'Billed' => $month['billed'] ?? 0, 'Rate' => ($month['collection_rate'] ?? 0) . '%',
            ];
        }
        return ['rows' => $rows, 'summary' => $villageId ? $this->paymentAnalytics->getVillageStats($villageId) : $this->paymentAnalytics->getSystemStats()];
    }

    protected function annualCollectionReport(?int $villageId): array
    {
        $trend = $this->paymentAnalytics->getMonthlyTrend($villageId, 24);
        $annual = [];
        foreach ($trend as $month) {
            $year = substr($month['month'] ?? '', 0, 4);
            if (!isset($annual[$year])) $annual[$year] = ['year' => $year, 'collected' => 0, 'billed' => 0];
            $annual[$year]['collected'] += $month['collected'] ?? 0;
            $annual[$year]['billed'] += $month['billed'] ?? 0;
        }
        $rows = [];
        foreach ($annual as $a) {
            $rows[] = ['Year' => $a['year'], 'Collected' => $a['collected'], 'Billed' => $a['billed'],
                'Rate' => $a['billed'] > 0 ? round(($a['collected'] / $a['billed']) * 100, 1) . '%' : '0%'];
        }
        return ['rows' => $rows];
    }

    protected function outstandingBalanceReport(?int $villageId): array
    {
        $query = Household::where('status', 'active')->with('village')
            ->whereHas('billings', fn($q) => $q->whereIn('status', ['pending', 'overdue', 'partially_paid']));
        if ($villageId) $query->where('village_id', $villageId);
        $households = $query->get();
        $rows = [];
        foreach ($households as $h) {
            $rows[] = [
                'Household #' => $h->household_number, 'Head Name' => $h->household_head_name ?? '',
                'Village' => $h->village?->village_name ?? '', 'Outstanding' => $h->outstanding_balance,
            ];
        }
        $totalOutstanding = $households->sum(fn($h) => $h->outstanding_balance);
        return ['rows' => $rows, 'totalOutstanding' => $totalOutstanding, 'total' => $households->count()];
    }

    protected function partialPaymentReport(?int $villageId): array
    {
        $query = HouseholdPayment::where('payment_status', 'partially_paid')->with('household', 'village', 'billing', 'paymentType')->orderByDesc('payment_date');
        if ($villageId) $query->where('village_id', $villageId);
        $payments = $query->get();
        $rows = [];
        foreach ($payments as $p) {
            $rows[] = [
                'Date' => $p->payment_date?->format('M d, Y') ?? '', 'Household' => $p->household?->household_number ?? '',
                'Type' => $p->paymentType?->type_name ?? '', 'Amount Due' => $p->billing?->total_amount ?? 0,
                'Paid' => $p->total_paid, 'Balance' => max(0, ($p->billing?->total_amount ?? 0) - $p->total_paid),
            ];
        }
        return ['rows' => $rows, 'total' => $payments->count()];
    }

    protected function paymentHistoryReport(?int $villageId, array $filters): array
    {
        $query = HouseholdPayment::with('household', 'village', 'paymentType', 'receivedByUser')->orderByDesc('payment_date');
        if ($villageId) $query->where('village_id', $villageId);
        if (!empty($filters['date_from'])) $query->where('payment_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->where('payment_date', '<=', $filters['date_to']);
        $payments = $query->limit(500)->get();
        $rows = [];
        foreach ($payments as $p) {
            $rows[] = [
                'Receipt #' => $p->receipt_number ?? '', 'Date' => $p->payment_date?->format('M d, Y') ?? '',
                'Household' => $p->household?->household_number ?? '', 'Type' => $p->paymentType?->type_name ?? '',
                'Amount' => $p->amount, 'Penalty' => $p->penalty, 'Total Paid' => $p->total_paid,
                'Method' => $p->payment_method_label ?? '', 'Status' => $p->payment_status,
            ];
        }
        return ['rows' => $rows, 'total' => $payments->count()];
    }

    protected function paymentReliabilityReport(?int $villageId): array
    {
        $distribution = $villageId ? $this->reliabilityService->getVillageDistribution($villageId) : $this->reliabilityService->getSystemDistribution();
        $avgScore = $villageId ? $this->reliabilityService->getAverageScore($villageId) : $this->reliabilityService->getSystemAverageScore();
        $rows = [];
        foreach ($distribution as $behavior => $info) {
            $rows[] = ['Classification' => ucfirst(str_replace('_', ' ', $behavior)), 'Count' => $info['count'] ?? 0,
                'Percentage' => ($info['percentage'] ?? 0) . '%', 'Avg Score' => $info['avg_score'] ?? 0];
        }
        return ['rows' => $rows, 'distribution' => $distribution, 'avgScore' => $avgScore];
    }

    protected function paymentPredictionReport(?int $villageId): array
    {
        $stats = $villageId ? $this->predictionAnalytics->getVillageStats($villageId) : $this->predictionAnalytics->getSystemStats();
        $households = Household::where('status', 'active')->where('approval_status', 'approved')
            ->whereNotNull('payment_behavior')->with('village')->orderByDesc('payment_reliability_score');
        if ($villageId) $households->where('village_id', $villageId);
        $households = $households->get();
        $rows = $households->map(fn($h) => [
            'Household #' => $h->household_number, 'Head Name' => $h->household_head_name ?? '',
            'Block/Lot' => $h->block_number . '/' . $h->lot_number, 'Behavior' => PaymentBehaviorService::BEHAVIOR_LABELS[$h->payment_behavior] ?? $h->payment_behavior,
            'Score' => $h->payment_reliability_score, 'Confidence' => $h->prediction_confidence,
        ])->toArray();
        return ['rows' => $rows, 'stats' => $stats, 'total' => $households->count()];
    }

    protected function delinquentReport(?int $villageId): array
    {
        $query = Household::where('status', 'active')->where('approval_status', 'approved')
            ->whereIn('payment_behavior', ['at_risk', 'high_risk'])->with('village')->orderBy('payment_reliability_score');
        if ($villageId) $query->where('village_id', $villageId);
        $households = $query->get();
        $rows = [];
        foreach ($households as $h) {
            $reasons = json_decode($h->prediction_reasons, true) ?? [];
            $rows[] = [
                'Household #' => $h->household_number, 'Head Name' => $h->household_head_name ?? '',
                'Village' => $h->village?->village_name ?? '', 'Behavior' => PaymentBehaviorService::BEHAVIOR_LABELS[$h->payment_behavior] ?? $h->payment_behavior,
                'Score' => $h->payment_reliability_score, 'Reasons' => implode('; ', $reasons),
            ];
        }
        return ['rows' => $rows, 'total' => $households->count()];
    }

    // ====== PARTICIPATION REPORTS ======

    protected function attendanceReport(?int $villageId, array $filters): array
    {
        $query = Event::with(['village', 'participations.household'])->orderByDesc('event_date');
        if ($villageId) $query->forVillage($villageId);
        $events = $query->get();
        $rows = [];
        foreach ($events as $e) {
            $rows[] = [
                'Event' => $e->title, 'Date' => $e->event_date?->format('M d, Y') ?? '',
                'Village' => $e->village?->village_name ?? 'All Villages', 'Type' => $e->event_type ?? '',
                'Attendees' => $this->countAttendees($e, $villageId),
            ];
        }
        return ['rows' => $rows, 'summary' => $villageId ? $this->participationAnalytics->getVillageStats($villageId) : $this->participationAnalytics->getSystemStats()];
    }

    protected function participationSummaryReport(?int $villageId): array
    {
        $stats = $villageId ? $this->participationAnalytics->getVillageStats($villageId) : $this->participationAnalytics->getSystemStats();
        $rows = [];
        foreach ($stats as $key => $value) {
            if (!is_array($value)) {
                $rows[] = ['Metric' => ucwords(str_replace('_', ' ', $key)), 'Value' => $value];
            }
        }
        return ['rows' => $rows, 'summary' => $stats];
    }

    protected function eventParticipationReport(?int $villageId): array
    {
        $query = Event::with(['village', 'participations.resident', 'participations.household'])->orderByDesc('event_date');
        if ($villageId) $query->forVillage($villageId);
        $events = $query->get();
        $rows = [];
        foreach ($events as $e) {
            $rows[] = [
                'Event' => $e->title, 'Date' => $e->event_date?->format('M d, Y') ?? '',
                'Village' => $e->village?->village_name ?? 'All Villages', 'Attendees' => $this->countAttendees($e, $villageId),
            ];
        }
        return ['rows' => $rows, 'total' => $events->count()];
    }

    protected function volunteerReport(?int $villageId): array
    {
        $query = Resident::where('status', 'active')->where('approval_status', 'approved')->where(function ($q) {
            $q->where('is_disaster_volunteer', true)->orWhere('is_village_volunteer', true);
        })->with('village')->orderBy('last_name');
        if ($villageId) $query->where('village_id', $villageId);
        $volunteers = $query->get();
        $rows = $volunteers->map(fn($r) => [
            'Name' => $r->full_name, 'Village' => $r->village?->village_name ?? '',
            'Disaster Volunteer' => $r->is_disaster_volunteer ? 'Yes' : 'No',
            'Village Volunteer' => $r->is_village_volunteer ? 'Yes' : 'No',
        ])->toArray();
        return ['rows' => $rows, 'total' => $volunteers->count()];
    }

    protected function communityEngagementReport(?int $villageId): array
    {
        $partStats = $villageId ? $this->participationAnalytics->getVillageStats($villageId) : $this->participationAnalytics->getSystemStats();
        $eventCount = Event::when($villageId, fn($q) => $q->forVillage($villageId))->count();
        return ['summary' => $partStats, 'eventCount' => $eventCount, 'rows' => []];
    }

    // ====== ANNOUNCEMENT REPORTS ======

    protected function announcementHistoryReport(?int $villageId): array
    {
        $query = Announcement::with('village', 'creator')->orderByDesc('created_at');
        if ($villageId) $query->forVillage($villageId);
        $announcements = $query->get();
        $rows = $announcements->map(fn($a) => [
            'Title' => $a->title, 'Type' => $a->announcement_type ?? '',
            'Village' => $a->village?->village_name ?? 'All Villages', 'Status' => $a->status ?? '',
            'Created' => $a->created_at?->format('M d, Y') ?? '', 'Responses' => $a->responses_count ?? 0,
        ])->toArray();
        return ['rows' => $rows, 'total' => $announcements->count()];
    }

    protected function announcementEngagementReport(?int $villageId): array
    {
        $stats = $villageId ? $this->announcementAnalytics->getVillageStats($villageId) : $this->announcementAnalytics->getSystemStats();
        $rows = [];
        foreach ($stats as $key => $value) {
            if (!is_array($value)) {
                $rows[] = ['Metric' => ucwords(str_replace('_', ' ', $key)), 'Value' => $value];
            }
        }
        return ['rows' => $rows, 'summary' => $stats];
    }

    protected function announcementArchiveReport(?int $villageId): array
    {
        $query = Announcement::where('status', 'archived')->with('village', 'creator')->orderByDesc('updated_at');
        if ($villageId) $query->forVillage($villageId);
        $announcements = $query->get();
        $rows = $announcements->map(fn($a) => [
            'Title' => $a->title, 'Type' => $a->announcement_type ?? '',
            'Village' => $a->village?->village_name ?? 'All Villages',
            'Archived' => $a->updated_at?->format('M d, Y') ?? '',
        ])->toArray();
        return ['rows' => $rows, 'total' => $announcements->count()];
    }

    /**
     * Attendees of an all-village event only count against the village the
     * report is scoped to; village-specific events count everyone.
     */
    protected function countAttendees(Event $event, ?int $villageId): int
    {
        if ($event->village_id === null && $villageId !== null) {
            return $event->participations
                ->filter(fn ($p) => (int) ($p->household->village_id ?? 0) === $villageId)
                ->count();
        }

        return $event->participations->count();
    }

    // ====== GIS REPORTS ======

    protected function gisSummaryReport(?int $villageId): array
    {
        $stats = $villageId ? $this->gisAnalytics->getVillageStats($villageId) : $this->gisAnalytics->getSystemStats();
        $rows = [];
        foreach ($stats as $key => $value) {
            if (!is_array($value)) {
                $rows[] = ['Metric' => ucwords(str_replace('_', ' ', $key)), 'Value' => $value];
            }
        }
        return ['rows' => $rows, 'stats' => $stats];
    }

    protected function gisLocationReport(?int $villageId): array
    {
        $query = Village::where('status', 'active');
        if ($villageId) $query->where('id', $villageId);
        $villages = $query->get();
        $rows = $villages->map(fn($v) => [
            'Village' => $v->village_name, 'Code' => $v->village_code,
            'Latitude' => $v->latitude ?? '', 'Longitude' => $v->longitude ?? '',
            'Address' => $v->address ?? '', 'Area (sq km)' => $v->land_area_sq_km ?? '',
        ])->toArray();
        return ['rows' => $rows, 'total' => $villages->count()];
    }

    protected function gisAnalyticsReport(?int $villageId): array
    {
        $stats = $villageId ? $this->gisAnalytics->getVillageStats($villageId) : $this->gisAnalytics->getSystemStats();
        return ['rows' => collect($stats)->map(fn($v, $k) => ['Metric' => ucwords(str_replace('_', ' ', $k)), 'Value' => $v])->values()->toArray(), 'stats' => $stats];
    }

    protected function gisComparisonReport(): array
    {
        $villages = Village::where('status', 'active')->get();
        $rows = $villages->map(fn($v) => [
            'Village' => $v->village_name, 'Code' => $v->village_code,
            'Latitude' => $v->latitude ?? '', 'Longitude' => $v->longitude ?? '',
            'Households' => $v->total_households ?? 0, 'Population' => $v->total_population ?? 0,
        ])->toArray();
        return ['rows' => $rows];
    }

    // ====== ANALYTICS REPORTS ======

    protected function analyticsPopulationReport(?int $villageId): array
    {
        return ['summary' => $this->population->getPopulationSummary($villageId), 'gender' => $this->population->getGenderDistribution($villageId),
            'age' => $this->population->getAgeDistribution($villageId), 'civilStatus' => $this->population->getCivilStatusDistribution($villageId),
            'rows' => []];
    }

    protected function analyticsHouseholdReport(?int $villageId): array
    {
        return ['summary' => $this->householdAnalytics->getHouseholdSummary($villageId), 'size' => $this->householdAnalytics->getSizeDistribution($villageId),
            'composition' => $this->householdAnalytics->getCompositionDistribution($villageId), 'rows' => []];
    }

    protected function analyticsEmploymentReport(?int $villageId): array
    {
        return ['summary' => $this->employmentAnalytics->getSummaryStats($villageId), 'status' => $this->employmentAnalytics->getEmploymentStatusDistribution($villageId),
            'occupation' => $this->employmentAnalytics->getOccupationDistribution($villageId), 'rows' => []];
    }

    protected function analyticsIncomeReport(?int $villageId): array
    {
        return ['summary' => $this->employmentAnalytics->getSummaryStats($villageId), 'range' => $this->employmentAnalytics->getIncomeRangeDistribution($villageId),
            'source' => $this->employmentAnalytics->getIncomeSourceDistribution($villageId), 'rows' => []];
    }

    protected function analyticsSkillsReport(?int $villageId): array
    {
        return ['summary' => $this->skillsAnalytics->getSummaryStats($villageId), 'primary' => $this->skillsAnalytics->getPrimarySkillDistribution($villageId),
            'combined' => $this->skillsAnalytics->getCombinedSkillDistribution($villageId), 'rows' => []];
    }

    protected function analyticsGovAssistanceReport(?int $villageId): array
    {
        return ['summary' => $this->govAssistance->getSummaryStats($villageId), 'programs' => $this->govAssistance->getProgramCounts($villageId),
            'distribution' => $this->govAssistance->getProgramDistribution($villageId), 'rows' => []];
    }

    protected function analyticsHealthcareReport(?int $villageId): array
    {
        return ['summary' => $this->healthcareAnalytics->getSummaryStats($villageId), 'coverage' => $this->healthcareAnalytics->getCoverageDistribution($villageId), 'rows' => []];
    }

    protected function analyticsParticipationReport(?int $villageId): array
    {
        $stats = $villageId ? $this->participationAnalytics->getVillageStats($villageId) : $this->participationAnalytics->getSystemStats();
        $rows = [];
        foreach ($stats as $key => $value) {
            if (!is_array($value)) $rows[] = ['Metric' => ucwords(str_replace('_', ' ', $key)), 'Value' => $value];
        }
        return ['rows' => $rows, 'summary' => $stats];
    }

    protected function analyticsExecutiveReport(?int $villageId): array
    {
        return ['insights' => $this->executiveIntelligence->getExecutiveInsights($villageId),
            'population' => $this->population->getPopulationSummary($villageId),
            'household' => $this->householdAnalytics->getHouseholdSummary($villageId), 'rows' => []];
    }

    // ====== EXECUTIVE SUMMARY ======

    protected function executiveSummaryReport(?int $villageId): array
    {
        $population = $this->population->getPopulationSummary($villageId);
        $household = $this->householdAnalytics->getHouseholdSummary($villageId);
        $payment = $villageId ? $this->paymentAnalytics->getVillageStats($villageId) : $this->paymentAnalytics->getSystemStats();
        $participation = $villageId ? $this->participationAnalytics->getVillageStats($villageId) : $this->participationAnalytics->getSystemStats();
        $govAssistance = $this->govAssistance->getSummaryStats($villageId);
        $healthcare = $this->healthcareAnalytics->getSummaryStats($villageId);
        $topOccupations = $this->occupationAnalytics->getTopOccupations($villageId, 5);
        $topSkills = $this->skillsAnalytics->getTopSkills($villageId, 5);
        $insights = $this->executiveIntelligence->getExecutiveInsights($villageId);
        $villageComparison = $this->population->getVillageComparisonRankings();

        $rows = [
            ['Section' => 'Population', 'Metric' => 'Total Residents', 'Value' => $population['total'] ?? 0],
            ['Section' => 'Population', 'Metric' => 'Male', 'Value' => $population['male_count'] ?? 0],
            ['Section' => 'Population', 'Metric' => 'Female', 'Value' => $population['female_count'] ?? 0],
            ['Section' => 'Household', 'Metric' => 'Total Households', 'Value' => $household['total'] ?? 0],
            ['Section' => 'Household', 'Metric' => 'Average Size', 'Value' => $household['average_size'] ?? 0],
            ['Section' => 'Payment', 'Metric' => 'Total Collected', 'Value' => $payment['total_collected'] ?? 0],
            ['Section' => 'Payment', 'Metric' => 'Collection Rate', 'Value' => ($payment['collection_rate'] ?? 0) . '%'],
            ['Section' => 'Participation', 'Metric' => 'Total Events', 'Value' => $participation['total_events'] ?? 0],
            ['Section' => 'Participation', 'Metric' => 'Total Participants', 'Value' => $participation['total_participants'] ?? 0],
            ['Section' => 'Gov. Assistance', 'Metric' => 'Beneficiaries', 'Value' => $govAssistance['with_assistance'] ?? 0],
            ['Section' => 'Gov. Assistance', 'Metric' => 'Coverage Rate', 'Value' => ($govAssistance['coverage_rate'] ?? 0) . '%'],
            ['Section' => 'Healthcare', 'Metric' => 'Coverage Rate', 'Value' => ($healthcare['overall_coverage_rate'] ?? 0) . '%'],
        ];

        return [
            'rows' => $rows, 'population' => $population, 'household' => $household,
            'payment' => $payment, 'participation' => $participation,
            'govAssistance' => $govAssistance, 'healthcare' => $healthcare,
            'topOccupations' => $topOccupations, 'topSkills' => $topSkills,
            'insights' => $insights, 'villageComparison' => $villageComparison,
        ];
    }

    // ====== HELPERS ======

    protected function applyAgeGroup($query, string $group): void
    {
        match ($group) {
            'infant' => $query->where('age', '<=', 5),
            'child' => $query->whereBetween('age', [6, 12]),
            'teen' => $query->whereBetween('age', [13, 17]),
            'youth' => $query->whereBetween('age', [18, 30]),
            'adult' => $query->whereBetween('age', [31, 59]),
            'senior' => $query->where('age', '>=', 60),
            default => null,
        };
    }
}
