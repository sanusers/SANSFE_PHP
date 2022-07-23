<?php

function Precall() {
    $query = "select SVL,DrCat,DrSpl,DrCamp,DrProd from vwPrecall_doctor where ListedDrCode='" . $_GET[ 'Msl_No' ] . "'";
    $response1 = performQuery( $query );
    if ( count( $response1 ) > 0 ) {
        $query = "select Trans_SlNo,Trans_Detail_Slno,convert(varchar,Activity_Date,0) Adate,Time DtTm1,convert(varchar,cast(convert(varchar,Activity_Date,101)+' '+Time  as datetime),20) as DtTm,isnull(CalFed,'') CalFed,Activity_Remarks,products,gifts,isnull(nextvstdate,'') nextvstdate from vwLastVstDet where rw=1 and Trans_Detail_Info_Code='" . $_GET[ 'Msl_No' ] . "' and SF_Code='" . $_GET[ 'sfCode' ] . "'";
        PreCallResult( $response1, performQuery( $query ) );
    } else {
        $result = array();
        $result[ 'success' ] = false;
        outputJSON( $result );
    }
}

function PreCallResult( $response1, $response2 ) {
    $result = array();
    if ( count( $response2 ) > 0 ) {
        $result[ 'SVL' ] = ( string )$response1[ 0 ][ 'SVL' ];
        $result[ 'DrCat' ] = ( string )$response1[ 0 ][ 'DrCat' ];
        $result[ 'DrSpl' ] = ( string )$response1[ 0 ][ 'DrSpl' ];
        $result[ 'DrCamp' ] = ( string )$response1[ 0 ][ 'DrCamp' ];
        $result[ 'DrProd' ] = ( string )$response1[ 0 ][ 'DrProd' ];
        $result[ 'success' ] = true;

        $dat = $response2[ 0 ][ 'DtTm1' ];
        $result[ 'LVDt' ] = date_format( $dat, 'd / m / Y g:i a' );
        $nextvstdate = $response2[ 0 ][ 'nextvstdate' ];
        $result[ 'next_visit_date' ] = $nextvstdate;
        $Prods = ( string )$response2[ 0 ][ 'products' ];
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
        $result[ 'CallFd' ] = ( string )$response2[ 0 ][ 'CalFed' ];
        $result[ 'Rmks' ] = ( string )$response2[ 0 ][ 'Activity_Remarks' ];
        $result[ 'ProdSmp' ] = $sSmp;
        $result[ 'Prodgvn' ] = $sProm;
        $result[ 'DrGft' ] = ( string )$response2[ 0 ][ 'gifts' ];
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
    outputJSON( $result );
}
?>