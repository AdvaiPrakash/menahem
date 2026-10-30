<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| The landing route delegates to LandingPageController, passing
| the content matrix directly to the Blade presentation layer.
*/

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
