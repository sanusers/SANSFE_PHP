<?php

function MasterSync() {
    $data = $GLOBALS[ 'Data' ];
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
    switch ( strtolower( $data[ 'tableName' ] ) ) {
        case "mas_worktype":
            $sql = "EXEC GetWorkTypes_App '" . $SF_Code . "'";
            outputJSON( performQuery( $sql ) );
            break;
        case "product_master":
            $sql = "EXEC getAppProd '" . $SF_Code . "'";
            outputJSON( performQuery( $sql ) );
            break;
        case "vwmydayplan":
            $sql = "EXEC getTodayTP_native_App '" . $RSF_Code . "', '" . $_GET[ 'cdate' ] . "'";
            //$sql = "EXEC SPR_TodayTP_APP '" . $RSF_Code . "', '" . $_GET[ 'cdate' ] . "'";
            outputJSON( performQuery( $sql ) );
            break;
        case "category_master":
            $sql = "EXEC GetProdBrand_App '" . $DivisionCode . "'";
            outputJSON( performQuery( $sql ) );
            break;
        case "gift_master":
            $sql = "EXEC getAppGift '" . $RSF_Code . "'";
            outputJSON( performQuery( $sql ) );
            break;
        case "vwleavetype":
            include 'leave.php';
            LeaveType( $DivisionCode );
            break;
        case "mas_superstockist":
            $query = "SELECT id, name, Division_Code FROM vwSuper_stockist_App WHERE Division_code='" . $DivisionCode . "'";
            outputJSON( performQuery( $query ) );
            break;
        case "quiz":
            include 'quiz.php';
            Quiz( $SF_Code, $DivisionCode );
            break;
        case "rcpadetail_new":
            $Rcpa = [];
            $query = "SELECT DrCode, DrName, ChmCode, ChmName, OPCode, OPName, OPQty, OPRate FROM Trans_RCPA_Head WHERE sf_code='" . $SF_Code . "' AND AR_Code='" . $_GET[ 'arc' ] . "' AND ARMSL_Code='" . $_GET[ 'arc_dt' ] . "'";
            $Rcpa_det = performQuery( $query );
            for ( $i = 0; $i < count( $Rcpa_det ); $i++ ) {
                $Rcpa[ $i ] = $Rcpa_det[ $i ];
                $Rcpa_id = $Rcpa_det[ $i ][ 'PK_ID' ];
                $query = "SELECT CompCode, CompName, CompPCode, CompPName, CPQty, CPRate FROM Trans_RCPA_Detail detail WHERE FK_PK_ID='" . $Rcpa_id . "'";
                $Rcpa[ $i ][ 'RcpaComp' ] = performQuery( $query );
            }
            outputJSON( $Rcpa );
            break;
        case "rcpadetail_report":
            $query = "SELECT H.DrName,H.ChmName,H.OPName,H.OPQty,H.OPUnit,D.CompName,D.CompPName,D.CPQty,D.CPUnit FROM Trans_RCPA_Head H INNER JOIN Trans_RCPA_Detail D ON H.pk_id=D.fk_pk_id WHERE sf_code='" . $SF_Code . "' and ARMSL_Code='" . $_GET[ 'arc_dt' ] . "'";
            outputJSON( performQuery( $query ) );
            break;
        case "vwedit_activity":
            include 'edit_activity.php';
            activity();
            break;
        case "event_captures_report":
            $query = "select sf_code,('photos/'+imgurl)Eventimg, title, remarks from DCREvent_Captures where sf_code='$SF_Code' and Trans_SlNo='" . $_GET[ 'arc' ] . "' and Trans_Detail_Slno='" . $_GET[ 'arc_dt' ] . "'";
            outputJSON( performQuery( $query ) );
            break;
        case "vwdcr_misseddates":
            $sql = "EXEC Get_MissedDates_App '" . $SF_Code . "'";
            outputJSON( performQuery( $sql ) );
            break;
        case "doctor_category":
            $query = "select Doc_Cat_Code id,Doc_Cat_Name name from Mas_Doctor_Category where Division_code='" . $DivisionCode . "' and Doc_Cat_Active_Flag=0";
            outputJSON( performQuery( $query ) );
            break;
        case "doctor_specialty":
            $query = "select Doc_Special_Code id,Doc_Special_Name name from Mas_Doctor_Speciality where Division_code='" . $DivisionCode . "' and Doc_Special_Active_Flag=0";
            outputJSON( performQuery( $query ) );
            break;
        case "mas_doc_class":
            $query = "select Doc_ClsCode id,Doc_ClsSName name from Mas_Doc_Class where Division_code='" . $DivisionCode . "' and Doc_Cls_ActiveFlag=0";
            outputJSON( performQuery( $query ) );
            break;
        case "mas_doc_qualification":
            $query = "select Doc_QuaCode id,Doc_QuaName name from Mas_Doc_Qualification where Division_code='" . $DivisionCode . "' and Doc_Qua_ActiveFlag=0";
            outputJSON( performQuery( $query ) );
            break;
        case "prod_feedbk":
            $query = "select FeedBack_Id id,FeedBack_Name name from Mas_Product_Feedback where Division_code='" . $DivisionCode . "' and Active_flag=0";
            outputJSON( performQuery( $query ) );
            break;
        case "vwfolders":
            $result = array();
            $sql = "select Move_MailFolder_Id id, Move_MailFolder_Name name from Mas_Mail_Folder_Name where division_code='$DivisionCode'";
            $result = performQuery( $sql );
            array_unshift( $result,
                array( "id" => "inbox", "name" => "Inbox" ),
                array( "id" => "sent", "name" => "Sent Item" ),
                array( "id" => "view", "name" => "Viewed" ) );
            outputJSON( $result );
            break;
        case "map_competitor_product":
            $query = "select Comp_Sl_No as id,Comp_Name as name,Comp_Prd_Sl_No as pid,Comp_Prd_name as pname from Map_Competitor_Product where Division_code='" . $DivisionCode . "' and Active_Flag=0";
            outputJSON( performQuery( $query ) );
            break;
        case "vwhosp_master_app":
            $query = "select Leave_Code id,Leave_SName name,Leave_Name from vwLeaveType where Division_code='" . $DivisionCode . "'";
            outputJSON( performQuery( $query ) );
            break;
        case "getmailsf":
            include 'mail.php';
            GetMailSF( $SF_Code );
            break;
        default:
            $today = ( isset( $data[ 'today' ] ) && $data[ 'today' ] == 0 ) ? null : $data[ 'today' ];
            $or = ( isset( $data[ 'or' ] ) && $data[ 'or' ] == 0 ) ? null : $data[ 'or' ];
            $wt = ( isset( $data[ 'wt' ] ) && $data[ 'wt' ] == 0 ) ? null : $data[ 'wt' ];
            $coloumns = json_decode( $data[ 'coloumns' ] );
            $where = isset( $data[ 'where' ] ) ? json_decode( $data[ 'where' ] ) : null;
            $join = isset( $data[ 'join' ] ) ? $data[ 'join' ] : null;
            $orderBy = isset( $data[ 'orderBy' ] ) ? json_decode( $data[ 'orderBy' ] ) : null;
            if ( !is_null( $or ) ) {
                $results = getFromTableWR( $GLOBALS[ 'TableName' ], $coloumns, $DivisionCode, $SF_Code, $orderBy, $where, $join, $today, $wt );
                outputJSON( $results );
            } else {
                $results = getFromTable( $GLOBALS[ 'TableName' ], $coloumns, $DivisionCode, $SF_Code, $orderBy, $where, $join, $today, $wt );
                outputJSON( $results );
            }
            break;
    }
}

