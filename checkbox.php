<?php
require_once("helper_functions.php");

if (non_empty($_GET["id"])) {
    $id = $_GET["id"];
    //$scroll_position = $_GET["scroll_position"];
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

//header("location:main.php?scroll_position={$scroll_position}");
header("location:main.php");
