<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    /**
     * お問い合わせ入力フォームを表示
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories','tags'));
    }

    /**
     * お問い合わせの確認画面を表示
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $category = Category::find($validated['category_id']);
        $tagIds = collect($validated['tag_ids'] ?? '');
        $tags = Tag::whereIn('id', $tagIds)->get();

        // 入力値を保持したまま確認画面へ
        return view('contact.confirm', compact('validated','category','tags'));
    }

    /**
     * お問い合わせを新規作成
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $contact = Contact::create($validated);
        
        // タグを追加
        $contact->tags()->attach($validated['tag_ids'] ?? []);

        return redirect()->route('contact.thanks');
    }

    /**
     * サンクスページを表示
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}
