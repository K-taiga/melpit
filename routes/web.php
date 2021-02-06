<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('top');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware('auth')
    ->group(function() {
        Route::get('sell','SellController@showSellForm')->name('sell');
});

Route::prefix('mypage')
    // ディレクトリのnamespace
    ->namespace('MyPage')
    // authの処理を挟む
    ->middleware('auth')
    ->group(function () {
        Route::get('edit-profile', 'ProfileController@showProfileEditForm')->name('mypage.edit-profile');
        Route::post('edit-profile', 'ProfileController@editProfile')->name('mypage.edit-profile');
});