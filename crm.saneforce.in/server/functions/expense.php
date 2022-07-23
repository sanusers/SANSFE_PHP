<?php

function Expense() {
    $dcrdate = date( 'd-m-Y' );
    $date = date( 'Y-m-d H:i:s' );

    $desig = $_GET[ 'desig' ];
    $sfCode = $_GET[ 'sfCode' ];
    $update = $_GET[ 'update' ];
    $divCode = $_GET[ 'divisionCode' ];
    $divisionCode = explode( ",", $divCode );
    $data = json_decode( $_POST[ 'data' ], true );
    $res = $data[ 0 ][ 'expense' ];

    $sql = "SELECT isNull(max(sl_no),0)+1 as RwID FROM Trans_FM_Expense_Head";
    $tRw = performQuery( $sql );
    $pk = ( int )$tRw[ 0 ][ 'RwID' ];
    if ( $update == 1 ) {
        updateEntry( $sfCode );
    }

    $sql = "insert into Trans_FM_Expense_Head(Sf_Code,Month,Year,sndhqfl,Division_Code,snd_dt,Sf_Name) select '$sfCode',MONTH('$date'),YEAR('$date'),0,$divisionCode[0],'$date','" . $res[ 'sfName' ] . "'";
    performQuery( $sql );

    $sql = "insert into Trans_FM_Expense_Detail(DCR_Date,Expense_wtype_Code,Expense_wtype_Name,Place_of_Work,Expense_Place_No,Division_Code,Expense_Allowance,Expense_Distance,Expense_Fare,Created_Date,LastUpdt_Date,Sf_Name,Sf_Code,Expense_Total) select '$dcrdate','" . $res[ 'worktype' ] . "','" . $res[ 'worktype_name' ] . "','" . $res[ 'place' ] . "','" . $res[ 'placeno' ] . "',$divisionCode[0],'" . $res[ 'allowance' ] . "','" . $res[ 'distance' ] . "','" . $res[ 'fare' ] . "','$date','$date','" . $res[ 'sfName' ] . "','$sfCode','" . $res[ 'tot' ] . "'";
    performQuery( $sql );

    $sql = "SELECT sl_no, Total_Allowance, Total_Distance, Total_Fare, Total_Expense, Total_Additional_Amt FROM Trans_Expense_Amount_Detail where Month=MONTH('$date') and year=YEAR('$date') and Sf_Code='$sfCode'";
    $tRw = performQuery( $sql );
    if ( empty( $tRw ) ) {
        $additionalAmount = $res[ 'additionalTot' ] + $res[ 'tot' ];
        $sql = "insert into Trans_Expense_Amount_Detail(Sf_Code,Month,Year,Division_Code,Sf_Name,Total_Allowance,Total_Distance,Total_Fare,Total_Expense,Total_Additional_Amt,Grand_Total) select '$sfCode',MONTH('$date'),YEAR('$date'),$divisionCode[0], '" . $res[ 'sfName' ] . "','" . $res[ 'allowance' ] . "','" . $res[ 'distance' ] . "','" . $res[ 'fare' ] . "','" . $res[ 'tot' ] . "','" . $res[ 'additionalTot' ] . "',$additionalAmount";
        performQuery( $sql );
    } else {
        $totAllowance = $tRw[ 0 ][ 'Total_Allowance' ] + $res[ 'allowance' ];
        $totDistance = $tRw[ 0 ][ 'Total_Distance' ] + $res[ 'distance' ];
        $totFare = $tRw[ 0 ][ 'Total_Fare' ] + $res[ 'fare' ];
        $totalExpense = $tRw[ 0 ][ 'Total_Expense' ] + $res[ 'tot' ];
        $totAdditionalAmt = $tRw[ 0 ][ 'Total_Additional_Amt' ] + $res[ 'additionalTot' ];
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
    outputJSON( $resp );
}

function MiscellaneousExpense($sfCode, $vals) {
    for ( $i = 0; $i < count( $vals ); $i++ ) {
        $query = "insert into Exp_miscellaneous_zoom (Expense_typ,Expense_Date,Expense_Parameter_Code,Expense_Parameter_Name,Amt,SF_Code,Expense_month,	expense_year,Division_Code) select '" . $vals[ $i ][ 'Expense_type' ] . "','" . $vals[ $i ][ 'Expense_date' ] . "','" . $vals[ $i ][ 'Expense_Parameter_Code' ] . "','" . $vals[ $i ][ 'Expense' ] . "','" . $vals[ $i ][ 'amount' ] . "','" . $sfCode . "','" . $vals[ $i ][ 'Expense_month' ] . "','" . $vals[ $i ][ 'Expense_year' ] . "','" . $Owndiv . "'";
        performQuery( $query );
    }
}
?>