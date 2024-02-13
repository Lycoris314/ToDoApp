<?php
require_once("helper_functions.php");

if (non_empty($_GET["id"])) {
    $id = $_GET["id"];
} else {
    header("location:error.php");
    exit();
}

$pdo = connect_db();
$stmt = ref_sql("select done from task where id=?", $id);
$done = $stmt->fetchColumn();
$done = 1 - $done;

upd_sql("update task set done={$done} where id=?", $id);
$pdo = null;

echo "success";

