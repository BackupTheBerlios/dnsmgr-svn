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

  function increase_serial($Serial) {
    // First strip of last two digits (these are only for daily counters)
    $serial_day_counter = substr($Serial, -2);
    $serial_date_part = substr($Serial, 0, (strlen($Serial) - 2) );
    
    $cur_date = date("Ymd");

    if ( $serial_day_counter == 99 ) { 
      // 99 changes at one day ... thats enough!!!
      // Stay at 99 and wait for next day!
      return $Serial; 
    }
    
    if ( "$serial_date_part" == "$cur_date" ) {
      $serial_day_counter++;
      if ($serial_day_counter <  10 ) {
        $serial_day_counter = "0".$serial_day_counter;
      }
      $Serial = $serial_date_part . $serial_day_counter ;
    } else if ( $serial_date_part < $cur_date ) {
      $Serial = $cur_date . "00";
    }
    
    // If the Serial_date_part is bigger than current day
    // something went wrong and we give back the original Serial without
    // modification
    
    return $Serial;
  }
}
?>
