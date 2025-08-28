<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentSearchController extends Controller
{
    /**
     * Get categories for the dropdown
     */
    protected function getCategories()
    {
        try {
            return DB::table('mdl_course_categories as cc')
                ->join('mdl_course as c', 'cc.id', '=', 'c.category')
                ->where('cc.parent', '!=', 0) // Skip top-level categories
                ->where('cc.visible', 1)
                ->where('c.visible', 1)
                ->select('cc.id', 'cc.name')
                ->distinct()
                ->orderBy('cc.name')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting categories: ' . $e->getMessage());
            return collect();
        }
    }
    
    public function index()
    {
        try {
            // Get all non-empty categories that are not the top-level category
            $categories = DB::table('mdl_course_categories as cc')
                ->join('mdl_course as c', 'cc.id', '=', 'c.category')
                ->where('cc.parent', '!=', 0) // Skip top-level categories
                ->where('cc.visible', 1)
                ->where('c.visible', 1)
                ->select('cc.id', 'cc.name')
                ->distinct()
                ->orderBy('cc.name')
                ->get();
            
            // If no categories found, return an empty collection
            if ($categories->isEmpty()) {
                $categories = collect([
                    (object)['id' => 3, 'name' => 'Computing and Informatics'],
                    (object)['id' => 7, 'name' => 'Mathematics and Statistics'],
                    (object)['id' => 8, 'name' => 'Technology Education'],
                    (object)['id' => 9, 'name' => 'Management Studies'],
                    (object)['id' => 10, 'name' => 'Entrepreneurship and Business'],
                    (object)['id' => 11, 'name' => 'Educational Management']
                ]);
            }
            
            return view('student-search.index', compact('categories'));
            
        } catch (\Exception $e) {
            // Log the error and return a default set of categories
            \Log::error('Error in StudentSearchController@index: ' . $e->getMessage());
            
            $categories = collect([
                (object)['id' => 3, 'name' => 'Computing and Informatics'],
                (object)['id' => 7, 'name' => 'Mathematics and Statistics'],
                (object)['id' => 8, 'name' => 'Technology Education']
            ]);
            
            return view('student-search.index', compact('categories'));
        }
    }

    public function getCourses(Request $request)
    {
        try {
            // Check if we're looking for a specific course by ID
            $courseId = $request->input('course_id');
            if ($courseId) {
                $course = DB::table('mdl_course')
                    ->where('id', $courseId)
                    ->where('visible', 1)
                    ->select('id', 'fullname as text', 'shortname')
                    ->first();
                
                if ($course) {
                    return response()->json([$course]);
                }
                return response()->json([
                    'error' => 'Course not found',
                    'course_id' => $courseId
                ], 404);
            }
            
            // Get category ID from query string for category-based search
            $categoryId = $request->input('category_id');
            
            if (!$categoryId) {
                return response()->json([
                    'error' => 'Category ID is required',
                    'category_id' => null
                ], 400);
            }
            
            // Validate category ID
            $category = DB::table('mdl_course_categories')
                ->where('id', $categoryId)
                ->where('visible', 1)
                ->first();
                
            if (!$category) {
                return response()->json([
                    'error' => 'Category not found or not visible',
                    'category_id' => $categoryId
                ], 404);
            }
            
            // Get all visible courses in this category
            $courses = DB::table('mdl_course')
                ->where('category', $categoryId)
                ->where('visible', 1)
                ->where('id', '!=', 1) // Skip the front page course
                ->select('id', 'fullname as text', 'shortname')
                ->orderBy('fullname')
                ->get();
                
            // If no courses found, return empty array
            if ($courses->isEmpty()) {
                return response()->json([
                    'error' => 'No courses found in this category',
                    'category_id' => $categoryId,
                    'category_name' => $category->name
                ], 404);
            }
            
            return response()->json($courses);
            
        } catch (\Exception $e) {
            \Log::error('Error in getCourses: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error fetching courses',
                'message' => $e->getMessage(),
                'category_id' => $categoryId ?? 'unknown'
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|numeric',
            ]);
            
            $students = collect();
            $course = null;
            
            // Get course details first
            $course = DB::table('mdl_course')
                ->where('id', $request->course_id)
                ->first();
                
            if (!$course) {
                if ($request->ajax()) {
                    return response()->json(['message' => 'Course not found'], 404);
                }
                return back()->with('error', 'Course not found');
            }
            
            // Get students enrolled in the course
            if (DB::getSchemaBuilder()->hasTable('mdl_user') && 
                DB::getSchemaBuilder()->hasTable('mdl_role_assignments') &&
                DB::getSchemaBuilder()->hasTable('mdl_context') &&
                DB::getSchemaBuilder()->hasTable('mdl_role')) {
                
                $students = DB::table('mdl_user')
                    ->join('mdl_role_assignments as ra', 'ra.userid', '=', 'mdl_user.id')
                    ->join('mdl_context as ctx', 'ctx.id', '=', 'ra.contextid')
                    ->join('mdl_role as r', 'r.id', '=', 'ra.roleid')
                    ->where('ctx.instanceid', $request->course_id)
                    ->where('ctx.contextlevel', 50) // Course context level
                    ->where('r.shortname', 'student')
                    ->where('mdl_user.deleted', 0)
                    ->select('mdl_user.id', 'mdl_user.firstname', 'mdl_user.lastname', 'mdl_user.email')
                    ->orderBy('mdl_user.lastname')
                    ->orderBy('mdl_user.firstname')
                    ->paginate(20);  // Paginate with 20 items per page
            }
            
            // For AJAX requests, return the partial view with pagination links
            if ($request->ajax()) {
                $view = view('student-search.results', [
                    'students' => $students,
                    'course' => $course
                ])->render();
                
                $pagination = $students->links()->toHtml();
                
                return response()->json([
                    'html' => $view,
                    'pagination' => $pagination
                ]);
            }
            
            // For regular requests, return the full view
            return view('student-search.index', [
                'students' => $students,
                'course' => $course,
                'categories' => $this->getCategories()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error performing search: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error performing search: ' . $e->getMessage());
        }
    }
}
