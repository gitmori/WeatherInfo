<?php
require_once __DIR__ . '/../config/opn_wm_api_key.php';

# ネットワークエラー画面を表示する関数（5秒間隔でリロード再接続を試みる）
function networkError($msg, $sec = 5) {
?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $sec; ?>">
    <title><?= $msg; ?></title>
  </head>
  <body>
    <div><?= $msg; ?></div>
    <div><?= $sec; ?>秒後に再接続します</div>
  </body>
</html>
<?php
    exit();
}

/**
 * OpenWeatherMapのAPIを利用したJSONを取得し連想配列にデコードする関数
 * 引数は表示形式と都市コード
 */
function getAssociative($api_type, $area_id) {

    # グローバルIPアドレスを調べるコマンド
    $command = 'curl -s ifconfig.me | grep \'^[21]\'';

    # 上記コマンドからレスポンス値を取得（グローバルIPアドレス）
    exec($command, $response);

    # グローバルIPアドレスが返ってこないの場合のエラーメッセージ
    $msg = 'Network Error..';

    # グローバルIPアドレスが返ってこないの場合はエラー画面を表示する
    !isset($response[0]) ? networkError($msg) : null;

    /** API Keyを外部ファイルから呼び出し（.gitignore済）
      * OpenWeatherMapよりAPI Keyを取得して設定することも可能
      */
    $api_key = opn_wm_api_key();

    # ベースURL
    $api_base = 'https://api.openweathermap.org/data/2.5/';

    # ベースURLのパラメータ
    $api_parm = '?id=' . $area_id . '&appid=' . $api_key;

    # URLの連結
    $api_url = $api_base . $api_type . $api_parm;

    # URLの読み込み
    $api_url = file_get_contents($api_url);

    # JSONをデコード（第二引数をtrueにすることでJSONを連想配列にする）
    $associative = json_decode($api_url, true);

    # 戻り値はデコードされた連想配列
    return $associative;
}

# 下記関数への引数（エラー制御演算子付き）
@$response = getAssociative($api_type, $area_id);

# 上記関数から取得した最新のUTCから指定した書式に変換する関数
function getDatetime($response, $format) {

    # 最新のUNIX時刻（UTC）
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
    $date = $datetime->format($format);

    return $date;
}

# 日本語表記の曜日を取得する関数
function getWeek($date) {

    # $date2をUNIX時刻に変換
    $day = strtotime($date);

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
}

# 都市名を日本語化する関数（新たな都市は都度追加）
function getCityName($city) {

    # 都市名の連想配列
    $list1 = ['Sapporo' => '札幌市', 'Tokyo' => '東京都', 'Yokohama' => '横浜市', 'Osaka' => '大阪市'];
    $list2 = ['Nagoya' => '名古屋市', 'Onomichi' => '尾道市', 'Fukuoka' => '福岡市', 'Naha' => '那覇市'];
    $list3 = ['Kitami' => '北見市', 'Chitose' => '千歳市', 'Hakodate' => '函館市'];

    # 上記配列をマージ
    $lists = array_merge($list1, $list2, $list3);

    # 連想配列の長さマイナス1
    $length = count($lists) - 1;

    # 連想配列のキー
    $keys = array_keys($lists);

    # 連想配列の値
    $values = array_values($lists);

    # 都市名の日本語表示の分岐
    for ($i = 0; $i <= $length; $i++):
        if($city == $keys[$i]):
            return  $values[$i];
        endif;
    endfor;
}

# 現在の天気を翻訳する関数
function getWeatherTranslation($weather) {

    # 翻訳用天気名の連想配列（新たな英語表記の天気名が表示された場合は都度追加）
    $list = ['Clear' => '晴れ', 'Clouds' => 'くもり', 'Rain' => '雨', 'Snow' => '雪', 'Mist' => '霧'];

    # 連想配列の長さマイナス1
    $length = count($list) - 1;

    # 連想配列のキー
    $keys = array_keys($list);

    # 連想配列の値
    $values = array_values($list);

    # 天気名の日本語化
    for ($i = 0; $i <= $length; $i++):
        if ($weather == $keys[$i]):
            return $values[$i];
        endif;
    endfor;
}

# 天気詳細を翻訳する関数
function getDescriptionTranslation($description) {
    switch ($description):
        case 'overcast clouds':
            return 'どんよりした雲<br class="nosp">（雲85~100%）';
            break;
        case 'broken clouds':
            return '千切れ雲<br class="nosp">（雲51~84%）';
            break;
        case 'scattered clouds':
            return '散らばった雲<br class="nosp">（雲25~50%）';
            break;
        case 'few clouds':
            return '少ない雲<br class="nosp">（雲11~25%）';
            break;
        case 'light rain':
            return '小雨';
            break;
        case 'moderate rain':
            return '雨';
            break;
        case 'heavy intensity rain':
            return '大雨';
            break;
        case 'very heavy rain':
            return '激しい大雨';
            break;
        case 'clear sky':
            return '快晴';
            break;
        case 'shower rain':
            return 'にわか雨';
            break;
        case 'light intensity shower rain':
            return '小雨のにわか雨';
            break;
        case 'heavy intensity shower rain':
            return '大雨のにわか雨';
            break;
        case 'thunderstorm':
            return '雷雨';
            break;
        case 'snow':
            return '雪';
            break;
        case 'light snow':
            return '小雪';
            break;
        case 'light shower snow':
            return '弱いにわか雪';
            break;
        case 'mist':
            return '靄';
            break;
        case 'tornado':
            return '強風';
            break;
        default:
            return $description;
    endswitch;
}

# 16方位により風向を取得する関数
function getDirection($deg) {
    if ($deg >= 0 && $deg <= 10):
        return '北';
    elseif ($deg >= 11 && $deg <= 29):
        return '北北東';
    elseif ($deg >= 30 && $deg <= 60):
        return '北東';
    elseif ($deg >= 61 && $deg <= 79):
        return '東北東';
    elseif ($deg >= 80 && $deg <= 100):
        return '東';
    elseif ($deg >= 101 && $deg <= 119):
        return '東南東';
    elseif ($deg >= 120 && $deg <= 150):
        return '南東';
    elseif ($deg >= 151 && $deg <= 169):
        return '南南東';
    elseif ($deg >= 170 && $deg <= 190):
        return '南';
    elseif ($deg >= 191 && $deg <= 209):
        return '南南西';
    elseif ($deg >= 210 && $deg <= 240):
        return '南西';
    elseif ($deg >= 241 && $deg <= 259):
        return '西南西';
    elseif ($deg >= 260 && $deg <= 280):
        return '西';
    elseif ($deg >= 281 && $deg <= 299):
        return '西北西';
    elseif ($deg >= 300 && $deg <= 330):
        return '北西';
    elseif ($deg >= 331 && $deg <= 349):
        return '北北西';
    elseif ($deg >= 350 && $deg <= 360):
        return '北';
    endif;
}