# COACHTECH お問い合わせ管理
### 概要
本システムは、一般ユーザーが誰でもお問い合わせを送信でき、管理者がその内容を確認・管理するWebシステムです。
#### 主な機能概要
- **一般ユーザー向け機能**:
  - お問い合わせ送信
- **管理者向け機能**:
  - 管理者認証機能（ログイン / ログアウト / 新規登録）
  - お問い合わせ一覧表示（ペジネーション・検索フィルター付き）・詳細確認・削除
  - タグ追加・更新・削除
 
---

### ER図
プロジェクト内のデータベース設計図です。

![ER図](./docs/images/contact-form-app-ER.png)
### 環境構築手順
ターミナルを開き、以下の手順で環境を構築してください。
※Windowsの方へ：WSL（Ubuntu）のターミナルで実行してください（PowerShellでは動きません）。
#### 前提条件
- **Docker Desktop** がインストールされ、起動していること
- **Git** が利用可能であること

#### 1. プロジェクトをクローン
プロジェクトをローカルにダウンロードします。
```bash
git clone https://github.com/kikukawa-hiromi/contact-form-app.git contact-form-app
cd contact-form-app
```
#### 2. Composerパッケージをインストール
Dockerを使用してComposerパッケージをインストールします。
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer create-project laravel/laravel:^10.0 contact-form-app
```
#### 3. 環境変数ファイルを作成
.env.example をコピーして .env ファイルを作成します。
```bash
cp .env.example .env
```
#### 4. Docker環境を起動
コンテナを起動します。初回はイメージのビルドに数分かかる場合があります。
```bash
./vendor/bin/sail up -d
```
#### 5. アプリケーションキーを生成
Laravelの暗号化キーを生成します。
```bash
./vendor/bin/sail artisan key:generate
```
#### 6. データベースの準備
テーブルを作成し、テストデータを投入します。
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```
### 使用技術
- バックエンド: PHP 8.2 / Laravel 10.0
- データベース: MySQL 8.0
- 開発環境: Docker / Laravel Sail
- フロントエンド: Blade, Tailwind CSS, Vite
- コード整形・テスト: Laravel Pint, PHPUnit
- ソース管理: Git, GitHub
### 開発環境URL
- Webアプリケーション: http://localhost
- DB管理 (phpMyAdmin): http://localhost:8080
### 作成者
菊川　裕美