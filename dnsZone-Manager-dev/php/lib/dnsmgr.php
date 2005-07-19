<?php

/**
* dnsmgr.php Holds specific DNSMGR Classes which are normally in needed in every file
*
* @package	DNSMGR
* @author       Tim Weippert <weiti@topf-sicret.org>
* @version	dnsZone-Manager 0.1
* @copyright	Tim Weippert, 2004
* @license	GPL
*/

/**
*/
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

/**
* Class which provides general dnsZone-Manager functions
*
* @package 	DNSMGR
* @author       Tim Weippert <weiti@topf-sicret.org>
* @version	dnsZone-Manager 0.1
* @copyright	Tim Weippert, 2004
* @license	GPL
*/

class DNSMGR {

 /**
  * Split an one line SOA 
  *
  * Split an one line SOA like from host -t SOA <domain> or from the LDAP backend in the SOARecord Attribute
  *
  * @access 	public
  * @param 	string 	One Line SOA Record
  * @return	array 	Array with SOA Fields as Key
  */

  function split_soa($SOA) {
    $pieces = explode(" ", $SOA);

    $result['MNAME'] = $pieces[0];
    $result['RNAME'] = $pieces[1];
    $result['SERIAL'] = $pieces[2];
    $result['REFRESH'] = $pieces[3];
    $result['RETRY'] = $pieces[4];
    $result['EXPIRE'] = $pieces[5];
    $result['MINIMUM'] = $pieces[6];

    return $result;
  }

 /**
  * Increase Serial 
  *
  * This function can construct up to 100 Serial Numbers for a zone a day ... more isn't
  * possible right now.
  *
  * @access 	public
  * @param 	string 	Submit current serial number.
  * @return 	string 	New Serialnumber
  */
  
  function increase_serial($Serial) {
    // First strip of last two digits (these are only for daily counters)
    $serial_day_counter = substr($Serial, -2);
    $serial_date_part = substr($Serial, 0, (strlen($Serial) - 2) );
    
    $cur_date = date("Ymd");

    if ( $serial_day_counter == 99 ) { 
      // 99 changes at one day ... thats enough!!!
      // Stay at 99 and wait for next day!
      return $Serial; 
    }
    
    if ( "$serial_date_part" == "$cur_date" ) {
      $serial_day_counter++;
      if ($serial_day_counter <  10 ) {
        $serial_day_counter = "0".$serial_day_counter;
      }
      $Serial = $serial_date_part . $serial_day_counter ;
    } else if ( $serial_date_part < $cur_date ) {
      $Serial = $cur_date . "00";
    }
    
    // If the Serial_date_part is bigger than current day
    // something went wrong and we give back the original Serial without
    // modification
    
    return $Serial;
  }
}


/**
* Class which provides LDAP Connectivity and Functions for DNSMGR
*
*
* @package      DNSMGR
* @author       Tim Weippert <weiti@topf-sicret.org>
* @version	dnsZone-Manager 0.1
* @copyright	Tim Weippert, 2004
* @license	GPL
*/

class LDAP_functions {

 /**
   * LDAP Search for DNSMGR
   *
   * <p>Function to Search in an LDAP Tree with several Parameters, 
   * most of them are optional or if missed set with an default value
   * which is mostly correct</p>
   *
   * @access    public
   * @param     string	LDAP Searchfilter
   * @param     string	LDAP Base
   * @param     array	Attributes to search (default: *)
   * @param	string	Search Scopre (default: sub)
   * @param	string	Sort Attribute (default: none)
   * @param	string	Bind DN (default: none - means read only access)
   * @param	string	Bind Password (default: none)
   * @return    array|false	Either Array with Search Result or False
   */

