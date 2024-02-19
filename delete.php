<?php
require_once("helper_functions.php");

$id = is_set("get","id");

$pdo = connect_db();
upd_sql($pdo,"delete from task where id=?", $id);
$pdo = null;
echo "success";

