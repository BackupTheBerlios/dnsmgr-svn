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

// Get DN for Save, Add and Delete below
$dn = LDAP_functions::get_domain_attr_dn($_GET['domain'], '@'); 

// Look for Additional $_POST Parameters
if ( $_POST['ADD_NEW_NS'] ) {
  // Add a NEW NS
  LDAP_functions::modify_attribute($_POST['NEW_NS'], $dn, 'add', 'nSRecord');
}
if ( $_POST['SAVE_NS'] ) {
  LDAP_functions::modify_attribute($_POST['OLD_NS'], $dn, 'del', 'nSRecord');
  LDAP_functions::modify_attribute($_POST['NS'], $dn, 'add', 'nSRecord');
}
if ( $_POST['DELETE_NS'] ) {
  // Remove NS ...
  LDAP_functions::modify_attribute($_POST['NS'], $dn, 'del', 'nSRecord');
}

if ( $_POST['ADD_NEW_MX'] ) {
  // Add a NEW MX
  LDAP_functions::modify_attribute($_POST['NEW_MX'], $dn, 'add', 'mXRecord');
}
if ( $_POST['SAVE_MX'] ) {
  LDAP_functions::modify_attribute($_POST['OLD_MX'], $dn, 'del', 'mXRecord');
  LDAP_functions::modify_attribute($_POST['MX'], $dn, 'add', 'mXRecord');
}

if ( $_POST['DELETE_MX'] ) {
  LDAP_functions::modify_attribute($_POST['MX'], $dn, 'del', 'mXRecord');
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
  <p class="light">
    The SOA Entry of this Domain. <br>
    (Note: the serial can't be edited directly, it will be generated automatically!)
  </p>
  <table>
  <tr><td>
  <table align="left" cellpadding="0" cellspacing="0" width="700" border="0">
    <tr>
      <td class="light" width="100">IN SOA</td>
      <td align="left"><input type="text" size="40" tabindex="1" name="MNAME" value="<?=$soa['MNAME']?>"> </td>
      <td class="light"><input type="text" size="40" tabindex="1" name="RNAME" value="<?=$soa['RNAME']?>"> ( </td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="SERIAL" value="<?=$soa['SERIAL']?>" style="background-color: darkgray" readonly >   ; Serial</td> 
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="REFRESH" value="<?=$soa['REFRESH']?>">   ; Refresh</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="RETRY" value="<?=$soa['RETRY']?>">   ; Retry</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="EXPIRE" value="<?=$soa['EXPIRE']?>">   ; Expire</td>
    </tr>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light">&nbsp;</td>
      <td class="light"><input type="text" tabindex="1" name="MINIMUM" value="<?=$soa['MINIMUM']?>"> ) ; Minimum</td>
    </tr>
  </table>
  </td></tr>
  <tr><td>
    <p class="light">
      <br/><br/>
      The NS and MX Entries of this Domain.
      <br/><br/>
    </p>
  </td></tr>
  <tr><td>
  <table align="left" cellpadding="0" cellspacing="0"  border="0">
   <? if ( is_array( $zone[0]['nsrecord'] ) ) { ?>
     <? foreach ( $zone[0]['nsrecord'] as $value ) { 
          if ( substr_count($value, '.') > 0 ) { ?>
          <tr>
            <td class="light" style="width: 50px">IN NS</td>
            <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&domain=".$_GET['domain']?> method="post" name="NS">
	    		      <input type="hidden" name="OLD_NS" value="<?=$value?>">
	                      <input type="text" size="40" tabindex="1" name="NS" value="<?=$value?>"></td>
            <td class="light"><input type="image" name="SAVE_NS" value="Save" style="border-style: none;" border="0" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save">
	                      <input type="image" name="DELETE_NS" value="Delete" style="border-style: none;" border="0" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete">
			      </form></td>
          </tr>
   <?     }
        }
      }
   ?>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&domain=".$_GET['domain']?> method="post" name="ADD_NS">
      			<input type="text" size="40" tabindex="1" name="NEW_NS" value=""></td>
      <td class="light"><input type="image" name="ADD_NEW_NS" value="Add NS" style="border-style: none;" border="0" src="<?=$conf['baseurl']?>/style/images/stock_add.gif" alt="Add">
			</form></td>
    </tr>
  </table>
  <!--</td></tr>
  <tr><td>-->
  <table align="right" cellpadding="0" cellspacing="0"border="0">
  <? if ( strpos($_GET['domain'], 'in-addr.arpa') == false ) { ?>
    <?
    // In an in-addr.arpa domain we don't need an MX and A entry
      if ( is_array( $zone[0]['mxrecord'] ) ) { ?>
     <? foreach ($zone[0]['mxrecord'] as $value) {
          if ( substr_count($value, '.') > 0 ) { ?>
          <tr>
            <td class="light" style="width: 50px">IN MX</td>
            <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&domain=".$_GET['domain']?> method="post" name="MX">
	    		      <input type="hidden" name="OLD_MX" value="<?=$value?>">
	                      <input type="text" size="40" tabindex="1" name="MX" value="<?=$value?>"></td>
            <td class="light"><input type="image" name="SAVE_MX" value="Save" style="border-style: none;" border="0" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save">
	                      <input type="image" name="DELETE_MX" value="Delete" style="border-style: none;" border="0" src="<?=$conf['baseurl']?>/style/images/stock_remove.gif" alt="Delete">
			      </form></td>
          </tr>
   <?     }
        }
      } ?>
    <tr>
      <td class="light">&nbsp;</td>
      <td class="light"><form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&domain=".$_GET['domain']?> method="post" name="ADD_MX">
      			<input type="text" size="40" tabindex="1" name="NEW_MX" value=""></td>
      <td class="light"><input type="image" name="ADD_NEW_MX" value="Add MX" style="border-style: none;" border="0" src="<?=$conf['baseurl']?>/style/images/stock_add.gif" alt="Add"></form></td>
    </tr>

  <? if ( $zone[0]['arecord'][0] ) { ?>
          <tr>
            <td class="light">&nbsp;</td>
            <td class="light">&nbsp;</td>
            <td class="light">&nbsp;</td>
          </tr>
          <tr>
            <td class="light">IN A</td>
            <td class="light"><input type="text" size="40" tabindex="1" name="@ARecord" value="<?=$zone[0]['arecord'][0]?>"></td>
            <td class="light">&nbsp;</td>
          </tr>
  <?} ?>
  <?}?>
  </table>
  </td></tr>
  </table>

<?
}

?>
