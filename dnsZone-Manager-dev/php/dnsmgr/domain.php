<? 
// Set Intialize Informations
define('APP_BASE', dirname(__FILE__));

include_once FRAMEWORK_BASE . '/lib/basic.php';
include_once FRAMEWORK_BASE . '/lib/session.php';
include_once FRAMEWORK_BASE . '/lib/auth.php';
include_once FRAMEWORK_BASE . '/lib/ldap.php';
include_once FRAMEWORK_BASE . '/lib/dnsmgr.php';
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

//Session::initSession();
if (Auth::checkAuth()) {
        Session::refreshSession();
}

if ( $conf['debug'] == 1 ) {
  print "UserID:".$_SESSION['__auth']['userID']."<BR>";
  print "APP_BASE: ".APP_BASE."<BR>";
  print "FRAMEWORK_BASE: ".FRAMEWORK_BASE."<BR>";
}

global $conf;
global $applications;

// Get DN for Domaininformation (Save, Add and Delete below)
$dn = LDAP_functions::get_domain_attr_dn($_GET['domain'], '@'); 

// Look for Additional $_POST Parameters

// Save SOA Record
if ( $_POST['SAVE_SOA'] ) {
  // First look for the Serial and try to increment them
  $New_Serial = DNSMGR::increase_serial($_POST['SERIAL']);
  
  // Put all SOA Fields in one String:
  $soa = $_POST['MNAME']." ".
  	 $_POST['RNAME']." ".
	 $New_Serial." ".
	 $_POST['REFRESH']." ".
	 $_POST['RETRY']." ".
	 $_POST['EXPIRE']." ".
	 $_POST['MINIMUM'];
  
  LDAP_functions::modify_attribute($soa, $dn, 'replace', 'sOARecord');

}

// Add a NEW NS
if ( $_POST['ADD_NEW_NS'] ) {
  LDAP_functions::modify_attribute($_POST['NEW_NS'], $dn, 'add', 'nSRecord');
}
// Save or Replace or Delete and add NS
if ( $_POST['SAVE_NS'] ) {
  LDAP_functions::modify_attribute($_POST['OLD_NS'], $dn, 'del', 'nSRecord');
  LDAP_functions::modify_attribute($_POST['NS'], $dn, 'add', 'nSRecord');
}
// Remove NS
if ( $_POST['DELETE_NS'] ) {
  LDAP_functions::modify_attribute($_POST['NS'], $dn, 'del', 'nSRecord');
}

// Add a NEW MX
if ( $_POST['ADD_NEW_MX'] ) {
  LDAP_functions::modify_attribute($_POST['NEW_MX'], $dn, 'add', 'mXRecord');
}
// Save or Replace or Delete and add MX
if ( $_POST['SAVE_MX'] ) {
  LDAP_functions::modify_attribute($_POST['OLD_MX'], $dn, 'del', 'mXRecord');
  LDAP_functions::modify_attribute($_POST['MX'], $dn, 'add', 'mXRecord');
}
// Remove MX
if ( $_POST['DELETE_MX'] ) {
  LDAP_functions::modify_attribute($_POST['MX'], $dn, 'del', 'mXRecord');
}

// Add an Domain ARrcord
if ( $_POST['ADD_DOMAIN_A'] ) {
  LDAP_functions::modify_attribute($_POST['@ARecord'], $dn, 'replace', 'ARecord');
}
// Remove an Domain ARecord
if ( $_POST['DELETE_DOMAIN_A'] ) {
  LDAP_functions::modify_attribute($_POST['@ARecord'], $dn, 'del', 'ARecord');
}

$dn = "";
?>

<span class="menuheader">Domain: <?=$_GET['domain']?></span>

<?
// Get SOA and Domain NS/MX/ARecord
$zone = LDAP_functions::my_ldap_search('(& (zonename='.$_GET['domain'].') (relativedomainname=@))', 
                                       '', 
				       array( "soarecord", "nsrecord", "mxrecord", "arecord") );
