<?php
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS' );
//ini_set( 'error_reporting', E_ALL );
//ini_set( 'display_errors', true );
session_start();
date_default_timezone_set( "Asia/Kolkata" );

include 'db_conn.php';
include 'functions/utils.php';

include 'functions/tour_plan.php';
include 'functions/table_list.php';
include 'functions/edit_activity.php';
include 'functions/approval_count.php';
include 'functions/add_update_delete_entry.php';
include 'functions/save_tour_plan.php';
include 'functions/notification.php';

$URL_BASE = "/";

$axn = $_GET[ 'axn' ];
$value = explode( ":", $axn );
switch ( strtolower( $value[ 0 ] ) ) {
    case "login":
        $data = json_decode( $_POST[ 'data' ], true );
        $username = ( string )$data[ 'name' ];
        $password = ( string )$data[ 'password' ];
        $DeviceRegId = ( string )$data[ 'AppDeviceRegId' ];
        $version = $data[ 'versionNo' ];
        $login_mode = $data[ 'mode' ];
        $AppDeviceRegId = $data[ 'device_id' ];
        $Device_version = '';
        $Device_name = '';
        if ( ( isset( $data[ 'Device_name' ] ) ) && ( isset( $data[ 'Device_version' ] ) ) ) {
            $Device_version = $data[ 'Device_version' ];
            $Device_name = $data[ 'Device_name' ];
        }
        $sql = "EXEC SPR_LoginAPP '" . $username . "', '" . $password . "'";
        $response = performQuery( $sql );
        return outputJSON( $response );
        break;
    case "table/list":
        mastersync();
        break;
    case "get/subordinate":
        $sfCode = $_GET[ 'rSF' ];
        $param = array( $sfCode );
        $sql = "EXEC getHyrSF_APP '" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "get/jointwork":
        $sfCode = $_GET[ 'rSF' ];
        $param = array( $sfCode );
        $sql = "EXEC getHyrSF_APP '" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "get/daysummcnt":
        $SFCode = $_GET[ 'sfCode' ];
        $sql = "EXEC getCusVstDet '" . $SFCode . "'";
        $response_1 = performQuery( $sql );

        $sql = "EXEC getWTVstDet '" . $SFCode . "'";
        $response_2 = performQuery( $sql );

        $result = array();
        $result = [ $response_1, $response_2 ];
        return outputJSON( $result );
        break;
    case "get/tpdetail":
        tourplan();
        break;
    case "get/tpsetup":
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $query = "select SF_code,isnull(AddsessionNeed,1)AddsessionNeed,isnull(AddsessionCount,1)AddsessionCount,isnull(DrNeed,1)DrNeed, isnull(ChmNeed,1)ChmNeed,isnull(JWNeed,1)JWNeed,isnull(ClusterNeed,1)ClusterNeed,isnull(clustertype,1)clustertype, div,isnull(StkNeed,1)StkNeed,isnull(Cip_Need,1)Cip_Need,isnull(HospNeed,1)HospNeed,isnull(FW_meetup_mandatory,1) FW_meetup_mandatory,isnull(max_doc,0)max_doc,isnull(tp_objective,1)tp_objective,isnull(Holiday_Editable,0) Holiday_Editable,isnull(Weeklyoff_Editable,0)Weeklyoff_Editable from tpSetup where div='" . $Owndiv . "'";
        $result = performQuery( $query );
        outputJSON( $result );
        break;
    case "get/quali":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $sql = "EXEC iOS_getDocQual '" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "get/class":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $sql = "EXEC iOS_getDocClass '" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "get/categorys":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $sql = "EXEC iOS_getDocCats '" . $sfCode . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/speciality":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $sql = "EXEC iOS_getDocSpec '" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "get/submgr":
        $sfCode = $_GET[ 'rSF' ];
        $param = array( $sfCode );
        $sql = "exec getHyrSF_APP '" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "getdivision_ho_sf":
        $sfCode = $_GET[ 'sfCode' ];
        $HOID = $_GET[ 'Ho_Id' ];
        $sql = "EXEC getDivision '" . $HOID . "','" . $sfCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "tp_objective":
        $divCode = $_GET[ 'divisionCode' ];
        $divisionCode = explode( ",", $divCode );
        $query = "SELECT id,objective_name name FROM mas_tp_objective WHERE status=0";
        $results = performQuery( $query );
        outputJSON( $results );
        break;
    case "get/last_checkindetails":
        $sfCode = $_GET[ 'sfCode' ];
        $divCode = $_GET[ 'divisionCode' ];
        $divisionCode = explode( ",", $divCode );
        $date = date( "Y-m-d" );
        $query1 = "select top 1 convert(varchar,id)id,LTRIM(RIGHT(CONVERT(VARCHAR(20), Start_Time, 100), 7)) name, (CASE WHEN End_Time IS NULL THEN '1' ELSE '0' END) AS status,Activity_date,CONVERT(VARCHAR, Start_Time, 100) Start_Time from Attendance_history where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='" . $date . "' order by id desc";
        $query2 = "select top 1 convert(varchar,Cust_id)id,Cust_name name,Activity_date,Checkin_time,Checkout_time,convert(varchar,Status)Status from Dcr_checkin where sf_code='$sfCode' and Activity_date='" . $date . "' and status!=1 order by ID DESC";
        $results = array();
        $results[ 'Day_Checkin' ] = performQuery( $query1 );
        $results[ 'Customer_Checkin' ] = performQuery( $query2 );
        outputJSON( $results );
        break;
    case "get/visit_control":
        $SFCode = $_GET[ 'sfCode' ];
        $query = "select CustCode,CustType, convert(varchar, Vst_Date, 23)Dcr_dt,month(Vst_Date) Mnth,year(Vst_Date) Yr,CustName,isnull(SDP,'')town_code,isnull(SDP_Name,'')town_name,1 Dcr_flag,isnull(FW_Indicator,'')FW_Indicator,isnull(WorkType_Name,'')WorkType_Name from tbVisit_Details where SF_Code='$SFCode' and (CustType=1 OR CustType = 0) and  cast(CONVERT(varchar,Vst_Date,101)as datetime) >= DATEADD(mm, DATEDIFF(mm, 0, GETDATE()) - 1, 0) order by Vst_Date";
        $results = performQuery( $query );
        outputJSON( $results );
        break;
    case "get/dcr_details":
        $SFCode = $_GET[ 'sfCode' ];
        $query = "select CustCode,CustType, convert(varchar, Vst_Date, 23)Dcr_dt,month(Vst_Date) Mnth,year(Vst_Date) Yr,CustName,isnull(SDP,'')town_code,isnull(SDP_Name,'')town_name,1 Dcr_flag from tbVisit_Details where SF_Code='$SFCode' and CustType=1 and  cast(CONVERT(varchar,Vst_Date,101)as datetime) >= DATEADD(DAY, -1, GETDATE()) order by Vst_Date";
        $results = performQuery( $query );
        outputJSON( $results );
        break;
    case "get/catvstfrq":
        $SFCode = $_GET[ 'sfCode' ];
        $sql = "EXEC GetCatVstCMn '" . $SFCode . "'";
        $result = performQuery( $sql );
        outputJSON( $result );
        break;
    case "checkin_details":
        $sfCode = $_GET[ 'sfCode' ];
        $date = date( "Y-m-d" );
        $query = "SELECT TOP 1 CONVERT(VARCHAR,id) id,LTRIM(RIGHT(CONVERT(VARCHAR(20), Start_Time, 100), 7)) [name], (CASE WHEN End_Time IS NULL THEN '1' ELSE '0' END) AS [status],CONVERT(VARCHAR, Start_Time, 100) Start_Time FROM Attendance_history WHERE Sf_Code='$sfCode' AND DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='" . $date . "' ORDER BY id DESC";
        outputJSON( performQuery( $query ) );
        break;
    case "getdoctor_dob_dow":
        $sfCode = $_GET[ 'sfCode' ];
        $sql = "EXEC getDoctor_Dob_Dow '" . $sfCode . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "gettpappr":
        approvalcount();
        break;
    case "get/dynactivity":
        $data = json_decode( $_POST[ 'data' ], true );
        $division = ( string )$data[ 'div' ];
        $query = "SELECT Activity_SlNo, Activity_Mode, REPLACE(Activity_Desig, ' ', '') Activity_Desig,Activity_SName,Activity_Name,Activity_OrderBy,Division_Code,Creation_date,Active_Flag,Activity_For,Activity_Available,Other_Multi_Activity_Name,Related_Activity_SlNo,Approval_Needed,Approved_By, Transaction_Involved,Editable FROM mas_activity WHERE Division_Code='" . $division . "' AND Active_Flag='0'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/missedflag":
        $sfCode = $_GET[ 'sfCode' ];
        $divCode = $_GET[ 'divisionCode' ];
        $divisionCode = explode( ",", $divCode );
        $sql = "EXEC getLockflag_App '" . $sfCode . "','" . $divisionCode[ 0 ] . "'";
        $arr = performQuery( $sql );
        $results[ "missflag" ] = $arr[ 0 ][ "missflag" ];
        outputJSON( $results );
        break;
    case "dcr/save":
        add_entry();
        break;
    case "dcr/updateentry":
        delete_entry();
        add_entry();
        break;
    case "dcr/reEntry":
        $data = json_decode( $_POST[ 'data' ], true );
        $arc = ( isset( $_GET[ 'arc' ] ) && strlen( $_GET[ 'arc' ] ) == 0 ) ? null : $_GET[ 'arc' ];
        $sql = "EXEC Delete_reject_dcr '$arc' ";
        performQuery( $sql );
        add_entry();
    case "save/dcract":
        $Dact = '';
        outputJSON( SaveDCRActivity( $Dact ) );
        break;
    case "dcr/updrem":
        update_entry();
        break;
    case "get/doctor":
        $SF = $_GET[ 'sfCode' ];
        $MSL = $_GET[ 'Msl_No' ];
        $query = "select Doc_Cat_Code,Doc_Cat_ShortName,Doc_QuaCode, visit_hours,visit_days,REPLACE(visit_days,'/',',') visit_days1,REPLACE(visit_hours,'/',',')visit_hours1,ListedDr_Address3,Doc_Qua_Name,Doc_Special_Code, Doc_Spec_ShortName,Hospital_Address,convert(nvarchar(MAX), ListedDr_DOB, 23) ListedDr_DOB,convert(nvarchar(MAX), ListedDr_DOW, 23) ListedDr_DOW, ListedDr_Hospital,ListedDr_Sex,ListedDr_RegNo,Visiting_Card,Dr_Potential, Dr_Contribution from mas_listeddr where ListedDrCode='" . $MSL . "'";
        outputJSON( performQuery( $query ) );
        break;
    case "save/newdr":
        $data = json_decode( $_POST[ 'data' ], true );
        $SF = ( string )$data[ 'SF' ];
        $DivCodes = ( string )$data[ 'DivCode' ];
        $DivCode = explode( ",", $DivCodes . "," );
        $DrName = ( string )$data[ 'DrName' ];
        $DrQCd = ( string )$data[ "DrQCd" ];
        $DrQNm = ( string )$data[ "DrQNm" ];
        $DrClsCd = ( string )$data[ "DrClsCd" ];
        $DrClsNm = ( string )$data[ "DrClsNm" ];
        $DrCatCd = ( string )$data[ "DrCatCd" ];
        $CatNm = ( string )$data[ "DrCatNm" ];
        $DrSpcCd = ( string )$data[ "DrSpcCd" ];
        $DrSpcNm = ( string )$data[ "DrSpcNm" ];
        $DrAddr = ( string )$data[ "DrAddr" ];
        $DrTerCd = ( string )$data[ "DrTerCd" ];
        $DrTerNm = ( string )$data[ "DrTerNm" ];
        $DrPincd = ( string )$data[ "DrPincd" ];
        $DrPhone = ( string )$data[ "DrPhone" ];
        $DrMob = ( string )$data[ "DrMob" ];
        $Uid = ( string )$data[ "Uid" ];
        $sql = "exec svNewCustomer_App 0,'','" . $DrName . "','" . $DrAddr . "','" . $DrTerCd . "','" . $DrTerNm . "','" . $DrCatCd . "','" . $CatNm . "','" . $DrSpcCd . "','" . $DrSpcNm . "','" . $DrClsCd . "','" . $DrClsNm . "','" . $DrQCd . "','" . $DrQNm . "','U','" . $SF . "','','','" . $DrPincd . "','" . $DrPhone . "','" . $DrMob . "','" . $Uid . "'";
        $output = performQuery( $sql );
        $result[ "Qry" ] = $output[ 0 ][ 'Msg' ];
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "createMail":
        $sf = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $date = date( 'Y-m-d H:i' );
        $data = json_decode( $_POST[ 'data' ], true );
        $temp = array_keys( $data[ 0 ] );
        $vals = $data[ 0 ];
        $divCode = $_GET[ 'divisionCode' ];
        $file = $vals[ 'fileName' ];
        if ( !empty( $file ) ) {
            $info = pathinfo( $file );
            $file_name = basename( $file, '.' . $info[ 'extension' ] );
            $ext = $info[ 'extension' ];
            $fileName = $file_name . "_" . $sf . "_" . date( 'd_m_Y' ) . "." . $ext;
        } else
            $fileName = "";
        $msg1 = urldecode( $vals[ 'message' ] );
        $msg = trim( $msg1, '"' );
        $sub1 = urldecode( $vals[ 'subject' ] );
        $sub = trim( $sub1, '"' );
        $sql = "select max(isnull(Trans_sl_no,0))+1 transslno from trans_mail_head";
        $tr = performQuery( $sql );
        $transslno = $tr[ 0 ][ 'transslno' ];
        $sql = "insert into trans_mail_head(Trans_sl_no,System_ip,Mail_SF_From,Mail_SF_To,Mail_Subject,Mail_Content,Mail_Attachement,Mail_CC,Mail_BCC,Division_Code,Mail_Sent_Time,To_SFName,CC_Sfname,Bcc_SfName,Mail_SF_Name,sent_flag) select '$transslno','','$sf','" . $vals[ 'to_id' ] . "','$sub','$msg','$fileName','" . $vals[ 'cc_id' ] . "','" . $vals[ 'bcc_id' ] . "','$Owndiv','$date','" . $vals[ 'to' ] . "','" . $vals[ 'cc' ] . "','" . $vals[ 'bcc' ] . "','" . $vals[ 'from' ] . "',0";
        performQuery( $sql );
        $ToCcBcc = explode( ",", $vals[ 'ToCcBcc' ] );
        for ( $i = 0; $i < count( $ToCcBcc ); $i++ ) {
            if ( $ToCcBcc[ $i ] ) {
                $sql = "insert into trans_mail_detail(Trans_Sl_no,open_mail_id,mail_active_flag,Division_code) select '$transslno','" . str_replace( ",", "", $ToCcBcc[ $i ] ) . "',0,'$Owndiv'";
                performQuery( $sql );
            }
        }
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "mailView":
        $date = date( 'Y-m-d H:i:s' );
        $id = $_GET[ 'id' ];
        $sql = "update trans_mail_detail set Mail_Active_Flag='10',Mail_Read_Date='$date' where Trans_Sl_No=$id";
        performQuery( $sql );
        $result[ 'success' ] = true;
        outputJSON( $result );
        break;
    case "mailMove":
        $folder = $_GET[ 'folder' ];
        $id = $_GET[ 'id' ];
        $date = date( 'Y-m-d H:i:s' );
        $sql = "update trans_mail_detail set Mail_moved_to='$folder',Mail_Active_Flag='12',mail_moved_date='$date' where Trans_Sl_No=$id";
        performQuery( $sql );
        $result[ 'success' ] = true;
        outputJSON( $result );
        break;
    case "mailDel":
        $folder = $_GET[ 'folder' ];
        $date = date( 'Y-m-d H:i:s' );
        $id = $_GET[ 'id' ];
        if ( $folder == "Sent" ) {
            $sql = "update MailBox_Details set Mail_SentItem_DelFlag=1 where Trans_Sl_No=$id";
        } else {
            $sql = "update trans_mail_detail set Mail_Active_Flag='-1',mail_delete_date='$date' where Trans_Sl_No=$id";
        }
        performQuery( $sql );
        $result[ 'success' ] = true;
        outputJSON( $result );
        break;
    case "fileAttachment":
        $sf = $_GET[ 'sf_code' ];
        $file = $_FILES[ 'imgfile' ][ 'name' ];
        $info = pathinfo( $file );
        $file_name = basename( $file, '.' . $info[ 'extension' ] );
        $ext = $info[ 'extension' ];
        $fileName = $file_name . "_" . $sf . "_" . date( 'd_m_Y' ) . "." . $ext;
        $file_src = '../MasterFiles/Mails/Attachment/' . $fileName;
        move_uploaded_file( $_FILES[ 'imgfile' ][ 'tmp_name' ], $file_src );
        break;
    case "get/precall":
        $SF = $_GET[ 'sfCode' ];
        $MSL = $_GET[ 'Msl_No' ];
        $result = array();
        $query = "select SLVNo SVL,Doc_Cat_ShortName DrCat,Doc_Spec_ShortName DrSpl,isnull(stuff((select ', '+Doc_SubCatName from Mas_Doc_SubCategory S where CHARINDEX(cast(Doc_SubCatCode as varchar),D.Doc_SubCatCode)>0 for XML Path('')),1,2,''),'') DrCamp,isnull(stuff((select ', '+Product_Detail_Name from Map_LstDrs_Product M	inner join Mas_Product_Detail P on M.Product_Code=P.Product_Detail_Code and P.Division_Code=M.Division_Code where Listeddr_Code=D.ListedDrCode for XML Path('')),1,2,''),'') DrProd from mas_listeddr D where ListedDrCode='" . $MSL . "'";
        $as = performQuery( $query );
        if ( count( $as ) > 0 ) {
            $result[ 'SVL' ] = ( string )$as[ 0 ][ 'SVL' ];
            $result[ 'DrCat' ] = ( string )$as[ 0 ][ 'DrCat' ];
            $result[ 'DrSpl' ] = ( string )$as[ 0 ][ 'DrSpl' ];
            $result[ 'DrCamp' ] = ( string )$as[ 0 ][ 'DrCamp' ];
            $result[ 'DrProd' ] = ( string )$as[ 0 ][ 'DrProd' ];
            $result[ 'success' ] = true;

            $query = "select Trans_SlNo,Trans_Detail_Slno,convert(varchar,Activity_Date,0) Adate,Time DtTm1,convert(varchar,cast(convert(varchar,Activity_Date,101)+' '+Time  as datetime),20) as DtTm,isnull(CalFed,'') CalFed,Activity_Remarks,products,gifts,isnull(nextvstdate,'') nextvstdate from vwLastVstDet where rw=1 and Trans_Detail_Info_Code='" . $MSL . "' and SF_Code='" . $SF . "'";
            $as = performQuery( $query );

            if ( count( $as ) > 0 ) {
                $dat = $as[ 0 ][ 'DtTm1' ];
                $result[ 'LVDt' ] = date_format( $dat, 'd / m / Y g:i a' );
                $nextvstdate = $as[ 0 ][ 'nextvstdate' ];
                $result[ 'next_visit_date' ] = $nextvstdate;
                $Prods = ( string )$as[ 0 ][ 'products' ];
                $sProds = explode( "#", $Prods . '#' );
                $sSmp = '';
                $sProm = '';
                for ( $il = 0; $il < count( $sProds ); $il++ ) {
                    if ( $sProds[ $il ] != '' ) {
                        $spr = explode( "~", $sProds[ $il ] );
                        $Qty = 0;
                        if ( count( $spr ) > 0 ) {
                            $QVls = explode( "$", $spr[ 1 ] );
                            $Qty = $QVls[ 0 ];
                            $Vals = $QVls[ 1 ];
                        }
                        if ( $Qty > 0 ) {
                            $sSmp = $sSmp . $spr[ 0 ] . " ( " . $Qty . " )" . ( ( $Vals > 0 ) ? " ( " . $Vals . " )," : "," );
                        } else {
                            $sProm = $sProm . $spr[ 0 ] . ", ";
                        }
                    }
                }
                $result[ 'CallFd' ] = ( string )$as[ 0 ][ 'CalFed' ];
                $result[ 'Rmks' ] = ( string )$as[ 0 ][ 'Activity_Remarks' ];
                $result[ 'ProdSmp' ] = $sSmp;
                $result[ 'Prodgvn' ] = $sProm;
                $result[ 'DrGft' ] = ( string )$as[ 0 ][ 'gifts' ];
            } else {
                $result[ 'CallFd' ] = '';
                $result[ 'Rmks' ] = '';
                $result[ 'ProdSmp' ] = '';
                $result[ 'Prodgvn' ] = '';
                $result[ 'DrGft' ] = '';
                $result[ 'next_visit_date' ] = '';
                $result[ 'LVDt' ] = '';
                $result[ 'success' ] = false;
            }
        } else {
            $result[ 'success' ] = false;
        }
        outputJSON( $result );
        break;
    case "get/MnthSumm":
        $sfCode = $_GET[ 'rptSF' ];
        $dyDt = $_GET[ 'rptDt' ];
        $sql = "EXEC getMonthSummaryApp '" . $sfCode . "','" . $dyDt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/Orders":
        $sfCode = $_GET[ 'sfCode' ];
        $sql = "EXEC getOrderApp '" . $sfCode . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/OrderDet":
        $SF = $_GET[ 'sfCode' ];
        $ordid = $_GET[ 'Ord_No' ];
        $sql = "EXEC getOrderSummaryApp '" . $SF . "','" . $ordid . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/DayRpt":
        $sfCode = $_GET[ 'sfCode' ];
        $dyDt = $_GET[ 'rptDt' ];
        $sql = "EXEC getDayReportApp '" . $sfCode . "','" . $dyDt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/CheckInRpt":
        $sfCode = $_GET[ 'sfCode' ];
        $dyDt = $_GET[ 'rptDt' ];
        $sql = "EXEC getCheckInReportApp '" . $sfCode . "','" . $dyDt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/DayRpt_Geo":
        $sfCode = $_GET[ 'sfCode' ];
        $dyDt = $_GET[ 'rptDt' ];
        $sql = "EXEC getDayReportApp_Geo '" . $sfCode . "','" . $dyDt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vwChkTPStatus":
        $sfCode = $_GET[ 'sfCode' ];
        $mMonth = $_GET[ 'month' ];
        $mYear = $_GET[ 'year' ];
        $query = "select TP_Entry_Count,TP_Flag, CASE WHEN TP_Flag = 1 THEN 'TP Not Approved. Contact Line Manager/Admin' WHEN TP_Flag = 2 THEN 'Tour Plan Rejected' WHEN TP_Flag = 0 THEN 'Prepare Tour Plan' Else '3' END AS TP_Status From vwCheckTPStatus where SF_Code='" . $sfCode . "' and Tour_Month ='" . $mMonth . "' and Tour_Year='" . $mYear . "'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/route_location":
        $sfCode = $_GET[ 'sfCode' ];
        $rSF = $_GET[ 'rSF' ];
        $divCode = str_replace( ",,", ",", $_GET[ 'divisionCode' ] );
        $ReportDate = $_GET[ 'ReportDate' ];
        $query = "SELECT TL.SF_code,MS.Sf_Name,MS.SF_Mobile,TL.Emp_Id,TL.Employee_Id,CAST(CONVERT(VARCHAR,TL.DtTm,21) AS DATETIME) DtTm,FORMAT(TL.DtTm, 'hh:mm tt') as TrackTime,TL.Lat,TL.Lon,CASE WHEN TL.Addr='' THEN 'No Address Found!' ELSE TL.Addr END as Addr,TL.Auc,TL.EMod,CASE WHEN CAST(TL.Battery AS INT) BETWEEN 80 AND 100 THEN 'FULL' WHEN CAST(TL.Battery AS INT) BETWEEN 60 AND 80 THEN 'MEDIUM' WHEN CAST(TL.Battery AS INT) BETWEEN 40 AND 60  THEN 'LOW' ELSE 'VERY LOW' END AS BatteryStatus,CASE WHEN TL.Battery='' THEN '0' ELSE TL.Battery END as Battery,TL.Mock FROM Mas_Salesforce_AM MSAM INNER JOIN Mas_Salesforce MS ON MSAM.Sf_Code = MS.Sf_Code INNER JOIN tbTrackLoction TL ON TL.SF_code=MS.SF_code WHERE TL.EMod='Apps' AND CAST(TL.DtTm AS DATE)='" . $ReportDate . "' AND MSAM.DCR_AM='" . $sfCode . "' ORDER BY TL.SF_code, TL.DtTm DESC";
        outputJSON( performQuery( $query ) );
        break;
    case "get/vwVstDet":
        $ACd = $_GET[ 'ACd' ];
        $typ = $_GET[ 'typ' ];
        $sql = "EXEC spGetVstDetApp '" . $ACd . "','" . $typ . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vwVstDet_Geo":
        $ACd = $_GET[ 'ACd' ];
        $sfCode = $_GET[ 'sfCode' ];
        $sql = "EXEC spGetVstDetApp_Geo '" . $sfCode . "','" . $ACd . "' ";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/Rpt_ActivityDet":
        $Arc = $_GET[ 'arc' ];
        $Arcdt = $_GET[ 'arc_dt' ];
        $result = array();
        $qury = "select DT.Activity_SlNo,H.Activity_Name,DT.Group_id from DCR_Detail_Activity DT  left outer join mas_activity H on DT.Activity_SlNo = H.Activity_SlNo where Trans_Main_Sl_No='" . $Arc . "' and Trans_Detail_Slno='" . $Arcdt . "' group by DT.Activity_SlNo,H.Activity_Name,DT.Group_id";
        $restt = performQuery( $qury );
        if ( count( $restt ) > 0 ) {
            for ( $ilk = 0; $ilk < count( $restt ); $ilk++ ) {
                $query = "select ROW_NUMBER() over (ORDER BY Group_id ASC) as slno,DT.Group_id,DH.Field_Name,DH.Order_by,DT.Creation_Name,cast(convert(varchar,DT.Activity_Date,101) as datetime) Activity_Date,DT.Updated_Time,DT.SF_Code,DT.Control_Id from mas_dynamic_screen_creation DH left outer join DCR_Detail_Activity DT ON DH.Activity_SlNo=DT.Activity_SlNo and  DH.Creation_Id=DT.Creation_Id and DH.Control_id=DT.Control_id and DH.Activity_SlNo='" . $restt[ $ilk ][ 'Activity_SlNo' ] . "' where DT.Group_id='" . $restt[ $ilk ][ 'Group_id' ] . "'  order by Order_by Asc";
                $rest = performQuery( $query );
                if ( count( $rest ) > 0 ) {
                    $Rptact = array();
                    for ( $il = 0; $il < count( $rest ); $il++ ) {
                        array_push( $Rptact, array(
                            'slno' => $rest[ $il ][ "slno" ],
                            'Group_id' => $rest[ $il ][ "Group_id" ],
                            'Field_Name' => $rest[ $il ][ "Field_Name" ],
                            'Creation_Name' => $rest[ $il ][ "Creation_Name" ],
                            'Activity_Date' => $rest[ $il ][ "Activity_Date" ],
                            'Updated_Time' => $rest[ $il ][ "Updated_Time" ],
                            'SF_Code' => $rest[ $il ][ "SF_Code" ],
                            'Control_Id' => $rest[ $il ][ "Control_Id" ],
                            'Order_by' => $rest[ $il ][ "Order_by" ] ) );
                    }
                    array_push( $result, array( 'Main_id' => $restt[ $ilk ][ 'Activity_SlNo' ], 'Main_Name' => $restt[ $ilk ][ 'Activity_Name' ], 'Group_id' => $restt[ $ilk ][ "Group_id" ], 'Activity_data' => $Rptact ) );
                }
            }
        }
        outputJSON( $result );
        break;
    case "get/Visit_monitor":
        $sfCode = $_GET[ 'sfCode' ];
        $cMnth = $_GET[ 'month' ];
        $cYr = $_GET[ 'year' ];
        $div_code = $_GET[ 'divisionCode' ];
        $sf_type = $_GET[ 'sf_type' ];
        $sql = "EXEC Visit_Coverage_Analysis_App '" . $div_code . "','" . $sfCode . "','" . $cMnth . "','" . $cYr . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/MissedRpt":
        $sfCode = $_GET[ 'sfCode' ];
        $dyDt = $_GET[ 'rptDt' ];
        $query = "SELECT cast(format(cast('$dyDt' as datetime), 'yyyy-MM-01') as varchar) fdate";
        $result = performQuery( $query );
        $fst_date = $result[ 0 ][ "fdate" ];
        $sql = "EXEC Missedreport_app '" . $sfCode . "','" . $dyDt . "','" . $fst_date . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/MissedRpt_view":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $Rptdt = $_GET[ 'report_date' ];
        $year = date( 'Y', strtotime( $Rptdt ) );
        $month = date( 'n', strtotime( $Rptdt ) );
        $sql = "EXEC Missedcall_report_app '" . $div . "','" . $sfCode . "','" . $month . "','" . $year . "','" . $Rptdt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/vist_analysis":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $Rptdt = $_GET[ 'vst_date' ];
        $year = date( 'Y', strtotime( $Rptdt ) );
        $month = date( 'n', strtotime( $Rptdt ) );
        $sql = "EXEC Dashboard_Native_App '" . $div . "','" . $sfCode . "','9','2021','" . $Rptdt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/newdr":
        $data = json_decode( $_POST[ 'data' ], true );
        $SF = ( string )$data[ 'SF' ];
        $DivCodes = ( string )$data[ 'DivCode' ];
        $DivCode = explode( ",", $DivCodes . "," );
        $DrName = ( string )$data[ 'DrName' ];
        $DrQCd = ( string )$data[ "DrQCd" ];
        $DrQNm = ( string )$data[ "DrQNm" ];
        $DrClsCd = ( string )$data[ "DrClsCd" ];
        $DrClsNm = ( string )$data[ "DrClsNm" ];
        $DrCatCd = ( string )$data[ "DrCatCd" ];
        $CatNm = ( string )$data[ "DrCatNm" ];
        $DrSpcCd = ( string )$data[ "DrSpcCd" ];
        $DrSpcNm = ( string )$data[ "DrSpcNm" ];
        $DrAddr = ( string )$data[ "DrAddr" ];
        $DrTerCd = ( string )$data[ "DrTerCd" ];
        $DrTerNm = ( string )$data[ "DrTerNm" ];
        $DrPincd = ( string )$data[ "DrPincd" ];
        $DrPhone = ( string )$data[ "DrPhone" ];
        $DrMob = ( string )$data[ "DrMob" ];
        $Uid = ( string )$data[ "Uid" ];
        $query = "exec svNewCustomer_App 0,'','" . $DrName . "','" . $DrAddr . "','" . $DrTerCd . "','" . $DrTerNm . "','" . $DrCatCd . "','" . $CatNm . "','" . $DrSpcCd . "','" . $DrSpcNm . "','" . $DrClsCd . "','" . $DrClsNm . "','" . $DrQCd . "','" . $DrQNm . "','U','" . $SF . "','','','" . $DrPincd . "','" . $DrPhone . "','" . $DrMob . "','" . $Uid . "'";
        $output = performQuery( $query );
        $result[ "Qry" ] = $output[ 0 ][ 'Msg' ];
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "get/DayCheckInRpt":
        $sfCode = $_GET[ 'sfCode' ];
        $dyDt = $_GET[ 'rptDt' ];
        $sql = "EXEC getDaycheckInReportApp '" . $sfCode . "','" . $dyDt . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/todayTP":
        $data = json_decode( $_POST[ 'data' ], true );
        $DivCodes = ( string )$data[ 'Div' ];
        $DivCode = explode( ",", $DivCodes . "," );
        $sfCode = ( string )$data[ 'SF' ];
        $SFMem = ( string )$data[ 'SFMem' ];
        $TPDt = ( string )$data[ 'TPDt' ];
        $PlnCd = ( string )$data[ 'Pl' ];
        $PlnNM = ( string )$data[ 'PlNm' ];
        $WT = ( string )$data[ 'WT' ];
        $WTNM = ( string )$data[ 'WTNMm' ];
        $Rem = ( string )$data[ 'Rem' ];
        $loc = ( string )$data[ 'location' ];
        $TpVwFlg = ( string )$data[ 'TpVwFlg' ];
        $TpDrc = ( string )$data[ 'TP_Doctor' ];
        $TpCluster = ( string )$data[ 'TP_DocCluster' ];
        $TpWrktype = ( string )$data[ 'TP_Worktype' ];

        if ( $TpCluster == '' || $TpCluster == "''" || $TpCluster == null ) {
            $TpCluster = '';
            $TpWrktype = '';
        }
        $InsMode = ( string )$data[ 'InsMode' ];
        $HeaderId = ( isset( $_GET[ 'Head_id' ] ) && strlen( $_GET[ 'Head_id' ] ) == 0 ) ? null : $_GET[ 'Head_id' ];
        if ( $HeaderId != null ) {
            $query = "exec Delete_reject_dcr '$HeaderId' ";
            performQuery( $query );
        }
        $qry = "select count(Leave_Id)Lcnt from mas_Leave_Form where SF_code='" . $sfCode . "' and Leave_Active_Flag<>1 and (cast(From_Date as date)<=cast('$TPDt' as date) and cast('$TPDt' as date)<=To_Date or cast(From_Date as date)<=cast('$TPDt' as date) and cast('$TPDt' as date)<=To_Date or cast(From_Date as date)>=cast('$TPDt' as date) and To_Date<=cast('$TPDt' as date))";
        $Lary = performQuery( $qry );
        if ( $Lary[ 0 ][ "Lcnt" ] > 0 ) {
            $result[ "Msg" ] = "Today Already Leave Posted...";
            $result[ "success" ] = false;
            return $result;
        }
        $query = "select Count(Trans_SlNo) Cnt from vwActivity_Report where Sf_Code='" . $sfCode . "' and Confirmed <>'2' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $TPDt . "' as datetime),101) as datetime) and FWFlg='L'";
        $ExisArr = performQuery( $query );
        if ( $ExisArr[ 0 ][ "Cnt" ] > 0 ) {
            $result[ "Msg" ] = "Today Already Leave Posted...";
            $result[ "success" ] = false;
            return $result;
        } else {
            $query = "select Count(Trans_SlNo) Cnt from vwActivity_Report where Sf_Code='" . $sfCode . "' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $TPDt . "' as datetime),101) as datetime) and Work_Type<>'" . $WT . "'";
            $ExisArr = performQuery( $query );
            $result[ "cqry" ] = $query;
            if ( $ExisArr[ 0 ][ "Cnt" ] > 0 && $InsMode == "0" ) {
                $result[ "Msg" ] = "Already you are submitted your work. Now you are deviate. Do you want continue?";
                $result[ "update" ] = true;
                $result[ "success" ] = false;
            } else {
                $query = "exec iOS_svTodayTP '" . $sfCode . "','" . $SFMem . "','" . $PlnCd . "','" . $PlnNM . "','" . $WT . "','" . $WTNM . "','" . $Rem . "','" . $loc . "','" . $TPDt . "','" . $TpVwFlg . "','" . $TpDrc . "','" . $TpCluster . "','" . $TpWrktype . "'";
                performQuery( $query );
                if ( $InsMode == "2" ) {
                    $query = "select Work_Type,WorkType_Name,FWFlg,Half_Day_FW from vwActivity_Report where Sf_Code='" . $sfCode . "' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $TPDt . "' as datetime),101) as datetime) and Work_Type<>'" . $WT . "'";
                    $ExisArr = performQuery( $query );
                    $PwTy = $ExisArr[ 0 ][ "Work_Type" ];
                    $PwTyNm = $ExisArr[ 0 ][ "WorkType_Name" ];
                    $PwFl = $ExisArr[ 0 ][ "FWFlg" ];
                    $HwTy = $ExisArr[ 0 ][ "Half_Day_FW" ];
                    $query = "select FWFlg,Wtype from vw_all where SFTyp='" . $SFTy . "' and type_code='" . $WT . "'";
                    $ExisArr = performQuery( $query );
                    $query = "update DCRMain_Trans set ";
                    if ( $PwFl != "F" ) {
                        $HwTy = $HwTy . $PwTy . ",";
                        $query = $query . " Work_type='" . $WT . "',FieldWork_Indicator='" . $ExisArr[ 0 ][ "FWFlg" ] . "',WorkType_Name='" . $ExisArr[ 0 ][ "Wtype" ] . "',";
                    } else {
                        $HwTy = $HwTy . $WT . ",";
                    }
                    $query = $query . "Half_Day_FW='" . $HwTy . "' where Sf_Code='" . $sfCode . "' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $TPDt . "' as datetime),101) as datetime)";
                    performQuery( $query );
                    performQuery( str_replace( "DCRMain_Trans", "DCRMain_Temp", $query ) );
                } else {
                    if ( $InsMode == "1" ) {
                        $query = "exec DelDCRTempByDt '" . $sfCode . "','" . date( 'Y-m-d 00:00:00.000', strtotime( $TPDt ) ) . "'";
                        performQuery( $query );
                    }
                    $query = "exec svDCRMain_App '" . $sfCode . "','" . date( 'Y-m-d 00:00:00.000', strtotime( $TPDt ) ) . "','" . $WT . "','" . $PlnCd . "','" . $DivCode[ 0 ] . "','" . $Rem . "','','app'";
                    $result[ "aqry" ] = $query;
                    performQuery( $query );
                }
                $result[ "Msg" ] = "Today Work Plan Submitted Successfully...";
                $result[ "success" ] = true;
            }
            return $result;
        }
        outputJSON( $result );
        break;
    case "save/tpdaynew":
        savetourplan( 0 );
        break;
    case "save/tourplannew":
        savetourplan( 1 );
        break;
    case "save/tourplan_fullmonth":
        $data = json_decode( $_POST[ 'data' ], true );
        $TPDatas = $data[ 0 ][ 'TPDatas' ];
        $sfCode = ( string )$data[ 0 ][ 'SFCode' ];
        $sfName = ( string )$data[ 0 ][ 'SFName' ];
        $DivCodes = ( string )$data[ 0 ][ 'DivCode' ];
        $DivCode = explode( ",", $DivCodes . "," );
        $tpmonth = $TPDatas[ 0 ][ 'Tour_Month' ];
        $tpyear = $TPDatas[ 0 ][ 'Tour_Year' ];
        $query = "update trans_tp_one set Change_Status='1' where sf_code='" . $sfCode . "' and Tour_Month='" . $tpmonth . "' and Tour_Year='" . $tpyear . "'";
        performQuery( $query );

        $query = "update Tourplan_detail set Change_Status='1' where SFCode='" . $sfCode . "' and cast(Mnth as int)='" . $tpmonth . "' and cast(Yr as int)='" . $tpyear . "'";
        performQuery( $query );
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "get/tpapproval":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $sql = "EXEC iOS_getTPApproval '" . $sfCode . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/tpapprovalnew":
        $data = json_decode( $_POST[ 'data' ], true );
        $TPDatas = $data[ 0 ][ 'TPDatas' ];
        $sfCode = ( string )$data[ 0 ][ 'SFCode' ];
        $sfName = ( string )$data[ 0 ][ 'SFName' ];
        $DivCodes = ( string )$data[ 0 ][ 'DivCode' ];
        $DivCode = explode( ",", $DivCodes . "," );

        $query = "update Tourplan_detail set Change_Status='3',Approve_mode='Apps',Approved_time=getdate() where SFCode='" . $sfCode . "' and cast(Mnth as int)='" . $data[ 0 ][ 'TPMonth' ] . "' and cast(Yr as int)='" . $data[ 0 ][ 'TPYear' ] . "'";
        performQuery( $query );

        $query = "insert into Trans_TP select  SF_Code,Tour_Month,Tour_Year,Submission_date,Tour_Date,WorkType_Code_B,Worktype_Name_B,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,Objective,Worked_With_SF_Code,Division_Code,'1',getdate(),Rejection_Reason,Change_Status,Territory_Code1,Territory_Code2,Territory_Code3,Worked_With_SF_Name,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,TP_Sf_Name,TP_Approval_MGR,Entry_mode,Dr_Code,Dr_Name,Chem_Code,Chem_Name,Stockist_Code,Stockist_Name,Hosptial_Code,Hosptial_Name,Others_Code,Others_Name,Deviate,Dr_two_code,Dr_two_name,Dr_three_code,Dr_three_name,Chem_two_code,Chem_two_name,Chem_three_code,Chem_three_name,Unlistdr_one_code,Unlistdr_one_name,Unlistdr_two_code,Unlistdr_two_name,Unlistdr_three_code,Unlistdr_three_name,Remark_two,Remark_three,Jointwork_two_code,Jointwork_two_name,Jointwork_three_code,Jointwork_three_name,Approval_mode,Stockist_two_code,Stockist_two_name,Stockist_three_code,Stockist_three_name,HQCodes,HQNames,Objective_id1,Objective_Name1,Objective_id2,Objective_Name2,Objective_id3,Objective_Name3,Hosptial_two_code,Hosptial_two_name,Hosptial_three_code,Hosptial_three_name,WTFlg,WTFlg1,WTFlg2,Hosptial_Code1,Hosptial_Name1,Hosptial_Code2,Hosptial_Name2,HQCodes1,HQNames1,HQCodes2,HQNames2 from Trans_TP_One where SF_Code ='" . $sfCode . "' and Tour_Month='" . $data[ 0 ][ 'TPMonth' ] . "' and Tour_Year='" . $data[ 0 ][ 'TPYear' ] . "'";
        performQuery( $query );

        $query = "delete from Trans_TP_One where SF_Code ='" . $sfCode . "' and Tour_Month='" . $data[ 0 ][ 'TPMonth' ] . "' and Tour_Year='" . $data[ 0 ][ 'TPYear' ] . "'";
        performQuery( $query );
        $month = $data[ 0 ][ 'TPMonth' ] + 1;
        $year = $data[ 0 ][ 'TPYear' ];
        $tdate = $year . '-' . $month . '-01';

        $sql = "update mas_salesforce_dcrtpdate set Last_TP_Date='$tdate' where sf_Code='" . $sfCode . "' and '$tdate'>Last_TP_Date";
        performQuery( $sql );
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "save/tpreject":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $Reason = ( string )$data[ 'Reason' ];
        $query = "exec iOS_svTPReject '" . $sfCode . "','" . $data[ 'TPMonth' ] . "','" . $data[ 'TPYear' ] . "','" . $Reason . "'";
        performQuery( $query );
        $result[ "Qry" ] = $query;
        $result[ "success" ] = true;
        return $result;
        $msg = "Your Tourplan is rejected for " . $Reason . "";
        notification( $sfCode, $msg, 0 );
        break;
    case "save/converstion":
        $data = json_decode( $_POST[ 'data' ], true );
        $sXML = "<Root>";
        $sXML = $sXML . "<Msg SF=\"" . $data[ 'SF' ] . "\" Dt=\"" . $data[ "MsgDt" ] . "\" To=\"" . $data[ "MsgTo" ] . "\" ToName=\"" . $data[ "MsgToName" ] . "\" mTxt=\"" . $data[ "MsgText" ] . "\" mPID=\"" . $data[ "MsgParent" ] . "\" />";
        $sXML = $sXML . "</Root>";
        $SFCodeFrom = $data[ 'SF' ];
        $SFCodeTo = $data[ 'MsgTo' ];
        $Msge = $data[ "MsgText" ];

        $sql = "EXEC iOS_SvMsgConversation '" . $sXML . "'";
        outputJSON( performQuery( $sql ) );
        Chat( $SFCodeFrom, $SFCodeTo, $Msge, 0 );
        break;
    case "get/conversation":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'SF' ];
        $msgDt = ( string )$data[ 'MsgDt' ];
        $sql = "EXEC iOS_GetMsgConversation '" . $sfCode . "','" . $msgDt . "'";
        $result = performQuery( $sql );
        $sql = "EXEC iOS_GetMsgConversationFiles '" . $sfCode . "','" . $msgDt . "'";
        $result1 = performQuery( $sql );
        for ( $il = 0; $il < count( $result ); $il++ ) {
            $msgId = $result[ $il ][ "Msg_Id" ];
            $rArry = array_filter( $result1, function ( $item )use( $msgId ) {
                return ( $item[ "Msg_Id" ] === $msgId );
            } );
            $nAry = array();
            foreach ( $rArry as $key => $value ) {
                $nAry[] = $rArry[ $key ];
            }
            $result[ $il ][ "Files" ] = $nAry;
        }
        outputJSON( $result );
        break;
    case "getDoctorNextVisit":
        $sfCode = $_GET[ 'sfCode' ];
        $month = $_GET[ 'month' ];
        $year = $_GET[ 'year' ];
        $query = "select * from vwDoctorNextVisit where sfcode='$sfCode' and month(date)=$month and year(date)=$year";
        outputJSON( performQuery( $query ) );
        break;
    case "vwLeaveStatus":
        $sfCode = $_GET[ 'sfCode' ];
        $query = "select * from vwLeaveEntitle where Sf_Code='$sfCode'";
        outputJSON( performQuery( $query ) );
        break;
    case "LeaveHistory":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $divc = ( string )$divs[ 0 ];
        $query = "select a.sf_code,b.division_code,(isnull(b.Leave_SName,'') +' - '+isnull(b.Leave_Name,'') )Leave_type,convert(varchar,a.From_Date,106)From_Date,convert(varchar,a.To_Date,106)To_Date,a.No_of_Days,convert(varchar,a.Created_Date,0) Apply_date,isnull(a.Rejected_Reason,'')Rejected_Reason,isnull(a.Reason,'') leave_Reason,isnull(a.Rejected_Reason,'')Rejected_Reason,a.Address,a.Leave_Active_Flag from mas_Leave_Form a left outer join mas_leave_type b on a.Leave_Type = b.Leave_code where  a.sf_code='$sfCode' and b.division_code='$divc' and year(a.Created_Date)= year(getdate())";
        outputJSON( performQuery( $query ) );
        break;
    case "getMailsApp":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $folder = $_GET[ 'folder' ];
        $month = $_GET[ 'month' ];
        $year = $_GET[ 'year' ];
        $fldr = $folder;
        if ( $folder != 'Inbox' && $folder != 'Sent Item' && $folder != 'Viewed' ) {
            $folder = 'Flder';
        }
        $query = "EXEC MailInbox_DivCode_New_App '$sfCode','$Owndiv','$folder','$fldr','$year','$month',''";
        outputJSON( performQuery( $query ) );
        break;
    case "vwProductDetailing":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $query = "select * from File_info where div_code='$Owndiv'";
        outputJSON( performQuery( $query ) );
        break;
    case "media_inbox":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $query = "select *,(case when media_sf_from='$sfCode' then 1 else 0 end) mode from Mas_MediaFiles_Info where (media_sf_from='$sfCode' or media_sf_to='$sfCode') and active_flag=0";
        outputJSON( performQuery( $query ) );
        break;
    case "vwMedUpdateUpload":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $query = "select * from vwMedUpdateUpload where Division_Code='$Owndiv'";
        outputJSON( performQuery( $query ) );
        break;
    case "vaccancyList":
        $divCode = $_GET[ 'divisionCode' ];
        $query = "select Sf_HQ HQName,sf_name from  Mas_Salesforce where sf_tp_active_flag=1 and sf_type=1 and division_code='$divCode'";
        outputJSON( performQuery( $query ) );
        break;
    case "vwLeave":
        $sfCode = $_GET[ 'sfCode' ];
        $query = "select * from vwLeave vl INNER JOIN vwLeaveType vw ON vl.Leave_Type = vw.leave_code where Reporting_To_SF='$sfCode'";
        outputJSON( performQuery( $query ) );
        break;
    case "vwCheckLeave":
        $sfCode = $_GET[ 'sfCode' ];
        $date = date( 'Y-m-d' );
        $sql = "select From_Date,To_Date,No_of_Days from mas_Leave_Form where To_Date>='$date' and sf_code='$sfCode' and Leave_Active_Flag !=1 order by From_Date";
        $leaveDays = performQuery( $sql );
        $currentDate = date_create( $date );
        $disableDates = array();
        $sql = "SELECT * FROM vwActivity_Report where SF_Code='" . $sfCode . "' and cast(activity_date as datetime)=cast('$date' as datetime)";
        $dcrEntry = performQuery( $sql );
        if ( count( $dcrEntry ) > 0 )
            array_push( $disableDates );
        for ( $i = 0; $i < count( $leaveDays ); $i++ ) {
            $fromDate = $leaveDays[ $i ][ 'From_Date' ];
            $toDate = $leaveDays[ $i ][ 'To_Date' ];
            $noOfDays = $leaveDays[ $i ][ 'No_of_Days' ];
            if ( $currentDate > $fromDate )
                $fromDate = $currentDate;
            $diff = date_diff( $fromDate, $toDate, TRUE );
            $days = $diff->format( "%a" ) + 1;
            for ( $j = 0; $j < $days; $j++ ) {
                array_push( $disableDates, $fromDate->format( 'd/m/Y' ) );
                $fromDate->modify( '+1 day' );
            }
        }
        outputJSON( $disableDates );
        break;
    case "get/dynview":
        $data = json_decode( $_POST[ 'data' ], true );
        $Act_slno = ( string )$data[ 'slno' ];
        $query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID from  mas_dynamic_screen_creation where Activity_SlNo='" . $Act_slno . "' and Active_Flag='0'";
        outputJSON( performQuery( $query ) );
        break;
    case "get/dynviewDetail":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'slno' ];
        $div = ( string )$data[ 'div' ];
        $sf = ( string )$data[ 'SF' ];
        $div = str_replace( ",", "", $div );

        $query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID    from mas_dynamic_screen_creation where Activity_SlNo='" . $sfCode . "' and Division_Code='" . $div . "' and Active_Flag='0' order by Order_by Asc";
        $res = performQuery( $query );
        if ( count( $res ) > 0 ) {
            for ( $il = 0; $il < count( $res ); $il++ ) {
                $id = $res[ $il ][ "Control_Id" ];
                if ( $id == "8" || $id == "9" ) {
                    if ( $res[ $il ][ "Control_Para" ] == "Mas_ListedDr" ) {
                        $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "' and Sf_Code='" . $sf . "'";
                    } else if ( $res[ $il ][ "Control_Para" ] == "Mas_Product_Detail" ) {
                        $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "' and Product_Active_Flag='0'";
                    } else {
                        $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "'";
                    }

                    $res[ $il ][ 'inputss' ] = $qu;
                    $res[ $il ][ 'input' ] = performQuery( $qu );
                } else if ( $id == "12" || $id == "13" ) {
                    $qu = "select Sl_No from Mas_Customized_Table_Name where Name_Table='" . $res[ $il ][ "Control_Para" ] . "'";
                    $res[ $il ][ 'inputss' ] = $qu;
                    $cus = performQuery( $qu );
                    $qu = "select Mas_Sl_No,Customized_Name from Mas_Customized_Table where Name_Table_Slno='" . $cus[ 0 ][ "Sl_No" ] . "'";
                    $cus = performQuery( $qu );
                    $res[ $il ][ 'input' ] = $cus;
                } else {
                    $res[ $il ][ 'input' ] = array();
                }
            }
        }
        outputJSON( $res );
        break;
    case "get/dynviewDetail_tp":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'slno' ];
        $div = ( string )$data[ 'div' ];
        $sf = ( string )$data[ 'SF' ];
        $div = str_replace( ",", "", $div );

        $query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID    from mas_dynamic_screen_creation where Activity_SlNo='" . $sfCode . "' and Division_Code='" . $div . "' and Active_Flag='0' and  Activity_For !='D,' order by Order_by Asc";
        $res = performQuery( $query );
        if ( count( $res ) > 0 ) {
            for ( $il = 0; $il < count( $res ); $il++ ) {
                $id = $res[ $il ][ "Control_Id" ];
                if ( $id == "8" || $id == "9" ) {
                    if ( $res[ $il ][ "Control_Para" ] == "Mas_ListedDr" ) {
                        $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "' and Sf_Code='" . $sf . "'";
                    } else if ( $res[ $il ][ "Control_Para" ] == "Mas_Product_Detail" ) {
                        $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "' and Product_Active_Flag='0'";
                    } else {
                        $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "'";
                    }
                    $res[ $il ][ 'inputss' ] = $qu;
                    $res[ $il ][ 'input' ] = performQuery( $qu );
                } else if ( $id == "12" || $id == "13" ) {
                    $qu = "select Sl_No from Mas_Customized_Table_Name where Name_Table='" . $res[ $il ][ "Control_Para" ] . "'";
                    $res[ $il ][ 'inputss' ] = $qu;
                    $cus = performQuery( $qu );
                    $qu = "select Mas_Sl_No,Customized_Name from Mas_Customized_Table where Name_Table_Slno='" . $cus[ 0 ][ "Sl_No" ] . "'";
                    $cus = performQuery( $qu );
                    $res[ $il ][ 'input' ] = $cus;
                } else {
                    $res[ $il ][ 'input' ] = array();
                }
            }
        }
        outputJSON( $res );
        break;
    case "mulGeotag":
        $sfCode = $_GET[ 'sfCode' ];
        $query = "select ListedDrCode as id,count(ListedDrCode) tagcnt  from  Mas_ListedDr D INNER JOIN  vwMap_GEO_Customers g ON Cust_Code = ListedDrCode where sf_code='$sfCode' group by ListedDrCode";
        outputJSON( performQuery( $query ) );
        break;
    case "getsurvey":
        $sfCode = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $survey_details = [];
        $query = "select  Survey_ID id,Survey_Title name,CONVERT(varchar,Effective_From_Date,23) as from_date,CONVERT(varchar,Effective_To_Date,23) as to_date from Mas_Question_Survey_Creation_Head where division_code='$Owndiv' and Close_flag='0' and Active_Flag='0' and cast(effective_from_date as date)<=cast(GETDATE() as date) and cast(effective_to_date as date)>=cast(GETDATE() as date) order by Survey_ID desc";
        $surveytitle = performQuery( $query );
        for ( $i = 0; $i < count( $surveytitle ); $i++ ) {
            $survey_id = $surveytitle[ $i ][ 'id' ];
            //$survey_details[$i]['survey_title']= $surveytitle[$i];
            $query = "select Question_Id id,Survey_ID Survey,Doctor_Category DrCat,Doctor_Speclty DrSpl,Doctor_Cls DrCls,Hospital_Class HosCls,Chemist_Category ChmCat,Stockist_State Stkstate,Stockist_HQ StkHQ,Processing_Type Stype from Mas_Question_Survey_Creation_Detail where division_code='$Owndiv' and Survey_id='$survey_id' and  charindex(','+'$sfCode'+',',','+SF_Code+',')>0 and isNull(SF_Code,'')<>''";
            $surveyfor = performQuery( $query );
            $survey_details[ $i ] = $surveytitle[ $i ];
            $survey_details[ $i ][ 'survey_for' ] = [];
            for ( $j = 0; $j < count( $surveyfor ); $j++ ) {
                $Survey = $surveyfor[ $j ][ 'Survey' ];
                //$survey_details[$i]['survey_for'] ='';
                if ( $survey_id == $Survey ) {
                    $query = "select sc.Question_Id id,Survey_ID Survey,Doctor_Category DrCat,Doctor_Speclty DrSpl,Doctor_Cls DrCls,Hospital_Class HosCls,Chemist_Category ChmCat,Stockist_State Stkstate,Stockist_HQ StkHQ,Processing_Type Stype,Control_Id Qc_id,Control_Name Qtype,Control_Para Qlength,'0' Mandatory,Question_Name Qname,Question_Add_Names Qanswer,Active_Flag from Mas_Question_Survey_Creation_Detail sc
						inner join Mas_Question_Creation qc on qc.Question_Id=sc.Question_Id
						where sc.division_code='$Owndiv' and Survey_id='$survey_id' and  charindex(','+'$sfCode'+',',','+SF_Code+',')>0";
                    $ssurveydetail = performQuery( $query );
                    $survey_details[ $i ][ 'survey_for' ] = $ssurveydetail;
                }
            }
        }
        outputJSON( $survey_details );
        break;
    case "delete_dcr":
        $sfCode = $_GET[ 'sfCode' ];
        $dcr_dt = $_GET[ 'dcr_dt' ];
        $query = "exec DelDCRTempByDt '" . $sfCode . "','" . $dcr_dt . "'";
        performQuery( $query );
        $results[ 'success' ] = true;
        outputJSON( $results );
        break;
    case "user_update":
        $data = json_decode( $_POST[ 'data' ], true );
        $pass = $data[ 'password' ];
        $sfCode = $_GET[ 'sfCode' ];
        $divCode = $_GET[ 'divisionCode' ];
        $query = "update mas_salesforce set Sf_Password='" . $pass . "' where Sf_code='" . $sfCode . "' and Division_Code='" . $divCode . "' ";
        performQuery( $query );
        $results[ "success" ] = true;
        $results[ "qry" ] = $query;
        outputJSON( $results );
        break;
    case "Leavevalidate":
        $data = json_decode( $_POST[ 'data' ], true );
        $sf_code = $_GET[ 'sfCode' ];
        $lv_type = ( string )$data[ 'lv_type' ];
        $fdate = strtotime( str_replace( "Z", "", str_replace( "T", " ", $data[ 'fdate' ] ) ) );
        $todate = strtotime( str_replace( "Z", "", str_replace( "T", " ", $data[ 'todate' ] ) ) );
        $from = date( 'Y-m-d 00:00:00', $fdate );
        $todt = date( 'Y-m-d 00:00:00', $todate );
        $query = "exec iOS_getLvlValidate '" . $sf_code . "','" . $from . "','" . $todt . "','" . $lv_type . "' ";
        outputJSON( performQuery( $query ) );
        break;
    case "get/setup":
        $rqSF = $_GET[ 'rSF' ];
        $sql = "EXEC getAPPSetups '" . $rqSF . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "tpview":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'sfCode' ];
        $t = strtotime( str_replace( "Z", "", str_replace( "T", " ", $data[ 'mnthYr' ] ) ) );
        $TpDt = date( 'Y-m-d 00:00:00', $t );
        $query = "SELECT convert(varchar,Tour_Date,103) [date],Worktype_Name_B wtype,replace(isnull(Tour_Schedule1,''),'0','') towns,replace(isnull(Tour_Schedule1,''),'0','') PlnNo,Worktype_Name_B1 wtype2,replace(isnull(Tour_Schedule2,''),'0','') towns2,replace(isnull(Tour_Schedule2,''),'0','') PlnNo2,Worktype_Name_B2 wtype3,replace(isnull(Tour_Schedule3,''),'0','') towns3,replace(isnull(Tour_Schedule2,''),'0','') PlnNo3,SF_Code sf_code from Trans_TP T where sf_code='$sfCode' and Tour_Month=month('$TpDt') and Tour_year=year('$TpDt') order by Tour_Date";
        outputJSON( performQuery( $query ) );
        break;
    case "tpviewdt":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = ( string )$data[ 'sfCode' ];
        $t = strtotime( str_replace( "Z", "", str_replace( "T", " ", $data[ 'tpDate' ] ) ) );
        $TpDt = date( 'Y-m-d 00:00:00', $t );
        $sql = "EXEC spTPViewDtws '$sfCode','$TpDt'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/geotag":
        $data = json_decode( $_POST[ 'data' ], true );
        $drcode = ( string )$data[ 'cuscode' ];
        $div = ( string )str_replace( ",", "", $data[ 'divcode' ] );
        $lat = ( string )$data[ 'lat' ];
        $long = ( string )$data[ 'long' ];
        $cust = ( string )$data[ 'cust' ];
        $addr = ( string )$data[ 'Addr' ];
        $imgname = ( string )$data[ 'imge_name' ];
        $taggedtime = ( string )$data[ 'tagged_time' ];
        $sfName = ( string )$data[ 'sfname' ];
        $sfCode = ( string )$data[ 'sfcode' ];
        $mode = ( string )$data[ 'Mode' ];
        if ( ( $taggedtime == 'null' )OR( $taggedtime == 'NULL' )OR( $taggedtime == "" ) ) {
            $taggedtime = date( 'Y-m-d H:i:s' );
        }
        if ( $cust == 'D' ) {
            $query = "exec Map_geotag '" . $drcode . "','" . $div . "','" . $lat . "','" . $long . "','" . $addr . "','" . $imgname . "','" . $taggedtime . "','" . $sfCode . "','" . $sfName . "','" . $mode . "' ";
            performQuery( $query );
            $result[ "cat" ] = "D";
        } else if ( $cust == 'C' ) {
            $query = "exec Map_Chem_geotag '" . $drcode . "','" . $div . "','" . $lat . "','" . $long . "','" . $addr . "','" . $imgname . "','" . $taggedtime . "','" . $sfCode . "','" . $sfName . "','" . $mode . "' ";
            performQuery( $query );
            $result[ "cat" ] = "C";
        } else if ( $cust == 'S' ) {
            $query = "exec Map_Stock_geotag '" . $drcode . "','" . $div . "','" . $lat . "','" . $long . "','" . $addr . "','" . $imgname . "','" . $taggedtime . "','" . $sfCode . "','" . $sfName . "','" . $mode . "' ";
            performQuery( $query );
            $result[ "cat" ] = "S";
        } else {
            $query = "exec Map_Unlist_geotag '" . $drcode . "','" . $div . "','" . $lat . "','" . $long . "','" . $addr . "','" . $imgname . "','" . $taggedtime . "','" . $sfCode . "','" . $sfName . "','" . $mode . "' ";
            performQuery( $query );
            $result[ "cat" ] = "U";
        }
        $result[ "Msg" ] = "Tag Submitted Successfully...";
        $result[ "success" ] = true;
        outputJSON( $result );
        break;
    case "get/geotag":
        $data = json_decode( $_POST[ 'data' ], true );
        $SF = ( string )$data[ 'SF' ];
        $cust = ( string )$data[ 'cust' ];
        $sql = "EXEC getViewTag '" . $SF . "','" . $cust . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "get/editdates":
        $data = json_decode( $_POST[ 'data' ], true );
        $SF = ( string )$data[ 'SF' ];
        $Div = ( string )$data[ 'Div' ];
        $sql = "EXEC GetDlyReEntryDts_App '" . $SF . "','" . $Div . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "entry/count":
        $eDate = $_GET[ 'eDate' ];
        if ( $eDate == '' || $eDate == null ) {
            $today = date( 'Y-m-d 00:00:00' );
        } else {
            $today = date( "Y-m-d 00:00:00", strtotime( $eDate ) );
        }
        $sfCode = $_GET[ 'sfCode' ];
        $query = "SELECT Employee_Id,case sf_type when 1 then 'MR' else 'MGR' End SF_Type FROM Mas_Salesforce_One where SF_code='" . $sfCode . "'";
        $response = performQuery( $query );

        $query = "SELECT work_Type worktype_code,Remarks daywise_remarks,Half_Day_FW halfdaywrk from vwActivity_Report H where SF_Code='" . $sfCode . "' and FWFlg <> 'F' and cast(activity_date as datetime)=cast('$today' as datetime)";
        $data = performQuery( $query );
        $result = array();
        if ( count( $data ) > 0 ) {
            $result[ "success" ] = false;
            $result[ 'data' ] = $data;
            outputJSON( $result );
            die;
        }
        $result[ "success" ] = true;
        $result[ 'data' ] = Entry_Count();
        outputJSON( $result );
        break;
    case "get/DocNxtVisit":
        $sfCode = $_GET[ 'sfCode' ];
        $rptSF = $_GET[ 'rSF' ];
        $divC = $_GET[ 'divisionCode' ];
        $sql = "EXEC Get_DocNxtVist '" . $rptSF . "'";
        outputJSON( performQuery( $sql ) );
        break;
    case "save/livetrack":
        $data = json_decode( $_POST[ 'data' ], true );
        $sfCode = $_GET[ 'sfCode' ];
        $query = "SELECT sf_emp_id,Employee_Id FROM Mas_Salesforce WHERE Sf_Code='$sfCode'";
        $sf = performQuery( $query );
        $empid = $sf[ 0 ][ 'sf_emp_id' ];
        $employeeid = $sf[ 0 ][ 'Employee_Id' ];
        $TrcLocs = $data;
        for ( $ik = 0; $ik < count( $TrcLocs ); $ik++ ) {
            $sql = "insert into tbTrackLoction(SF_code,Emp_Id,Employee_Id,DtTm,Lat,Lon,Addr,Auc,EMod,Battery,SF_Mobile,updatetime,IsOnline) select '$sfCode','$empid','$employeeid','" . $TrcLocs[ $ik ][ 'time' ] . "','" . $TrcLocs[ $ik ][ 'Latitude' ] . "','" . $TrcLocs[ $ik ][ 'Longitude' ] . "','" . $TrcLocs[ $ik ][ 'Address' ] . "','','Apps','" . $TrcLocs[ $ik ][ 'Battery' ] . "','" . $TrcLocs[ $ik ][ 'Mobile' ] . "',getdate(),'" . $TrcLocs[ $ik ][ 'IsOnline' ] . "'";
            performQuery( $sql );
        }
        $result = array();
        $result[ 'success' ] = true;
        outputJSON( $result );
        break;
    case "get/live_track_SF":
        $sfCode = $_GET[ 'sfCode' ];
        $Div = str_replace( ",,", ",", $_GET[ 'divisionCode' ] );
        $query = "SELECT Sf_Code,Sf_Name,SF_Mobile FROM Mas_Salesforce WHERE Reporting_To_SF='" . $sfCode . "'";
        outputJSON( performQuery( $query ) );
        break;
    case "save/stockistprimary":
        $SFCode = $_GET[ 'sfCode' ];
        $SFName = $_GET[ 'sf_name' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $DivCode = ( string )$divs[ 0 ];
        $JSONArray = json_decode( $_POST[ 'data' ], true );
        for ( $i = 0; $i < count( $JSONArray ); $i++ ) {
            $Stockist_Code = ( string )$JSONArray[ $i ][ 'Stockist_Code' ];
            $Stockist_Name = ( string )$JSONArray[ $i ][ 'Stockist_Name' ];
            $Trans_Month = ( string )$JSONArray[ $i ][ 'Trans_Month' ];
            $Trans_Year = ( string )$JSONArray[ $i ][ 'Trans_Year' ];
            $Pri_Value = ( string )$JSONArray[ $i ][ 'Pri_Value' ];
            $Sec_Value = ( string )$JSONArray[ $i ][ 'Sec_Value' ];

            $sql = "select * from Trans_Pri_Sec_Sale where Trans_Month='$Trans_Month' and Trans_Year='$Trans_Year' and sf_code='$SFCode' and Division_Code='$DivCode' and Stockist_Code='$Stockist_Code'";
            $data1 = performQuery( $sql );
            if ( count( $data1 ) > 0 ) {
                $query1 = "Update Trans_Pri_Sec_Sale set Pri_Value='$Pri_Value',Sec_Value='$Sec_Value',Updated_Date=getdate() where Trans_Month='$Trans_Month' and Trans_Year='$Trans_Year' and sf_code='$SFCode' and Division_Code='$DivCode' and Stockist_Code='$Stockist_Code'";
                performQuery( $query1 );
            } else {
                $query2 = "SELECT isNull(max(Sl_No),0)+1 as RwID FROM Trans_Pri_Sec_Sale";
                $trw = performQuery( $query2 );
                $pk = ( int )$trw[ 0 ][ 'RwID' ];
                $query3 = "insert into Trans_Pri_Sec_Sale(Sl_No,Stockist_Code,Stockist_Name,Trans_Month,Trans_Year,Pri_Value,Sec_Value,Division_Code,Created_Date,Approved_Flag, View_Flag, Entry_Mode,sf_code) select '$pk','$Stockist_Code','$Stockist_Name','$Trans_Month','$Trans_Year','$Pri_Value','$Sec_Value','$DivCode',getdate(),'0','0','Apps','$SFCode'";
                performQuery( $query3 );
            }
        }
        $results[ 'success' ] = true;
        outputJSON( $results );
        break;
    case "get/stockistprimary":
        $SFCode = $_GET[ 'sfCode' ];
        $rptSF = $_GET[ 'rSF' ];
        $Trans_Month = $_GET[ 'month' ];
        $Trans_Year = $_GET[ 'year' ];
        $div = $_GET[ 'divisionCode' ];
        $divs = explode( ",", $div . "," );
        $DivCode = ( string )$divs[ 0 ];
        $query = "select Sl_No,Stockist_Code,Stockist_Name,Trans_Month,Trans_Year,Pri_Value,Sec_Value,Division_Code,Approved_Flag,View_Flag,sf_code from Trans_Pri_Sec_Sale where Trans_Month='$Trans_Month' and Trans_Year='$Trans_Year' and Division_Code='$DivCode'";
        $data = performQuery( $query );
        $result = array();
        if ( count( $data ) == 0 ) {
            $results[] = $data[ 0 ];
            outputJSON( $result );
        } else {
            outputJSON( $data );
        }
        break;
    case "svfeedback_entry":
        $data = json_decode( $_POST[ 'data' ], true );
        $query = "insert into SF_Feedback_form (SF_Code,SF_name,Site,Division_Code,Feedback_remark,Created_dtm,status) select '" . $data[ 'sfCode' ] . "','" . $data[ 'sf_name' ] . "','" . $data[ 'weburl' ] . "','" . $data[ 'divisionCode' ] . "','" . $data[ 'remarks' ] . "',getdate(),'0'";
        performQuery( $query );
        $results[ 'success' ] = true;
        outputJSON( $results );
        break;
    case "get/expensedetails":
        $divCode = $_GET[ 'divisionCode' ];
        $sfCode = $_GET[ 'sfCode' ];
        $monthexp = $_GET[ 'monthexp' ];
        $query1 = "select * FROM Trans_Expense_Head_App  where division_code ='" . $divCode . "' and SF_Code='" . $sfCode . "' and Expense_Month='" . $monthexp . "'";
        $result1 = performQuery( $query1 );
        $Sl_No = $result1[ 0 ][ 'Sl_No' ];
        $query2 = "select * FROM Trans_Expense_Detail_App  where Sl_No ='" . $Sl_No . "'";
        $result2 = performQuery( $query2 );
        if ( $result2 ) {
            outputJSON( $result2 );
        } else {
            outputJSON( [] );
        }
        break;
    case "get/camp_apprlist":
        $divcode = $_GET[ 'Division_Code' ];
        $Sf_code = $_GET[ 'Sf_code' ];
        $Camp_Type = $_GET[ 'Camp_Type' ];
        $Camp_Code = $_GET[ 'Camp_Code' ];
        $query = "Select * from Trans_opd_camp_approval where Division_Code='" . $divcode . "' and Camp_Code='" . $Camp_Code . "' and Sf_code='" . $Sf_code . "' and Camp_Type='" . $Camp_Type . "'";
        $result = performQuery( $query );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "get/manager_camplist":
        $divcode = $_GET[ 'Division_Code' ];
        $Sf_code = $_GET[ 'Sf_code' ];
        $Camp_Status = $_GET[ 'Camp_Status' ];
        $query = "Select * from Trans_opd_camp_approval where Division_Code='$divcode' and Sf_code='$Sf_code' and Camp_Status='$Camp_Status'";
        $result = performQuery( $query );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "get_campopdapprlist":
        $SF = $_GET[ 'SF' ];
        $Div = $_GET[ 'Div' ];
        $sql = "exec getCamp_opd_approvelist '" . $SF . "','" . $Div . "'";
        $result = performQuery( $sql );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "get/Camp_taggedlist":
        $divisionCode = $_GET[ 'divisionCode' ];
        $OPD_Code = $_GET[ 'OPD_Code' ];
        $query = "Select * from Map_OPDCamp_Drs_Details where OPD_Code='" . $OPD_Code . "' and Division_Code='" . $divisionCode . "'";
        $result = performQuery( $query );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "get/campdetails":
        $divCode = $_GET[ 'divisionCode' ];
        $sfCode = $_GET[ 'sfCode' ];
        $Campaign_Lock_flag = $_GET[ 'Campaign_Lock_flag' ];
        $dateTime = date( 'Y-m-d 00:00:000' );
        $sql = "EXEC getCamp_approvelist '$sfCode','$divCode','$dateTime','$Campaign_Lock_flag'";
        $result = performQuery( $sql );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "get/campaigndetails":
        $divCode = $_GET[ 'divisionCode' ];
        $sfCode = $_GET[ 'sfCode' ];
        $Campaign_Lock_flag = $_GET[ 'Campaign_Lock_flag' ];
        $dateTime = date( 'Y-m-d 00:00:000' );
        $sql = "EXEC getCampaign_approvelist '$sfCode','$divCode','$dateTime','$Campaign_Lock_flag'";
        $result = performQuery( $sql );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "get/expenselist":
        $divCode = $_GET[ 'divisionCode' ];
        $param_type = $_GET[ 'param_type' ];
        $query = "select * FROM fixed_variable_expense_setup  where division_code ='" . $divCode . "' and param_type !='F'";
        $result = performQuery( $query );
        if ( $result ) {
            outputJSON( $result );
        } else {
            outputJSON( [] );
        }
        break;
    case "travel_Distance":
        $sfCode = $_GET[ 'sfCode' ];
        $data = json_decode( $_POST[ 'data' ], true );
        $data1 = array_keys( $data[ 0 ] );
        $vals = $data[ 0 ][ $data1[ 0 ] ];
        $query = "select id from distance_Travelled where activity_date = '" . $vals[ "date" ] . "'";
        $idNo = performQuery( $query );
        $idValue = $idNo[ 0 ][ 'id' ];
        if ( count( $idNo ) > 0 ) {
            $query = "update distance_Travelled set travel_km = '" . $vals[ "km" ] . "' , remarks = '" . $vals[ "remarks" ] . "' , update_time = '" . $vals[ "submitted_Time" ] . "' where id ='$idValue'";
            performQuery( $query );
        } else {
            $query = "insert into distance_Travelled (sfName,sfCode,divisionCode,remarks,travel_km,emp_id,activity_date,submitted_time) select '" . $vals[ "sfName" ] . "','" . $vals[ "sfCode" ] . "','" . $vals[ "divisionCode" ] . "','" . $vals[ "remarks" ] . "','" . $vals[ "km" ] . "', sf_emp_id ,'" . $vals[ "date" ] . "','" . $vals[ "submitted_Time" ] . "' from Mas_Salesforce where Sf_Code = '$sfCode'";
            performQuery( $query );
        }
        $results[ 'success' ] = true;
        outputJSON( $results );
        break;
    case "imgupload":
        move_uploaded_file( $_FILES[ "imgfile" ][ "tmp_name" ], "../photos/" . $_FILES[ "imgfile" ][ "name" ] );
        break;
    case "profileupload":
        $sf = $_GET[ 'sf_code' ];
        move_uploaded_file( $_FILES[ "imgfile" ][ "tmp_name" ], "../Profile_Imgs/" . $sf . "_" . $_FILES[ "imgfile" ][ "name" ] );
        break;
    case "fileAttachment_record":
        $sf = $_GET[ 'sfCode' ];
        $div = $_GET[ 'divisionCode' ];
        $contentype = $_GET[ 'contenttype' ];
        $divs = explode( ",", $div . "," );
        $Owndiv = ( string )$divs[ 0 ];
        $file = $_FILES[ 'mediafile' ][ 'name' ];
        $info = pathinfo( $file );
        $file_name = basename( $file, '.' . $info[ 'extension' ] );
        $file_name = str_replace( "%20", "_", $file_name );
        $ext = $info[ 'extension' ];
        $fileName = $file_name . "_" . $sf . "_" . date( 'd_m_Y' ) . "." . $ext;
        $file_src = '../MasterFiles/media_recorder/' . $fileName;
        $result = array();
        if ( move_uploaded_file( $_FILES[ 'mediafile' ][ 'tmp_name' ], $file_src ) ) {
            $query = "select reporting_to_sf from mas_salesforce where sf_code='" . $sf . "'";
            $rep = performQuery( $query );
            $reprtTo = $rep[ 0 ][ 'reporting_to_sf' ];
            $query = "insert into Mas_MediaFiles_Info select '" . $sf . "','" . $reprtTo . "','" . $contentype . "','" . $fileName . "',getdate(),'" . $Owndiv . "',0";
            performQuery( $query );
            $result[ 'success' ] = true;
        } else {
            $result[ 'success' ] = $_FILES[ 'mediafile' ][ 'error' ];
        }
        outputJSON( $result );
        break;
    case "fileAttachment_mail":
        $sf = $_GET[ 'sf_code' ];
        $file = $_FILES[ 'imgfile' ][ 'name' ];
        $info = pathinfo( $file );
        $file_name = basename( $file, '.' . $info[ 'extension' ] );
        $ext = $info[ 'extension' ];
        $fileName = $file_name . "_" . $sf . "_" . date( 'd_m_Y' ) . "." . $ext;
        $file_src = '../MasterFiles/Mails/Attachment/' . $fileName;
        move_uploaded_file( $_FILES[ 'imgfile' ][ 'tmp_name' ], $file_src );
        break;
    case "deleteEntry":
        $data = json_decode( $_POST[ 'data' ], true );
        $arc = ( isset( $data[ 'arc' ] ) && strlen( $data[ 'arc' ] ) == 0 ) ? null : $data[ 'arc' ];
        $amc = ( isset( $data[ 'amc' ] ) && strlen( $data[ 'amc' ] ) == 0 ) ? null : $data[ 'amc' ];
        $result = delete_entry( $arc, $amc );
        break;
	case "vwChkTransApproval":
        $sfCode = $_GET['sfCode'];
        $query = "select * from vwChkTransApproval where Reporting_To_SF='$sfCode'";
        outputJSON(performQuery($query));
        break;
}
?>