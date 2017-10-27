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

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

Route::post('/register', 'Api\Auth\LoginController@register');
Route::post('/login', 'Api\Auth\LoginController@login');
Route::post('/verifikasiregister', 'Api\Auth\LoginController@register_verification');
Route::post('/operations', 'Api\Auth\LoginController2@operations');

Route::get('/pusher', function () {
	$data = (object) [
		'data' => 'data',
	];
	event(new App\Events\HelloPusherEvent($data));
	$customer = App\Customer::first();
	$users = $customer->users;
	foreach ($users as $u) {
		$u->notify(new App\Notifications\DummyNotif($data));
	}
	return 'test';
});
