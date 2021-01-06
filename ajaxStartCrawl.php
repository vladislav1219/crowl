 
    <?php 
    if(isset($_POST['projectName'])) {
        # Process the $_POST into a configuration file
        
            # Process exclusions
        
        $exclusions = "";
        
        if($_POST['exclusions'] !== "") { 
            if(strpos("\n", $_POST['exclusions'])) { # Many exclusions
                
                # If many exclusions, add one per line with 4 spaces before starting at line 2 (multi line input format for configParser)
                
                $spaceItUp = false;
                
                foreach(explode("\n", $_POST['exclusions']) as $line) {
                    if ($spaceItUp) {
                        $exclusions .= "    " . $line;
                    } else {
                        $exclusions .= $line;
                        $spaceItUp = true;
                    }
                }
                
            } else { # One exclusion
                $exclusions = $_POST['exclusions'];
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
MYSQL_USER = Syard
MYSQL_PASSWORD = Badorpopopopo*4
            ";
            $searchReplaceArray = array(
              '{projectName}' => $_POST['projectName'], 
              '{startUrl}' => $_POST['startUrl'],
              '{userAgent}' => $_POST['userAgent'], 
              '{obeyRobots}' => $_POST['obeyRobots'],
              '{exclusions}' => $_POST['exclusions'], 
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
        
        escapeshellcmd('nohup python3.8 /home/Syard/crowl/crowl.py --conf ' . $filename . ' > /dev/null 2>&1 &');
    }
    ?>