<?php
Route::group(['middleware' => ['web']], function () {
    Route::prefix('razorpay/payment')->group(function () {

        Route::get('/razorpay', 'Razorpay\Http\Controllers\RazorpayController@redirect')->name('razorpay.payment.redirect');

        Route::post('/transaction','Razorpay\Http\Controllers\RazorpayController@transaction')->name('razorpay.payment.transaction');
    });
});
