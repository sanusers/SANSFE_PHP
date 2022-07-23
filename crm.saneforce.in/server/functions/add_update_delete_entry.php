<?php

function add_entry() {
    $sfCode = $_GET[ 'sfCode' ];
    $div = $_GET[ 'divisionCode' ];
    $MSL = $_GET[ 'Msl_No' ];
    $divs = explode( ",", $div . "," );
    $Owndiv = ( string ) $divs[ 0 ];
    $data = $GLOBALS['Data'];
    $today = date( 'Y-m-d 00:00:00' );
    $temp = array_keys( $data[ 0 ] );
    $vals = $data[ 0 ][ $temp[ 0 ] ];

    $HeaderId = ( isset( $_GET[ 'Head_id' ] ) && strlen( $_GET[ 'Head_id' ] ) == 0 ) ? null : $_GET[ 'Head_id' ];
    if ( $HeaderId != null ) {
        $query = "EXEC Delete_reject_dcr '$HeaderId'";
        performQuery( $query );
    }

    $sql = "SELECT Employee_Id,case sf_type when 1 then 'MR' else 'MGR' End SF_Type FROM Mas_Salesforce_One where SF_code='" . $sfCode . "'";
    $result = performQuery( $sql );
    $IdNo = ( string )$result[ 0 ][ 'Employee_Id' ];
    $SFTyp = ( string )$result[ 0 ][ 'SF_Type' ];
    switch ( strtolower( $temp[ 0 ] ) ) {
        case "tbmydayplan":
            include 'mydayplan.php';
			SaveMyDayPlan($sfCode, $Owndiv, $today);
            break;
        case "checkin":
			include 'checkin_checkout.php';
            CheckIn();
            break;
        case "checkout":
			include 'checkin_checkout.php';
            CheckOut();
            break;
        case "tp_attendance":
			include 'checkin_checkout.php';
            TPAttendance();
            break;
        case "chemists_master":
            include 'dcr_masters_save.php';
			Chemist_Master($sfCode, $vals);
            break;
        case "expense_miscellaneous":
			include 'functions/expense.php';
            MiscellaneousExpense($sfCode, $vals);
            break;
        case "unlisted_doctor_master":
			include 'dcr_masters_save.php';
            UnListed_Doc_Master($sfCode, $vals);
            break;
        case "quiz_results":
           	include 'quiz.php';
			Quiz_Result();
            break;
        case "mcl_details":
            include 'dcr_masters_save.php';
			Doctor_Master($data[ 0 ][ 'MCL_Details' ]);
            break;
        case "tbRCPADetails":
            $sql = "insert into tbRCPADetails select '" . $sfCode . "','" . date( 'Y-m-d H:i:s' ) . "'," . $vals[ "RCPADt" ] . "," .
            $vals[ "ChmId" ] . "," . $vals[ "DrId" ] . "," . $vals[ "CmptrName" ] . "," . $vals[ "CmptrBrnd" ] . "," . $vals[ "CmptrPriz" ] . "," .
            $vals[ "ourBrnd" ] . "," . $vals[ "ourBrndNm" ] . "," . $vals[ "Remark" ] . ",'" . $div . "'," . $vals[ "CmptrQty" ] . "," . $vals[ "CmptrPOB" ] . "," . $vals[ "ChmName" ] . "," . $vals[ "DrName" ] . "";
            performQuery( $sql );
            break;
        case "tbRemdrCall":
            $sql = "SELECT isNull(max(cast(replace(RwID,'RC/" . $IdNo . "/','') as numeric)),0)+1 as RwID FROM tbRemdrCall where RwID like 'RC/" . $IdNo . "/%'";
            $tRw = performQuery( $sql );
            $pk = ( int )$tRw[ 0 ][ 'RwID' ];

            $sql = "insert into tbRemdrCall(RwID,SF_Code,CallDate,ListedDrCode,WWith,WWithNm,Prods,ProdsNm,Remarks,location,Division_Code) select 'RC/" . $IdNo . "/" . $pk . "','" . $sfCode . "','" . date( 'Y-m-d H:i:s' ) . "','" . $vals[ "Doctor_ID" ] . "','" . $vals[ "WWith" ] . "','" . $vals[ "WWithNm" ] . "','" . $vals[ "Prods" ] . "','" . $vals[ "ProdsNm" ] . "','" . $vals[ "Remarks" ] . "','" . $vals[ "location" ] . "','" . $div . "'";
            performQuery( $sql );
            break;
        case "expense":
            include 'expense.php';
            Expense();
            break;
        case "TourPlanSubmit":
            $month = $_GET[ 'month' ];
            $year = $_GET[ 'year' ];
            $sql = "update Trans_TP_One set Change_Status=1, Confirmed=1 where Tour_Month=$month and Tour_Year=$year and Sf_Code='$sfCode'";
            performQuery( $sql );
            $resp[ "success" ] = true;
            outputJSON( $resp );
            break;
        case "DevApproval":
            $slno = $_GET[ 'slno' ];
            $sql = "update DCR_MissedDates set status=4 where sl_no='$slno'";
            performQuery( $sql );
            break;
        case "TPApproval":
            include 'approval_reject.php';
            TP_Approval();
            break;
        case "TPReject":
            include 'approval_reject.php';
            TP_Reject($Owndiv);
            break;
        case "LeaveApproval":
            include 'approval_reject.php';
            Leave_Approval();
            break;
        case "LeaveReject":
            include 'approval_reject.php';
            Leave_Reject( $vals[ 'Sf_Code' ] );
            break;
        case "LeaveForm":
			include 'leave.php';
            LeaveForm($sfCode, $Owndiv. $vals);
            break;
        case "RCPAEntry":
            $mData = json_decode( $_POST[ 'data' ], true );
            $RCPADt = date( 'Y-m-d 00:00:00', strtotime( $today ) );
            $ARCD = '';
            $ARDCd = 0;
            $sql = "{call  svDCRMain_App(?,?,'-1','',?,'',?)}";
            $params = array( array( $sfCode, SQLSRV_PARAM_IN ),
                array( $today, SQLSRV_PARAM_IN ),
                array( $Owndiv, SQLSRV_PARAM_IN ),
                array( & $ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_VARCHAR( 50 ) ) );
            performQueryWP( $sql, $params );
            include 'dcr_save.php';
            SaveRCPAEntry( $ARCd, $ARDCd, $mData[ 0 ][ "RCPAEntry" ], $RCPADt );
            break;
        case "Order_Product":
			include 'order_management.php';
            Order_Management();
            break;
        case "DCRApproval":
            include 'approval_reject.php';
            DCR_Approval();
            break;
        case "DCRReject":
            include 'approval_reject.php';
            DCR_Reject();
            break;
        case "DCRTPDevReason":
			include 'approval_reject.php';
            DCRTPDevReason($sfCode);
            break;
        case "Survey_App":
            include 'survey.php';
			Survey_App();
            break;
        case "Activity_Report_APP":
            include 'dcr_save.php';
            DCR_Save();
            break;
    }
    $resp[ "success" ] = true;
    echo json_encode( $resp );
}

