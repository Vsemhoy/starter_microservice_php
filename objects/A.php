<?php
namespace objects;
require_once("ObjectInterface.php");

class A implements ObjectInterface
{
    public static function Hello(){
        echo "Hello wooow! A";
        echo "<br>";
    }

    function Get($name){
        echo $name;
    }
}