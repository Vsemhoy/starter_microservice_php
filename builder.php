<?php
// 1 - find all files from "objects" and form namespaces
// 2 - read source code in "source/index_source.php"
// 3 - write "index.php":
// 3.1 - write includes of each object
// 3.2 - write namespaces use
// 3.3 - write script from source

$loc = __DIR__;
$dirs = ["objects", "modules", "server", "database"];



?>

<style>
body {
    background: #3f0d64;
    color: white;
}
</style>

<?php

$includes = "";
$result = "<?php\n";
$uses   = "";

$result .= "// This file generated by {calling} builder.php from source code! Do not change this file, change source code!\n";
foreach ($dirs AS $dir){

    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != ".." && $file != "ObjectInterface.php"){
                    $name = explode(".", $file)[0];
                    $includes .= "  require_once('" . $loc . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file . "');\n";
                    $uses .= "  use " . $dir . "\\" .$name.";\n";
                }
            }
            closedir($dh);
        }
    }
}


$result .= $includes;
$result .= "\n";
$result .= $uses;
$result .= "?>\n\n";

$indexSrc = file_get_contents($loc . DIRECTORY_SEPARATOR . "source" . DIRECTORY_SEPARATOR . "index_source.php");

$result .= $indexSrc;

//$indexFile = fopen($loc . DIRECTORY_SEPARATOR . "index.php", "r") or die("Unable to open file!");
//fclose($indexFile);
    if (!file_put_contents($loc . DIRECTORY_SEPARATOR . "index.php",
    $result))
    {
        echo "Wrong data!";
    } else {
        echo "Build ok!";
    }
    
?>