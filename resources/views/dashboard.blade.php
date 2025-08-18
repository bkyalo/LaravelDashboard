@extends('layouts.app')

@section('title', 'Moodle Dashboard')

@push('styles')
    <!-- Syncfusion Essential JS 2 Styles -->
    <link href="https://cdn.syncfusion.com/ej2/material.css" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary-color: #4FC3F7;
            --secondary-color: #4CAF50;
            --accent-color: #FFA726;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-color: #e0e0e0;
            --bg-light: #f8f9fa;
            --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            --card-hover: 0 4px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }
        
        /* Layout */
        .dashboard {
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
            font-family: 'Quicksand', sans-serif;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .dashboard-header h1 {
            color: var(--text-primary);
            margin: 0;
            font-weight: 600;
        }
        
        .last-updated {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .two-column {
            grid-template-columns: 1fr 1fr;
        }
        
        .full-width-card {
            grid-column: 1 / -1;
            margin-bottom: 20px;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 20px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-hover);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card h3 {
            margin: 0;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .card-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .card-badge.online {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .stat-item {
            flex: 1;
            padding: 10px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1.2;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 3px;
        }
        
        .stat-divider {
            width: 1px;
            height: 40px;
            background-color: var(--border-color);
        }
        
        .stats-details {
            background: var(--bg-light);
            border-radius: 8px;
            padding: 12px 15px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .stat-row:last-child {
            margin-bottom: 0;
        }
        
        /* Active Users */
        .active-users {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .active-count {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .count {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            line-height: 1.2;
        }
        
        .count-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .recent-activity-title {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin: 0 0 10px 0;
        }
        
        .recent-users {
            flex: 1;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .user-row {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .user-row:last-child {
            border-bottom: none;
        }
        
        .user-status {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .user-status.online {
            background-color: var(--secondary-color);
        }
        
        .user-name {
            flex: 1;
            font-size: 0.9rem;
        }
        
        .user-last-seen {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        /* Tables */
        .table-container {
            overflow-x: auto;
            flex: 1;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            table-layout: fixed;
        }
        
        .data-table th {
            text-align: left;
            padding: 12px 10px;
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
        }
        
        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            vertical-align: middle;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover {
            background-color: rgba(79, 195, 247, 0.05);
        }
        
        .index-col {
            width: 40px;
            text-align: center;
            color: var(--text-secondary);
        }
        
        .enroll-col {
            width: 100px;
            text-align: right;
        }
        
        .course-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .enrollment-count {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            min-width: 40px;
            text-align: center;
        }
        
        /* Charts */
        .chart-container {
            height: 300px;
            margin-top: 10px;
        }
        
        .large-chart {
            height: 400px;
            margin-top: 10px;
        }
        
        .chart-legend {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 6px;
        }
        
        /* No Data State */
        .no-data {
            padding: 20px;
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
            background: var(--bg-light);
            border-radius: 8px;
            margin: 10px 0;
        }
        
        /* Responsive */
        @media (max-width: 1400px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .dashboard-grid.two-column {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                flex-direction: column;
                gap: 15px;
            }
            
            .stat-divider {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard {
                padding: 15px 10px;
            }
            
            .card {
                padding: 15px;
            }
            
            .stat-value, .count {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard">
        <div class="dashboard-header">
            <h1>Moodle Analytics Dashboard</h1>
            <div class="last-updated">Last updated: {{ now()->format('M d, Y H:i:s') }}</div>
        </div>
        
        <!-- Top Stats Row -->
        <div class="dashboard-grid">
            <!-- User Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h3>User Statistics</h3>
                    <span class="card-badge">Total</span>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalUsers) }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($totalCourses) }}</div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                </div>
                <div class="stats-details">
                    <div class="stat-row">
                        <span class="stat-label">Active (30 days):</span>
                        <span class="stat-value">{{ number_format($activeUsers30Days) }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Inactive (30 days):</span>
                        <span class="stat-value">{{ number_format($inactiveUsers30Days) }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Never logged in:</span>
                        <span class="stat-value">{{ number_format($neverLoggedInUsers) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Active Users Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Active Users</h3>
                    <span class="card-badge online">Live</span>
                </div>
                <div class="active-users">
                    <div class="active-count">
                        <span class="count">{{ $usersLast30Minutes }}</span>
                        <span class="count-label">Active in last 30 minutes</span>
                    </div>
                    
                    <h4 class="recent-activity-title">Recently Active (Last 5 min)</h4>
                    @if(count($usersLast5Minutes) > 0)
                        <div class="recent-users">
                            @foreach($usersLast5Minutes as $user)
                                <div class="user-row">
                                    <span class="user-status online"></span>
                                    <span class="user-name">{{ $user['name'] }}</span>
                                    <span class="user-last-seen">{{ $user['last_seen'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-data">
                            No active users in the last 5 minutes
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Course Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Course Statistics</h3>
                </div>
                <div id="courseChart" class="chart-container"></div>
            </div>
            
            <!-- User Activity Card -->
            <div class="card">
                <div class="card-header">
                    <h3>User Activity</h3>
                </div>
                <div id="activityChart" class="chart-container"></div>
            </div>
        </div>
        
        <!-- Top Enrolled Courses -->
        <div class="dashboard-grid two-column">
            <div class="card">
                <div class="card-header">
                    <h3>Top 10 Most Enrolled Courses</h3>
                    <span class="card-badge">All Time</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="index-col">#</th>
                                <th>Course Name</th>
                                <th class="enroll-col">Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCourses as $index => $course)
                                <tr>
                                    <td class="index-col">{{ $index + 1 }}</td>
                                    <td class="course-name">{{ $course['name'] }}</td>
                                    <td class="enroll-col">
                                        <span class="enrollment-count">{{ number_format($course['enrollments']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="no-data">
                                        No course enrollment data available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Top PDC Courses -->
            <div class="card">
                <div class="card-header">
                    <h3>Top 10 PDC Courses</h3>
                    <span class="card-badge">All Time</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="index-col">#</th>
                                <th>Course Name</th>
                                <th class="enroll-col">Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pdcCourses as $index => $course)
                                <tr>
                                    <td class="index-col">{{ $index + 1 }}</td>
                                    <td class="course-name">{{ $course['name'] }}</td>
                                    <td class="enroll-col">
                                        <span class="enrollment-count">{{ number_format($course['enrollments']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="no-data">
                                        No PDC course enrollment data available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Monthly Statistics Section -->
        <div class="full-width-card">
            <div class="card">
                <div class="card-header">
                    <h3>Monthly Statistics ({{ now()->year }})</h3>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color" style="background: #4FC3F7;"></span>
                            <span>Active Users</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #FFA726;"></span>
                            <span>New Users</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #66BB6A;"></span>
                            <span>New Enrollments</span>
                        </div>
                    </div>
                </div>
                <div id="monthlyLoginsChart" class="large-chart"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Syncfusion Essential JS 2 Scripts -->
    <script src="https://cdn.syncfusion.com/ej2/dist/ej2.min.js"></script>
    <script>
        // Initialize Syncfusion components when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Course Statistics Chart
            var courseChart = new ej.charts.Chart({
                primaryXAxis: {
                    valueType: 'Category',
                    labelStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    }
                },
                primaryYAxis: {
                    labelStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    }
                },
                series: [{
                    type: 'Column',
                    dataSource: [
                        { x: 'Jan', y: 45 }, { x: 'Feb', y: 60 },
                        { x: 'Mar', y: 75 }, { x: 'Apr', y: 90 },
                        { x: 'May', y: 85 }, { x: 'Jun', y: 95 }
                    ],
                    xName: 'x', yName: 'y', name: 'Enrollments'
                }],
                title: 'Monthly Course Enrollments',
                // Apply Quicksand font to chart elements
                font: {
                    fontFamily: 'Quicksand, sans-serif',
                    fontWeight: '500'
                },
                titleStyle: {
                    fontFamily: 'Quicksand, sans-serif',
                    fontWeight: '600'
                },
                tooltip: {
                    textStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    }
                }
            });
            courseChart.appendTo('#courseChart');

            // User Activity Chart - Last 30 Days
            var activityChart = new ej.charts.AccumulationChart({
                series: [{
                    dataSource: [
                        { 
                            x: 'Active (' + {{ $activeUsers30Days }} + ')', 
                            y: {{ $activeUsers30Days }}, 
                            text: '{{ $totalUsers > 0 ? round(($activeUsers30Days / $totalUsers) * 100) : 0 }}%'
                        },
                        { 
                            x: 'Inactive (' + {{ $inactiveUsers30Days }} + ')', 
                            y: {{ $inactiveUsers30Days }}, 
                            text: '{{ $totalUsers > 0 ? round(($inactiveUsers30Days / $totalUsers) * 100) : 0 }}%'
                        },
                        { 
                            x: 'Never Logged In (' + {{ $neverLoggedInUsers }} + ')', 
                            y: {{ $neverLoggedInUsers }}, 
                            text: '{{ $totalUsers > 0 ? round(($neverLoggedInUsers / $totalUsers) * 100) : 0 }}%'
                        }
                    ],
                    xName: 'x', 
                    yName: 'y',
                    innerRadius: '40%',
                    dataLabel: { 
                        visible: true, 
                        name: 'text', 
                        position: 'Inside',
                        font: {
                            fontWeight: '600',
                            color: '#fff'
                        }
                    },
                    palettes: ['#4FC3F7', '#4DB6AC', '#FF8A65']
                }],
                legendSettings: { 
                    visible: true,
                    textStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    }
                },
                title: 'User Activity (Last 30 Days)',
                // Apply Quicksand font to chart elements
                font: {
                    fontFamily: 'Quicksand, sans-serif',
                    fontWeight: '500'
                },
                tooltip: {
                    enable: true,
                    format: '${point.x}: <b>${point.y} users</b>'
                }
            });
            activityChart.appendTo('#activityChart');

            // Monthly Stats Line Chart
            var monthlyStatsData = @json($monthlyStats);
            var monthlyStatsChart = new ej.charts.Chart({
                primaryXAxis: {
                    valueType: 'Category',
                    labelStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    },
                    majorGridLines: { width: 0 },
                    majorTickLines: { width: 0 },
                    lineStyle: { width: 0 }
                },
                primaryYAxis: {
                    title: 'Number of Users',
                    labelFormat: '{value}',
                    labelStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    },
                    lineStyle: { width: 0 },
                    majorTickLines: { width: 0 }
                },
                series: [
                    {
                        dataSource: monthlyStatsData,
                        xName: 'month',
                        yName: 'active_users',
                        name: 'Active Users',
                        type: 'Line',
                        width: 2,
                        marker: { 
                            visible: true, 
                            width: 8, 
                            height: 8,
                            fill: '#4FC3F7',
                            border: { width: 2, color: '#4FC3F7' }
                        },
                        animation: { enable: true },
                        fill: '#4FC3F7',
                        border: { width: 2, color: '#4FC3F7' }
                    },
                    {
                        dataSource: monthlyStatsData,
                        xName: 'month',
                        yName: 'new_users',
                        name: 'New Users',
                        type: 'Line',
                        width: 2,
                        marker: { 
                            visible: true, 
                            width: 8, 
                            height: 8,
                            fill: '#FFA726',
                            border: { width: 2, color: '#FFA726' }
                        },
                        animation: { enable: true },
                        fill: '#FFA726',
                        border: { width: 2, color: '#FFA726' }
                    },
                    {
                        dataSource: monthlyStatsData,
                        xName: 'month',
                        yName: 'new_enrollments',
                        name: 'New Enrollments',
                        type: 'Line',
                        width: 2,
                        marker: { 
                            visible: true, 
                            width: 8, 
                            height: 8,
                            fill: '#66BB6A',
                            border: { width: 2, color: '#66BB6A' }
                        },
                        animation: { enable: true },
                        fill: '#66BB6A',
                        border: { width: 2, color: '#66BB6A' }
                    }
                ],
                tooltip: {
                    enable: true,
                    format: '${point.x} <b>${point.y} ${point.series.name}</b>',
                    textStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    }
                },
                legend: {
                    visible: true,
                    position: 'Bottom',
                    textStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    }
                },
                // Apply Quicksand font to chart elements
                font: {
                    fontFamily: 'Quicksand, sans-serif',
                    fontWeight: '500'
                },
                titleStyle: {
                    fontFamily: 'Quicksand, sans-serif',
                    fontWeight: '600'
                },
                // Set colors for the series to match the legend
                palettes: ['#4FC3F7', '#FFA726', '#66BB6A'],
                // Add some padding at the bottom for the legend
                margin: { bottom: 40 },
                // Ensure the legend uses the same colors as the lines
                legendSettings: {
                    textStyle: {
                        fontFamily: 'Quicksand, sans-serif',
                        fontWeight: '500'
                    },
                    useSeriesColor: true
                }
            });
            monthlyStatsChart.appendTo('#monthlyLoginsChart');

            // Top Enrolled Courses - Table is now handled by server-side rendering

            // Recent Activities Grid
            var recentActivities = new ej.grids.Grid({
                dataSource: [
                    { id: 1, activity: 'User login', time: '2 mins ago', status: 'Success' },
                    { id: 2, activity: 'Course access', time: '5 mins ago', status: 'Success' },
                    { id: 3, activity: 'Quiz submission', time: '15 mins ago', status: 'Success' },
                    { id: 4, activity: 'Resource download', time: '30 mins ago', status: 'Success' },
                    { id: 5, activity: 'Discussion post', time: '1 hour ago', status: 'Success' }
                ],
                columns: [
                    { field: 'activity', headerText: 'Activity', width: 150 },
                    { field: 'time', headerText: 'Time', width: 100 },
                    { field: 'status', headerText: 'Status', width: 80 }
                ],
                // Apply Quicksand font to grid
                font: {
                    fontFamily: 'Quicksand, sans-serif',
                    fontWeight: '500'
                }
            });
            recentActivities.appendTo('#recentActivities');
        });
    </script>
@endpush
