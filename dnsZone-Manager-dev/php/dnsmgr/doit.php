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

if ( $conf['debug'] == 1 ) {
  print "UserID:".$_SESSION['__auth']['userID']."<BR>";
  print "APP_BASE: ".APP_BASE."<BR>";
  print "FRAMEWORK_BASE: ".FRAMEWORK_BASE."<BR>";
}

global $conf;
global $applications;

// use TreeMenu
require_once(FRAMEWORK_BASE.'/lib/TreeMenu.php');
$icon = 'stock_open16.gif';
$level_count = 0;
$menu  = new HTML_TreeMenu();
$root = new HTML_TreeNode(array('text' => "dnsZone-Manager",
                                 'icon' => $icon));

$top_level = LDAP_functions::my_ldap_search('objectclass=dcObject', '', array( "dc" ), 'one', 'dc');

if (is_array($top_level) && (count($top_level) > 1)) { 
  foreach ($top_level as $value) {
    if ( $value['dc'][0] ) {
      
      $level[$level_count] = &$root->addItem( new HTML_TreeNode(array('text' => $value['dc'][0],
      					                             'icon' => $icon)));
      
      $domain_level = LDAP_functions::my_ldap_search('(& (objectclass=domain) (description=ActiveDomain*))', 
                                                     $value['dn'],
						     array( "*" ));
      if ( $conf['debug'] == 1 ) {
        print "Domain Level: ".count($domain_level)." is array? ".is_array($domain_level)."<br/>";
      }
      
      if ( is_array($domain_level) && count($domain_level) > 1 ) { 
        foreach ( $domain_level as $domain ) {
	  if ( $domain['dn'] ) {
	    if ( $conf['debug'] == 1 ) {
              print "Domain found: ".$domain['associateddomain'][0]." DN: ".$domain['dn']." Level: ".$level[$level_count]."<br>";
	    }
            $level[$level_count]->addItem( new HTML_TreeNode(array('text' => $domain['associateddomain'][0],
	                                                           'link' => $conf['baseurl']."/index.php?".Session::getSID()."&domain=".$domain['associateddomain'][0],
      					                           'icon' => 'emblem_web16.gif')));
	  }
	}
      }
      
      $level_count++;
    }
  }
}
/* 
    $node1->addItem(new HTML_TreeNode(array('text' => "Second level, item 3",
                                            'link' => "test.php",
                                            'icon' => $icon)));
*/    
    $menu->addItem($root);
    // Create the presentation class
    $treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => $conf['baseurl'].'/style/images/',
                                                      'defaultClass' => 'treeMenuBold'));
    //$listBox  = &new HTML_TreeMenu_Listbox($menu, array('linkTarget' => '_self'));
?>

<table border="0" width="100%">
<tr>
  <td align="left" valign="top" width="250">
<!--    <div>-->
      <?$treeMenu->printMenu()?>
      <!--
      <br/>
      <?//$listBox->printMenu()?>
      -->
<!--    </div>-->
  </td>
  <td align="left" valign="top">
<!--    <div>-->
      <? 
      if ( $_GET['domain'] ) {
        include APP_BASE."/dnsmgr/domain.php";
      } 
      ?> 
<!--    </div>-->
  </td>
</tr>
</table>
