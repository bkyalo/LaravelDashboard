<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Moodle Dashboard')</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dashboard-16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('dashboard-32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('dashboard-96.png') }}">
    <link rel="shortcut icon" href="{{ asset('dashboard-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('dashboard-96.png') }}">

    <!-- Google Fonts - Quicksand -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #037b90;  /* Primary color */
            --sidebar-color: #ecf0f1;
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --sidebar-active: #ff7f50;  /* Secondary color for active state */
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Quicksand', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: var(--sidebar-color);
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .nav-link {
            color: var(--sidebar-color);
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 0.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--sidebar-hover);
            color: white;
        }
        
        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all var(--transition-speed) ease;
            padding: 1.5rem;
        }
        
        /* Toggle button for mobile */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--sidebar-active);
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(calc(-1 * var(--sidebar-width)));
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar-toggle {
                display: flex;
            }
            
            .main-content.shifted {
                margin-left: var(--sidebar-width);
            }
        }
        
        /* Active state for current page */
        .nav-link.active {
            background: var(--sidebar-active);
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Moodle Admin</h3>
        </div>
        <nav class="sidebar-menu">
            <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.index') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark-fill me-2"></i>
                <span>Courses</span>
            </a>
            <a href="{{ route('courses.time-vs-grades') }}" class="nav-link {{ request()->routeIs('courses.time-vs-grades') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow me-2"></i>
                <span>Time vs. Grades</span>
            </a>
            <a href="{{ route('pdc-courses.index') }}" class="nav-link {{ request()->routeIs('pdc-courses.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark"></i>
                <span>PDC Courses</span>
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-mortarboard"></i>
                <span>Students</span>
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-person-video3"></i>
                <span>Teachers</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        @yield('content')
    </main>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.getElementById('mainContent');
            
            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('shifted');
            }
            
            // Add click event to toggle button
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInside = sidebar.contains(event.target) || 
                                    (sidebarToggle && sidebarToggle.contains(event.target));
                
                if (!isClickInside && window.innerWidth <= 992) {
                    if (sidebar.classList.contains('active')) {
                        toggleSidebar();
                    }
                }
            });
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('shifted');
                }
            }
            
            window.addEventListener('resize', handleResize);
            
            // Highlight active menu item
            const currentLocation = location.href;
            const menuItems = document.querySelectorAll('.nav-link');
            
            menuItems.forEach(item => {
                if (item.href === currentLocation) {
                    item.classList.add('active');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
