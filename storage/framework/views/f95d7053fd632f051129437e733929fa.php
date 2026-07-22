<?php $__env->startSection('title', $village->village_name . ' — GIS Dashboard'); ?>
<?php $__env->startSection('bodyClasses', 'dashboard-body gis-page'); ?>

<?php $__env->startSection('content'); ?>
<div id="gis-layout" style="height: calc(100vh - var(--mvms-navbar-height) - var(--mvms-footer-height)); display: flex; flex-direction: column; overflow: hidden;">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-shrink-0">
        <div>
            <h1 class="page-title mb-1"><?php echo e($village->village_name); ?> <small class="text-muted fs-6">GIS Dashboard</small></h1>
            <p class="page-subtitle text-muted"><?php echo e($village->village_code); ?> — <?php echo e($village->address); ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('villages.show', $village)); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Village Profile
            </a>
            <a href="<?php echo e(route('gis.export', ['village_id' => $village->id])); ?>" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i> Export
            </a>
        </div>
    </div>

    <div class="row g-1 mb-1 align-items-center flex-shrink-0" id="search-filter-bar">
        <div class="col">
            <div class="input-group input-group-sm">
                <input type="text" id="search-input" class="form-control" placeholder="Name, block, lot, resident...">
                <button id="btn-search" class="btn btn-primary"><i class="bi bi-search"></i></button>
            </div>
        </div>
        <div class="col-auto">
            <select id="filter-status" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="relocated">Relocated</option>
            </select>
        </div>
        <div class="col-auto">
            <select id="filter-approval" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Approval</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" id="filter-block" class="form-control form-control-sm" placeholder="Block #" style="width:90px;">
        </div>
        <div class="col-auto">
            <button id="btn-apply-filter" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i></button>
            <button id="btn-clear-filter" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

    <div class="row g-0 flex-grow-1 flex-nowrap align-items-stretch" style="min-height: 0; overflow: hidden;">
        <div class="col-auto d-flex flex-column" style="width: 360px; min-height: 0; overflow: hidden;">
            <div id="stats-panel" class="d-flex flex-column border flex-grow-1" style="border-radius: 0; min-height: 0; overflow: hidden;">
                <div class="bg-success text-white d-flex justify-content-between align-items-center py-2 px-3 flex-shrink-0">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-line me-2"></i> <?php echo e($village->village_name); ?></h6>
                    <button class="btn btn-sm btn-outline-light py-0 px-1" id="btn-collapse-stats" title="Collapse">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
                <div class="p-3 flex-grow-1 overflow-auto" id="stats-body" style="min-height: 0;">
                    <?php $dp = $dashboardData; ?>

                    
                    <div class="stat-section text-center">
                        <?php if($dp['village_profile']['logo_url']): ?>
                            <img src="<?php echo e($dp['village_profile']['logo_url']); ?>" alt="Logo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #dee2e6;" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <h6 class="mt-2 mb-0 fw-bold"><?php echo e($dp['village_profile']['name']); ?></h6>
                        <small class="text-muted"><?php echo e($dp['village_profile']['code']); ?></small>
                    </div>

                    
                    <div class="stat-section">
                        <div class="stat-section-title">Population Statistics</div>
                        <div class="stat-row"><span class="stat-label">Total Population</span><span class="stat-value text-primary"><?php echo e($dp['population']['total_population']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Households</span><span class="stat-value"><?php echo e($dp['population']['households']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Avg Household Size</span><span class="stat-value"><?php echo e($dp['population']['average_household_size']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Pending Residents</span><span class="stat-value text-warning"><?php echo e($dp['population']['pending_residents']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Approved Residents</span><span class="stat-value text-success"><?php echo e($dp['population']['approved_residents']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Rejected Residents</span><span class="stat-value text-danger"><?php echo e($dp['population']['rejected_residents']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Pending Households</span><span class="stat-value text-warning"><?php echo e($dp['population']['pending_households']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Approved Households</span><span class="stat-value text-success"><?php echo e($dp['population']['approved_households']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Rejected Households</span><span class="stat-value text-danger"><?php echo e($dp['population']['rejected_households']); ?></span></div>
                    </div>

                    
                    <div class="stat-section">
                        <div class="stat-section-title">Demographics</div>
                        <div class="stat-row"><span class="stat-label"><i class="bi bi-gender-male me-1"></i>Male</span><span class="stat-value"><?php echo e($dp['demographics']['male']); ?></span></div>
                        <div class="stat-row"><span class="stat-label"><i class="bi bi-gender-female me-1"></i>Female</span><span class="stat-value"><?php echo e($dp['demographics']['female']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Children (&lt;15)</span><span class="stat-value"><?php echo e($dp['demographics']['children']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Youth (15-30)</span><span class="stat-value"><?php echo e($dp['demographics']['youth']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Adults (31-59)</span><span class="stat-value"><?php echo e($dp['demographics']['adults']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Senior Citizens</span><span class="stat-value"><?php echo e($dp['demographics']['senior_citizens']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">PWD</span><span class="stat-value"><?php echo e($dp['demographics']['pwd']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Solo Parents</span><span class="stat-value"><?php echo e($dp['demographics']['solo_parents']); ?></span></div>
                        <?php if($dp['demographics']['indigenous_people'] > 0): ?>
                        <div class="stat-row"><span class="stat-label">Indigenous People</span><span class="stat-value"><?php echo e($dp['demographics']['indigenous_people']); ?></span></div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="stat-section">
                        <div class="stat-section-title">Socio-Economic</div>
                        <div class="stat-row"><span class="stat-label">Employment Rate</span><span class="stat-value text-success"><?php echo e($dp['socio_economic']['employment_rate']); ?>%</span></div>
                        <div class="stat-row"><span class="stat-label">Unemployment Rate</span><span class="stat-value text-danger"><?php echo e($dp['socio_economic']['unemployment_rate']); ?>%</span></div>
                        <div class="stat-row"><span class="stat-label">Students</span><span class="stat-value"><?php echo e($dp['socio_economic']['students']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Self-Employed</span><span class="stat-value"><?php echo e($dp['socio_economic']['self_employed']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Retired</span><span class="stat-value"><?php echo e($dp['socio_economic']['retired']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Avg Income</span><span class="stat-value"><?php echo e($dp['socio_economic']['average_monthly_income']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Top Occupation</span><span class="stat-value" style="font-size:0.75rem;"><?php echo e($dp['socio_economic']['most_common_occupation']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Top Skill</span><span class="stat-value" style="font-size:0.75rem;"><?php echo e($dp['socio_economic']['most_common_skill']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Top Education</span><span class="stat-value" style="font-size:0.75rem;"><?php echo e($dp['socio_economic']['highest_educational_attainment']); ?></span></div>
                    </div>

                    
                    <div class="stat-section">
                        <div class="stat-section-title">Healthcare</div>
                        <div class="stat-row"><span class="stat-label">PhilHealth Members</span><span class="stat-value"><?php echo e($dp['healthcare']['philhealth_members']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">PWD</span><span class="stat-value"><?php echo e($dp['healthcare']['pwd']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Senior Citizens</span><span class="stat-value"><?php echo e($dp['healthcare']['senior_citizens']); ?></span></div>
                    </div>

                    
                    <div class="stat-section">
                        <div class="stat-section-title">Gov. Assistance</div>
                        <div class="stat-row"><span class="stat-label">4Ps</span><span class="stat-value"><?php echo e($dp['government_assistance']['four_ps']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Scholarship</span><span class="stat-value"><?php echo e($dp['government_assistance']['scholarship']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Livelihood (TUPAD)</span><span class="stat-value"><?php echo e($dp['government_assistance']['livelihood_program']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Senior Pension</span><span class="stat-value"><?php echo e($dp['government_assistance']['senior_pension']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">PWD Assistance</span><span class="stat-value"><?php echo e($dp['government_assistance']['pwd_assistance']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Other</span><span class="stat-value"><?php echo e($dp['government_assistance']['other_assistance']); ?></span></div>
                    </div>

                    
                    <div class="stat-section">
                        <div class="stat-section-title">Community Participation</div>
                        <div class="stat-row"><span class="stat-label">Participation Rate</span><span class="stat-value text-primary"><?php echo e($dp['participation']['participation_rate']); ?>%</span></div>
                        <div class="stat-row"><span class="stat-label">Community Events</span><span class="stat-value"><?php echo e($dp['participation']['community_events']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Attendance Rate</span><span class="stat-value"><?php echo e($dp['participation']['attendance_rate']); ?>%</span></div>
                        <div class="stat-row"><span class="stat-label">Most Active HH</span><span class="stat-value" style="font-size:0.75rem;"><?php echo e($dp['participation']['most_active_household']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Most Active Res.</span><span class="stat-value" style="font-size:0.75rem;"><?php echo e($dp['participation']['most_active_resident']); ?></span></div>
                    </div>

                    
                    <?php if($dp['payment']['enabled']): ?>
                    <div class="stat-section">
                        <div class="stat-section-title">Payment Analytics</div>
                        <div class="stat-row"><span class="stat-label">Collection Rate</span><span class="stat-value text-success"><?php echo e($dp['payment']['collection_percentage']); ?>%</span></div>
                        <div class="stat-row"><span class="stat-label">Collected</span><span class="stat-value"><?php echo e($dp['payment']['collected_amount']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Outstanding</span><span class="stat-value text-danger"><?php echo e($dp['payment']['outstanding_balance']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Paid HH</span><span class="stat-value text-success"><?php echo e($dp['payment']['paid_households']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Partial HH</span><span class="stat-value text-warning"><?php echo e($dp['payment']['partial_payments']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Unpaid HH</span><span class="stat-value text-danger"><?php echo e($dp['payment']['unpaid_households']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Excellent Payers</span><span class="stat-value text-success"><?php echo e($dp['payment']['excellent_payers']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Good Payers</span><span class="stat-value text-info"><?php echo e($dp['payment']['good_payers']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">Moderate Payers</span><span class="stat-value text-warning"><?php echo e($dp['payment']['moderate_payers']); ?></span></div>
                        <div class="stat-row"><span class="stat-label">High-Risk Payers</span><span class="stat-value text-danger"><?php echo e($dp['payment']['high_risk_payers']); ?></span></div>
                    </div>
                    <?php else: ?>
                    <div class="stat-section">
                        <div class="stat-section-title">Payment Analytics</div>
                        <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> No payment module available.</p>
                    </div>
                    <?php endif; ?>

                    <hr class="my-3">
                    <h6 class="fw-semibold text-primary mb-2"><i class="bi bi-legend me-1"></i> Legend</h6>
                    <div class="d-flex align-items-center mb-1">
                        <span style="width:14px;height:14px;background:#1a73e8;border-radius:4px;display:inline-block;margin-right:8px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></span>
                        <small>Village</small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                        <span style="width:12px;height:12px;background:#28a745;border-radius:50%;display:inline-block;margin-right:8px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></span>
                        <small>Paid</small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                        <span style="width:12px;height:12px;background:#ffc107;border-radius:50%;display:inline-block;margin-right:8px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></span>
                        <small>Partial</small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                        <span style="width:12px;height:12px;background:#dc3545;border-radius:50%;display:inline-block;margin-right:8px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></span>
                        <small>Unpaid</small>
                    </div>
                    <div class="d-flex align-items-center mb-1">
                        <span style="width:12px;height:12px;background:#6c757d;border-radius:50%;display:inline-block;margin-right:8px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></span>
                        <small>Payment Disabled</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span style="width:12px;height:12px;background:#0d6efd;border-radius:50%;display:inline-block;margin-right:8px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3);"></span>
                        <small>Selected Household</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm h-100" style="border-radius: 0;">
                <div class="card-body p-0 position-relative h-100">
                    <div id="gis-map" style="height:100%;width:100%;"></div>
                    <div id="map-loading" class="position-absolute top-50 start-50 translate-middle d-none" style="z-index:9999;">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .gis-page .app-content { padding: 0 !important; padding-top: var(--mvms-navbar-height) !important; }
    #stats-panel { transition: width 0.3s ease; }
    #stats-panel.collapsed-panel { width: 48px !important; overflow: hidden; }
    #stats-panel.collapsed-panel #stats-body, #stats-panel.collapsed-panel .bg-success h6 { display: none; }
    .gis-popup { text-align: left; min-width: 220px; max-width: 300px; font-size: 0.85rem; }
    .gis-popup .btn { font-size: 0.75rem; }
    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; border-bottom: 1px solid #f0f0f0; }
    .stat-row:last-child { border-bottom: none; }
    .stat-label { font-size: 0.78rem; color: #6c757d; }
    .stat-value { font-weight: 600; font-size: 0.83rem; }
    .stat-section { margin-bottom: 12px; }
    .stat-section-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mvms-primary); font-weight: 700; margin-bottom: 6px; }
    .payment-badge { font-size: 0.7rem; padding: 2px 6px; }
    @media (max-width: 767.98px) {
        #gis-layout {
            height: calc(100vh - var(--mvms-navbar-height) - 1rem) !important;
        }
        #gis-layout > .d-flex.mb-1 { flex-wrap: wrap; gap: 4px !important; }
        #gis-layout > .d-flex.mb-1 > div { flex: 1 1 auto !important; min-width: 0; }
        #gis-layout > .d-flex.mb-1 h1 { font-size: 1rem !important; margin-bottom: 0 !important; }
        #gis-layout > .d-flex.mb-1 .page-subtitle { display: none !important; }
        #gis-layout > .d-flex.mb-1 .btn { flex: 0 0 auto; }
        #search-filter-bar { flex-wrap: wrap; gap: 2px !important; margin-bottom: 4px !important; }
        #search-filter-bar .col,
        #search-filter-bar .col-auto { flex: 1 1 auto !important; width: auto !important; min-width: 0; }
        #search-filter-bar .col { min-width: 120px; }
        #search-filter-bar .col-auto .form-select,
        #search-filter-bar .col-auto .form-control { width: 100% !important; font-size: 0.78rem !important; }
        #search-filter-bar .input-group { width: 100% !important; }
        #gis-layout > .row { flex-direction: column !important; }
        #gis-layout > .row > .col-auto { width: 100% !important; flex: 0 0 auto !important; max-height: 35vh; overflow: hidden; }
        #gis-layout > .row > .col { flex: 1 1 auto !important; min-height: 0; }
        #gis-layout > .row > .col-auto:has(#stats-panel.collapsed-panel) { max-height: none !important; }
        #stats-panel.collapsed-panel { width: 100% !important; }
        #stats-panel.collapsed-panel .bg-success h6,
        #stats-panel.collapsed-panel #stats-body { display: none !important; }
        #stats-panel.collapsed-panel .bg-success { padding: 4px 8px !important; }
        #stats-panel { border-radius: 0 !important; }
        #stats-panel .p-3 { padding: 0.35rem !important; }
        #stats-body { max-height: calc(35vh - 44px) !important; overflow-y: auto !important; }
        .stat-section { margin-bottom: 4px !important; }
        .stat-section-title { font-size: 0.62rem !important; margin-bottom: 2px !important; }
        .stat-row { padding: 1px 0 !important; }
        .stat-label { font-size: 0.65rem !important; }
        .stat-value { font-size: 0.7rem !important; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var loadingEl = document.getElementById('map-loading');
    loadingEl.classList.remove('d-none');

    var center = <?php echo json_encode($center, 15, 512) ?>;
    var households = <?php echo json_encode($dashboardData['households'], 15, 512) ?>;
    var dashboardData = <?php echo json_encode($dashboardData, 15, 512) ?>;
    var villageId = <?php echo e($village->id); ?>;
    var paymentEnabled = <?php echo e($dashboardData['village_profile']['payment_enabled'] ? 'true' : 'false'); ?>;

    var isMobile = window.innerWidth < 768;
    var statsPanel = document.getElementById('stats-panel');
    var collapseIcon = document.querySelector('#btn-collapse-stats i');
    if (isMobile) {
        statsPanel.classList.add('collapsed-panel');
        collapseIcon.className = 'bi bi-chevron-down';
    }

    var map = L.map('gis-map', {
        fullscreenControl: true,
        fullscreenControlOptions: { position: 'topleft' },
        zoomControl: true,
        updateWhenIdle: false,
        preferCanvas: true,
    }).setView([center.lat, center.lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    var tilesLoading = 0;
    map.on('tileload', function() {
        tilesLoading++;
        if (tilesLoading >= 4) loadingEl.classList.add('d-none');
    });
    setTimeout(function() { loadingEl.classList.add('d-none'); }, 4000);

    var villageIcon = L.divIcon({
        className: 'gis-marker',
        html: '<div style="width:26px;height:26px;background:#1a73e8;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;"><i class="bi bi-building" style="color:#fff;font-size:11px;"></i></div>',
        iconSize: [32, 32], iconAnchor: [16, 16],
    });

    if (center.lat && center.lng) {
        L.marker([center.lat, center.lng], { icon: villageIcon })
            .bindPopup('<strong style="color:#1a73e8;"><?php echo e($village->village_name); ?></strong>')
            .addTo(map);
    }

    var householdLayer = L.layerGroup().addTo(map);
    var allMarkers = [];

    function getHouseholdColor(h) {
        if (h.payment_status === 'paid') return '#28a745';
        if (h.payment_status === 'partial') return '#ffc107';
        if (h.payment_status === 'unpaid') return '#dc3545';
        if (h.payment_status === 'disabled' || h.payment_status === 'no_billing') return '#6c757d';
        if (h.status === 'active') return '#28a745';
        if (h.status === 'inactive') return '#ffc107';
        return '#6c757d';
    }

    function createHouseholdMarker(h) {
        var color = getHouseholdColor(h);
        var icon = L.divIcon({
            className: 'gis-marker',
            html: '<div style="width:12px;height:12px;background:' + color + ';border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
            iconSize: [16, 16], iconAnchor: [8, 8],
        });
        var m = L.marker([h.latitude, h.longitude], { icon: icon });
        var paymentBadge = '';
        if (h.payment_status && h.payment_status !== 'disabled' && h.payment_status !== 'no_billing') {
            var pClass = h.payment_status === 'paid' ? 'success' : (h.payment_status === 'partial' ? 'warning' : 'danger');
            paymentBadge = '<br><span class="badge bg-' + pClass + ' payment-badge">' + h.payment_status.charAt(0).toUpperCase() + h.payment_status.slice(1) + '</span>';
        }
        var memberCount = h.member_count || 0;
        var predBadge = '';
        if (h.payment_behavior) {
            var bClass = h.payment_behavior === 'excellent' ? 'success' : (h.payment_behavior === 'good' ? 'info' : (h.payment_behavior === 'moderate' ? 'warning' : 'danger'));
            predBadge = '<br><span class="badge bg-' + bClass + ' payment-badge"><i class="bi bi-graph-up-arrow me-1"></i>' + h.payment_behavior + '</span>';
        }
        m.bindPopup(
            '<div class="gis-popup">' +
            '<strong style="color:#1a73e8;">' + h.household_number + '</strong><hr class="my-1">' +
            '<div class="d-flex justify-content-between mb-1"><small>Head:</small><strong>' + (h.household_head_name || '') + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Block/Lot:</small><strong>' + (h.block_number || '-') + '/' + (h.lot_number || '-') + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Members:</small><strong>' + memberCount + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Status:</small><span class="badge bg-' + (h.status === 'active' ? 'success' : (h.status === 'inactive' ? 'warning' : 'secondary')) + '">' + h.status + '</span></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Approval:</small><span class="badge bg-' + (h.approval_status === 'approved' ? 'success' : (h.approval_status === 'pending' ? 'warning' : 'danger')) + '">' + (h.approval_status || 'N/A') + '</span></div>' +
            paymentBadge + predBadge +
            '<a href="/households/' + h.id + '" class="btn btn-primary btn-sm mt-2 w-100"><i class="bi bi-eye me-1"></i>View Details</a>' +
            '</div>'
        );
        return m;
    }

    function renderMarkers(data) {
        householdLayer.clearLayers();
        allMarkers = [];
        data.forEach(function(h) {
            if (h.latitude && h.longitude) {
                var m = createHouseholdMarker(h);
                m.addTo(householdLayer);
                allMarkers.push({ marker: m, data: h });
            }
        });
    }

    function fitToMarkers() {
        if (allMarkers.length === 0) return;
        var group = L.featureGroup(allMarkers.map(function(item) { return item.marker; }));
        map.fitBounds(group.getBounds().pad(0.1));
    }

    setTimeout(function() {
        renderMarkers(households);
        fitToMarkers();
    }, 80);

    document.getElementById('btn-search').addEventListener('click', function() {
        var q = document.getElementById('search-input').value.trim();
        if (!q) return;
        fetch('/gis/search?q=' + encodeURIComponent(q) + '&village_id=' + villageId)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && res.data.length > 0) {
                    renderMarkers(res.data);
                    fitToMarkers();
                    if (allMarkers.length === 1) {
                        allMarkers[0].marker.openPopup();
                        map.setView([allMarkers[0].data.latitude, allMarkers[0].data.longitude], 17);
                    }
                }
            });
    });

    document.getElementById('btn-apply-filter').addEventListener('click', function() {
        var params = new URLSearchParams();
        params.set('village_id', villageId);
        var st = document.getElementById('filter-status').value;
        var ap = document.getElementById('filter-approval').value;
        var bl = document.getElementById('filter-block').value;
        if (st) params.set('status', st);
        if (ap) params.set('approval_status', ap);
        if (bl) params.set('block_number', bl);
        fetch('/gis/filter?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    renderMarkers(res.data);
                    fitToMarkers();
                }
            });
    });

    document.getElementById('btn-clear-filter').addEventListener('click', function() {
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-approval').value = '';
        document.getElementById('filter-block').value = '';
        document.getElementById('search-input').value = '';
        renderMarkers(households);
    });

    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') document.getElementById('btn-search').click();
    });

    document.getElementById('btn-collapse-stats').addEventListener('click', function() {
        var panel = document.getElementById('stats-panel');
        var mob = window.innerWidth < 768;
        panel.classList.toggle('collapsed-panel');
        var icon = this.querySelector('i');
        if (mob) {
            icon.className = panel.classList.contains('collapsed-panel') ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
        } else {
            icon.className = panel.classList.contains('collapsed-panel') ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
        }
        setTimeout(function() { map.invalidateSize(); }, 350);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/gis/village.blade.php ENDPATH**/ ?>