<?php

use App\Http\Controllers\PlantingExerciseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\UserController;

use App\Http\Controllers\UserController;
Route::group(['middleware' => 'auth.basic'], function () {
//Route::get('/users', [UserController::class, 'index']);
Route::get('/update', [UserController::class, 'updateUsers']);
Route::get('/regional_performance', [UserController::class, 'calculateRegionalPerformance']);
Route::post('/activate', [UserController::class, 'store']);
Route::post('/confirm_otp', [UserController::class, 'confirmOtp']);
Route::post('/set_password', [UserController::class,'setPassword']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/register_exercise',[PlantingExerciseController::class, 'registerExercise']);
Route::post('/start_exercise',[PlantingExerciseController::class, 'startExercise']);
Route::get('/close_exercise',[PlantingExerciseController::class, 'closeExercise']);
Route::get('/search_exercise',[PlantingExerciseController::class, 'searchPlantingExercise']);
Route::get('/participate_in_exercise',[PlantingExerciseController::class, 'participate']);
Route::post('/plant_tree',[PlantingExerciseController::class, 'plantATree']);
Route::post('/upload_image',[PlantingExerciseController::class, 'uploadImage']);
Route::get('/initiator_exercises',[PlantingExerciseController::class, 'fetchPlantingExercisesByInitiator']);
Route::get('/participated_exercises',[UserController::class, 'fetchParticipatedExercises']);
Route::get('/count',[UserController::class, 'countTreesOfParticipants']);
Route::get('/updateDashboard',[UserController::class, 'updateDashboard']);
Route::get('/sec_performance',[UserController::class, 'calculateSecretariatPerformance']);
Route::get('/teachers_per_county',[UserController::class, 'calculateTeacherPerformancePerCounty']);
});

//Route::get('/users', 'UserController@index');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Route::get('/users', 'UserController@index');
//Route::post('/users', 'UserController@store');
// Other API routes...

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
