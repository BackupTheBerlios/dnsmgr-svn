<?php
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

class Basic {
	function getErrorMessage($error) 
	{
		include_once FRAMEWORK_BASE . '/lib/error.php';
		
		return $errors[$error];
	}
    /**
     * Abort with a fatal error, displaying debug information to the
     * user.
     *
     * @access public
     *
     * @param integer $error  			Error number which is assigned to a text in errors.php.
     * @param integer $file             The file in which the error occured.
     * @param integer $line             The line on which the error occured.
     * @param optional boolean $log     Log this message via Horde::logMesage()?
     */
    function fatalError($error, $file, $line, $log = true)
    {
        $errortext = _("<b>A fatal error has occurred:</b>") . "<br /><br />\n";
        $errortext .= Basic::getErrorMessage($error) . "<br /><br />\n";
        $errortext .= sprintf(_("[line %s of %s]"), $line, $file);

        /*if ($log) {
            $errortext .= "<br /><br />\n";
            $errortext .= _("Details have been logged for the administrator.");
        }*/

        // Hardcode a small stylesheet so that this doesn't depend on
        // anything else.
        echo <<< HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Application Framework :: Fatal Error</title>
<style type="text/css">
<!--
body { font-family: Geneva,Arial,Helvetica,sans-serif; font-size: 12px; background-color: #013069; color: #ffffff; }
.header { color: #000000; background-color: #ffffff; font-family: Verdana,Helvetica,sans-serif; font-size: 12px; }
-->
</style>
</head>
<body>
<table border="0" align="center" width="500" cellpadding="2" cellspacing="0">
<tr><td class="header" align="center">$errortext</td></tr>
</table>
</body>
</html>
HTML;

        exit;
    }
}

class Menu {
	function buildAppMenu($app)
	{
		global $appmenu, $conf;
		foreach (array_keys($appmenu[$app]) as $item) {
			if ($appmenu[$app][$item]['status'] == 'active') {
				if ($appmenu[$app][$item]['type'] == 'link') {
					echo "<tr><td>&nbsp;</td>";
					echo "<td><img src=\"".$conf['images']."/".$appmenu[$app][$item]['icon']."\" border=\"0\">&nbsp;<a href=\"index.php?".Session::getSID()."&view=".$appmenu[$app][$item]['template']."\" class=\"submenuitem\">".$appmenu[$app][$item]['name']."</a></td></tr>";
				} else if ($appmenu[$app][$item]['type'] == 'spacer') {
					echo "<tr><td colspan=\"2\"><br style=\"font-size:2pt;\"></td></tr>";
				}
			}
		};
	}
}
?>