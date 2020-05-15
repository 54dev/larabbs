<?php

use Illuminate\Http\Request;

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

Route::prefix('v1')->namespace('Api')->name('api.v1.')->group(function(){

    Route::middleware('throttle:'.config('api.rate_limits.sign'))->group(function (){
        Route::post('captchas', 'CaptchasController@store')->name('captchas.store');
        Route::post('verificationCodes','VerificationCodesController@store')->name('verificationCodes.store');
        //注册新用户
        Route::post('users', 'UsersController@store')->name('users.store');

        //第三方登录
        Route::post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
                ->where('social_type','weixin')
                ->name('socials.authorizations.store');
        //登录
        Route::post('authorizations', 'AuthorizationsController@store')->name('api.authorizations.store');
        // refresh token
        Route::put('authorizations/current', 'AuthorizationsController@update')->name('authorizations.update');
        // del token
        Route::delete('authorizations/current', 'AuthorizationsController@destroy')->name('authorizations.destory');
    });

    Route::middleware('throttle:' . config('api.rate_limits.access'))->group(function () {

        // guest
        Route::get('/users/{user}', 'UsersController@show')->name('users.show');
        Route::get('categories','CategoriesController@index')->name('categories.index');
        Route::get('users/{user}/topics', 'TopicsController@userIndex')->name('users.topics.index');
        Route::resource('topics', 'TopicsController')->only(['index','show']);
        Route::get('topics/{topic}/replies', 'RepliesController@index')->name('topics.replies.index');
        Route::get('users/{user}/replies', 'RepliesController@userIndex')->name('users.replies.index');
        Route::get('notifications','NotificationsController@index')->name('notifications.index');
        Route::get('notifications/stats','NotificationsController@stats')->name('notifications.stats');
        Route::patch('user/read/notifications', 'NotificationsController@read')->name('user.notifications.read');
        Route::get('user/permissions', 'PermissionsController@index')->name('user.permissions.index');
        Route::get('links','LinksController@index')->name('links.index');


        // login
        Route::middleware('auth:api')->group(function (){
            Route::get('user','UsersController@me')->name('user.show');

            Route::post('images', 'ImagesController@store')->name('images.store');

            // 编辑登录用户信息
            Route::patch('user', 'UsersController@update')->name('user.update');

            Route::post('images', 'ImagesController@store')->name('images.store');

            //post topic
            Route::resource('topics','TopicsController')->only(['store','update','destroy']);

            // post reply
            Route::post('topics/{topic}/replies', 'RepliesController@store')->name('topics.replies.store');
            // delete reply
            Route::delete('topics/{topic}/replies/{reply}', 'RepliesController@destroy')->name('topics.replies.destroy');
        });
    });

});
