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
if ( $_POST['DELETE_ARecord'] ) {
  //Delete an ARecord
  $dn = LDAP_functions::get_domain_attr_dn($_GET['domain'], $_POST['RDN']);
   LDAP_functions::modify_attribute($_POST['ARecord'], $dn, 'del', 'ARecord');
}
if ( $_POST['SAVE_ARecord'] ) {
    $dn = LDAP_functions::get_domain_attr_dn($_GET['domain'], $_POST['RDN']);
    if ( $dn ) {
      //Object exists in LDAP, only an attribute has to be edited
      LDAP_functions::modify_attribute($_POST['ARecord'], $dn, 'add', 'ARecord');
    } else {
      // Need to create an complete object a la ldap_add
      $domain_dn = LDAP_functions::get_domain_dn($_GET['domain']);
      LDAP_functions::add_rdn_arecord($_GET['domain'], $domain_dn, $_POST['RDN'],$_POST['ARecord'] );
    }
}
?>
<span class="menuheader">A Records of Domain: <?=$_GET['domain']?></span>
<p class="light">
  All A Records for a Domain
</p>

<table width="100%">
<tr><td>
  <table align="left" cellpadding="0" cellspacing="0" border="0">
    <?
    // GET all ARecords
    $ARecords = LDAP_functions::my_ldap_search('(& (zonename='.$_GET['domain'].') (arecord=*) )', 
                                       '', 
				       array( "arecord", "relativedomainname"), 
				       '', 
				       "arecord");

    if (is_array($ARecords) && (count($ARecords) > 1)) {
      foreach ( $ARecords as $rdn ) {
        if ( $rdn['relativedomainname'][0] != '' ) {
          if ( $rdn['relativedomainname'][0] == '@' ) {
	  ?>
          <tr>
            <td class="light"><input type="text" size="20" tabindex="1" style="background-color: darkgray" name="@" value="<?=$rdn['relativedomainname'][0]?>" readonly></td>
	    <td class="light" align="center" width="70">IN A</td>
	    <td class="light"><input type="text" size="20" tabindex="1"  style="background-color: darkgray" name="@" value="<?=$rdn['arecord'][0]?>" readonly></td>
	    <td class="light">&nbsp;</td>
          </tr>
          <?
	  } else {
          ?>
          <tr>
	     <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']."&amp;record=a"?> method="post" name="ARecords">
            <td class="light"><input type="text" size="20" tabindex="1" name="RDN" value="<?=$rdn['relativedomainname'][0]?>"></td>
	    <td class="light" align="center" width="70">IN A</td>
	    <td class="light"><input type="text" size="20" tabindex="1" name="ARecord" value="<?=$rdn['arecord'][0]?>"></td>
	    <td class="light"><input type="image" name="SAVE_ARecord" value="Save" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save ARecord" title="Save ARecord">
	                      <input type="image" name="DELETE_ARecord" value="Delete" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete ARecord" title="Delete ARecord"></td>
	    </form>
          </tr>
          <?
	  }
        }
      }
    }
    ?>
  </table>
</td><tr>
<tr><td>
  <p class="light">
  Add New A Record
  </p>
  <table align="left" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']."&amp;record=a"?> method="post" name="ARecords">
      <td class="light"><input type="text" size="20" tabindex="1" name="RDN" value=""></td>
      <td class="light" align="center" width="70">IN A</td>
      <td class="light"><input type="text" size="20" tabindex="1" name="ARecord" value=""></td>
      <td class="light"><input type="image" name="SAVE_ARecord" value="Save" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save ARecord" title="Save ARecord"></td>
      </form>
    </tr>
  </table>
</td></tr>
</table>
