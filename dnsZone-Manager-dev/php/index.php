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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>dnsZone-Manager</title>
	<link href="<?echo $conf['baseurl']?>/style/style.css" rel="stylesheet" type="text/css">
	<script language="javascript" type="text/javascript" src="<?echo $conf['baseurl']?>/scripts/TreeMenu.js"></script>
</head>

<body style="padding: 0px; margin: 0px;">
<table class="topbar">
<tr>
  <td align="left" valign="top" style="width: 300px;">
    <span class="header">dnsZone Manager</span><br>
    <span style="color: #FFFFFF">Version 0.1</span>
  <td align="center" valign="center"> <!-- menubar -->
    <div class="buttons">
      <a href="<?echo $conf['baseurl'];?>/dnsmgr/domain-new.php?<?echo Session::getSID();?>" target="_NEW" class="menuitem">
      <div class="button"><img src="<?echo $conf['images'];?>/emblem_web36.gif" border="0" alt="New Domain"><br>New Domain</a>
    </div>
  </td>
  <td align="right" style="width: 200px"> <!-- logout -->
    <div class="buttons">
      <a href="<?echo $conf['baseurl'];?>/login.php?<?echo Session::getSID();?>" class="menuitem">
      <img src="<?echo $conf['images'];?>/stock_quit36.gif" border="0" alt="Logoff"><br>Logoff</a>
    </div>
  </td>
</tr>
</table>
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
<!--        <td align="center"><p class="light"><a href="<?echo $conf['baseurl'];?>/dnsmgr/domain-new.php?<?echo Session::getSID();?>" target="_NEW" class="menuitem"><img src="<?echo $conf['images'];?>/emblem_web36.gif" border="0" alt="New Domain"><br>New Domain</a></p></td>
        <td align="center"><p class="light"><a href="<?echo $conf['baseurl'];?>/login.php?<?echo Session::getSID();?>" class="menuitem"><img src="<?echo $conf['images'];?>/stock_quit36.gif" border="0" alt="Logoff"><br>Logoff</a></p></td>
      </tr>
    </table>
  </td>
</tr>
</table
-->
<br/>
  
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

<br>

<table class="bottombar">
<tr>
  <td align="left">&copy; 2004, Tim Weippert</td>
  <td align="right">	<a href="license.php" class="bottommenuitem">License</a> | 
	  		<a href="license.php" class="bottommenuitem">Dokumentation</a> |
			<a href="credits.php" class="bottommenuitem">Credits</a>
   </td>
</tr>
</table>
</body>
</html>
