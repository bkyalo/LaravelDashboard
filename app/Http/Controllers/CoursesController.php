<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoursesController extends Controller
{
    public function index()
    {
        try {
            // Get current and previous month dates
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();

            // Get current month stats
            $currentStats = [
                'total_courses' => $this->getTotalCourses($currentMonthEnd),
                'active_courses' => $this->getActiveCourses($currentMonthEnd),
                'new_courses' => $this->getNewCourses($currentMonthStart, $currentMonthEnd),
                'pdc_courses' => $this->getPdcCourses($currentMonthEnd),
                'courses_without_enrollments' => $this->getCoursesWithoutEnrollments($currentMonthEnd)
            ];

            // Get last month stats
            $lastMonthStats = [
                'total_courses' => $this->getTotalCourses($lastMonthEnd),
                'active_courses' => $this->getActiveCourses($lastMonthEnd),
                'new_courses' => $this->getNewCourses($lastMonthStart, $lastMonthEnd),
                'pdc_courses' => $this->getPdcCourses($lastMonthEnd),
                'courses_without_enrollments' => $this->getCoursesWithoutEnrollments($lastMonthEnd)
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

            // Get top 10 most enrolled courses
            $topEnrolledCourses = DB::table('mdl_course as c')
                ->select(
                    'c.id',
                    'c.fullname as course_name',
                    'c.shortname',
                    'c.visible',
                    DB::raw('COUNT(ue.id) as enrollment_count')
                )
                ->leftJoin('mdl_enrol as e', 'c.id', '=', 'e.courseid')
                ->leftJoin('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
                ->where('c.id', '!=', 1) // Skip the front page
                ->where('c.visible', 1) // Only visible courses
                ->groupBy('c.id', 'c.fullname', 'c.shortname', 'c.visible')
                ->orderByDesc('enrollment_count')
                ->take(10)
                ->get();

            // Get recently modified courses
            $recentlyModified = DB::table('mdl_course')
                ->select('id', 'fullname', 'shortname', 'timemodified')
                ->where('id', '!=', 1) // Skip the front page
                ->orderBy('timemodified', 'desc')
                ->take(10)
                ->get()
                ->map(function ($course) {
                    $course->last_modified = $course->timemodified 
                        ? Carbon::createFromTimestamp($course->timemodified)->diffForHumans()
                        : 'Never';
                    return $course;
                });

            // Get course creation stats by month for the current year
            $creationStats = DB::table('mdl_course')
                ->select(
                    DB::raw('FROM_UNIXTIME(timecreated, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('id', '!=', 1) // Skip the front page
                ->whereRaw('YEAR(FROM_UNIXTIME(timecreated)) = ?', [now()->year])
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get courses per category
            $coursesPerCategory = $this->getCoursesPerCategory();
            
            return view('courses.index', [
                'stats' => $stats,
                'topEnrolledCourses' => $topEnrolledCourses,
                'recentlyModified' => $recentlyModified,
                'creationStats' => $creationStats,
                'coursesPerCategory' => $coursesPerCategory
            ]);

        } catch (\Exception $e) {
            // Log the full error with stack trace
            $errorMessage = 'Error in CoursesController: ' . $e->getMessage() . '\n' . $e->getTraceAsString();
            \Log::error($errorMessage);
            
            // Show the error directly on the screen
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            
            return view('errors.debug', [
                'error' => $errorDetails,
                'title' => 'Error Loading Courses',
                'description' => 'An error occurred while loading the courses data.'
            ]);
        }
    }

    private function getTotalCourses($endDate)
    {
        return DB::table('mdl_course')
            ->where('id', '!=', 1) // Skip the front page
            ->where('timecreated', '<=', $endDate->timestamp)
            ->count();
    }

    private function getActiveCourses($endDate)
    {
        // Active courses are those with recent activity (last 30 days)
        $thirtyDaysAgo = now()->subDays(30)->timestamp;
        
        return DB::table('mdl_course as c')
            ->join('mdl_logstore_standard_log as l', 'c.id', '=', 'l.courseid')
            ->where('c.id', '!=', 1) // Skip the front page
            ->where('l.timecreated', '>=', $thirtyDaysAgo)
            ->where('l.timecreated', '<=', $endDate->timestamp)
            ->distinct('c.id')
            ->count('c.id');
    }

    private function getNewCourses($startDate, $endDate)
    {
        return DB::table('mdl_course')
            ->where('id', '!=', 1) // Skip the front page
            ->whereBetween('timecreated', [$startDate->timestamp, $endDate->timestamp])
            ->count();
    }

    private function getPdcCourses($endDate)
    {
        return DB::table('mdl_course')
            ->where('id', '!=', 1) // Skip the front page
            ->where('shortname', 'LIKE', '%PDC%')
            ->where('timecreated', '<=', $endDate->timestamp)
            ->count();
    }

    private function getCoursesWithoutEnrollments($endDate)
    {
        // First, get all course IDs that have enrollments
        $coursesWithEnrollments = DB::table('mdl_enrol as e')
            ->join('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
            ->select('e.courseid')
            ->distinct()
            ->pluck('courseid');

        // Then count courses that don't have any enrollments
        return DB::table('mdl_course as c')
            ->where('c.id', '!=', 1) // Skip the front page
            ->where('c.timecreated', '<=', $endDate->timestamp)
            ->whereNotIn('c.id', $coursesWithEnrollments)
            ->count();
    }
    
    private function getCoursesPerCategory()
    {
        return DB::table('mdl_course_categories as cc')
            ->leftJoin('mdl_course as c', 'cc.id', '=', 'c.category')
            ->select(
                'cc.id',
                'cc.name as category_name',
                DB::raw('COUNT(c.id) as course_count'),
                DB::raw('SUM(CASE WHEN c.visible = 1 THEN 1 ELSE 0 END) as visible_courses'),
                DB::raw('MAX(cc.depth) as depth')
            )
            ->where('cc.id', '!=', 1) // Skip the front page category
            ->groupBy('cc.id', 'cc.name')
            ->orderBy('cc.path')
            ->get()
            ->map(function($category) {
                // Calculate indentation based on depth
                $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $category->depth - 1);
                $category->display_name = $indent . $category->category_name;
                return $category;
            });
    }
    
    /**
     * Show time spent vs grade correlations
     */
    public function timeVsGrades($courseId = null)
    {
        try {
            // Get list of courses for the dropdown
            $courses = DB::table('mdl_course')
                ->where('id', '!=', 1) // Skip site course
                ->orderBy('fullname')
                ->get(['id', 'fullname']);
                
            // If no course is selected, show the first one
            if (!$courseId && $courses->isNotEmpty()) {
                $courseId = $courses->first()->id;
            }
            
            $data = [
                'courses' => $courses,
                'selectedCourseId' => $courseId,
                'correlationData' => collect([]),
                'userData' => collect([])
            ];
            
            if ($courseId) {
                // Get time spent and grades data for the selected course
                $data['correlationData'] = $this->getTimeVsGradeData($courseId);
                
                // Get user data for the table
                $data['userData'] = $this->getUserTimeGradeData($courseId);
            }
            
            return view('courses.time-vs-grades', $data);
            
        } catch (\Exception $e) {
            return view('errors.debug', [
                'title' => 'Error',
                'description' => 'An error occurred while loading the Time vs. Grades dashboard.',
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }
    
    /**
     * Get time spent vs grade data for a course
     */
    /**
     * Get time spent vs grade data for a course with optimized queries
     * 
     * @param int $courseId
     * @return \Illuminate\Support\Collection
     */
    private function getTimeVsGradeData($courseId)
    {
        // Define the time window (last 6 months)
        $timeWindow = now()->subMonths(6)->timestamp;
        
        // Get all enrolled users with their grades in a single query
        $usersWithGrades = DB::table('mdl_user_enrolments AS ue')
            ->join('mdl_enrol AS e', 'ue.enrolid', '=', 'e.id')
            ->join('mdl_user AS u', 'ue.userid', '=', 'u.id')
            ->leftJoin('mdl_grade_grades AS gg', function($join) use ($courseId) {
                $join->on('gg.userid', '=', 'u.id')
                    ->whereExists(function($query) use ($courseId) {
                        $query->select(DB::raw(1))
                            ->from('mdl_grade_items AS gi')
                            ->whereColumn('gi.id', 'gg.itemid')
                            ->where('gi.courseid', $courseId)
                            ->where('gi.itemtype', 'course');
                    });
            })
            ->where('e.courseid', $courseId)
            ->where('u.deleted', 0)
            ->where('u.suspended', 0)
            ->select(
                'u.id',
                'u.firstname',
                'u.lastname',
                'gg.finalgrade as grade',
                DB::raw('(SELECT COUNT(*) FROM mdl_logstore_standard_log l 
                         WHERE l.userid = u.id 
                         AND l.courseid = e.courseid 
                         AND l.timecreated > ' . $timeWindow . ' 
                         AND l.action IN ("viewed", "submitted", "started")) as activity_count')
            )
            ->get();

        // Process the results
        return $usersWithGrades->map(function($user) use ($timeWindow) {
            // Calculate time spent based on activity count (5 minutes per activity)
            $timeSpent = $user->activity_count * 5;
            
            // Skip users with no activity or no grades
            if ($timeSpent <= 0 || is_null($user->grade)) {
                return null;
            }
            
            return (object)[
                'userid' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'time_spent_minutes' => $timeSpent,
                'avg_grade' => (float)$user->grade
            ];
        })->filter()->sortBy('time_spent_minutes')->values();
    }
    
    /**
     * Get detailed user data for the table
     */
    /**
     * Get detailed user time and grade data for a course with optimized queries
     * 
     * @param int $courseId
     * @return \Illuminate\Support\Collection
     */
    private function getUserTimeGradeData($courseId)
    {
        // Define the time window (last 6 months)
        $timeWindow = now()->subMonths(6)->timestamp;
        
        // Get all enrolled users with their activity and grades in optimized queries
        $userActivity = DB::table('mdl_user_enrolments AS ue')
            ->join('mdl_enrol AS e', 'ue.enrolid', '=', 'e.id')
            ->join('mdl_user AS u', 'ue.userid', '=', 'u.id')
            ->leftJoin('mdl_grade_grades AS gg', function($join) use ($courseId) {
                $join->on('gg.userid', '=', 'u.id')
                    ->whereExists(function($query) use ($courseId) {
                        $query->select(DB::raw(1))
                            ->from('mdl_grade_items AS gi')
                            ->whereColumn('gi.id', 'gg.itemid')
                            ->where('gi.courseid', $courseId)
                            ->where('gi.itemtype', 'course');
                    });
            })
            ->leftJoin(DB::raw('(SELECT userid, MAX(timecreated) as last_access 
                               FROM mdl_logstore_standard_log 
                               WHERE courseid = ' . $courseId . ' 
                               AND action IN ("viewed", "submitted", "started")
                               GROUP BY userid) as last_activity'), 
                'last_activity.userid', '=', 'u.id')
            ->where('e.courseid', $courseId)
            ->where('u.deleted', 0)
            ->where('u.suspended', 0)
            ->select(
                'u.id',
                'u.firstname',
                'u.lastname',
                'u.email',
                'gg.finalgrade as grade',
                'last_activity.last_access',
                DB::raw('(SELECT COUNT(*) FROM mdl_logstore_standard_log l 
                         WHERE l.userid = u.id 
                         AND l.courseid = e.courseid 
                         AND l.timecreated > ' . $timeWindow . ' 
                         AND l.action IN ("viewed", "submitted", "started")) as activity_count')
            )
            ->orderBy('u.lastname')
            ->orderBy('u.firstname')
            ->get();

        // Process the results
        return $userActivity->map(function($user) {
            // Calculate time spent based on activity count (5 minutes per activity)
            $timeSpent = $user->activity_count * 5;
            
            return (object)[
                'userid' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'time_spent_minutes' => $timeSpent,
                'avg_grade' => $user->grade ? (float)$user->grade : null,
                'last_accessed' => $user->last_access ? date('Y-m-d H:i:s', $user->last_access) : null
            ];
        });
    }
}
