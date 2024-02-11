<?php
require_once("helper_functions.php");

$msg="エラーが発生しました";

if (non_empty($_GET["msg"])) {
    $msg = h($_GET["msg"]);
}

echo $msg;