function update_entry() {
    $today = date( 'Y-m-d 00:00:00' );
    $data = json_decode( $_POST[ 'data' ], true );
    $SFCode = ( string )$data[ 0 ][ 'Activity_Report' ][ 'SF_code' ];
    $sql = "select SF_Code from vwActivity_report where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$today' as datetime)";
    $result = performQuery( $sql );
    if ( count( $result ) < 1 ) {
        $result = array();
        $result[ 'success' ] = false;
        $result[ 'type' ] = 2;
        $result[ 'msg' ] = 'No Call Report Submited...';
        outputJSON( $result );
        exit;
    }
    $Remarks = ( string )$data[ 0 ][ 'Activity_Report' ][ 'remarks' ];
    $HalfDy = ( string )$data[ 0 ][ 'Activity_Report' ][ 'HalfDay_FW_Type' ];

    $sql = "EXEC DCRUpdateEntry '".$SFCode."','".$Remarks."','".$HalfDy."','".$today."'";
    performQuery( $sql );
    $response[ "success" ] = true;
    echo json_encode( $response );
}

function delete_entry() {
    $data = json_decode( $_POST[ 'data' ], true );
    $arc = ( isset( $_GET[ 'arc' ] ) && strlen( $_GET[ 'arc' ] ) == 0 ) ? null : $_GET[ 'arc' ];
    $amc = ( isset( $_GET[ 'amc' ] ) && strlen( $_GET[ 'amc' ] ) == 0 ) ? null : $_GET[ 'amc' ];
    if ( !is_null( $amc ) ) {
        $sql = "EXEC DCRDeleteEntry '" . $amc . "', '" . $arc . "'";
        performQuery( $sql );
    }
}
?>