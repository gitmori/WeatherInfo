<?php
# タイムゾーンの設定
require_once __DIR__ . '/../common/timezone.php';

# CSSを記述するコード内でのヘッダ設定
header('Content-Type: text/css; charset=utf-8');

# 現在のシステム時刻（ゼロなしの時）
$now = date('G');

# システム時刻5:00〜16:59までとそれ以外の時刻で背景色と文字色を切り替える
$bgcol = $now >= 5 && $now < 17 ? '#87ceeb' : '#000033';
$ftcol = $now >= 5 && $now < 17 ? '#000000' : '#ffffff';
?>
body {
  background-color: <?= $bgcol; ?>;
    color: <?= $ftcol; ?>;
    }
