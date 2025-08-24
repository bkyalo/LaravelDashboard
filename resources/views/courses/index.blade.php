@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
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
        height: 400px; /* Reduced from 600px */
        margin-bottom: 2rem;
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        padding: 0;
        list-style: none;
        margin: 0;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .pagination .page-item {
        margin: 0;
    }

    .pagination .page-item:not(:first-child) .page-link {
        margin-left: -1px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .pagination .page-item:not(:last-child) .page-link {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .pagination .page-item .page-link {
        color: #037b90;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        transition: all 0.2s ease;
        text-decoration: none;
        background: white;
        position: relative;
    }

    .pagination .page-item.active .page-link {
        background-color: #037b90;
        color: white;
        border-color: #037b90;
        z-index: 1;
    }

    .pagination .page-item.disabled .page-link {
        color: #cbd5e1;
        background-color: #f8fafc;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }
    
    .pagination .page-link:not(.disabled):hover {
        background-color: #f1f5f9;
        z-index: 2;
    }
    
    .page-link {
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 8px !important;
        transition: all 0.2s ease;
    }
    
    .page-link:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        color: #3b82f6;
    }
    
    .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .page-item.disabled .page-link {
        background-color: #f8fafc;
        color: #94a3b8;
    }
    
    /* Sort links */
    .sort-link {
        color: #94a3b8;
        margin-left: 0.25rem;
        text-decoration: none;
    }
    
    .sort-link:hover {
        color: #3b82f6;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-card .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .table-card .card-header .d-flex {
            width: 100%;
        }
        
        .table-card .card-header form {
            width: 100%;
            max-width: 100%;
        }
        
        .table-card .card-header input[type="text"] {
            width: 100% !important;
        }
        
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }
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
<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Load Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<script>
// Function to update URL parameters without page reload
function updateUrlParameter(param, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(param, value);
    window.history.pushState({}, '', url);
}

// Handle sort direction toggle
function toggleSortDirection(currentDir) {
    return currentDir === 'asc' ? 'desc' : 'asc';
}

// Initialize tooltips
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Handle search form submission
    const searchForm = document.querySelector('form[action="{{ route('courses.index') }}"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (!searchInput.value.trim()) {
                // Remove search parameter if search is empty
                const url = new URL(window.location.href);
                url.searchParams.delete('search');
                window.location.href = url.toString();
                e.preventDefault();
            }
        });
    }
});
</script>
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
    
    // Create the pie chart
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: categories,
            datasets: [{
                data: courseCounts,
                backgroundColor: backgroundColors,
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 15,
                hoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%',
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart',
                animateScale: true,
                animateRotate: true
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 10,
                    bottom: 10
                }
            }
        }
    });
});

// Enrollments by Category Bar Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('enrollmentsByCategoryChart').getContext('2d');
    
    // Prepare data from PHP
    const enrollmentsData = @json($enrollmentsByCategory);
    
    // Sort by enrollment count in descending order
    enrollmentsData.sort((a, b) => b.enrollments_count - a.enrollments_count);
    
    // Prepare data for the chart
    const categories = [];
    const enrollmentCounts = [];
    const backgroundColors = [];
    const borderColors = [];
    
    // Color palette
    const colorPalette = [
        'rgba(54, 162, 235, 0.7)',  // Blue
        'rgba(255, 99, 132, 0.7)',  // Red
        'rgba(75, 192, 192, 0.7)',  // Teal
        'rgba(255, 159, 64, 0.7)',  // Orange
        'rgba(153, 102, 255, 0.7)', // Purple
        'rgba(255, 205, 86, 0.7)',  // Yellow
        'rgba(201, 203, 207, 0.7)'  // Gray
    ];
    
    // Process data
    enrollmentsData.forEach((item, index) => {
        // Add indentation based on depth
        const indent = '    '.repeat(item.depth - 1);
        categories.push(`${indent}${item.category_name}`);
        enrollmentCounts.push(item.enrollments_count);
        
        // Assign colors
        const colorIndex = index % colorPalette.length;
        backgroundColors.push(colorPalette[colorIndex]);
        borderColors.push(colorPalette[colorIndex].replace('0.7', '1'));
    });
    
    // Create the bar chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Number of Enrollments',
                data: enrollmentCounts,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 4,
                barThickness: 'flex',
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Horizontal bars
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Enrollments',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            family: 'monospace', // Better for indentation
                            size: 12
                        },
                        callback: function(value) {
                            // Return the label as is (with indentation)
                            return this.getLabelForValue(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            // Remove indentation for tooltips
                            const label = context.label.replace(/\s{4}/g, '');
                            return `${label}: ${context.raw.toLocaleString()} enrollments`;
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    displayColors: false
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
});

