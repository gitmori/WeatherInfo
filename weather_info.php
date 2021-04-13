<?php
require_once __DIR__ . '/my_module/openweathermap.php';
require_once __DIR__ . '/common/header.php';
require_once __DIR__ . '/common/db_connect.php';
require_once __DIR__ . '/common/timezone.php';

# ページタイトル
$title = '気象情報';

# 自動更新間隔（10分とする）
$reload_interval = 10 * 60;

# getWeather関数（表示形式は現在，都市IDは北見市）
$response = getWeather('weather', 2129537);

#$response = getWeather('weather', 2130741);
#$response = getWeather('weather', 2128295);
#$response = getWeather('weather', 2130452);
#$response = getWeather('weather', 1850147);

# 現在のUNIX時刻（UTC）
$utc = $response['dt'];

# DateTimeZoneオブジェクトのインスタンスを生成しJSTを設定
$jst = new DateTimeZone('Asia/Tokyo');

/**
 * DateTimeオブジェクトのインスタンスを生成
 * UNIX時刻を日時に変換
 */
$datetime = new DateTime();

# UTCをJSTに変換
$datetime->setTimestamp($utc)->setTimeZone($jst);

# 書式設定し年月日を取得
$date = $datetime->format('Y年m月d日');

# 上記より得られた年月日より曜日を取得する関数
function getWeek($date) {

    # 不要文字を除去するための連想配列
    $replace = ['年' => '', '月' => '', '日' => ''];

    # 曜日を取得するために変数$dateの不要文字を除去
    $day = str_replace(array_keys($replace), array_values($replace), $date);

    # 上記より得られた年月日をUNIX時刻に変換
    $day = strtotime($day);

    # 曜日の配列番号を取得
    $day = date('w', $day);

    # 曜日の配列
    $week = ['日', '月', '火', '水', '木', '金', '土'];

    # 連想配列の長さマイナス1
    $length = count($week) - 1;

    # 曜日の日本語表示の分岐
    for ($i = 0; $i <= $length; $i++):
        if ($day == $i):
            return $week[$i];
        endif;
    endfor;

    # null処理
    $week = null;
    $length = null;
}

# 曜日
$day = getWeek($date);

# 時刻
$time = $datetime->format('H:i');

# 都市名（英語）
$city = $response['name'];

# 都市名を日本語化する関数
function getCityName($city) {

    # 都市名の連想配列
    $list = ['Kitami' => '北見市', 'Abashiri' => '網走市', 'Sapporo' => '札幌市', 'Chitose' => '千歳市', 'Tokyo' => '東京都'];

    # 連想配列の長さマイナス1
    $length = count($list) - 1;

    # 連想配列のキー
    $keys = array_keys($list);

    # 連想配列の値
    $values = array_values($list);

    # 都市名の日本語表示の分岐
    for ($i = 0; $i <= $length; $i++):
        if ($city == $keys[$i]):
            return $values[$i];
        endif;
    endfor;

    $list = null;
    $length = null;
    $keys = null;
    $values = null;
}

# 都市名（日本語）
$city = getCityName($city);

# 天気情報の配列
$weather_data = $response['weather'];

# 天気名の連想配列（新たな英語表記の天気名が表示された場合は都度追加）
$list = ['Clear' => '晴れ', 'Clouds' => 'くもり', 'Rain' => '雨', 'Snow' => '雪', 'Mist' => '霧'];

# 連想配列の長さマイナス1
$length = count($list) - 1;

# 連想配列のキー
$keys = array_keys($list);

# 連想配列の値
$values = array_values($list);

# 天気名詳細日本語表示
foreach ($weather_data as $item):

    # 天気名の日本語表示の分岐
    for ($i = 0; $i <= $length; $i++):
        if ($item['main'] == $keys[$i]):
            $weather = $values[$i];
        endif;
    endfor;

    $list = null;
    $length = null;
    $keys = null;
    $values = null;

    # 詳細を関数getTranslationを用いて日本語化
    $description = getTranslation($item['description']);

    # 天気アイコン
    $icon = $item['icon'];
    $img = 'https://openweathermap.org/img/wn/' .$icon .'@2x.png';
endforeach;

# 絶対零度
$absolute_zero = -273.15;

