<?php
require_once __DIR__ . '/my_module/openweathermap.php';
require_once __DIR__ . '/common/header.php';
require_once __DIR__ . '/common/db_connect.php';
require_once __DIR__ . '/common/timezone.php';

# ページタイトル
$title = '気象情報';

# ブラウザ自動更新間隔（10分とする）
$reload_interval = 10 * 60;

# 都市名と都市コードの連想配列（新たな都市は都度追加）
$citycode1 = ['札幌' => 2128295, '東京都' => 1850144, '横浜市' => 1848354, '大阪市' => 1853909];
$citycode2 = ['名古屋市' => 1856057, '尾道市' => 1853992, '福岡市' => 1863967, '那覇市' => 1856035];
$citycode3 = ['北見市' => 2129537, '千歳市' => 2130452, '函館市' => 2130188];

# 上記配列をマージ
$citycodes = array_merge($citycode1, $citycode2, $citycode3);

/*
気象情報を取得したい都市名を上記連想配列から選択し入力
今回は北見市とする
*/
$city = $citycodes['北見市'];

# 表示形式と都市コードを指定しJSONを連想配列にデコード
$response = getAssociative('weather', $city);

# 日付の書式1（表示用）
$date1 = getDatetime($response, 'Y年m月d日');

# 日付の書式2（曜日取得用）
$date2 = getDatetime($response, 'Ymd', 'weather');

# 曜日（日本語化）
$day = getWeek($date2);

# 時刻の書式
$time = getDatetime($response, 'H:i');

# 都市名（英語表記）
$city = $response['name'];

# 都市名（日本語化）
$city = getCityName($city);

# 天気情報の配列
$weather_data = $response['weather'];

# 現在の天気（英語表記）
$weather = $weather_data[0]['main'];

# 現在の天気（日本語化）
$weather = getWeatherTranslation($weather);

# 天気の詳細（英語表記）
$description = $weather_data[0]['description'];

# 天気の詳細（日本語化）
$description = getDescriptionTranslation($description);

# 天気アイコン
$icon = $weather_data[0]['icon'];
$img = 'https://openweathermap.org/img/wn/' .$icon .'@2x.png';

# 絶対零度
$absolute_zero = -273.15;

# 温度情報のメイン階層
$temp_data = $response['main'];

# 現在の気温，体感温度，最高気温，最低気温（小数点以下四捨五入かつ摂氏表示）
$temp = round($temp_data['temp'] + $absolute_zero);
$feels_like = round($temp_data['feels_like'] + $absolute_zero);
$temp_max = round($temp_data['temp_max'] + $absolute_zero);
$temp_min = round($temp_data['temp_min'] + $absolute_zero);

# 湿度
$humidity = $temp_data['humidity'];

# 気圧
$pressure = $temp_data['pressure'];

# 風の情報のメイン階層
$wind_data = $response['wind'];

# 風速（小数点第二位で四捨五入）
$speed = round($wind_data['speed'], 1);

# 風向（16方位で日本語化)
$deg = getDirection($wind_data['deg']);

# DB接続処理
try {

    # DB名
    $dbname = 'weather';

    # 最新の気象更新時間と都市名を取得するSQL
    $sql = "select datetime, updated from " . $dbname . ".weather_data order by updated desc limit 1";

    # DB接続関数
    $stmt = db_connect($dbname, $sql);

    # 最新の気象更新日時を取得
    foreach ($stmt as $value):
        $latest = $value[0];
    endforeach;

    #null処理
    $stmt = null;
    $sql = null;

    # 日時（DB用の書式）
    $datetime = getDatetime($response, 'Y-m-d H:i:s');

    # システム日時
    $now = date('Y-m-d H:i:s');

    /**
      *  〜DB登録条件は下記のいずれか〜
      * 1. DBに最新の気象更新日時が存在しない場合
      * 2. 最新の気象更新日時とDBに最新の気象更新日時が異なる場合
      *    かつ最新の気象更新日時がDBに最新の気象更新日時より後の場合
      *    かつ日時（DB用の書式）とシステム日時が一致しない場合
      */
    if (!isset($latest) || (($datetime != $latest) && ($datetime > $latest) && ($datetime != $now))):

        # 気象情報をDBに登録するsql（文字列はシングルクォーテーションで囲むこと）
        $sql  = "insert into " . $dbname . ".weather_data(datetime, day, city, weather, description, icon, ";
        $sql .= "temp, feels_like, temp_max, temp_min, humidity, pressure, speed, deg) ";
        $sql .= "values('$datetime', '$day', '$city', '$weather', '$description', '$icon', ";
        $sql .= "$temp, $feels_like, $temp_max, $temp_min, $humidity, $pressure, $speed, '$deg')";

        # DB接続関数
        $stmt = db_connect($dbname, $sql);

        # null処理
        $stmt = null;
        $sql = null;
    endif;

# 例外処理
} catch (PDOException $e) {
    header('Content-type: text/html; charset=utf-8', true, 500);
    exit('DB接続失敗' . $e->getMessage() . '-' . $e->getLine());
}
?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $reload_interval; ?>">
    <!--
    時刻によって背景色と文字色を切り替えるCSSを設定
    PHPを使用するので拡張子はcssではなくphpにする
    -->
    <link rel="stylesheet" href="css/weather_info_css.php">
    <title><?= $title; ?></title>
  </head>
  <body>
    <font>
      <h1><?= $title; ?></h1>
      <div><span><?= $date1; ?></span><span><?= '（' . $day . '）' ?></span></div>
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
    </font>
  </body> 
</html>
