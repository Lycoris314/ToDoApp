<?php
require_once("helper_functions.php");

$msg = "エラーが発生しました";

if (isset($_GET["msg"])) {
    $msg = h($_GET["msg"]);
}

echo $msg;