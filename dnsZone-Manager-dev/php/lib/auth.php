<?php

/**
* @package	DNSMGR
* @author	Tim Weippert <weiti@topf-sicret.org>
*/

class Auth {
	function getAuthObject($driver, $params)
	{
          $class = 'Auth_' . $driver;
          if (class_exists($class)) {
            return new $class($params);
          } else {
	    Basic::fatalError(15, __FILE__, __LINE__);
          }
	}
	
    	function setAuth($userID, $dn, $cn)
    	{
	  global $conf;
		
          if (!isset($_SESSION['__auth'])) {
            session_register('__auth');
	    session_register('activeapp');
          }
          $GLOBALS['__auth'] = &$_SESSION['__auth'];
	  $GLOBALS['activeapp'] = &$_SESSION['activeapp'];
	  
	  $auth = array('authenticated' => true,
                        'userID' => $userID,
		        'userDN' => $dn,
		        'userCN' => $cn, 
			'SID'	 => Session::getSID(),
                        'timestamp' => time(),
		        'lastaction' => time());
	  $GLOBALS['__auth'] = $auth;
	  $GLOBALS['activeapp'] = '';
    	}
	
	function getAuth()
	{
	    if (isset($_SESSION['__auth'])) {
	        if (!empty($_SESSION['__auth']['authenticated']) &&
	            !empty($_SESSION['__auth']['userID'])) {
	            return $_SESSION['__auth']['userID'];
	        }
	    }
	    return false;
	}
	
	function checkAuth()
	{
	    if (isset($_SESSION['__auth'])) {
	        if (!empty($_SESSION['__auth']['authenticated']) &&
	            !empty($_SESSION['__auth']['userID'])) {
					return true;
	        }
	    }
	    return false;
	}
}

class Auth_ldap {
	var $params = array();
	
    	function Auth_ldap($params = array())
    	{
          if (isset($params['host'])) {
            $this->params['host'] = $params['host'];
          }
          if (isset($params['basedn'])) {
            $this->params['basedn'] = $params['basedn'];
          }
          if (isset($params['logfile'])) {
            $this->params['logfile'] = $params['logfile'];
          }
          if (isset($params['port'])) {
            $this->params['port'] = $params['port'];
          }
          if (isset($params['uid'])) {
            $this->params['uid'] = $params['uid'];
          }
          if (isset($params['cn'])) {
            $this->params['cn'] = $params['cn'];
          } else {
            $this->params['cn'] = 'cn';
	  }
    	}
		
	function authenticateLDAP($userID, $pass)
	{
	    /* Ensure we've been provided with all of the necessary parameters. */
	    if (!isset($this->params['host'])) {
	        Basic::fatalError(10, __FILE__, __LINE__);
	    }
	    if (!isset($this->params['port'])) {
	        Basic::fatalError(10, __FILE__, __LINE__);
	    }
	    if (!isset($this->params['basedn'])) {
	        Basic::fatalError(10, __FILE__, __LINE__);
	    }
	    if (!isset($this->params['uid'])) {
	        Basic::fatalError(10, __FILE__, __LINE__);
	    }
	    if (!isset($pass) || ($pass == '')) {
	        Basic::fatalError(11, __FILE__, __LINE__);
	    }
	    if (!isset($userID) || ($userID == '')) {
	        Basic::fatalError(11, __FILE__, __LINE__);
	    }
	
	    /* Connect to the LDAP server. */
	    $ldap = @ldap_connect($this->params['host']);
	    if (!$ldap) {
	        Basic::fatalError(12, __FILE__, __LINE__);
	    }

	    // Set LDAPv3
	    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

	    if ( $this->params['logfile'] ) {
    	      // Logfile ist gesetzt also schreib es auch!
	      // Set file for opening
	      $fp = fopen($this->params['logfile'], 'a');
	      
	      // Check if can write to file
	      if ($fp)  {
	        fwrite($fp, date("M d Y H:i:s")." Login: User with ID (".$userID.") logged in. Session (".Session::getSID().") created.\n" );
	      }
	      
	      // Close the written file
	      fclose($fp); 
	    }

	    /* Search for the user's full DN. 
	       Administrative Attributes only set here not in config */

	    $search = @ldap_search($ldap, $this->params['basedn'], $this->params['uid'] . '=' . $userID, 
	    			   array($this->params['uid'], $this->params['cn']));
	    $result = @ldap_get_entries($ldap, $search);
	    if (is_array($result) && (count($result) > 1)) {
	      $dn = $result[0]['dn'];
	      $cn = $result[0][$this->params['cn']][0];
	    } else {
	      @ldap_close($ldap);
	      Basic::fatalError(13, __FILE__, __LINE__);
	    }
	
	    /* Attempt to bind to the LDAP server as the user. */
	    $bind = @ldap_bind($ldap, $dn, $pass);
	    if ($bind != false) {
	        @ldap_close($ldap);
	        Auth::setAuth($userID, $dn, $cn);
	        return true;
	    }
	
	    if ( $this->params['logfile'] ) {
    	      // Logfile ist gesetzt also schreib es auch!
	      // Set file for opening
	      $fp = fopen($this->params['logfile'], 'a');
	      
	      // Check if can write to file
	      if ($fp)  {
	        fwrite($fp, date("M d Y H:i:s")." Login: User with ID (".$userID.") failed to log in.\n" );
	      }
	      
	      // Close the written file
	      fclose($fp); 
	    }
	    @ldap_close($ldap);
	    return false;
	}
}
?>
