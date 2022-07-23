<?php
$sfCode = $_GET[ 'sfCode' ];
$RSF_Code = $_GET[ 'rSF' ];
$eDate = $_GET[ 'eDate' ];
if ( $eDate == '' || $eDate == null ) {
    $today = date( 'Y-m-d 00:00:00' );
} else {
    $today = date( "Y-m-d 00:00:00", strtotime( $eDate ) );
}

function EntryCount() {
    $query = "SELECT work_Type worktype_code,Remarks daywise_remarks,Half_Day_FW halfdaywrk from vwActivity_Report H where SF_Code='" . $RSF_Code . "' and FWFlg <> 'F' and cast(activity_date as datetime)=cast('$today' as datetime)";
    $data = performQuery( $query );
    $result = array();
    if ( count( $data ) > 0 ) {
        $result[ "success" ] = false;
        $result[ 'data' ] = $data;
        outputJSON( $result );
        die;
    }
    $result[ "success" ] = true;
    $result[ 'data' ] = Entry_Count();
    outputJSON( $result );
}

function Entry_Count() {
    $results = array();
    $query = "select Count(Trans_Detail_Info_Code) doctor_count from vwActivity_MSL_Details D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) chemist_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=2";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) stockist_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=3";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) uldoctor_count from vwActivity_Unlst_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=4";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select isnull((SELECT top 1 isnull(remarks,'') from vwActivity_Report where sf_code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)),'') as remarks";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select isnull((SELECT top 1 Half_Day_FW from vwActivity_Report where sf_code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)),'') as halfdaywrk";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) hospital_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=5";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];

    $query = "select Count(Trans_Detail_Info_Code) cip_count from vwActivity_CIP_Details D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=6";
    $temp = performQuery( $query );
    $results[] = $temp[ 0 ];
    return $results;
}
?>