# 天気情報の配列
$temp_data = $response['main'];

# 気温，体感温度，最高気温，最低気温（小数点以下四捨五入摂氏表示）
$temp = round($temp_data['temp'] + $absolute_zero);
$feels_like = round($temp_data['feels_like'] + $absolute_zero);
$temp_max = round($temp_data['temp_max'] + $absolute_zero);
$temp_min = round($temp_data['temp_min'] + $absolute_zero);

# 気圧
$pressure = $temp_data['pressure'];

# 湿度
$humidity = $temp_data['humidity'];

# 風の情報の配列
$wind_data = $response['wind'];

# 風速（小数点第二位で四捨五入）
$speed = round($wind_data['speed'], 1);

# 風向（関数getDirectionを用いて日本語化)
$deg = getDirection($wind_data['deg']);

# DB接続処理
try {
    # DB名
    $dbname = 'weather';

    # 最新の気象更新時間を取得するsql
    $sql = "select daytime, updated from weather_data order by updated desc limit 1";

    # DB接続関数
    $stmt = db_connect($dbname, $sql);

    # 最新の気象更新時間を取得
    foreach ($stmt as $value):
        $latest = $value[0];
        $updated = $value[1];
    endforeach;

    #null処理
    $stmt = null;
    $sql = null;

    # 年月日DB用書式
    $date_db = $datetime->format('Y-m-d');

    # 時刻DB用書式
    $time_db = $datetime->format('H:i:s');

    # 日時DB用書式
    $daytime_db = $date_db . ' ' . $time_db;

    # エラー日時（協定世界時JST）
    $unix_error = '1970-01-01 09:00:00';

    /**
     * ページ読み込み時のエラー回避
     * 日時がDBに登録されている最新更新日時と異なる場合
     * かつエラー日時でない場合
     * かつ最新更新日時より前でない場合はDBに登録
     */
    if (($daytime_db != $latest) && ($daytime_db != $unix_error) && ($daytime_db > $latest) && ($daytime_db !=  $updated)):

        # 気象情報をDBに登録するsql（文字列はシングルクォーテーションで囲むこと）
        $sql  = "insert into weather_data(daytime, day, city, weather, description, icon, ";
        $sql .= "temp, feels_like, temp_max, temp_min, humidity, pressure, speed, deg) ";
        $sql .= "values('$daytime_db', '$day', '$city', '$weather', '$description', '$icon', ";
        $sql .= "$temp, $feels_like, $temp_max, $temp_min, $humidity, $pressure, $speed, '$deg')";

        # DB接続関数
        $stmt = db_connect($dbname, $sql);

        # null処理
        $stmt = null;
    endif;

# 例外処理
} catch (PDOException $e) {
    header('Content-type: text/html; charset=utf-8', true, 500);
    $msg =  $e->getMessage() . '-' . $e->getLine();
    redirect($msg);
}
?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $reload_interval; ?>">
    <title><?= $title; ?></title>
  </head>
  <body>
    <h1><?= $title; ?></h1>
    <div><span><?= $date; ?></span><span><?= '（' . $day . '）' ?></span></div>
    <div><span><?= $time; ?></span> 時点の気象情報</div>
    <hr style="border:0;border-top:1px none;">
    <div>都市名　　: <span><?= $city; ?></span></div>
    <div>現在の天気: <span><?= $weather; ?></span></div>
    <div>天気の詳細: <span><?= $description; ?></span></div>
    <img src="<?= $img; ?>">
    <div>現在の気温: <span><?= $temp . '℃'; ?></span></div>
    <div>体感気温　: <span><?= $feels_like . '℃'; ?></span></div>
    <div>最高気温　: <span><?= $temp_max . '℃'; ?></span></div>
    <div>最低気温　: <span><?= $temp_min . '℃'; ?></span></div>
    <div>湿度　　　: <span><?= $humidity . '%'; ?></span></div>
    <div>気圧　　　: <span><?= $pressure . 'hPa'; ?></span></div>
    <div>風速　　　: <span><?= $speed . 'm/s'; ?></span></div>
    <div>風向　　　: <span><?= $deg . 'の風'; ?></span></div>
    <p><a href="link.php">トップページ</a>へ戻る</p>
  </body> 
</html>
