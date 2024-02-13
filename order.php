<?php

if(isset($_GET["order"])){
    $order= $_GET["order"];

    setcookie("order",$order,time()+60*60*24*7); //一週間保存
}
header("location:main.php");