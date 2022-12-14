<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ScraperController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SampleController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TwitterController;

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

Route::get('lang/{locale}', [LocalizationController::class, 'index'])->name('lang');

Route::controller(SampleController::class)->group(function () {

    Route::get('login', 'index')->name('login');

    Route::get('registration', 'registration')->name('registration');

    Route::get('forgot-password', 'forgotPassword')->name('forgotPassword');

    Route::get('logout', 'logout')->name('logout');

    Route::get('dashboard', 'dashboard' )->name('dashboard')->middleware(['auth', 'is_verify_email']);

    Route::get('dashboard', 'dashboard' )->name('dashboard')->middleware(['auth', 'isAdmin']);

    Route::get('author/{id}', 'getAuthor')->name('sample.author');

    Route::post('validate_registration', 'validate_registration')->name('sample.validate_registration');

    Route::post('validate_login', 'validate_login')->name('sample.validate_login');

    Route::get('account/verify/{token}', 'verifyAccount')->name('user.verify');

    Route::post('content/post', 'store')->name('content.post');

    Route::post('content/comment', 'store')->name('content.comment');

    Route::get('admin/graphs', [GraphController::class, 'index'])->name('graph');

    Route::post('admin/graphs', [GraphController::class, 'getDate'])->name('graph.send');

    Route::get('cabinet', [SampleController::class, 'cabinet'])->name('cabinet');

    Route::post('add-profile-photo', [SampleController::class, 'addPhoto'])->name('addProfilePhoto');

    Route::post('change-email', [SampleController::class, 'changeEmail'])->name('changeEmail');

    Route::post('change-name', [SampleController::class, 'changeName'])->name('changeName');
});

Route::resource('dashboard',PostController::class);

Route::post('dashboard', [PostController::class, 'store'])->name('post.store');
Route::post('update', [PostController::class, 'update'])->name('post.update');
Route::post('addComment', [CommentController::class, 'store'])->name('comment.addComment');
Route::post('editComment', [CommentController::class, 'update'])->name('comment.editComment');
Route::delete('dashboard/{id}', [PostController::class, 'destroy'])->name('post.destroy');
Route::delete('dashboard/comment/{id}', [CommentController::class, 'destroy'])->name('comment.destroy');

Route::get('registration', [DropdownController::class, 'index'])->name('registration');
Route::post('api/fetch-states', [DropdownController::class, 'fetchState']);
Route::post('api/fetch-cities', [DropdownController::class, 'fetchCity']);

Route::get('/forget-password', [ForgotPasswordController::class, 'getEmail'])->name('getEmail');
Route::post('/forget-password', [ForgotPasswordController::class, 'postEmail'])->name('postEmail');

Route::get('{token}/reset-password', [ResetPasswordController::class, 'getPassword'])->name('getPassword');
Route::post('/reset-password', [ResetPasswordController::class, 'updatePassword'])->name('updatePassword');

Route::get('weather', function () {
    $location = 'Kremenchuk';
    $key = config('services.openweather.key');

    $response = Http::get("https://api.openweathermap.org/data/2.5/weather?q={$location}&appid={$key}&units=metric");
    return view('weather.weather', [
        'currentWeather' => $response->json(),
    ]);
});

Route::get('/tweets', TwitterController::class)->name('tweets');

Route::get('autoria', [ScraperController::class, 'index'])->name('autoria');
Route::post('autoria/fetch-model', [ScraperController::class, 'getModel'])->name('autoria.getModel');
Route::post('autoria', [ScraperController::class, 'scraper'])->name('autoria.car');

Route::post('/reply/store', [CommentController::class, 'replyStore'])->name('reply.add');
