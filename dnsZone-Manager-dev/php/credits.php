<?
define('FRAMEWORK_BASE', dirname(__FILE__));
include_once FRAMEWORK_BASE . '/config/mainconfig.php';
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

<table align="center" cellpadding="0" cellspacing="0" width="400">
<tr>
<td>
<fieldset><legend>Credits</legend>
<table align="center" cellpadding="0" cellspacing="0" width="319">
<tr>
    <td class="light">Thanks to <b>Müffel</b></td>
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
