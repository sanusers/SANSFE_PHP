<?php

function approvalcount() {
  $sfCode = $_GET[ 'sfCode' ];
  $tp = array();
  $results = array();
  $apprCount = array();

  $sql = "select cast(DT.tp_date as date) tp_date,DT.NextMonth monthname,DT.tpmonth from vwTP_Current_Next DT where DT.sf_code='$sfCode'";
  $currnext = performQuery( $sql );
  $tp[ 'currnext' ] = $currnext;

  $query = "select Count(*) dcrappr_count  from DCRMain_Temp d inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code where d.Confirmed=1 and s.Reporting_To_SF='$sfCode' and cast(Activity_Date as date)<cast(GETDATE() as date)";
  $temp = performQuery( $query );
  $apprCount[] = $temp[ 0 ];

  $query = "select Count(*) tpappr_count from vwChkTransApproval where Reporting_To_SF='$sfCode'";
  $temp = performQuery( $query );
  $apprCount[] = $temp[ 0 ];

  $query = "select Count(*) leaveappr_count from vwLeave vl INNER JOIN vwLeaveType vw ON vl.Leave_Type = vw.leave_code where Reporting_To_SF='$sfCode'";
  $temp = performQuery( $query );
  $apprCount[] = $temp[ 0 ];

  $query = "select count(*) devappr_count from DCR_MissedDates d inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code where  d.status=3 and Reporting_To_SF='$sfCode'";
  $temp = performQuery( $query );
  $apprCount[] = $temp[ 0 ];
  $results[ 'tp' ] = $tp;
  $results[ 'apprCount' ] = $apprCount;
  return outputJSON( $result );
}
?>