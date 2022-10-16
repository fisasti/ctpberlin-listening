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
// middleware('auth:api')->
// Route::group(function () {
    Route::match(array('GET', 'POST'), '/addVideoWalk', '\App\Http\Controllers\VideoWalkController@add');
    Route::post('/transcodeVideo', '\App\Http\Controllers\VideoWalkController@transcodeVideo');
    Route::post('/muxAssetCreated', '\App\Http\Controllers\VideoWalkController@webhookMuxAssetCreated');
// });
/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */
