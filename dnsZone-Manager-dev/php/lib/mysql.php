<?php

include_once FRAMEWORK_BASE . '/config/mainconfig.php';

class MySQL_functions {

	function get_entries($app, $db_sql) 
	{
	  global $conf, $applications;
	 
	  $conf['debug'] && print "Application: $app<br>";

	  /* Connecting, selecting database */
	  $link = mysql_connect($applications[$app]['DB_Host'],
	  			$applications[$app]['DB_User'],
				$applications[$app]['DB_Pass'])
	  	or die("Could not connect : " . mysql_error());

    	  $conf['debug'] && print "Connected successfully";

	  /* Selecting configured Database */
	  mysql_select_db($applications[$app]['DB_Name']) or die("Could not select database");
	 
	  /* SQL Query over configured Table or given SQL! */
	  if ( $db_sql && $db_sql != "" ) {
	    $query = $db_sql;
	  } else {
	    $query = "SELECT * FROM ".$applications[$app]['DB_Table'];
	  }
	  $result = mysql_query($query) or die("Query failed : " . mysql_error());
	  
	  $count = 1;
	  while ($line = mysql_fetch_assoc($result)) {
	    $conf['debug'] && print "Added $count to return array<BR>";
	    $return[$count] = $line;
	    $count++;
	  }
	  
	  /* Free resultset */
	  $result && mysql_free_result($result);
	  
	  /* Closing connection */
	  mysql_close($link);

	  /* Return an Array of Arrays with the result */
	  return $return;
	}
	
	function insert_entries($app, $db_sql) 
	{
	  global $conf, $applications;
	 
	  $conf['debug'] && print "Application: $app<br>";

	  /* Connecting, selecting database */
	  $link = mysql_connect($applications[$app]['DB_Host'],
	  			$applications[$app]['DB_User'],
				$applications[$app]['DB_Pass'])
	  	or die("Could not connect : " . mysql_error());

    	  $conf['debug'] && print "Connected successfully";

	  /* Selecting configured Database */
	  mysql_select_db($applications[$app]['DB_Name']) or die("Could not select database");
	  $query = $db_sql;
	  if ( $result = mysql_query($query) ) {
	    $return = 1;
	  } else { 
	    $return = 0;
	  }
	  
	  /* Free resultset */
	  //$result && mysql_free_result($result);
	  
	  /* Closing connection */
	  mysql_close($link);

	  /* Return Result */
	  return $return;
	}

	function update_entries($app, $db_sql)
	{
	  global $conf, $applications;
	 
	  $conf['debug'] && print "Application: $app<br>";

	  /* Connecting, selecting database */
	  $link = mysql_connect($applications[$app]['DB_Host'],
	  			$applications[$app]['DB_User'],
				$applications[$app]['DB_Pass'])
	  	or die("Could not connect : " . mysql_error());

    	  $conf['debug'] && print "Connected successfully";

	  /* Selecting configured Database */
	  mysql_select_db($applications[$app]['DB_Name']) or die("Could not select database");
	  $query = $db_sql;
	  if ( $result = mysql_query($query) ) {
	    $return = 1;
	  } else { 
	    $return = 0;
	  }
	  
	  /* Free resultset */
	  //$result && mysql_free_result($result);
	  
	  /* Closing connection */
	  mysql_close($link);

	  /* Return Result */
	  return $return;
	}

	function delete_entries($app, $db_sql)
	{
	  global $conf, $applications;
	 
	  $conf['debug'] && print "Application: $app<br>";

	  /* Connecting, selecting database */
	  $link = mysql_connect($applications[$app]['DB_Host'],
	  			$applications[$app]['DB_User'],
				$applications[$app]['DB_Pass'])
	  	or die("Could not connect : " . mysql_error());

    	  $conf['debug'] && print "Connected successfully";

	  /* Selecting configured Database */
	  mysql_select_db($applications[$app]['DB_Name']) or die("Could not select database");
	  $query = $db_sql;
	  if ( $result = mysql_query($query) ) {
	    $return = 1;
	  } else { 
	    $return = 0;
	  }
	  
	  /* Free resultset */
	  //$result && mysql_free_result($result);
	  
	  /* Closing connection */
	  mysql_close($link);

	  /* Return Result */
	  return $return;
	}
}
?>
