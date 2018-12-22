<?php
Route::post(config('comepay.notificationUrl'), 'Grechanyuk\Comepay\Controllers\NotificationController@notificate')->middleware('comepay');