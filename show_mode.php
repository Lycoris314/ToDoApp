<?php

if(isset($_GET["show_mode"])){
    $show_mode= $_GET["show_mode"];

    setcookie("show_mode",$show_mode,time()+60*60*24*7); //一週間保存;
}
header("location:main.php");