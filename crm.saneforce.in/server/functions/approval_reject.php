<?php
$Date_Format1 = date( 'Y-m-d' );
$Date_Format2 = date( 'Y-m-d H:i' );

function TP_Approval() {
    $code = $_GET[ 'code' ];
    $month = $_GET[ 'month' ];
    $year = $_GET[ 'year' ];
    global $conn, $NeedRollBack;
    if ( sqlsrv_begin_transaction( $conn ) === false ) {
        die( print_r( sqlsrv_errors(), true ) );
    }
    $NeedRollBack = false;
    $sql = "insert into Trans_TP(Division_Code,SF_Code,Worked_With_SF_Code,Worked_With_SF_Name,Tour_Date,Tour_Month,Tour_Year,WorkType_Code_B,Worktype_Name_B,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,Objective,Confirmed,Confirmed_Date,Rejection_Reason,Territory_Code1,Territory_Code2,Territory_Code3,TP_Sf_Name,TP_Approval_MGR,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,Submission_date,Change_Status)select Division_Code,SF_Code,Worked_With_SF_Code,Worked_With_SF_Name,Tour_Date,Tour_Month,Tour_Year,WorkType_Code_B,Worktype_Name_B,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,Objective,1,GETDATE(),Rejection_Reason,Territory_Code1,Territory_Code2,Territory_Code3,TP_Sf_Name,TP_Approval_MGR,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,Submission_date,1 from Trans_TP_One where sf_Code='$code' and Tour_Month=$month and Tour_Year=$year";
    $trs = performQuery( $sql );
    if ( $NeedRollBack == true ) {
        sqlsrv_rollback( $conn );
        $result[ "success" ] = false;
    } else {
        sqlsrv_commit( $conn );
        $result[ "success" ] = true;
    }
    if ( count( $trs ) > 0 ) {
        $sql = "delete from Trans_TP_One where sf_Code='$code' and Tour_Month=$month and Tour_Year=$year";
        performQuery( $sql );
        if ( $month == "12" ) {
            $year = $year + 1;
            $month = 1;
        } else
            $month = $month + 1;
        $date = $year . '-' . $month . '-01';
        $sql = "update mas_salesforce_dcrtpdate set Last_TP_Date='$date' where sf_Code='$code' and '$date'>Last_TP_Date";
        performQuery( $sql );
    }
    $resp[ "success" ] = true;
    outputJSON( $resp );
}

function TP_Reject( $Owndiv ) {
    $month = $_GET[ 'month' ];
    $year = $_GET[ 'year' ];
    $sql = "insert into TP_Reject_B_Mgr(SF_Code,Tour_Month,Tour_Year,Reject_date,Division_Code,Rejection_Reason) select '" . $code . "', $month, $year,'" . $Date_Format2 . "',$Owndiv," . $vals[ 'reason' ] . "";
    performQuery( $sql );

    $sql = "update Trans_TP_One set Change_Status=2,Confirmed=0,Rejection_Reason=" . $vals[ 'reason' ] . " where Tour_Month=$month and Tour_Year=$year and Sf_Code='$code'";
    performQuery( $sql );
    $resp[ "success" ] = true;
    outputJSON( $resp );
}

function Leave_Approval() {
    $sql = "exec iOS_svLeaveAppRej  '" . $_GET[ 'leaveid' ] . "','0','','" . $RSF . "','Apps'";
    performQuery( $sql );
    $resp[ "success" ] = true;
    outputJSON( $resp );
}

function Leave_Reject( $SF ) {
    $LvID = ( string )$_GET[ 'leaveid' ];
    $query = "exec iOS_svLeaveAppRej  '" . $LvID . "','1','" . $vals[ 'reason' ] . "','" . $SF . "'";
    performQuery( $query );
    $result[ "success" ] = true;
    outputJSON( $result );
}

function DCR_Approval() {
    $date = date( 'Y-m-d', strtotime( str_replace( '/', '-', $_GET[ 'date' ] ) ) );
    $sql = "EXEC ApproveDCRByDt '" . $_GET[ 'code' ] . "','$date'";
    performQuery( $sql );
    $response[ "success" ] = true;
    outputJSON( $response );
}

function DCR_Reject() {
    $date = date( 'Y-m-d 00:00:00', strtotime( str_replace( '/', '-', $_GET[ 'date' ] ) ) );
    $sql = "EXEC App_DcrReject '" . $_GET[ 'code' ] . "','$date','" . $vals[ 'reason' ] . "'";
    performQuery( $sql );
    $response[ "success" ] = true;
    outputJSON( $response );
}

function DCRTPDevReason($sfCode) {
	$data = $GLOBALS['Data'];
    $temp = array_keys( $data[ 0 ] );
    $vals = $data[ 0 ][ $temp[ 0 ] ];
    $sql = "exec svDCRTPDevReason '$sfCode','".$vals[ 'wtype' ]."','". $vals[ 'clusterid' ]."','".$vals[ 'ClstrName' ]."','$Date_Format1','".$vals[ 'reason' ]."','".$vals[ 'status' ]."'";
    performQuery( $sql );
}
?>