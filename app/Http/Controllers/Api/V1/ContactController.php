<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * お問い合わせ一覧を取得
     */
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        // リクエストから必要な検索パラメーターのみ取得
        $filters = $request->only(['keyword', 'gender', 'category_id', 'date']);

        // スコープ呼びで全検索条件が適用
        $contacts = Contact::filter($filters)
                        ->with(['category','tags'])
                        ->latest()
                        ->paginate($request->input('per_page'));

        return ContactResource::collection($contacts);
    }

    /**
     * お問い合わせを登録
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = Contact::create($validated);

        // タグを追加
        $contact->tags()->attach($validated['tag_ids'] ?? []);
        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * お問い合わせ詳細を取得
     */
    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        // お問い合わせが見つからない場合
        if (!$contact) {
            return response()->json([
                'message' => 'お問い合わせが見つかりませんでした。'
            ], Response::HTTP_NOT_FOUND);
        }

        return new ContactResource($contact);
    }

    /**
     * お問い合わせを更新
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $validated = $request->validated();

        $contact->update($validated);

        // お問い合わせが見つからない場合
        if (!$contact) {
            return response()->json([
                'message' => 'お問い合わせが見つかりませんでした。'
            ], Response::HTTP_NOT_FOUND);
        }

        if (array_key_exists('tag_ids', $validated)) {
            $contact->tags()->sync($validated['tag_ids'] ?? []);
        }

        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * お問い合わせを削除
     */
    public function destroy(Contact $contact): JsonResponse
    {
        // お問い合わせが見つからない場合
        if (!$contact) {
            return response()->json([
                'message' => 'お問い合わせが見つかりませんでした。'
            ], Response::HTTP_NOT_FOUND);
        }

        $contact->delete();

        return response()->json(null, 204);
    }
}
