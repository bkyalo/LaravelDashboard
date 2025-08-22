@extends('layouts.app')

@push('styles')
<style>
    /* Card Styles */
    .table-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.04);
        margin-bottom: 2rem;
        padding: 1.5rem;
    }

    .stat-card {
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
        background: #fff;
    }

    .stat-card-1 { border-color: #3b82f6; }
    .stat-card-2 { border-color: #10b981; }
    .stat-card-3 { border-color: #f59e0b; }
    .stat-card-4 { border-color: #8b5cf6; }
    .stat-card-5 { border-color: #ef4444; }

    /* Chart Container */
    .chart-container {
        height: 400px;
        position: relative;
        margin: 1.5rem 0;
    }

    /* Sort Link */
    .sort-link {
        color: #94a3b8;
        text-decoration: none;
        margin-left: 0.5rem;
        transition: color 0.2s ease;
    }
    .sort-link:hover {
        color: #3b82f6;
    }

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .pagination {
        --bs-pagination-padding-x: 0.75rem;
        --bs-pagination-padding-y: 0.375rem;
        --bs-pagination-font-size: 0.875rem;
        --bs-pagination-color: #4b5563;
        --bs-pagination-bg: #fff;
        --bs-pagination-border-width: 1px;
        --bs-pagination-border-color: #e5e7eb;
        --bs-pagination-border-radius: 0.5rem;
        --bs-pagination-hover-color: #1f2937;
        --bs-pagination-hover-bg: #f3f4f6;
        --bs-pagination-hover-border-color: #e5e7eb;
        --bs-pagination-focus-color: #1f2937;
        --bs-pagination-focus-bg: #f3f4f6;
        --bs-pagination-active-color: #fff;
        --bs-pagination-active-bg: #3b82f6;
        --bs-pagination-active-border-color: #3b82f6;
        --bs-pagination-disabled-color: #9ca3af;
        --bs-pagination-disabled-bg: #fff;
        --bs-pagination-disabled-border-color: #e5e7eb;
    }

    .pagination .page-item {
        margin: 0 0.25rem;
    }

    .pagination .page-link {
        min-width: 2.5rem;
        height: 2.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--bs-pagination-border-radius);
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        padding: var(--bs-pagination-padding-y) var(--bs-pagination-padding-x);
        color: var(--bs-pagination-color);
        background: var(--bs-pagination-bg);
        border: var(--bs-pagination-border-width) solid var(--bs-pagination-border-color);
    }

    .pagination .page-item.active .page-link {
        color: var(--bs-pagination-active-color);
        background: var(--bs-pagination-active-bg);
        border-color: var(--bs-pagination-active-border-color);
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }

    .pagination .page-item:not(.active) .page-link:hover {
        color: var(--bs-pagination-hover-color);
        background: var(--bs-pagination-hover-bg);
        border-color: var(--bs-pagination-hover-border-color);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .pagination .page-item.disabled .page-link {
        color: var(--bs-pagination-disabled-color);
        background: var(--bs-pagination-disabled-bg);
        border-color: var(--bs-pagination-disabled-border-color);
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Pagination Info */
    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        padding: 0.5rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .chart-container {
            height: 300px;
        }
        .table-card {
            padding: 1rem;
        }
        .stat-card {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="h3 mb-0 text-dark">PDC Courses Management</h1>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        @foreach([
            ['title' => 'Total PDC Courses', 'stat' => 'total_courses', 'icon' => 'bi-collection-play', 'color' => 'stat-card-1', 'icon_color' => 'text-primary'],
            ['title' => 'Total Enrollments', 'stat' => 'total_enrollments', 'icon' => 'bi-people', 'color' => 'stat-card-2', 'icon_color' => 'text-success'],
            ['title' => 'New This Month', 'stat' => 'new_courses', 'icon' => 'bi-plus-circle', 'color' => 'stat-card-3', 'icon_color' => 'text-warning'],
            ['title' => 'No Enrollments', 'stat' => 'courses_without_enrollments', 'icon' => 'bi-person-x', 'color' => 'stat-card-5', 'icon_color' => 'text-danger']
        ] as $card)
        <div class="col-md-3">
            <div class="stat-card {{ $card['color'] }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">{{ $card['title'] }}</h6>
                        <h3 class="mb-2">{{ number_format($stats[$card['stat']]['current']) }}</h3>
                        @if($stats[$card['stat']]['has_change'])
                        <small class="text-{{ $stats[$card['stat']]['is_positive'] ? 'success' : 'danger' }}">
                            <i class="bi {{ $stats[$card['stat']]['is_positive'] ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            {{ abs($stats[$card['stat']]['percentage']) }}% from last month
                        </small>
                        @endif
                    </div>
                    <i class="bi {{ $card['icon'] }} fs-1 {{ $card['icon_color'] }}"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- PDC Courses Table -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center p-4">
                    <h2 class="card-title mb-0 h4">All PDC Courses</h2>
                    <div class="d-flex align-items-center gap-3">
                        <form action="{{ route('pdc-courses.index') }}" method="GET" class="d-flex align-items-center">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Search PDC courses..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary btn-sm ms-2">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                        <span class="text-muted small">
                            @if($pdcCourses->total() > 0)
                                Showing {{ $pdcCourses->firstItem() }} to {{ $pdcCourses->lastItem() }} of {{ $pdcCourses->total() }} PDC courses
                            @else
                                No PDC courses found
                            @endif
                        </span>
                    </div>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="ps-4">#</th>
                                <th scope="col">
                                    Course Name
                                    <a href="{{ route('pdc-courses.index', array_merge(request()->query(), ['sort_by' => 'course_name', 'sort_dir' => request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'course_name' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                                <th scope="col">Short Name</th>
                                <th scope="col" class="text-center">Students</th>
                                <th scope="col" class="text-center">Instructors</th>
                                <th scope="col" class="text-center">Total</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pdcCourses as $index => $course)
                            <tr>
                                <td class="ps-4">{{ $pdcCourses->firstItem() + $index }}</td>
                                <td>{{ $course->course_name }}</td>
                                <td>{{ $course->shortname }}</td>
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
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-eye-slash-fill me-1"></i> Hidden
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="mt-2 mb-0">No PDC courses found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($pdcCourses->hasPages())
                <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
                    <div class="pagination-info mb-3 mb-md-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Showing <span class="fw-semibold">{{ $pdcCourses->firstItem() }}</span> to 
                        <span class="fw-semibold">{{ $pdcCourses->lastItem() }}</span> of 
                        <span class="fw-semibold">{{ $pdcCourses->total() }}</span> PDC courses
                    </div>
                    <div class="pagination-container">
                        {{ $pdcCourses->withQueryString()->onEachSide(1)->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enrollments by Category Chart -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header p-4">
                    <h5 class="card-title mb-0">Enrollments by Category</h5>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container">
                        <canvas id="enrollmentsByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Sections -->
    <div class="row g-4 mb-5">
        <!-- Top Enrolled PDC Courses -->
        <div class="col-md-6">
            <div class="table-card h-100">
                <div class="card-header p-4">
                    <h2 class="card-title h4">Top Enrolled PDC Courses</h2>
                </div>
                <div class="table-responsive p-3">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Course Name</th>
                                <th scope="col" class="text-end">Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topEnrolledCourses as $course)
                            <tr>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="{{ $course->course_name }}">
                                        {{ $course->course_name }}
                                    </div>
                                    <div class="text-muted small">{{ $course->shortname }}</div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary">{{ number_format($course->enrollment_count) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-3">No enrollment data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PDC Courses by Category -->
        <div class="col-md-6">
            <div class="table-card h-100">
                <div class="card-header p-4">
                    <h2 class="card-title h4">PDC Courses by Category</h2>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container">
                        <canvas id="pdcCoursesByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Modified PDC Courses -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header p-4">
                    <h2 class="card-title h4">Recently Modified PDC Courses</h2>
                </div>
                <div class="table-responsive p-3">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Course Name</th>
                                <th scope="col">Short Name</th>
                                <th scope="col">Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentlyModified as $course)
                            <tr>
                                <td>{{ $course->fullname }}</td>
                                <td>{{ $course->shortname }}</td>
                                <td class="text-nowrap">
                                    <i class="bi bi-clock-history me-1 text-muted"></i>
                                    {{ $course->last_modified }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-3">No recently modified PDC courses</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enrollments by Category Chart
    const categoryCtx = document.getElementById('enrollmentsByCategoryChart').getContext('2d');
    const categoryData = @json($enrollmentsByCategory);
    
    const categoryLabels = categoryData.map(item => item.category_name);
    const categoryValues = categoryData.map(item => item.enrollments_count);
    
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Enrollments',
                data: categoryValues,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(20, 184, 166, 0.7)',
                    'rgba(236, 72, 153, 0.7)',
                    'rgba(234, 88, 12, 0.7)',
                    'rgba(220, 38, 38, 0.7)'
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(139, 92, 246, 1)',
                    'rgba(20, 184, 166, 1)',
                    'rgba(236, 72, 153, 1)',
                    'rgba(234, 88, 12, 1)',
                    'rgba(220, 38, 38, 1)'
                ],
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.x} enrollments`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { 
                        display: true,
                        drawBorder: false
                    },
                    ticks: { 
                        precision: 0,
                        padding: 10
                    }
                },
                y: {
                    grid: { 
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        padding: 10
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            layout: {
                padding: {
                    left: 20,
                    right: 20,
                    top: 20,
                    bottom: 20
                }
            }
        }
    });

    // PDC Courses by Category Chart
    const coursesCtx = document.getElementById('pdcCoursesByCategoryChart').getContext('2d');
    const categories = @json($coursesPerCategory->pluck('category_name'));
    const courseCounts = @json($coursesPerCategory->pluck('course_count'));
    
    new Chart(coursesCtx, {
        type: 'doughnut',
        data: {
            labels: categories,
            datasets: [{
                data: courseCounts,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(234, 88, 12, 0.8)',
                    'rgba(220, 38, 38, 0.8)'
                ],
                borderWidth: 1
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
                        font: { size: 14 }
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
            }
        }
    });
});
</script>
@endpush