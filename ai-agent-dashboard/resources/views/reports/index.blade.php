@extends('layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Activity Reports</h1>
    </div>

    <!-- Activity Table -->
    <div class="card shadow mb-4">
        <div class="card-header flex-column flex-md-row d-flex justify-content-between align-items-md-center py-3">
            <h6 class="m-0 font-weight-bold text-primary">Agent Activity Log</h6>

            <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0">

                <!-- Status Filters -->
                <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                    <button type="button" class="btn btn-secondary filter-btn active" data-status="all">
                        All <span class="badge badge-light ml-1 count">{{ $counts['all'] }}</span>
                    </button>
                    <button type="button" class="btn btn-success filter-btn" data-status="completed">
                        Completed <span class="badge badge-light ml-1 count">{{ $counts['completed'] }}</span>
                    </button>
                    <button type="button" class="btn btn-danger filter-btn" data-status="failed">
                        Failed <span class="badge badge-light ml-1 count">{{ $counts['failed'] }}</span>
                    </button>
                    <button type="button" class="btn btn-warning filter-btn" data-status="pending">
                        Pending <span class="badge badge-light ml-1 count">{{ $counts['pending'] }}</span>
                    </button>
                    <button type="button" class="btn btn-info filter-btn" data-status="in-progress">
                        In Progress <span class="badge badge-light ml-1 count">{{ $counts['in-progress'] }}</span>
                    </button>
                    <button type="button" class="btn btn-dark filter-btn" data-status="log">
                        Logs <span class="badge badge-light ml-1 count">{{ $counts['log'] }}</span>
                    </button>
                </div>

                <!-- Export Buttons -->
                <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                    <button id="exportCsvBtn" class="btn btn-outline-success">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button id="exportJsonBtn" class="btn btn-outline-info">
                        <i class="fas fa-file-code"></i> JSON
                    </button>
                </div>

                <!-- Date Quick Filters -->
                <div class="btn-group btn-group-sm mr-2 mb-2" role="group">
                    <button type="button" class="btn btn-outline-primary date-btn" data-range="today">Today</button>
                    <button type="button" class="btn btn-outline-primary date-btn" data-range="7d">7d</button>
                    <button type="button" class="btn btn-outline-primary date-btn" data-range="30d">30d</button>
                    <button type="button" class="btn btn-outline-primary date-btn active" data-range="all">All</button>
                </div>

                <!-- From / To -->
                <div class="d-flex align-items-center mr-2 mb-2">
                    <input type="date" id="fromDate" class="form-control form-control-sm mr-1">
                    <input type="date" id="toDate" class="form-control form-control-sm">
                </div>

                <!-- Search -->
                <input type="text" id="searchInput" class="form-control form-control-sm mb-2 mb-md-0"
                       placeholder="Search tasks/agent..." style="min-width: 200px;">
            </div>
        </div>

        <div class="card-body">
            <!-- Total visible counter -->
            <div class="mb-2">
                <strong>Total visible tasks:
                    <span id="visibleCount" class="badge badge-secondary px-2 py-1">{{ $counts['all'] }}</span>
                </strong>
            </div>

            <div class="table-responsive">
                <table id="activityTable" class="table table-bordered table-sm mb-0" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Time</th>
                            <th style="width: 140px;">Agent</th>
                            <th style="width: 160px;">Status</th>
                            <th>Task</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            @php
                                $rowClass = '';
                                $badgeClass = 'badge-secondary';
                                $icon = '📝'; $label = ucfirst($entry['status']);

                                switch ($entry['status']) {
                                    case 'completed': $rowClass='table-success'; $badgeClass='badge-success'; $icon='✅'; $label='Completed'; break;
                                    case 'failed': $rowClass='table-danger'; $badgeClass='badge-danger'; $icon='❌'; $label='Failed'; break;
                                    case 'pending': $rowClass='table-warning'; $badgeClass='badge-warning'; $icon='⏳'; $label='Pending'; break;
                                    case 'in-progress': $rowClass='table-info'; $badgeClass='badge-info'; $icon='📤'; $label='In Progress'; break;
                                }
                            @endphp

                            <tr class="{{ $rowClass }}" 
                                data-status="{{ $entry['status'] }}" 
                                data-time="{{ $entry['time'] }}">
                                <td>{{ $entry['time'] }}</td>
                                <td>{{ $entry['agent'] }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $icon }} {{ $label }}</span></td>
                                <td>{{ $entry['task'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No activity found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 right-0 p-3" style="z-index: 1080; right: 0; bottom: 0;">
        <div id="exportToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
            <div class="toast-header">
                <strong class="mr-auto text-success">Export</strong>
                <small>Just now</small>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body" id="exportToastBody">
                ✅ Exported successfully.
            </div>
        </div>
    </div>

    <script>
        (function () {
            function ready(fn) {
                if (document.readyState !== 'loading') fn();
                else document.addEventListener('DOMContentLoaded', fn, { once: true });
            }

            ready(function () {
                const rows = Array.from(document.querySelectorAll('#activityTable tbody tr'));
                const statusBtns = document.querySelectorAll('.filter-btn');
                const dateBtns = document.querySelectorAll('.date-btn');
                const searchInput = document.getElementById('searchInput');
                const fromDate = document.getElementById('fromDate');
                const toDate = document.getElementById('toDate');
                const visibleCountEl = document.getElementById('visibleCount');
                const toastEl = $('#exportToast');
                const toastBody = document.getElementById('exportToastBody');

                function parseDate(str) { return new Date(str.replace(/-/g, '/')); }

                function updateCounts() {
                    let counts = { all: 0, completed: 0, failed: 0, pending: 0, "in-progress": 0, log: 0 };

                    $("#activityTable tbody tr:visible").each(function () {
                        const status = $(this).data("status");
                        counts.all++;
                        if (counts[status] !== undefined) counts[status]++;
                    });

                    $(".filter-btn[data-status='all'] .count").text(counts.all);
                    $(".filter-btn[data-status='completed'] .count").text(counts.completed);
                    $(".filter-btn[data-status='failed'] .count").text(counts.failed);
                    $(".filter-btn[data-status='pending'] .count").text(counts.pending);
                    $(".filter-btn[data-status='in-progress'] .count").text(counts["in-progress"]);
                    $(".filter-btn[data-status='log'] .count").text(counts.log);

                    visibleCountEl.textContent = counts.all;
                }

                function applyFilter() {
                    const activeStatus = document.querySelector('.filter-btn.active')?.dataset.status || 'all';
                    const activeRange = document.querySelector('.date-btn.active')?.dataset.range || 'all';
                    const q = (searchInput.value || '').toLowerCase();

                    const now = new Date();
                    let rangeStart = null;
                    if (activeRange === 'today') { rangeStart = new Date(); rangeStart.setHours(0,0,0,0); }
                    else if (activeRange === '7d') { rangeStart = new Date(now); rangeStart.setDate(now.getDate() - 7); }
                    else if (activeRange === '30d') { rangeStart = new Date(now); rangeStart.setDate(now.getDate() - 30); }

                    const from = fromDate.value ? parseDate(fromDate.value) : null;
                    const to = toDate.value ? parseDate(toDate.value) : null;

                    rows.forEach(row => {
                        const status = row.dataset.status;
                        const text = row.textContent.toLowerCase();
                        const timeStr = row.dataset.time;
                        const rowDate = new Date(timeStr.replace(/-/g,'/'));

                        const matchesStatus = (activeStatus === 'all' || status === activeStatus);
                        const matchesSearch = (q === '' || text.includes(q));

                        let matchesDate = true;
                        if (rangeStart) matchesDate = (rowDate >= rangeStart);
                        if (from) matchesDate = matchesDate && (rowDate >= from);
                        if (to) { let toEnd=new Date(to); toEnd.setHours(23,59,59,999); matchesDate = matchesDate && (rowDate <= toEnd); }

                        row.style.display = (matchesStatus && matchesSearch && matchesDate) ? '' : 'none';
                    });

                    updateCounts();
                }

                // Bind filters
                statusBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        statusBtns.forEach(b => b.classList.remove('active'));
                        this.classList.add('active'); applyFilter();
                    });
                });

                dateBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        dateBtns.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        fromDate.value = ''; toDate.value = '';
                        applyFilter();
                    });
                });

                [searchInput, fromDate, toDate].forEach(el => {
                    el.addEventListener('input', applyFilter);
                });

                // Export visible rows
                function exportTable(format) {
                    const visibleRows = $("#activityTable tbody tr:visible").map(function () {
                        const cols = $(this).children("td").map(function () {
                            return $(this).text().trim();
                        }).get();
                        return { time: cols[0], agent: cols[1], status: cols[2], task: cols[3] };
                    }).get();

                    if (visibleRows.length === 0) {
                        toastBody.textContent = "⚠️ No visible tasks to export.";
                        toastEl.toast('show');
                        return;
                    }

                    if (format === "csv") {
                        let csv = "Time,Agent,Status,Task\n";
                        visibleRows.forEach(r => { csv += `"${r.time}","${r.agent}","${r.status}","${r.task}"\n`; });
                        const blob = new Blob([csv], { type: "text/csv" });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement("a");
                        a.href = url; a.download = "report-" + new Date().toISOString().slice(0,10) + ".csv"; 
                        a.click(); URL.revokeObjectURL(url);
                        toastBody.textContent = `✅ Exported ${visibleRows.length} tasks to CSV.`;
                    }
                    if (format === "json") {
                        const blob = new Blob([JSON.stringify(visibleRows, null, 2)], { type: "application/json" });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement("a");
                        a.href = url; a.download = "report-" + new Date().toISOString().slice(0,10) + ".json"; 
                        a.click(); URL.revokeObjectURL(url);
                        toastBody.textContent = `✅ Exported ${visibleRows.length} tasks to JSON.`;
                    }

                    toastEl.toast('show');
                }

                document.getElementById("exportCsvBtn").addEventListener("click", () => exportTable("csv"));
                document.getElementById("exportJsonBtn").addEventListener("click", () => exportTable("json"));

                // Initial
                applyFilter();
            });
        })();
    </script>
@endsection
