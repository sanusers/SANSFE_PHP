<?php

function SaveSecondary( $SF_Code ) {
    for ( $i = 0; $i < count( $GLOBALS[ 'Data' ] ); $i++ ) {
        $sql = "select count(sf_code) as mRow from Trans_Pri_Sec_Sale where Trans_Month='" . $GLOBALS[ 'Data' ][ $i ][ 'Trans_Month' ] . "' and Trans_Year='" . $GLOBALS[ 'Data' ][ $i ][ 'Trans_Year' ] . "' and sf_code='" . $SF_Code . "' and Division_Code='$DivisionCode' and Stockist_Code='" . $GLOBALS[ 'Data' ][ $i ][ 'Stockist_Code' ] . "'";
        $data1 = performQuery( $sql );
        if ( $data1[ 0 ][ 'mRow' ] > 0 ) {
            $query1 = "Update Trans_Pri_Sec_Sale set Pri_Value='" . $GLOBALS[ 'Data' ][ $i ][ 'Pri_Value' ] . "',Sec_Value='" . $GLOBALS[ 'Data' ][ $i ][ 'Sec_Value' ] . "',Updated_Date=getdate() where Trans_Month='" . $GLOBALS[ 'Data' ][ $i ][ 'Trans_Month' ] . "' and Trans_Year='" . $GLOBALS[ 'Data' ][ $i ][ 'Trans_Year' ] . "' and sf_code='$SF_Code' and Division_Code='$DivisionCode' and Stockist_Code='" . $GLOBALS[ 'Data' ][ $i ][ 'Stockist_Code' ] . "'";
            performQuery( $query1 );
        } else {
            $query2 = "SELECT isNull(max(Sl_No),0)+1 as RwID FROM Trans_Pri_Sec_Sale";
            $trw = performQuery( $query2 );
            $pk = ( int )$trw[ 0 ][ 'RwID' ];
            $query3 = "insert into Trans_Pri_Sec_Sale(Sl_No,Stockist_Code,Stockist_Name,Trans_Month,Trans_Year,Pri_Value,Sec_Value,Division_Code,Created_Date,Approved_Flag, View_Flag, Entry_Mode,sf_code) select '$pk','" . $GLOBALS[ 'Data' ][ $i ][ 'Stockist_Code' ] . "','" . $GLOBALS[ 'Data' ][ $i ][ 'Stockist_Name' ] . "','" . $GLOBALS[ 'Data' ][ $i ][ 'Trans_Month' ] . "','" . $GLOBALS[ 'Data' ][ $i ][ 'Trans_Year' ] . "','" . $GLOBALS[ 'Data' ][ $i ][ 'Pri_Value' ] . "','" . $GLOBALS[ 'Data' ][ $i ][ 'Sec_Value' ] . "','$DivisionCode', getdate(), '0','0','Apps', '$SF_Code'";
            performQuery( $query3 );
        }
    }
    $results[ 'success' ] = true;
    outputJSON( $results );
}

function GetSecondary( $DivisionCode ) {
    $query = "select Sl_No,Stockist_Code,Stockist_Name,Trans_Month,Trans_Year,Pri_Value,Sec_Value,Division_Code,Approved_Flag,View_Flag,sf_code from Trans_Pri_Sec_Sale where Trans_Month='" . $_GET[ 'month' ] . "' and Trans_Year='" . $_GET[ 'year' ] . "' and Division_Code='" . $DivisionCode . "'";
    outputJSON( performQuery( $query ) );
}
?>