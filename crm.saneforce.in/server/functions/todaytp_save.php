<?php

function SvMyTodayTP() {
    $data = $GLOBALS[ 'Data' ];
    $DivCode = explode( ",", $data[ 'Div' ] . "," );
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
    $InsMode = ( string )$data[ 'InsMode' ];

    $HeaderId = ( isset( $_GET[ 'Head_id' ] ) && strlen( $_GET[ 'Head_id' ] ) == 0 ) ? null : $_GET[ 'Head_id' ];
    if ( $HeaderId != null ) {
        $query = "exec Delete_reject_dcr '$HeaderId' ";
        performQuery( $query );
    }

    $qry = "select count(Leave_Id)Lcnt from mas_Leave_Form where SF_code='" . $data[ 'SF' ] . "' and Leave_Active_Flag<>1 and (cast(From_Date as date)<=cast('".$data[ 'TPDt' ]."' as date) and cast('".$data[ 'TPDt' ]."' as date)<=To_Date or cast(From_Date as date)<=cast('".$data[ 'TPDt' ]."' as date) and cast('".$data[ 'TPDt' ]."' as date)<=To_Date or cast(From_Date as date)>=cast('".$data[ 'TPDt' ]."' as date) and To_Date<=cast('".$data[ 'TPDt' ]."' as date))";
    $Lary = performQuery( $qry );
    if ( $Lary[ 0 ][ "Lcnt" ] > 0 ) {
        $result[ "Msg" ] = "Today Already Leave Posted...";
        $result[ "success" ] = false;
        return $result;
    }

    $query = "select Count(Trans_SlNo) Cnt from vwActivity_Report where Sf_Code='" . $data[ 'SF' ] . "' and Confirmed <>'2' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $data[ 'TPDt' ] . "' as datetime),101) as datetime) and FWFlg='L'";
    $ExisArr = performQuery( $query );
    if ( $ExisArr[ 0 ][ "Cnt" ] > 0 ) {
        $result[ "Msg" ] = "Today Already Leave Posted...";
        $result[ "success" ] = false;
        return $result;
    } else {
        $query = "select Count(Trans_SlNo) Cnt from vwActivity_Report where Sf_Code='" . $data[ 'SF' ] . "' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $data[ 'TPDt' ] . "' as datetime),101) as datetime) and Work_Type<>'" . $WT . "'";
        $ExisArr = performQuery( $query );
        $result[ "cqry" ] = $query;
        if ( $ExisArr[ 0 ][ "Cnt" ] > 0 && $InsMode == "0" ) {
            $result[ "Msg" ] = "Already you are submitted your work. Now you are deviate. Do you want continue?";
            $result[ "update" ] = true;
            $result[ "success" ] = false;
        } else {
            $query = "exec iOS_svTodayTP '" . $data[ 'SF' ] . "','" . $data[ 'SFMem' ] . "','" . $PlnCd . "','" . $PlnNM . "','" . $WT . "','" . $WTNM . "','" . $Rem . "','" . $loc . "','" . $data[ 'TPDt' ] . "','" . $TpVwFlg . "','" . $TpDrc . "','" . $TpCluster . "','" . $TpWrktype . "'";
            //echo $query;
            performQuery( $query );
            if ( $InsMode == "2" ) {
                $query = "select Work_Type,WorkType_Name,FWFlg,Half_Day_FW from vwActivity_Report where Sf_Code='" . $data[ 'SF' ] . "' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $data[ 'TPDt' ] . "' as datetime),101) as datetime) and Work_Type<>'" . $WT . "'";
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
                $query = $query . "Half_Day_FW='" . $HwTy . "' where Sf_Code='" . $data[ 'SF' ] . "' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('" . $data[ 'TPDt' ] . "' as datetime),101) as datetime)";
                performQuery( $query );
                performQuery( str_replace( "DCRMain_Trans", "DCRMain_Temp", $query ) );
            } else {
                if ( $InsMode == "1" ) {
                    $query = "exec DelDCRTempByDt '" . $data[ 'SF' ] . "','" . date( 'Y-m-d 00:00:00.000', strtotime( $data[ 'TPDt' ] ) ) . "'";
                    performQuery( $query );
                }

                $query = "exec svDCRMain_App '" . $data[ 'SF' ] . "','" . date( 'Y-m-d 00:00:00.000', strtotime( $data[ 'TPDt' ] ) ) . "','" . $WT . "','" . $PlnCd . "','" . $DivCode[ 0 ] . "','" . $Rem . "','','app'";
                $result[ "aqry" ] = $query;
                performQuery( $query );
            }
            $result[ "Msg" ] = "Today Work Plan Submitted Successfully...";
            $result[ "success" ] = true;
        }
        outputJSON($result);
    }
}
?>