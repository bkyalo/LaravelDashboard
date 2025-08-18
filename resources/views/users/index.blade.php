@extends('layouts.app')

@section('title', 'Users Management')

@push('styles')
    <style>
        .stat-card {
            border: 1px solid #e9ecef;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            background: white !important;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2c3e50);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
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
        
        .stat-number::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2c3e50);
            border-radius: 3px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover .stat-number::after {
            transform: scaleX(1);
        }
        
        .animate-number {
            transition: all 1s ease-out;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            font-size: 2rem;
            color: #037b90;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
            color: #ff7f50;
        }
        
        .data-container {
            background: #fff;
            border-radius: 8px;
            padding: 1.25rem;
            margin-top: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table-responsive {
            margin-top: 1rem;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(3, 123, 144, 0.05);
        }
        
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 1rem;
            }
            .data-container {
                padding: 1rem;
            }
        }
    </style>
@endpush

@section('content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-dark">Users Management</h1>
        <div class="text-muted">Last updated: {{ now()->format('M d, Y H:i:s') }}</div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Users Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-1">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-number">{{ number_format($stats['total_users']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['total_users']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['total_users']['has_change'])
                        <i class="bi {{ $stats['total_users']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['total_users']['delta']) }} ({{ abs($stats['total_users']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <!-- Active Users Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-2">
                <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
                <div class="stat-number">{{ number_format($stats['active_users']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['active_users']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['active_users']['has_change'])
                        <i class="bi {{ $stats['active_users']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['active_users']['delta']) }} ({{ abs($stats['active_users']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Active (30d)</div>
            </div>
        </div>

        <!-- New Users Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-3">
                <div class="stat-icon"><i class="bi bi-person-plus-fill"></i></div>
                <div class="stat-number">{{ number_format($stats['new_users']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ $stats['new_users']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['new_users']['has_change'])
                        <i class="bi {{ $stats['new_users']['is_positive'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ abs($stats['new_users']['delta']) }} ({{ abs($stats['new_users']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">New (30d)</div>
            </div>
        </div>

        <!-- Never Logged In Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-4">
                <div class="stat-icon"><i class="bi bi-person-dash-fill"></i></div>
                <div class="stat-number">{{ number_format($stats['never_logged_in']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ !$stats['never_logged_in']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['never_logged_in']['has_change'])
                        <i class="bi {{ !$stats['never_logged_in']['is_positive'] ? 'bi-arrow-down-right' : 'bi-arrow-up-right' }}"></i>
                        {{ abs($stats['never_logged_in']['delta']) }} ({{ abs($stats['never_logged_in']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">Never Logged In</div>
            </div>
        </div>

        <!-- Users with 0 Enrollments Card -->
        <div class="col-md-2 col-sm-6">
            <div class="stat-card stat-card-5">
                <div class="stat-icon"><i class="bi bi-person-x-fill"></i></div>
                <div class="stat-number">{{ number_format($stats['users_with_no_enrollments']['current'] ?? 0) }}</div>
                <div class="stat-delta {{ !$stats['users_with_no_enrollments']['is_positive'] ? 'text-success' : 'text-danger' }}">
                    @if($stats['users_with_no_enrollments']['has_change'])
                        <i class="bi {{ !$stats['users_with_no_enrollments']['is_positive'] ? 'bi-arrow-down-right' : 'bi-arrow-up-right' }}"></i>
                        {{ abs($stats['users_with_no_enrollments']['delta']) }} ({{ abs($stats['users_with_no_enrollments']['percentage']) }}%)
                    @else
                        <i class="bi bi-dash"></i> No change
                    @endif
                </div>
                <div class="stat-label">No Enrollments</div>
            </div>
        </div>
    </div>
    <!-- Users by Role and Institution -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="data-container">
                <h5 class="section-title">Users by Role</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th class="text-end">Users</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total = $usersByRole->sum('count');
                            @endphp
                            @forelse($usersByRole as $role)
                                @php
                                    $percentage = $total > 0 ? round(($role->count / $total) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $role->role ?? 'Unknown' }}</td>
                                    <td class="text-end">{{ number_format($role->count) }}</td>
                                    <td class="text-end">{{ $percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No role data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="data-container">
                <h5 class="section-title">Users by Institution</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Institution</th>
                                <th class="text-end">Users</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total = $usersByInstitution->sum('count');
                            @endphp
                            @forelse($usersByInstitution as $institution)
                                @php
                                    $percentage = $total > 0 ? round(($institution->count / $total) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $institution->institution ?: 'Not Specified' }}</td>
                                    <td class="text-end">{{ number_format($institution->count) }}</td>
                                    <td class="text-end">{{ $percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No institution data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Trend and Department Distribution -->
    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <div class="data-container">
                <h5 class="section-title">User Registration Trend ({{ date('Y') }})</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">New Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $months = [
                                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                                ];
                                $registrationData = collect($registrationStats ?? []);
                            @endphp
                            @foreach($months as $index => $month)
                                @php
                                    $count = $registrationData->firstWhere('month', $month)->count ?? 0;
                                @endphp
                                <tr>
                                    <td>{{ $month }}</td>
                                    <td class="text-end">{{ number_format($count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="data-container">
                <h5 class="section-title">Users by Department (Top 10)</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th class="text-end">Users</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topDepartments = collect($usersByDepartment ?? [])
                                    ->sortByDesc('count')
                                    ->take(10);
                                $totalDept = $topDepartments->sum('count');
                            @endphp
                            @forelse($topDepartments as $dept)
                                @php
                                    $percentage = $totalDept > 0 ? round(($dept->count / $totalDept) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $dept->department ?: 'Not Specified' }}</td>
                                    <td class="text-end">{{ number_format($dept->count) }}</td>
                                    <td class="text-end">{{ $percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No department data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Enrolled Users -->
    <div class="data-container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">Top 10 Most Enrolled Users</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                <i class="bi bi-trophy-fill me-1"></i> Enrollment Leaders
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle">
                <thead>
                    <tr class="bg-light">
                        <th class="rounded-start">#</th>
                        <th>Name</th>
                        <th>ID Number</th>
                        <th>Department</th>
                        <th class="text-end rounded-end">Courses</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topEnrolledUsers as $index => $user)
                        <tr class="position-relative hover-shadow">
                            <td class="ps-3">
                                @if($index < 3)
                                    <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                                @else
                                    <span class="text-muted fw-medium">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $user->firstname }} {{ $user->lastname }}</td>
                            <td>
                                <span class="text-muted">{{ $user->idnumber ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1"></i> {{ $user->department ?: 'Not Specified' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="progress flex-grow-1 me-3" style="height: 6px;">
                                        @php
                                            $maxCourses = $topEnrolledUsers->first()->course_count;
                                            $width = $maxCourses > 0 ? ($user->course_count / $maxCourses) * 100 : 0;
                                            $bgClass = $index < 3 ? 'bg-warning' : 'bg-primary';
                                        @endphp
                                        <div class="progress-bar {{ $bgClass }}" role="progressbar" 
                                             style="width: {{ $width }}%" 
                                             aria-valuenow="{{ $user->course_count }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $maxCourses }}">
                                        </div>
                                    </div>
                                    <span class="badge {{ $index < 3 ? 'bg-warning text-dark' : 'bg-primary' }} px-2 py-1">
                                        {{ $user->course_count }} <i class="bi bi-journal-text ms-1"></i>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">No enrollment data available</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .rank-1 { background: #FFD700; color: #000; }
        .rank-2 { background: #C0C0C0; color: #000; }
        .rank-3 { background: #CD7F32; color: #fff; }
        .hover-shadow {
            transition: all 0.2s ease;
            border-radius: 8px;
        }
        .hover-shadow:hover {
            background: #fff !important;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }
        .table thead th {
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            padding: 0.75rem 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f5f5f5;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .progress {
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>

    <!-- Recent Logins -->
    <div class="data-container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">Recent User Activity</h5>
            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                <i class="bi bi-clock-history me-1"></i> Last 10 Logins
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle">
                <thead>
                    <tr class="bg-light">
                        <th class="rounded-start">User</th>
                        <th>ID Number</th>
                        <th>Last Login</th>
                        <th class="text-end rounded-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogins as $user)
                        @php
                            $loginTime = $user->lastlogin ? \Carbon\Carbon::createFromTimestamp($user->lastlogin) : null;
                            $isRecent = $loginTime && $loginTime->diffInHours(now()) < 24;
                            $isVeryRecent = $loginTime && $loginTime->diffInMinutes(now()) < 60;
                        @endphp
                        <tr class="position-relative hover-shadow">
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="avatar-status">
                                            <div class="avatar-initials">
                                                {{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}
                                            </div>
                                            @if($isVeryRecent)
                                                <span class="status-dot bg-success"></span>
                                            @elseif($isRecent)
                                                <span class="status-dot bg-warning"></span>
                                            @else
                                                <span class="status-dot bg-secondary"></span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $user->firstname }} {{ $user->lastname }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $user->idnumber ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($loginTime)
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock-history me-2 text-muted"></i>
                                        <div>
                                            <div class="fw-medium">{{ $user->last_login_ago }}</div>
                                            <small class="text-muted">{{ $loginTime->format('M d, Y h:i A') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Never logged in</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if($isVeryRecent)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active Now
                                    </span>
                                @elseif($isRecent)
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active Today
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">No recent login data available</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- User List Placeholder -->
    <div class="data-container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">User List</h5>
            <div class="ms-auto">
                <input type="text" class="form-control" placeholder="Search users..." style="width: 200px;">
            </div>
        </div>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            User list with advanced filtering and actions will be implemented in the next phase.
        </div>
    </div>
    
    <style>
        /* Add to existing styles */
        .avatar-status {
            position: relative;
            width: 36px;
            height: 36px;
        }
        .avatar-initials {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #037b90, #00b4d8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .status-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid white;
        }
        .bg-success {
            background-color: #198754 !important;
        }
        .bg-warning {
            background-color: #ffc107 !important;
        }
        .bg-secondary {
            background-color: #6c757d !important;
        }
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize any interactive elements here if needed
        console.log('Users page loaded successfully');
        
        // Add any additional JavaScript functionality here
        // For example, search functionality can be added later
    });
</script>
@endpush