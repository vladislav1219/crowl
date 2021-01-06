<!DOCTYPE html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("db.php");
require("functions.php");
?>




<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <!-- Jszip -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.5.0/jszip.min.js" integrity="sha512-y3o0Z5TJF1UsKjs/jS2CDkeHN538bWsftxO9nctODL5W40nyXIbs0Pgyu7//icrQY9m6475gLaVr39i/uh/nLA==" crossorigin="anonymous"></script>
    <!-- Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/7.4.0/polyfill.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.1.1/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.2/FileSaver.min.js"></script>
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
    <!-- Clipboard.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.0/clipboard.js" integrity="sha512-I9OrKsdK01dpIZ7hLciafnQLwRERglFFIGUP8UlthtDbkl6NOS0/dGyCPrwpa0q91AVRXrsmOHtHUhF3Aj9Sdg==" crossorigin="anonymous"></script>
    <!-- Bootsrap Bundle CDN -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/crowl/">Timcrowl</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="/crowl/add_crawl.php">Add Crawl</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/crowl/"></a>
                </li>
            </ul>
        </div>
    </nav>

    <?php
    if (isset($_GET['crawl'])) {
    
    $crawl = getSql("SELECT * FROM crawls WHERE id=?",[$_GET['crawl']])->fetch();
    if ($crawl) {
        $pages = getSql(str_replace("{db}", $crawl['nameid'], "select * from `{db}`.urls;"),[])->fetchAll();
        echo("<script>var crawlData = " . json_encode($pages) . "</script>");
        echo("<script>var nameId = \"" . $crawl['nameid'] . "\"</script>");
    }

    
}

    ?>

    <!-- Crawl launcher -->
    <div class="container-fluid">
        <div class="crawlData-container">

            <div i="httpcodespie"></div>
            <div id="crawlDataGrid"></div>
        </div>
        <div id="popup">
        </div>
    </div>


    <script>
        var dataGrid = $("#crawlDataGrid").dxDataGrid({
            dataSource: crawlData,

            summary: {
                totalItems: [{
                    column: "url",
                    summaryType: "count"
                }]
            },
            columns: ["url",

                {

                    dataField: "response_code",
                    cellTemplate: function(element, info) {
                        element.append("<div>" + info.text + "</div>");
                        if (info.text >= 200 && info.text <= 300) {
                            var badgeClass = "success";
                        } else if (info.text >= 300 && info.text <= 400) {
                            var badgeClass = "warning";

                        } else if (info.text >= 400 && info.text <= 500) {
                            var badgeClass = "danger";

                        } else if (info.text >= 500) {
                            var badgeClass = "dark";

                        }

                        element.addClass("badge badge-" + badgeClass);


                    }
                },

                {
                    dataField: "outlinks",
                    width: 100,
                    alignment: 'center',
                    cellTemplate: function(container, options) {
                        $('<a/>').addClass('dx-link')
                            .text('See links')
                            .on('dxclick', function() {

                                var incomingLinksData = getLinks(nameId = nameId, direction = "incoming", urlId = options.data['id']);
                                var outgoingLinksData = getLinks(nameId = nameId, direction = "outgoing", urlId = options.data['id']);
                                window.currentUrlPopup = options.data['url'];
                                alert(options.data['url']);

                                $(function() {
                                    new Clipboard('.copy-text');
                                });


                                $("#popup").dxPopup({
                                    showTitle: true,
                                    title: window.currentUrlPopup,
                                    closeOnOutsideClick: true,
                                    dragEnabled: false,
                                    toolbarItems: [

                                        {
                                            widget: "dxButton",
                                            toolbar: "top",
                                            location: "before",

                                            options: {
                                                text: "Copy",
                                                icon: "copy",
                                                onClick: function(e) {
                                                    copyToClipboard(window.currentUrlPopup);
                                                }
                                            }
                                        },

                                        {
                                            widget: "dxButton",
                                            toolbar: "top",
                                            location: "before",
                                            options: {
                                                text: "Visit",
                                                icon: "movetofolder",
                                                onClick: function(e) {
                                                    window.open(window.currentUrlPopup, "_blank");
                                                }
                                            }
                                        }

                                    ],

                                    contentTemplate: function(options) {

                                        var scrollView = $('<div />');

                                        scrollView.append(["<h4>Incoming links</h4>",
                                            $("<div id='popupIncomingLinks' />"),
                                            "<h4>Outgoing links</h4>",
                                            $("<div id='popupOutgoingLinks' />")
                                        ]);

                                        scrollView.dxScrollView({
                                            width: '100%',
                                            height: '100%'
                                        });

                                        return scrollView;

                                    }
                                });

                                $("#popup").dxPopup("instance").show();

                                var incomingLinksDataGrid = $("#popupIncomingLinks").dxDataGrid({
                                    dataSource: incomingLinksData,
                                    columns: ["source", "text"],
                                    searchPanel: {
                                        visible: true,
                                        highlightCaseSensitive: true
                                    },
                                    summary: {
                                        totalItems: [{
                                            column: "source",
                                            summaryType: "count"
                                        }]
                                    },
                                    paging: {
                                        pageSize: 10
                                    }
                                });

                                var outgoingLinksDataGrid = $("#popupOutgoingLinks").dxDataGrid({
                                    dataSource: outgoingLinksData,
                                    columns: ["target", "text", {dataField: "nofollow",  dataType:"boolean"}],
                                    searchPanel: {
                                        visible: true,
                                        highlightCaseSensitive: true
                                    },
                                    summary: {
                                        totalItems: [{
                                            column: "target",
                                            summaryType: "count"
                                        }]
                                    },
                                    paging: {
                                        pageSize: 10
                                    }
                                });

                            }).appendTo(container);
                    }
                },

                {
                    dataField: "level",
                    dataType: "number"
                }, "title", "h1", "meta_description", {
                    dataField: "wordcount",
                    dataType: "number"
                }, "meta_robots", "XRobotsTag", "canonical", "hreflangs", "html_lang", "meta_keywords", {
                    caption: "Structured data",
                    dataField: "microdata",
                    dataType: "boolean",
                    width: 100
                }, "content_type", {
                    dataField: "meta_viewport",
                    width: 100
                }, "latency", "size", {
                    dataField: "crawled_at",
                    dataType: "datetime",
                    format: "M/d/yyyy, HH:mm"
                }
            ],
            allowColumnReordering: true,
            columnResizingMode: 'widget',
            allowColumnResizing: true,
            columnAutoWidth: true,
            showBorders: true,
            columnFixing: true,
            selection: {
                mode: "multiple"
            },
            export: {
                enabled: true,
                allowExportSelectedData: true
            },
            hoverStateEnabled: true,
            onSelectionChanged: function(selectedItems) {},
            columnsAutoWidth: true,
            filterRow: {
                visible: true
            },
            filterPanel: {
                visible: true
            },
            searchPanel: {
                visible: true,
                highlightCaseSensitive: false
            },
            headerFilter: {
                visible: true
            }
        });


        function getLinks(nameid, direction, urlId) {
            var links = "";

            $.ajax({
                url: "ajaxLinks.php",
                dataType: "json",
                async: false,
                success: function(data) {
                    if (direction == "incoming") {} else if (direction = "outgoing") {}

                    links = data;

                },

                data: {
                    nameId: nameId,
                    direction: direction,
                    id: urlId
                }
            });
            return (links);
        }

    </script>

    <script>
        function copyToClipboard(text) {
            if (window.clipboardData && window.clipboardData.setData) {
                // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
                return clipboardData.setData("Text", text);

            } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
                var textarea = document.createElement("textarea");
                textarea.textContent = text;
                textarea.style.position = "fixed"; // Prevent scrolling to bottom of page in Microsoft Edge.
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    return document.execCommand("copy"); // Security exception may be thrown by some browsers.
                } catch (ex) {
                    console.warn("Copy to clipboard failed.", ex);
                    return false;
                } finally {
                    document.body.removeChild(textarea);
                }
            }
        }

    </script>

    <script>
        var responseCodePieData = []
        console.log(crawlData);
        $("#httpcodespie").dxPieChart({
            size: {
                width: 500
            },
            palette: "green mist",
            dataSource: crawlData["response_code"],
            series: [{
                argumentField: "response_code",
                label: {
                    visible: true,
                    connector: {
                        visible: true,
                        width: 1
                    }
                }
            }],
            title: "Area of Countries",
            "export": {
                enabled: true
            },
            onPointClick: function(e) {
                var point = e.target;

                toggleVisibility(point);
            },
            onLegendClick: function(e) {
                var arg = e.target;

                toggleVisibility(this.getAllSeries()[0].getPointsByArg(arg)[0]);
            }
        });

        function toggleVisibility(item) {
            if (item.isVisible()) {
                item.hide();
            } else {
                item.show();
            }
        }

    </script>


</body>


</html>
