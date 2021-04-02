<?php
require_once __DIR__ . '/../common/sql_runner.php';

function weather_table_creator() {

    # DB名
    $dbname = 'weather';

    # テーブルを生成するSQL
    $sql1  = "create table if not exists " . $dbname . ".weather_data(num int(10) not null auto_increment, ";
    $sql1 .= "daytime varchar(20), day varchar(2), city varchar(10), weather varchar(10), description varchar(50), ";
    $sql1 .= "icon varchar(5), temp int(4), feels_like int(4), temp_max int(4), temp_min int(4), humidity int(3), ";
    $sql1 .= "pressure int(5), speed float, deg varchar(10), ";
    $sql1 .= "updated datetime not null default current_timestamp on update current_timestamp, primary key(num))";

    # 初期データをインポートするsql3
    $sql2 = "insert into weather.weather_data(daytime, day, city, weather, description, icon, temp, feels_like, ";
    $sql2 .= "temp_max, temp_min, humidity, pressure, speed, deg) values('1970-01-01 09:00:00', ";
    $sql2 .= "'木', '北見市', 'くもり', '初期データ', '04d', 0, 0, 0, 0, 100, 1000, 1, '南')";

    $sql = [$sql1, $sql2];
    $cnt = count($sql) - 1;

    for ($i = 0; $i <= $cnt; $i++):

        # クラスSqlRunnerのインスタンス作成
        $command[$i] = new SqlRunner($dbname, $sql[$i]);

        $command[$i]->db_control();
        $command[$i] = null;
    endfor;
}

# 上記関数実行
weather_table_creator();