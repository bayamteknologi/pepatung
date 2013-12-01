<?php

/*
 *
 *  Pepatung PHP Framework
 *
 *  Proudly coded by @akifrabbani
 *  I love PHP <3
 *
 */

    
if (!isset($_SESSION)) session_start();

// include settings
// WARNING: If you don't want to override default settings,
//          don't change this.
//

include "config.php";

// default values

if (!defined("PEPATUNG")) define("PEPATUNG",true);
if (!defined("PEPATUNG_FRAMEWORK_CURRENT_VERSION")) define("PEPATUNG_FRAMEWORK_CURRENT_VERSION","0.1");

if (!defined("USE_MYSQLI")) define("USE_MYSQLI", false); // by default we will use PDO
if (!defined("DB_ENGINE")) define("DB_ENGINE", "mysql");
if (!defined("DB_HOST")) define("DB_HOST", "localhost");
if (!defined("DB_USERNAME")) define("DB_USERNAME", "");
if (!defined("DB_PASSWORD")) define("DB_PASSWORD", "");
if (!defined("DB_NAME")) define("DB_NAME", "");
if (!defined("DB_PORT")) define("DB_PORT", 3306); // DB_PORT wil be 3306
if (!defined("MEMCACHE_HOST")) define("MEMCACHE_HOST", "localhost");
if (!defined("MEMCACHE_PORT")) define("MEMCACHE_PORT", 11211);
if (!defined("MYSQL_LEGACY")) define("MYSQL_LEGACY",false);
if (!defined("CURRENT_SYSTEM_HASH")) define("CURRENT_SYSTEM_HASH","Pepatung_Application");
if (!defined("CURRENT_THEME_PATH")) define("CURRENT_THEME_PATH", "theme");
if (!defined("CURRENT_THEME_NAME")) define("CURRENT_THEME_NAME", "bootstrap");
if (!defined("DEBUG_MODE")) define("DEBUG_MODE", 0);

if (defined("CURRENT_TIMEZONE")) { // CURRENT_TIMEZONE is in Asia/Kuala_Lumpur
    date_default_timezone_set(CURRENT_TIMEZONE);
} else {
    date_default_timezone_set("Asia/Kuala_Lumpur");
}

if (defined("DEFAULT_PAGE_TITLE")) {
    $_SESSION["_main_".CURRENT_SYSTEM_HASH."_pageTitle"] = DEFAULT_PAGE_TITLE;
} else {
    $_SESSION["_main_".CURRENT_SYSTEM_HASH."_pageTitle"] = "Pepatung";
}

// debug mode

if (defined("DEBUG_MODE") && DEBUG_MODE == 2) {

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

} elseif (defined("DEBUG_MODE") && DEBUG_MODE == 1) {

    error_reporting(E_ALL);
    ini_set('display_errors', '0');

} else {

    error_reporting(0);
    ini_set('display_errors', '0');

}

// set some strings
$_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"] = "";

class pepatung {
    
    
    function __construct () {

        $_SESSION["_main_".CURRENT_SYSTEM_HASH."_themepath"] = CURRENT_THEME_PATH;

        if (isset($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"])) unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"]);
        
        $this->setTheme(CURRENT_THEME_NAME);

    }

    function call($info) {

        if (isset($_SESSION["_main_".CURRENT_SYSTEM_HASH."_".$info])) {
            return ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_".$info]);
        } else {
            $this->throwError ("CRITICAL","Cannot find specified system variable.");
            return false;
        }
    }

    function pepatung() {
        return true;
    }
    
