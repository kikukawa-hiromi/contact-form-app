<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 問い合わせ情報のダミーデータを20件作成
        Contact::factory()->count(20)->create();

        // 既存のタグIDを全件取得
        $tagIds = Tag::pluck('id');

        // 作成した問い合わせ情報のデータをループし、ランダムなタグをattach
        Contact::all()->each(function ($contact) use ($tagIds) {
            // 1〜3個のタグIDをランダムに抽出して中間テーブルへ紐付け
            $contact->tags()->attach(
                $tagIds->random(rand(1, 3))
            );
        });
    }
}
