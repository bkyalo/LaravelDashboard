@extends('layouts.app')

@push('styles')
<style>
    .table-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.04); margin-bottom: 2rem; }
    .stat-card { border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid; }
    .stat-card-1 { border-color: #3b82f6; }
    .stat-card-2 { border-color: #10b981; }
    .stat-card-3 { border-color: #f59e0b; }
    .stat-card-4 { border-color: #8b5cf6; }
    .stat-card-5 { border-color: #ef4444; }
    .chart-container { height: 500px; position: relative; }
    .sort-link { color: #94a3b8; text-decoration: none; margin-left: 0.5rem; }
    .sort-link:hover { color: #3b82f6; }
    /* Pagination Styles */
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
    
    .pagination .page-link {
        min-width: 2.5rem;
        height: 2.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 0.25rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .pagination .page-item.active .page-link {
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3), 0 2px 4px -1px rgba(59, 130, 246, 0.2);
    }
    
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0 1rem;
    }
    
    .pagination .page-item:not(.active) .page-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .pagination .page-item.disabled .page-link {
        opacity: 0.6;
    }
    
    /* Pagination info */
    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-dark">PDC Courses Management</h1>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-1">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total PDC Courses</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_courses']['current']) }}</h3>
                        @if($stats['total_courses']['has_change'])
                        <small class="text-{{ $stats['total_courses']['is_positive'] ? 'success' : 'danger' }}">
                            <i class="bi {{ $stats['total_courses']['is_positive'] ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            {{ abs($stats['total_courses']['percentage']) }}% from last month
                        </small>
                        @endif
                    </div>
                    <i class="bi bi-collection-play fs-1 text-primary"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card stat-card-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Enrollments</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_enrollments']['current']) }}</h3>
                        @if($stats['total_enrollments']['has_change'])
                        <small class="text-{{ $stats['total_enrollments']['is_positive'] ? 'success' : 'danger' }}">
                            <i class="bi {{ $stats['total_enrollments']['is_positive'] ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            {{ abs($stats['total_enrollments']['percentage']) }}% from last month
                        </small>
                        @endif
                    </div>
                    <i class="bi bi-people fs-1 text-success"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card stat-card-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">New This Month</h6>
                        <h3 class="mb-0">{{ number_format($stats['new_courses']['current']) }}</h3>
                        @if($stats['new_courses']['has_change'])
                        <small class="text-{{ $stats['new_courses']['is_positive'] ? 'success' : 'danger' }}">
                            <i class="bi {{ $stats['new_courses']['is_positive'] ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                            {{ abs($stats['new_courses']['percentage']) }}% from last month
                        </small>
                        @endif
                    </div>
                    <i class="bi bi-plus-circle fs-1 text-warning"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card stat-card-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">No Enrollments</h6>
                        <h3 class="mb-0">{{ number_format($stats['courses_without_enrollments']['current']) }}</h3>
                        @if($stats['courses_without_enrollments']['has_change'])
                        <small class="text-{{ !$stats['courses_without_enrollments']['is_positive'] ? 'success' : 'danger' }}">
                            <i class="bi {{ !$stats['courses_without_enrollments']['is_positive'] ? 'bi-arrow-down' : 'bi-arrow-up' }}"></i>
                            {{ abs($stats['courses_without_enrollments']['percentage']) }}% from last month
                        </small>
                        @endif
                    </div>
                    <i class="bi bi-person-x fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- PDC Courses Table -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">All PDC Courses</h2>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>
                                    Course Name
                                    <a href="{{ route('pdc-courses.index', array_merge(request()->query(), ['sort_by' => 'course_name', 'sort_dir' => request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="sort-link">
                                        <i class="bi {{ request('sort_by') === 'course_name' && request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                    </a>
                                </th>
                                <th>Short Name</th>
                                <th class="text-center">Students</th>
                                <th class="text-center">Instructors</th>
                                <th class="text-center">Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pdcCourses as $index => $course)
                            <tr>
                                <td>{{ $pdcCourses->firstItem() + $index }}</td>
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
                                        <span class="badge bg-success-light">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-light">
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
                <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
                    <div class="pagination-info mb-3 mb-md-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Showing <span class="fw-semibold">{{ $pdcCourses->firstItem() }}</span> to 
                        <span class="fw-semibold">{{ $pdcCourses->lastItem() }}</span> of 
                        <span class="fw-semibold">{{ $pdcCourses->total() }}</span> PDC courses
                    </div>
                    <div class="mt-2 mt-md-0">
                        {{ $pdcCourses->withQueryString()->onEachSide(1)->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Additional Sections -->
    <div class="row mt-4">
        <!-- Top Enrolled PDC Courses -->
        <div class="col-md-6">
            <div class="table-card h-100">
                <div class="card-header">
                    <h2 class="card-title">Top Enrolled PDC Courses</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th class="text-end">Enrollments</th>
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
                <div class="card-header">
                    <h2 class="card-title">PDC Courses by Category</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="pdcCoursesByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recently Modified PDC Courses -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header">
                    <h2 class="card-title">Recently Modified PDC Courses</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
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
    // PDC Courses by Category Chart
    const ctx = document.getElementById('pdcCoursesByCategoryChart').getContext('2d');
    const categories = @json($coursesPerCategory->pluck('category_name'));
    const courseCounts = @json($coursesPerCategory->pluck('course_count'));
    
    new Chart(ctx, {
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
