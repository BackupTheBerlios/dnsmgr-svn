<?
/**
* session.php Holds Session functions for the DNSMGR
*
* @package      DNSMGR
* @author       Tim Weippert <weiti@topf-sicret.org>
* @version      dnsZone-Manager 0.1
* @copyright    Tim Weippert, 2004
* @license      GPL
*/

/**
* Session Class
*
*@package 	DNSMGR
*/

class Session {
	
	function initSession()
	{
	  global $conf;
	  
	  if ($conf['session.sditransfer'] == 'url') {
	    ini_set("session.use_only_cookies",0);
	    ini_set("session.use_cookies",0);
	  } else {
	    ini_set("session.use_only_cookies",0);
	    ini_set("session.use_cookies",1);
	  }
	  ini_set("session.gc_maxlifetime", $conf['session_maxlifetime']);
	  ini_set("session.gc_probability", 100);
	  ini_set("session.save_path", $conf['session.save_path']);
	  ini_set("session.name", $conf['session_name']);
	  @session_start();
	}
	
	function refreshSession()
	{
          $GLOBALS['__auth'] = &$_SESSION['__auth'];
          $GLOBALS['__auth']['lastaction'] = time();
	}
	
	function delSession()
	{
	  global $conf;

	  if ( $conf['auth']['params']['logfile'] ) {
    	    // Logfile ist gesetzt also schreib es auch!
	    // Set file for opening
	    $fp = fopen($conf['auth']['params']['logfile'] , 'a');
	      
	    // Check if can write to file
	    if ($fp)  {
	      fwrite($fp, date("M d Y H:i:s")." Login: User with ID (".$_SESSION['__auth']['userID'].") logged out. Session (".Session::getSID().") closed.\n" );
	    }
	      
	    // Close the written file
	    fclose($fp); 
	  }

    	  $_SESSION = array(); 
          session_unset();
          unset($_SESSION);
          session_destroy();
          @unlink($conf['session.save_path']."sess_".session_id()); //or die("Unable to delete session");
	}
	
	function checkSession() 
	{
	  global $conf;
	  if (!empty($_SESSION['__auth']['lastaction'])) {
	    $now = time();
	    $calc = $_SESSION['__auth']['lastaction']+$conf['session_timeout'];
	    if ($calc>$now) {
	      return true;
	    }
	  }
	  return false;
	}
	
	function getSID()
	{
	  $sid = session_name()."=".session_id();
	  return $sid;
	}
}
?>
