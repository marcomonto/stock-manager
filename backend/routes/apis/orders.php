<?php


use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'orders',
    'as' => 'orders.',
    'controller' => OrderController::class,
], function () {
    Route::get('/', 'list')->name('list');
    Route::get('/{orderId}', 'find')->name('find');
    Route::post('/', 'create')->name('create');
    Route::patch('/{orderId}', 'patch')->name('patch');
    Route::delete('/{orderId}', 'delete')->name('delete');
});
