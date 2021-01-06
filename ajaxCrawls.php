<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php");
require_once("functions.php");


if (isset($_GET['delete_nameid'])) {
    
    $id = getSql("SELECT id FROM crawls WHERE nameid= ?",[$_GET['delete_nameid']])->fetch()['id'];
    $nameid = getSql("SELECT nameid FROM crawls WHERE nameid= ?",[$_GET['delete_nameid']])->fetch()['nameid'];
    sendSql("DELETE FROM crawls WHERE  nameid= ?",[$_GET['delete_nameid']]);
    sendSql("DELETE FROM crawl_stats WHERE  crawl_id= ?",[$id]);
    sendSql("DROP DATABASE " .  $nameid,[]);
    
}  elseif (isset($_GET['updatename_by_id'])) {
    
    sendSql("UPDATE `crawls` SET `name` = ? WHERE `id` = ?",[$_GET['new_name'], $_GET['updatename_by_id']]);
}

else { 
$crawls = getSql("SELECT * FROM crawls JOIN crawl_stats ON crawls.id = crawl_stats.crawl_id",[])->fetchAll();
header('Content-type: application/json');
echo(json_encode($crawls));
}

?>