    function connectDB($dbengine=DB_ENGINE,$host=DB_HOST,$username=DB_USERNAME,$password=DB_PASSWORD,$dbname=DB_NAME,$legacy=MYSQL_LEGACY,$mysqli = USE_MYSQLI,$port = DB_PORT) {

        if ($dbengine == "mysql") {
            // if legacy mode is enabled
            if ($legacy) {
                if (mysql_connect($host,$username,$password)) {
                    if (!mysql_select_db($dbname)) {
                        $this->throwError("CRITICAL","Cannot select the database that will be used. (Using MYSQL_LEGACY)");
                        return false; 
                    }
                } else {
                   $this->throwError("CRITICAL","Cannot connect to database using credentials provided. (Using MYSQL_LEGACY)");
                    return false; 
                }
            }

            if ($mysqli == true) {
                $db = new mysqli($host, $username, $password, $dbname, $port);

                if ($db) {
                    return $db;
                } else {
                    $this->throwError("CRITICAL","Cannot connect to database using credentials provided. (Using MYSQLI)");
                    return false;
                }

            } else {

                try {            
                    $db = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);   
                    return $db;
                } catch(PDOException $db) {
                    $this->throwError("CRITICAL","Cannot connect to database using credentials provided. (Using PDO)");
                    return false;
                }
            }
        } else {
            $this->throwError("CRITICAL","Database engine not supported.");
            return false;
        }
    }
    
    function throwError($type, $details) {
        if ($type=="CRITICAL") {
            trigger_error($details);
            if (defined("DEBUG_MODE") && DEBUG_MODE == 1 || DEBUG_MODE == 2) echo "Error: $details";
            die();  
        }
    }

    function memcache($db = false, $query = false, $prepared = false, $host = MEMCACHE_HOST, $port = MEMCACHE_PORT) {

        /*
         *
         ********************************************
         * Memcache function for Pepatung Framework *
         * first trial - I'm proud for having this  *
         * description hahaha :P                    *
         ********************************************
         *
         * $db (default: false)
         * db statement (mysql etc2)
         * if false please throw error.
         *
         * $query (default: false)
         * query to be used in statement
         * if false oso throw error lol
         *
         * prepared (default: false)
         * we expect this is an array
         * if not array throw error again HAHAHA
         * if array, use prepare + execute instead of raw query
         * 
         */

        if (class_exists("Memcache")) {
            // of course we want the Memcache class to exists :v
            if ($memcache->connect($host, $port)) {
                if ($cache = $memcache->get(md5($query))) {
                    // fetch array of caches
                    return $cache;
                } else {
                    // cache doesn't exists, start query manually
                    if (is_array($prepared)) {
                        $stmt = $db->prepare($query);
                        $dataContainer = array();
                        if ($stmt->execute($prepared)) {
                            // load query
                            // as usual
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // fetch data
                                $dataContainer[] = $row;
                            }

                            // write to memcache server
                            if ($memcache->set(md5($query), $dataContainer)) {
                                return $dataContainer;
                            } else {
                                // error cannot view bla2
                                $this->throwError("CRITICAL","Cannot write cache to Memcache server. (Error: Memcache)");
                                return false;
                            }
                        } else {
                            // error cannot fetch data
                            $this->throwError("CRITICAL","Cannot execute prepared statement. (Error: Memcache)");
                            return false;
                        }
                    } else {
                        $dataContainer = array();
                        if ($stmt = $db->query($query)) {
                            // load query
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // fetch data
                                $dataContainer[] = $row;
                            }

                            // write to memcache server
                            if ($memcache->set(md5($query), $dataContainer)) {
                                return $dataContainer;
                            } else {
                                // error cannot view bla2
                                $this->throwError("CRITICAL","Cannot write cache to Memcache server. (Error: Memcache)");
                                return false;
                            }
                        } else {
                            // error
                            $this->throwError("CRITICAL","Cannot make query to database server. (Error: Memcache)");
                            return false;
                        }
                    }
                }
            } else {
                // cannot connect error
                $this->throwError("CRITICAL","Cannot connect to Memcache server based on the credentials provided. (Error: Memcache)");
                return false;
            }
        } else {
            $this->throwError("CRITICAL","Cannot find Memcache class. Is it installed? (Error: Memcache)");
            return false;
            
        }        

    }
    
    function setHeader($code) {
        
        if ($code==404) {
            header("HTTP/1.1 404 Not Found");
        } else {
            header("$code");
        }
        
    }
    
    function p($text) {
        
        $_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"] = $_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"].$text;
    
    }

    function loadOutput() {

        //compress disabled. hehe
        //$output = preg_replace(array('/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'),array(' ',''), $_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"]);
        echo $_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"];
    
    }

    function setTemplate($name) {

        $fileLocated = CURRENT_THEME_PATH."/".CURRENT_THEME_NAME."/".$name.".php";
        if (file_exists($fileLocated)) {
            $_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"] = $name;
            return true;
        } else {
            $this->throwError ("CRITICAL","Cannot find specified template in current theme folder.");
            return false;
        }


    }

    function setTheme($name) {

            $fileLocated = $this->call("themepath")."/".$name."/pepatung_theme.php";

            if (file_exists($fileLocated)) {
                $_SESSION["_main_".CURRENT_SYSTEM_HASH."_theme"]=$name;
                return true;
            } else {
                $this->throwError ("CRITICAL","Cannot find specified theme in default theme path.");
                return false;
            }

    }

    function pageTitle($text = "") {
        if ($text == "") {
            return ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_pageTitle"]);
        } else {
            $_SESSION["_main_".CURRENT_SYSTEM_HASH."_pageTitle"] = $text;
            return $text;
        }
    }

    function output($compress = false) {

        if (isset($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"])) {
        $fileLocated = $this->call("themepath")."/".$this->call("theme")."/".$_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"].".php";
            if (file_exists($fileLocated)) {
                // flush strings
                include $fileLocated;
                unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_theme"]);
                unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"]);
                unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_pageTitle"]);
            } else {
                return false;
            }

        } else {
            $this->loadOutput($compress);
        }
    }

    function loadTemplate($base) {

        $fileLocated = $this->call("themepath")."/".$this->call("theme")."/".$base.".php";
        if (file_exists($fileLocated)) {
            include ($fileLocated);
            return true;
        } else {
            return false;
        }

    }
    
    function session($mode,$value,$value2="") {

        if ($mode == "checkSession") {

            if (isset($_SESSION["_".CURRENT_SYSTEM_HASH."_".$value])) {
                return true;
            } else {
                return false;
            }

        }        
        
        if ($mode == "createSession") {

                $_SESSION["_".CURRENT_SYSTEM_HASH."_".$value] = $value2;
                return true;                

        }

        if ($mode == "modifySession") {

            if (isset($_SESSION["_".CURRENT_SYSTEM_HASH."_".$value])) {

                if ($value2 != "") {
                    $_SESSION["_".CURRENT_SYSTEM_HASH."_".$value] = $value2;
                    return true;
                } else {
                    return false;
                }
                
            } else {
                return false;
            }

        }

        if ($mode == "destroySession") {

            if (isset($_SESSION["_".CURRENT_SYSTEM_HASH."_".$value])) {
                unset ($_SESSION["_".CURRENT_SYSTEM_HASH."_".$value]);
            } else {
                return false;
            }

        }  

        if ($mode == "fetchSession") {

            if (isset($_SESSION["_".CURRENT_SYSTEM_HASH."_".$value])) {
               return $_SESSION["_".CURRENT_SYSTEM_HASH."_".$value];
            } else {
                return false;
            }

        }          
  
    }

    function redirect ($url,$time = 0) {

        if (isset($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"])) unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"]);
 
        if (!headers_sent()) {
            header("Refresh: $time; url=$url");
            exit();
        } else {
            echo '
            <script>setTimeout(function () {window.location.href = "'.$url.'";},'.$time.');var x=setTimeout();</script>
            <meta http-equiv="Refresh" content="'.$time.'; url='.$url.'">';
            exit();
        }
    }
    
    function generateRandomToken($how_much = 3) {
        
        $hash_container = "";
    
        while ($how_much != 0) {
            $how_much = $how_much - 1;
            $hash_container .= md5(md5(md5(md5(rand(111111111111111,999999999999999)))));
        }
    
        return $hash_container;
    
    }

    function inc($base) {

        $fileLocated = $base;
        if (file_exists($fileLocated)) {
            include ($fileLocated);
            return true;
        } else {
            return false;
        }

    }
}
?>