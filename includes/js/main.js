let dbArr = [];
let excelArr = [];
// Fill localhost info
$("#fillLocalhostDB").on("click", function (e) {
  e.preventDefault();
  $('input[name="db_host"]').val("127.0.0.1");
  $('input[name="db_port"]').val(3306);
  $('input[name="db_name"]').val("sync_excel");
  $('input[name="db_username"]').val("root");
});
// Call api function
function callAPI(formID, type, btn) {
  $("#connectType").val(type);
  let form = $("#" + formID);
  let formBtn = $("#"+btn);
  let formData = new FormData(form[0]);
  let formUrl = form.attr("action");
  $.ajax({
    type: "POST",
    dataType: "json",
    url: formUrl,
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    beforeSend: function () {
      $(".form-status").html(
        `<h6 class="text-muted"><i class="fas fa-circle-notch fa-spin"></i> Connecting to your database, please wait...</h6>`
      );
      formBtn.prop("disabled", true);
    },
    success: function (data) {
      // Fill Select table with database tables
      if (type == "tables") {
        $("#dbTables").find("option").not(":first").remove();
        if (data.success) {
          $.each(data.data, function (i, val) {
            $("#dbTables").append(
              $("<option>", {
                value: val.table_name,
                text: val.table_name,
              })
            );
          });
          $("#dbTables").prop("disabled", false);
        } else {
          $("#dbTables").prop("disabled", true);
        }
        // Fill columns flow with selected table columns
      } else if (type == "columns") {
        if (data.success) {
          dbArr = Object.keys(data.data).map(function (key) {
            return data.data[key].COLUMN_NAME;
          });
          $("#dbTag").toggleClass("close-tag open-tag");
          $("#dbColumns").prop("disabled", false);
        } else {
          $("#dbColumns").prop("disabled", true);
        }
      } else if (type == "execute") {

      }
      $(".form-status").html(
        `<h6 class="text-${
          data.success ? "success" : "danger"
        }"><i class="fas ${
          data.success ? "fa-check-circle" : "fa-exclamation-triangle"
        }"></i> ${data.message}</h6>`
      );

      formBtn.prop("disabled", false);
    },
  });
}
// Connect and Get Tables
$("#connectDB").on("click", function (e) {
  e.preventDefault();
  callAPI("connectDBForm", "tables", "connectDB");
});
// Connect and Get Selected Table Columns
$("#dbTables").on("change", function (e) {
  e.preventDefault();
  callAPI("connectDBForm", "columns", "dbTables");
});
// Get uploaded excel columns
$("#importFile").on("click", function (e) {
  $("#excelFile").trigger("click");
});
// Read first row of excel file
$("#excelFile").on("change", function () {
  //Get the files from Upload control
  var file = $(this).get(0).files[0];
  readXlsxFile(file).then(function (data) {
    excelArr = data[0]; // First row of excel
  });
  $("#excelTag").toggleClass("close-tag open-tag");
});
// Draw flow click
$("#drawFlow").on("click", function (e) {
  $("#flowSection").removeClass("d-none");
  drawFlow(dbArr, excelArr);
});
// Listen for generated json
$("#get_data").on("click", function () {
  $("#syncBtn").prop("disabled", false);
  $("#matchTag").removeClass("close-tag").addClass("open-tag");
});

