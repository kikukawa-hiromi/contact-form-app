<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * タグ編集フォームを表示
     */
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * タグを削除
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect('/admin');
    }

    /**
     * タグを新規作成
     */
    public function store(StoreTagRequest $request)
    {
        Tag::create($request->validated());

        return redirect('/admin');
    }

    /**
     * タグを更新
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return redirect('/admin');
    }
}
