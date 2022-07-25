<?php
date_default_timezone_set( "Asia/Kolkata" );
$Date_Format5 = date( 'Y-m-d 00:00:00' );

function add_entry() {
    $data = $GLOBALS[ 'Data' ];
    $temp = array_keys( $data[ 0 ] );
    $vals = $data[ 0 ][ $temp[ 0 ] ];
    if ( isset( $_GET[ 'sfCode' ] ) ) {
        $SF_Code = $_GET[ 'sfCode' ];
    }
    if ( isset( $_GET[ 'rSF' ] ) ) {
        $RSF_Code = $_GET[ 'rSF' ];
    }
    if ( isset( $_GET[ 'divisionCode' ] ) ) {
        $Div_Code = isset( $_GET[ 'divisionCode' ] );
        $DivCode = explode( ",", $Div_Code . "," );
        $DivisionCode = ( string )$DivCode[ 0 ];
    }
    if ( isset( $_GET[ 'Msl_No' ] ) ) {
        $MSL = $_GET[ 'Msl_No' ];
    }
    $HeaderId = ( isset( $_GET[ 'Head_id' ] ) && strlen( isset( $_GET[ 'Head_id' ] ) ) == 0 ) ? null : isset( $_GET[ 'Head_id' ] );
    if ( $HeaderId != null ) {
        $query = "EXEC Delete_reject_dcr '$HeaderId'";
        performQuery( $query );
    }
    $sql = "SELECT Employee_Id, case sf_type when 1 then 'MR' else 'MGR' End SF_Type FROM Mas_Salesforce_One where SF_code='" . $SF_Code . "'";
    $result = performQuery( $sql );
    $IdNo = ( string )$result[ 0 ][ 'Employee_Id' ];
    $SFTyp = ( string )$result[ 0 ][ 'SF_Type' ];
    switch ( strtolower( $temp[ 0 ] ) ) {
        case "tbmydayplan":
            include 'mydayplan.php';
            SaveMyDayPlan( $SF_Code, $DivisionCode, $Date_Format5 );
            break;
        case "checkin":
            include 'checkin_checkout.php';
            CheckIn( $SF_Code, $DivisionCode, $vals );
            break;
        case "checkout":
            include 'checkin_checkout.php';
            CheckOut( $SF_Code, $vals );
            break;
        case "tp_attendance":
            include 'checkin_checkout.php';
            TPAttendance( $SF_Code, $DivisionCode, $vals );
            break;
        case "chemists_master":
            include 'dcr_masters_save.php';
            Chemist_Master( $SF_Code, $DivisionCode, $vals );
            break;
        case "expense_miscellaneous":
            include 'functions/expense.php';
            MiscellaneousExpense( $SF_Code, $vals );
            break;
        case "unlisted_doctor_master":
            include 'dcr_masters_save.php';
            UnListed_Doc_Master( $SF_Code, $vals );
            break;
        case "quiz_results":
            include 'quiz.php';
            Quiz_Result( $SF_Code, $DivisionCode );
            break;
        case "mcl_details":
            include 'dcr_masters_save.php';
            Doctor_Master( $data[ 0 ][ 'MCL_Details' ] );
            break;
        case "tbrcpadetails":
            $sql = "insert into tbRCPADetails select '" . $SF_Code . "','" . date( 'Y-m-d H:i:s' ) . "'," . $vals[ "RCPADt" ] . "," .
            $vals[ "ChmId" ] . "," . $vals[ "DrId" ] . "," . $vals[ "CmptrName" ] . "," . $vals[ "CmptrBrnd" ] . "," . $vals[ "CmptrPriz" ] . "," .
            $vals[ "ourBrnd" ] . "," . $vals[ "ourBrndNm" ] . "," . $vals[ "Remark" ] . ",'" . $div . "'," . $vals[ "CmptrQty" ] . "," . $vals[ "CmptrPOB" ] . "," . $vals[ "ChmName" ] . "," . $vals[ "DrName" ] . "";
            performQuery( $sql );
            break;
        case "tbremdrcall":
            $sql = "SELECT isNull(max(cast(replace(RwID,'RC/" . $IdNo . "/','') as numeric)),0)+1 as RwID FROM tbRemdrCall where RwID like 'RC/" . $IdNo . "/%'";
            $tRw = performQuery( $sql );
            $pk = ( int )$tRw[ 0 ][ 'RwID' ];

            $sql = "insert into tbRemdrCall(RwID,SF_Code,CallDate,ListedDrCode,WWith,WWithNm,Prods,ProdsNm,Remarks,location,Division_Code) select 'RC/" . $IdNo . "/" . $pk . "','" . $SF_Code . "','" . date( 'Y-m-d H:i:s' ) . "','" . $vals[ "Doctor_ID" ] . "','" . $vals[ "WWith" ] . "','" . $vals[ "WWithNm" ] . "','" . $vals[ "Prods" ] . "','" . $vals[ "ProdsNm" ] . "','" . $vals[ "Remarks" ] . "','" . $vals[ "location" ] . "','" . $div . "'";
            performQuery( $sql );
            break;
        case "expense":
            include 'expense.php';
            Expense();
            break;
        case "tourplansubmit":
            $month = $_GET[ 'month' ];
            $year = $_GET[ 'year' ];
            $sql = "update Trans_TP_One set Change_Status=1, Confirmed=1 where Tour_Month=$month and Tour_Year=$year and Sf_Code='$SF_Code'";
            performQuery( $sql );
            $resp[ "success" ] = true;
            outputJSON( $resp );
            break;
        case "devapproval":
            $slno = $_GET[ 'slno' ];
            $sql = "update DCR_MissedDates set status=4 where sl_no='$slno'";
            performQuery( $sql );
            break;
        case "tpapproval":
            include 'approval_reject.php';
            TP_Approval();
            break;
        case "tpreject":
            include 'approval_reject.php';
            TP_Reject( $DivisionCode, $vals );
            break;
        case "leaveapproval":
            include 'approval_reject.php';
            Leave_Approval( $RSF_Code );
            break;
        case "leavereject":
            include 'approval_reject.php';
            Leave_Reject( $vals[ 'Sf_Code' ], $vals[ 'reason' ] );
            break;
        case "leaveform":
            include 'leave.php';
            LeaveForm( $SF_Code, $DivisionCode, $vals );
            break;
        case "rcpaentry":
            $mData = json_decode( $_POST[ 'data' ], true );
            $RCPADt = date( 'Y-m-d 00:00:00', strtotime( $Date_Format5 ) );
            $ARCD = '';
            $ARDCd = 0;
            $sql = "{call  svDCRMain_App(?,?,'-1','',?,'',?)}";
            $params = array( array( $SF_Code, SQLSRV_PARAM_IN ),
                array( $Date_Format5, SQLSRV_PARAM_IN ),
                array( $DivisionCode, SQLSRV_PARAM_IN ),
                array( & $ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_VARCHAR( 50 ) ) );
            performQueryWP( $sql, $params );
            include 'dcr_save.php';
            SaveRCPAEntry( $SF_Code, $DivisionCode, $ARCd, $ARDCd, $mData[ 0 ][ "RCPAEntry" ], $RCPADt );
            break;
        case "order_product":
            include 'order_management.php';
            Order_Management();
            break;
        case "dcrapproval":
            include 'approval_reject.php';
            DCR_Approval();
            break;
        case "dcrreject":
            include 'approval_reject.php';
            DCR_Reject( $vals );
            break;
        case "dcrtpdevreason":
            include 'approval_reject.php';
            DCRTPDevReason( $SF_Code, $vals );
            break;
        case "survey_app":
            include 'survey.php';
            Survey_App();
            break;
        case "activity_report_app":
            include 'dcr_save.php';
            DCR_Save( $SF_Code, $DivisionCode );
            break;
    }
    $resp[ "success" ] = true;
    echo json_encode( $resp );
}

