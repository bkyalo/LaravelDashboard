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
    private function getTimeVsGradeData($courseId)
    {
        // First, get all enrolled users for this course
        $enrolledUsers = DB::table('mdl_user_enrolments AS ue')
            ->join('mdl_enrol AS e', 'ue.enrolid', '=', 'e.id')
            ->join('mdl_user AS u', 'ue.userid', '=', 'u.id')
            ->where('e.courseid', $courseId)
            ->select('u.id', 'u.firstname', 'u.lastname')
            ->get();
            
        $result = [];
        
        foreach ($enrolledUsers as $user) {
            // Get time spent (in minutes) - limit to last 90 days for performance
            $timeSpent = DB::table('mdl_logstore_standard_log')
                ->where('userid', $user->id)
                ->where('courseid', $courseId)
                ->where('action', 'viewed')
                ->where('timecreated', '>', time() - (90 * 24 * 60 * 60)) // Last 90 days
                ->count() * 5; // 5 minutes per log entry
                
            // Skip users with no activity
            if ($timeSpent <= 0) {
                continue;
            }
            
            // Get average grade for the course
            $grade = DB::table('mdl_grade_grades AS gg')
                ->join('mdl_grade_items AS gi', function($join) use ($courseId) {
                    $join->on('gi.id', '=', 'gg.itemid')
                         ->where('gi.courseid', $courseId)
                         ->where('gi.itemtype', 'course');
                })
                ->where('gg.userid', $user->id)
                ->avg('gg.finalgrade');
                
            // Skip users with no grades
            if (is_null($grade)) {
                continue;
            }
            
            $result[] = (object)[
                'userid' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'time_spent_minutes' => $timeSpent,
                'avg_grade' => (float)$grade
            ];
        }
        
        // Sort by time spent
        usort($result, function($a, $b) {
            return $a->time_spent_minutes <=> $b->time_spent_minutes;
        });
        
        return collect($result);
    }
    
    /**
     * Get detailed user data for the table
     */
    private function getUserTimeGradeData($courseId)
    {
        // First, get all enrolled users for this course
        $enrolledUsers = DB::table('mdl_user_enrolments AS ue')
            ->join('mdl_enrol AS e', 'ue.enrolid', '=', 'e.id')
            ->join('mdl_user AS u', 'ue.userid', '=', 'u.id')
            ->where('e.courseid', $courseId)
            ->select('u.id', 'u.firstname', 'u.lastname', 'u.email')
            ->orderBy('u.lastname')
            ->orderBy('u.firstname')
            ->get();
            
        $result = [];
        
        foreach ($enrolledUsers as $user) {
            // Get time spent (in minutes) - limit to last 90 days for performance
            $timeSpent = DB::table('mdl_logstore_standard_log')
                ->where('userid', $user->id)
                ->where('courseid', $courseId)
                ->where('action', 'viewed')
                ->where('timecreated', '>', time() - (90 * 24 * 60 * 60)) // Last 90 days
                ->count() * 5; // 5 minutes per log entry
                
            // Get last access time
            $lastAccess = DB::table('mdl_logstore_standard_log')
                ->where('userid', $user->id)
                ->where('courseid', $courseId)
                ->where('action', 'viewed')
                ->max('timecreated');
                
            // Get average grade for the course
            $grade = DB::table('mdl_grade_grades AS gg')
                ->join('mdl_grade_items AS gi', function($join) use ($courseId) {
                    $join->on('gi.id', '=', 'gg.itemid')
                         ->where('gi.courseid', $courseId)
                         ->where('gi.itemtype', 'course');
                })
                ->where('gg.userid', $user->id)
                ->avg('gg.finalgrade');
                
            $result[] = (object)[
                'userid' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'time_spent_minutes' => $timeSpent,
                'avg_grade' => $grade ? (float)$grade : null,
                'last_accessed' => $lastAccess ? date('Y-m-d H:i:s', $lastAccess) : null
            ];
        }
        
        return collect($result);
    }
}
