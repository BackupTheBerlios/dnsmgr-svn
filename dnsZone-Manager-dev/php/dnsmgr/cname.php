<? 
// Set Intialize Informations
define('APP_BASE', dirname(__FILE__));

include_once FRAMEWORK_BASE . '/lib/basic.php';
include_once FRAMEWORK_BASE . '/lib/session.php';
include_once FRAMEWORK_BASE . '/lib/auth.php';
include_once FRAMEWORK_BASE . '/lib/ldap.php';
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

//Session::initSession();
if (Auth::checkAuth()) {
        Session::refreshSession();
}

global $conf;
global $applications;

// look for actions
if ( $_POST['DELETE_CName'] ) {
  //Delete an CName
  $dn = LDAP_functions::get_domain_attr_dn($_GET['domain'], $_POST['RDN']);
  LDAP_functions::modify_attribute($_POST['RDN'], $dn, 'del', 'relativedomainname');
}
?>
<span class="menuheader">CNAME Records of Domain: <?=$_GET['domain']?></span>

<fieldset><legend>Existing CNAME Records</legend>
<p class="light">
  All CNAME Records in this zone
</p>

<table width="100%">
<tr><td>
  <table align="left" cellpadding="0" cellspacing="0" border="0">
    <?
    // GET all CNames
    $CNames = LDAP_functions::my_ldap_search('(& (zonename='.$_GET['domain'].') (cnamerecord=*) )', 
                                       '', 
				       array( "cnamerecord", "relativedomainname"), 
				       '', 
				       '');

    if (is_array($CNames) && (count($CNames) > 1)) {
      foreach ( $CNames as $rdn ) {
        if ( is_array($rdn['relativedomainname']) ) {
	  array_shift ( $rdn['relativedomainname'] );
          foreach ( $rdn['relativedomainname'] as $rdn_cname ) {
            if ( $rdn_cname != '' && ( strpos($rdn_cname, "CNAMEs") == false) ) {
            ?>
              <tr>
	       <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']."&amp;record=cname"?> method="post" name="CNames">
               <td class="light"><input type="text" size="20" tabindex="1" name="RDN" value="<?=$rdn_cname?>"></td>
	       <td class="light" align="center" width="70">IN CNAME</td>
	       <td class="light"><input type="text" size="20" tabindex="1" name="CName" value="<?=$rdn['cnamerecord'][0]?>"></td>
	       <td class="light"><input type="image" name="SAVE_CName" value="Save" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save CName" title="Save CName">
	                      <input type="image" name="DELETE_CName" value="Delete" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete CName" title="Delete CName"></td>
	       </form>
              </tr>
            <?
	    }
	  }
        }
      }
    }
    ?>
  </table>
</td><tr>
</table>
</fieldset>

<!--

<fieldset><legend>Add New CNAME Record</legend>
<p class="light">
  Add New CNAME Record
</p>

<table width="100%">
<tr><td>
  <table align="left" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td class="light"><input type="text" size="20" tabindex="1" name="RDN" value=""></td>
      <td class="light" align="center" width="70">IN A</td>
      <td class="light"><input type="text" size="20" tabindex="1" name="CName" value=""></td>
      <td class="light"><input type="image" name="SAVE_CName" value="Save" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save CName" title="Save CName"></td>
    </tr>
  </table>
</td></tr>
</table>
</fieldset>
-->