function update_entry() {
    $data = $GLOBALS[ 'Data' ];
    $SFCode = ( string )$data[ 0 ][ 'Activity_Report' ][ 'SF_code' ];
    $Remarks = ( string )$data[ 0 ][ 'Activity_Report' ][ 'remarks' ];
    $HalfDy = ( string )$data[ 0 ][ 'Activity_Report' ][ 'HalfDay_FW_Type' ];
    $sql = "select SF_Code from vwActivity_report where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$Date_Format5' as datetime)";
    $result = performQuery( $sql );
    if ( count( $result ) < 1 ) {
        $result = array();
        $result[ 'success' ] = false;
        $result[ 'type' ] = 2;
        $result[ 'msg' ] = 'No Call Report Submited...';
        outputJSON( $result );
        exit;
    }
    $sql = "EXEC DCRUpdateEntry '" . $SFCode . "','" . $Remarks . "','" . $HalfDy . "','" . $Date_Format5 . "'";
    performQuery( $sql );
    $response[ "success" ] = true;
    echo json_encode( $response );
}

function delete_entry() {
    $arc = ( isset( $_GET[ 'arc' ] ) && strlen( $_GET[ 'arc' ] ) == 0 ) ? null : $_GET[ 'arc' ];
    $amc = ( isset( $_GET[ 'amc' ] ) && strlen( $_GET[ 'amc' ] ) == 0 ) ? null : $_GET[ 'amc' ];
    if ( !is_null( $amc ) ) {
        $sql = "EXEC DCRDeleteEntry '" . $amc . "', '" . $arc . "'";
        performQuery( $sql );
    }
}
?>