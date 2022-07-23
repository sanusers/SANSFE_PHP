<?php

function SaveMyDayPlan( $sfCode, $Owndiv, $today ) {
    $today = date( 'Y-m-d 00:00:00', strtotime( $today ) );
    if ( $vals[ "dcr_activity_date" ] != null && $vals[ "dcr_activity_date" ] != '' ) {
        $today = str_replace( "'", "", $vals[ "dcr_activity_date" ] );
    }
    $query = "insert into tbMyDayPlan select '" . $sfCode . "','" . $vals[ "sf_member_code" ] . "','$today','" . $vals[ "cluster" ] . "','" . $vals[ "remarks" ] . "','" . $Owndiv . "','" . $vals[ "wtype" ] . "','" . $vals[ "FWFlg" ] . "','" . $vals[ "ClstrName" ] . "','" . $vals[ "wtype_name" ] . "','$location','" . $vals[ "TpVwFlg" ] . "','" . $vals[ "TP_Doctor" ] . "','" . $vals[ "TP_DocCluster" ] . "','" . $vals[ "TP_Worktype" ] . "'";
    performQuery( $query );

    if ( str_replace( "'", "", $vals[ "FWFlg" ] ) != "F" ) {
        $query = "SELECT FWFlg, Confirmed FROM vwActivity_Report where SF_Code='" . $sfCode . "'  and cast(activity_date as datetime)=cast('$today' as datetime)";
        $result1 = performQuery( $query );
        if ( count( $result1 ) > 0 ) {
            delete_AR_entry( $sfCode, $vals[ "wtype" ], $today );
            $ARCd = "0";
            $sql = "{call  svDCRMain_App(?,?," . $vals[ "wtype" ] . ",'" . str_replace( "'", "", $vals[ "cluster" ] ) . "',?,'" . str_replace( "'", "", $vals[ "remarks" ] ) . "',?)}";


            $params = array( array( $sfCode, SQLSRV_PARAM_IN ),
                array( $today, SQLSRV_PARAM_IN ),
                array( $Owndiv, SQLSRV_PARAM_IN ),
                array( & $ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_VARCHAR( 50 ) ) );
            performQueryWP( $sql, $params );
        }
    }
}
?>