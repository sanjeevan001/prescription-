<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\NotificationController;

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

Route::get('/login', function () {
    return view('login');
})->name('login');

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Routes for prescriptions
    Route::get('/create/prescription', [PrescriptionController::class, 'create'])->name('prescriptions.index'); // Display the form to create a new prescription
    Route::post('/store/prescription', [PrescriptionController::class, 'store'])->name('prescriptions.store'); // Store a new prescription
    Route::get('/prescriptions', [PrescriptionController::class, 'show'])->name('prescriptions.show'); // Display a list of all prescriptions

    // Routes for quotations
    Route::get('/create/{id}/quotation', [QuotationController::class, 'create']); // Display the form to create a quotation for the given prescription
    Route::post('/store/{id}/quotation', [QuotationController::class, 'store'])->name('quotation.store'); // Store a new quotation for the given prescription
    Route::get('/quotations', [QuotationController::class, 'show'])->name('quotations.show'); // Display a list of all quotations
    Route::get('/quotation/items/{id}', [QuotationController::class, 'findItems']); // Find the items for the given quotation
    Route::post('/quotation/status', [QuotationController::class, 'updateStatus']); // Update the status of the given quotation

    // Route for notifications
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead'); // Mark the notification as read
});
