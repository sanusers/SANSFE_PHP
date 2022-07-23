<?php

function Activity() {
    $result = array();
    $qury = "select DT.Activity_SlNo,H.Activity_Name,DT.Group_id from DCR_Detail_Activity DT  left outer join mas_activity H on DT.Activity_SlNo = H.Activity_SlNo where Trans_Main_Sl_No='" . $_GET[ 'arc' ] . "' and Trans_Detail_Slno='" . $_GET[ 'arc_dt' ] . "' group by DT.Activity_SlNo,H.Activity_Name,DT.Group_id";
    $response1 = performQuery( $qury );
    if ( count( $response1 ) > 0 ) {
        for ( $ilk = 0; $ilk < count( $response1 ); $ilk++ ) {
            $query = "select ROW_NUMBER() over (ORDER BY Group_id ASC) as slno,DT.Group_id,DH.Field_Name,DH.Order_by,DT.Creation_Name,cast(convert(varchar,DT.Activity_Date,101) as datetime) Activity_Date,DT.Updated_Time,DT.SF_Code,DT.Control_Id from mas_dynamic_screen_creation DH left outer join DCR_Detail_Activity DT ON DH.Activity_SlNo=DT.Activity_SlNo and  DH.Creation_Id=DT.Creation_Id and DH.Control_id=DT.Control_id and DH.Activity_SlNo='" . $response1[ $ilk ][ 'Activity_SlNo' ] . "' where DT.Group_id='" . $response1[ $ilk ][ 'Group_id' ] . "'  order by Order_by Asc";
            $response2 = performQuery( $query );
            if ( count( $response2 ) > 0 ) {
                $Rptact = array();
                for ( $il = 0; $il < count( $response2 ); $il++ ) {
                    array_push( $Rptact, array( 'slno' => $response2[ $il ][ "slno" ], 'Group_id' => $response2[ $il ][ "Group_id" ], 'Field_Name' => $response2[ $il ][ "Field_Name" ], 'Creation_Name' => $response2[ $il ][ "Creation_Name" ], 'Activity_Date' => $response2[ $il ][ "Activity_Date" ], 'Updated_Time' => $response2[ $il ][ "Updated_Time" ], 'SF_Code' => $response2[ $il ][ "SF_Code" ], 'Control_Id' => $response2[ $il ][ "Control_Id" ], 'Order_by' => $response2[ $il ][ "Order_by" ] ) );
                }
                array_push( $result, array( 'Main_id' => $response1[ $ilk ][ 'Activity_SlNo' ], 'Main_Name' => $response1[ $ilk ][ 'Activity_Name' ], 'Group_id' => $response1[ $ilk ][ "Group_id" ], 'Activity_data' => $Rptact ) );
            }
        }
    }
    outputJSON( $result );
}

function DynamicActivity( $DivisionCode ) {
    $query = "SELECT Activity_SlNo, Activity_Mode, REPLACE(Activity_Desig, ' ', '') Activity_Desig,Activity_SName,Activity_Name,Activity_OrderBy,Division_Code,Creation_date,Active_Flag, Activity_For,Activity_Available,Other_Multi_Activity_Name,Related_Activity_SlNo,Approval_Needed,Approved_By, Transaction_Involved,Editable FROM mas_activity WHERE Division_Code='" . $DivisionCode . "' AND Active_Flag='0'";
    outputJSON( performQuery( $query ) );
}

function DynamicViewDetails() {
    $slno = ( string )$GLOBALS[ 'Data' ][ 'slno' ];
    $sf = ( string )$GLOBALS[ 'Data' ][ 'SF' ];
    $div = str_replace( ",", "", $GLOBALS[ 'Data' ][ 'div' ] );
    $query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID    from mas_dynamic_screen_creation where Activity_SlNo='" . $slno . "' and Division_Code='" . $div . "' and Active_Flag='0' order by Order_by Asc";
    $res = performQuery( $query );
    if ( count( $res ) > 0 ) {
        for ( $il = 0; $il < count( $res ); $il++ ) {
            $id = $res[ $il ][ "Control_Id" ];
            if ( $id == "8" || $id == "9" ) {
                if ( $res[ $il ][ "Control_Para" ] == "Mas_ListedDr" ) {
                    $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "' and Sf_Code='" . $sf . "'";
                } else if ( $res[ $il ][ "Control_Para" ] == "Mas_Product_Detail" ) {
                    $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "' and Product_Active_Flag='0'";
                } else {
                    $qu = "select " . $res[ $il ][ "Table_code" ] . "," . $res[ $il ][ "Table_name" ] . " from " . $res[ $il ][ "Control_Para" ] . " where Division_Code='" . $div . "'";
                }

                $res[ $il ][ 'inputss' ] = $qu;
                $res[ $il ][ 'input' ] = performQuery( $qu );
            } else if ( $id == "12" || $id == "13" ) {
                $qu = "select Sl_No from Mas_Customized_Table_Name where Name_Table='" . $res[ $il ][ "Control_Para" ] . "'";
                $res[ $il ][ 'inputss' ] = $qu;
                $cus = performQuery( $qu );
                $qu = "select Mas_Sl_No,Customized_Name from Mas_Customized_Table where Name_Table_Slno='" . $cus[ 0 ][ "Sl_No" ] . "'";
                $cus = performQuery( $qu );
                $res[ $il ][ 'input' ] = $cus;
            } else {
                $res[ $il ][ 'input' ] = array();
            }
        }
    }
    outputJSON( $res );
}
?>