<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to AEW (Advanced English Writing).'
    ], 401);
    //return view('welcome');
});
