<? 
// Set Intialize Informations
define('APP_BASE', dirname(__FILE__));

include_once FRAMEWORK_BASE . '/lib/basic.php';
include_once FRAMEWORK_BASE . '/lib/session.php';
include_once FRAMEWORK_BASE . '/lib/auth.php';
include_once FRAMEWORK_BASE . '/lib/mysql.php';
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

Session::initSession();
if (Auth::checkAuth()) {
        Session::refreshSession();
}

// Set own Applicationname
$APP_NAME = $_GET['app'];

if ( $conf['debug'] == 1 ) {
  print "UserID:".$_SESSION['__auth']['userID']."<BR>";
  print "APP_NAME: ".$APP_NAME."<BR>";
  print "APP_BASE: ".APP_BASE."<BR>";
  print "FRAMEWORK_BASE: ".FRAMEWORK_BASE."<BR>";
}

global $conf;

?>

<table align="center" border="0" width="80%" height="50" class="brightblue">
<tr> 
    
<?
foreach (array_keys($appmenu[$APP_NAME]) as $submenu) {
  if ( $conf['debug'] == 1 ) {
    print "Submenu Found: ".$appmenu['$APP_NAME'][$submenu]['name']."<BR>";
  }

  if ( $appmenu[$APP_NAME][$submenu]['status'] == 'active' ) {
    echo "<td align=\"center\"><p class=\"light\"><a href=\"".
          $conf['baseurl']."/index.php?".Session::getSID()."&app=".$APP_NAME."&sub=".$submenu."&action=".
	  $appmenu[$APP_NAME][$submenu]['template'].
	  "\" class=\"menuitem\"><img src=\"".$applications[$APP_NAME]['appbase']."/images/".
	  $appmenu[$APP_NAME][$submenu]['icon']."\" border=\"0\"><br>";
    echo $appmenu[$APP_NAME][$submenu]['name'];
    echo "</p></td><td>&nbsp;&nbsp;</td>";
  }
}
?>
    
</tr>
</table>
<br/><br/> 
  
<?
// If an action is set, look for an php File with the action name and include
// it here
if ( $_GET['action'] ) { 
  ?>
  <center><span class="menuheader"><?echo $appmenu[$_GET['app']][$_GET['sub']]['name'];?></span><br></center> 
  <br style="font-size:5pt;">
  <?
  include APP_BASE."/".$_GET['action'].".php";
}
?>

