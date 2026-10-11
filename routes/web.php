<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// トップページ（/）でお問い合わせ入力ページを表示
Route::get('/', [ContactController::class, 'index'])->name('contact.index');

// お問い合わせの入力確認画面を表示
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm');

// お問い合わせ送信後のサンクスページを表示
Route::get('/contacts/thanks', [ContactController::class, 'thanks'])->name('contacts.thanks');

// その他のお問い合わせのCRUDルート
Route::resource('contacts', ContactController::class)->except(['index']);

// 認証を必要とするページ
Route::middleware('auth')->group(function () {
    // タグのCRUDルート
    Route::resource('admin/tags', TagController::class);
});