<?php 

/**
 *
 * Fire and forget a prepared (sanitized) SQL request, set success status if request successful
 *
 * @param     string $sql The SQL request to prepare, with '?' as variables placeholders that will be replaced when preparing the request.
 * @param      array $params Must be in order with the placeholders in the SQL request.
 *
 */

    function sendSql($sql,$params) {  
    try {
        global $conn;
        global $addStatus;
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $preparedQuery = $conn->prepare($sql);
        $preparedQuery->execute( $params);
        $addStatus = "Success";
        }
    catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
        $addStatus = $e->getMessage();
        }
        
        return ($addStatus);
    }


/**
 *
 * Send an SQL sanitized request, with '?' as variables placeholders, and return query object for further processing (fetch / fetchall / rowcount etc.)
 *
 * @param     string $sql The SQL request to prepare
  * @param      array $params Must be in order with the placeholders in the SQL request.

 *
 */

    function getSql($sql, $params) {
        global $conn;
        $preparedQuery = $conn->prepare($sql);
        $preparedQuery->execute($params);
        $preparedQuery -> setFetchMode(PDO::FETCH_ASSOC);
        return($preparedQuery);
    }

/**
 *
 * Returns a badge according to the type requested and the content provided.
 *
 * @param     string $type Can be light, info, primary, danger, warning
 * @param      string $content 
 *
 */
     function badge($type, $content) {
        return '<span class="badge badge-'. $type .'">'. $content .'</span>';
    }


/**
 *
 * Returns an alert according to the type and content provided.
 *
 * @param     string $type Can be light, info, primary, danger, warning
 * @param     string $message
 *
 */
    function alert($type,$message) {
         return '<div style="margin-bottom:20px;" class="alert alert-'. $type .'">
                     '. $message .'
                  </div>';
        }

/**
 *
 * Returns an alert according to the type and content provided.
 *
 * @param     string $database Is the name of the database to connect to.
 *
 */
    function crawlDbConnection($database) {
        $host = 'localhost';
        $dbname = $database;
        $username = 'vladislav';
        $password = 'Pakatopopopopo*4';
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        global $conn;
        return($conn);
    } catch (PDOException $pe) {
        die("Could not connect to the database $dbname :" . $pe->getMessage());
}
}

/**
 *
 * Insert fromv key -> value array
 *
 * @param     array $key_value_array Is the name of the database to connect to.
 * @param     string $table Is the name of the tabe to insert within.
 *
 */

function insertArray($table, $key_value_array) {
    global $conn;
    $sql = 'INSERT INTO ' . $table . ' '; 
    $columns = '(';
    $values = '(';
    foreach ($key_value_array as $k => $v) {
        $columns .= '`' . $k . '`, ';
        $values .= "'" . $v . "', ";
    }
    $columns = rtrim($columns, ', ') . ')';
    $values = rtrim($values, ', ') . ')';
    $sql .= $columns . ' VALUES ' . $values;
    var_dump($sql);
    $q = $conn->prepare($sql);
    $q->execute();
}

?>
