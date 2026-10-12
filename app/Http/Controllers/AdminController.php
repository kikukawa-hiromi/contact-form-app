<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    /**
     * お問い合わせ一覧を表示
     */
    public function index(IndexContactRequest $request)
    {
        $categories = Category::all();
        $tags = Tag::all();
        $contacts = Contact::with('tags');

        // キーワードでの絞り込み（部分一致）
        if ($request->filled('keyword')) {
            $contacts = $contacts->where(function ($query) use ($request) {
                    $query->where('first_name', 'like',  "%{$request->keyword}%")
                        ->orWhere('last_name', 'like',  "%{$request->keyword}%")
                        ->orWhere('email', 'like',  "%{$request->keyword}%");
                });
        }
        
        // 性別での絞り込み
        if ($request->filled('gender') && $request->gender != 0) {
            $contacts = $contacts->where('gender', $request->gender);
        }

        // お問い合わせの種類での絞り込み
        if ($request->filled('category_id')) {
            $contacts = $contacts->where('category_id', $request->category_id);
        }
        // 登録日での絞り込み
        if ($request->filled('date')) {
            $contacts = $contacts->whereDate('created_at', $request->date);
        }

        $contacts = $contacts
                    ->latest() // 新しい順に並べ替え
                    ->paginate(7) // ページネーション（1ページに7件）
                    ->withQueryString();

        return view('admin.index', compact('contacts','categories','tags'));
    }

    /**
     * お問い合わせ詳細を表示
     */
    public function show(Contact $contact)
    {
        $contact->load('category');

        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせを削除
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
