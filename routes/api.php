<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeesController;
use App\Models\Employee;

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

//Paso por middlewares Autenticacion y autorizacion
Route::middleware(['token','permissions'])->prefix('employees')->group(function () {

    Route::put('/register', [EmployeesController::class, 'register']);
    Route::put('/listEmployees', [EmployeesController::class, 'listEmployees']);
});

//Sin paso por middlewares
Route::prefix('employees')->group(function () {
    Route::post('/login', [EmployeesController::class, 'login']);
    Route::post('/passwordRecovery', [EmployeesController::class, 'passwordRecovery']);
});





