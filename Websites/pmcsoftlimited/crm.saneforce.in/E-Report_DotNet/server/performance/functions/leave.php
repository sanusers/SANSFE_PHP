<?php

function LeaveStatus( $SF_Code ) {
    $query = "select id,sf_code,Leave_Type_Code,No_Of_Days,Leave_Taken_Days,Leave_Balance_Days,Trans_Year from vwLeaveEntitle where Sf_Code='" . $SF_Code . "'";
    outputJSON( performQuery( $query ) );
}

function LeaveHistory( $SF_Code, $DivisionCode ) {
    $query = "select a.sf_code,b.division_code,(isnull(b.Leave_SName,'') +' - '+isnull(b.Leave_Name,'') ) Leave_type,convert(varchar,a.From_Date,106)From_Date,convert(varchar,a.To_Date,106)To_Date,a.No_of_Days,convert(varchar,a.Created_Date,0) Apply_date,isnull(a.Rejected_Reason,'')Rejected_Reason,isnull(a.Reason,'') leave_Reason,isnull(a.Rejected_Reason,'')Rejected_Reason,a.Address,a.Leave_Active_Flag from mas_Leave_Form a left outer join mas_leave_type b on a.Leave_Type = b.Leave_code where  a.sf_code='" . $SF_Code . "' and b.division_code='" . $DivisionCode . "' and year(a.Created_Date)= year(getdate())";
    outputJSON( performQuery( $query ) );
}

function ViewLeave( $SF_Code ) {
    $query = "select vl.Sf_Code, vl.Leave_Id, vl.Leave_Type, vl.Reason,	vl.[Address], vl.FieldForceName, vl.Reporting_To_SF, vl.Designation, vl.HQ,	vl.EmpCode,	vl.From_Date, vl.To_Date,	vl.LeaveDays, vw.Leave_Code,	vw.Leave_SName,	vw.Leave_Name, vw.Division_Code from vwLeave vl INNER JOIN vwLeaveType vw ON vl.Leave_Type = vw.leave_code where Reporting_To_SF='" . $SF_Code . "'";
    outputJSON( performQuery( $query ) );
}

function CheckLeaveStatus( $SF_Code, $Date_Format3 ) {
    $sql = "select From_Date,To_Date,No_of_Days from mas_Leave_Form where To_Date>='" . $Date_Format3 . "' and sf_code='" . $SF_Code . "' and Leave_Active_Flag !=1 order by From_Date";
    $leaveDays = performQuery( $sql );
    $currentDate = date_create( $Date_Format3 );
    $disableDates = array();
    $sql = "SELECT Trans_SlNo, Sf_Code, Work_Type, Plan_No, Plan_Name, WorkType_Name FROM vwActivity_Report where SF_Code='" . $SF_Code . "' and cast(activity_date as datetime)=cast('" . $Date_Format3 . "' as datetime)";
    $dcrEntry = performQuery( $sql );
    if ( count( $dcrEntry ) > 0 )
        array_push( $disableDates );
    for ( $i = 0; $i < count( $leaveDays ); $i++ ) {
        $fromDate = $leaveDays[ $i ][ 'From_Date' ];
        $toDate = $leaveDays[ $i ][ 'To_Date' ];
        $noOfDays = $leaveDays[ $i ][ 'No_of_Days' ];
        if ( $currentDate > $fromDate )
            $fromDate = $currentDate;
        $diff = date_diff( $fromDate, $toDate, TRUE );
        $days = $diff->format( "%a" ) + 1;
        for ( $j = 0; $j < $days; $j++ ) {
            array_push( $disableDates, $fromDate->format( 'd/m/Y' ) );
            $fromDate->modify( '+1 day' );
        }
    }
    outputJSON( $disableDates );
}

function LeaveForm( $SF_Code, $DivisionCode, $data ) {
    $sql = "SELECT isNull(max(Leave_Id),0)+1 as RwID FROM Mas_Leave_Form";
    $tRw = performQuery( $sql );
    $pk = ( int )$tRw[ 0 ][ 'RwID' ];
    $query = "exec iOS_svLeaveApp '" . $SF_Code . "','" . $data[ 'From_Date' ] . "','" . $data[ 'To_Date' ] . "','" . $data[ 'No_of_Days' ] . "','" . $data[ 'Leave_Type' ] . "','" . $data[ 'Reason' ] . "','" . $data[ 'address' ] . "'";
    performQuery( $query );

    $sql = "SELECT sf_type FROM Mas_Salesforce_One where Sf_Code='$SF_Code'";
    $sfType = performQuery( $sql );
    $days = $data[ 'No_of_Days' ];
    $date = $data[ 'From_Date' ];
    for ( $i = 1; $i <= $days; $i++ ) {
        $query = "exec ChkandPostLeaveDt 0,'$SF_Code'," . $sfType[ 0 ][ 'sf_type' ] . ",$DivisionCode,'$date','','apps'";
        $results = performQuery( $query );
        $date = date( 'Y-m-d', strtotime( $date . ' + 1 days' ) );
    }
}

function LeaveValidation( $SF_Code, $LeaveType, $mFromDate, $mToDate ) {
    $fdate = strtotime( str_replace( "Z", "", str_replace( "T", " ", $mFromDate ) ) );
    $todate = strtotime( str_replace( "Z", "", str_replace( "T", " ", $mToDate ) ) );
    $from = date( 'Y-m-d 00:00:00', $fdate );
    $todt = date( 'Y-m-d 00:00:00', $todate );
    $query = "exec iOS_getLvlValidate '" . $SF_Code . "','" . $from . "','" . $todt . "','" . $LeaveType . "' ";
    outputJSON( performQuery( $query ) );
}

function LeaveType( $DivisionCode ) {
    $query = "select Leave_Code id, Leave_SName name, Leave_Name from vwLeaveType where Division_code='" . $DivisionCode . "'";
    outputJSON( performQuery( $query ) );
}
?>