<?php

/***************************************************************
 * General Tool Config
 **************************************************************/
// Debugging anschalten => 0 = aus, 1 = an!
$conf['debug'] = 0;

$conf['vdir'] = '/~weiti/dnsZone-Manager';
$conf['fsbase'] = '/home/weiti/public_html/dnsZone-Manager';

$conf['baseurl'] = 'http://intranet.topf-sicret.de'.$conf['vdir'];
$conf['images'] = $conf['vdir'].'/style/images';
$conf['applications'] = $conf['fsbase'];
$conf['appuri'] = $conf['baseurl'];
$conf['language'] = 'de_DE';
$conf['session_name'] = 'dnsmgr';
$conf['cache_limiter'] = 'nocache';
$conf['session_timeout'] = 3600;
$conf['session_maxlifetime'] = 3600;
$conf['session.save_path'] = '/tmp/';
$conf['session.sditransfer'] = 'url';

// Authentification Settings
$conf['auth']['type'] = 'ldap';
$conf['auth']['params']['host'] = 'localhost';
$conf['auth']['params']['port'] = '389';
$conf['auth']['params']['basedn'] = 'c=org';
$conf['auth']['params']['uid'] = 'uid';
$conf['auth']['params']['cn'] = 'cn';
$conf['auth']['params']['logfile'] = '/tmp/dnsmgr.log';

/***************************************************************
 * Applications 
 **************************************************************/

$applications['dnsmgr'] = array(
    'appname' => 'dnsZone-Manager',
    'appbase' => $conf['vdir'].'/dnsmgr',
    'appfsbase' => $conf['fsbase'].'/dnsmgr',
    'applogfile' => '/tmp/dnsmgr.log',
    'entrypoint' => 'index',
    'icon' => '/images/computer_icon_24.gif',
    'allow_guests' => true,
    'allow_only_admins' => false, 
    'status' => 'active', 
    'Backend_Type' => 'ldap', 
    'Backend_Host' => 'localhost', 
    'Backend_Base' => 'ou=DNS,o=topf-sicret,c=org',
    'Backend_Bind_DN' => 'cn=admin,c=org', 
    'Backend_Bind_DN_Passwd' => 'admin'
);

/***************************************************************
 * Menus
 **************************************************************/

//admin-tool Menu Config
$appmenu['dnsmgr'] = array(
	'1' => array(
		'name' => 'Zones',
		'template' => 'zones',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'2' => array(
		'name' => 'Domains',
		'template' => 'domains',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'3' => array(
		'name' => 'NameServer (NS)',
		'template' => 'nsserver',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'4' => array(
		'name' => 'MailServer (MX)',
		'template' => 'mxserver',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'5' => array(
		'name' => 'Views',
		'template' => 'views',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'6' => array(
		'name' => 'A Records',
		'template' => 'arecords',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'7' => array(
		'name' => 'CNAME Records',
		'template' => 'cnamerecords',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	),
	'8' => array(
		'name' => 'PTR Records',
		'template' => 'ptrrecords',
		'icon' => 'icon_run.gif',
		'type' => 'link',
		'status' => 'active'
	)
);

?>
