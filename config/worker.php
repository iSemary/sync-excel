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
                $response['message'] = 'Tables added successfully.';
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
                $response['message'] = 'Columns added successfully.';
                $response['data'] = $tables;
                $response['success'] = true;
            } else {
                $response['message'] = 'Columns not available.';
                $response['success'] = false;
            }
        } elseif ($_POST['type'] == 'execute') {
            $flowJson = json_decode($_POST['flow_json'], true);
            $error = false;
            /**
             *  Array Examples
             * ['id','timestamp','null', '0']
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
             * Define the linked flow
             *
             * 
             * Example : ['Excel Column' => 'DB column', 'Default Column' => 'DB Column']
             */

            $linkedArr = [];
            foreach ($flowJson['links'] as $link) {
                $inputIndex  = substr($link['toConnector'], strpos($link['toConnector'], "_") + 1) - 1;
                $outputIndex = substr($link['fromConnector'], strpos($link['fromConnector'], "_") + 1) - 1;

                $linkedArr[($link['fromOperator'] == 'operator3' ?  $defaultsArr[$outputIndex]  : $excelArr[$outputIndex])] = $dbArr[$inputIndex];
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

            $columns = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $_POST['db_name'] . "' AND TABLE_NAME = '" . $_POST['db_table'] . "'");

            $columnsCount = $columns->rowCount();

            /**
             * Generating a comma separated values for columns
             */
            $columns = $columns->fetchAll(PDO::FETCH_ASSOC);
            $columns = array_column($columns, 'COLUMN_NAME');
            $columns = json_encode($columns);
            $columns = str_replace(['[', ']', '"'], ['(', ')', ''], $columns);

            $columnsValues = '(';
            for ($i = 0; $i < $columnsCount; $i++) {
                $columnsValues .= '?' . ($i + 1 == $columnsCount ? ')' : ',');
            }


            /**
             * Deletes all tables row if enabled
             */
            if (isset($_POST['truncate_table']) && $_POST['truncate_table'] == '1') {
                $db->query("TRUNCATE " . $_POST['db_name'] . '.' . $_POST['db_table']);
            }

            /**
             * Looping over each row in excel file
             */
            foreach ($reader as $index => $row) {
                if (isset($_POST['start_row']) && !empty($_POST['start_row']) && ($index + 1) < $_POST['start_row']) {
                    continue;
                }

                if (isset($_POST['end_row']) && !empty($_POST['end_row']) && (($index) >= $_POST['end_row'])) {
                    continue;
                }

                /**
                 * ==== Matching each column with it's value
                 */
                $COLUMNS_FILL = [];
                foreach ($dbArr as $i => $dbColumn) {
                    $value = '';
                    if (in_array(array_search($dbColumn, $linkedArr), $defaultsArr)) {
                        switch ((array_search($dbColumn, $linkedArr))) {
                            case 'ai':
                                $value = 0;
                                break;
                            case 'null':
                                $value = null;
                                break;
                            case 'timestamp':
                                $value = time();
                                break;
                            default:
                                $value = array_search($dbColumn, $linkedArr);
                                break;
                        }
                    } else {
                        $excelIndex = array_search(array_search($dbColumn, $linkedArr), $excelArr);
                        $value = $row[$excelIndex];
                    }

                    $COLUMNS_FILL[] = $value;
                }


                try {

                    /**
                     * Insert statement with table name and comma separated columns, binding mark, values 
                     */
                    $statement = $db->prepare('INSERT INTO ' . $_POST['db_table'] . ' ' . $columns . ' VALUES ' . $columnsValues);
                    $statement->execute($COLUMNS_FILL);
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                    $response['success'] = false;
                    $response['data'] = [];
                    $error = true;
                }
            }

            /**
             * Delete excel file after the process ends
             */
            if (is_file('../temp/' . $excelFile)) {
                unlink('../temp/' . $excelFile);
            }

            if (!$error) {
                $response['message'] = 'Syncing done successfully.';
                $response['success'] = true;
                $response['data'] = [];
            }
        }

        echo json_encode($response);
    } else {
        echo json_encode($connectDB);
    }
} else {
    $response['message'] = $_SERVER['REQUEST_METHOD'] . ' request not available.';
    $response['success'] = false;
}
