<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/native-php/index.php');
});
