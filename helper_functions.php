<?php

//リダイレクト用にもう一つ必要かも
function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}

function connect_db()
{
    require_once("DBInfo.php");
    try {
        $pdo = new PDO(DBInfo::DNS, DBInfo::USER, DBInfo::PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        header("location:error.php?msg={$e->getMessage()}");
    }
}

//スーパーグローバル変数がセットされているか調べる
function non_empty(...$super_global)
{
    $result = true;
    foreach ($super_global as $val) {
        $result = $result && isset($val) && !($val === "");
    }
    return $result;
}

function bindValues(PDOStatement $stmt, ...$values)
{
    foreach ($values as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
}

function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, "UTF-8");
}


function total_task()
{
    global $pdo;
    $stmt = $pdo->query("select count(id) from task");
    return $stmt->fetchColumn();
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
    }
}

//参照系クエリを実行する関数
function ref_sql(string $sql, ...$values):PDOStatement
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        bindValues($stmt, ...$values);
        $stmt->execute();
    } catch (Exception $e) {
        $pdo = null;
        header("location:error.php?msg={$e->getMessage()}");
    }
    return $stmt;
}