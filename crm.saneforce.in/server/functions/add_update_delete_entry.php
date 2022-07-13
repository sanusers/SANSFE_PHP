<?php

function add_entry() {
    $sfCode = $_GET[ 'sfCode' ];
    $div = $_GET[ 'divisionCode' ];
    $MSL = $_GET[ 'Msl_No' ];
    $divs = explode( ",", $div . "," );
    $Owndiv = ( string )$divs[ 0 ];
    $data = json_decode( $_POST[ 'data' ], true );
    $today = date( 'Y-m-d 00:00:00' );
    $temp = array_keys( $data[ 0 ] );
    $vals = $data[ 0 ][ $temp[ 0 ] ];

    $HeaderId = ( isset( $_GET[ 'Head_id' ] ) && strlen( $_GET[ 'Head_id' ] ) == 0 ) ? null : $_GET[ 'Head_id' ];
    if ( $HeaderId != null ) {
        $query = "EXEC Delete_reject_dcr '$HeaderId' ";
        performQuery( $query );
    }

    $sql = "SELECT Employee_Id,case sf_type when 1 then 'MR' else 'MGR' End SF_Type FROM Mas_Salesforce_One where SF_code='" . $sfCode . "'";
    $result = performQuery( $sql );
    $IdNo = ( string )$result[ 0 ][ 'Employee_Id' ];
    $SFTyp = ( string )$result[ 0 ][ 'SF_Type' ];
    switch ( strtolower( $temp[ 0 ] ) ) {
        case "tbmydayplan":
            $today = date( 'Y-m-d 00:00:00', strtotime( $today ) );
            if ( $vals[ "location" ] == null ) {
                $location = "";
            } else {
                $location = $vals[ "location" ];
            }

            if ( $vals[ "TP_Doctor" ] == null ) {
                $vals[ "TP_Doctor" ] = "''";
            }

            if ( $vals[ "TP_DocCluster" ] == null ) {
                $vals[ "TP_DocCluster" ] = "''";
            }

            if ( $vals[ "TP_Worktype" ] == null ) {
                $vals[ "TP_Worktype" ] = "''";
            }

            if ( $vals[ "dcr_activity_date" ] != null && $vals[ "dcr_activity_date" ] != '' ) {
                $today = str_replace( "'", "", $vals[ "dcr_activity_date" ] );
            }

            $query = "insert into tbMyDayPlan select '" . $sfCode . "','" . $vals[ "sf_member_code" ] . "','$today','" . $vals[ "cluster" ] . "','" . $vals[ "remarks" ] . "','" . $Owndiv . "','" . $vals[ "wtype" ] . "','" . $vals[ "FWFlg" ] . "','" . $vals[ "ClstrName" ] . "','" . $vals[ "wtype_name" ] . "','$location','" . $vals[ "TpVwFlg" ] . "','" . $vals[ "TP_Doctor" ] . "','" . $vals[ "TP_DocCluster" ] . "','" . $vals[ "TP_Worktype" ] . "'";
            performQuery( $query );

            if ( str_replace( "'", "", $vals[ "FWFlg" ] ) != "F" ) {
                $query = "SELECT FWFlg, Confirmed FROM vwActivity_Report where SF_Code='" . $sfCode . "'  and cast(activity_date as datetime)=cast('$today' as datetime)";
                $result1 = performQuery( $query );
                if ( count( $result1 ) > 0 ) {
                    if ( $result1[ 0 ][ 'FWFlg' ] == 'L' && $result1[ 0 ][ 'Confirmed' ] != 2 && $result1[ 0 ][ 'Confirmed' ] != 3 ) {
                        $result = array();
                        $result[ 'success' ] = false;
                        $result[ 'msg' ] = 'Leave Post Already Updated';
                        outputJSON( $result );
                        die;
                    } else {
                        delete_AR_entry( $sfCode, $vals[ "wtype" ], $today );

                        $ARCd = "0";
                        $sql = "{call  svDCRMain_App(?,?," . $vals[ "wtype" ] . ",'" . str_replace( "'", "", $vals[ "cluster" ] ) . "',?,'" . str_replace( "'", "", $vals[ "remarks" ] ) . "',?)}";
                        $params = array( array( $sfCode, SQLSRV_PARAM_IN ),
                            array( $today, SQLSRV_PARAM_IN ),
                            array( $Owndiv, SQLSRV_PARAM_IN ),
                            array( & $ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_VARCHAR( 50 ) ) );
                        performQueryWP( $sql, $params );
                    }
                } else {
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
            break;
        case "checkin":
            $date = date( 'Y-m-d' );
            $query = "insert into Dcr_checkin(Cust_id,Cust_name,Sf_Code,Division_Code,Activity_date,Checkin_time,Checkin_Lat, Checkin_Long, Checkout_Lat,Checkout_Long,Status,Checkin_addrs) select '" . $vals[ "cust_id" ] . "','" . $vals[ "cust_name" ] . "','" . $sfCode . "','" . $Owndiv . "','$date','" . $vals[ "intime" ] . "','" . $vals[ "lat" ] . "','" . $vals[ "long" ] . "','','','0','" . $vals[ "cust_add" ] . "'";
            performQuery( $query );
            break;
        case "checkout":
            $date = date( 'Y-m-d' );
            $dateTime = date( 'Y-m-d H:i' );
            $query = "select top 1 ID from Dcr_checkin where sf_code='$sfCode' and cust_id=" . $vals[ "cust_id" ] . " order by ID DESC";
            $result = performQuery( $query );
            $id = $result[ 0 ][ 'ID' ];
            $query = "update Dcr_checkin set Checkout_time='$dateTime',Status='1',Checkout_Lat='" . $vals[ "lat" ] . "',Checkout_Long='" . $vals[ "long" ] . "',Checkout_addrs='" . $vals[ "cust_add" ] . "' where ID='$id'";
            performQuery( $query );
            break;
        case "tp_attendance":
            $dateTime = date( 'Y-m-d H:i' );
            $date = date( 'Y-m-d' );
            $lat = $vals[ 'lat' ];
            $long = $vals[ 'long' ];
            $Day_addr = $vals[ 'address' ];
            $update = $_GET[ 'update' ];
            if ( $update == 0 ) {
                $query = "exec Attendance_entry '$sfCode','$Owndiv','$dateTime','$lat','$long','$date','$Day_addr'";
                $result = performQuery( $query );
            } else {
                $query = "select id from TP_Attendance_App where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time)) = '$date' order by id desc";
                $tr = performQuery( $query );
                $id = $tr[ 0 ][ 'id' ];

                $query = "update TP_Attendance_App set End_Lat=$lat,End_Long=$long,End_Time='$dateTime',End_addres='$Day_addr' where id=$id";
                performQuery( $query );

                $query = "select ID from Attendance_history where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='$date' order by id desc";
                $tr1 = performQuery( $query );
                $id1 = $tr1[ 0 ][ 'ID' ];

                $query = "update Attendance_history set End_Lat=$lat,End_Long=$long,End_Time='$dateTime', End_addres='$Day_addr' where ID=$id1";
                performQuery( $query );
                $result = [];
                $result[ "msg" ] = "1";
            }
            outputJSON( $result );
            break;
        case "chemists_master":
            $query = "SELECT isNull(max(Chemists_Code),0)+2 as RwID FROM Mas_Chemists";
            $result = performQuery( $query );
            $pk = ( int )$result[ 0 ][ 'RwID' ];

            $query = "insert into Mas_Chemists(Chemists_Code,Chemists_Name,Chemists_Address1,Territory_Code,Chemists_Phone,Chemists_Contact,Division_Code,Cat_Code,Chemists_Active_Flag,Sf_Code,Created_Date,Created_By) select '" . $pk . "'," . $vals[ "chemists_name" ] . "," . $vals[ "Chemists_Address1" ] . "," . $vals[ "town_code" ] . "," . $vals[ "Chemists_Phone" ] . ",'','" . $Owndiv . "','','0','" . $sfCode . "','" . date( 'Y-m-d H:i:s' ) . "','Apps'";
            performQuery( $query );
            break;
        case "expense_miscellaneous":
            for ( $i = 0; $i < count( $vals ); $i++ ) {
                $query = "insert into Exp_miscellaneous_zoom (Expense_typ,Expense_Date,Expense_Parameter_Code,Expense_Parameter_Name,Amt,SF_Code,Expense_month,	expense_year,Division_Code) select '" . $vals[ $i ][ 'Expense_type' ] . "','" . $vals[ $i ][ 'Expense_date' ] . "','" . $vals[ $i ][ 'Expense_Parameter_Code' ] . "','" . $vals[ $i ][ 'Expense' ] . "','" . $vals[ $i ][ 'amount' ] . "','" . $sfCode . "','" . $vals[ $i ][ 'Expense_month' ] . "','" . $vals[ $i ][ 'Expense_year' ] . "','" . $Owndiv . "'";
                performQuery( $query );
            }
            break;
        case "unlisted_doctor_master":
            $query = "SELECT isNull(max(UnListedDrCode),0)+1 as RwID FROM Mas_UnListedDr";
            $result = performQuery( $query );
            $pk = ( int )$result[ 0 ][ 'RwID' ];
            if ( $vals[ "unlisted_doctor_pincode" ] == null || $vals[ "unlisted_doctor_pincode" ] == "undefined" ) {
                $vals[ "unlisted_doctor_pincode" ] = "''";
                $vals[ "unlisted_doctor_mobileno" ] = "''";
                $vals[ "unlisted_doctor_email" ] = "''";
            }
            if ( $vals[ "unlisted_doctor_addr1" ] == null || $vals[ "unlisted_doctor_addr1" ] == "undefined" ) {
                $vals[ "unlisted_doctor_addr1" ] = "''";
                $vals[ "unlisted_doctor_addr2" ] = "''";
            }
            $query = "insert into Mas_UnListedDr(UnListedDrCode,UnListedDr_Name,UnListedDr_Address1,UnListedDr_Address2, Doc_Special_Code, Doc_Cat_Code,Territory_Code,UnListedDr_Active_Flag,UnListedDr_Sl_No,Division_Code,SLVNo, Doc_QuaCode,Doc_ClsCode,Sf_Code,UnListedDr_Created_Date,Created_By,UnListedDr_PinCode,UnListedDr_Phone, UnListedDr_Mobile,UnListedDr_Email) select '" . $pk . "'," . $vals[ "unlisted_doctor_name" ] . "," . $vals[ "unlisted_doctor_addr1" ] . "," . $vals[ "unlisted_doctor_addr2" ] . "," . $vals[ "unlisted_specialty_code" ] . "," . $vals[ "unlisted_cat_code" ] . "," . $vals[ "town_code" ] . ",0,'" . $pk . "','" . $Owndiv . "','" . $pk . "'," . $vals[ "unlisted_qulifi" ] . "," . $vals[ "unlisted_class" ] . ",'" . $sfCode . "','" . date( 'Y-m-d H:i:s' ) . "','Apps'," . $vals[ "unlisted_doctor_pincode" ] . "," . $vals[ "unlisted_doctor_mobileno" ] . "," . $vals[ "unlisted_doctor_mobileno" ] . "," . $vals[ "unlisted_doctor_email" ] . "";
            performQuery( $query );
            break;
        case "quiz_results":
            $quizresults = $vals[ 0 ];
            $first = $vals[ 1 ][ 0 ];
            $surveyId = $first[ 'survey_id' ];
            $firstStartTime = $first[ 'start' ];
            $firstEndTime = $first[ 'end' ];
            if ( $first[ 'NoOfAttempts' ] == "2" ) {
                $second = $vals[ 2 ][ 0 ];
                $secStartTime = $second[ 'start' ];
                $secEndTime = $second[ 'end' ];
            } else {
                $secStartTime = "";
                $secEndTime = "";
            }
            for ( $i = 0; $i < count( $quizresults ); $i++ ) {
                $quesid = $quizresults[ $i ][ 'Question_Id' ];
                $inputid = $quizresults[ $i ][ 'input_id' ];
                $secinputid = $quizresults[ $i ][ 'Sec_input_id' ];

                $query = "select isnull(max(max_sl_no),0)+1 id from Quiz_MaxSlNo where sf_code='$sfCode'";
                $tr = performQuery( $query );
                $id = $tr[ 0 ][ 'id' ];
                $code = $sfCode . '-' . $id;

                $query = "select sf_name sfName from mas_salesforce where sf_code='$sfCode'";
                $tr = performQuery( $query );
                $sfName = $tr[ 0 ][ 'sfName' ];

                if ( $id == "1" ) {
                    $query = "insert into Quiz_MaxSlNo select '$sfCode',$Owndiv,$id";
                    performQuery( $query );
                } else {
                    $query = "update Quiz_MaxSlNo set max_sl_no=$id where sf_code='$sfCode'";
                    performQuery( $query );
                }

                $query = "delete from quiz_result where Sf_Code='$sfCode' and Quiz_Id='$quesid' and Division_Code='$Owndiv' and Survey_Id='$surveyId'";
                performQuery( $query );

                $query = "insert into quiz_result(Result_Id,Sf_Code,Sf_Name,Division_Code,Quiz_Id,Input_Id,Status,Survey_Id,Created_Date,Second_Input_Id,First_Start_time,First_End_time,Second_Start_time,Second_End_time) select '$code','$sfCode','$sfName','$Owndiv','$quesid','$inputid',0,'$surveyId',getdate(),'$secinputid','$firstStartTime','$firstEndTime','$secStartTime','$secEndTime'";
                performQuery( $query );

                $query = "insert into trackquiz_result(Result_Id,Sf_Code,Sf_Name,Division_Code,Quiz_Id,Input_Id,Status,Survey_Id,Created_Date,Second_Input_Id,First_Start_time,First_End_time,Second_Start_time,Second_End_time) select '$code','$sfCode','$sfName','$Owndiv','$quesid','$inputid',0,'$surveyId',getdate(),'$secinputid','$firstStartTime','$firstEndTime','$secStartTime','$secEndTime'";
                performQuery( $query );
            }
            $query = "update Processing_UserList set Process_Status='F' where SurveyId='$surveyId' and sf_code='$sfCode'";
            performQuery( $query );
            $result[ 'success' ] = true;
            return outputJSON( $result );
            break;
        case "mcl_details":
            $primary_key = "ListedDrCode";
            $row_id = $data[ 0 ][ 'MCL_Details' ][ 'doctorCode' ];
            $data[ 0 ][ 'MCL_Details' ][ 'Update_Mode' ] = "'Apps'";
            unset( $data[ 0 ][ 'MCL_Details' ][ 'doctorCode' ] );
            unset( $data[ 0 ][ 'MCL_Details' ][ 'workPlace' ] );
            unset( $data[ 0 ][ 'MCL_Details' ][ 'Pri_Appt_Meet' ] );
            unset( $data[ 0 ][ 'MCL_Details' ][ 'Drs_Meet_Day' ] );
            unset( $data[ 0 ][ 'MCL_Details' ][ 'Add_Hos_Nur' ] );
            unset( $data[ 0 ][ 'MCL_Details' ][ 'email' ] );
            unset( $data[ 0 ][ 'MCL_Details' ][ 'mobile' ] );
            foreach ( $data[ 0 ][ 'MCL_Details' ] as $col => $val ) {
                $cols[] = $col . " = " . $val;
            }
            $query = "UPDATE Mas_ListedDr set " . join( ", ", $cols ) . " where $primary_key = $row_id";
            performQuery( $query );
            break;
        case "savecamp_approval":
            $sql = "SELECT isNull(max(Trans_sl_No),0)+1 as RwID FROM Trans_opd_camp_approval";
            $tRw = performQuery( $sql );
            $pk = ( int )$tRw[ 0 ][ 'RwID' ];
            $div = $vals[ "Division_Code" ];
            $divs = explode( ",", $div . "," );
            $Owndiv = ( string )$divs[ 0 ];

            $query = "insert into Trans_opd_camp_approval(Trans_sl_No,Division_Code,Camp_Name,Camp_Code,Camp_Type,Doctor_Name,Doctor_Code,Date_Camp,Place_Camp,Expected_Patients,Exp_Bussiness,ROI_From_Month,ROI_From_Year,ROI_To_Month,ROI_To_Year,Entry_Date,Sf_Code,Sf_Name,Entry_Sf_Code,Entry_Sf_name,Camp_Status,Entry_Mode)select '$pk','$Owndiv','" . $vals[ "Camp_Name" ] . "','" . $vals[ "Camp_Code" ] . "','" . $vals[ "Camp_Type" ] . "','" . $vals[ "Doctor_Name" ] . "','" . $vals[ "Doctor_Code" ] . "','" . $vals[ "Date_Camp" ] . "','" . $vals[ "Place_Camp" ] . "','" . $vals[ "Expected_Patients" ] . "','" . $vals[ "Exp_Bussiness" ] . "','" . $vals[ "ROI_From_Month" ] . "','" . $vals[ "ROI_From_Year" ] . "','" . $vals[ "ROI_To_Month" ] . "','" . $vals[ "ROI_To_Year" ] . "','" . $vals[ "Entry_Date" ] . "','" . $vals[ "Sf_Code" ] . "','" . $vals[ "Sf_Name" ] . "','" . $vals[ "Entry_Sf_Code" ] . "','" . $vals[ "Entry_Sf_name" ] . "','0','Apps'";
            performQuery( $query );

            $query1 = "update Map_OPDCamp_Drs_Details set doccamp_flag='1' where SF_Code='" . $vals[ "Sf_Code" ] . "' and OPD_Code='" . $vals[ "Camp_Code" ] . "' and DRCode='" . $vals[ "Doctor_Code" ] . "'";
            performQuery( $query1 );
            break;
        case "savecamp_cme_approval":
            $sql = "SELECT isNull(max(Trans_sl_No),0)+1 as RwID FROM Trans_opd_camp_approval";
            $tRw = performQuery( $sql );
            $pk = ( int )$tRw[ 0 ][ 'RwID' ];

            $query = "insert into Trans_opd_camp_approval(Trans_sl_No,Camp_Name,Camp_Code,Camp_Type,CME_Participant_List,CME_Date,CME_Venue,CME_Start_Date,CME_End_Date,CME__Other_Speaker_Name,CME_Speaker_Code,CME_Speaker_Name,Entry_Date,Sf_Code,Sf_Name,Entry_Sf_Code,Entry_Sf_name,Camp_Status,Entry_Mode)select '$pk','" . $vals[ "Camp_Name" ] . "','" . $vals[ "Camp_Code" ] . "','" . $vals[ "Camp_Type" ] . "','" . $vals[ "CME_Participant_List" ] . "','" . $vals[ "CME_Date" ] . "','" . $vals[ "CME_Venue" ] . "','" . $vals[ "CME_Start_Date" ] . "','" . $vals[ "CME_End_Date" ] . "','" . $vals[ "CME__Other_Speaker_Name" ] . "','" . $vals[ "CME_Speaker_Code" ] . "','" . $vals[ "CME_Speaker_Name" ] . "','" . $vals[ "Entry_Date" ] . "','" . $vals[ "Sf_Code" ] . "','" . $vals[ "Sf_Name" ] . "','" . $vals[ "Entry_Sf_Code" ] . "','" . $vals[ "Entry_Sf_name" ] . "','0','Apps'";
            performQuery( $query );
            break;
        case "Camp_TagApproval":
            $div = $_GET[ 'divisionCode' ];
            $divs = explode( ",", $div . "," );
            $data = json_decode( $_POST[ 'data' ], true );
            $tag_data = $data[ 0 ][ 'Camp_TagApproval' ];
            $query2 = "delete from  Map_OPDCamp_Drs_Details where OPD_Code='" . $tag_data[ 0 ][ 'OPD_Code' ] . "' and  Division_Code='" . $tag_data[ 0 ][ 'Division_Code' ] . "'";
            performQuery( $query2 );
            for ( $i = 0; $i < count( $tag_data ); $i++ ) {
                $query = "update mas_listeddr set Doc_SubCatCode=replace(Doc_SubCatCode,'," . $tag_data[ $i ][ 'OPD_Code' ] . ",',',')+'" . $tag_data[ $i ][ 'OPD_Code' ] . ",' where ListedDrCode='" . $tag_data[ $i ][ 'DRCode' ] . "'";
                performQuery( $query );

                $query = "insert into Map_OPDCamp_Drs_Details(SF_Code,OPD_Code,DRCode,Active_Flag,Division_Code,Map_Date,ApproveDt,doccamp_flag)select '" . $tag_data[ $i ][ 'SF_Code' ] . "','" . $tag_data[ $i ][ 'OPD_Code' ] . "','" . $tag_data[ $i ][ 'DRCode' ] . "','" . $tag_data[ $i ][ 'Active_Flag' ] . "','" . $tag_data[ $i ][ 'Division_Code' ] . "','" . $tag_data[ $i ][ 'Map_Date' ] . "',null,'0'";
                performQuery( $query );
            }
            $query1 = "select * from  mas_campaign_lock where Doc_SubCatCode='" . $tag_data[ 0 ][ 'OPD_Code' ] . "' and  Division_Code='" . $tag_data[ 0 ][ 'Division_Code' ] . "'";
            $result = performQuery( $query1 );
            if ( count( $result ) > 0 ) {
                $query2 = "update  mas_campaign_lock set Campaign_Lock_flag='1' where Doc_SubCatCode='" . $tag_data[ 0 ][ 'OPD_Code' ] . "' and  Division_Code='" . $tag_data[ 0 ][ 'Division_Code' ] . "'";
                performQuery( $query2 );
            } else {
                $query3 = "insert into mas_campaign_lock(SF_Code,Division_Code,Campaign_Lock_flag,Doc_SubCatCode,Camp_Mode,Entry_Mode,Entry_Mode_ref)select '" . $tag_data[ 0 ][ 'SF_Code' ] . "','" . $tag_data[ 0 ][ 'Division_Code' ] . "','1','" . $tag_data[ 0 ][ 'OPD_Code' ] . "','Campaign','Apps','TR'";
                performQuery( $query3 );
            }
            break;
        case "approvereject_tagcamp":
            $div = $_GET[ 'divisionCode' ];
            $divs = explode( ",", $div . "," );
            $mode = $_GET[ 'mode' ];
            $OPD_Code = $_GET[ 'OPD_Code' ];
            if ( $mode == 'approve' ) {
                $query1 = "update  mas_campaign_lock set Campaign_Lock_flag='2' where Doc_SubCatCode='$OPD_Code' and  Division_Code='$div'";
                performQuery( $query1 );
            } else {
                $query2 = "update  mas_campaign_lock set Campaign_Lock_flag='3'where Doc_SubCatCode='$OPD_Code' and  Division_Code='$div'";
                performQuery( $query2 );
            }
            break;
        case "approvereject_camp":
            $div = $_GET[ 'divisionCode' ];
            $divs = explode( ",", $div . "," );
            $mode = $_GET[ 'mode' ];
            $Camp_Code = $_GET[ 'Camp_Code' ];
            $Sf_code = $_GET[ 'Sf_code' ];
            $Camp_Type = $_GET[ 'Camp_Type' ];
            $dr_code = $_GET[ 'dr_code' ];
            if ( $mode == 'approve' ) {
                $query1 = "update  Trans_opd_camp_approval set Camp_Status='1' where Division_Code='$div' and Camp_Code='$Camp_Code' and Sf_code='$Sf_code' and Camp_Type='$Camp_Type'";
                performQuery( $query1 );

                $query2 = "update Map_OPDCamp_Drs_Details set doccamp_flag='2' where SF_Code='$Sf_code' and OPD_Code='$Camp_Code' and DRCode='$dr_code'";
                performQuery( $query2 );
            } else {
                $query2 = "update  Trans_opd_camp_approval set Camp_Status='2' where Division_Code='$div' and Camp_Code='$Camp_Code' and Sf_code='$Sf_code' and Camp_Type='$Camp_Type'";
                performQuery( $query2 );

                $query1 = "update Map_OPDCamp_Drs_Details set doccamp_flag='3' where SF_Code='$Sf_code' and OPD_Code='$Camp_Code' and DRCode='$dr_code'";
                performQuery( $query1 );
            }
            break;
        case "Map_GEO_Customers":
            $addr = "'" . getaddress( str_replace( "'", "", $vals[ "lat" ] ), str_replace( "'", "", $vals[ "long" ] ) ) . "'";
            $sql = "SELECT isNull(max(MapId),0)+1 as MapId FROM Map_GEO_Customers";
            $topr = performQuery( $sql );
            $pk = ( int )$topr[ 0 ][ 'MapId' ];

            $sql = "insert into Map_GEO_Customers(MapId, Cust_Code, lat, long, addrs, StatFlag, Division_code) select $pk," . $vals[ "Cust_Code" ] . "," . $vals[ "lat" ] . "," . $vals[ "long" ] . "," . $addr . "," . $vals[ "StatFlag" ] . ",$Owndiv";
            performQuery( $sql );
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
            $res = $data[ 0 ][ 'expense' ];
            $date = date( 'Y-m-d H:i:s' );
            $update = $_GET[ 'update' ];
            $dcrdate = date( 'd-m-Y' );

            $divCode = $_GET[ 'divisionCode' ];
            $divisionCode = explode( ",", $divCode );
            $desig = $_GET[ 'desig' ];
            $sfCode = $_GET[ 'sfCode' ];
            $sfName = $res[ 'sfName' ];
            $expenseAllowance = $res[ 'allowance' ];
            $expenseDistance = $res[ 'distance' ];
            $expenseFare = $res[ 'fare' ];
            $total = $res[ 'tot' ];
            $additionalTot = $res[ 'additionalTot' ];
            $wcode = $res[ 'worktype' ];
            $wname = $res[ 'worktype_name' ];
            $place = $res[ 'place' ];
            $placeno = $res[ 'placeno' ];
            $sql = "SELECT isNull(max(sl_no),0)+1 as RwID FROM Trans_FM_Expense_Head";
            $tRw = performQuery( $sql );
            $pk = ( int )$tRw[ 0 ][ 'RwID' ];
            if ( $update == 1 ) {
                updateEntry( $sfCode );
            }

            $sql = "insert into Trans_FM_Expense_Head(Sf_Code,Month,Year,sndhqfl,Division_Code,snd_dt,Sf_Name) select '$sfCode',MONTH('$date'),YEAR('$date'),0,$divisionCode[0],'$date'," . $sfName . "";
            performQuery( $sql );

            $sql = "insert into Trans_FM_Expense_Detail(DCR_Date,Expense_wtype_Code,Expense_wtype_Name,Place_of_Work,Expense_Place_No,Division_Code,Expense_Allowance,Expense_Distance,Expense_Fare,Created_Date,LastUpdt_Date,Sf_Name,Sf_Code,Expense_Total) select '$dcrdate',$wcode,$wname,$place,$placeno,$divisionCode[0],$expenseAllowance,$expenseDistance,$expenseFare,'$date','$date',$sfName,'$sfCode',$total";
            performQuery( $sql );

            $sql = "SELECT sl_no, Total_Allowance, Total_Distance, Total_Fare, Total_Expense, Total_Additional_Amt FROM Trans_Expense_Amount_Detail where Month=MONTH('$date') and year=YEAR('$date') and Sf_Code='$sfCode'";
            $tRw = performQuery( $sql );
            if ( empty( $tRw ) ) {
                $additionalAmount = $additionalTot + $total;
                $sql = "insert into Trans_Expense_Amount_Detail(Sf_Code,Month,Year,Division_Code,Sf_Name,Total_Allowance,Total_Distance,Total_Fare,Total_Expense,Total_Additional_Amt,Grand_Total) select '$sfCode',MONTH('$date'),YEAR('$date'),$divisionCode[0], $sfName,$expenseAllowance,$expenseDistance,$expenseFare,$total,$additionalTot,$additionalAmount";
                performQuery( $sql );
            } else {
                $totAllowance = $tRw[ 0 ][ 'Total_Allowance' ] + $expenseAllowance;
                $totDistance = $tRw[ 0 ][ 'Total_Distance' ] + $expenseDistance;
                $totFare = $tRw[ 0 ][ 'Total_Fare' ] + $expenseFare;
                $totalExpense = $tRw[ 0 ][ 'Total_Expense' ] + $total;
                $totAdditionalAmt = $tRw[ 0 ][ 'Total_Additional_Amt' ] + $additionalTot;
                $grandTotal = $totalExpense + $totAdditionalAmt;
                $slNo = $tRw[ 0 ][ 'sl_no' ];
                $sql = "update Trans_Expense_Amount_Detail set Total_Allowance=$totAllowance,Total_Distance=$totDistance,Total_Fare=$totFare,Total_Expense=$totalExpense,Total_Additional_Amt=$totAdditionalAmt,Grand_Total=$grandTotal where Sl_No='$slNo'";
                performQuery( $sql );
            }
            $extraDet = $res[ 'extraDetails' ];
            for ( $i = 0; $i < count( $extraDet ); $i++ ) {
                $parameterName = $extraDet[ $i ][ 'parameter' ];
                $amount = $extraDet[ $i ][ 'amount' ];
                $type = $extraDet[ $i ][ 'type' ];
                if ( $type == true )
                    $type = 0;
                else
                    $type = 1;
                if ( !empty( $parameterName ) )
                    $sql = "insert into Trans_Additional_Exp(Sf_Code,Month,Year,Division_Code,Created_Date,LastUpdt_Date,Created_By,Parameter_Name,Amount,Cal_Type,Confirmed) select '$sfCode',MONTH('$date'),YEAR('$date'),$divisionCode[0],'$date','$date','$sfCode','$parameterName','$amount','$type',0";
                performQuery( $sql );
            }
            $resp[ "success" ] = true;
            echo json_encode( $resp );
            break;

        case "Tour_Plan":
            $divCode = $_GET[ 'divisionCode' ];
            $divisionCode = explode( ",", $divCode );
            $desig = $_GET[ 'desig' ];
            $objective = $data[ 0 ][ 'Tour_Plan' ][ 'objective' ];
            $tourDate = $data[ 0 ][ 'Tour_Plan' ][ 'Tour_Date' ];
            $worktype_code = $data[ 0 ][ 'Tour_Plan' ][ 'worktype_code' ];
            $worktype_name = $data[ 0 ][ 'Tour_Plan' ][ 'worktype_name' ];
            $worktype_code2 = $data[ 0 ][ 'Tour_Plan' ][ 'worktype_code2' ];
            $worktype_name2 = $data[ 0 ][ 'Tour_Plan' ][ 'worktype_name2' ];
            $worktype_code3 = $data[ 0 ][ 'Tour_Plan' ][ 'worktype_code3' ];
            $worktype_name3 = $data[ 0 ][ 'Tour_Plan' ][ 'worktype_name3' ];
            $worked_with_code = $data[ 0 ][ 'Tour_Plan' ][ 'Worked_with_Code' ];
            $worked_with_name = $data[ 0 ][ 'Tour_Plan' ][ 'Worked_with_Name' ];
            $RouteCode = $data[ 0 ][ 'Tour_Plan' ][ 'RouteCode' ];
            $RouteName = $data[ 0 ][ 'Tour_Plan' ][ 'RouteName' ];
            $RouteCode2 = $data[ 0 ][ 'Tour_Plan' ][ 'RouteCode2' ];
            $RouteName2 = $data[ 0 ][ 'Tour_Plan' ][ 'RouteName2' ];
            $RouteCode3 = $data[ 0 ][ 'Tour_Plan' ][ 'RouteCode3' ];
            $RouteName3 = $data[ 0 ][ 'Tour_Plan' ][ 'RouteName3' ];
            $sfName = $data[ 0 ][ 'Tour_Plan' ][ 'sfName' ];
            $sql = "delete from Trans_TP_One WHERE SF_Code ='" . $sfCode . "' and Tour_Date=cast($tourDate as datetime)";
            performQuery( $sql );
            $sql = "insert into Trans_TP_One(SF_Code,Tour_Month,Tour_Year,Submission_date,Tour_Date,WorkType_Code_B,Worktype_Name_B,Territory_Code1,Objective,Worked_With_SF_Code,Division_Code,Tour_Schedule1,Worked_With_SF_Name,TP_Sf_Name,Confirmed,Territory_Code2,Tour_Schedule2,Territory_Code3,Tour_Schedule3,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,Change_Status) select '" . $sfCode . "',MONTH($tourDate),YEAR($tourDate),'" . date( 'Y-m-d' ) . "',$tourDate,$worktype_code,$worktype_name,$RouteCode,$objective,$worked_with_code," . $divisionCode[ 0 ] . ",$RouteName,$worked_with_name,$sfName,0,$RouteCode2,$RouteName2,$RouteCode3,$RouteName3,$worktype_code2,$worktype_name2,$worktype_code3,$worktype_name3,0";
            performQuery( $sql );
            $resp[ "success" ] = true;
            echo json_encode( $resp );
            break;
        case "TourPlanSubmit":
            $month = $_GET[ 'month' ];
            $year = $_GET[ 'year' ];
            $sql = "update Trans_TP_One set Change_Status=1 where Tour_Month=$month and Tour_Year=$year and Sf_Code='$sfCode'";
            performQuery( $sql );
            $resp[ "success" ] = true;
            echo json_encode( $resp );
            break;
        case "DevApproval":
            $slno = $_GET[ 'slno' ];
            $sql = "update DCR_MissedDates set status=4 where sl_no='$slno'";
            performQuery( $sql );
            break;
        case "TPApproval":
            $month = $_GET[ 'month' ];
            $year = $_GET[ 'year' ];
            $code = $_GET[ 'code' ];

            global $data, $conn, $NeedRollBack;
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
            echo json_encode( $resp );
            break;
        case "TPReject":
            $month = $_GET[ 'month' ];
            $year = $_GET[ 'year' ];
            $code = $_GET[ 'code' ];
            $sql = "insert into TP_Reject_B_Mgr(SF_Code,Tour_Month,Tour_Year,Reject_date,Division_Code,Rejection_Reason) select '" . $code . "',$month,$year,'" . date( 'Y-m-d H:i' ) . "',$Owndiv," . $vals[ 'reason' ] . "";
            performQuery( $sql );

            $sql = "update Trans_TP_One set Change_Status=2,Confirmed=0,Rejection_Reason=" . $vals[ 'reason' ] . " where Tour_Month=$month and Tour_Year=$year and Sf_Code='$code'";
            performQuery( $sql );
            $resp[ "success" ] = true;
            echo json_encode( $resp );
            break;
        case "LeaveApproval":
            $leaveid = $_GET[ 'leaveid' ];
            $RSF = $_GET[ 'sfCode' ];
            $sql = "exec iOS_svLeaveAppRej  '" . $leaveid . "','0','','" . $RSF . "','Apps'";
            performQuery( $sql );
            $resp[ "success" ] = true;
            echo json_encode( $resp );
            break;
        case "LeaveReject":
            global $data;
            $SF = ( string )$vals[ 'Sf_Code' ];
            $LvID = ( string )$_GET[ 'leaveid' ];
            $query = "exec iOS_svLeaveAppRej  '" . $LvID . "','1','" . $vals[ 'reason' ] . "','" . $SF . "'";
            performQuery( $query );
            $result[ "Qry" ] = $query;
            $result[ "success" ] = true;
            $msg = "Your leave request is rejected for " . $vals[ 'reason' ] . "";
            notification( $SF, $msg, 0 );
            break;
        case "LeaveForm":
            $name = $_GET[ 'sf_name' ];
            $sql = "SELECT isNull(max(Leave_Id),0)+1 as RwID FROM Mas_Leave_Form";
            $tRw = performQuery( $sql );
            $pk = ( int )$tRw[ 0 ][ 'RwID' ];
            if ( $vals[ 'Leave_Type' ] == '' || $vals[ 'Leave_Type' ] == null ) {
                die;
            }
            $query = "exec iOS_svLeaveApp '" . $sfCode . "','" . $vals[ 'From_Date' ] . "','" . $vals[ 'To_Date' ] . "','" . $vals[ 'No_of_Days' ] . "','" . $vals[ 'Leave_Type' ] . "','" . $vals[ 'Reason' ] . "','" . $vals[ 'address' ] . "'";
            performQuery( $query );

            $sql = "SELECT DeviceRegId FROM Access_Table where sf_code=(select Reporting_To_SF from mas_salesforce_one where Sf_Code='$sfCode')";
            $device = performQuery( $sql );
            $reg_id = $device[ 0 ][ 'DeviceRegId' ];
            if ( !empty( $reg_id ) ) {
                $msg = "Leave Application Received";
                notification( $sfCode, $msg, 0 );
            }
            $sql = "SELECT sf_type FROM Mas_Salesforce_One where Sf_Code='$sfCode'";
            $sfType = performQuery( $sql );
            $days = $vals[ 'No_of_Days' ];
            $date = $vals[ 'From_Date' ];
            for ( $i = 1; $i <= $days; $i++ ) {
                $query = "exec ChkandPostLeaveDt 0,'$sfCode'," . $sfType[ 0 ][ 'sf_type' ] . ",$Owndiv,'$date','','apps'";
                $results = performQuery( $query );
                $date = date( 'Y-m-d', strtotime( $date . ' + 1 days' ) );
            }
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
            SaveRCPAEntry( $ARCd, $ARDCd, $mData[ 0 ][ "RCPAEntry" ], $RCPADt );
            break;
        case "Order_Product":
            $sfCode = $_GET[ 'sfCode' ];
            $sfName = $_GET[ 'sfName' ];
            $div = $_GET[ 'divisionCode' ];
            $divs = explode( ",", $div . "," );
            $Owndiv = ( string )$divs[ 0 ];
            $ord_date = date( 'Y-m-d H:i:s' );
            $pData = json_decode( $_POST[ 'data' ], true );

            $sql = "select isnull(max(Trans_SlNo),0)+1 sl_no from Trans_Order_Book_Head";
            $tr = performQuery( $sql );
            $trans_slh = $tr[ 0 ][ sl_no ];
            $orderData = $pData[ 0 ][ "Order_Product" ];
            $ordmonth = $orderData[ 'order_date' ];
            $orderDetail = $pData[ 1 ][ "Order_Product_Details" ];
            $sql = "insert into Trans_Order_Book_Head (Sf_Code,Sf_Name,Division_Code,Stockist_Code,Stockist_Name,Mode_of_Order,DHP_Code,DHP_Name,Sub_Div_Code,Order_Date,Order_Month,Order_Year,Entry_Mode,Created_Date,Order_Flag,Order_type) select '$sfCode','$sfName','$Owndiv','" . $orderData[ "Stockist_id" ] . "','" . $orderData[ "Stockist_name" ] . "','" . $orderData[ "Selected_mode" ] . "','" . $orderData[ "DHP_Code" ] . "','" . $orderData[ "DHP_Name" ] . "','48','" . $orderData[ "order_date" ] . "','" . $orderData[ "month" ] . "','" . $orderData[ "year" ] . "','Apps','$ord_date','0','" . $orderData[ "Order_Type" ] . "'";
            performQuery( $sql );
            for ( $j = 0; $j < count( $orderDetail ); $j++ ) {
                $sql = "insert into Trans_Order_Book_Detail (Trans_SlNo,Sf_Code,Product_Code,Product_Name,Pack,Order_Sal_Qty,Order_Free_Qty,Order_Rate,Order_Value,NRV_Value,TotNet_Amt,Division_Code,Order_Sch_Qty,Order_Free_Value,Discount,Remarks,Order_tax,Order_discount) select '$trans_slh','$sfCode','" . $orderDetail[ $j ][ "product_code" ] . "','" . $orderDetail[ $j ][ "product_Name" ] . "','','" . $orderDetail[ $j ][ "Product_Order_Qty" ] . "','" . $orderDetail[ $j ][ "Additional_Qty" ] . "','" . $orderDetail[ $j ][ "product_Rate" ] . "','" . $orderDetail[ $j ][ "Order_value" ] . "','" . $orderDetail[ $j ][ "NRV" ] . "','','$Owndiv','" . $orderDetail[ $j ][ "Scheme_Quantity" ] . "','" . $orderDetail[ $j ][ "FreeQTy_value" ] . "','" . $orderDetail[ $j ][ "product_Discount" ] . "','" . $orderDetail[ $j ][ "feedback" ] . "','" . $orderDetail[ $j ][ "product_Tax" ] . "','" . $orderDetail[ $j ][ "Discount" ] . "'";
                performQuery( $sql );
            }
            break;
        case "DCRApproval":
            $date = $_GET[ 'date' ];
            $code = $_GET[ 'code' ];
            $date = str_replace( '/', '-', $date );
            $date = date( 'Y-m-d', strtotime( $date ) );
            $sql = "EXEC ApproveDCRByDt '" . $code . "','$date'";
            performQuery( $sql );
            $response[ "success" ] = true;
            echo json_encode( $$response );
            break;
        case "DCRReject":
            $date = $_GET[ 'date' ];
            $code = $_GET[ 'code' ];
            $date = str_replace( '/', '-', $date );
            $date = date( 'Y-m-d 00:00:00', strtotime( $date ) );
            $sql = "EXEC App_DcrReject '$code','$date'," . $vals[ 'reason' ] . "";
            performQuery( $sql );
            $msg = ",Your " . $date . " DCR Rejected for " . $vals[ 'reason' ] . "";
            notification( $code, $msg, 0 );
            $response[ "success" ] = true;
            echo json_encode( $response );
            break;
        case "DCRTPDevReason":
            $Reason = $vals[ 'reason' ];
            $TPWType = $vals[ 'wtype' ];
            $TPAreaCode = $vals[ 'clusterid' ];
            $TPArea = $vals[ 'ClstrName' ];
            $ADate = date( 'Y-m-d' );
            $status = $vals[ 'status' ];
            $sql = "exec svDCRTPDevReason '$sfCode','$TPWType','$TPAreaCode','$TPArea','$ADate','$Reason','$status'";
            performQuery( $sql );
            break;
        case "Survey_App":
            $data = json_decode( $_POST[ 'data' ], true );
            $sfCode = $_GET[ 'sfCode' ];
            $div = $_GET[ 'divisionCode' ];
            $Owndiv = str_replace( ",", "", $div );
            for ( $i = 0; $i < count( $vals ); $i++ ) {
                $sql = "exec sv_Survey '" . date( 'Y-m-d 00:00:00' ) . "','$Owndiv','" . $vals[ $i ][ 'Survey_Id' ] . "','" . $vals[ $i ][ 'Question_Id' ] . "','$sfCode','" . $vals[ $i ][ 'Doctor_code' ] . "','" . $vals[ $i ][ 'Chemist_code' ] . "','" . $vals[ $i ][ 'Trans_month' ] . "','" . $vals[ $i ][ 'Trans_year' ] . "','" . date( 'Y-m-d H:i:s' ) . "','" . $vals[ $i ][ 'Answer' ] . "'";
                performQuery( $sql );
            }
            break;
        case "Activity_Report_APP":
            $username = $vals[ 'username' ];
            $AppDeviceRegId = $vals[ 'app_device_id' ];
            $sql = "select * from mas_salesforce where UsrDfd_UserName='$username' and SF_Status=0";
            $tr = performQuery( $sql );
            if ( count( $tr ) == 0 && $AppDeviceRegId != null ) {
                $respon = array();
                $respon[ 'success' ] = false;
                $respon[ 'type' ] = 3;
                $respon[ 'msg' ] = "User Status Changed,. Kindly Login Again....";
                return outputJSON( $respon );
                die;
            }
            $sql = "select app_device_id from access_table where Sf_Code='$sfCode'";
            $arr = performQuery( $sql );
            if ( $arr[ 0 ][ 'app_device_id' ] == "" && $AppDeviceRegId != null ) {
                $sql = "update access_table set app_device_id='$AppDeviceRegId' where Sf_Code='$sfCode'";
                performQuery( $sql );
            } else if ( $arr[ 0 ][ 'app_device_id' ] != $AppDeviceRegId && $AppDeviceRegId != null ) {
                $sql = "select DeviceId_Need from Access_Master where division_code='$Owndiv'";
                $tr = performQuery( $sql );
                if ( $tr[ 0 ][ 'DeviceId_Need' ] == "2" ) {
                    $respon = array();
                    $respon[ 'success' ] = false;
                    $respon[ 'type' ] = 3;
                    $respon[ 'msg' ] = "Device Not Valid..";
                    return outputJSON( $respon );
                    die;
                }
            }
            if ( $vals[ "dcr_activity_date" ] != null && $vals[ "dcr_activity_date" ] != '' ) {
                $today = $vals[ "dcr_activity_date" ];
            }
            $vals[ "Worktype_code" ] = "'" . str_replace( "'", "", $vals[ "Worktype_code" ] ) . "'";
            $sql = "SELECT FWFlg, Confirmed FROM vwActivity_Report where SF_Code='" . $sfCode . "' and lower(Work_Type) <>lower(" . $vals[ "Worktype_code" ] . ")  and cast(activity_date as datetime)=cast('$today' as datetime)";
            $result1 = performQuery( $sql );

            $sql = "SELECT * FROM dcrmain_temp where SF_Code='" . $sfCode . "' and  cast(activity_date as datetime)=cast('$today' as datetime) and confirmed=2 and fieldwork_indicator='L'";
            $leavereg = performQuery( $sql );
            if ( count( $leavereg ) > 0 ) {
                $sql = "delete FROM dcrmain_temp where SF_Code='" . $sfCode . "' and  cast(activity_date as datetime)=cast('$today' as datetime) and confirmed=2 and fieldwork_indicator='L'";
                performQuery( $sql );
            }
            if ( count( $result1 ) > 0 ) {
                if ( !isset( $_GET[ 'replace' ] ) ) {
                    $result = array();
                    $result[ 'success' ] = false;
                    if ( $result1[ 0 ][ 'FWFlg' ] == 'L' && $result1[ 0 ][ 'Confirmed' ] != 2 && $result1[ 0 ][ 'Confirmed' ] != 3 ) {
                        $result[ 'type' ] = 2;
                        $result[ 'msg' ] = 'Leave Post Already Updated';
                    } else {
                        $result[ 'type' ] = 1;
                        $result[ 'msg' ] = 'Already There is a Data For other Work do you want to replace....?';
                    }
                    $result[ 'data' ] = $data;
                    outputJSON( $result );
                    die;
                } else {
                    delete_AR_entry( $sfCode, $vals[ "Worktype_code" ], $today );
                }
            }
            $pProd = '';
            $npProd = '';
            $pGCd = '';
            $pGNm = '';
            $pGQty = '';
            $SPProds = '';
            $nSPProds = '';
            $Inps = '';
            $nInps = '';
            $vTyp = 0;
            $VstFlag = 0;
            $Ydat = date( "Y" );
            $Mdat = date( 'm' );
            for ( $i = 1; $i < count( $data ); $i++ ) {
                $tableData = $data[ $i ];
                if ( isset( $tableData[ 'Activity_Doctor_Report' ] ) ) {
                    $vTyp = 1;
                    $DetTB = $tableData[ 'Activity_Doctor_Report' ];
                    $cCode = $DetTB[ "doctor_code" ];
                    $cName = $DetTB[ "doctor_name" ];
                    if ( $DetTB[ "Doc_Meet_Time" ] == "null" || $DetTB[ "Doc_Meet_Time" ] == null || $DetTB[ "Doc_Meet_Time" ] == '' ) {
                        $vTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $vTm = $DetTB[ "Doc_Meet_Time" ];
                    }
                    if ( $DetTB[ "modified_time" ] == "null" || $DetTB[ "modified_time" ] == null || $DetTB[ "modified_time" ] == '' ) {
                        $mTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $mTm = $DetTB[ "modified_time" ];
                    }
                    $pob = $DetTB[ "Doctor_POB" ];
                    $tvist = $DetTB[ "Tlvst" ];
                    $tvs = str_replace( "'", "", $tvist );

                    $nextVisitDate = $DetTB[ 'nextVisitDate' ];
                    if ( $nextVisitDate == "null" || $nextVisitDate == null || $nextVisitDate == '' )
                        $nextVisitDate = "''";
                    $hospitalcode = $DetTB[ 'hospital_code' ];

                    $hospitalname = $DetTB[ 'hospital_name' ];
                    if ( $hospitalcode == null || $hospitalcode == '' ) {
                        $hospitalcode = "''";
                        $hospitalname = "''";
                    }
                    $proc = "svDCRLstDet_App";
                    $check = 0;
                    if ( $check >= $tvs )$VstFlag = 1;
                    if ( $cName == '' || $cName == "''" ) {
                        $sql_name = "SELECT ListedDr_Name name from Mas_ListedDr where ListedDrCode='" . $cCode . "'";
                    }
                    if ( $cCode == '' || $cCode == "''" ) {
                        $name_sql = "SELECT ListedDrCode cd from Mas_ListedDr where ListedDr_Name='" . $cName . "'";
                        //echo $name_sql;
                        $drname = performQuery( $name_sql );
                        $cCode = $drname[ 0 ][ "cd" ];
                        $cName = $DetTB[ "doctor_name" ];
                    }

                }
                if ( isset( $tableData[ 'Activity_Chemist_Report' ] ) ) {
                    $vTyp = 2;
                    $DetTB = $tableData[ 'Activity_Chemist_Report' ];
                    $cCode = $DetTB[ "chemist_code" ];
                    $cName = $DetTB[ "chemist_name" ];
                    // $vTm = $DetTB["Chm_Meet_Time"];
                    if ( $DetTB[ "Chm_Meet_Time" ] == "null" || $DetTB[ "Chm_Meet_Time" ] == null || $DetTB[ "Chm_Meet_Time" ] == '' ) {
                        $vTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $vTm = $DetTB[ "Chm_Meet_Time" ];
                    }
                    if ( $DetTB[ "modified_time" ] == "null" || $DetTB[ "modified_time" ] == null || $DetTB[ "modified_time" ] == '' ) {
                        $mTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $mTm = $DetTB[ "modified_time" ];
                    }
                    //echo $vTm;
                    //$vTm = date('Y-m-d H:i:s');
                    $pob = $DetTB[ "Chemist_POB" ];
                    //$sql = "SELECT Chemists_Name name from vwChemists_Master_APP where Chemists_Code=" . $cCode;
                    if ( $cName == '' || $cName == "''" ) {
                        $sql_name = "SELECT Chemists_Name name from vwChemists_Master_APP where Chemists_Code='" . $cCode . "'";
                    }

                    if ( $cCode == '' || $cCode == "''" ) {
                        $name_sql = "SELECT Chemists_Code cd from Mas_Chemists where Chemists_Name='" . $cName . "'";
                        //echo $name_sql;
                        $chmName = performQuery( $name_sql );
                        $cCode = $chmName[ 0 ][ "cd" ];
                        $cName = $DetTB[ "chemist_name" ];
                    }
                }
                if ( isset( $tableData[ 'Activity_Stockist_Report' ] ) ) {
                    $vTyp = 3;
                    $DetTB = $tableData[ 'Activity_Stockist_Report' ];
                    $cCode = $DetTB[ "stockist_code" ];
                    // $vTm = $DetTB["Stk_Meet_Time"];
                    if ( $DetTB[ "Stk_Meet_Time" ] == "null" || $DetTB[ "Stk_Meet_Time" ] == null || $DetTB[ "Stk_Meet_Time" ] == '' ) {
                        $vTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $vTm = $DetTB[ "Stk_Meet_Time" ];
                    }
                    if ( $DetTB[ "modified_time" ] == "null" || $DetTB[ "modified_time" ] == null || $DetTB[ "modified_time" ] == '' ) {
                        $mTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $mTm = $DetTB[ "modified_time" ];
                    }
                    //$vTm = date('Y-m-d H:i:s');
                    $pob = $DetTB[ "Stockist_POB" ];
                    $sql_name = "SELECT stockiest_name name from vwstockiest_Master_APP where stockiest_code=" . $cCode;
                }
                if ( isset( $tableData[ 'Activity_UnListedDoctor_Report' ] ) ) {
                    $vTyp = 4;
                    $DetTB = $tableData[ 'Activity_UnListedDoctor_Report' ];
                    $cCode = $DetTB[ "uldoctor_code" ];
                    //$vTm = $DetTB["UnListed_Doc_Meet_Time"];
                    if ( $DetTB[ "UnListed_Doc_Meet_Time" ] == "null" || $DetTB[ "UnListed_Doc_Meet_Time" ] == null || $DetTB[ "UnListed_Doc_Meet_Time" ] == '' ) {
                        $vTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $vTm = $DetTB[ "UnListed_Doc_Meet_Time" ];
                    }
                    if ( $DetTB[ "modified_time" ] == "null" || $DetTB[ "modified_time" ] == null || $DetTB[ "modified_time" ] == '' ) {
                        $mTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $mTm = $DetTB[ "modified_time" ];
                    }
                    //$vTm = date('Y-m-d H:i:s');
                    $pob = $DetTB[ "UnListed_Doctor_POB" ];
                    $proc = "svDCRUnlstDet_App";
                    $sql_name = "SELECT unlisted_doctor_name name from vwunlisted_doctor_master_APP where unlisted_doctor_code=" . $cCode;
                }
                if ( isset( $tableData[ 'Activity_Hosp_Report' ] ) ) {
                    $vTyp = 5;
                    $DetTB = $tableData[ 'Activity_Hosp_Report' ];
                    $cCode = $DetTB[ "hospital_code" ];
                    // $vTm = $DetTB["Hosp_Meet_Time"];
                    if ( $DetTB[ "Hosp_Meet_Time" ] == "null" || $DetTB[ "Hosp_Meet_Time" ] == null || $DetTB[ "Hosp_Meet_Time" ] == '' ) {
                        $vTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $vTm = $DetTB[ "Hosp_Meet_Time" ];
                    }
                    if ( $DetTB[ "modified_time" ] == "null" || $DetTB[ "modified_time" ] == null || $DetTB[ "modified_time" ] == '' ) {
                        $mTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $mTm = $DetTB[ "modified_time" ];
                    }
                    $pob = $DetTB[ "Hosp_POB" ];
                    $sql_name = "SELECT hospital_name name from vwHosp_Master_APP where hospital_code=" . $cCode;
                }
                if ( isset( $tableData[ 'Activity_Cip_Report' ] ) ) {
                    $vTyp = 6;
                    $DetTB = $tableData[ 'Activity_Cip_Report' ];
                    $cCode = $DetTB[ "doctor_code" ];
                    // $vTm = $DetTB["Doc_Meet_Time"];
                    if ( $DetTB[ "Doc_Meet_Time" ] == "null" || $DetTB[ "Doc_Meet_Time" ] == null || $DetTB[ "Doc_Meet_Time" ] == '' ) {
                        $vTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $vTm = $DetTB[ "Doc_Meet_Time" ];
                    }
                    if ( $DetTB[ "modified_time" ] == "null" || $DetTB[ "modified_time" ] == null || $DetTB[ "modified_time" ] == '' ) {
                        $mTm = date( 'Y-m-d H:i:s' );
                    } else {
                        $mTm = $DetTB[ "modified_time" ];
                    }
                    $pob = $DetTB[ "Doctor_POB" ];
                    $tvist = $DetTB[ "Tlvst" ];
                    $tvs = str_replace( "'", "", $tvist );

                    $nextVisitDate = $DetTB[ 'nextVisitDate' ];
                    if ( $nextVisitDate == "null" || $nextVisitDate == null || $nextVisitDate == '' )
                        $nextVisitDate = "''";
                    $hospitalcode = $DetTB[ 'hospital_code' ];
                    $hospitalname = $DetTB[ 'hospital_name' ];
                    if ( $hospitalcode == null || $hospitalcode == '' ) {
                        $hospitalcode = "''";
                        $hospitalname = "''";
                    }
                    $proc = "svDCRCIPDet_App";
                    $sql_name = "SELECT name from vwCIP_APP where id=" . $cCode;
                }
                if ( isset( $tableData[ "Activity_Event_Captures" ] ) ) {
                    $Event_Captures = $tableData[ "Activity_Event_Captures" ];
                }
                if ( $sql_name != "" || $sql_name != null ) {
                    $tRw = performQuery( $sql_name );
                    $cName = $tRw[ 0 ][ "name" ];
                }
                if ( isset( $tableData[ 'Activity_Sample_Report' ] ) || isset( $tableData[ 'Activity_Unlistedsample_Report' ] ) ) {
                    if ( isset( $tableData[ 'Activity_Sample_Report' ] ) )
                        $samp = $tableData[ 'Activity_Sample_Report' ];
                    if ( isset( $tableData[ 'Activity_Unlistedsample_Report' ] ) )
                        $samp = $tableData[ 'Activity_Unlistedsample_Report' ];
                    for ( $j = 0; $j < count( $samp ); $j++ ) {
                        $feedback = $samp[ $j ][ "feedback" ];
                        if ( $feedback == null )
                            $feedback = "0";
                        $rcpa_qty = $samp[ $j ][ "Product_Rcpa_Qty" ];
                        if ( $rcpa_qty == null )
                            $rcpa_qty = "0";
                        $prodfeedback_id = $samp[ $j ][ "feedbk_id" ];
                        $prodfeedback_text = $samp[ $j ][ "feedbk" ];
                        if ( $samp[ $j ][ "Product_Rx_Qty" ] == "" )$samp[ $j ][ "Product_Rx_Qty" ] = "0";
                        $pProd = $pProd . ( ( $pProd != "" ) ? "#" : '' ) . $samp[ $j ][ "product_code" ] . "~" . $samp[ $j ][ "Product_Sample_Qty" ] . "$" . $samp[ $j ][ "Product_Rx_Qty" ] . "$" . $feedback . "^" . $rcpa_qty . "$" . $prodfeedback_id;
                        $npProd = $npProd . ( ( $npProd != "" ) ? "#" : '' ) . $samp[ $j ][ "product_Name" ] . "~" . $samp[ $j ][ "Product_Sample_Qty" ] . "$" . $samp[ $j ][ "Product_Rx_Qty" ] . "$" . $feedback . "^" . $rcpa_qty . "$" . $prodfeedback_text;
                    }
                }
                if ( isset( $tableData[ 'Activity_POB_Report' ] ) || isset( $tableData[ 'Activity_Stk_POB_Report' ] ) ) {

                    if ( isset( $tableData[ 'Activity_POB_Report' ] ) )
                        $samp = $tableData[ 'Activity_POB_Report' ];
                    if ( isset( $tableData[ 'Activity_Stk_POB_Report' ] ) )
                        $samp = $tableData[ 'Activity_Stk_POB_Report' ];
                    for ( $j = 0; $j < count( $samp ); $j++ ) {
                        $SPProds = $SPProds . $samp[ $j ][ "product_code" ] . "~" . $samp[ $j ][ "Qty" ] . "#";
                        $nSPProds = $nSPProds . $samp[ $j ][ "product_Name" ] . "~" . $samp[ $j ][ "Qty" ] . "#";
                    }
                }
                if ( isset( $tableData[ 'Activity_Input_Report' ] ) || isset( $tableData[ 'Activity_Chm_Sample_Report' ] ) || isset( $tableData[ 'Activity_Stk_Sample_Report' ] ) || isset( $tableData[ 'activity_unlistedGift_Report' ] ) ) {
                    if ( isset( $tableData[ 'Activity_Input_Report' ] ) )
                        $inp = $tableData[ 'Activity_Input_Report' ];
                    if ( isset( $tableData[ 'Activity_Chm_Sample_Report' ] ) )
                        $inp = $tableData[ 'Activity_Chm_Sample_Report' ];
                    if ( isset( $tableData[ 'Activity_Stk_Sample_Report' ] ) )
                        $inp = $tableData[ 'Activity_Stk_Sample_Report' ];
                    if ( isset( $tableData[ 'activity_unlistedGift_Report' ] ) )
                        $inp = $tableData[ 'activity_unlistedGift_Report' ];
                    for ( $j = 0; $j < count( $inp ); $j++ ) {
                        if ( $j == 0 && ( $vTyp == 1 || $vTyp == 4 ) ) {
                            $pGCd = $inp[ $j ][ "Gift_Code" ];
                            $pGNm = $inp[ $j ][ "Gift_Name" ];
                            $pGQty = $inp[ $j ][ "Gift_Qty" ];
                        } else {
                            $Inps = $Inps . $inp[ $j ][ "Gift_Code" ] . "~" . $inp[ $j ][ "Gift_Qty" ] . "#";
                            $nInps = $nInps . $inp[ $j ][ "Gift_Name" ] . "~" . $inp[ $j ][ "Gift_Qty" ] . "#";
                        }
                    }
                }
            }
            $ARCd = "";
            $ARDCd = ( strlen( $_GET[ 'amc' ] ) == 0 ) ? "0" : $_GET[ 'amc' ];
            $sql = "SELECT trans_slno FROM vwActivity_Report where SF_Code='" . $sfCode . "' and cast(activity_date as date)=cast('$today' as date)";
            $trans = performQuery( $sql );
            if ( count( $trans ) == 0 ) {
                $sql = "{call  svDCRMain_App(?,?," . $vals[ "Worktype_code" ] . ",'" . str_replace( "'", "", $vals[ "Town_code" ] ) . "',?,'" . str_replace( "'", "", $vals[ "Daywise_Remarks" ] ) . "',?)}";
                $params = array( array( $sfCode, SQLSRV_PARAM_IN ),
                    array( $today, SQLSRV_PARAM_IN ),
                    array( $Owndiv, SQLSRV_PARAM_IN ),
                    array( & $ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_VARCHAR( 50 ) ) );
                performQueryWP( $sql, $params );
            } else
                $ARCd = $trans[ 0 ][ 'trans_slno' ];
            $loc = explode( ":", str_replace( "'", "", $DetTB[ "location" ] ) . ":" );
            $lat = $loc[ 0 ]; //latitude
            $lng = $loc[ 1 ]; //longitude
            $DetTB[ "geoaddress" ] = "NA";
            $apps = "'261'";
            $vst = "0";
            $sqlsp = "{call  ";
            if ( $vTyp != 0 ) {
                if ( $vTyp == 2 || $vTyp == 3 || $vTyp == 5 )$proc = "svDCRCSHDet_App";
                if ( $pob == '' )
                    $pob = '0';
                $sqlsp = $sqlsp . $proc . " (?,?,?," . $vTyp . "," . $cCode . ",'" . $cName . "','" . str_replace( "'", "", $vTm ) . "'," . $pob . ",'" . str_replace( "'", "", $DetTB[ "Worked_With" ] ) . "',?,?,?,?,";
                if ( $vTyp == 1 || $vTyp == 4 || $vTyp == 6 )
                    $sqlsp = $sqlsp . "?,?,?,?,?,";
                if ( $vTyp == 1 || $vTyp == 6 ) {
                    $sqlsp = $sqlsp . "'" . str_replace( "'", "", $vals[ "Town_code" ] ) . "','" . str_replace( "'", "", $vals[ "Daywise_Remarks" ] ) . "',?,'" . str_replace( "'", "", $vals[ "rx_t" ] ) . "','" . $mTm . "',?,?,'" . str_replace( "'", "", $vals[ "DataSF" ] ) . "','" . $DetTB[ "geoaddress" ] . "'," . $apps . "," . $vst . ",'" . str_replace( "'", "", $nextVisitDate ) . "')}";
                } else {
                    $sqlsp = $sqlsp . "'" . str_replace( "'", "", $vals[ "Town_code" ] ) . "','" . str_replace( "'", "", $vals[ "Daywise_Remarks" ] ) . "',?,'" . str_replace( "'", "", $vals[ "rx_t" ] ) . "','" . $mTm . "',?,?,'" . str_replace( "'", "", $vals[ "DataSF" ] ) . "','" . $DetTB[ "geoaddress" ] . "')}";
                    $params = array( array( $ARCd, SQLSRV_PARAM_IN ),
                        array( & $ARDCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_NVARCHAR( 50 ) ), array( $sfCode, SQLSRV_PARAM_IN ) );
                }
                if ( $vTyp == 1 || $vTyp == 4 || $vTyp == 6 ) {
                    array_push( $params, array( $pProd, SQLSRV_PARAM_IN ) );
                    array_push( $params, array( $npProd, SQLSRV_PARAM_IN ) );
                }
                array_push( $params, array( $SPProds, SQLSRV_PARAM_IN ) );
                array_push( $params, array( $nSPProds, SQLSRV_PARAM_IN ) );
                if ( $vTyp == 1 || $vTyp == 4 || $vTyp == 6 ) {
                    array_push( $params, array( $pGCd, SQLSRV_PARAM_IN ) );
                    array_push( $params, array( $pGNm, SQLSRV_PARAM_IN ) );
                    array_push( $params, array( $pGQty, SQLSRV_PARAM_IN ) );
                }
                array_push( $params, array( $Inps, SQLSRV_PARAM_IN ) );
                array_push( $params, array( $nInps, SQLSRV_PARAM_IN ) );
                array_push( $params, array( $Owndiv, SQLSRV_PARAM_IN ) );
                array_push( $params, array( $loc[ 0 ], SQLSRV_PARAM_IN ) );
                array_push( $params, array( $loc[ 1 ], SQLSRV_PARAM_IN ) );
                performQueryWP( $sqlsp, $params );
                if ( sqlsrv_errors() != null ) {
                    print_r( $params );
                    outputJSON( sqlsrv_errors() );
                    die;
                }
                for ( $j = 0; $j < count( $Event_Captures ); $j++ ) {
                    $ev_imgurl = $sfCode . "_" . $Event_Captures[ $j ][ "imgurl" ];
                    $ev_title = $Event_Captures[ $j ][ "title" ];
                    $ev_remarks = $Event_Captures[ $j ][ "remarks" ];
                    $sql = "insert into DCREvent_Captures(Trans_slno,Trans_detail_slno,imgurl,title,remarks,Division_Code,sf_code) select '" . $ARCd . "','" . $ARDCd . "','" . $ev_imgurl . "','" . $ev_title . "','" . $ev_remarks . "','" . $Owndiv . "','$sfCode'";
                    performQuery( $sql );
                }
                SaveRCPAEntry( $ARCd, $ARDCd, $DetTB, $today );
                if ( sqlsrv_errors() != null ) {
                    outputJSON( $params . "<br>" );
                    outputJSON( sqlsrv_errors() );
                    die;
                }
                if ( $ARDCd == "Exists" ) {
                    $resp[ "msg" ] = "Call Already Exists";
                    $resp[ "success" ] = false;
                    echo json_encode( $resp );
                    die;
                }
                if ( isset( $tableData[ 'Dynamic_Activity_App' ] ) ) {
                    for ( $d = 0; $d < count( $tableData[ 'Dynamic_Activity_App' ] ); $d++ ) {
                        $Dact = $tableData[ 'Dynamic_Activity_App' ][ $d ][ 'val' ];
                        $resp[ "ErQry" ] = "Dynamic_Activity_App";
                        SaveDCRActivity( $Dact );
                    }
                }
            }
            break;
    }
    $resp[ "success" ] = true;
    echo json_encode( $resp );
}

function SaveRCPAEntry( $ARCd, $ARDCd, $mData, $RCPADt ) {
    global $data;
    $sfCode = $_GET[ 'sfCode' ];
    $sfName = '';
    $CustCode = $mData[ 'doctor_code' ];
    $CustName = '';
    $div = $_GET[ 'divisionCode' ];
    $divs = explode( ",", $div . "," );
    $Owndiv = ( string )$divs[ 0 ];
    $RCPADatas = $mData[ 'RCPAEntry' ];
    $EID = 0;
    for ( $Ri = 0; $Ri < count( $RCPADatas ); $Ri++ ) {
        $RCPAData = $RCPADatas[ $Ri ];
        if ( $CustCode == "" || $CustCode == null ) {
            $CustCode = $RCPAData[ "doc_id" ];
        }
        $ChmIds = $RCPAData[ "chemist_id" ];
        $ChmNms = $RCPAData[ "chemist_name" ];
        $VstTime = "";
        $JWWrk = "";
        $lat = "";
        $lng = "";
        $DataSF = "";
        if ( $ARDCd != "" ) {
            $query = "select Trans_Detail_Slno,convert(varchar,time,20) tmv,Worked_with_Code,lati,long,DataSF,Division_code from vwActivity_MSL_Details where Trans_Detail_Slno='" . $ARDCd . "'";
            $arr = performQuery( $query );

            if ( count( $arr[ 0 ] ) > 0 ) {
                $VstTime = $arr[ 0 ][ "tmv" ];
                $JWWrk = $arr[ 0 ][ "Worked_with_Code" ];
                $lat = $arr[ 0 ][ "lati" ];
                $lng = $arr[ 0 ][ "long" ];
                $DataSF = $arr[ 0 ][ "DataSF" ];
            }
        }

        $query = "exec svDCRCSHDet_App '" . $ARCd . "',0,'" . $sfCode . "','2','" . $ChmIds . "','" . $ChmNms . "','" . $VstTime . "',0,'" . $JWWrk . "','','','','','','','" . $Owndiv . "',0,'" . $VstTime . "','" . $lat . "','" . $lng . "','" . $DataSF . "','NA','App'";
        $params = array( array( $ARDCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING( SQLSRV_ENC_CHAR ), SQLSRV_SQLTYPE_VARCHAR( 50 ) ) );
        performQuery( $query );
        $sXML = "<ROOT>";
        $Comps = $RCPAData[ "compats" ];
        for ( $Rj = 0; $Rj < count( $Comps ); $Rj++ ) {
            $Comp = $Comps[ $Rj ];
            $sXML = $sXML . "<Comp CCode=\"" . $Comp[ "comptid" ] . "\" CName=\"" . $Comp[ "comptname" ] . "\" CPCode=\"" . $Comp[ "comptpbid" ] . "\" CPName=\"" . $Comp[ "comptpname" ] . "\" CPQty=\"" . $Comp[ "comptbqty" ] . "\" CPRate=\"" . $Comp[ "comptbprice" ] . "\" CPValue=\"" . $Comp[ "comptbamount" ] . "\" CPUnit=\"" . $Comp[ "compunit" ] . "\" />";
        }
        $sXML = $sXML . "</ROOT>";
        $query = "exec iOS_svRCPAEntry '" . $sfCode . "','" . $sfName . "','" . $RCPADt . "'," . $CustCode . ",'" . $CustName . "','" . $ChmIds . "','" . $ChmNms . "','" . $RCPAData[ "obid" ] . "','" . $RCPAData[ "obname" ] . "','" . $RCPAData[ "obqty" ] . "','" . $RCPAData[ "obprice" ] . "','" . $RCPAData[ "tamount" ] . "','" . $RCPAData[ "obunity" ] . "','" . $ARCd . "','" . $ARDCd . "','" . $EID . "','" . $sXML . "'";

        performQueryWP( $query, [] );
    }
    return $result;
}

function SaveDCRActivity( $Dact ) {
    $response = array();
    if ( $Dact == undefined || $Dact == null || $Dact == '' ) {
        $data = json_decode( $_POST[ 'data' ], true );
        $val = $data[ 'val' ];
    } else {
        $val = $Dact;
    }

    $sql = "select isnull(Max(cast(Max_slno as int)),0)+1 transslno from Activity_Group_SlNo with (INDEX(Idx_Activity_Group_SlNo))";
    $tr = performQuery( $sql );
    $gid = $tr[ 0 ][ 'transslno' ];

    $sql = "update Activity_Group_SlNo set Max_slno ='$gid'";
    performQuery( $sql );
    $g_status = 0;

    for ( $i = 0; $i < count( $val ); $i++ ) {
        $det_no = "0";
        $main_no = "0";
        $type_val = "0";
        $cust_code = "0";
        $value = $val[ $i ];
        $sf = $value[ "SF" ];
        $div = $value[ "div" ];
        $act_date = $value[ "act_date" ];
        $update_time = $value[ "update_time" ];
        $slno = $value[ "slno" ];
        $ctrl_id = $value[ "ctrl_id" ];
        $create_id = $value[ "creat_id" ];
        $va = $value[ "values" ];
        $codes = $value[ "codes" ];
        $type_val = $value[ "type" ];
        $dt = $value[ "dcr_date" ];

        if ( $type_val != "0" ) {
            if ( $type_val == '1' || $type_val == '2' || $type_val == '3' || $type_val == '4' || $type_val == '' ) {
                $query = "exec svDCRMain_App '" . $sf . "','" . $dt . "','" . $value[ 'WT' ] . "','" . $value[ 'Pl' ] . "','" . $div . "','','','Apps'";
                $response[ "MQry" ] = $query;
                performQuery( $query );
                $query = "select Trans_SlNo from vwActivity_Report where Sf_Code='" . $sf . "' and  cast(Activity_Date as datetime)=cast('" . $dt . "' as datetime)";
                $arr = performQuery( $query );
                $response[ "SlQry" ] = $query;
                $response[ "valQry" ] = $arr[ 0 ][ "Trans_SlNo" ];
                $det_no = $arr[ 0 ][ "Trans_SlNo" ];
                $cust_code = $value[ "cus_code" ];
            }

            if ( $type_val == '1' ) {
                $query = "exec svDCRLstDet_App '" . $det_no . "',0,'" . $sf . "',1,'" . $cust_code . "','" . $value[ 'cusname' ] . "','" . $dt . "',0,'','','','','','','','','','','','','" . $div . "',0,'" . $dt . "','" . $value[ 'lat' ] . "','" . $value[ 'lng' ] . "','" . $value[ 'DataSF' ] . "','NA','Apps'";
                performQuery( $query );
                $query = "select Trans_Detail_Slno from vwActivity_MSL_Details where Trans_SlNo='" . $det_no . "' and Trans_Detail_Info_Code='" . $cust_code . "'";
                $arr = performQuery( $query );
                $main_no = $arr[ 0 ][ "Trans_Detail_Slno" ];
            }

            if ( $type_val == '2' || $type_val == '3' ) {
                $query = "exec svDCRCSHDet_App '" . $det_no . "',0,'" . $sf . "','" . $type_val . "','" . $cust_code . "','" . $value[ 'cusname' ] . "','" . $dt . "',0,'','','','','','','','" . $div . "',0,'" . $dt . "','" . $value[ 'lat' ] . "','" . $value[ 'lng' ] . "','" . $value[ 'DataSF' ] . "','NA','Apps'";
                $result[ "CQry" ] = $query;
                performQuery( $query );
                $query = "select Trans_Detail_Slno from vwActivity_CSH_Detail where Trans_SlNo='" . $det_no . "' and Trans_Detail_Info_Code='" . $cust_code . "'";
                $arr = performQuery( $query );
                $main_no = $arr[ 0 ][ "Trans_Detail_Slno" ];
            }

            if ( $type_val == '4' ) {
                $query = "exec svDCRUnlstDet_App '" . $det_no . "',0,'" . $sf . "','" . $type_val . "','" . $cust_code . "','" . $value[ 'cusname' ] . "','" . $dt . "',0,'','','','','','','','','','','','','" . $div . "',0,'" . $dt . "','" . $value[ 'lat' ] . "','" . $value[ 'lng' ] . "','" . $value[ 'DataSF' ] . "','NA','Apps'";
                $result[ "NQry" ] = $query;
                performQuery( $query );
                $query = "select Trans_Detail_Slno from vwActivity_Unlst_Detail where Trans_SlNo='" . $det_no . "' and Trans_Detail_Info_Code='" . $cust_code . "'";
                $arr = performQuery( $query );
                $main_no = $arr[ 0 ][ "Trans_Detail_Slno" ];
            }
        }
        $sql = "EXEC svDcrActivity '$sf','$div','$act_date','$update_time','$slno','$ctrl_id','$create_id','$va','$codes','$det_no','$main_no','$type_val','$cust_code','$gid',$g_status";
        $arr = performQuery( $sql );
        $response[ "finalQry" ] = $arr;

    }
    $response[ 'success' ] = true;
    return $response;
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

    $sql = "update DCRMain_Temp set Remarks='$Remarks',Half_Day_FW='$HalfDy' where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$today' as datetime)";
    $result = performQuery( $sql );

    $sql = "update DCRMain_Trans set Remarks='$Remarks',Half_Day_FW='$HalfDy' where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$today' as datetime)";
    $result = performQuery( $sql );

    $response[ "success" ] = true;
    echo json_encode( $response );
}

function delete_entry() {
    $data = json_decode( $_POST[ 'data' ], true );
    $arc = ( isset( $_GET[ 'arc' ] ) && strlen( $_GET[ 'arc' ] ) == 0 ) ? null : $_GET[ 'arc' ];
    $amc = ( isset( $_GET[ 'amc' ] ) && strlen( $_GET[ 'amc' ] ) == 0 ) ? null : $_GET[ 'amc' ];
    if ( !is_null( $amc ) ) {
        $query = "DELETE FROM DCRDetail_Lst_Temp where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM DCRDetail_Lst_Trans where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM DCRDetail_CSH_Temp where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM DCRDetail_CSH_Trans where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM DCRDetail_Unlst_Temp where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM DCRDetail_Unlst_Trans where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM Trans_LdrNxtVst_Det where trans_slno='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM Trans_RCPA_Head where AR_Code='" . $arc . "' and ARMSL_Code='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM Trans_RCPA_Detail where DCR_id='" . $arc . "' and Dcrdetail_id='" . $amc . "'";
        performQuery( $query );

        $query = "DELETE FROM DCR_Detail_Activity where Trans_Detail_Slno='" . $amc . "'";
        performQuery( $query );
    }
}

function delete_AR_entry( $SF, $WT, $Dt ) {
    $query_main = "SELECT Trans_SlNo FROM vwActivity_Report where SF_Code='" . $SF . "' and lower(Work_Type) <> lower(" . $WT . ") and cast(activity_date as datetime)=cast('$Dt' as datetime)";

    $sql = "DELETE FROM DCRDetail_Lst_Temp where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCRDetail_Lst_Trans where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCRDetail_CSH_Temp where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCRDetail_CSH_Trans where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCRDetail_Unlst_Temp where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCRDetail_Unlst_Trans where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCREvent_Captures where Trans_SlNo in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCR_Detail_Activity where Trans_Main_Sl_No in (" . $query_main . ")";
    performQuery( $sql );

    $sql = "DELETE FROM DCRMain_Temp where SF_Code='" . $SF . "' and lower(Work_Type) <> lower(" . $WT . ") and cast(activity_date as datetime)=cast('$Dt' as datetime)";
    performQuery( $sql );

    $sql = "DELETE FROM DCRMain_Trans where SF_Code='" . $SF . "' and lower(Work_Type) <> lower(" . $WT . ") and cast(activity_date as datetime)=cast('$Dt' as datetime)";
    performQuery( $sql );
}

function Entry_Count() {
    $sfCode = $_GET[ 'sfCode' ];
    $eDate = $_GET[ 'eDate' ];
    if ( $eDate == '' || $eDate == null ) {
        $today = date( 'Y-m-d 00:00:00' );
    } else {
        $today = date( "Y-m-d 00:00:00", strtotime( $eDate ) );
    }
    $results = array();

    $query = "select Count(Trans_Detail_Info_Code) doctor_count from vwActivity_MSL_Details D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) chemist_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=2";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) stockist_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=3";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) uldoctor_count from vwActivity_Unlst_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=4";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select isnull((SELECT top 1 isnull(remarks,'') from vwActivity_Report where sf_code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)),'') as remarks";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select isnull((SELECT top 1 Half_Day_FW from vwActivity_Report where sf_code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)),'') as halfdaywrk";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) hospital_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=5";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) cip_count from vwActivity_CIP_Details D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=6";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];
    return $results;
}
?>