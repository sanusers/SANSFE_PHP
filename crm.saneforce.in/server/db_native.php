<?php
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS' );
//ini_set( 'error_reporting', E_ALL );
//ini_set( 'display_errors', true );
session_start();

include 'db_conn.php';
include 'functions/utils.php';

date_default_timezone_set( "Asia/Kolkata" );
$Date_Format1 = date( 'Y-m-d H:i:s' );
$Date_Format2 = date( 'Y-m-d H:i' );
$Date_Format3 = date( 'Y-m-d' );
$Date_Format4 = date( 'd_m_Y' );
$URL_BASE = "/";

$axn = $_GET[ 'axn' ];
$value = explode( ":", $axn );

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

$data = json_decode( $_POST[ 'data' ], true );
$GLOBALS[ 'Data' ] = $data;

switch ( strtolower( $value[ 0 ] ) ) {
    case "login":
        $response_array = array();
        $sql = "EXEC SPR_LoginAPP '" . $data[ 'name' ] . "', '" . $data[ 'password' ] . "'";
        $response = performQuery( $sql );
        if ( count( $response ) > 0 ) {
            $response_array[ 'success' ] = true;
            $result = array_merge( $response_array, $response[ 0 ] );
            outputJSON( $result );
        } else {
            $result[ 'success' ] = false;
            $result[ 'msg' ] = "Check User and Password";
            outputJSON( $result );
        }
        break;
    case "table/list":
        include 'functions/table_list.php';
        MasterSync();
        break;
    case "get/class":
        $sql = "EXEC iOS_getDocClass '" . $data[ 'SF' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/subordinate":
        $sql = "EXEC getHyrSF_APP '" . $RSF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/jointwork":
        $sql = "EXEC getHyrSF_APP '" . $RSF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/daysummcnt":
        $sql = "EXEC getCusVstDet '" . $SF_Code . "'";
        $response_1 = performQuery( $sql );
        $sql = "EXEC getWTVstDet '" . $SF_Code . "'";
        $response_2 = performQuery( $sql );
        $result = array();
        $result = [ $response_1, $response_2 ];
        outputJSON( $result );
        break;
    case "get/tpdetail":
        include 'functions/tour_plan.php';
        TourPlanDetails();
        break;
    case "get/tpsetup":
        $query = "EXEC Get_Tpsetup '" . $DivisionCode . "'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/quali":
        $sql = "EXEC iOS_getDocQual '" . $data[ 'SF' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/categorys":
        $sql = "EXEC iOS_getDocCats '" . $data[ 'SF' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/speciality":
        $sql = "EXEC iOS_getDocSpec '" . $data[ 'SF' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/submgr":
        $sql = "exec getHyrSF_APP '" . $RSF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "getdivision_ho_sf":
        $sql = "EXEC getDivision '" . $_GET[ 'Ho_Id' ] . "','" . $SF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "tp_objective":
        $query = "SELECT id,objective_name name FROM mas_tp_objective WHERE division_code='" . $DivisionCode . "' and status=0";
        outputJSON( performQuery( $query ) );
        break;
    case "get/last_checkindetails":
        $query1 = "select top 1 convert(varchar,id)id,LTRIM(RIGHT(CONVERT(VARCHAR(20), Start_Time, 100), 7)) name, (CASE WHEN End_Time IS NULL THEN '1' ELSE '0' END) AS status,Activity_date,CONVERT(VARCHAR, Start_Time, 100) Start_Time from Attendance_history where Sf_Code='$SF_Code' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='" . $Date_Format3 . "' order by id desc";
        $query2 = "select top 1 convert(varchar,Cust_id)id,Cust_name name,Activity_date,Checkin_time,Checkout_time,convert(varchar,Status)Status from Dcr_checkin where sf_code='$SF_Code' and Activity_date='" . $Date_Format3 . "' and status!=1 order by ID DESC";
        $results = array();
        $results[ 'Day_Checkin' ] = performQuery( $query1 );
        $results[ 'Customer_Checkin' ] = performQuery( $query2 );
        outputJSON( $results );
        break;
    case "get/visit_control":
        $query = "select CustCode,CustType, convert(varchar, Vst_Date, 23)Dcr_dt,month(Vst_Date) Mnth,year(Vst_Date) Yr,CustName,isnull(SDP,'')town_code,isnull(SDP_Name,'')town_name,1 Dcr_flag from vwVisitDetails where SF_Code='" . $SF_Code . "' order by Vst_Date";
        outputJSON( performQuery( $query ) );
        break;
    case "get/catvstfrq":
        $sql = "EXEC GetCatVstCMn '" . $SF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "checkin_details":
        $query = "SELECT TOP 1 CONVERT(VARCHAR,id) id,LTRIM(RIGHT(CONVERT(VARCHAR(20), Start_Time, 100), 7)) [name], (CASE WHEN End_Time IS NULL THEN '1' ELSE '0' END) AS [status],CONVERT(VARCHAR, Start_Time, 100) Start_Time FROM Attendance_history WHERE Sf_Code='" . $SF_Code . "' AND DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='" . $Date_Format3 . "' ORDER BY id DESC";
        outputJSON( performQuery( $query ) );
        break;
    case "getdoctor_dob_dow":
        $sql = "EXEC getDoctor_Dob_Dow '" . $SF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "gettpappr":
        include 'functions/approval_count.php';
        ApprovalCount( $SF_Code );
        break;
    case "get/dynactivity":
        include 'functions/activity.php';
        DynamicActivity( $data[ 'div' ] );
        break;
    case "get/missedflag":
        $sql = "EXEC getLockflag_App '" . $SF_Code . "','" . $DivisionCode . "'";
        $response = performQuery( $sql );
        $results[ "missflag" ] = $response[ 0 ][ "missflag" ];
        outputJSON( $results );
        break;
    case "dcr/save":
        include 'functions/add_update_delete_entry.php';
        add_entry();
        break;
    case "dcr/updateentry":
        include 'functions/add_update_delete_entry.php';
        delete_entry();
        add_entry();
        break;
    case "save/dcract":
        include 'functions/activity_save.php';
        $Dact = '';
        outputJSON( SaveDCRActivity( $Dact ) );
        break;
    case "dcr/updrem":
        include 'functions/add_update_delete_entry.php';
        update_entry();
        break;
    case "createmail":
        include 'functions/mail.php';
        CreateMail( $Date_Format2, $Date_Format4 );
        break;
    case "mailview":
        include 'functions/mail.php';
        MailView( $Date_Format1 );
        break;
    case "mailmove":
        include 'functions/mail.php';
        MailMove( $Date_Format1 );
        break;
    case "maildel":
        include 'functions/mail.php';
        MailDelete( $Date_Format1 );
        break;
    case "fileattachment_mail":
        include 'functions/mail.php';
        MailFileUpload( $Date_Format4 );
        break;
    case "getmailsapp":
        include 'functions/mail.php';
        GetMailApp();
        break;
    case "fileattachment":
        $file = $_FILES[ 'imgfile' ][ 'name' ];
        $info = pathinfo( $file );
        $file_name = basename( $file, '.' . $info[ 'extension' ] );
        $ext = $info[ 'extension' ];
        $fileName = $file_name . "_" . $SF_Code . "_" . $Date_Format4 . "." . $ext;
        $file_src = '../MasterFiles/Mails/Attachment/' . $fileName;
        move_uploaded_file( $_FILES[ 'imgfile' ][ 'tmp_name' ], $file_src );
        break;
    case "get/precall":
        include 'functions/precall.php';
        Precall();
        break;
    case "get/mnthsumm":
        $sql = "EXEC getMonthSummaryApp '" . $_GET[ 'rptSF' ] . "','" . $_GET[ 'rptDt' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/orders":
        $sql = "EXEC getOrderApp '" . $SF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/orderdet":
        $sql = "EXEC getOrderSummaryApp '" . $SF_Code . "','" . $_GET[ 'Ord_No' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/dayrpt":
        $sql = "EXEC getDayReportApp '" . $SF_Code . "','" . $_GET[ 'rptDt' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/checkinrpt":
        $sql = "EXEC getCheckInReportApp '" . $SF_Code . "','" . $_GET[ 'rptDt' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/dayrpt_geo":
        $sql = "EXEC getDayReportApp_Geo '" . $SF_Code . "','" . $_GET[ 'rptDt' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vwchktpstatus":
        $query = "select TP_Entry_Count,TP_Flag, CASE WHEN TP_Flag = 1 THEN 'TP Not Approved. Contact Line Manager/Admin' WHEN TP_Flag = 2 THEN 'Tour Plan Rejected' WHEN TP_Flag = 0 THEN 'Prepare Tour Plan' Else '3' END AS TP_Status From vwCheckTPStatus where SF_Code='" . $SF_Code . "' and Tour_Month ='" . $_GET[ 'month' ] . "' and Tour_Year='" . $_GET[ 'year' ] . "'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/route_location":
        $sql = "EXEC Track_route '" . $SF_Code . "','" . $_GET[ 'ReportDate' ] . "' ";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vwvstdet":
        $sql = "EXEC spGetVstDetApp '" . $_GET[ 'ACd' ] . "','" . $_GET[ 'typ' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vwvstdet_geo":
        $sql = "EXEC spGetVstDetApp_Geo '" . $SF_Code . "','" . $_GET[ 'ACd' ] . "' ";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/rpt_activitydet":
        include 'functions/activity.php';
        Activity();
        break;
    case "get/visit_monitor":
        $sql = "EXEC Visit_Coverage_Analysis_App '" . $DivisionCode . "','" . $SF_Code . "','" . $_GET[ 'month' ] . "','" . $_GET[ 'year' ] . "'";
        break;
    case "get/missedrpt":
        $sql = "EXEC Missedreport_app '" . $SF_Code . "','" . $_GET[ 'rptDt' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/missedrpt_view":
        $Rptdt = $_GET[ 'report_date' ];
        $year = date( 'Y', strtotime( $Rptdt ) );
        $month = date( 'n', strtotime( $Rptdt ) );
        $sql = "EXEC Missedcall_report_app '" . $DivisionCode . "','" . $SF_Code . "','" . $month . "','" . $year . "','" . $Rptdt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vist_analysis":
        $sql = "EXEC Dashboard_Native_App '" . $div . "','" . $SF_Code . "','9','2021','" . $_GET[ 'vst_date' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/daycheckinrpt":
        $sql = "EXEC getDaycheckInReportApp '" . $SF_Code . "','" . $_GET[ 'rptDt' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/todaytp":
        include 'functions/todaytp_save.php';
        SvMyTodayTP();
        break;
    case "save/tpdaynew":
        savetourplan( 0 );
        break;
    case "save/tourplan_fullmonth":
        include 'functions/tourplanfullmonth.php';
        TourPlanFullMonth();
        break;
    case "get/tpapproval":
        $sql = "EXEC iOS_getTPApproval '" . $data[ 'SF' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/tpapprovalnew":
        $TPDatas = $data[ 0 ][ 'TPDatas' ];
        $query = "EXEC SV_TPApproval '" . $data[ 0 ][ 'SFCode' ] . "','" . $data[ 0 ][ 'TPMonth' ] . "','" . $data[ 0 ][ 'TPYear' ] . "'";
        performQuery( $query );
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "save/tpreject":
        $query = "exec iOS_svTPReject '" . $data[ 'SF' ] . "','" . $data[ 'TPMonth' ] . "','" . $data[ 'TPYear' ] . "','" . $data[ 'Reason' ] . "'";
        performQuery( $query );
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "save/converstion":
        include 'functions/messenger.php';
        SaveMessage();
        break;
    case "get/conversation":
        include 'functions/messenger.php';
        GetMessage( $data[ 'SF' ] );
        break;
    case "get/docnxtvisit":
        $sql = "EXEC Get_DocNxtVist '" . $RSF_Code . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "vwleavestatus":
        include 'functions/leave.php';
        LeaveStatus( $SF_Code );
        break;
    case "leavehistory":
        include 'functions/leave.php';
        LeaveHistory( $SF_Code, $DivisionCode );
        break;
    case "vwleave":
        include 'functions/leave.php';
        ViewLeave( $SF_Code );
        break;
    case "vwcheckleave":
        include 'functions/leave.php';
        CheckLeaveStatus( $SF_Code, $Date_Format3 );
        break;
    case "leavevalidate":
        include 'functions/leave.php';
        LeaveValidation( $SF_Code, $data[ 'lv_type' ], $data[ 'fdate' ], $data[ 'todate' ] );
        break;
    case "vwproductdetailing":
        $query = "select ID,[FileName],FileSubject,Div_Code,Update_dtm,ContentType,[Data],Designation_Code,Designation_Short_Name from File_info div_code='$DivisionCode'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/dynview":
        $query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID from  mas_dynamic_screen_creation where Activity_SlNo='" . $data[ 'slno' ] . "' and Active_Flag='0'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/dynviewDetail":
        include 'functions/activity.php';
        DynamicViewDetails();
        break;
    case "getsurvey":
        include 'functions/survey.php';
        Survey( $SF_Code, $DivisionCode );
        break;
    case "delete_dcr":
        $query = "exec DelDCRTempByDt '" . $SF_Code . "','" . $_GET[ 'dcr_dt' ] . "'";
        performQuery( $query );
        $results[ 'success' ] = true;
        outputJSON( $results );
        break;
    case "user_update":
        $query = "update mas_salesforce set Sf_Password='" . $data[ 'password' ] . "' where Sf_code='" . $SF_Code . "' and Division_Code='" . $DivisionCode . "' ";
        performQuery( $query );
        $results[ "success" ] = true;
        outputJSON( $results );
        break;
    case "save/geotag":
        $div = ( string )str_replace( ",", "", $data[ 'divcode' ] );
        $cust = ( string )$data[ 'cust' ];
        $taggedtime = ( string )$data[ 'tagged_time' ];
        if ( ( $taggedtime == 'null' )OR( $taggedtime == 'NULL' )OR( $taggedtime == "" ) ) {
            $taggedtime = $Date_Format1;
        }
        if ( $cust == 'D' ) {
            $query = "exec Map_geotag '" . $data[ 'cuscode' ] . "','" . $div . "','" . $data[ 'lat' ] . "','" . $data[ 'long' ] . "','" . $data[ 'Addr' ] . "','" . $data[ 'Addr' ] . "','" . $taggedtime . "','" . $data[ 'sfname' ] . "','" . $data[ 'sfname' ] . "','" . $data[ 'Mode' ] . "' ";
            $result[ "cat" ] = "D";
        } else if ( $cust == 'C' ) {
            $query = "exec Map_Chem_geotag '" . $data[ 'cuscode' ] . "','" . $div . "','" . $data[ 'lat' ] . "','" . $data[ 'long' ] . "','" . $data[ 'Addr' ] . "','" . $data[ 'Addr' ] . "','" . $taggedtime . "','" . $data[ 'sfname' ] . "','" . $data[ 'sfname' ] . "','" . $data[ 'Mode' ] . "' ";
            $result[ "cat" ] = "C";
        } else if ( $cust == 'S' ) {
            $query = "exec Map_Stock_geotag '" . $data[ 'cuscode' ] . "','" . $div . "','" . $data[ 'lat' ] . "','" . $data[ 'long' ] . "','" . $data[ 'Addr' ] . "','" . $data[ 'Addr' ] . "','" . $taggedtime . "','" . $data[ 'sfname' ] . "','" . $data[ 'sfname' ] . "','" . $data[ 'Mode' ] . "' ";
            $result[ "cat" ] = "S";
        } else {
            $query = "exec Map_Unlist_geotag '" . $data[ 'cuscode' ] . "','" . $div . "','" . $data[ 'lat' ] . "','" . $data[ 'long' ] . "','" . $data[ 'Addr' ] . "','" . $data[ 'Addr' ] . "','" . $taggedtime . "','" . $data[ 'sfname' ] . "','" . $data[ 'sfname' ] . "','" . $data[ 'Mode' ] . "' ";
            $result[ "cat" ] = "U";
        }
        performQuery( $query );
        $result[ "Msg" ] = "Tag Submitted Successfully...";
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "get/geotag":
        $sql = "EXEC getViewTag '" . $data[ 'SF' ] . "','" . $data[ 'cust' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/editdates":
        $sql = "EXEC GetDlyReEntryDts_App '" . $data[ 'SF' ] . "','" . $data[ 'Div' ] . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "entry/count":
        include 'functions/entry_count.php';
        EntryCount();
        break;
    case "save/livetrack":
        include 'functions/live_tracking.php';
        LiveTracking_Save( $RSF_Code );
        break;
    case "get/live_track_sf":
        include 'functions/live_tracking.php';
        Get_SF_Track( $SF_Code );
        break;
    case "save/stockistprimary":
        include 'functions/secondary_save.php';
        SaveSecondary( $SF_Code );
        break;
    case "get/stockistprimary":
        include 'functions/secondary_save.php';
        GetSecondary( $DivisionCode );
        break;
    case "svfeedback_entry":
        $query = "insert into SF_Feedback_form (SF_Code,SF_name,Site,Division_Code,Feedback_remark,Created_dtm,status) select '" . $data[ 'sfCode' ] . "','" . $data[ 'sf_name' ] . "','" . $data[ 'weburl' ] . "','" . $data[ 'divisionCode' ] . "','" . $data[ 'remarks' ] . "',getdate(),'0'";
        performQuery( $query );
        $results[ 'success' ] = true;
        outputJSON( $results );
        break;
    case "travel_distance":
        include 'functions/travel.php';
        Travel_Distance( $SF_Code );
        break;
    case "imgupload":
        move_uploaded_file( $_FILES[ "imgfile" ][ "tmp_name" ], "../photos/" . $_FILES[ "imgfile" ][ "name" ] );
        break;
    case "profileupload":
        $sf = $_GET[ 'sf_code' ];
        move_uploaded_file( $_FILES[ "imgfile" ][ "tmp_name" ], "../Profile_Imgs/" . $sf . "_" . $_FILES[ "imgfile" ][ "name" ] );
        break;
    case "deleteentry":
        include 'functions/add_update_delete_entry.php';
        $arc = ( isset( $data[ 'arc' ] ) && strlen( $data[ 'arc' ] ) == 0 ) ? null : $data[ 'arc' ];
        $amc = ( isset( $data[ 'amc' ] ) && strlen( $data[ 'amc' ] ) == 0 ) ? null : $data[ 'amc' ];
        if ( !is_null( $amc ) ) {
            $sql = "EXEC DCRDeleteEntry '" . $amc . "', '" . $arc . "'";
            performQuery( $sql );
        }
        break;
    default:
        $results[ 'success' ] = false;
        outputJSON( $results );
        break;
}
?>