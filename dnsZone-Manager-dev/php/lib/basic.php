<?php

/**
* basic.php Holds basic functions like Errorhandling, etc.
*
* @package      DNSMGR
* @author       Tim Weippert <weiti@topf-sicret.org>
* @version      dnsZone-Manager 0.1
* @copyright    Tim Weippert, 2004
* @license      GPL
*/

/**
* Basic Class
*
* @package	DNSMGR
*/
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
     * @param integer $error  		Error number which is assigned to a text in errors.php.
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

