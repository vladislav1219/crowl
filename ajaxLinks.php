<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php");
require_once("functions.php");


if (isset($_GET['nameId'])) {
    $nameId = getSql("SELECT nameId FROM crawls WHERE nameId = ?",[$_GET['nameId']])->fetch()['nameId'];
    $url = getSql("SELECT url FROM `" . $nameId . "`.urls WHERE id = ?",[$_GET['id']])->fetch()['url'];
    if ($nameId) {
        
        if($url=="all") {
            
        } else {
        
        if ($_GET['direction'] == "outgoing") {
            
            $sql = 'SELECT
                        *
                    FROM
                        `' . $nameId . '`.links
                    WHERE
                        (
                            source = ? OR source LIKE ?
                        ) AND(
                            links.target NOT LIKE ? AND links.target != ?
                        )';

        
            $links= getSql($sql,[$url, "%".$url."#%", "%".$url."#%", $url])->fetchAll();
            
        } elseif ($_GET['direction'] == "incoming") {
            
            $sql = 'SELECT
                        *
                    FROM
                        `' . $nameId . '`.links
                    WHERE
                        (
                            target = ? OR target LIKE ?
                        ) AND(
                            links.source NOT LIKE ? AND links.source != ?
                        )';
            
            $links= getSql($sql,[$url, "%".$url."#%", "%".$url."#%", $url])->fetchAll();
        }
    }
            
        header('Content-type: application/json');
        echo(json_encode($links));
        
    }
}


?>
