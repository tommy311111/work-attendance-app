# kintai-app

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:tommy311111/flea-market-app.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. .env.example ファイルをコピーして .env ファイルを作成

```bash
cp .env.example .env
```

4. .env ファイルの一部を以下のように編集
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
php artisan migrate
```

7. シーディングの実行
``` bash
php artisan db:seed
```


## メール認証とMailtrap設定

本アプリでは、会員登録後にメール認証を行います。開発環境では [Mailtrap](https://mailtrap.io/) を使用して、送信メールの確認を行います。

### Mailtrapの使用方法

1. [Mailtrap](https://mailtrap.io/) にサインアップ（無料プランで可）
2. ダッシュボードから Inbox を作成
3. 「SMTP Settings」→「Laravel」を選択し、右上の "Copy" ボタンで設定をすべてコピーしてください。
4. コピーした内容を .env に貼り付け、 MAIL_FROM_ADDRESS と MAIL_FROM_NAME を書き加えてください。
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=あなたのMailtrapユーザー名
MAIL_PASSWORD=あなたのMailtrapパスワード
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Flea Market App"
```
 パスワードは一部しか表示されないため、「Copy」ボタンで全体をコピーしないと正しく取得できません。

## テスト環境のセットアップ手順

このプロジェクトでは、テスト実行に専用のテスト用データベース `demo_test` を使用します。以下の手順に従って準備をしてください。

---

### 🔹 1. テスト用データベースの作成（MySQL）

```bash
docker-compose exec mysql bash
mysql -u root -p
```
※ パスワードは docker-compose.yml 内の MYSQL_ROOT_PASSWORD に記載されている値です。
```sql
CREATE DATABASE demo_test;
SHOW DATABASES;
```
demo_test が一覧に表示されれば作成完了です。

### 🔹 2. テスト用 .env.testing ファイルの作成
```bash
docker-compose exec php bash
cp .env.testing.example .env.testing
```
`.env.testing`ファイルの以下の2項目だけ、自分のMailtrap情報に書き換えてください。
```env
MAIL_USERNAME=あなたのMailtrapユーザー名
MAIL_PASSWORD=あなたのMailtrapパスワード
```
その他のメール設定（MAIL_HOST や MAIL_PORT など）は .env.testing.example にすでに記載されています。

### 🔹 3. テスト環境用のセットアップ
```bash
php artisan key:generate --env=testing
php artisan config:clear
php artisan migrate --env=testing
```
### 🔹 4. テストの実行方法
以下のコマンドで、Feature テストを実行できます
```bash
php artisan test --env=testing
```
補足:
テストでは demo_test データベースが使用されます。本番・開発用DBとは異なります。


## テストユーザー情報（初期データ）

開発環境またはテスト環境でログイン確認するためのテストユーザーがあらかじめ用意されています。 ※ 全ユーザーのパスワードは共通で `password` です
| 名前    | メールアドレス                                           | パスワード    | 出品         | 購入 | コメント | いいね | 役割               |
| ----- | ------------------------------------------------- | -------- | ---------- | -- | ---- | --- | ---------------- |
| 佐藤 美咲 | [misaki@example.com](mailto:misaki@example.com)   | password | 6件（未販売）    | なし | 0件   | 0件  | 出品のみを行う出品専用ユーザー  |
| 鈴木 大輔 | [daisuke@example.com](mailto:daisuke@example.com) | password | 1件（売却）     | 1件 | 0件   | 0件  | 出品と購入を1回ずつ経験済み   |
| 高橋 結衣 | [yui@example.com](mailto:yui@example.com)         | password | 3件（うち2つ売却） | -  | 7件   | 7件  | アクティブなコメント＆いいね担当 |
| 田中 直人 | [naoto@example.com](mailto:naoto@example.com)     | password | 0件         | 2件 | 5件   | 6件  | 購入＋コメント／いいね担当    |
| 伊藤 紗季 | [saki@example.com](mailto:saki@example.com)       | password | 0件         | 0件 | 2件   | 4件  | 閲覧ユーザー（軽めのアクション） |

> セキュリティ上、本番環境には **このテストユーザーを残さないようにしてください**。



## 使用技術(実行環境)
- PHP7.4.9
- Laravel8.83.3
- MySQL8.0.26


## テーブル設計


## ER図


## 主な画面構成（詳細は別添のExcelを参照）

- 商品一覧（トップページ）
- 商品詳細
- 会員登録／ログイン
- メール認証
- 商品出品
- プロフィール
- プロフィール編集
- 商品購入
- 送付先住所変更
>（全10画面）

## URL
- 開発環境：http://localhost
- phpMyAdmin：http://localhost:8080/