// Initialize DataTable with server-side processing
$(document).ready(function() {
    // Check if jQuery is loaded
    if (typeof jQuery == 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // Check if DataTable is available
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables is not loaded');
        return;
    }

    const table = $('#courses-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("courses.data") }}', // We'll create this route next
            type: 'GET',
            data: function(d) {
                // You can add additional parameters here if needed
            },
            dataSrc: function(json) {
                $('#courses-count').text(`Showing ${json.recordsFiltered} courses`);
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                $('#courses-count').text('Error loading courses');
                return [];
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { 
                data: 'course_name', 
                name: 'course_name',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<span class="text-truncate d-inline-block" style="max-width: 300px;" title="${data}">${data}</span>`;
                    }
                    return data;
                }
            },
            { 
                data: 'student_count', 
                name: 'student_count',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-primary bg-opacity-10 text-primary">${parseInt(data).toLocaleString()}</span>`;
                }
            },
            { 
                data: 'instructor_count', 
                name: 'instructor_count',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-info bg-opacity-10 text-info">${parseInt(data).toLocaleString()}</span>`;
                }
            },
            { 
                data: null,
                className: 'text-center fw-bold',
                render: function(data) {
                    const total = parseInt(data.student_count) + parseInt(data.instructor_count);
                    return total.toLocaleString();
                },
                orderable: false
            },
            { 
                data: 'visible',
                name: 'visible',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-success-light"><i class="bi bi-check-circle-fill"></i> Active</span>'
                        : '<span class="badge bg-danger-light"><i class="bi bi-eye-slash-fill"></i> Hidden</span>';
                }
            }
        ],
        order: [[1, 'asc']], // Default sort by course name
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>', // Custom DOM layout
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search courses...",
            lengthMenu: "Show _MENU_ courses per page",
            zeroRecords: "No courses found",
            info: "Showing _START_ to _END_ of _TOTAL_ courses",
            infoEmpty: "No courses available",
            infoFiltered: "(filtered from _MAX_ total courses)",
            paginate: {
                first: "First",
                last: "Last",
                next: "<i class='bi bi-chevron-right'></i>",
                previous: "<i class='bi bi-chevron-left'></i>"
            }
        },
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
    });

    // Add custom search input
    $('.dataTables_filter input')
        .attr('placeholder', 'Search courses...')
        .addClass('form-control form-control-sm')
        .css('width', '250px', 'display', 'inline-block');
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

        <!-- Total Enrollments Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-3">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-number">{{ number_format($stats['total_enrollments']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['total_enrollments']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['total_enrollments']['has_change'])
                        <i class="bi {{ $stats['total_enrollments']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['total_enrollments']['delta']) }} ({{ abs($stats['total_enrollments']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Total Enrollments</div>
            </div>
        </div>

        <!-- Active Users Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-4">
                <div class="stat-icon"><i class="bi bi-person-check"></i></div>
                <div class="stat-number">{{ number_format($stats['active_users']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['active_users']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['active_users']['has_change'])
                        <i class="bi {{ $stats['active_users']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['active_users']['delta']) }} ({{ abs($stats['active_users']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Active Users (30d)</div>
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

    <!-- Charts Row -->
    <div class="row">
        <!-- Courses Per Category Chart -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Courses by Category</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="coursesByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments by Category Chart -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Enrollments by Category (Excluding PDC)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="enrollmentsByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses Enrollment Stats Row -->
    <div class="row g-4">
        <!-- Top Enrolled Courses -->
        <div class="col-lg-6">
            <div class="table-card h-100">
                <div class="card-header">
                    <h2 class="card-title">Top Enrolled Courses</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Course Name</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topEnrolledCourses as $index => $course)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-truncate" style="max-width: 200px;" title="{{ $course->course_name }}">
                                    {{ $course->course_name }}
                                </td>
                                <td>
                                    <span class="enrollment-count">
                                        <i class="bi bi-people-fill text-primary"></i>
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
                                <td colspan="4" class="text-center">No courses found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Least Enrolled Courses -->
        <div class="col-lg-6">
            <div class="table-card h-100">
                <div class="card-header">
                    <h2 class="card-title">Least Enrolled Courses</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Course Name</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leastEnrolledCourses as $index => $course)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-truncate" style="max-width: 200px;" title="{{ $course->course_name }}">
                                    {{ $course->course_name }}
                                </td>
                                <td>
                                    <span class="enrollment-count">
                                        <i class="bi bi-people-fill text-primary"></i>
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
                                <td colspan="4" class="text-center">No courses found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- All Courses Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">All Courses</h2>
                    <div class="d-flex align-items-center gap-3">
                        <form action="{{ route('courses.index') }}" method="GET" class="d-flex align-items-center">
                            <input type="text" name="search" class="form-control form-control-sm shadow-sm" 
                                   placeholder="Search courses..." value="{{ request('search') }}" 
                                   style="width: 250px; border-radius: 8px;">
                            <button type="submit" class="btn btn-primary btn-sm ms-2" style="border-radius: 8px;">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                        <span class="text-muted" id="courses-count">
                            @if($allCourses->total() > 0)
                                Showing {{ $allCourses->firstItem() }} to {{ $allCourses->lastItem() }} of {{ $allCourses->total() }} courses
                            @else
                                No courses found
                            @endif
                        </span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">
                                    #
                                    <a href="{{ route('courses.index', array_merge(request()->query(), ['sort_by' => 'id', 'sort_dir' => request('sort_by') === 'id' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'id' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                                <th>
                                    Course Name
                                    <a href="{{ route('courses.index', array_merge(request()->query(), ['sort_by' => 'course_name', 'sort_dir' => request('sort_by') === 'course_name' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'course_name' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                                <th class="text-center" style="width: 15%;">
                                    Students
                                    <a href="{{ route('courses.index', array_merge(request()->query(), ['sort_by' => 'student_count', 'sort_dir' => request('sort_by') === 'student_count' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'student_count' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                                <th class="text-center" style="width: 15%;">
                                    Instructors
                                    <a href="{{ route('courses.index', array_merge(request()->query(), ['sort_by' => 'instructor_count', 'sort_dir' => request('sort_by') === 'instructor_count' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'instructor_count' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                                <th class="text-center" style="width: 15%;">Total</th>
                                <th style="width: 15%;">
                                    Status
                                    <a href="{{ route('courses.index', array_merge(request()->query(), ['sort_by' => 'visible', 'sort_dir' => request('sort_by') === 'visible' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'visible' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allCourses as $index => $course)
                                <tr>
                                    <td class="text-center">{{ $allCourses->firstItem() + $index }}</td>
                                    <td class="text-truncate" style="max-width: 300px;" title="{{ $course->course_name }}">
                                        {{ $course->course_name }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ number_format($course->student_count) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ number_format($course->instructor_count) }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">
                                        {{ number_format($course->student_count + $course->instructor_count) }}
                                    </td>
                                    <td>
                                        @if($course->visible)
                                            <span class="badge bg-success-light"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                        @else
                                            <span class="badge bg-danger-light"><i class="bi bi-eye-slash-fill me-1"></i> Hidden</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="mt-2 mb-0">No courses found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($allCourses->hasPages())
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-center">
                        <nav>
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($allCourses->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">« Prev</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $allCourses->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" rel="prev">« Prev</a>
                                    </li>
                                @endif

                                {{-- Page Number Links --}}
                                @for ($page = 1; $page <= $allCourses->lastPage(); $page++)
                                    <li class="page-item {{ $page == $allCourses->currentPage() ? 'active' : '' }}">
                                        <a class="page-link"
                                            href="{{ $allCourses->url($page) }}&{{ http_build_query(request()->except('page')) }}">{{ $page }}</a>
                                    </li>
                                @endfor

                                {{-- Next Page Link --}}
                                @if ($allCourses->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $allCourses->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" rel="next">Next »</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">Next »</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
