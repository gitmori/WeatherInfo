<?php
require_once __DIR__ . '/phpQuery-onefile.php';

# スクレイピング専用関数
function scraping($url) {

    # htmlソースを文字列に読み込む
    $html = file_get_contents($url);

    # ソースがxmlの場合はphpQueryが対応しないので正規表現でxml部分を削除
    $html = preg_replace('/^<\?xml.*\?>/', '', $html);

    # phpQueryのドキュメントオブジェクトを生成   
    $doc = phpQuery::newDocument($html);

    # 戻り値
    return $doc;
}