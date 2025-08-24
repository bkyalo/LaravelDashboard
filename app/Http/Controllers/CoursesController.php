<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoursesController extends Controller
{
    /**
     * Get base course query with common filters
     */
    protected function getBaseCourseQuery()
    {
        return DB::table('mdl_course as c')
            ->where('c.id', '!=', 1); // Skip the front page
    }

    /**
     * Get paginated courses with search and sort
     */
    protected function getPaginatedCourses($request, $perPage = 10)
    {
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'fullname');
        $sortDir = $request->input('sort_dir', 'asc');

        $query = $this->getBaseCourseQuery()
            ->select(
                'c.id',
                'c.fullname as course_name',
                'c.visible',
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
            );

        if ($search) {
            $query->where('c.fullname', 'LIKE', "%{$search}%");
        }

        $sortableColumns = [
            'id' => 'c.id',
            'course_name' => 'c.fullname',
            'student_count' => 'student_count',
            'instructor_count' => 'instructor_count',
            'visible' => 'c.visible'
        ];

        if (array_key_exists($sortBy, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sortBy], $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($perPage)
            ->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir
            ]);
    }

    /**
     * Get top or least enrolled courses
     */
    protected function getEnrolledCourses($limit = 10, $order = 'desc')
    {
        $query = $this->getBaseCourseQuery()
            ->select(
                'c.id',
                'c.fullname as course_name',
                'c.visible',
                DB::raw('COUNT(ue.id) as enrollment_count')
            )
            ->leftJoin('mdl_enrol as e', 'c.id', '=', 'e.courseid')
            ->leftJoin('mdl_user_enrolments as ue', 'e.id', '=', 'ue.enrolid')
            ->where('c.visible', 1)
            ->groupBy('c.id', 'c.fullname', 'c.visible');

        if ($order === 'desc') {
            $query->orderByDesc('enrollment_count');
        } else {
            $query->having('enrollment_count', '>', 0)
                  ->orderBy('enrollment_count');
        }

        return $query->take($limit)->get();
    }

    /**
     * Get recently modified courses
     */
    protected function getRecentlyModified($limit = 10)
    {
        return $this->getBaseCourseQuery()
            ->select('id', 'fullname', 'shortname', 'timemodified')
            ->orderBy('timemodified', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($course) {
                $course->last_modified = $course->timemodified 
                    ? Carbon::createFromTimestamp($course->timemodified)->diffForHumans()
                    : 'Never';
                return $course;
            });
    }

    /**
     * Get course creation stats by month for the current year
     */
    protected function getCreationStats()
    {
        return $this->getBaseCourseQuery()
            ->select(
                DB::raw('FROM_UNIXTIME(timecreated, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw('YEAR(FROM_UNIXTIME(timecreated)) = ?', [now()->year])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get dashboard statistics
     */
    protected function getDashboardStats()
    {
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $currentStats = [
            'total_courses' => $this->getTotalCourses($currentMonthEnd),
            'active_courses' => $this->getActiveCourses($currentMonthEnd),
            'new_courses' => $this->getNewCourses($currentMonthStart, $currentMonthEnd),
            'pdc_courses' => $this->getPdcCourses($currentMonthEnd),
            'courses_without_enrollments' => $this->getCoursesWithoutEnrollments($currentMonthEnd)
        ];

        $lastMonthStats = [
            'total_courses' => $this->getTotalCourses($lastMonthEnd),
            'active_courses' => $this->getActiveCourses($lastMonthEnd),
            'new_courses' => $this->getNewCourses($lastMonthStart, $lastMonthEnd),
            'pdc_courses' => $this->getPdcCourses($lastMonthEnd),
            'courses_without_enrollments' => $this->getCoursesWithoutEnrollments($lastMonthEnd)
        ];

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

        return $stats;
    }

    public function index(Request $request)
    {
        try {
            // Get dashboard statistics
            $stats = $this->getDashboardStats();
            
            // Get paginated courses with search and sort
            $allCourses = $this->getPaginatedCourses($request);
            
            // Get top and least enrolled courses
            $topEnrolledCourses = $this->getEnrolledCourses(10, 'desc');
            $leastEnrolledCourses = $this->getEnrolledCourses(10, 'asc');
            
            // Get recently modified courses
            $recentlyModified = $this->getRecentlyModified(10);
            
            // Get course creation stats and categories
            $creationStats = $this->getCreationStats();
            $coursesPerCategory = $this->getCoursesPerCategory();

            return view('courses.index', [
                'stats' => $stats,
                'topEnrolledCourses' => $topEnrolledCourses,
                'leastEnrolledCourses' => $leastEnrolledCourses,
                'recentlyModified' => $recentlyModified,
                'creationStats' => $creationStats,
                'coursesPerCategory' => $coursesPerCategory,
                'allCourses' => $allCourses,
                'search' => $request->input('search'),
                'sortBy' => $request->input('sort_by', 'fullname'),
                'sortDir' => $request->input('sort_dir', 'asc')
            ]);

        } catch (\Exception $e) {
            // Log the full error with stack trace
            $errorMessage = 'Error in CoursesController: ' . $e->getMessage() . '\n' . $e->getTraceAsString();
            \Log::error($errorMessage);
            
            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to load courses. Please try again.'], 500);
            }
            
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

    /**
     * Get courses data for DataTables
     */
    public function getCoursesData()
    {
        try {
            $query = DB::table('mdl_course as c')
                ->select(
                    'c.id',
                    'c.fullname as course_name',
                    'c.visible',
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
                ->where('c.id', '!=', 1); // Skip the front page

            // Handle search
            if (request()->has('search') && !empty(request('search')['value'])) {
                $search = request('search')['value'];
                $query->where('c.fullname', 'LIKE', "%{$search}%");
            }

            // Handle sorting
            if (request()->has('order')) {
                $orderColumn = request('order')[0]['column'];
                $orderDir = request('order')[0]['dir'];
                $columns = [
                    'c.id',
                    'c.fullname',
                    'student_count',
                    'instructor_count',
                    'c.visible'
                ];
                
                if (isset($columns[$orderColumn])) {
                    $query->orderBy($columns[$orderColumn], $orderDir);
                }
            } else {
                $query->orderBy('c.fullname', 'asc');
            }

            // Get total records count
            $totalRecords = $query->count();
            
            // Apply pagination
            $perPage = request('length', 10);
            $page = request('start', 0) / $perPage + 1;
            
            $courses = $query->paginate($perPage, ['*'], 'page', $page);

            // Format data for DataTables
            $data = [];
            foreach ($courses as $index => $course) {
                $data[] = [
                    'DT_RowIndex' => $courses->firstItem() + $index,
                    'course_name' => $course->course_name,
                    'student_count' => $course->student_count,
                    'instructor_count' => $course->instructor_count,
                    'visible' => $course->visible,
                ];
            }

            return response()->json([
                'draw' => request('draw', 1),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $courses->total(),
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getCoursesData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load courses data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
