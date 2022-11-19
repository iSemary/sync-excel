<?php

require './connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to database
    $connectDB = connect($_POST['db_name'], $_POST['db_host'], $_POST['db_port'], $_POST['db_username'], $_POST['db_password']);
    if ($connectDB['success']) {
        $db = $connectDB['data'];

        if ($_POST['type'] == 'tables') {
            $tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '". $_POST['db_name'] . "'");
            $tables = $tables->fetchAll(PDO::FETCH_ASSOC);
            if ($tables) {
                $response['message'] = 'Available tables.';
                $response['data'] = $tables;
                $response['success'] = true;
            } else {
                $response['message'] = 'Tables not available.';
                $response['success'] = false;
            }
        } elseif ($_POST['type'] == 'columns') {

            $tables = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$_POST['db_name']."' AND TABLE_NAME = '".$_POST['db_table']."'");
            $tables = $tables->fetchAll(PDO::FETCH_ASSOC);
            if ($tables) {
                $response['message'] = 'Available columns.';
                $response['data'] = $tables;
                $response['success'] = true;
            } else {
                $response['message'] = 'Columns not available.';
                $response['success'] = false;
            }
        }


        echo json_encode($response);
    } else {
        echo json_encode($connectDB);
    }
} else {
    $response['message'] = $_SERVER['REQUEST_METHOD'] . ' Request not available.';
    $response['success'] = false;
}
