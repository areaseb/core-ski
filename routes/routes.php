<?php

//Route for anonimous users

use Areaseb\Core\Http\Controllers\PhonecallController;

Route::get('webhook/calls', [PhonecallController::class, 'test']);
Route::post('webhook/calls', [PhonecallController::class, 'testPost']);

Route::get('webhook/calls/{phone}', [PhonecallController::class, 'company']);
