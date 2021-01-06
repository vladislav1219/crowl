<!DOCTYPE html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php");
require_once("functions.php");

?>




<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <!-- Devextreme CSS template -->
    <link href="https://cdn3.devexpress.com/jslib/20.2.3/css/dx.common.css" rel="stylesheet">
    <link href="https://cdn3.devexpress.com/jslib/20.2.3/css/dx.greenmist.css" rel="stylesheet">
    <!-- Bootstrap CSS template -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Jquery 3.5.1 CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous">
    </script>
    <!-- Devextreme - Development files CDN -->
    <script src="https://cdn3.devexpress.com/jslib/20.2.3/js/dx.all.debug.js"></script>
    <!-- Bootsrap Bundle CDN -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <!-- Crawl launcher -->


    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/crowl/">Timcrowl</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="/crowl/add_crawl.php">Launch Crawl</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"></a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="demo-container">
            <div id="gridContainer"></div>
        </div>
        <div>
            <button onclick="refresh();"></button>
        </div>
    </div>

    <!-- Datagrid -->

    <script>
        var crawls = "";
        $.ajax({
            url: "ajaxCrawls.php",
            dataType: "json",
            success: function(data) {

                crawls = data;
                console.log(crawls);

                var dataGrid = $("#gridContainer").dxDataGrid({
                    dataSource: crawls,
                    columns: [{
                            width: 40,
                            alignment: "left",
                            cellTemplate: function(container, options) {
                                $('<a/>').addClass('dx-link')
                                    .text('Voir')
                                    .on('dxclick', function() {
                                        window.location = 'crawl.php?crawl=' + options.data['id'];
                                    })
                                    .appendTo(container);
                            }
                        },
                        'name',
                        'state', {
                            dataField: "dequeued",
                            caption: "Pages"
                        },
                        {
                            dataField: "domain",
                            groupIndex: 0
                        }
                    ],
                    editing: {
                        mode: "row",
                        allowDeleting: true,
                        allowUpdating: true
                    },
                    allowColumnReordering: true,
                    allowColumnResizing: true,
                    columnAutoWidth: true,
                    showBorders: true,
                    rowAlternationEnabled: true,
                    groupPanel: {
                        visible: true
                    },
                    grouping: {
                        autoExpandAll: true,
                    },
                    selection: {
                        mode: "single"
                    },
                    hoverStateEnabled: true,
                    onSelectionChanged: function(selectedItems) {
                        var data = selectedItems.selectedRowsData[0];
                        console.log(selectedItems.selectedRowsData);
                        if (data) {
                            $(".employeeNotes").text(data.Notes);
                            $(".employeePhoto").attr("src", data.Picture);
                        }
                    },

                    onRowRemoved: function(e) {
                        deleteRow(e.data['nameid']);
                    },
                    onSaved: function(e) {
                        if (typeof(e.changes[0]['data']) !== "undefined") {
                            changeName(e.changes[0]["data"]["id"], e.changes[0]["data"]["name"]);
                        }
                    }
                });

            }
        });

        console.log(crawls);

    </script>



    <script>
        function deleteRow(nameid) {
            $.ajax({
                url: "ajaxCrawls.php",
                data: {
                    delete_nameid: nameid,
                }
            });
        }

        function changeName(id, new_name) {
            $.ajax({
                url: "ajaxCrawls.php",
                data: {
                    updatename_by_id: id,
                    new_name: new_name
                }
            });
        }

        function refresh() {
            crawls = [];
            console.log(crawls);
            $("#gridContainer").dxDataGrid("instance").option("dataSource", crawls);
        }

        function yourFunction() {
            // do whatever you like here

            setTimeout(yourFunction, 5000);
        }

    </script>

</body>

</html>
