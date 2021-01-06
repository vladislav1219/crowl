<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php");
require_once("functions.php");


if (isset($_GET['crawl_id'])) {
    
    $config = getSql("SELECT * FROM configuration WHERE crawl_id = ?",[$_GET['crawl_id']])->fetchAll();
     header('Content-type: application/json');
     echo(json_encode($config));    
}


?>
