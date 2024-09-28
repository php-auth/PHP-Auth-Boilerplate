<?php

use Core\Route;
use App\Lib\Auth;

Route::add('/', fn() => controller('App')->screen('home'));

Route::add('/dashboard', function () {
    Auth::group('ALL', fn() => controller('App')->screen('dashboard'));
});
