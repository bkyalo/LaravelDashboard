<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsersController extends Controller
{
    /**
     * Display a listing of users with statistics.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get total number of users
            $totalUsers = DB::table('mdl_user')
                ->where('deleted', 0)
                ->count();

            // Get active users (logged in within last 30 days)
            $activeUsers = DB::table('mdl_user')
                ->where('lastaccess', '>', time() - (30 * 24 * 60 * 60))
                ->where('deleted', 0)
                ->count();

            // Get newly registered users (last 30 days)
            $newUsers = DB::table('mdl_user')
                ->where('timecreated', '>', time() - (30 * 24 * 60 * 60))
                ->where('deleted', 0)
                ->count();

            // Get users who never logged in
            $neverLoggedIn = DB::table('mdl_user')
                ->where('lastlogin', 0)
                ->where('deleted', 0)
                ->count();

            // Get users by role
            $usersByRole = DB::table('mdl_role_assignments as ra')
                ->join('mdl_role as r', 'ra.roleid', '=', 'r.id')
                ->select('r.shortname as role', DB::raw('COUNT(DISTINCT ra.userid) as count'))
                ->groupBy('r.shortname')
                ->get();

            // Get current date and previous month dates
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $twoMonthsAgoStart = now()->subMonths(2)->startOfMonth();
            $twoMonthsAgoEnd = now()->subMonths(2)->endOfMonth();

            // Get current month stats
            $currentStats = [
                'total_users' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->where('timecreated', '<=', $currentMonthEnd->timestamp)
                    ->count(),
                'active_users' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->where('lastlogin', '>=', now()->subDays(30)->timestamp)
                    ->count(),
                'new_users' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->whereBetween('timecreated', [$currentMonthStart->timestamp, $currentMonthEnd->timestamp])
                    ->count(),
                'never_logged_in' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->where('lastlogin', 0)
                    ->orWhereNull('lastlogin')
                    ->count(),
                'users_with_no_enrollments' => DB::table('mdl_user as u')
                    ->leftJoin('mdl_user_enrolments as ue', 'u.id', '=', 'ue.userid')
                    ->whereNull('ue.id')
                    ->where('u.deleted', 0)
                    ->count('u.id')
            ];

            // Get last month stats
            $lastMonthStats = [
                'total_users' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->where('timecreated', '<=', $lastMonthEnd->timestamp)
                    ->count(),
                'active_users' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->whereBetween('lastlogin', [
                        now()->subMonth()->subDays(30)->timestamp,
                        $lastMonthEnd->timestamp
                    ])
                    ->count(),
                'new_users' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->whereBetween('timecreated', [$lastMonthStart->timestamp, $lastMonthEnd->timestamp])
                    ->count(),
                'never_logged_in' => DB::table('mdl_user')
                    ->where('deleted', 0)
                    ->where('lastlogin', 0)
                    ->orWhereNull('lastlogin')
                    ->where('timecreated', '<=', $lastMonthEnd->timestamp)
                    ->count(),
                'users_with_no_enrollments' => DB::table('mdl_user as u')
                    ->leftJoin('mdl_user_enrolments as ue', 'u.id', '=', 'ue.userid')
                    ->whereNull('ue.id')
                    ->where('u.deleted', 0)
                    ->where('u.timecreated', '<=', $lastMonthEnd->timestamp)
                    ->count('u.id')
            ];

            // Calculate deltas and percentages
            $stats = [];
            foreach ($currentStats as $key => $currentValue) {
                $lastValue = $lastMonthStats[$key] ?? 0;
                $delta = $currentValue - $lastValue;
                $percentage = $lastValue > 0 ? round(($delta / $lastValue) * 100) : 0;
                $stats[$key] = [
                    'current' => $currentValue,
                    'last' => $lastValue,
                    'delta' => $delta,
                    'percentage' => $percentage,
                    'is_positive' => $delta >= 0,
                    'has_change' => $lastValue > 0
                ];
            }

            // Get user registration by month for the current year
            $registrationStats = DB::table('mdl_user')
                ->select(
                    DB::raw('FROM_UNIXTIME(timecreated, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('deleted', 0)
                ->where('timecreated', '>', strtotime('first day of january this year'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get users grouped by institution (skip empty institutions)
            $usersByInstitution = DB::table('mdl_user')
                ->select(
                    'institution',
                    DB::raw('COUNT(*) as count')
                )
                ->where('deleted', 0)
                ->whereNotNull('institution')
                ->where('institution', '<>', '')
                ->groupBy('institution')
                ->orderBy('count', 'desc')
                ->get();

            // Get users grouped by department (skip empty departments)
            $usersByDepartment = DB::table('mdl_user')
                ->select(
                    'department',
                    DB::raw('COUNT(*) as count')
                )
                ->where('deleted', 0)
                ->whereNotNull('department')
                ->where('department', '<>', '')
                ->groupBy('department')
                ->orderBy('count', 'desc')
                ->get();

            // Get top 10 most enrolled users with their course count
            $topEnrolledUsers = DB::table('mdl_user_enrolments as ue')
                ->join('mdl_user as u', 'ue.userid', '=', 'u.id')
                ->select(
                    'u.id',
                    'u.idnumber',
                    'u.department',
                    'u.firstname',
                    'u.lastname',
                    DB::raw('COUNT(DISTINCT ue.enrolid) as course_count')
                )
                ->where('u.deleted', 0)
                ->groupBy('u.id', 'u.idnumber', 'u.department', 'u.firstname', 'u.lastname')
                ->orderBy('course_count', 'desc')
                ->take(10)
                ->get();

            // Get last 10 logged-in users with their last login time
            $recentLogins = DB::table('mdl_user')
                ->select(
                    'id',
                    'firstname',
                    'lastname',
                    'lastlogin',
                    'idnumber'
                )
                ->where('deleted', 0)
                ->whereNotNull('lastlogin')
                ->where('lastlogin', '>', 0)
                ->orderBy('lastlogin', 'desc')
                ->take(10)
                ->get()
                ->map(function($user) {
                    $user->last_login_ago = $user->lastlogin ? now()->diffForHumans(now()->createFromTimestamp($user->lastlogin), true) . ' ago' : 'Never';
                    return $user;
                });

            return view('users.index', [
                'topEnrolledUsers' => $topEnrolledUsers,
                'recentLogins' => $recentLogins,
                'stats' => $stats,
                'usersByRole' => $usersByRole,
                'registrationStats' => $registrationStats,
                'usersByInstitution' => $usersByInstitution,
                'usersByDepartment' => $usersByDepartment
            ]);

        } catch (\Exception $e) {
            // Log the error and return with a message
            \Log::error('Error fetching user statistics: ' . $e->getMessage());
            return view('users.index')->with('error', 'Unable to load user statistics. Please try again later.');
        }
    }
}
