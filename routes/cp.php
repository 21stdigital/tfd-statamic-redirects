<?php

use Illuminate\Support\Facades\Route;
use TFD\Redirects\Http\Controllers\IndexController;
use TFD\Redirects\Http\Controllers\NotFoundController;
use TFD\Redirects\Http\Controllers\RedirectsController;

Route::get('statamic-redirects', [IndexController::class, 'index'])->name('statamic-redirects::index');

Route::get('statamic-redirects/redirects', [RedirectsController::class, 'index'])->name('statamic-redirects::redirects.index');
Route::get('statamic-redirects/redirects/create', [RedirectsController::class, 'create'])->name('statamic-redirects.create');
Route::post('statamic-redirects/redirects/store', [RedirectsController::class, 'store'])->name('statamic-redirects.store');

Route::get('statamic-redirects/redirects/edit/{id}', [RedirectsController::class, 'edit'])->name('statamic-redirects.edit');
Route::post('statamic-redirects/redirects/update/{id}', [RedirectsController::class, 'update'])->name('statamic-redirects.update');
Route::delete('statamic-redirects/redirects/delete/{id}', [RedirectsController::class, 'delete'])->name('statamic-redirects.delete');

Route::get('statamic-redirects/notfound', [NotFoundController::class, 'index'])->name('statamic-redirects::notfound.index');
