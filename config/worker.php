<?php

require './connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to database
    $connectDB = connect($_POST['db_name'], $_POST['db_host'], $_POST['db_port'], $_POST['db_username'], $_POST['db_password']);
    if ($connectDB['success']) {
        $db = $connectDB['data'];

        if ($_POST['type'] == 'tables') {
            $tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . $_POST['db_name'] . "'");
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
            $tables = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $_POST['db_name'] . "' AND TABLE_NAME = '" . $_POST['db_table'] . "'");
            $tables = $tables->fetchAll(PDO::FETCH_ASSOC);
            if ($tables) {
                $response['message'] = 'Available columns.';
                $response['data'] = $tables;
                $response['success'] = true;
            } else {
                $response['message'] = 'Columns not available.';
                $response['success'] = false;
            }
        } elseif ($_POST['type'] == 'execute') {
            $flowJson = json_decode($_POST['flow_json'], true);

            /**
             * ['ai','timestamp','null', '0']
             * ['id','A', ...]
             * ['A1','B1', ...]
             */
            $defaultsArr = [];
            $dbArr = [];
            $excelArr = [];


            /**
             * Associate data from json file into arrays 
             */
            foreach ($flowJson['operators'] as $index => $operator) {
                // Get types to excel array
                if ($index == 'operator1') {
                    foreach ($operator['properties']['outputs'] as $i => $output) {
                        $excelArr[] = $output['label'];
                    }
                }
                // Get types to db array
                if ($index == 'operator2') {
                    foreach ($operator['properties']['inputs'] as $i => $output) {
                        $dbArr[] = $output['label'];
                    }
                }
                // Get types to defaults array
                if ($index == 'operator3') {
                    foreach ($operator['properties']['outputs'] as $i => $output) {
                        $defaultsArr[] = $output['type'];
                    }
                }
            }


            /**
             * ['excelCol' => 'dbCol', 'defaultCol' => 'dbCol']
             */
            $linkedArr = [];

            /**
             * Define the linked flow
             */
            foreach ($flowJson['links'] as $key => $link) {
                die(var_dump($link));
            }


             die(var_dump($linkedArr));
        }


        echo json_encode($response);
    } else {
        echo json_encode($connectDB);
    }
} else {
    $response['message'] = $_SERVER['REQUEST_METHOD'] . ' request not available.';
    $response['success'] = false;
}
