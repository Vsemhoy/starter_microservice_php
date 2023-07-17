<?php
namespace objects;

class D implements ObjectInterface
{
    public static function Hello(){
        echo "Hello wooow! D";
        echo "<br>";
    }

    public function Get($name){
        echo "HELLO VASOOON";
    }
}