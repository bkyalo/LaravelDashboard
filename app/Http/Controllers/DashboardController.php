<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get total number of non-deleted users
            $totalUsers = DB::table('mdl_user')
                ->where('deleted', 0)
                ->count();
                
            // Get total number of non-deleted courses
            $totalCourses = DB::table('mdl_course')
                ->where('id', '>', 1)
                ->count();
                
            // Get active users in last 30 days (users who logged in within last 30 days)
            $activeUsers30Days = DB::table('mdl_user')
                ->where('deleted', 0)
                ->where('lastaccess', '>=', now()->subDays(30)->timestamp)
                ->count();
                
            // Get inactive users (no login in last 30 days but have logged in before)
            $inactiveUsers30Days = DB::table('mdl_user')
                ->where('deleted', 0)
                ->where('lastaccess', '>', 0) // Has logged in at least once
                ->where('lastaccess', '<', now()->subDays(30)->timestamp)
                ->count();
                
            // Get users who have never logged in
            $neverLoggedInUsers = DB::table('mdl_user')
                ->where('deleted', 0)
                ->where('firstaccess', 0) // Never logged in
                ->count();
                
            // Get top 10 most enrolled courses (excluding PDC courses)
            $topCourses = DB::table('mdl_course as c')
                ->select(
                    'c.id',
                    'c.fullname as course_name',
                    'c.shortname',
                    DB::raw('COUNT(ue.id) as enrollment_count')
                )
                ->leftJoin('mdl_enrol as e', 'e.courseid', '=', 'c.id')
                ->leftJoin('mdl_user_enrolments as ue', 'ue.enrolid', '=', 'e.id')
                ->where('c.id', '>', 1) // Exclude front page
                ->where('c.visible', 1) // Only visible courses
                ->where('c.shortname', 'NOT LIKE', '%PDC%') // Exclude PDC courses
                ->groupBy('c.id', 'c.fullname', 'c.shortname')
                ->orderByDesc('enrollment_count')
                ->limit(10)
                ->get()
                ->map(function($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->course_name,
                        'enrollments' => $course->enrollment_count
                    ];
                });

            // Get top 10 most enrolled PDC courses
            $pdcCourses = DB::table('mdl_course as c')
                ->select(
                    'c.id',
                    'c.fullname as course_name',
                    'c.shortname',
                    DB::raw('COUNT(ue.id) as enrollment_count')
                )
                ->leftJoin('mdl_enrol as e', 'e.courseid', '=', 'c.id')
                ->leftJoin('mdl_user_enrolments as ue', 'ue.enrolid', '=', 'e.id')
                ->where('c.shortname', 'LIKE', '%PDC%')
                ->where('c.visible', 1)
                ->groupBy('c.id', 'c.fullname', 'c.shortname')
                ->orderByDesc('enrollment_count')
                ->limit(10)
                ->get()
                ->map(function($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->course_name,
                        'shortname' => $course->shortname,
                        'enrollments' => $course->enrollment_count
                    ];
                });

            // Get users logged in the last 30 minutes
            $usersLast30Minutes = DB::table('mdl_user')
                ->where('lastaccess', '>=', now()->subMinutes(30)->timestamp)
                ->where('deleted', 0)
                ->count();

            // Get users logged in the last 5 minutes with details
            $usersLast5Minutes = DB::table('mdl_user')
                ->select('id', 'firstname', 'lastname', 'lastaccess')
                ->where('lastaccess', '>=', now()->subMinutes(5)->timestamp)
                ->where('deleted', 0)
                ->orderByDesc('lastaccess')
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'last_seen' => 'Just now' // You can format this better with Carbon if needed
                    ];
                });

        } catch (\Exception $e) {
            // Fallback in case of any database errors
            $totalUsers = 0;
            $activeUsersCount = 0;
            $totalCourses = 0;
            $usersLast30Minutes = [];
            $usersLast5Minutes = [];
            $monthlyLogins = [];
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $error = $e->getMessage();
        }

        // Get monthly statistics for current year up to current month
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        $monthlyStats = collect(range(1, $currentMonth))->map(function ($month) use ($currentYear, $currentMonth) {
            $startOfMonth = now()->setYear($currentYear)->setMonth($month)->startOfMonth()->timestamp;
            $endOfMonth = ($month == $currentMonth) 
                ? now()->timestamp  // For current month, go up to now
                : now()->setYear($currentYear)->setMonth($month)->endOfMonth()->timestamp;
            
            // Active users (logged in this month)
            $activeUsers = DB::table('mdl_user')
                ->where('deleted', 0)
                ->whereBetween('lastaccess', [$startOfMonth, $endOfMonth])
                ->distinct()
                ->count('id');
                
            // New users (created this month)
            $newUsers = DB::table('mdl_user')
                ->where('deleted', 0)
                ->whereBetween('timecreated', [$startOfMonth, $endOfMonth])
                ->count();
                
            // New enrollments (this month)
            $newEnrollments = DB::table('mdl_user_enrolments')
                ->whereBetween('timecreated', [$startOfMonth, $endOfMonth])
                ->count();
            
            return [
                'month' => now()->setMonth($month)->format('M'),
                'active_users' => $activeUsers,
                'new_users' => $newUsers,
                'new_enrollments' => $newEnrollments
            ];
        })->values()->toArray();

        

        return view('dashboard', [
            'totalUsers' => $totalUsers,
            'totalCourses' => $totalCourses,
            'activeUsers30Days' => $activeUsers30Days ?? 0,
            'inactiveUsers30Days' => $inactiveUsers30Days ?? 0,
            'neverLoggedInUsers' => $neverLoggedInUsers ?? 0,
            'topCourses' => $topCourses ?? [],
            'pdcCourses' => $pdcCourses ?? [],
            'usersLast30Minutes' => $usersLast30Minutes,
            'usersLast5Minutes' => $usersLast5Minutes,
            'monthlyStats' => $monthlyStats,
            'error' => $error ?? null
        ]);
    }
}
