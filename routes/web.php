<?php

use App\Http\Controllers\AdminController;
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
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');

// お問い合わせ情報の登録
Route::POST('/contacts', [ContactController::class, 'store'])->name('contact.store');

// お問い合わせ送信後のサンクスページを表示
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

// 認証を必要とするページ
Route::middleware('auth')->group(function () {
    // お問い合わせ一覧画面を表示
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    // お問い合わせ詳細画面を表示
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->name('admin.show');
    // お問い合わせを削除
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // タグのCRUDルート
    Route::resource('admin/tags', TagController::class);
});
