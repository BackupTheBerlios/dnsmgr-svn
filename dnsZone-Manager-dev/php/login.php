<?php
define('FRAMEWORK_BASE', dirname(__FILE__));
include_once FRAMEWORK_BASE . '/lib/basic.php';
include_once FRAMEWORK_BASE . '/lib/session.php';
include_once FRAMEWORK_BASE . '/lib/auth.php';
include_once FRAMEWORK_BASE . '/config/mainconfig.php';

Session::initSession();
if (Auth::checkAuth()) {
	Session::refreshSession();
}

if (isset($_POST['dologin']) && !Auth::checkAuth()) {
  $auth = Auth::getAuthObject($conf['auth']['type'], $conf['auth']['params']);
  if ($auth->authenticateLDAP($_POST['user'], $_POST['pass'])) {
    header('Location: index.php?'.session_name().'='.session_id());
  } else {
	Session::delSession();
        //header('Location: '.$conf['baseurl'].'/');
	header('Location: index.php?login_err=1');
  }
} else {
  Session::delSession();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>dnsZone-Manager</title>
	<link href="<?echo $conf['baseurl']?>/style/style.css" rel="stylesheet" type="text/css" />
</head>

<body style="padding: 0px; margin: 0px;" onload="inputfocus();">
<body style="padding: 0px; margin: 0px;">
<table class="topbar">
<tr>
  <td align="left" valign="top" style="width: 300px;">
    <span class="header">dnsZone Manager</span><br>
    <span style="color: #FFFFFF">Version 0.1</span>
  </td>
</tr>
</table>

<br>
<br>
<br>

<script language="JavaScript" type="text/javascript" src="<?echo $conf['baseurl']?>/scripts/general.js"></script>
<form action="login.php" method="post" name="login">
<input type="hidden" name="dologin" value="true">

<table align="center" cellpadding="0" cellspacing="0" width="400">
<tr>
<td>
<fieldset><legend>Login</legend>
<table align="center" cellpadding="0" cellspacing="0" width="319">
<!--
<tr>
	<td align="center" colspan="2"><img src="<?echo $conf['baseurl']?>/style/images/login_header.jpg" alt=""></td>
</tr>
-->
<? if ( $_GET['login_err'] == 1) { ?>
  <tr><td>&nbsp;</td></tr>
  <tr>
    <td align="center" colspan="2" class="lighterr"><p class="lighterr">Anmeldung fehlgeschlagen, bitte versuchen Sie es nochmals.</p></td>
  </tr>
<?}?>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tr>
	<td align="right" class="light"><b>Benutzername</b>&nbsp;&nbsp;</td>
	<td align="left"><input type="text" tabindex="1" name="user" value=""></td>
</tr>
<tr>
	<td align="right" class="light"><b>Passwort</b>&nbsp;&nbsp;</td>
	<td align="left"><input type="password" tabindex="2" name="pass"></td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td align="left" class="light"><input type="submit" class="button" name="button" tabindex="4" value="Anmelden"></td>
</tr>
</table>
</form>
</fieldset>
</td>
</tr>
</table>

<br>
<br>
<br>
<br>
<br>

<table class="bottombar">
<tr>
  <td align="left">&copy; 2004, Tim Weippert</td>
  <td align="right"><a href="license.php" class="bottommenuitem">License</a> <a href="credits.php" class="bottommenuitem">Credits</a></td>
</tr>
</table>
</body>
</html>
<?}?>
