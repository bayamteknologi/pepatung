<?php

/*

Pepatung Framework
Built by akifrabbani
Need more work.

version 0.1
*/

if (!isset($_SESSION)) session_start();

// include settings
include "settings.php";

if (defined("CURRENT_TIMEZONE")) {

    date_default_timezone_set(CURRENT_TIMEZONE);

}

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
    
    function connectDB($host=DB_HOST,$username=DB_USERNAME,$password=DB_PASSWORD,$dbname=DB_NAME) {
        try {
            $db = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $username, $password);
            return $db;
        } catch(PDOException $db) {
            $this->throwError("CRITICAL","Cannot connect to database using credentials provided.");
        }
        
        
    }
    
    function throwError($type, $details) {
        if ($type=="CRITICAL") {
            trigger_error($details);
            if (defined("DEBUG_MODE") && DEBUG_MODE == 1 || DEBUG_MODE == 2) echo "Error: $details";
            die();  
        }
    }
    
    function setHeader($code) {
        
        if ($code==404) {
            header("HTTP/1.1 404 Not Found");
        } else {
            header("HTTP/1.1 $code");
        }
        
    }
    
    function p($text) {
        
        $_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"] = $_SESSION["_main_".CURRENT_SYSTEM_HASH."_output"].$text;
    
    }

    function loadOutput() {
        
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

    function output() {

        if (isset($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"])) {
        $fileLocated = $this->call("themepath")."/".$this->call("theme")."/".$_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"].".php";
            if (file_exists($fileLocated)) {

                include $fileLocated;
                unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_theme"]);
                unset ($_SESSION["_main_".CURRENT_SYSTEM_HASH."_template"]);
             //   return true;
            } else {
                return false;
            }

        } else {
            $this->loadOutput();
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

    function redirect ($url,$time=0) {

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
	
	function generateRandomToken($how_much=3) {
        
		$hash_container = "0xE";
    
		while ($how_much != 0) {
			$how_much = $how_much-1;
			$hash_container .= md5(rand(111111111111111,999999999999999));
		}
    
		return $hash_container;
    
    }
    
}
?>