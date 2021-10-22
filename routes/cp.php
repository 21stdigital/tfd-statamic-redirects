<?php

use Illuminate\Support\Facades\Route;
use TFD\Redirects\Http\Controllers\RedirectsController;

Route::get('statamic-redirects', [RedirectsController::class, 'index'])->name('statamic-redirects.index');
Route::get('statamic-redirects/create', [RedirectsController::class, 'create'])->name('statamic-redirects.create');
Route::post('statamic-redirects/store', [RedirectsController::class, 'store'])->name('statamic-redirects.store');

Route::get('statamic-redirects/edit/{id}', [RedirectsController::class, 'edit'])->name('statamic-redirects.edit');
Route::post('statamic-redirects/update/{id}', [RedirectsController::class, 'update'])->name('statamic-redirects.update');
Route::delete('statamic-redirects/delete/{id}', [RedirectsController::class, 'delete'])->name('statamic-redirects.delete');
