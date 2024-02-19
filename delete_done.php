<?php
require_once("helper_functions.php");

$pdo = connect_db();
upd_sql($pdo ,"delete from task where done=1");
$pdo = null;

header("location:main.php");