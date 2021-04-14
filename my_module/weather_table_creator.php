<?php
require_once __DIR__ . '/../common/db_connect.php';

# テーブルを生成する関数
function weather_table_creator() {

    # DB名
    $dbname = 'weather';

    # テーブルを生成するSQL
    $sql  = "create table if not exists " . $dbname . ".weather_data(num int(10) not null auto_increment, ";
    $sql .= "datetime varchar(20), day varchar(2), city varchar(10), weather varchar(10), description varchar(50), ";
    $sql .= "icon varchar(5), temp int(4), feels_like int(4), temp_max int(4), temp_min int(4), humidity int(3), ";
    $sql .= "pressure int(5), speed float, deg varchar(10), ";
    $sql .= "updated datetime not null default current_timestamp on update current_timestamp, primary key(num))";

    db_connect($dbname, $sql);
 }

# 上記関数を実行
weather_table_creator();