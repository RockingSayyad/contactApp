<?php

 use Illuminate\Support\Facades\Route;
 use App\Http\Controllers\ContactController;

 Route::get('/', function () {
     return view('welcome');
 });


Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::post('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');
Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::get('contacts/{id}', [ContactController::class, 'show'])->name('contacts.show'); 
Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');
