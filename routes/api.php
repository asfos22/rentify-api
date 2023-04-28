<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Account related relation route

Route::group([
    'prefix' => 'auth',
], function () {

    Route::post('login', '\App\Http\Controllers\Auth\LoginController@accountLogin');
    Route::delete('logout', '\App\Http\Controllers\Auth\LoginController@deleteLogout');
    Route::post('password/change', '\App\Http\Controllers\Auth\PasswordController@postChangePassword');
    Route::post('password/forgot/change', '\App\Http\Controllers\Auth\PasswordController@postForgotChangePassword');
    Route::post('password/reset', '\App\Http\Controllers\Auth\PasswordController@postResetForgottenPassword');
    Route::post('registration', '\App\Http\Controllers\Auth\RegistrationController@postAccountRegistration');
    Route::post('confirm', '\App\Http\Controllers\Auth\ConfirmationController@postAccountConfirm');
    Route::post('forgot/confirm', '\App\Http\Controllers\Auth\ConfirmationController@postForgotAccountConfirm');
   // Route::match(array('GET', 'POST'), 'verify', '\App\Http\Controllers\Auth\ConfirmationController@postAccountVerify');

});

// Property related relation route

Route::group([
    'prefix' => 'property',
], function () {

    Route::get('list/facilities', '\App\Http\Controllers\Property\FacilityController@getPropertyFacility');
    Route::post('house/list', '\App\Http\Controllers\PropertyController@createNewProperty');
   // Route::get('list/new/', '\App\Http\Controllers\Web\PropertyController@newProperty');
    Route::get('house/list/', '\App\Http\Controllers\PropertyController@index')->name('property/house/list/');
    Route::get('house/list/{token}', '\App\Http\Controllers\PropertyController@fetchByToken')->name('house/list/');
    Route::post('house/list/location', '\App\Http\Controllers\PropertyController@getNearbyProperties');
    // Route::match(array('GET', 'POST'), 'house/list/location', '\App\Http\Controllers\PropertyController@getNearbyProperties')
    Route::get('list/facilities/rule', '\App\Http\Controllers\Property\FacilityController@getPropertyFacilityRules');

});

Route::group([
    'prefix' => 'media',
], function () {

    Route::post('property/list/upload', '\App\Http\Controllers\Media\MediaController@createPropertyMedia');

    Route::get('user/properties/houses/media/images/{src}',
        '\App\Http\Controllers\PropertyController@index')
        ->name('house/list/upload/');
});

//-- message
Route::group([
    'prefix' => 'messages',
], function () {

    //--
    //Route::get('user/message', '\App\Http\Controllers\PropertyController@getMessages');

    Route::get('user/message', '\App\Http\Controllers\Message\MessageController@getMessages')->name('messages/user/message');
    //--
    Route::post('user/message', '\App\Http\Controllers\Message\MessageController@createRentHostMessage')->name('messages/user/message');

    //--
    Route::post('user/message/guest', '\App\Http\Controllers\Message\MessageController@createGuestHostMessage')->name('messages/user/message');

     // -- conversations

     Route::post('user/message/conversations',
     '\App\Http\Controllers\Message\MessageController@getConversations')
       ->name('messages/user/message/conversations');

     //-- conversation

    Route::post('user/message/conversation',
        '\App\Http\Controllers\Message\MessageController@getConversation')
        ->name('messages/user/message/conversation');
       
    //--
    Route::post('user/message/conversation/new',
        '\App\Http\Controllers\Message\MessageController@createConversation');
});

//-- review
Route::group([
    'prefix' => 'reviews',
], function () {

    Route::get('property/scale/review', '\App\Http\Controllers\RateReview\RateReviewController@fetchPropertyReviewScale')
           ->name('reviews/users/review/property');
    Route::get('users/review/property', '\App\Http\Controllers\RateReview\RateReviewController@fetchUserPropertyReview')
          ->name('reviews/users/review/property');
    Route::post('users/review/property', '\App\Http\Controllers\RateReview\RateReviewController@createUserPropertyReview')
          ->name('reviews/users/review/property');
});

//-- user fcm subscription
Route::group([
    'prefix' => 'users',
], function () {
    Route::post('user/notifications/subscription',
        '\App\Http\Controllers\Subscription\SubscriptionController@createUserNotificationToken')
        ->name('users/user/notifications/subscription');
});

//Location related relation route

/*Route::group([
    'prefix' => 'location',
], function () {
    Route::post('list/property', '\App\Http\Controllers\Property\FacilityController@getPropertyFacility');
});*/

//-- guest subscription
/*Route::group([
'prefix' => 'guests'
], function () {
Route::post('guest/subscription',
'\App\Http\Controllers\Subscription\SubscriptionController@createGuestNotificationToken')
->name('guests/guest/subscription');
});*/
