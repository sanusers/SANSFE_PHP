<?php

function DCR_Save() {
    if ( $vals[ "dcr_activity_date" ] != null && $vals[ "dcr_activity_date" ] != '' ) {
        $today = $vals[ "dcr_activity_date" ];
    }
    $vals[ "Worktype_code" ] = "'" . str_replace( "'", "", $vals[ "Worktype_code" ] ) . "'";

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
    $IsRCPA_Avail = 0;
    for ( $i = 1; $i < count( $data ); $i++ ) {
        $tableData = $data[ $i ];
        if ( isset( $tableData[ 'Activity_Doctor_Report' ] ) ) {
            $vTyp = 1;
            $DetTB = $tableData[ 'Activity_Doctor_Report' ];
            $cCode = $DetTB[ "doctor_code" ];
            $cName = $DetTB[ "doctor_name" ];
            $vTm = $DetTB[ "Doc_Meet_Time" ];
            $mTm = $DetTB[ "modified_time" ];
            $pob = $DetTB[ "Doctor_POB" ];
            $tvist = $DetTB[ "Tlvst" ];
            $tvs = str_replace( "'", "", $tvist );
            if ( sizeof( $DetTB[ "RCPAEntry" ] ) > 0 ) {
                $IsRCPA_Avail = 1;
            }
            $hospitalcode = $DetTB[ 'hospital_code' ];
            $hospitalname = $DetTB[ 'hospital_name' ];
            $nextVisitDate = $DetTB[ 'nextVisitDate' ];
            $proc = "svDCRLstDet_App";
            $check = 0;
            if ( $check >= $tvs )$VstFlag = 1;
        }
        if ( isset( $tableData[ 'Activity_Chemist_Report' ] ) ) {
            $vTyp = 2;
            $DetTB = $tableData[ 'Activity_Chemist_Report' ];
            $cCode = $DetTB[ "chemist_code" ];
            $cName = $DetTB[ "chemist_name" ];
            $vTm = $DetTB[ "Chm_Meet_Time" ];
            $mTm = $DetTB[ "modified_time" ];
            $pob = $DetTB[ "Chemist_POB" ];
        }
        if ( isset( $tableData[ 'Activity_Stockist_Report' ] ) ) {
            $vTyp = 3;
            $DetTB = $tableData[ 'Activity_Stockist_Report' ];
            $cCode = $DetTB[ "stockist_code" ];
            $vTm = $DetTB[ "Stk_Meet_Time" ];
            $mTm = $DetTB[ "modified_time" ];
            $pob = $DetTB[ "Stockist_POB" ];
            $cName = $DetTB[ "stockist_name" ];
        }
        if ( isset( $tableData[ 'Activity_UnListedDoctor_Report' ] ) ) {
            $vTyp = 4;
            $DetTB = $tableData[ 'Activity_UnListedDoctor_Report' ];
            $cCode = $DetTB[ "uldoctor_code" ];
            $vTm = $DetTB[ "UnListed_Doc_Meet_Time" ];
            $mTm = $DetTB[ "modified_time" ];
            $pob = $DetTB[ "UnListed_Doctor_POB" ];
            $proc = "svDCRUnlstDet_App";
            $cName = $DetTB[ "uldoctor_name" ];
        }
        if ( isset( $tableData[ 'Activity_Hosp_Report' ] ) ) {
            $vTyp = 5;
            $DetTB = $tableData[ 'Activity_Hosp_Report' ];
            $cCode = $DetTB[ "hospital_code" ];
            $vTm = $DetTB[ "Hosp_Meet_Time" ];
            $mTm = $DetTB[ "modified_time" ];
            $pob = $DetTB[ "Hosp_POB" ];
            $cName = $DetTB[ "hospital_name" ];
        }
        if ( isset( $tableData[ 'Activity_Cip_Report' ] ) ) {
            $vTyp = 6;
            $DetTB = $tableData[ 'Activity_Cip_Report' ];
            $cCode = $DetTB[ "doctor_code" ];
            $vTm = $DetTB[ "Doc_Meet_Time" ];
            $mTm = $DetTB[ "modified_time" ];
            $pob = $DetTB[ "Doctor_POB" ];
            $tvist = $DetTB[ "Tlvst" ];
            $tvs = str_replace( "'", "", $tvist );
            $nextVisitDate = $DetTB[ 'nextVisitDate' ];
            $hospitalcode = $DetTB[ 'hospital_code' ];
            $hospitalname = $DetTB[ 'hospital_name' ];
            $cName = $DetTB[ 'doctor_name' ];
            $proc = "svDCRCIPDet_App";
        }
        if ( isset( $tableData[ "Activity_Event_Captures" ] ) ) {
            $Event_Captures = $tableData[ "Activity_Event_Captures" ];
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
    } else {
        $ARCd = $trans[ 0 ][ 'trans_slno' ];
    }
    $loc = explode( ":", str_replace( "'", "", $DetTB[ "location" ] ) . ":" );
    $lat = $loc[ 0 ];
    $lng = $loc[ 1 ];
    $DetTB[ "geoaddress" ] = "NA";
    $apps = "'261'";
    $vst = "0";
    $sqlsp = "{call  ";
    if ( $vTyp != 0 ) {
        if ( $vTyp == 2 || $vTyp == 3 || $vTyp == 5 )
            $proc = "svDCRCSHDet_App";
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
        if ( $IsRCPA_Avail > 0 ) {
            SaveRCPAEntry( $ARCd, $ARDCd, $DetTB, $today );
        }
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
        for ( $j = 0; $j < count( $Event_Captures ); $j++ ) {
            $ev_imgurl = $sfCode . "_" . $Event_Captures[ $j ][ "imgurl" ];
            $ev_title = $Event_Captures[ $j ][ "title" ];
            $ev_remarks = $Event_Captures[ $j ][ "remarks" ];
            $sql = "insert into DCREvent_Captures(Trans_slno,Trans_detail_slno,imgurl,title,remarks,Division_Code,sf_code) select '" . $ARCd . "','" . $ARDCd . "','" . $ev_imgurl . "','" . $ev_title . "','" . $ev_remarks . "','" . $Owndiv . "','$sfCode'";
            performQuery( $sql );
        }
        if ( isset( $tableData[ 'Dynamic_Activity_App' ] ) ) {
            for ( $d = 0; $d < count( $tableData[ 'Dynamic_Activity_App' ] ); $d++ ) {
                $Dact = $tableData[ 'Dynamic_Activity_App' ][ $d ][ 'val' ];
                $resp[ "ErQry" ] = "Dynamic_Activity_App";
                include 'activity_save.php';
                SaveDCRActivity( $Dact );
            }
        }
    }
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

function delete_AR_entry( $SF, $WT, $Dt ) {
    $sql = "EXEC DeleteAREntry '" . $SF . "', '" . $WT . "', '" . $Dt . "'";
    performQuery( $sql );
}
?>