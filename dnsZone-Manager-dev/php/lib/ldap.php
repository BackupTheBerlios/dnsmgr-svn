<?php

include_once FRAMEWORK_BASE . '/config/mainconfig.php';

class LDAP_functions {

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
