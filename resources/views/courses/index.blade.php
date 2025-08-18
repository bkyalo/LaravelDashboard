@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<style>
    /* Table Styles */
    .table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.04);
        margin-bottom: 2rem;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .table-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0,0,0,0.08);
    }
    
    .table-card .card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.03);
        background-color: #f8fafc;
    }
    
    .table-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: #1e293b;
    }
    
    .table {
        margin-bottom: 0;
        color: #334155;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #64748b;
        background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        position: relative;
        white-space: nowrap;
    }
    
    .table th:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 25%;
        height: 50%;
        width: 1px;
        background-color: #e2e8f0;
    }
    
    .table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-color: #f1f5f9;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr {
        position: relative;
    }
    
    .table tbody tr:hover {
        background-color: #f8fafc;
        transform: translateX(4px);
        box-shadow: -4px 0 0 0 #3b82f6;
    }
    
    .table tbody tr td:first-child {
        position: relative;
    }
    
    .table tbody tr:hover td:first-child::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #3b82f6;
        border-radius: 4px 0 0 4px;
    }
    
    .badge {
        padding: 0.4em 0.8em;
        font-weight: 500;
        font-size: 0.75rem;
        border-radius: 6px;
    }
    
    .badge.bg-success-light {
        background-color: #ecfdf5;
        color: #059669;
    }
    
    .badge.bg-danger-light {
        background-color: #fef2f2;
        color: #dc2626;
    }
    
    .enrollment-count {
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
        border: 1px solid rgba(14, 165, 233, 0.2);
    }
    
    tr:hover .enrollment-count {
        background: #0ea5e9;
        color: white;
        transform: scale(1.05);
    }
    
    .enrollment-count i {
        margin-right: 4px;
        font-size: 0.75rem;
    }
    
    /* Stat Cards */
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .stat-card-1 { border-top: 4px solid #4361ee; }
    .stat-card-2 { border-top: 4px solid #3a0ca3; }
    .stat-card-3 { border-top: 4px solid #7209b7; }
    .stat-card-4 { border-top: 4px solid #f72585; }
    .stat-card-5 { border-top: 4px solid #4cc9f0; }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .stat-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .stat-card-1 .stat-icon { color: #4361ee; }
    .stat-card-2 .stat-icon { color: #3a0ca3; }
    .stat-card-3 .stat-icon { color: #7209b7; }
    .stat-card-4 .stat-icon { color: #f72585; }
    .stat-card-5 .stat-icon { color: #4cc9f0; }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        margin: 0.5rem 0;
        font-family: 'Quicksand', sans-serif;
        letter-spacing: -0.5px;
        color: #2c3e50;
        line-height: 1.2;
    }

    .stat-delta {
        font-size: 0.85rem;
        font-weight: 600;
        margin: 0.25rem 0 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-delta.text-success {
        color: #10b981 !important;
    }

    .stat-delta.text-danger {
        color: #ef4444 !important;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
        margin-top: 0.5rem;
    }

    /* Chart Container */
    .chart-container {
        position: relative;
        height: 600px; /* Increased from 400px */
        margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
        .chart-container {
            height: 1000px; /* Increased from 600px for better mobile view */
        }
    }
    
    /* Adjust chart elements for better spacing */
    #coursesByCategoryChart {
        width: 100% !important;
        height: 100% !important;
    }

    /* Tables */
    .table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .table-card .card-header {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-card .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        color: #1f2937;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background-color: #f9fafb;
        color: #4b5563;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 0.75rem 1rem;
        text-align: left;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success-light {
        background-color: #d1fae5;
        color: #065f46;
    }

    .bg-danger-light {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .text-muted {
        color: #6b7280 !important;
    }

    .text-nowrap {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('coursesByCategoryChart').getContext('2d');
    
    // Prepare data from PHP
    const categories = @json($coursesPerCategory->pluck('category_name'));
    const courseCounts = @json($coursesPerCategory->pluck('course_count'));
    
    // Generate colors
    const backgroundColors = [];
    const borderColors = [];
    
    // Use a color palette that works well for charts
    const colorPalette = [
        'rgba(54, 162, 235, 1)',  // Blue
        'rgba(255, 99, 132, 1)',  // Red
        'rgba(75, 192, 192, 1)',  // Teal
        'rgba(255, 159, 64, 1)',  // Orange
        'rgba(153, 102, 255, 1)', // Purple
        'rgba(255, 205, 86, 1)',  // Yellow
        'rgba(201, 203, 207, 1)'  // Gray
    ];
    
    // Assign colors to categories
    for (let i = 0; i < categories.length; i++) {
        const colorIndex = i % colorPalette.length;
        backgroundColors.push(colorPalette[colorIndex]);
        borderColors.push(colorPalette[colorIndex].replace('0.7', '1'));
    }
    
    // Create the chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Number of Courses',
                data: courseCounts,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.9,
                categoryPercentage: 0.9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Makes the chart horizontal
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0,
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.x !== null) {
                                label += new Intl.NumberFormat().format(context.parsed.x);
                            }
                            return label;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 10,
                    bottom: 50 // Increased bottom padding for x-axis labels
                }
            }
        }
    });
});
</script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-dark">Courses Management</h1>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Courses Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-1">
                <div class="stat-icon"><i class="bi bi-journal-bookmark"></i></div>
                <div class="stat-number">{{ number_format($stats['total_courses']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['total_courses']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['total_courses']['has_change'])
                        <i class="bi {{ $stats['total_courses']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['total_courses']['delta']) }} ({{ abs($stats['total_courses']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Total Courses</div>
            </div>
        </div>

        <!-- Active Courses Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-2">
                <div class="stat-icon"><i class="bi bi-lightning-charge"></i></div>
                <div class="stat-number">{{ number_format($stats['active_courses']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['active_courses']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['active_courses']['has_change'])
                        <i class="bi {{ $stats['active_courses']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['active_courses']['delta']) }} ({{ abs($stats['active_courses']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Active (30d)</div>
            </div>
        </div>

        <!-- New Courses Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-3">
                <div class="stat-icon"><i class="bi bi-plus-circle"></i></div>
                <div class="stat-number">{{ number_format($stats['new_courses']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['new_courses']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['new_courses']['has_change'])
                        <i class="bi {{ $stats['new_courses']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['new_courses']['delta']) }} ({{ abs($stats['new_courses']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">New This Month</div>
            </div>
        </div>

        <!-- PDC Courses Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-4">
                <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
                <div class="stat-number">{{ number_format($stats['pdc_courses']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['pdc_courses']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['pdc_courses']['has_change'])
                        <i class="bi {{ $stats['pdc_courses']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['pdc_courses']['delta']) }} ({{ abs($stats['pdc_courses']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">PDC Courses</div>
            </div>
        </div>

        <!-- Courses Without Enrollments Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-5">
                <div class="stat-icon"><i class="bi bi-person-x"></i></div>
                <div class="stat-number">{{ number_format($stats['courses_without_enrollments']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ !$stats['courses_without_enrollments']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['courses_without_enrollments']['has_change'])
                        <i class="bi {{ !$stats['courses_without_enrollments']['is_positive'] ? 'bi-arrow-down-right' : 'bi-arrow-up-right' }}"></i>
                        {{ abs($stats['courses_without_enrollments']['delta']) }} ({{ abs($stats['courses_without_enrollments']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">No Enrollments</div>
            </div>
        </div>
    </div>

    <!-- Top Enrolled Courses Table -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header">
                    <h2 class="card-title">Top Enrolled Courses</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Course Name</th>
                                <th>Short Name</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topEnrolledCourses as $index => $course)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <div class="fw-bold">{{ $course->course_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $course->shortname }}</td>
                                <td>
                                    <span class="enrollment-count">
                                        <i class="bi bi-people-fill"></i>
                                        {{ number_format($course->enrollment_count) }}
                                    </span>
                                </td>
                                <td>
                                    @if($course->visible)
                                        <span class="badge bg-success-light"><i class="bi bi-check-circle-fill"></i> Active</span>
                                    @else
                                        <span class="badge bg-danger-light"><i class="bi bi-eye-slash-fill"></i> Hidden</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No courses found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- <!-- Recently Modified Courses Table -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header">
                    <h2 class="card-title">Recently Modified Courses</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Short Name</th>
                                <th>Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentlyModified as $course)
                            <tr>
                                <td>{{ $course->fullname }}</td>
                                <td>{{ $course->shortname }}</td>
                                <td class="text-nowrap">
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-clock-history me-2 text-muted"></i>
                                        {{ $course->last_modified }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No recently modified courses found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Courses Per Category Chart -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">Courses by Category</h2>
                    <div class="text-muted small">Total Categories: {{ $coursesPerCategory->count() }}</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="coursesByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
