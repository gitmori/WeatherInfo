<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/scraping.php';

/**
 * セッションが存在しなければセッション開始
 *エラー制御演算子
 */
@!isset($_SESSION) ? session_start() : null;

# リファラ情報を取得する関数
function pre_page() {

    # リファラ取得
    $ref_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

    # リファラをスクレイピングする関数をコール
    $doc = scraping($ref_url);

    # タイトル（タグ込み）を取得
    $ref_title = $doc['title'];

    # タグを除去
    $ref_title = $ref_title->text();

    # 戻り値はリファラとタイトル
    return [$ref_url, $ref_title];
}

# リダイレクトページを表示する関数（リダイレクト時間の初期値は2秒）
function redirect($msg, $url = 'referer', $title = 'referer', $sec = 2) {
    if ($url == 'referer' && $title == 'referer'):
        list($ref_url, $ref_title) = pre_page();
        $url = $ref_url;
        $title = $ref_title;
    else:
        # 何もしない
    endif;
?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $sec; ?>;URL=<?= $url; ?>">
    <title>リダイレクト</title>
  </head>
  <body>
    <div><?= $msg; ?></div>
    <div><?= $sec; ?>秒後に<a href="<?= $url; ?>"><?= $title; ?></a>へ移動します</div>
  </body>
</html>
<?php
    exit();
}
?>