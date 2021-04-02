 <?php
require_once __DIR__ . '/../config/db_info.php';

# DB設定
function db_config($dbname) {

    # DB接続情報を外部ファイルから呼び出し（.gitignore済）
    $server = db_info()[0];
    $dsn = 'mysql: host=' . $server . '; dbname=' . $dbname . '; charset=utf8mb4; unix_socket=/tmp/mysql.sock';
    $user = db_info()[1];
    $pass = db_info()[2];

    # 戻り値
    return [$dsn, $user, $pass];
}

# DB名とsqlは引数（プレースホルダはデフォルトfalse）
function db_connect($dbname, $sql, $placeholder = 'false') {

    # DB設定情報を外部ファイルから呼び出し（.gitignore済）
    list($dsn, $user, $pass) = db_config($dbname);

    # PDOクラスのインスタンス作成
    $dbh = new PDO($dsn, $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($placeholder == 'true'):
        $stmt = $dbh->prepare($sql);
    elseif ($placeholder == 'false'):
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
    endif;

    # null処理
    $dbh = null;

    # 戻り値は$stmt
    return $stmt;
}