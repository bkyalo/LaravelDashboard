 <?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CoursesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Users Management
Route::get('/users', [UsersController::class, 'index'])->name('users.index');

// Courses Management
Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
Route::get('/courses/time-vs-grades', [CoursesController::class, 'timeVsGrades'])->name('courses.time-vs-grades');
