@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@syncfusion/ej2@20.4.38/material.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    .chart-container { 
        height: 500px; 
        margin-bottom: 2rem; 
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.04);
    }
    .table-card { 
        background: #fff; 
        border-radius: 12px; 
        box-shadow: 0 2px 20px rgba(0,0,0,0.04); 
        margin-bottom: 2rem; 
    }
    .course-selector { 
        max-width: 400px; 
        margin-left: auto; 
    }
    .section-title {
        font-family: 'Quicksand', sans-serif;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
    }
    #timeVsGradeChart {
        width: 100%;
        height: 100%;
        min-height: 400px;
    }
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
                            <h5 class="section-title">Time Spent vs. Grade Distribution</h5>
                            <div id="timeVsGradeChart"></div>
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@syncfusion/ej2@20.4.38/dist/ej2.min.js"></script>
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
        const timeVsGradeData = @json($correlationData->map(function($item) {
            return [
                'time_spent_minutes' => $item->time_spent_minutes,
                'avg_grade' => $item->avg_grade,
                'name' => $item->firstname . ' ' . $item->lastname
            ];
        }));

        if (timeVsGradeData && timeVsGradeData.length > 0 && document.getElementById('timeVsGradeChart')) {
            try {
                // Load necessary Syncfusion modules
                ej.base.enableRipple(true);
                ej.charts.Chart.Inject(ej.charts.ScatterSeries, ej.charts.Legend, ej.charts.Tooltip);
                
                // Initialize chart
                const chart = new ej.charts.Chart({
                    title: 'Time Spent vs. Grade Distribution',
                    titleStyle: { 
                        fontFamily: 'Quicksand, sans-serif', 
                        fontWeight: '600', 
                        size: '16px',
                        color: '#2c3e50'
                    },
                    primaryXAxis: {
                        title: 'Time Spent (Minutes)',
                        valueType: 'Double',
                        labelStyle: { 
                            fontFamily: 'Quicksand, sans-serif', 
                            size: '12px',
                            color: '#7f8c8d'
                        },
                        titleStyle: {
                            fontFamily: 'Quicksand, sans-serif',
                            fontWeight: '500'
                        },
                        minimum: 0,
                        edgeLabelPlacement: 'Shift'
                    },
                    primaryYAxis: {
                        title: 'Average Grade',
                        labelFormat: '{value}',
                        minimum: 0,
                        maximum: 100,
                        interval: 20,
                        labelStyle: { 
                            fontFamily: 'Quicksand, sans-serif', 
                            size: '12px',
                            color: '#7f8c8d'
                        },
                        titleStyle: {
                            fontFamily: 'Quicksand, sans-serif',
                            fontWeight: '500'
                        }
                    },
                    series: [{
                        dataSource: timeVsGradeData,
                        xName: 'time_spent_minutes',
                        yName: 'avg_grade',
                        name: 'Students',
                        type: 'Scatter',
                        marker: { 
                            width: 10, 
                            height: 10, 
                            fill: '#037b90',
                            border: { width: 1, color: '#fff' }
                        },
                        tooltipMappingName: 'name',
                        tooltip: {
                            enable: true,
                            format: '${point.x} minutes, Grade: ${point.y}',
                            header: '<b>${point.tooltip}</b><br/>',
                            textStyle: { fontFamily: 'Quicksand, sans-serif' }
                        }
                    }],
                    legendSettings: { 
                        visible: true,
                        position: 'Bottom',
                        textStyle: { fontFamily: 'Quicksand, sans-serif' }
                    },
                    tooltip: {
                        enable: true,
                        format: '${point.x} minutes<br/>Grade: ${point.y}',
                        header: '<b>${point.tooltip}</b><br/>',
                        textStyle: { fontFamily: 'Quicksand, sans-serif' }
                    },
                    theme: 'Material',
                    background: 'transparent',
                    load: function(args) {
                        // Add custom styling when chart loads
                        const selectedTheme = location.hash.split('/')[1];
                        selectedTheme ? args.chart.theme = (selectedTheme.charAt(0).toUpperCase() + 
                            selectedTheme.slice(1)).replace(/-dark/i, 'Dark').replace(/contrast/i, 'HighContrast') :
                            (args.chart.theme = 'Material');
                    }
                });
                
                // Render the chart
                chart.appendTo('#timeVsGradeChart');
                
                // Handle window resize
                window.addEventListener('resize', function() {
                    chart.refresh();
                });
                
            } catch (e) {
                console.error('Error initializing chart:', e);
                document.getElementById('timeVsGradeChart').innerHTML =
                    '<div class="alert alert-danger">Error loading Time vs. Grade chart</div>';
            }
        } else {
            document.getElementById('timeVsGradeChart').innerHTML =
                '<div class="alert alert-warning">No data available for Time vs. Grade analysis</div>';
        }
    @endif
});
</script>
@endpush
