<?php
require_once("helper_functions.php");

$id =is_set("get","id");

$pdo = connect_db();
$stmt = ref_sql("select done from task where id=?", $id);
$done = $stmt->fetchColumn();
$done = 1 - $done;

upd_sql("update task set done={$done} where id=?", $id);
$pdo = null;

echo "success";

