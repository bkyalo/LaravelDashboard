<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('/test-db', function() {
    try {
        // Test database connection
        $pdo = DB::connection()->getPdo();
        
        // Get list of all tables
        $tables = [];
        $result = DB::select("SHOW TABLES");
        
        // Extract table names from the result
        $tables = array_map('current', (array) $result);
        
        // Get sample data from course tables if they exist
        $sampleData = [];
        if (in_array('mdl_course', $tables)) {
            $sampleData['courses'] = DB::table('mdl_course')->limit(5)->get();
        }
        if (in_array('mdl_course_categories', $tables)) {
            $sampleData['categories'] = DB::table('mdl_course_categories')->limit(5)->get();
        }
        
        return response()->json([
            'connection' => 'success',
            'tables' => $tables,
            'sample_data' => $sampleData
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'connection' => 'failed'
        ], 500);
    }
});
