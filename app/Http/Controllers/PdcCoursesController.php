<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PdcCoursesController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get current and previous month dates for stats comparison
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();

            // Get current month stats for PDC courses
            $currentStats = [
                'total_courses' => $this->getTotalPdcCourses($currentMonthEnd),
                'total_enrollments' => $this->getTotalEnrollments($currentMonthEnd),
                'new_courses' => $this->getNewPdcCourses($currentMonthStart, $currentMonthEnd),
                'courses_without_enrollments' => $this->getPdcCoursesWithoutEnrollments($currentMonthEnd)
            ];

            // Get last month stats for comparison
            $lastMonthStats = [
                'total_courses' => $this->getTotalPdcCourses($lastMonthEnd),
                'total_enrollments' => $this->getTotalEnrollments($lastMonthEnd),
                'new_courses' => $this->getNewPdcCourses($lastMonthStart, $lastMonthEnd),
                'courses_without_enrollments' => $this->getPdcCoursesWithoutEnrollments($lastMonthEnd)
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

            // Get enrollments by category for the chart
            $enrollmentsByCategory = $this->getEnrollmentsByCategory();
            
            // Get PDC courses with pagination and search
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'fullname');
            $sortDir = $request->input('sort_dir', 'asc');
            $perPage = 10;
            
            $query = DB::table('mdl_course as c')
                ->leftJoin('mdl_course_categories as cc', 'c.category', '=', 'cc.id')
                ->select(
                    'c.id',
                    'c.fullname as course_name',
                    'cc.name as category_name',
                    'c.visible',
                    'c.timecreated',
                    'c.timemodified',
                    DB::raw('(SELECT COUNT(DISTINCT ue.userid) 
                             FROM mdl_enrol e 
                             JOIN mdl_user_enrolments ue ON e.id = ue.enrolid 
                             JOIN mdl_role_assignments ra ON ra.userid = ue.userid 
                             JOIN mdl_role r ON ra.roleid = r.id 
                             WHERE e.courseid = c.id AND r.shortname = "student") as student_count'),
                    DB::raw('(SELECT COUNT(DISTINCT ue.userid) 
                             FROM mdl_enrol e 
                             JOIN mdl_user_enrolments ue ON e.id = ue.enrolid 
                             JOIN mdl_role_assignments ra ON ra.userid = ue.userid 
                             JOIN mdl_role r ON ra.roleid = r.id 
                             WHERE e.courseid = c.id AND r.shortname IN ("editingteacher", "teacher")) as instructor_count')
                )
                ->where('c.id', '!=', 1)
                ->where(function($query) {
                    $query->where('c.shortname', 'LIKE', '%PDC%');
                });

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('c.fullname', 'LIKE', "%{$search}%")
                      ->orWhere('c.shortname', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            $sortableColumns = [
                'id' => 'c.id',
                'course_name' => 'c.fullname',
                'category_name' => 'cc.name',
                'student_count' => 'student_count',
                'instructor_count' => 'instructor_count',
                'visible' => 'c.visible',
                'created' => 'c.timecreated',
                'modified' => 'c.timemodified'
            ];

            if (array_key_exists($sortBy, $sortableColumns)) {
                $query->orderBy($sortableColumns[$sortBy], $sortDir === 'desc' ? 'desc' : 'asc');
            }

            // Get paginated results
            $pdcCourses = $query->paginate($perPage)
                ->appends([
                    'search' => $search,
                    'sort_by' => $sortBy,
                    'sort_dir' => $sortDir
                ]);

            // Get top enrolled PDC courses
            $topEnrolledCourses = DB::table('mdl_course as c')
                ->select(
                    'c.id',
                    'c.fullname as course_name',
                    'c.shortname',
                    DB::raw('(SELECT COUNT(DISTINCT ue.userid) 
                             FROM mdl_enrol e 
                             JOIN mdl_user_enrolments ue ON e.id = ue.enrolid 
                             WHERE e.courseid = c.id) as enrollment_count')
                )
                ->where('c.id', '!=', 1)
                ->where('c.visible', 1)
                ->where('c.shortname', 'LIKE', '%PDC%')
                ->orderBy('enrollment_count', 'desc')
                ->take(5)
                ->get();

            // Get PDC courses without enrollments
            $coursesWithoutEnrollments = DB::table('mdl_course as c')
                ->leftJoin('mdl_enrol as e', 'c.id', '=', 'e.courseid')
                ->leftJoin('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
                ->select('c.id', 'c.fullname as course_name', 'c.shortname')
                ->where('c.id', '!=', 1)
                ->where('c.shortname', 'LIKE', '%PDC%')
                ->groupBy('c.id', 'c.fullname', 'c.shortname')
                ->havingRaw('COUNT(ue.id) = 0')
                ->orderBy('c.fullname')
                ->get();

            // Get recently modified PDC courses
            $recentlyModified = DB::table('mdl_course as c')
                ->leftJoin('mdl_course_categories as cc', 'c.category', '=', 'cc.id')
                ->select('c.id', 'c.fullname', 'c.shortname', 'c.timemodified', 'cc.name as category_name')
                ->where('c.id', '!=', 1)
                ->where('c.shortname', 'LIKE', '%PDC%')
                ->orderBy('c.timemodified', 'desc')
                ->take(5)
                ->get()
                ->map(function ($course) {
                    $course->last_modified = $course->timemodified 
                        ? Carbon::createFromTimestamp($course->timemodified)->diffForHumans()
                        : 'Never';
                    return $course;
                });

            // Get PDC courses per category
            $coursesPerCategory = DB::table('mdl_course as c')
                ->join('mdl_course_categories as cc', 'c.category', '=', 'cc.id')
                ->select('cc.name as category_name', DB::raw('COUNT(*) as course_count'))
                ->where('c.shortname', 'LIKE', '%PDC%')
                ->groupBy('cc.name')
                ->orderBy('course_count', 'desc')
                ->get();

            return view('pdc-courses.index', [
                'pdcCourses' => $pdcCourses,
                'stats' => $stats,
                'topEnrolledCourses' => $topEnrolledCourses,
                'coursesWithoutEnrollments' => $coursesWithoutEnrollments,
                'recentlyModified' => $recentlyModified,
                'coursesPerCategory' => $coursesPerCategory,
                'enrollmentsByCategory' => $enrollmentsByCategory,
                'search' => $request->input('search', ''),
                'sortBy' => $request->input('sort_by', 'fullname'),
                'sortDir' => $request->input('sort_dir', 'asc')
            ]);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error in PdcCoursesController: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Failed to load PDC courses. Please try again.',
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            return view('errors.debug', [
                'title' => 'PDC Courses Error',
                'description' => 'An error occurred while loading PDC courses.',
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]
            ]);
        }
    }

    // Helper methods for PDC course statistics
    private function getTotalPdcCourses($date = null)
    {
        $query = DB::table('mdl_course')
            ->where('id', '!=', 1)
            ->where('shortname', 'LIKE', '%PDC%');
            
        if ($date) {
            $query->where('timecreated', '<=', $date->timestamp);
        }
        
        return $query->count();
    }

    private function getActivePdcCourses($date = null)
    {
        $query = DB::table('mdl_course')
            ->where('id', '!=', 1)
            ->where('visible', 1)
            ->where('shortname', 'LIKE', '%PDC%');
            
        if ($date) {
            $query->where('timecreated', '<=', $date->timestamp);
        }
        
        return $query->count();
    }

    private function getNewPdcCourses($startDate, $endDate)
    {
        return DB::table('mdl_course')
            ->where('id', '!=', 1)
            ->where('shortname', 'LIKE', '%PDC%')
            ->whereBetween('timecreated', [$startDate->timestamp, $endDate->timestamp])
            ->count();
    }

    private function getPdcCoursesWithoutEnrollments($date = null)
    {
        return DB::table('mdl_course as c')
            ->select('c.id')
            ->leftJoin('mdl_enrol as e', 'c.id', '=', 'e.courseid')
            ->leftJoin('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
            ->where('c.id', '!=', 1)
            ->where('c.shortname', 'LIKE', '%PDC%')
            ->where(function($query) use ($date) {
                if ($date) {
                    $query->where('c.timecreated', '<=', $date->timestamp);
                }
            })
            ->groupBy('c.id')
            ->havingRaw('COUNT(ue.id) = 0')
            ->get()
            ->count();
    }
    
    /**
     * Get enrollments count by category for PDC courses
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getEnrollmentsByCategory()
    {
        return DB::table('mdl_course as c')
            ->select(
                'cc.name as category_name',
                DB::raw('COUNT(DISTINCT ue.userid) as enrollments_count')
            )
            ->join('mdl_enrol as e', 'c.id', '=', 'e.courseid')
            ->join('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
            ->join('mdl_course_categories as cc', 'c.category', '=', 'cc.id')
            ->where('c.shortname', 'LIKE', '%PDC%')
            ->where('c.id', '!=', 1)
            ->groupBy('cc.id', 'cc.name')
            ->having('enrollments_count', '>', 0)
            ->orderBy('enrollments_count', 'desc')
            ->get();
    }
            
    private function getTotalEnrollments($date = null)
    {
        return DB::table('mdl_course as c')
            ->join('mdl_enrol as e', 'c.id', '=', 'e.courseid')
            ->join('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
            ->where('c.id', '!=', 1)
            ->where('c.shortname', 'LIKE', '%PDC%')
            ->where(function($query) use ($date) {
                if ($date) {
                    $query->where('ue.timecreated', '<=', $date->timestamp);
                }
            })
            ->count(DB::raw('DISTINCT ue.id'));
    }
}
