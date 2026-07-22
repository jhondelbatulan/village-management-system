<?php $__env->startSection('title', 'GIS Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div id="gis-layout" style="height: calc(100vh - var(--mvms-navbar-height) - 1.5rem - var(--mvms-footer-height)); display: flex; flex-direction: column; overflow: hidden;">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-shrink-0">
        <div>
            <h1 class="page-title mb-1">GIS Dashboard</h1>
            <p class="page-subtitle text-muted">Interactive Geographic Intelligence Dashboard</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group" style="width:260px;">
                <span class="input-group-text bg-white"><i class="bi bi-funnel"></i></span>
                <select id="focus-village" class="form-select form-select-sm">
                    <option value="">All Villages</option>
                    <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v->id); ?>"><?php echo e($v->village_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <a href="<?php echo e(route('gis.export')); ?>" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i> Export
            </a>
        </div>
    </div>

    <div class="row g-1 mb-2 align-items-center flex-shrink-0" id="search-filter-bar">
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
            <select id="filter-mapped" class="form-select form-select-sm" style="width:auto;">
                <option value="">All</option>
                <option value="mapped">Mapped</option>
                <option value="unmapped">Unmapped</option>
            </select>
        </div>
        <div class="col-auto" id="filter-payment-wrap" style="display:none;">
            <select id="filter-payment" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Payment</option>
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>
        <div class="col-auto">
            <button id="btn-apply-filter" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i></button>
            <button id="btn-clear-filter" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

    <div class="row g-0 flex-grow-1 flex-nowrap align-items-stretch" style="min-height: 0; overflow: hidden;">
        <div class="col-auto d-flex flex-column" style="width: 360px; min-height: 0; overflow: hidden;">
            <div id="stats-panel" class="d-flex flex-column border flex-grow-1" style="border-radius: 0; min-height: 0; overflow: hidden;">
                <div class="bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3 flex-shrink-0">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-line me-2"></i> <span id="panel-title">System Overview</span></h6>
                    <button class="btn btn-sm btn-outline-light py-0 px-1" id="btn-collapse-stats" title="Collapse">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
                <div class="p-3 flex-grow-1 overflow-auto" id="stats-body" style="min-height: 0;">
                    <div id="village-profile-panel" class="d-none"></div>
                    <div id="stats-content"></div>

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
    #stats-panel { transition: width 0.3s ease; }
    #stats-panel.collapsed-panel { width: 48px !important; overflow: hidden; }
    #stats-panel.collapsed-panel #stats-body, #stats-panel.collapsed-panel .bg-primary h6 { display: none; }
    .gis-popup { text-align: left; min-width: 220px; max-width: 300px; font-size: 0.85rem; }
    .gis-popup .btn { font-size: 0.75rem; }
    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; border-bottom: 1px solid #f0f0f0; }
    .stat-row:last-child { border-bottom: none; }
    .stat-label { font-size: 0.78rem; color: #6c757d; }
    .stat-value { font-weight: 600; font-size: 0.83rem; }
    .stat-section { margin-bottom: 12px; }
    .stat-section-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mvms-primary); font-weight: 700; margin-bottom: 6px; }
    .quick-action-btn { font-size: 0.78rem; padding: 4px 7px; }
    .activity-item { font-size: 0.78rem; padding: 4px 0; border-bottom: 1px solid #f5f5f5; }
    .activity-item:last-child { border-bottom: none; }
    .village-profile-card { text-align: center; padding: 10px 0; }
    .village-profile-card img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #dee2e6; }
    .payment-badge { font-size: 0.7rem; padding: 2px 6px; }
    @media (max-width: 767.98px) {
        #gis-layout {
            height: calc(100vh - var(--mvms-navbar-height) - 1rem - var(--mvms-footer-height)) !important;
        }
        #gis-layout > .d-flex.mb-2 { flex-wrap: wrap; gap: 4px !important; }
        #gis-layout > .d-flex.mb-2 > div { flex: 1 1 auto !important; min-width: 0; }
        #gis-layout > .d-flex.mb-2 h1 { font-size: 1rem !important; margin-bottom: 0 !important; }
        #gis-layout > .d-flex.mb-2 .page-subtitle { display: none !important; }
        #gis-layout > .d-flex.mb-2 .input-group { width: 100% !important; }
        #gis-layout > .d-flex.mb-2 .btn { flex: 0 0 auto; }
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
        #stats-panel.collapsed-panel .bg-primary h6,
        #stats-panel.collapsed-panel #stats-body { display: none !important; }
        #stats-panel.collapsed-panel .bg-primary { padding: 4px 8px !important; }
        #stats-panel { border-radius: 0 !important; }
        #stats-panel .p-3 { padding: 0.35rem !important; }
        #stats-body { max-height: calc(35vh - 44px) !important; overflow-y: auto !important; }
        #stats-panel.collapsed-panel + .col #gis-map,
        #stats-panel.collapsed-panel ~ .col #gis-map { min-height: 60vh; }
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
    var allHouseholds = <?php echo json_encode($households, 15, 512) ?>;
    var villageMarkersData = <?php echo json_encode($villageMarkers, 15, 512) ?>;
    var allVillages = <?php echo json_encode($villages, 15, 512) ?>;
    var systemOverview = <?php echo json_encode($overviewData ?? null, 15, 512) ?>;
    var currentRole = '<?php echo e(auth()->user()->role?->role_slug ?? "super_admin"); ?>';

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
    }).setView([center.lat, center.lng], 12);

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

    var householdLayer = L.layerGroup().addTo(map);
    var villageLayer = L.layerGroup().addTo(map);
    var allMarkers = [];
    var selectedHouseholdMarker = null;

    function getHouseholdColor(h) {
        if (h.payment_status === 'paid') return '#28a745';
        if (h.payment_status === 'partial') return '#ffc107';
        if (h.payment_status === 'unpaid') return '#dc3545';
        if (h.payment_status === 'disabled' || h.payment_status === 'no_billing') return '#6c757d';
        if (h.status === 'active') return '#28a745';
        if (h.status === 'inactive') return '#ffc107';
        return '#6c757d';
    }

    function createVillageMarker(v) {
        var icon = L.divIcon({
            className: 'gis-marker',
            html: '<div style="width:26px;height:26px;background:#1a73e8;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;"><i class="bi bi-building" style="color:#fff;font-size:11px;"></i></div>',
            iconSize: [32, 32], iconAnchor: [16, 16],
        });
        var m = L.marker([v.latitude, v.longitude], { icon: icon });
        var hhCount = v.household_count || v.total_households || 0;
        var resCount = v.resident_count || v.total_population || 0;
        m.bindPopup(
            '<div class="gis-popup">' +
            '<strong style="font-size:1rem;color:#1a73e8;">' + v.village_name + '</strong><br>' +
            '<small class="text-muted">' + (v.address || '') + '</small><hr class="my-1">' +
            '<div class="d-flex justify-content-between mb-1"><small>Households:</small><strong>' + hhCount + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Residents:</small><strong>' + resCount + '</strong></div>' +
            '<button onclick="selectVillage(' + v.id + ')" class="btn btn-primary btn-sm mt-2 w-100"><i class="bi bi-bar-chart-line me-1"></i>View Dashboard</button>' +
            '</div>'
        );
        return m;
    }

    function createHouseholdMarker(h) {
        var color = getHouseholdColor(h);
        var icon = L.divIcon({
            className: 'gis-marker',
            html: '<div style="width:12px;height:12px;background:' + color + ';border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
            iconSize: [16, 16], iconAnchor: [8, 8],
        });
        var m = L.marker([h.latitude, h.longitude], { icon: icon });
        var villageName = h.village ? h.village.village_name : '';
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
            '<strong style="color:#1a73e8;">' + h.household_number + '</strong><br>' +
            '<small class="text-muted">' + villageName + '</small><hr class="my-1">' +
            '<div class="d-flex justify-content-between mb-1"><small>Head:</small><strong>' + (h.household_head_name || '') + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Block/Lot:</small><strong>' + (h.block_number || '-') + '/' + (h.lot_number || '-') + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Members:</small><strong>' + memberCount + '</strong></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Status:</small><span class="badge bg-' + (h.status === 'active' ? 'success' : (h.status === 'inactive' ? 'warning' : 'secondary')) + '">' + h.status + '</span></div>' +
            '<div class="d-flex justify-content-between mb-1"><small>Approval:</small><span class="badge bg-' + (h.approval_status === 'approved' ? 'success' : (h.approval_status === 'pending' ? 'warning' : 'danger')) + '">' + (h.approval_status || 'N/A') + '</span></div>' +
            paymentBadge + predBadge +
            '<a href="/households/' + h.id + '" class="btn btn-primary btn-sm mt-2 w-100"><i class="bi bi-eye me-1"></i>View Details</a>' +
            '</div>'
        );
        m.on('click', function() {
            if (selectedHouseholdMarker) {
                var prev = selectedHouseholdMarker._prevIcon;
                if (prev) selectedHouseholdMarker.setIcon(prev);
            }
            selectedHouseholdMarker = m;
        });
        return m;
    }

    function renderAll() {
        householdLayer.clearLayers();
        villageLayer.clearLayers();
        allMarkers = [];

        villageMarkersData.forEach(function(v) {
            if (v.latitude && v.longitude) {
                var m = createVillageMarker(v);
                m.addTo(villageLayer);
                allMarkers.push({ type: 'village', marker: m, data: v });
            }
        });

        allHouseholds.forEach(function(h) {
            if (h.latitude && h.longitude) {
                var m = createHouseholdMarker(h);
                m.addTo(householdLayer);
                allMarkers.push({ type: 'household', marker: m, data: h });
            }
        });
    }

    setTimeout(function() {
        renderAll();
        renderSystemOverview();
        var allItems = allMarkers;
        if (allItems.length > 0) fitToMarkers(allItems);
    }, 80);

    function fitToMarkers(markers) {
        if (markers.length === 0) return;
        var group = L.featureGroup(markers.map(function(item) { return item.marker; }));
        map.fitBounds(group.getBounds().pad(0.1));
    }

    window.selectVillage = function(vid) {
        document.getElementById('focus-village').value = vid;
        loadVillageData(vid);
    };

    document.getElementById('focus-village').addEventListener('change', function() {
        loadVillageData(this.value);
    });

    function loadVillageData(vid) {
        if (!vid) {
            renderSystemOverview();
            renderAll();
            document.getElementById('panel-title').textContent = 'System Overview';
            document.getElementById('village-profile-panel').classList.add('d-none');
            document.getElementById('filter-payment-wrap').style.display = 'none';
            var allItems = allMarkers;
            if (allItems.length > 0) fitToMarkers(allItems);
            return;
        }

        var loading = document.getElementById('map-loading');
        loading.classList.remove('d-none');

        fetch('/gis/village/' + vid + '/dashboard-data')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                loading.classList.add('d-none');
                if (!res.success) return;

                var data = res.data;
                document.getElementById('panel-title').textContent = data.village_profile.name;
                document.getElementById('filter-payment-wrap').style.display = data.payment.enabled ? '' : 'none';

                renderVillageProfile(data.village_profile);
                renderPopulationStats(data.population);
                renderDemographics(data.demographics);
                renderSocioEconomic(data.socio_economic);
                renderHealthcare(data.healthcare);
                renderGovAssistance(data.government_assistance);
                renderParticipation(data.participation);
                if (data.payment.enabled) renderPaymentStats(data.payment);

                householdLayer.clearLayers();
                villageLayer.clearLayers();
                allMarkers = [];

                var sv = villageMarkersData.find(function(v) { return v.id == vid; });
                if (sv && sv.latitude && sv.longitude) {
                    var m = createVillageMarker(sv);
                    m.addTo(villageLayer);
                    allMarkers.push({ type: 'village', marker: m, data: sv });
                }

                data.households.forEach(function(h) {
                    if (h.latitude && h.longitude) {
                        var m = createHouseholdMarker(h);
                        m.addTo(householdLayer);
                        allMarkers.push({ type: 'household', marker: m, data: h });
                    }
                });

                if (allMarkers.length > 0) fitToMarkers(allMarkers);
            })
            .catch(function() {
                loading.classList.add('d-none');
            });
    }

    function renderSystemOverview() {
        var html = '';
        if (systemOverview) {
            var vs = systemOverview.villages_summary || [];
            var pp = systemOverview.population || {};
            var py = systemOverview.payment || {};

            html += '<div class="stat-section">';
            html += '<div class="stat-section-title">System Overview</div>';
            html += '<div class="stat-row"><span class="stat-label">Total Villages</span><span class="stat-value text-primary">' + vs.length + '</span></div>';
            html += '<div class="stat-row"><span class="stat-label">Total Population</span><span class="stat-value">' + (pp.total_population || 0) + '</span></div>';
            html += '<div class="stat-row"><span class="stat-label">Total Households</span><span class="stat-value">' + (pp.households || 0) + '</span></div>';
            html += '<div class="stat-row"><span class="stat-label">Avg Household Size</span><span class="stat-value">' + (pp.average_household_size || 0) + '</span></div>';
            html += '</div>';

            if (py.total_billings > 0) {
                html += '<div class="stat-section">';
                html += '<div class="stat-section-title">System Payments</div>';
                html += '<div class="stat-row"><span class="stat-label">Collection Rate</span><span class="stat-value text-success">' + (py.collection_percentage || 0) + '%</span></div>';
                html += '<div class="stat-row"><span class="stat-label">Collected</span><span class="stat-value">' + (py.collected_amount || '₱0') + '</span></div>';
                html += '<div class="stat-row"><span class="stat-label">Outstanding</span><span class="stat-value text-danger">' + (py.outstanding_balance || '₱0') + '</span></div>';
                html += '</div>';
            }

            html += '<div class="stat-section">';
            html += '<div class="stat-section-title">Villages</div>';
            vs.forEach(function(v) {
                html += '<div class="stat-row" style="cursor:pointer;" onclick="selectVillage(' + v.id + ')">';
                html += '<span class="stat-label">' + v.village_name + '</span>';
                html += '<span class="stat-value">' + (v.resident_count || 0) + ' res / ' + (v.household_count || 0) + ' hh</span>';
                html += '</div>';
            });
            html += '</div>';
        } else {
            html += '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 small">Loading overview...</p></div>';
        }
        document.getElementById('stats-content').innerHTML = html;
    }

    function renderVillageProfile(vp) {
        var el = document.getElementById('village-profile-panel');
        el.classList.remove('d-none');
        var html = '<div class="village-profile-card">';
        html += '<img src="' + vp.logo_url + '" alt="Logo" onerror="this.style.display=\'none\'">';
        html += '<h6 class="mt-2 mb-0 fw-bold">' + vp.name + '</h6>';
        html += '<small class="text-muted">' + (vp.code || '') + '</small>';
        html += '<div class="text-start mt-2">';
        html += '<div class="stat-row"><span class="stat-label">Address</span><span class="stat-value" style="font-size:0.78rem;">' + (vp.address || 'N/A') + '</span></div>';
        if (vp.established_date) html += '<div class="stat-row"><span class="stat-label">Established</span><span class="stat-value" style="font-size:0.78rem;">' + vp.established_date + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">President</span><span class="stat-value" style="font-size:0.78rem;">' + vp.president + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Secretary</span><span class="stat-value" style="font-size:0.78rem;">' + vp.secretary + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Treasurer</span><span class="stat-value" style="font-size:0.78rem;">' + vp.treasurer + '</span></div>';
        if (vp.latitude && vp.longitude) {
            html += '<div class="stat-row"><span class="stat-label">Coordinates</span><span class="stat-value" style="font-size:0.78rem;">' + vp.latitude + ', ' + vp.longitude + '</span></div>';
        }
        html += '<div class="stat-row"><span class="stat-label">Payment</span><span class="stat-value">' + (vp.payment_enabled ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>') + '</span></div>';
        html += '</div></div>';
        el.innerHTML = html;
    }

    function renderPopulationStats(p) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Population Statistics</div>';
        html += '<div class="stat-row"><span class="stat-label">Total Population</span><span class="stat-value text-primary">' + (p.total_population || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Households</span><span class="stat-value">' + (p.households || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Avg Household Size</span><span class="stat-value">' + (p.average_household_size || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Pending Residents</span><span class="stat-value text-warning">' + (p.pending_residents || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Approved Residents</span><span class="stat-value text-success">' + (p.approved_residents || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Rejected Residents</span><span class="stat-value text-danger">' + (p.rejected_residents || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Pending Households</span><span class="stat-value text-warning">' + (p.pending_households || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Approved Households</span><span class="stat-value text-success">' + (p.approved_households || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Rejected Households</span><span class="stat-value text-danger">' + (p.rejected_households || 0) + '</span></div>';
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    function renderDemographics(d) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Demographics</div>';
        html += '<div class="stat-row"><span class="stat-label"><i class="bi bi-gender-male me-1"></i>Male</span><span class="stat-value">' + (d.male || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label"><i class="bi bi-gender-female me-1"></i>Female</span><span class="stat-value">' + (d.female || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Children (&lt;15)</span><span class="stat-value">' + (d.children || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Youth (15-30)</span><span class="stat-value">' + (d.youth || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Adults (31-59)</span><span class="stat-value">' + (d.adults || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Senior Citizens</span><span class="stat-value">' + (d.senior_citizens || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">PWD</span><span class="stat-value">' + (d.pwd || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Solo Parents</span><span class="stat-value">' + (d.solo_parents || 0) + '</span></div>';
        if (d.indigenous_people > 0) {
            html += '<div class="stat-row"><span class="stat-label">Indigenous People</span><span class="stat-value">' + d.indigenous_people + '</span></div>';
        }
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    function renderSocioEconomic(se) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Socio-Economic</div>';
        html += '<div class="stat-row"><span class="stat-label">Employment Rate</span><span class="stat-value text-success">' + (se.employment_rate || 0) + '%</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Unemployment Rate</span><span class="stat-value text-danger">' + (se.unemployment_rate || 0) + '%</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Students</span><span class="stat-value">' + (se.students || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Self-Employed</span><span class="stat-value">' + (se.self_employed || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Retired</span><span class="stat-value">' + (se.retired || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Avg Monthly Income</span><span class="stat-value">' + (se.average_monthly_income || 'N/A') + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Top Occupation</span><span class="stat-value" style="font-size:0.75rem;">' + (se.most_common_occupation || 'N/A') + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Top Skill</span><span class="stat-value" style="font-size:0.75rem;">' + (se.most_common_skill || 'N/A') + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Top Education</span><span class="stat-value" style="font-size:0.75rem;">' + (se.highest_educational_attainment || 'N/A') + '</span></div>';
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    function renderHealthcare(hc) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Healthcare</div>';
        html += '<div class="stat-row"><span class="stat-label">PhilHealth Members</span><span class="stat-value">' + (hc.philhealth_members || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">PWD</span><span class="stat-value">' + (hc.pwd || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Senior Citizens</span><span class="stat-value">' + (hc.senior_citizens || 0) + '</span></div>';
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    function renderGovAssistance(ga) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Gov. Assistance</div>';
        html += '<div class="stat-row"><span class="stat-label">4Ps</span><span class="stat-value">' + (ga.four_ps || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Scholarship</span><span class="stat-value">' + (ga.scholarship || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Livelihood (TUPAD)</span><span class="stat-value">' + (ga.livelihood_program || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Senior Pension</span><span class="stat-value">' + (ga.senior_pension || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">PWD Assistance</span><span class="stat-value">' + (ga.pwd_assistance || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Other Assistance</span><span class="stat-value">' + (ga.other_assistance || 0) + '</span></div>';
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    function renderParticipation(pt) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Community Participation</div>';
        html += '<div class="stat-row"><span class="stat-label">Participation Rate</span><span class="stat-value text-primary">' + (pt.participation_rate || 0) + '%</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Community Events</span><span class="stat-value">' + (pt.community_events || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Attendance Rate</span><span class="stat-value">' + (pt.attendance_rate || 0) + '%</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Most Active HH</span><span class="stat-value" style="font-size:0.75rem;">' + (pt.most_active_household || 'N/A') + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Most Active Res.</span><span class="stat-value" style="font-size:0.75rem;">' + (pt.most_active_resident || 'N/A') + '</span></div>';
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    function renderPaymentStats(py) {
        var html = '<div class="stat-section">';
        html += '<div class="stat-section-title">Payment Analytics</div>';
        html += '<div class="stat-row"><span class="stat-label">Collection Rate</span><span class="stat-value text-success">' + (py.collection_percentage || 0) + '%</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Collected</span><span class="stat-value">' + (py.collected_amount || '₱0') + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Outstanding</span><span class="stat-value text-danger">' + (py.outstanding_balance || '₱0') + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Paid HH</span><span class="stat-value text-success">' + (py.paid_households || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Partial HH</span><span class="stat-value text-warning">' + (py.partial_payments || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Unpaid HH</span><span class="stat-value text-danger">' + (py.unpaid_households || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Excellent Payers</span><span class="stat-value text-success">' + (py.excellent_payers || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Good Payers</span><span class="stat-value text-info">' + (py.good_payers || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">Moderate Payers</span><span class="stat-value text-warning">' + (py.moderate_payers || 0) + '</span></div>';
        html += '<div class="stat-row"><span class="stat-label">High-Risk Payers</span><span class="stat-value text-danger">' + (py.high_risk_payers || 0) + '</span></div>';
        html += '</div>';
        document.getElementById('stats-content').innerHTML += html;
    }

    document.getElementById('btn-search').addEventListener('click', function() {
        var q = document.getElementById('search-input').value.trim();
        if (!q) return;
        var vid = focusSelect.value;
        var url = '/gis/search?q=' + encodeURIComponent(q);
        if (vid) url += '&village_id=' + vid;
        fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success && res.data.length > 0) {
                householdLayer.clearLayers();
                allMarkers = [];
                res.data.forEach(function(h) {
                    if (h.latitude && h.longitude) {
                        var m = createHouseholdMarker(h);
                        m.addTo(householdLayer);
                        allMarkers.push({ type: 'household', marker: m, data: h });
                    }
                });
                if (allMarkers.length > 0) {
                    fitToMarkers(allMarkers);
                    if (allMarkers.length === 1) allMarkers[0].marker.openPopup();
                }
            }
        });
    });

    document.getElementById('btn-apply-filter').addEventListener('click', function() {
        var params = new URLSearchParams();
        var vid = document.getElementById('focus-village').value;
        var st = document.getElementById('filter-status').value;
        var ap = document.getElementById('filter-approval').value;
        var bl = document.getElementById('filter-block').value;
        var mp = document.getElementById('filter-mapped').value;
        if (vid) params.set('village_id', vid);
        if (st) params.set('status', st);
        if (ap) params.set('approval_status', ap);
        if (bl) params.set('block_number', bl);
        if (mp === 'mapped') params.set('mapped', '1');
        if (mp === 'unmapped') params.set('unmapped', '1');
        fetch('/gis/filter?' + params.toString()).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) {
                householdLayer.clearLayers();
                allMarkers = [];
                res.data.forEach(function(h) {
                    if (h.latitude && h.longitude) {
                        var m = createHouseholdMarker(h);
                        m.addTo(householdLayer);
                        allMarkers.push({ type: 'household', marker: m, data: h });
                    }
                });
                if (allMarkers.length > 0) fitToMarkers(allMarkers);
            }
        });
    });

    document.getElementById('btn-clear-filter').addEventListener('click', function() {
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-approval').value = '';
        document.getElementById('filter-block').value = '';
        document.getElementById('filter-mapped').value = '';
        document.getElementById('search-input').value = '';
        var vid = document.getElementById('focus-village').value;
        loadVillageData(vid);
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

    window.refreshGisData = function() {
        loadVillageData(document.getElementById('focus-village').value);
    };

    window.resetGisFilters = function() {
        document.getElementById('focus-village').value = '';
        document.getElementById('search-input').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-approval').value = '';
        document.getElementById('filter-block').value = '';
        document.getElementById('filter-mapped').value = '';
        loadVillageData('');
    };
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/gis/index.blade.php ENDPATH**/ ?>