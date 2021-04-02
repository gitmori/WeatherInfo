<?php
require_once __DIR__ . '/../config/opn_wm_api_key.php';

/**
 * OpenWeatherMapのAPIを利用したJSONを取得する関数
 * 引数は表示形式と都市ID
 */
function getWeather($api_type, $area_id) {

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

    # JSON文字列をデコード（第二引数をtrueにすることでJSONを連想配列にする）
    $json = json_decode($api_url, true);

    # 戻り値はJSON
    return $json;
}

# 天気詳細を翻訳する関数
function getTranslation($arg) {
    switch ($arg):
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
            return $arg;
    endswitch;
}

# 16方位により風向を取得する関数
function getDirection($arg) {
    if ($arg >= 0 && $arg <= 10):
        return '北';
    elseif ($arg >= 11 && $arg <= 29):
        return '北北東';
    elseif ($arg >= 30 && $arg <= 60):
        return '北東';
    elseif ($arg >= 61 && $arg <= 79):
        return '東北東';
    elseif ($arg >= 80 && $arg <= 100):
        return '東';
    elseif ($arg >= 101 && $arg <= 119):
        return '東南東';
    elseif ($arg >= 120 && $arg <= 150):
        return '南東';
    elseif ($arg >= 151 && $arg <= 169):
        return '南南東';
    elseif ($arg >= 170 && $arg <= 190):
        return '南';
    elseif ($arg >= 191 && $arg <= 209):
        return '南南西';
    elseif ($arg >= 210 && $arg <= 240):
        return '南西';
    elseif ($arg >= 241 && $arg <= 259):
        return '西南西';
    elseif ($arg >= 260 && $arg <= 280):
        return '西';
    elseif ($arg >= 281 && $arg <= 299):
        return '西北西';
    elseif ($arg >= 300 && $arg <= 330):
        return '北西';
    elseif ($arg >= 331 && $arg <= 349):
        return '北北西';
    elseif ($arg >= 350 && $arg <= 360):
        return '北';
    endif;
}