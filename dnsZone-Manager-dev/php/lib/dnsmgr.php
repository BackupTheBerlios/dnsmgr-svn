<?php

include_once FRAMEWORK_BASE . '/config/mainconfig.php';

class DNSMGR {

  function split_soa($SOA) {
    $pieces = explode(" ", $SOA);

    $result['MNAME'] = $pieces[0];
    $result['RNAME'] = $pieces[1];
    $result['SERIAL'] = $pieces[2];
    $result['REFRESH'] = $pieces[3];
    $result['RETRY'] = $pieces[4];
    $result['EXPIRE'] = $pieces[5];
    $result['MINIMUM'] = $pieces[6];

    return $result;
  }
}
?>
