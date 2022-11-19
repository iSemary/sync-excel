<?php

require './connect.php';
require './spreadsheet-reader/php-excel-reader/excel_reader2.php';
require('./spreadsheet-reader/SpreadsheetReader.php');



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
             *  Array Examples
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
             * Example : ['Excel Column' => 'DB column', 'Default Column' => 'DB Column']
             */


            /**
             * Define the linked flow
             */
            $linkedArr = [];
            foreach ($flowJson['links'] as $link) {
                $inputIndex  = substr($link['toConnector'], strpos($link['toConnector'], "_") + 1) - 1;
                $outputIndex = substr($link['fromConnector'], strpos($link['fromConnector'], "_") + 1) - 1;

                $linkedArr[] = [($link['fromOperator'] == 'operator3' ?  $defaultsArr[$outputIndex]  : $excelArr[$outputIndex]) => $dbArr[$inputIndex]];
            }


            /**
             * Upload excel file
             */
            $excelFile = $_FILES['excel_file'];
            $excelFileName = $_FILES['excel_file']['name'];
            $excelFileSize = $_FILES['excel_file']['size'];
            $excelFileTmp = $_FILES['excel_file']['tmp_name'];
            $excelFileType = $_FILES['excel_file']['type'];
            // Custom name
            $excelFile =  'SE' . '_' . rand(0, 1000000000) . $excelFileName;
            // Move to temp folder
            move_uploaded_file($excelFileTmp, "../temp/" . $excelFile);

            // Read Excel file
            $reader = new SpreadsheetReader("../temp/" . $excelFile);
            foreach ($reader as $index => $row) {
                if (isset($_POST['start_row']) && ($index + 1) < $_POST['start_row']) {
                    return;
                }
                if (isset($_POST['end_row']) && ($index + 1) == $_POST['end_row']) {
                    return;
                }

                
                print_r($row);
            }

            /**
             * Delete excel file after the process ends
             */
            if (is_file('../temp/' . $excelFile)) {
                unlink('../temp/' . $excelFile);
            }

            // die(var_dump($linkedArr));
        }


        echo json_encode($response);
    } else {
        echo json_encode($connectDB);
    }
} else {
    $response['message'] = $_SERVER['REQUEST_METHOD'] . ' request not available.';
    $response['success'] = false;
}
