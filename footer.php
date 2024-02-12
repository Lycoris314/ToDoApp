<?php

if(isset($_GET["footer"])){
    $footer= $_GET["footer"];

    setcookie("footer",$footer); 

}