if (is_array($zone) && (count($zone) > 1)) {
  // Should get only one entry!!!! count gives 2 :)
  if ( $conf['debug'] == 1 ) {
    print "<br>Zoneentries: ".count($zone)."<br>";
  }
  // extract SOA
  $soa = DNSMGR::split_soa($zone[0]['soarecord'][0]);
  

  // Information to Form (SOA)
?>
  <table border="0" cellspacing="0" cellpadding="0" style="width: 100%">
  <tr>
  <td>
  <fieldset class="front"><legend>SOA Record</legend>
  <p class="light">
    The SOA Entry of this Domain. <br>
    (Note: the serial can't be edited directly, it will be generated automatically while saving the SOA Record (at the moment)!)
  </p>
  <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="SOA">
  <table align="left" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
    <tr>
      <td class="light" style="width: 50px;">IN SOA</td>
      <td align="left"><input type="text" size="40" tabindex="1" name="MNAME" value="<?=$soa['MNAME']?>"> </td>
      <td class="light"><input type="text" size="40" tabindex="1" name="RNAME" value="<?=$soa['RNAME']?>"> ( </td>
      <td class="light" style="width: 24px"><input type="image" name="SAVE_SOA" value="SaveSOA" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save SOA" title="Save SOA"></td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="SERIAL" value="<?=$soa['SERIAL']?>" style="background-color: darkgray" readonly >   ; Serial</td> 
      <td class="light">&nbsp;</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="REFRESH" value="<?=$soa['REFRESH']?>">   ; Refresh</td>
      <td class="light">&nbsp;</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="RETRY" value="<?=$soa['RETRY']?>">   ; Retry</td>
      <td class="light">&nbsp;</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="EXPIRE" value="<?=$soa['EXPIRE']?>">   ; Expire</td>
      <td class="light">&nbsp;</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="MINIMUM" value="<?=$soa['MINIMUM']?>"> ) ; Minimum</td>
      <td class="light">&nbsp;</td>
    </tr>
  </table>
  </form>
  </fieldset>
  </td></tr>
  <tr><td>

  <table align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
  <tr>
  <td align="left" valign="top" style="width: 50%">

  <fieldset><legend>NS Records</legend>
  <p class="light" style="text-align: left;">
    The NS entries for this domain
  </p>
  <table align="center" cellpadding="0" cellspacing="0"  border="0">
   <? if ( is_array( $zone[0]['nsrecord'] ) ) { ?>
     <? foreach ( $zone[0]['nsrecord'] as $value ) { 
          if ( substr_count($value, '.') > 0 ) { ?>
          <tr>
            <td class="light" style="width: 50px">IN NS</td>
            <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="NS">
	    		      <input type="hidden" name="OLD_NS" value="<?=$value?>">
	                      <input type="text" style="width: 200px" tabindex="1" name="NS" value="<?=$value?>"></td>
            <td class="light" style="width: 55px">
			      <input type="image" name="SAVE_NS" value="Save" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save NS" title="Save Nameserver">
	                      <input type="image" name="DELETE_NS" value="Delete" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete NS" title="Delete Nameserver">
			      </form></td>
          </tr>
   <?     }
        }
      }
   ?>
    <tr>
      <td class="light" style="width: 50px">&nbsp;</td>
      <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="ADD_NS">
      			<input type="text" style="width: 200px" tabindex="1" name="NEW_NS" value=""></td>
      <td class="light" style="width: 55px" align="left">
			<input type="image" name="ADD_NEW_NS" value="Add NS" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_add.gif" alt="Add NS" title="Add Nameserver">
			</form></td>
    </tr>
  </table>
  </fieldset>
  
  </td>
  <td align="right" valign="top" style="width: 50%">
  
  <fieldset><legend>MX Records</legend>
  <p class="light" style="text-align: left;">
    The MX entries for this domain
  </p>

  <table align="center" cellpadding="0" cellspacing="0" border="0">
  <? if ( strpos($_GET['domain'], 'in-addr.arpa') == false ) { ?>
    <?
    // In an in-addr.arpa domain we don't need an MX and A entry
      if ( is_array( $zone[0]['mxrecord'] ) ) { ?>
     <? foreach ($zone[0]['mxrecord'] as $value) {
          if ( substr_count($value, '.') > 0 ) { ?>
          <tr>
            <td class="light" style="width: 50px">IN MX</td>
            <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="MX">
	    		      <input type="hidden" name="OLD_MX" value="<?=$value?>">
	                      <input type="text" style="width: 200px" tabindex="1" name="MX" value="<?=$value?>"></td>
            <td class="light" style="width: 55px">
			      <input type="image" name="SAVE_MX" value="Save" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save MX" title="Save Mailexchange">
	                      <input type="image" name="DELETE_MX" value="Delete" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete MX" title="Delete Mailexchange">
			      </form></td>
          </tr>
   <?     }
        }
      } ?>
    <tr>
      <td class="light" style="width: 50px">&nbsp;</td>
      <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="ADD_MX">
      			<input type="text" style="width: 200px" tabindex="1" name="NEW_MX" value=""></td>
      <td class="light" style="width: 55px" align="left">
			<input type="image" name="ADD_NEW_MX" value="Add MX" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_add.gif" alt="Add MX" title="Add Mailexchange">
			</form></td>
    </tr>
  </table>
  </fieldset>

  </td>
  </tr>
  </table>

  </td></tr>
  <tr><td>
  <fieldset><legend>Domain A Record</legend>
    <p class="light">
      This is an "special" ARecord for the Domain itself.<br/>
      (For example to use http://<?=$_GET['domain']?> in your browser)
    </p>
    <table border="0" cellspacing="0" cellpadding="0" style="width: 100%;">
    <tr><td>
    <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="Change_@A">
    <table align="left" cellpadding="0" cellspacing="0"  border="0">
      <tr>
        <td class="light" style="width: 50px">IN A</td>
        <td class="light"><input type="text" size="40" tabindex="1" name="@ARecord" value="<?=$zone[0]['arecord'][0]?>"></td>
        <td class="light"><input type="image" name="ADD_DOMAIN_A" value="Add @A" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save @ A" title="Save @ ARecord">
	                  <input type="image" name="DELETE_DOMAIN_A" value="Delete @A" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete @ A" title="Delete @ ARecord"></td>
      </tr>
    </table>
    </form>
    </td>
    </tr>
    </table>
    </fieldset>
  </td></tr>
  <tr><td>
    <fieldset><legend>Quickadd host A Record</legend>
    <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&amp;domain=".$_GET['domain']?> method="post" name="QUICK_ADD">
      <? include APP_BASE."/dnsmgr/quick_add_arecord.php"; ?>
    </form>
    </fieldset>
  </td></tr>
  <?}?>

  </table>

<?
}

?>
