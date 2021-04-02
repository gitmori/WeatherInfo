<?php
require_once __DIR__ . '/db_connect.php';

/**
 * オブジェクト指向で記述
 *クラスSqlRunnerを宣言
 */
class SqlRunner {

    # プロパティ
    private $dbname;
    private $sql;

    # 上記変数のコンストラクタを記述
    public function __construct($dbname, $sql) {
        $this->dbname = $dbname;
        $this->sql = $sql;
    }

    # メソッド（処理したい流れを関数化したもの）
    public function db_control() {

        # DB接続関数（外部関数）
        $stmt = db_connect($this->dbname, $this->sql);
 
        # null処理
        $stmt = null;
    }
}