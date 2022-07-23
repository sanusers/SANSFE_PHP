<?php

function SaveDCRActivity( $Dact ) {
    $response = array();
    if ( $Dact == undefined || $Dact == null || $Dact == '' ) {
        $data = $GLOBALS['Data'];
        $val = $data[ 'val' ];
    } else {
        $val = $Dact;
    }
    $sql = "select isnull(Max(cast(Max_slno as int)),0)+1 transslno from Activity_Group_SlNo with (INDEX(Idx_Activity_Group_SlNo))";
    $tr = performQuery( $sql );
    $sql = "update Activity_Group_SlNo set Max_slno ='".$tr[ 0 ][ 'transslno' ]."'";
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
                $query = "select Trans_Detail_Slno from vwActivity_MSL_Details where Trans_SlNo='" . $det_no . "' and Trans_Detail_Info_Code='" . $cust_code . "'";
                $arr = performQuery( $query );
                $main_no = $arr[ 0 ][ "Trans_Detail_Slno" ];
            }

            if ( $type_val == '2' || $type_val == '3' ) {
                $query = "select Trans_Detail_Slno from vwActivity_CSH_Detail where Trans_SlNo='" . $det_no . "' and Trans_Detail_Info_Code='" . $cust_code . "' and Trans_Detail_Info_Type = '".$type_val."'";
                $arr = performQuery( $query );
                $main_no = $arr[ 0 ][ "Trans_Detail_Slno" ];
            }

            if ( $type_val == '4' ) {
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

?>