  function my_ldap_search($search_filter, $base = '', $search_attributes = array( "*" ), $scope = 'sub' , $sort_attribute = '', $bind_dn = '' , $bind_passwd = '') 
  {
    global $conf;
    global $applications;

    if ( $conf['debug'] == 1 ) {
      print "Read Config from file: ".FRAMEWORK_BASE . '/config/mainconfig.php'."<BR>";
    }
   
    if ( ! $base ) { $base = $applications['dnsmgr']['Backend_Base']; }
    $cc=@ldap_connect($applications['dnsmgr']['Backend_Host']);  
      
    if ( ( $bind_dn != '' ) &&  ( $bind_passwd != '' ) ) {
      // binding to ldap server
      $ldapbind = ldap_bind($ldapconn, $bind_dn, $bind_passwd);
      // verify binding
      if (! $ldapbind) {
        if ( $conf['debug'] == 1 ) {
          print "LDAP bind failed...";
	}
      }
    }
    if ( $conf['debug'] == 1 ) {
      print "DEBUG: $base -> $search_filter -> $search_attributes<br>";
    }
    // Set LDAPv3
    @ldap_set_option($cc, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($cc) { 
      if ( $scope == "one" ) {
        $search = @ldap_list($cc, $base, $search_filter, $search_attributes );
      } elseif ( $scope == "base" ) {
        $search = @ldap_read($cc, $base, $search_filter, $search_attributes );
      } else { 
        $search = @ldap_search($cc, $base, $search_filter, $search_attributes );
      }
      if ( $sort_attribute ) {
        @ldap_sort($ldap ,$search, $sort_attribute);
      }
      // Get results
      $result = @ldap_get_entries($cc, $search);

      if ( $result ) {
        @ldap_close($cc);
        return $result;
      } 
    }
    @ldap_close($cc);
    return 1;
  }
  
 /**
  * Add New A Record (LDAP ADD)
  *
  * This function adds an complete new LDAP Entry with an given arecord
  *
  * @access 	public
  * @param 	string 	Domainname
  * @param	string	Domain Distinguish Name
  * @param	string	Entry Name
  * @param	string	Value of the A Record Field (IP Address)
  * @return 	true|false
  */
  
  function add_rdn_arecord($domain, $domain_dn, $entry, $value) {
    global $conf;
    global $applications;

    if ( $conf['debug'] == 1 ) {
      print "Read Config from file: ".FRAMEWORK_BASE . '/config/mainconfig.php'."<BR>";
    }

    $rdn = "relativedomainname=".$entry.",".$domain_dn;

    $add_array['objectclass'][0] = "top"; 
    $add_array['objectclass'][1] = "dNSZone"; 
    $add_array['relativedomainname'] = $entry; 
    $add_array['zonename'] = $domain; 
    $add_array['dnsttl'] = "86400"; 
    $add_array['dnsclass'] = "IN"; 
    $add_array['arecord'] = $value; 
    
    $base = $applications['dnsmgr']['Backend_Base'];
    $cc=@ldap_connect($applications['dnsmgr']['Backend_Host']); 
    
    // Set LDAPv3
    @ldap_set_option($cc, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($cc) { 
      $cr = ldap_bind($cc, $applications['dnsmgr']['Backend_Bind_DN'], $applications['dnsmgr']['Backend_Bind_DN_Passwd'] );

      if ( $cr ) {
        // We are authenticated
	ldap_add($cc, $rdn, $add_array);
      } else {
        ldap_close($cc);
        return 1;
      }
    } else {
      ldap_close($cc);
      return 1;
    }

    ldap_close($cc);
    return 0;
  }  
  
  function modify_attribute($entry, $dn, $action, $attribute) {
    global $conf;
    global $applications;

    if ( $conf['debug'] == 1 ) {
      print "Read Config from file: ".FRAMEWORK_BASE . '/config/mainconfig.php'."<BR>";
    }
    
    $modify_array[$attribute] = $entry;
    
    $base = $applications['dnsmgr']['Backend_Base'];
    $cc=@ldap_connect($applications['dnsmgr']['Backend_Host']); 
    
    // Set LDAPv3
    @ldap_set_option($cc, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($cc) { 
      $cr = ldap_bind($cc, $applications['dnsmgr']['Backend_Bind_DN'], $applications['dnsmgr']['Backend_Bind_DN_Passwd'] );

      if ( $cr ) {
        if ( $conf['debug'] == 1 ) {
          print "Bind result: ".$cr." ".$admin_dn." </br>";
	  print "DEBUG: ".$dn." ".$domain." ".$rdn;
        }
        
	if ( $action == "add" ) {
          ldap_mod_add($cc,$dn,$modify_array);
	} else if ( $action == "del" ) {
          ldap_mod_del($cc,$dn,$modify_array);
	} else if ( $action == "replace" ) {
          ldap_mod_replace($cc,$dn,$modify_array);
	} else {
          ldap_close($cc);
          return 1;
        }

      } else {
        ldap_close($cc);
        return 1;
      }
      
    } else {
      ldap_close($cc);
      return 1;
    }

    ldap_close($cc);
    return 0;
  }  
  
  function get_domain_attr_dn($domain, $rdn) {
    global $conf;
    global $applications;
   
    $ldap_filter = '(&(zonename='.$domain.') (relativedomainname='.$rdn.'))';
    
    if ( $conf['debug'] == 1 ) {
      print "Read Config from file: ".FRAMEWORK_BASE . '/config/mainconfig.php'."<BR>";
      print "Search DN for ".$ldap_filter."<BR>";
    }

    $base = $applications['dnsmgr']['Backend_Base'];
    $cc=@ldap_connect($applications['dnsmgr']['Backend_Host']);  
    
    // Set LDAPv3
    @ldap_set_option($cc, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($cc) { 
      if ( $conf['debug'] == 1 ) { print "LDAP connected :".$base."<BR>"; }
      $search = @ldap_search($cc, $base, $ldap_filter, array( "*" ));
      if ( $search ) {
        $result = @ldap_get_entries($cc, $search);
        if (is_array($result) && (count($result) > 1)) {
          $dn = $result[0]['dn'];
          if ( $conf['debug'] == 1 ) { print "DN: ".$dn." ".$result." ".$search."<BR>"; }
        }
      @ldap_close($cc);
      return $dn;
      }
    } else {
      @ldap_close($cc);
      return 1;
    }
  }

  function get_domain_dn($domain) {
    global $conf;
    global $applications;
   
    $ldap_filter = '(associateddomain='.$domain.')';

    if ( $conf['debug'] == 1 ) {
      print "Read Config from file: ".FRAMEWORK_BASE . '/config/mainconfig.php'."<BR>";
      print "Search DN for ".$ldap_filter."<BR>";
    }

    $base = $applications['dnsmgr']['Backend_Base'];
    $cc=@ldap_connect($applications['dnsmgr']['Backend_Host']);  
    
    // Set LDAPv3
    @ldap_set_option($cc, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($cc) { 
      if ( $conf['debug'] == 1 ) { print "LDAP connected :".$base."<BR>"; }
      $search = @ldap_search($cc, $base, $ldap_filter, array( "*" ));
      if ( $search ) {
        $result = @ldap_get_entries($cc, $search);
        if (is_array($result) && (count($result) > 1)) {
          $dn = $result[0]['dn'];
          if ( $conf['debug'] == 1 ) { print "DN: ".$dn." ".$result." ".$search."<BR>"; }
        }
      @ldap_close($cc);
      return $dn;
      }
    } else {
      @ldap_close($cc);
      return 1;
    }
  }
}

?>
