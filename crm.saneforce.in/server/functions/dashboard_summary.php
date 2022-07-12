<?php

function summary() {
  $SFCode = $_GET[ 'sfCode' ];
  $sql = "EXEC getCusVstDet '" . $SFCode . "'";
  $response_1 = performQuery( $sql );

  $sql = "EXEC getWTVstDet '" . $SFCode . "'";
  $response_2 = performQuery( $sql );

  $result = array();
  $result = [ $response_1, $response_2 ];
  return outputJSON( $result );
}
?>