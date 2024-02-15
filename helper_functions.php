<?php

//mysqlに接続
function connect_db()
{
    require_once("DBInfo.php");
    try {
        $pdo = new PDO(DBInfo::DNS, DBInfo::USER, DBInfo::PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        header("location:error.php?msg={$e->getMessage()}");
        exit();
    }
}


function is_set(string $method, string $name){
    switch($method){
        case "get":
            if(isset($_GET[$name])){
                return $_GET[$name]; 
            }else{   
                echo "パラメータがセットされていません。";
                exit();
            };
        case "post":
            if(isset($_POST[$name])){
                return $_POST[$name]; 
            }else{   
                echo "パラメータがセットされていません。";
                exit();
            };
    }
}

function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, "UTF-8");
}

function bindValues(PDOStatement $stmt, ...$values)
{
    foreach ($values as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
}

//更新系クエリを実行する関数
function upd_sql(string $sql, ...$values)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        bindValues($stmt, ...$values);
        $pdo->beginTransaction();
        $stmt->execute();
        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $pdo = null;
        header("location:error.php?msg={$e->getMessage()}");
        exit();
    }
}

//参照系クエリを実行する関数
function ref_sql(string $sql, ...$values): PDOStatement
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        bindValues($stmt, ...$values);
        $stmt->execute();

    } catch (Exception $e) {
        $pdo = null;
        header("location:error.php?msg={$e->getMessage()}");
        exit();
    }
    return $stmt;
}


//PHP用コンソール
function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}