<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [Auth::class, 'register']);
Route::post('login', [Auth::class, 'login']);
Route::get('logout', [Auth::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('transaction', [TransactionController::class, 'getAllTransaction']);
    Route::get('transaction/{id}', function (Request $id) {
        return TransactionController::getTransaction($id);
    });
    Route::post('transaction', [TransactionController::class, 'createTransaction']);
    Route::patch('transaction/{id}', function (Request $request, $id) {
        return TransactionController::editTransaction($request, $id);
    });
    Route::delete('transaction/{id}', function ($id) {
        return TransactionController::deleteTransaction($id);
    });
    Route::get('weekly/transaction', function () {
        return TransactionController::getWeeklyTransaction();
    });
    Route::get('monthly/transaction', function () {
        return TransactionController::getMonthlyTransaction();
    });
    Route::get('weekly/transaction/type', function () {
        return TransactionController::getWeeklyTransactionByType();
    });
    Route::get('date/transaction', function (Request $request) {
        return TransactionController::getTransactionByDate($request);
    });
});
