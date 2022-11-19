<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sync Excel</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/theme.min.css" />
    <link rel="stylesheet" href="./includes/plugins/flowchart/jquery.flowchart.min.css">
    <style>
        /* Important Style */
        .flowchart-example-container {
            width: 100%;
            height: 450px;
            background: white;
            border: 1px solid #BBB;
            margin-bottom: 10px;
            overflow: scroll;
            resize: both;
        }

        .flowchart-operator {
            position: absolute;
            width: 200px;
        }

        .ready-icons {
            width: fit-content;
            margin: 0 auto;
            display: flex;
            font-size: 20px;
        }

        .ready-icons div {
            margin-left: 5px;
        }

        .tag {
            border: 2px solid;
            padding: 3px 10px 3px 10px;
            border-radius: 43px;
        }

        .close-tag {
            color: #d32f2f;
        }

        .open-tag {
            color: #2e7d32;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="w-100 row">
                <div class="col-md-6">
                    <a class="navbar-brand" href="#">Abdelrhaman Samir (iSemary) </a><span><small class="text-muted">Code is a way to make life easier.</small></span>
                </div>
                <div class="col-md-6 text-right">
                    <div class="">
                        <a href="https://github.com/iSemary" target="_blank" class="btn btn-sm btn-dark my-2 my-sm-0"><i class="fab fa-github"></i> Github</a>
                        <a href="https://www.linkedin.com/in/isemary/" target="_blank" class="btn btn-sm btn-primary my-2 my-sm-0"><i class="fab fa-linkedin"></i> LinkedIn</a>
                    </div>
                </div>
            </div>
        </nav>
        <div class="card mt-2">
            <div class="card-header">
                <h3><i class="fas fa-file-excel"></i> Sync Excel</h3>
            </div>
            <div class="card-body">
                <div>
                    <div class="">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Phase 1</h4>
                                <h6><i class="fas fa-plug"></i> Connect to database and select table</h6>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-info" type="button" id="fillLocalhostDB"><i class="fas fa-server"></i> Fill Localhost Database</button>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="./config/worker.php" id="connectDBForm">
                        <input type="hidden" name="type" id="connectType" value="">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Database Host</label>
                                <input type="text" class="form-control" name="db_host" placeholder="127.0.0.1" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Database Port</label>
                                <input type="number" class="form-control" name="db_port" placeholder="3306" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Database Name</label>
                                <input type="text" class="form-control" name="db_name" placeholder="db_name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Database Username</label>
                                <input type="text" class="form-control" name="db_username" placeholder="db_username" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Database Password</label>
                                <input type="password" class="form-control" name="db_password" placeholder="db_password">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Database Table</label>
                                <select class="form-control select2" id="dbTables" name="db_table" disabled>
                                    <option value="">Select Table</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <hr>
                <div>
                    <div class="row">
                        <div class="col-6">
                            <h4>Phase 2</h4>
                            <h6><i class="fas fa-code-branch"></i> Match your columns</h6>
                            <input type="file" class="d-none" id="excelFile" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" name="excel_file">
                        </div>
                        <div class="col-6">

                        </div>
                    </div>
                    <div class="d-none" id="flowSection">
                        <div id="chart_container">
                            <div class="flowchart-example-container" id="syncExcelDBFlow"></div>
                        </div>
                        <!-- <div class="draggable_operators">
                            <div class="draggable_operators_label">
                                Operators (drag and drop them in the flowchart):
                            </div>
                            <div class="draggable_operators_divs">
                                <div class="draggable_operator" data-nb-inputs="1" data-nb-outputs="0">1 input</div>
                                <div class="draggable_operator" data-nb-inputs="0" data-nb-outputs="1">1 output</div>
                                <div class="draggable_operator" data-nb-inputs="1" data-nb-outputs="1">1 input &amp; 1 output</div>
                                <div class="draggable_operator" data-nb-inputs="1" data-nb-outputs="2">1 in &amp; 2 out</div>
                                <div class="draggable_operator" data-nb-inputs="2" data-nb-outputs="1">2 in &amp; 1 out</div>
                                <div class="draggable_operator" data-nb-inputs="2" data-nb-outputs="2">2 in &amp; 2 out</div>
                            </div>
                        </div> -->
                        <!-- <button class="btn btn-sm btn-secondary create_operator">Create operator</button> -->
                        <button class="btn btn-sm btn-secondary delete_selected_button">Delete selected operator / link</button>
                        <div id="operator_properties" style="display: block;">
                            <label for="operator_title">Operator's title: </label><input id="operator_title" type="text">
                        </div>
                        <!-- <div id="link_properties" style="display: block;">
                            <label for="link_color">Link's color: </label><input id="link_color" type="color">
                        </div> -->
                        <button class="btn btn-sm btn-secondary get_data" id="get_data">Get data</button>
                        <button class="btn btn-sm btn-secondary set_data" id="set_data">Set data</button>
                        <!-- <button class="btn btn-sm btn-secondary " id="save_local">Save to local storage</button>
                        <button class="btn btn-sm btn-secondary " id="load_local">Load from local storage</button> -->
                        <div class="mt-2">
                            <div class="row">
                                <div class="col-6 form-group">
                                    <label><i class="fas fa-code"></i> Generated JSON</label>
                                    <textarea class="form-control w-100" id="flowchart_data" placeholder="Generated data passed to backend side..."></textarea>
                                </div>
                                <div class="col-6">
                                    <small><i class="fas fa-info-circle"></i> Set the inputs empty if you want to push the whole excel file to the table</small>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label>Start from row : </label>
                                            <input type="number" class="form-control" name="start_row" value="" placeholder="100">
                                        </div>
                                        <div class="col-6 form-group">
                                            <label>End at row : </label>
                                            <input type="number" class="form-control" name="ebd_row" value="" placeholder="200">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="form-group">
                    <div class="row">
                        <div class="col-2 text-left">
                            <button type="button" class="btn btn-success" disabled><i class="fas fa-sync" id="SyncIcon"></i> Sync</button>
                        </div>
                        <div class="col-4 text-center">
                            <div class="ready-icons">
                                <div class="tag close-tag" id="dbTag">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="tag close-tag" id="excelTag">
                                    <i class="fas fa-file-excel"></i>
                                </div>
                                <div class="tag close-tag" id="matchTag">
                                    <i class="fas fa-code-branch"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 text-right">
                            <button type="button" class="btn btn-warning" id="connectDB"><i class="fas fa-database"></i> Connect to database</button>
                            <button type="button" class="btn btn-primary" id="importFile"><i class="fas fa-upload"></i> Import Excel</button>
                            <button type="button" class="btn btn-secondary" id="drawFlow"><i class="fas fa-upload"></i> Draw Flow</button>
                            <div class="form-status mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/read-excel-file@5.5.3/bundle/read-excel-file.min.js"></script>
    <script src="./includes/plugins/flowchart/jquery.flowchart.min.js"></script>
    <script src="./includes/js/main.js"></script>
</body>

</html>