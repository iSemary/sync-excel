<?php

/**
 * Undocumented function
 *
 * @param [type] $dbName
 * @param [type] $dbHost
 * @param [type] $dbPort
 * @param [type] $dbUsername
 * @param [type] $dbPassword
 * @return void
 */
function connect($dbName, $dbHost, $dbPort, $dbUsername, $dbPassword) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

        try {
            $db = new PDO('mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName, $dbUsername, $dbPassword, $options);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $response['message'] = 'Database connected successfully!';
            $response['success'] = true;
            $response['data'] = $db;
        } catch (PDOException $e) {
            $response['message'] = 'Failed To Connect ' . $e->getMessage();
            $response['success'] = false;
        }
    } else {
        $response['message'] = $_SERVER['REQUEST_METHOD'] . ' Request not available.';
        $response['success'] = false;
    }
    header('Content-Type: application/json; charset=utf-8');
    return $response;
}

