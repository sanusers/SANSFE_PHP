<?php

function TourPlanFullMonth() {
    $TPDatas = $GLOBALS['Data'][ 0 ][ 'TPDatas' ];
    $query = "EXEC SV_SubmitTP '" . $GLOBALS['Data'][ 0 ][ 'SFCode' ] . "','" . $TPDatas[ 0 ][ 'Tour_Month' ] . "','" . $TPDatas[ 0 ][ 'Tour_Year' ] . "'";
    performQuery( $query );
    $result[ "success" ] = true;
    outputJSON( $result );
}
?>