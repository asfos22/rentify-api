<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->away('https://');  //todo redirect to web
});


// Account related relation route

Route::group([
    'prefix' => 'auth'
], function () {


    /*Route::get('login', '\App\Http\Controllers\Auth\LoginController@getAuthLoginView')->name('auth/login');
    Route::post('login', '\App\Http\Controllers\Auth\LoginController@accountLoginWeb')->name('auth/login');
    Route::post('registration', '\App\Http\Controllers\Auth\RegistrationController@postAccountRegistrationWeb')->name('auth/registration');
    Route::delete('logout', '\App\Http\Controllers\Auth\LoginController@deleteLogout');
    Route::post('password/change', '\App\Http\Controllers\Auth\PasswordController@postChangePassword');
    Route::post('password/reset', '\App\Http\Controllers\Auth\PasswordController@postResetForgottenPassword');
    Route::get('confirm', '\App\Http\Controllers\Auth\ConfirmationController@getConfirm');
    Route::post('confirm', '\App\Http\Controllers\Auth\ConfirmationController@postConfirmDB');*/
    Route::post('verify', '\App\Http\Controllers\Auth\LoginController@postVerify')->name('auth/verify');

});

/*Route::get('/', '\App\Http\Controllers\Web\PropertyController@index')->name('/');

Route::group([
    'prefix' => 'property'
], function () {
    Route::post('list/new/', '\App\Http\Controllers\Web\PropertyController@createNewProperty')->name('property/list/new/');
    Route::get('list/new/', '\App\Http\Controllers\Web\PropertyController@newProperty')->name('property/list/new/');
    Route::get('house/list/', '\App\Http\Controllers\Web\PropertyController@exploreProperty')->name('property/house/list/');
    Route::get('house/geo/', '\App\Http\Controllers\Web\PropertyController@exploreGeolocationProperty')->name('property/house/geo/');
    Route::get('house/list/property', '\App\Http\Controllers\Web\PropertyController@propertyDetails')->name('property/house/list/property');
    // Route::get('upload/user/properties/houses/media/images/{src}', '\App\Http\Controllers\PropertyController@index')->name('house/list/media/');
    //Route::post('', 'App\Http\Controllers\AuthApiController@signup');

    /*Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'App\Http\Controllers\AuthApiController@logout');
        Route::get('user', 'App\Http\Controllers\AuthApiController@user');
    });*
});

// hos related route

Route::group([
    'prefix' => 'dashboard'
], function () {
    Route::get('/host/', '\App\Http\Controllers\DashBoard\User\UserDashBoardController@getDashboard')->name('dashboard/host/');

    Route::get('/host/bookings', '\App\Http\Controllers\DashBoard\User\UserDashBoardController@getBookingDashboard')->name('dashboard/host/bookings');

    Route::get('/host/list', '\App\Http\Controllers\DashBoard\User\UserDashBoardController@getListDashboard')->name('dashboard/host/list');

    Route::get('/host/review', '\App\Http\Controllers\DashBoard\User\UserDashBoardController@getReviewDashboard')->name('dashboard/host/review');


    // Route::get('upload/user/properties/houses/media/images/{src}', '\App\Http\Controllers\PropertyController@index')->name('house/list/media/');
    //Route::post('', 'App\Http\Controllers\AuthApiController@signup');

    /*Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'App\Http\Controllers\AuthApiController@logout');
        Route::get('user', 'App\Http\Controllers\AuthApiController@user');
    });*
});


// -- upload
Route::group([
    'prefix' => 'upload'
], function () {
    Route::get('user/properties/houses/media/images/{src}', '\App\Http\Controllers\PropertyController@index')->name('house/list/media/');
});
//-- message
Route::group([
    'prefix' => 'messages'
], function () {


    Route::post('user/message', '\App\Http\Controllers\Message\MessageController@createRentHostMessage')->name('messages/user/message');

    Route::get('user/message', '\App\Http\Controllers\Message\MessageController@getWebMessages')->name('messages/user/message');

    // -- conversation
    Route::get('user/message/conversation',
        '\App\Http\Controllers\Message\MessageController@getWebConversation')
        ->name('messages/user/message/conversation');

    //--
    Route::post('user/message/conversation',
        '\App\Http\Controllers\Message\MessageController@createConversation')->name('user/message/conversation');

    //->where('token', '[A-Z]+');;


});


//-- review
Route::group([
    'prefix' => 'reviews'
], function () {
    Route::post('user/review', '\App\Http\Controllers\PropertyController@createReview')->name('reviews/user/review');
});

//-- subscription
Route::group([
    'prefix' => 'users'
], function () {
    Route::post('user/subscription', '\App\Http\Controllers\PropertyController@createNotificationToken')->name('users/user/subscription');
});*/


