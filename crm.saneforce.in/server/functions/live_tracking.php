<?php

function LiveTracking_Save( $RSF_Code ) {
    $data = $GLOBALS[ 'Data' ];
    $query = "SELECT sf_emp_id,Employee_Id FROM Mas_Salesforce WHERE Sf_Code='" . $RSF_Code . "'";
    $response = performQuery( $query );
    $empid = $response[ 0 ][ 'sf_emp_id' ];
    $employeeid = $response[ 0 ][ 'Employee_Id' ];
    for ( $ik = 0; $ik < count( $data ); $ik++ ) {
        $sql = "insert into tbTrackLoction(SF_code,Emp_Id,Employee_Id,DtTm,Lat,Lon,Addr,Auc,EMod,Battery,SF_Mobile,updatetime,IsOnline) select '$RSF_Code ','$empid','$employeeid','" . $data[ $ik ][ 'time' ] . "','" . $data[ $ik ][ 'Latitude' ] . "','" . $data[ $ik ][ 'Longitude' ] . "','" . $data[ $ik ][ 'Address' ] . "','','Apps','" . $data[ $ik ][ 'Battery' ] . "','" . $data[ $ik ][ 'Mobile' ] . "',getdate(),'" . $data[ $ik ][ 'IsOnline' ] . "'";
        performQuery( $sql );
    }
    $result = array();
    $result[ 'success' ] = true;
    outputJSON( $result );
}

function Get_SF_Track() {
    $query = "SELECT Sf_Code,Sf_Name,SF_Mobile FROM Mas_Salesforce WHERE Reporting_To_SF='" . $SF_Code . "'";
    outputJSON( performQuery( $query ) );
}
?>