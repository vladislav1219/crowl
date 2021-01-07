<!DOCTYPE html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db.php");
require_once("functions.php");

    if(isset($_POST['projectName'])) {
        
        # Add the configuration to database 
        $postArray = $_POST;
        
        # Process the $_POST into a configuration file

            # Process exclusions
        
        $exclusions = "";
                
        if($_POST['exclusions'] !== "") { 
            
            $_POST['exclusions'] = preg_split('/\s\s+/', $_POST['exclusions']); # Split exclusions into array
            
            
            if(count($_POST['exclusions']) > 1) { # Many exclusions
                
                # If many exclusions, add one per line with 4 spaces before starting at line 2 (multi line input format for configParser)
                
                $spaceItUp = false;
                
                foreach($_POST['exclusions'] as $line) {
                    if ($spaceItUp) {
                        $exclusions .= "\n    " . $line;
                    } else {
                        $exclusions .= $line;
                        $spaceItUp = true;
                    }
                }
            } else { # One exclusion
                $exclusions = $_POST['exclusions'][0];
            }
        }        

            # Process outputs
                # CSV
        if(isset($_POST['outputCsv'])) {
            $_POST['outputCsv'] = "crowl.CrowlCsvPipeline = 100";    
        } else {$_POST['outputCsv'] = "";}
        
        
                # MySql        
        if(isset($_POST['outputMysql'])) {
            $_POST['outputMysql'] = "crowl.CrowlMySQLPipeline = 200";
        } else {$_POST['outputMysql'] = "";}
        
            # Process checkboxes 
        
        $checkboxes = ["obeyRobots","storeLinks","storeContent"];
        foreach($checkboxes as $checkbox) {
            if(!isset($_POST[$checkbox])) {
                $_POST[$checkbox] = "False";
            } else {
                $_POST[$checkbox] = "True";
            }
        }

            # Create config file
        
        $configFile = "
[PROJECT]
PROJECT_NAME = {projectName}
START_URL = {startUrl}
[CRAWLER]
USER_AGENT = {userAgent}
ROBOTS_TXT_OBEY = {obeyRobots}
EXCLUSION_PATTERN = {exclusions}
DOWNLOAD_DELAY = {delay}
CONCURRENT_REQUESTS = {threads}
MIME_TYPES = {mimeTypes}
ACCEPT_LANGUAGE = {acceptLanguage}

[EXTRACTION]
LINKS = {storeLinks}
CONTENT = {storeContent}
DEPTH = {depth}

[OUTPUT]
{outputCsv}
{outputMysql}

[MYSQL]
MYSQL_HOST = localhost
MYSQL_PORT = 3306
MYSQL_USER = vladislav
MYSQL_PASSWORD = Pakatopopopopo*4
            ";
            $searchReplaceArray = array(
              '{projectName}' => $_POST['projectName'], 
              '{startUrl}' => $_POST['startUrl'],
              '{userAgent}' => $_POST['userAgent'], 
              '{obeyRobots}' => $_POST['obeyRobots'],
              '{exclusions}' => $exclusions, 
              '{delay}' => $_POST['amountInputDelay'],
              '{threads}' => $_POST['amountInputThreads'], 
              '{mimeTypes}' => $_POST['mimeTypes'],
              '{acceptLanguage}' => $_POST['acceptLanguage'],
              '{storeLinks}' => $_POST['storeLinks'], 
              '{storeContent}' => $_POST['storeContent'],
              '{depth}' => $_POST['amountInputDepth'], 
              '{outputCsv}' => $_POST['outputCsv'],
              '{outputMysql}' => $_POST['outputMysql']
            );
        
            $parsedConfigFile = str_replace(
              array_keys($searchReplaceArray), 
              array_values($searchReplaceArray), 
              $configFile
            ); 
        
        
        # Generate random name for the file 
        
        $filename = "temp_config_" . bin2hex(random_bytes(10)) . ".ini";
        
        # Create the .ini file
        
        file_put_contents($filename,$parsedConfigFile);
        
        # Launch the python script
        
        $cmd = escapeshellcmd('nohup python3.8 crowl.py --conf ' . $filename) . ' > /dev/null 2>&1 &';
        $result = shell_exec($cmd);
        sleep(3);
        
        # Delete temp config file
        #unlink($filename);
        
        # Select lastId and add config to crawl
        $lastId = getSql("SELECT lastId from lastId",[]) -> fetch()['lastId'];
        sendSql("UPDATE crawls SET config = ? WHERE id = ?",[$parsedConfigFile, $lastId]);
        
        $postArray["crawl_id"] = $lastId;
        
        insertArray("configuration", $postArray);
        
            
    }
    // else
    // {
    //     echo "from here";

    //     $cmds = 'nohup python3.8 pageRank.py &';
    //     $results = shell_exec($cmds);
    //     // $result1= shell_exec($cmd1);
    //     // echo $result1;
    //     echo "to here";
    //     sleep(3);
    // }
    ?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <!-- Devextreme CSS template -->
    <link href="https://cdn3.devexpress.com/jslib/20.2.3/css/dx.common.css" rel="stylesheet">
    <link href="https://cdn3.devexpress.com/jslib/20.2.3/css/dx.greenmist.css" rel="stylesheet">
    <!-- Bootstrap CSS template -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Crawl launcher -->
    <div class="container-fluid">
        <?php if(isset($_POST['projectName'])) {
    
    echo(alert("success","Crawl task has been launched."));
    
}
        ?>


        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="/crowl/">Timcrowl</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </nav>
        <div class="container-fluid">
            <form>
                <div class="form-group">
                    <label for="crawlSelect">Load crawl configuration</label>
                    <select class="form-control" onChange="loadConfig(this);" id="crawlSelect">

                        <?php 
                    
                    $crawls = getSql("SELECT * FROM crawls LEFT JOIN crawl_stats ON crawls.id = crawl_stats.crawl_id ORDER BY start_time
",[]) -> fetchAll();
                    
                    foreach ($crawls as $crawl) {
                        
                        var_dump($crawl);
                        if ($crawl['state'] !== 'running') {
                            $crawl['state'] == badge('success',$crawl['state']);
                        } else {
                            $crawl['state'] = badge('warning',$crawl['state']);
                        }
                        
                        echo('<option value='. $crawl['id'] . '>'  .  $crawl['state'] . ' - ' . $crawl['name']  . ' - ' . $crawl['domain'] .  '</option>');
                    }?>
                    </select>
                </div>
            </form>
            <form method="post">
                <div class="form-group row">
                    <label for="projectName" class="col-sm-2 col-form-label">Nom</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="projectName" name="projectName" placeholder="Crawl #1" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="startUrl" class="col-sm-2 col-form-label">Start URL</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="startUrl" name="startUrl" placeholder="https://www.example.com" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="userAgent" class="col-sm-2 col-form-label">User Agent</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="userAgent" name="userAgent" value="Crowl (+https://www.crowl.tech/)" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="acceptLanguage" class="col-sm-2 col-form-label">Accept language</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="acceptLanguage" name="acceptLanguage" value="en" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="mimeTypes" class="col-sm-2 col-form-label">MIME types</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="mimeTypes" name="mimeTypes" value="text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" required>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-2">Crawl options</div>
                    <div class="col-sm-2">

                        <label for="delay">Download delay</label>
                        <input type="range" class="custom-range" min="0" max="5" step="0.1" id="delay" value="0.5" oninput="this.form.amountInputDelay.value=this.value">
                        <input id="amountInputDelay" class="form-control" type="number" name="amountInputDelay" step="0.1" min="0" max="5" value="0.5" oninput="this.form.delay.value=this.value" />

                        <label for="threads">Crawler threads</label>
                        <input type="range" class="custom-range" min="0" max="10" step="1" id="threads" value="5" oninput="this.form.amountInputThreads.value=this.value">
                        <input id="amountInputThreads" class="form-control" type="number" name="amountInputThreads" step="1" min="0" max="10" value="5" oninput="this.form.threads.value=this.value" />

                        <label for="depth">Crawl depth</label>
                        <input type="range" class="custom-range" min="0" max="10" step="1" id="depth" value="5" oninput="this.form.amountInputDepth.value=this.value">
                        <input id="amountInputDepth" class="form-control" type="number" name="amountInputDepth" step="1" min="0" max="10" value="5" oninput="this.form.depth.value=this.value" />
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-2">Exclusions</div>
                    <div class="col-sm-2">
                        <label for="exclusions">Disallowed regex</label>
                        <textarea class="form-control" id="exclusions" name="exclusions" rows="3"></textarea>
                    </div>
                </div>


                <div class="form-group row">
                    <div class="col-sm-2">Extraction options</div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="obeyRobots" name="obeyRobots" checked="checked">
                            <label class="form-check-label" for="obeyRobots">
                                Obey robots.txt
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="storeLinks" name="storeLinks" checked="checked">
                            <label class="form-check-label" for="storeLinks">
                                Store links
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="storePageContent" name="storePageContent">
                            <label class="form-check-label" for="storePageContent">
                                Store page content
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="outputCsv" name="outputCsv">
                            <label class="form-check-label" for="outputCsv">
                                CSV output
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="outputMysql" name="outputMysql" checked="checked">
                            <label class="form-check-label" for="outputMysql">
                                MySQL output
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-10">
                        <button type="submit" class="btn btn-primary">Launch</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function loadConfig(wellThis) {
            $.ajax({
                url: "ajaxAddCrawl.php",
                dataType: "json",
                async: true,
                success: function(data) {

                    if (typeof data[0] !== 'undefined') {

                        delete data[0]['id'];
                        delete data[0]['crawl_id'];
                        data[0]["threads"] = data[0]["amountInputThreads"];
                        data[0]["delay"] = data[0]["amountInputDelay"];
                        data[0]["depth"] = data[0]["amountInputDepth"];

                        for (key in data[0]) {
                            document.getElementById(key).value = data[0][key];
                        }

                    }
                },

                data: {
                    crawl_id: wellThis.value
                }
            });
        }

    </script>


    <!-- Devextreme - Development files CDN -->
    <script src="https://cdn3.devexpress.com/jslib/20.2.3/js/dx.all.debug.js"></script>
    <!-- Jquery 3.5.1 CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous">
    </script>
    <!-- Bootsrap Bundle CDN -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
