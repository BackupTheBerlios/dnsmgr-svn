<?php
define('FRAMEWORK_BASE', dirname(__FILE__));
define('APP_BASE', dirname(__FILE__));

include_once FRAMEWORK_BASE . '/lib/basic.php';
include_once FRAMEWORK_BASE . '/lib/session.php';
include_once FRAMEWORK_BASE . '/lib/auth.php';
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

Session::initSession();

if (!Auth::checkAuth()) {
	Session::delSession();
	//header('Location: login.php');
	if ( isset($_GET['login_err']) ) {
	  header('Location: login.php?login_err=1');
	} else {
	  header('Location: login.php?xxx');
	}
} else {
	if (!Session::checkSession()) {
		Session::delSession();
		header('Location: login.php');
	} else {
		Session::refreshSession();
	}
}

$APP_NAME = 'dnsmgr';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>dnsZone-Manager</title>
	<link href="<?echo $conf['baseurl']?>/style/style.css" rel="stylesheet" type="text/css">
	<script language="javascript" type="text/javascript" src="<?echo $conf['baseurl']?>/scripts/TreeMenu.js"></script>
</head>

<body>
<table align="center" border="0" width="100%" height="50" class="brightblue">
<tr>
  <td align="center" class="brightblue" width="100%" height="50">
    <table border="0">
      <tr>
      <td><span class="header">dnsZone Manager</span></td>
      <?
      /*foreach (array_keys($appmenu[$APP_NAME]) as $submenu) {
      
        if ( $appmenu[$APP_NAME][$submenu]['status'] == 'active' ) {
          echo "<td align=\"center\"><p class=\"light\"><a href=\"".
                $conf['baseurl']."/index.php?".Session::getSID()."&sub=".$submenu."&action=".
                $appmenu[$APP_NAME][$submenu]['template'].
                "\" class=\"menuitem\"><img src=\"".$applications[$APP_NAME]['appbase']."/images/".
                $appmenu[$APP_NAME][$submenu]['icon']."\" border=\"0\"><br>";
          echo $appmenu[$APP_NAME][$submenu]['name'];
          echo "</p></td><td>&nbsp;&nbsp;</td>";
        }
      }*/
      ?>
        <td align="center"><p class="light"><a href="<?echo $conf['baseurl'];?>/login.php?<?echo Session::getSID();?>" class="menuitem"><img src="<?echo $conf['images'];?>/exit_icon_24.gif" border="0"><br>Logoff</a></p></td>
      </tr>
    </table>
  </td>
</tr>
</table>
<br/><br/> 
  
<?
// If an action is set, look for an php File with the action name and include
// it here
/*if ( $_GET['action'] ) { 
  ?>
  <center><span class="menuheader"><?echo $appmenu[$_GET['app']][$_GET['sub']]['name'];?></span><br></center> 
  <br style="font-size:5pt;">
  <?
  include APP_BASE."/dnsmgr/".$_GET['action'].".php";
}*/
  include APP_BASE."/dnsmgr/doit.php";
?>

</body>
</html>
