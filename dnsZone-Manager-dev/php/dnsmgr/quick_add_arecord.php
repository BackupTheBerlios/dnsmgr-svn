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

if ( $_POST['SAVE_QUICK_ADD'] ) {
  // Multiline Textfield
  $quick = $_POST['QUICK_ADD'];
  // Replace all possible carriage returns with \n
  str_replace(Array("\r\n", "\r"), "\n", $quick);
  // explode string by \n
  $quick_lines = explode("\n", $quick);
  foreach ( $quick_lines as $line ) {
    //replace \t with " "
    str_replace("\t", " ", $line);
    list($entry, $value) = explode(" ", $line);

    $dn = LDAP_functions::get_domain_attr_dn($_GET['domain'], $entry);
    if ( $dn ) {
      //Object exists in LDAP, only an attribute has to be edited
      LDAP_functions::modify_attribute($value, $dn, 'add', 'ARecord');
    } else {
      // Need to create an complete object a la ldap_add
      $domain_dn = LDAP_functions::get_domain_dn($_GET['domain']);
      LDAP_functions::add_rdn_arecord($_GET['domain'], $domain_dn, $entry, $value);
    }
  }
}
?>

<p class="light">
Quick Add A Records for this Domain.
</p>
<table align="left" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td class="light"><textarea name="QUICK_ADD" cols="50" rows="6"></textarea></td>
    <td class="light" style="width: 24px" valign="top"><input type="image" name="SAVE_QUICK_ADD" value="SaveQuick" style="border-style: none;" src="<?=$conf['baseurl']?>/style/images/stock_ok.gif" alt="Save Quick Add" title="Save Quick Add"></td>
  </tr>
</table>
