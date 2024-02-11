<?php
require_once("helper_functions.php");

if (non_empty($_GET["id"])) {
    $id = $_GET["id"];
}else{
    header("location:error.php");
}


$pdo = connect_db();
upd_sql("delete from task where id=?", $id);
$pdo = null;
header("location:main.php");

