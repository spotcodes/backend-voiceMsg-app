<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

Auth::routes();

//password/reset
// for admin panel
//password/reset/{token}

Route::get('/admin', [AdminController::class, 'index']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::get('/thankyou', [HomeController::class, 'thankyou']);

Route::group(['prefix' => '/admin', 'middleware' => ['adminauth']], function(){
    	Route::get('/dashboard', [DashboardController::class, 'dashboard']);
	
		Route::get('/users', [EmployeeController::class, 'employees']);
		
		Route::get('/userStatusChange/{status}/{id}',[EmployeeController::class, 'userStatusChange']);
        Route::post('/deleteemployee',[EmployeeController::class, 'deleteemployee']);
		Route::get('/notification',[EmployeeController::class, 'notification']);
		Route::post('/saveNotification',[EmployeeController::class, 'saveNotification']);
		
		/*Route::get('/addemployee', [EmployeeController::class, 'addemployee']);
		Route::post('/saveemployee', [EmployeeController::class, 'saveemployee']);

		Route::get('/editemployee/{id}',[EmployeeController::class, 'editemployee']);
		
		Route::post('/updateemployee',[EmployeeController::class, 'updateemployee']);
		*/

		Route::get('/news', [NewsController::class, 'news']);
		Route::get('/addnews', [NewsController::class, 'addnews']);
		Route::post('/savenews',[NewsController::class, 'savenews']);
		Route::get('/editnews/{id}',[NewsController::class, 'editnews']);
		Route::post('/updatenews',[NewsController::class, 'updatenews']);
		Route::post('/deletenews',[NewsController::class, 'deletenews']);

		Route::get('/category', [CategoryController::class, 'category']);
		Route::get('/addcategory', [CategoryController::class, 'addcategory']);
		Route::post('/savecategory',[CategoryController::class, 'savecategory']);
		Route::get('/editcategory/{id}',[CategoryController::class, 'editcategory']);
		Route::post('/updatecategory',[CategoryController::class, 'updatecategory']);
		Route::post('/deletecategory',[CategoryController::class, 'deletecategory']);

		Route::get('/photo', [PhotoController::class, 'photo']);
		Route::get('/viewphoto/{id}',[PhotoController::class, 'viewphoto']);
		Route::post('/deleteComment',[PhotoController::class, 'deleteComment']);
		Route::post('/deletephoto',[PhotoController::class, 'deletephoto']);
		
		///////////
		Route::post('/logout', [DashboardController::class, 'logout']);

		Route::get('/changePassword', [DashboardController::class, 'changePassword']);
		Route::post('/updatePassword', [DashboardController::class, 'updatePassword']);
		Route::post('/editUser',[EmployeeController::class,'editUser']);
        Route::get('/storeUser',[EmployeeController::class,'storeUser']);

		

});


