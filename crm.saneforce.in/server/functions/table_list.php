<?php

function mastersync() {
  $data = json_decode( $_POST[ 'data' ], true );
  switch ( strtolower( $data[ 'tableName' ] ) ) {
    case "mas_worktype":
      $RSF = $data[ 'sfCode' ];
      $sql = "EXEC GetWorkTypes_App '" . $RSF . "'";
      outputJSON( performQuery( $sql ) );
      break;
    case "product_master":
      $SFCode = $_GET[ 'sfCode' ];
      $sql = "EXEC getAppProd '" . $SFCode . "'";
      outputJSON( performQuery( $sql ) );
      break;
    case "mas_superstockist":
      $Div = explode( ",", $data[ 'divisionCode' ] . "," );
      $OwnDiv = ( string )$Div[ 0 ];
      $query = "SELECT id, name, Division_Code FROM vwSuper_stockist_App WHERE Division_code='" . $OwnDiv . "'";
      outputJSON( performQuery( $query ) );
      break;
    case "quiz":
      $Div = explode( ",", $data[ 'divisionCode' ] . "," );
      $OwnDiv = ( string )$Div[ 0 ];
      $query = "SELECT survey_id, quiz_title, SUBSTRING(filepath, CHARINDEX(')', filepath)+1, LEN(filepath)) [FileName] FROM QuizTitleCreation WHERE division_code='" . $OwnDiv . "' AND active=0 AND MONTH(effective_date)=MONTH(GETDATE())AND YEAR(effective_date)=YEAR(GETDATE())AND CAST(effective_date AS DATE)<=CAST(GETDATE() AS DATE) ORDER BY survey_id DESC";
      $quiztitle = performQuery( $query );
      $quiztitle1 = array();
      $processUser1 = array();
      for ( $i = 0; $i < count( $quiztitle ); $i++ ) {
        $surveyid = $quiztitle[ $i ][ 'survey_id' ];
        $query = "SELECT NoOfAttempts [type], [Type] NoOfAttempts, timelimit FROM Processing_UserList WHERE surveyid='" . $surveyid . "' AND sf_code='" . $sfCode . "' AND process_status='P' AND CAST(from_date AS DATE)<=CAST(GETDATE() AS DATE)AND CAST(to_date AS DATE)>=CAST(GETDATE() AS DATE)";
        $processUser = performQuery( $query );
        if ( count( $processUser ) > 0 ) {
          $processUser1 = $processUser;
          $quiztitle1 = $quiztitle[ $i ];
          $quiztitle = array();
        }
      }
      $processUser = array();
      $processUser = $processUser1;
      $quiztitle = array();
      $quiztitle[ 0 ] = $quiztitle1;
      $surveyid = $quiztitle[ 0 ][ 'survey_id' ];
      if ( $quiztitle[ 0 ][ 'FileName' ] != "" ) {
        if ( $extn == "png" || $extn == "jpg" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "image/png";
        } else if ( $extn == "doc" || $extn == "dot" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "application/msword";
        } else if ( $extn == "docx" || $extn == "DOCX" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "application/msword";
        } else if ( $extn == "xls" || $extn == "xlt" || $extn == "xla" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "application/vnd.ms-excel";
        } else if ( $extn == "xlsx" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        } else if ( $extn == "mp4" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "video/mp4";
        } else if ( $extn == "pptx" ) {
          $quiztitle[ 0 ][ 'mimetype' ] = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        } else {
          $quiztitle[ 0 ][ 'mimetype' ] = "application/" + $extn;
        }
        $extn = end( explode( '.', $quiztitle[ 0 ][ 'FileName' ] ) );
      }
      $query = "SELECT Question_Type_id, Question_Id, Question_Text, surveyid FROM AddQuestions WHERE surveyid='" . $surveyid . "' ORDER BY question_id ASC";
      $questions = performQuery( $query );
      $query = "SELECT input_id, Question_Id, Input_Text, Correct_Ans FROM AddInputOptions WHERE question_id IN(SELECT question_id FROM AddQuestions WHERE surveyid='" . $surveyid . "') ORDER BY question_id ASC";
      $answers = performQuery( $query );
      $results = array();
      $results[ 'quiztitle' ][ 0 ] = $quiztitle[ 0 ];
      $results[ 'processUser' ] = $processUser;
      $results[ 'questions' ] = $questions;
      $results[ 'answers' ] = $answers;
      if ( count( $processUser ) == 0 )
        $results = array();
      outputJSON( $results );
      break;
    case "vwmydayplan":
      $RSF = $_GET[ 'rSF' ];
      $cdate = $_GET[ 'cdate' ];
      if ( $cdate == '' || $cdate == "''" || $cdate == null ) {
        $cdate = date( "Y-m-d" );
      }
      $sql = "EXEC getTodayTP_native_App '" . $RSF . "', '" . $cdate . "'";
      //$sql = "EXEC SPR_TodayTP_APP '" . $RSF . "', '" . $cdate . "'";
      outputJSON( performQuery( $sql ) );
      break;
    case "rcpadetail_new":
      $arc = $_GET[ 'arc' ];
      $arc_dt = $_GET[ 'arc_dt' ];
      $sfCode = $_GET[ 'sfCode' ];
      $Rcpa = [];
      $query = "SELECT DrCode, DrName, ChmCode, ChmName, OPCode, OPName, OPQty, OPRate FROM Trans_RCPA_Head WHERE sf_code='" . $sfCode . "' AND AR_Code='" . $arc . "' AND ARMSL_Code='" . $arc_dt . "'";
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
      $arc = $_GET[ 'arc' ];
      $arc_dt = $_GET[ 'arc_dt' ];
      $sfCode = $_GET[ 'sfCode' ];
      $query = "SELECT H.DrName,H.ChmName,H.OPName,H.OPQty,H.OPUnit,D.CompName,D.CompPName,D.CPQty,D.CPUnit FROM Trans_RCPA_Head H INNER JOIN Trans_RCPA_Detail D ON H.pk_id=D.fk_pk_id WHERE sf_code='" . $sfCode . "' and ARMSL_Code='" . $arc_dt . "'";
      outputJSON( performQuery( $query ) );
      break;
    case "vwedit_activity":
      activity();
      break;
    case "event_captures_report":
      $arc = $_GET[ 'arc' ];
      $arc_dt = $_GET[ 'arc_dt' ];
      $sfCode = $_GET[ 'sfCode' ];
      $query = "select sf_code,('photos/'+imgurl)Eventimg, title, remarks from DCREvent_Captures where sf_code='$sfCode' and Trans_SlNo='$arc' and Trans_Detail_Slno='$arc_dt'";
      outputJSON( performQuery( $query ) );
      break;
    case "category_master":
      $sql = "EXEC GetProdBrand_App '" . $div . "'";
      outputJSON( performQuery( $sql ) );
      break;
    case "vwdcr_misseddates":
      $sql = "EXEC Get_MissedDates_App '" . $sfCode . "'";
      outputJSON( performQuery( $sql ) );
      break;
    case "gift_master":
      $sql = "EXEC getAppGift '" . $sfCode . "'";
      outputJSON( performQuery( $sql ) );
      break;
    case "doctor_category":
      $query = "select Doc_Cat_Code id,Doc_Cat_Name name from Mas_Doctor_Category where Division_code='" . $Owndiv . "' and Doc_Cat_Active_Flag=0";
      outputJSON( performQuery( $query ) );
      break;
    case "doctor_specialty":
      $query = "select Doc_Special_Code id,Doc_Special_Name name from Mas_Doctor_Speciality where Division_code='" . $Owndiv . "' and Doc_Special_Active_Flag=0";
      outputJSON( performQuery( $query ) );
      break;
    case "mas_doc_class":
      $query = "select Doc_ClsCode id,Doc_ClsSName name from Mas_Doc_Class where Division_code='" . $Owndiv . "' and Doc_Cls_ActiveFlag=0";
      outputJSON( performQuery( $query ) );
      break;
    case "mas_doc_qualification":
      $query = "select Doc_QuaCode id,Doc_QuaName name from Mas_Doc_Qualification where Division_code='" . $Owndiv . "' and Doc_Qua_ActiveFlag=0";
      outputJSON( performQuery( $query ) );
      break;
    case "prod_feedbk":
      $query = "select FeedBack_Id id,FeedBack_Name name from Mas_Product_Feedback where Division_code='" . $Owndiv . "' and Active_flag=0";
      outputJSON( performQuery( $query ) );
      break;
    case "vwfolders":
      $Div = explode( ",", $data[ 'divisionCode' ] . "," );
      $OwnDiv = ( string )$Div[ 0 ];
      $result = array();
      $sql = "select Move_MailFolder_Id id, Move_MailFolder_Name name from Mas_Mail_Folder_Name where division_code='$Owndiv'";
      $result = performQuery( $sql );
      array_unshift( $result,
        array( "id" => "inbox", "name" => "Inbox" ),
        array( "id" => "sent", "name" => "Sent Item" ),
        array( "id" => "view", "name" => "Viewed" ) );
      outputJSON( $result );
      break;
    case "getmailsf":
      $sfCode = $_GET[ 'sfCode' ];
      $divCode = $_GET[ 'divisionCode' ];
      $sql = "exec getFullHryList '$sfCode'";
      outputJSON( performQuery( $sql ) );
      break;
    case "vwleavetype":
      $query = "select Leave_Code id, Leave_SName name, Leave_Name from vwLeaveType where Division_code='" . $Owndiv . "'";
      outputJSON( performQuery( $query ) );
      break;
    case "map_competitor_product":
      $div = $_GET[ 'divisionCode' ];
      $divs = explode( ",", $div . "," );
      $Owndiv = ( string )$divs[ 0 ];
      $query = "select Comp_Sl_No as id,Comp_Name as name,Comp_Prd_Sl_No as pid,Comp_Prd_name as pname from Map_Competitor_Product where Division_code='" . $Owndiv . "' and Active_Flag=0";
      outputJSON( performQuery( $query ) );
      break;
    case "vwhosp_master_app":
      $query = "select Leave_Code id,Leave_SName name,Leave_Name from vwLeaveType where Division_code='" . $Owndiv . "'";
      outputJSON( performQuery( $query ) );
      break;
    case "vwactivity_csh_detail":
      $or = ( isset( $data[ 'or' ] ) && $data[ 'or' ] == 0 ) ? null : $data[ 'or' ];
      $where = isset( $data[ 'where' ] ) ? json_decode( $data[ 'where' ] ) : null;
      $query = "select * from vwActivity_CSH_Detail where Trans_Detail_Info_Type=" . $or . " and " . join( " or ", $where ) . " order by vstTime";
      outputJSON( performQuery( $query ) );
      break;
    default:
      $sfCode = ( isset( $data[ 'sfCode' ] ) && $data[ 'sfCode' ] == 0 ) ? null : $_GET[ 'sfCode' ];
      $div = $_GET[ 'divisionCode' ];
      $divs = explode( ",", $div . "," );
      $Owndiv = ( string )$divs[ 0 ];
      $divisionCode = ( int )$Owndiv;
      $today = ( isset( $data[ 'today' ] ) && $data[ 'today' ] == 0 ) ? null : $data[ 'today' ];
      $or = ( isset( $data[ 'or' ] ) && $data[ 'or' ] == 0 ) ? null : $data[ 'or' ];
      $wt = ( isset( $data[ 'wt' ] ) && $data[ 'wt' ] == 0 ) ? null : $data[ 'wt' ];
      $tableName = $data[ 'tableName' ];
      $coloumns = json_decode( $data[ 'coloumns' ] );
      $where = isset( $data[ 'where' ] ) ? json_decode( $data[ 'where' ] ) : null;
      $join = isset( $data[ 'join' ] ) ? $data[ 'join' ] : null;
      $orderBy = isset( $data[ 'orderBy' ] ) ? json_decode( $data[ 'orderBy' ] ) : null;
      if ( !is_null( $or ) ) {
        $results = getFromTableWR( $tableName, $coloumns, $divisionCode, $sfCode, $orderBy, $where, $join, $today, $wt );
        outputJSON( $results );
      } else {
        $results = getFromTable( $tableName, $coloumns, $divisionCode, $sfCode, $orderBy, $where, $join, $today, $wt );
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
    //$today = date('Y-m-d 00:00:00');
    $query .= " and cast(tab.activity_date as datetime)=cast('$today' as datetime)";
  }

  if ( !is_null( $orderBy ) ) {
    $query .= " ORDER BY " . join( ",", $orderBy );
  }
  return performQuery( $query );
}
?>