<?php

function savetourplan( $State ) {
    $data = json_decode( $_POST[ 'data' ], true );
    $sfCode = $_GET[ 'sfCode' ];
    $sfCode = ( string )$data[ 'SF' ];
    $sfName = ( string )$data[ 'SFName' ];
    $DivCodes = ( string )$data[ 'DivCode' ];
    $DivCode = explode( ",", $DivCodes . "," );
    $TPDatas = $data[ 'TPDatas' ];

    for ( $i = 0; $i < count( $TPDatas ); $i++ ) {
        $TPData = $TPDatas[ $i ];
        if ( $TPData[ "dayno" ] != "" ) {
            $TPDet = $TPData[ "DayPlan" ];
            $TPWTCd = array();
            $TPWTNm = array();
            $TPSFCd = array();
            $TPSFNm = array();
            $TPPlCd = array();
            $TPPlNm = array();
            $TPDRCd = array();
            $TPDRNm = array();
            $TPCHCd = array();
            $TPCHNm = array();
            $TPJWCd = array();
            $TPJWNm = array();
            $TPRmks = array();
            $TPSTCd = array();
            $TPSTNm = array();
            $TPOBJCd = array();
            $TPOBJNm = array();
            for ( $il = 0; $il < count( $TPDet ); $il++ ) {
                array_push( $TPWTCd, $TPDet[ $il ][ "WTCd" ] );
                array_push( $TPWTNm, $TPDet[ $il ][ "WTNm" ] );
                array_push( $TPSFCd, $TPDet[ $il ][ "HQCd" ] );
                array_push( $TPSFNm, $TPDet[ $il ][ "HQNm" ] );
                array_push( $TPPlCd, $TPDet[ $il ][ "TerrCd" ] );
                array_push( $TPPlNm, $TPDet[ $il ][ "TerrNm" ] );
                array_push( $TPJWCd, $TPDet[ $il ][ "JWCd" ] );
                array_push( $TPJWNm, $TPDet[ $il ][ "JWNm" ] );
                array_push( $TPDRCd, $TPDet[ $il ][ "DRCd" ] );
                array_push( $TPDRNm, $TPDet[ $il ][ "DRNm" ] );
                array_push( $TPCHCd, $TPDet[ $il ][ "CHCd" ] );
                array_push( $TPCHNm, $TPDet[ $il ][ "CHNm" ] );
                array_push( $TPSTCd, $TPDet[ $il ][ "STCd" ] );
                array_push( $TPSTNm, $TPDet[ $il ][ "STNm" ] );
                array_push( $TPRmks, $TPDet[ $il ][ "DayRmk" ] );
                array_push( $TPOBJCd, $TPDet[ $il ][ "objectiveid" ] );
                array_push( $TPOBJNm, $TPDet[ $il ][ "objective" ] );
            }

            $query = "exec iOS_svTourPlanNew '" . $sfCode . "','" . $sfName . "','" . $data[ 'TPMonth' ] . "','" . $data[ 'TPYear' ] . "','" . $TPData[ "TPDt" ] . "','" . $State . "','" . $TPWTCd[ 0 ] . "','" . $TPWTCd[ 1 ] . "','" . $TPWTCd[ 2 ] . "','" . $TPWTNm[ 0 ] . "','" . $TPWTNm[ 1 ] . "','" . $TPWTNm[ 2 ] . "','" . $TPSFCd[ 0 ] . "','" . $TPSFCd[ 1 ] . "','" . $TPSFCd[ 2 ] . "','" . $TPSFNm[ 0 ] . "','" . $TPSFNm[ 1 ] . "','" . $TPSFNm[ 2 ] . "','" . $TPPlCd[ 0 ] . "','" . $TPPlCd[ 1 ] . "','" . $TPPlCd[ 2 ] . "','" . $TPPlNm[ 0 ] . "','" . $TPPlNm[ 1 ] . "','" . $TPPlNm[ 2 ] . "','" . $TPJWCd[ 0 ] . "','" . $TPJWCd[ 1 ] . "','" . $TPJWCd[ 2 ] . "','" . $TPJWNm[ 0 ] . "','" . $TPJWNm[ 1 ] . "','" . $TPJWNm[ 2 ] . "','" . $TPDRCd[ 0 ] . "','" . $TPDRCd[ 1 ] . "','" . $TPDRCd[ 2 ] . "','" . $TPDRNm[ 0 ] . "','" . $TPDRNm[ 1 ] . "','" . $TPDRNm[ 2 ] . "','" . $TPCHCd[ 0 ] . "','" . $TPCHCd[ 1 ] . "','" . $TPCHCd[ 2 ] . "','" . $TPCHNm[ 0 ] . "','" . $TPCHNm[ 1 ] . "','" . $TPCHNm[ 2 ] . "','" . $TPRmks[ 0 ] . "','" . $TPRmks[ 1 ] . "','" . $TPRmks[ 2 ] . "','" . $DivCode[ 0 ] . "','" . $TPSTCd[ 0 ] . "','" . $TPSTCd[ 1 ] . "','" . $TPSTCd[ 2 ] . "','" . $TPSTNm[ 0 ] . "','" . $TPSTNm[ 1 ] . "','" . $TPSTNm[ 2 ] . "','" . $TPOBJCd[ 0 ] . "','" . $TPOBJNm[ 0 ] . "','" . $TPOBJCd[ 1 ] . "','" . $TPOBJNm[ 1 ] . "','" . $TPOBJCd[ 2 ] . "','" . $TPOBJNm[ 2 ] . "','0','Apps'";
            performQuery( $query );

            $query = "exec svTourPlan_detail '" . $sfCode . "','" . $sfName . "','" . $data[ 'TPMonth' ] . "','" . $data[ 'TPYear' ] . "','" . $TPData[ "TPDt" ] . "','" . $State . "','" . $TPWTCd[ 0 ] . "','" . $TPWTCd[ 1 ] . "','" . $TPWTCd[ 2 ] . "','" . $TPWTNm[ 0 ] . "','" . $TPWTNm[ 1 ] . "','" . $TPWTNm[ 2 ] . "','" . $TPSFCd[ 0 ] . "','" . $TPSFCd[ 1 ] . "','" . $TPSFCd[ 2 ] . "','" . $TPSFNm[ 0 ] . "','" . $TPSFNm[ 1 ] . "','" . $TPSFNm[ 2 ] . "','" . $TPPlCd[ 0 ] . "','" . $TPPlCd[ 1 ] . "','" . $TPPlCd[ 2 ] . "','" . $TPPlNm[ 0 ] . "','" . $TPPlNm[ 1 ] . "','" . $TPPlNm[ 2 ] . "','" . $TPJWCd[ 0 ] . "','" . $TPJWCd[ 1 ] . "','" . $TPJWCd[ 2 ] . "','" . $TPJWNm[ 0 ] . "','" . $TPJWNm[ 1 ] . "','" . $TPJWNm[ 2 ] . "','" . $TPDRCd[ 0 ] . "','" . $TPDRCd[ 1 ] . "','" . $TPDRCd[ 2 ] . "','" . $TPDRNm[ 0 ] . "','" . $TPDRNm[ 1 ] . "','" . $TPDRNm[ 2 ] . "','" . $TPCHCd[ 0 ] . "','" . $TPCHCd[ 1 ] . "','" . $TPCHCd[ 2 ] . "','" . $TPCHNm[ 0 ] . "','" . $TPCHNm[ 1 ] . "','" . $TPCHNm[ 2 ] . "','" . $TPRmks[ 0 ] . "','" . $TPRmks[ 1 ] . "','" . $TPRmks[ 2 ] . "','" . $DivCode[ 0 ] . "','" . $TPSTCd[ 0 ] . "','" . $TPSTCd[ 1 ] . "','" . $TPSTCd[ 2 ] . "','" . $TPSTNm[ 0 ] . "','" . $TPSTNm[ 1 ] . "','" . $TPSTNm[ 2 ] . "',0,'Apps','','','','','',''";
            performQuery( $query );
            $result[ "Qry" ] = $query;
        }
    }
    $result[ "success" ] = true;
    return outputJSON( $result );
}
?>