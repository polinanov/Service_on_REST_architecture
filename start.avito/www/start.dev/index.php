<?php
require_once 'classApi.php';
try {
    $api = new UsersApi();
    echo $api->run()."\n";
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}



?>