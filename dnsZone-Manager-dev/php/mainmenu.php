<table border="0">
<tr>
<?
//foreach($applications as $app) {

  if ( $conf['debug'] == 1 ) {
    print "UserID:".$_SESSION['__auth']['userID']."<BR>";
    print "UserDN:".$_SESSION['__auth']['userDN']."<BR>";
    print "UserCN:".$_SESSION['__auth']['userCN']."<BR>";
    print "SID:".$_SESSION['__auth']['SID']."<BR>";
    print "lastaction:". $_SESSION['__auth']['lastaction']."<BR>";
  }
  
foreach (array_keys($applications) as $app) {
  if ($applications[$app]['status'] == 'active') { 

  
    if ( $applications[$app]['allow_only_admins'] == 'true' ) { 
      // Check if is Admin Only and if an Admin requests this
      // Allow isAdmin and isCustomAdmin!!!
      if ( ( $_SESSION['__auth']['isAdmin'] == 'YES' ) ) {
        echo "<td align=\"center\"><p class=\"light\"><a href=\"".
	     $conf['baseurl']."/index.php?".Session::getSID()."&app=".$app.
	     "\" class=\"menuitem\"><img src=\"".$applications[$app]['appbase'].
	     $applications[$app]['icon']."\" border=\"0\"><br>";
        echo $applications[$app]['appname'];
        echo "</p></td><td>&nbsp;&nbsp;</td>";
      }
    } else {
      echo "<td align=\"center\"><p class=\"light\"><a href=\"".
           $conf['baseurl']."/index.php?".Session::getSID()."&app=".$app.
	   "\" class=\"menuitem\"><img src=\"".$applications[$app]['appbase'].
	   $applications[$app]['icon']."\" border=\"0\"><br>";
      echo $applications[$app]['appname'];
      echo "</p></td><td>&nbsp;&nbsp;</td>";
    }
  }
}
?>
<td align="center"><p class="light"><a href="<?echo $conf['baseurl'];?>/login.php?<?echo Session::getSID();?>" class="menuitem"><img src="<?echo $conf['images'];?>/exit_icon_24.gif" border="0"><br>Logoff</a></p></td>
</tr>
</table>
