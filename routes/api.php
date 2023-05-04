<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

Route::middleware('auth:api')->group(function () {

    Route::get('/user', [AuthController::class, 'getUser'])->name('api.user');
    Route::put('/user', [AuthController::class, 'update'])->name('api.user.update');
    Route::get('/refresh-token', [AuthController::class, 'refresh'])->name('api.refresh');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Kapal
    Route::get('/kapal', [KapalController::class, 'index'])->name('api.kapal');
    Route::post('/kapal', [KapalController::class, 'store'])->name('api.kapal.store');
    Route::put('/kapal', [KapalController::class, 'update'])->name('api.kapal.update');
});
