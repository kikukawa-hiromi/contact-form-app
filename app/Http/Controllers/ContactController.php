<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
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

        return view('contact.index', compact('categories', 'tags'));
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
        return view('contact.confirm', compact('validated', 'category', 'tags'));
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

    /**
     * お問い合わせ一覧をエクスポート
     */
    public function export(ExportContactRequest $request)
    {
        $categories = Category::all();
        $tags = Tag::all();

        // リクエストから必要な検索パラメーターのみ取得
        $filters = $request->only(['keyword', 'gender', 'category_id', 'date']);

        // 検索条件の有無
        $hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();

        // スコープ呼びで全検索条件が適用
        $contacts = Contact::filter($filters);

        // 検索条件がある場合のみ適用
        if (! $hasFilters) {
            $contacts = $contacts->latest(); // 作成日時の降順
        }

        $fileName = 'contacts_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->streamDownload(function () use ($contacts) {
            $stream = fopen('php://output', 'w');

            // UTF-8のBOMを出力
            fwrite($stream, "\xEF\xBB\xBF");

            // CSVヘッダー行の書き込み
            fputcsv($stream, ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時']);

            // 検索したデータをチャンク（分割）取得して順次書き込む
            $contacts->chunk(100, function ($contacts) use ($stream) {
                foreach ($contacts as $contact) {
                    fputcsv($stream, [
                        $contact->id,
                        $contact->first_name . ' ' . $contact->last_name,
                        [1 => '男性', 2 => '女性', 3 => 'その他'][$contact->gender],
                        $contact->email,
                        $contact->tel,
                        $contact->address,
                        $contact->building,
                        $contact->category?->content ?? '',
                        $contact->detail,
                        $contact->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($stream);
        }, $fileName, $headers);
    }
}
