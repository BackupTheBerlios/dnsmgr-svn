<? 
// Set Intialize Informations
define('APP_BASE', dirname(__FILE__));

include_once FRAMEWORK_BASE . '/lib/basic.php';
include_once FRAMEWORK_BASE . '/lib/session.php';
include_once FRAMEWORK_BASE . '/lib/auth.php';
include_once FRAMEWORK_BASE . '/lib/ldap.php';
include_once FRAMEWORK_BASE . '/lib/dnsmgr.php';
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

Session::initSession();
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
?>

<span class="menuheader">Domain: <?=$_GET['domain']?></span>

<?
$zone = LDAP_functions::my_ldap_search('(& (zonename='.$_GET['domain'].') (relativedomainname=@))', 
                                       '', 
				       '*');
if (is_array($zone) && (count($zone) > 1)) {
  // Should get only one entry!!!! count gives 2 :)
  if ( $conf['debug'] == 1 ) {
    print "<br>Zoneentries: ".count($zone)."<br>";
  }
  // extract SOA
  $soa = DNSMGR::split_soa($zone[0]['soarecord'][0]);
  
  // Look for an special aRecord ... only one, please!
  if ( $zone[0]['arecord'][0] ) {
    $domain_arecord = $zone[0]['arecord'][0];
  }

  // Information to Form (SOA)
?>
  <form action=<? echo $conf['baseurl']."/index.php?".Session::getSID()."&domain=".$_GET['domain']?> method="post" name="SOA">
  <p class="light">
    The SOA Entry of this Domain. <br>
    (Note: the serial can't be edited directly, it will be generated automatically!)
  </p>
  <table align="left" cellpadding="0" cellspacing="0" width="500">
    <tr>
      <td class="light" width="150">MName (Master)</td>
      <td align="left"><input type="text" tabindex="1" name="MNAME" value="<?=$soa['MNAME']?>"></td>
    </tr>
    <tr>
      <td class="light">RName (e-Mail)</td><td><input type="text" size="30" maxlength="40" tabindex="1" name="RNAME" value="<?=$soa['RNAME']?>"></td>
    </tr>
    <tr>
      <td class="light">Serial</td><td><input type="text" tabindex="1" name="SERIAL" value="<?=$soa['SERIAL']?>" readonly ></td>
    </tr>
    <tr>
      <td class="light">Refresh</td><td><input type="text" tabindex="1" name="REFRESH" value="<?=$soa['REFRESH']?>"></td>
    </tr>
    <tr>
      <td class="light">Retry</td><td><input type="text" tabindex="1" name="RETRY" value="<?=$soa['RETRY']?>"></td>
    </tr>
    <tr>
      <td class="light">Expire</td><td><input type="text" tabindex="1" name="EXPIRE" value="<?=$soa['EXPIRE']?>"></td>
    </tr>
    <tr>
      <td class="light">Minimum TTL</td><td><input type="text" tabindex="1" name="MINIMUM" value="<?=$soa['MINIMUM']?>"></td>
    </tr>
  </table>
  </form>
<?
}
?>