// Draw Flow based on database columns and excel columns
function drawFlow(db_columns, excel_columns) {
  $(document).ready(function () {
    /* global $ */
    var $flowchart = $("#syncExcelDBFlow");
    var $container = $flowchart.parent();

    // Apply the plugin on a standard, empty div...
    $flowchart.flowchart({
      data: defaultFlowchartData,
      defaultSelectedLinkColor: "#000055",
      grid: 10,
      multipleLinksOnInput: true,
      multipleLinksOnOutput: true,
    });

    function getOperatorData($element) {
      var nbInputs = parseInt($element.data("nb-inputs"), 10);
      var nbOutputs = parseInt($element.data("nb-outputs"), 10);
      var data = {
        properties: {
          title: $element.text(),
          inputs: {},
          outputs: {},
        },
      };

      var i = 0;
      for (i = 0; i < nbInputs; i++) {
        data.properties.inputs["input_" + i] = {
          label: "Input " + (i + 1),
        };
      }
      for (i = 0; i < nbOutputs; i++) {
        data.properties.outputs["output_" + i] = {
          label: "Output " + (i + 1),
        };
      }

      return data;
    }

    //-----------------------------------------
    //--- operator and link properties
    //--- start
    var $operatorProperties = $("#operator_properties");
    $operatorProperties.hide();
    var $linkProperties = $("#link_properties");
    $linkProperties.hide();
    var $operatorTitle = $("#operator_title");
    var $linkColor = $("#link_color");

    $flowchart.flowchart({
      onOperatorSelect: function (operatorId) {
        $operatorProperties.show();
        $operatorTitle.val(
          $flowchart.flowchart("getOperatorTitle", operatorId)
        );
        return true;
      },
      onOperatorUnselect: function () {
        $operatorProperties.hide();
        return true;
      },
      onLinkSelect: function (linkId) {
        $linkProperties.show();
        $linkColor.val($flowchart.flowchart("getLinkMainColor", linkId));
        return true;
      },
      onLinkUnselect: function () {
        $linkProperties.hide();
        return true;
      },
    });

    $operatorTitle.keyup(function () {
      var selectedOperatorId = $flowchart.flowchart("getSelectedOperatorId");
      if (selectedOperatorId != null) {
        $flowchart.flowchart(
          "setOperatorTitle",
          selectedOperatorId,
          $operatorTitle.val()
        );
      }
    });

    $linkColor.change(function () {
      var selectedLinkId = $flowchart.flowchart("getSelectedLinkId");
      if (selectedLinkId != null) {
        $flowchart.flowchart(
          "setLinkMainColor",
          selectedLinkId,
          $linkColor.val()
        );
      }
    });
    //--- end
    //--- operator and link properties
    //-----------------------------------------

    //-----------------------------------------
    //--- delete operator / link button
    //--- start
    $flowchart
      .parent()
      .siblings(".delete_selected_button")
      .click(function () {
        $flowchart.flowchart("deleteSelected");
      });
    //--- end
    //--- delete operator / link button
    //-----------------------------------------

    //-----------------------------------------
    //--- create operator button
    //--- start
    var operatorI = 0;
    $flowchart
      .parent()
      .siblings(".create_operator")
      .click(function () {
        var operatorId = "created_operator_" + operatorI;
        var operatorData = {
          top: $flowchart.height() / 2 - 30,
          left: $flowchart.width() / 2 - 100 + operatorI * 10,
          properties: {
            title: "Operator " + (operatorI + 3),
            inputs: {
              input_1: {
                label: "Input 1",
              },
            },
            outputs: {
              output_1: {
                label: "Output 1",
              },
            },
          },
        };

        operatorI++;

        $flowchart.flowchart("createOperator", operatorId, operatorData);
      });
    //--- end
    //--- create operator button
    //-----------------------------------------

    //-----------------------------------------
    //--- draggable operators
    //--- start
    //var operatorId = 0;
    var $draggableOperators = $(".draggable_operator");
    $draggableOperators.draggable({
      cursor: "move",
      opacity: 0.7,

      // helper: 'clone',
      appendTo: "body",
      zIndex: 1000,

      helper: function (e) {
        var $this = $(this);
        var data = getOperatorData($this);
        return $flowchart.flowchart("getOperatorElement", data);
      },
      stop: function (e, ui) {
        var $this = $(this);
        var elOffset = ui.offset;
        var containerOffset = $container.offset();
        if (
          elOffset.left > containerOffset.left &&
          elOffset.top > containerOffset.top &&
          elOffset.left < containerOffset.left + $container.width() &&
          elOffset.top < containerOffset.top + $container.height()
        ) {
          var flowchartOffset = $flowchart.offset();

          var relativeLeft = elOffset.left - flowchartOffset.left;
          var relativeTop = elOffset.top - flowchartOffset.top;

          var positionRatio = $flowchart.flowchart("getPositionRatio");
          relativeLeft /= positionRatio;
          relativeTop /= positionRatio;

          var data = getOperatorData($this);
          data.left = relativeLeft;
          data.top = relativeTop;

          $flowchart.flowchart("addOperator", data);
        }
      },
    });
    //--- end
    //--- draggable operators
    //-----------------------------------------

    //-----------------------------------------
    //--- save and load
    //--- start
    function Flow2Text() {
      var data = $flowchart.flowchart("getData");
      $("#flowchart_data").val(JSON.stringify(data, null, 2));
    }
    $("#get_data").click(Flow2Text);

    function Text2Flow() {
      var data = JSON.parse($("#flowchart_data").val());
      $flowchart.flowchart("setData", data);
    }
    $("#set_data").click(Text2Flow);

    /*global localStorage*/
    function SaveToLocalStorage() {
      if (typeof localStorage !== "object") {
        alert("local storage not available");
        return;
      }
      Flow2Text();
      localStorage.setItem("stgLocalFlowChart", $("#flowchart_data").val());
    }
    $("#save_local").click(SaveToLocalStorage);

    function LoadFromLocalStorage() {
      if (typeof localStorage !== "object") {
        alert("local storage not available");
        return;
      }
      var s = localStorage.getItem("stgLocalFlowChart");
      if (s != null) {
        $("#flowchart_data").val(s);
        Text2Flow();
      } else {
        alert("local storage empty");
      }
    }
    $("#load_local").click(LoadFromLocalStorage);
    //--- end
    //--- save and load
    //-----------------------------------------
  });

  // Input example
  // let db_columns = ['A', 'B', 'C'];
  // let excel_columns = ['A', 'B', 'C'];
  $("#syncExcelDBFlow").html("");
  var dbData = {};
  $.each(db_columns, function (i, val) {
    dbData["input_" + ++i] = {
      label: val,
    };
  });
  var excelData = {};
  $.each(excel_columns, function (i, val) {
    excelData["output_" + ++i] = {
      label: val,
    };
  });

  // Output example
  //  {
  //         input_1: {
  //             label: 'Input 1',
  //         },
  //         input_2: {
  //             label: 'Input 2',
  //         },
  //     }

  var defaultFlowchartData = {
    operators: {
      operator1: {
        top: 20,
        left: 300,
        properties: {
          title: "Excel Columns",
          inputs: {},
          outputs: excelData,
        },
      },
      operator2: {
        top: 20,
        left: 700,
        properties: {
          title: "Database Columns",
          inputs: dbData,
          outputs: {},
        },
      },
      operator3: {
        top: 0,
        left: 0,
        properties: {
          title: "Default Values",
          inputs: {},
          outputs: {
            output_1: {
              label: "Auto Increment",
              type: "id",
            },
            output_2: {
              label: "Time Stamp",
              type: "timestamp",
            },
            output_3: {
              label: "Null",
              type: "null",
            },
            output_4: {
              label: "0",
              type: "0",
            },
          },
        },
      },
    },
    links: {},
    /**
         {
        link_1: {
            fromOperator: 'operator1',
            fromConnector: 'output_1',
            toOperator: 'operator2',
            toConnector: 'input_2',
        },
            }
         */
  };
  if (false) console.log("remove lint unused warning", defaultFlowchartData);
}

// Pass data to execute the cycle in backend side
$("#syncBtn").on("click", function (e) {
  e.preventDefault();
  callAPI("connectDBForm", "execute", "syncBtn");
});
