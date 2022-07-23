<?php

function activity() {
  $result = array();
  $query1 = "select Group_id from DCR_Detail_Activity  where sf_code='" . $_GET[ 'sfCode' ] . "' and Trans_Main_Sl_No='" . $_GET[ 'arc' ] . "' and Trans_Detail_Slno='" . $_GET[ 'arc_dt' ] . "' group by Group_id";
  $response1 = performQuery( $query1 );
  if ( count( $response1 ) > 0 ) {
    for ( $i = 0; $i < count( $response1 ); $i++ ) {
      $query2 = "select a.Trans_SlNo,a.Group_id,a.SF_Code,'1' Status,a.SF_Emp_ID,a.Division_Code,a.Activity_SlNo,a.Control_Id,a.Creation_Id,a.Creation_Code,a.Creation_Name,cast(convert(varchar,a.Activity_Date,101) as datetime) Activity_Date,a.Trans_Main_Sl_No,a.Trans_Detail_Slno,a.Trans_Detail_Info_Type,a.Trans_Detail_Info_Code,b.Activity_Name from DCR_Detail_Activity a left outer join Mas_Activity b on a.Activity_SlNo=b.Activity_SlNo where a.Group_id ='" . $response1[ $i ][ 'Group_id' ] . "'";
      $response2 = performQuery( $query2 );
      if ( count( $response2 ) > 0 ) {
        $activity = array();
        for ( $j = 0; $j < count( $response2 ); $j++ ) {
          array_push( $activity, array(
            'Sf_Code' => $response2[ $j ][ "SF_Code" ],
            'Ctrl_id' => $response2[ $j ][ "Control_Id" ],
            'Creation_id' => $response2[ $j ][ "Creation_Id" ],
            'Creation_Code' => $response2[ $j ][ "Creation_Code" ],
            'Creation_Name' => $response2[ $j ][ "Creation_Name" ] ) );
        }
        array_push( $result, array( 'Main_id' => $response2[ 0 ][ "Activity_SlNo" ], 'Main_Name' => $response2[ 0 ][ "Activity_Name" ], 'DCR_Date' => $response2[ 0 ][ "Activity_Date" ], 'Sf_code' => $response2[ 0 ][ "SF_Code" ], 'Group_id' => $response2[ 0 ][ "Group_id" ], 'Status' => $response2[ 0 ][ "Status" ], 'Activity_data' => $activity ) );
      }
    }
  }
  return outputJSON( $result );
}
?>