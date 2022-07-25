<?php

function Travel_Distance( $SF_Code ) {
    $data = array_keys( $GLOBALS[ 'Data' ][ 0 ] );
    $vals = $GLOBALS[ 'Data' ][ 0 ][ $data[ 0 ] ];
    $query = "select id from distance_Travelled where activity_date = '" . $vals[ "date" ] . "'";
    $idNo = performQuery( $query );
    $idValue = $idNo[ 0 ][ 'id' ];
    if ( count( $idNo ) > 0 ) {
        $query = "update distance_Travelled set travel_km = '" . $vals[ "km" ] . "' , remarks = '" . $vals[ "remarks" ] . "' , update_time = '" . $vals[ "submitted_Time" ] . "' where id ='$idValue'";
        performQuery( $query );
    } else {
        $query = "insert into distance_Travelled (sfName,sfCode,divisionCode,remarks,travel_km,emp_id,activity_date,submitted_time) select '" . $vals[ "sfName" ] . "','" . $SF_Code . "','" . $vals[ "divisionCode" ] . "','" . $vals[ "remarks" ] . "','" . $vals[ "km" ] . "', sf_emp_id ,'" . $vals[ "date" ] . "','" . $vals[ "submitted_Time" ] . "' from Mas_Salesforce where Sf_Code = '$SF_Code'";
        performQuery( $query );
    }
    $results[ 'success' ] = true;
    outputJSON( $results );
}
?>