function getFromTableWR( $tableName, $coloumns, $divisionCode, $sfCode = null, $orderBy = null, $where = null, $join = null, $today = null, $wt = null ) {
    $query = "SELECT " . join( ",", $coloumns ) . " FROM $tableName as tab";
    if ( !is_null( $join ) ) {
        $query .= " join " . join( " join ", $join );
    }
    $query .= " WHERE tab.Division_Code=" . $divisionCode;
    if ( !is_null( $where ) ) {
        $query .= " and " . join( " or ", $where );
    }
    if ( !is_null( $today ) ) {
        $today = date( 'Y-m-d 00:00:00' );
        $query .= "and cast(tab.activity_date as datetime)=cast('$today' as datetime)";
    }
    if ( !is_null( $orderBy ) ) {
        $query .= " ORDER BY " . join( ", ", $orderBy );
    }
    return performQuery( $query );
}

function getFromTable( $tableName, $coloumns, $divisionCode, $sfCode = null, $orderBy = null, $where = null, $join = null, $today, $wt = null ) {
    $query = "SELECT " . join( ",", $coloumns ) . " FROM $tableName as tab";
    if ( !is_null( $join ) ) {
        $query .= " join " . join( " join ", $join );
    }
    if ( !is_null( $sfCode ) ) {
        $query .= " WHERE tab.SF_Code='$sfCode'";
    } else {
        $query .= " WHERE tab.Division_Code=" . $divisionCode;
    }
    if ( !is_null( $where ) ) {
        $query .= " and " . join( " and ", $where );
    }
    if ( !is_null( $today ) ) {
        $query .= " and cast(tab.activity_date as datetime)=cast('$today' as datetime)";
    }
    if ( !is_null( $orderBy ) ) {
        $query .= " ORDER BY " . join( ",", $orderBy );
    }
    return performQuery( $query );
}
?>