@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .chart-container { height: 500px; margin-bottom: 2rem; }
    .table-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.04); margin-bottom: 2rem; }
    .course-selector { max-width: 400px; margin-left: auto; }
    #timeVsGradeChart { width: 100% !important; height: 100% !important; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Time Spent vs. Grade Correlations</h1>
    </div>
    
    <!-- Course Selection -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('courses.time-vs-grades') }}" class="row g-3">
                        <div class="col-md-8">
                            <label for="course" class="form-label">Select Course</label>
                            <select name="course_id" id="course" class="form-select" onchange="this.form.submit()">
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $selectedCourseId == $course->id ? 'selected' : '' }}>
                                        {{ $course->fullname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @if($selectedCourseId)
        <!-- Scatter Plot -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="table-card">
                    <div class="card-header">
                        <h2 class="card-title">Time Spent vs. Grade Distribution</h2>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="timeVsGradeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="card-header">
                        <h2 class="card-title">User Activity and Grades</h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="userDataTable" class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th class="text-end">Time Spent (min)</th>
                                        <th class="text-end">Average Grade</th>
                                        <th class="text-end">Last Accessed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userData as $user)
                                    <tr>
                                        <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-end">{{ number_format($user->time_spent_minutes) }}</td>
                                        <td class="text-end">{{ $user->avg_grade ? number_format($user->avg_grade, 2) : 'N/A' }}</td>
                                        <td class="text-end">{{ $user->last_accessed ? \Carbon\Carbon::parse($user->last_accessed)->diffForHumans() : 'Never' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#userDataTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        language: { search: "_INPUT_", searchPlaceholder: "Search users..." }
    });
    
    // Initialize chart if we have data
    @if(isset($correlationData) && count($correlationData) > 0)
        const ctx = document.getElementById('timeVsGradeChart').getContext('2d');
        const chartData = {
            datasets: [{
                label: 'Students',
                data: @json($correlationData->map(function($item) {
                    return [
                        'x' => $item->time_spent_minutes,
                        'y' => $item->avg_grade,
                        'name' => $item->firstname . ' ' . $item->lastname
                    ];
                })),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        };
        
        const config = {
            type: 'scatter',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: { display: true, text: 'Time Spent (minutes)' },
                        beginAtZero: true
                    },
                    y: {
                        title: { display: true, text: 'Average Grade' },
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.raw.name + ': ' + context.raw.y.toFixed(2) + ' grade, ' + context.raw.x + ' min';
                            }
                        }
                    }
                }
            }
        };
        
        new Chart(ctx, config);
    @endif
});
</script>
@endpush
