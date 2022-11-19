<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $dbName = $_POST['db_name'];
    $dbHost = $_POST['db_host'];
    $dbPort = $_POST['db_port'];
    $dbUsername = $_POST['db_username'];
    $dbPassword = $_POST['db_password'];


    $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    );

    try {
        $db = new PDO('mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName, $dbUsername, $dbPassword, $options);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $response['message'] = 'Database connected successfully!';
        $response['success'] = true;
    } catch (PDOException $e) {
        $response['message'] = 'Failed To Connect' . $e->getMessage();
        $response['success'] = false;
    }
} else {
    $response['message'] = $_SERVER['REQUEST_METHOD'] . ' Request not available.';
    $response['success'] = false;
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
