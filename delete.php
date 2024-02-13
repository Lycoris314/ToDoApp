<?php
require_once("helper_functions.php");

if (non_empty($_GET["id"])) {
    $id = $_GET["id"];
} else {
    exit();
}


$pdo = connect_db();
upd_sql("delete from task where id=?", $id);
$pdo = null;
echo "success";

