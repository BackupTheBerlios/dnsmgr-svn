<?php

include_once FRAMEWORK_BASE . '/config/mainconfig.php';

class LDAP_functions {

  function my_ldap_search($search_filter, $base = '', $search_attributes = '', $scope = 'sub' , $sort_attribute = '', $bind_dn = '' , $bind_passwd = '') 
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
      $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);
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
        $search = @ldap_list($cc, $base, $search_filter, array($search_attributes) );
      } elseif ( $scop == "base" ) {
        $search = @ldap_read($cc, $base, $search_filter, array($search_attributes) );
      } else { 
        $search = @ldap_search($cc, $base, $search_filter, array($search_attributes) );
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

}
