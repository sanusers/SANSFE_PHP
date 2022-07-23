<?php
date_default_timezone_set( "Asia/Kolkata" );
$Date_Format1 = date( 'Y-m-d H:i' );
$Date_Format2 = date( 'Y-m-d' );

$sfCode = $_GET[ 'sfCode' ];
$div = $_GET[ 'divisionCode' ];
$divs = explode( ",", $div . "," );
$Owndiv = ( string )$divs[ 0 ];
$data = $GLOBALS[ 'Data' ];
$temp = array_keys( $data[ 0 ] );
$vals = $data[ 0 ][ $temp[ 0 ] ];

function CheckIn() {
    $query = "insert into Dcr_checkin(Cust_id,Cust_name,Sf_Code,Division_Code,Activity_date,Checkin_time,Checkin_Lat, Checkin_Long, Checkout_Lat,Checkout_Long,Status,Checkin_addrs) select '" . $vals[ "cust_id" ] . "','" . $vals[ "cust_name" ] . "','" . $sfCode . "','" . $Owndiv . "','$Date_Format2','" . $vals[ "intime" ] . "','" . $vals[ "lat" ] . "','" . $vals[ "long" ] . "','','','0','" . $vals[ "cust_add" ] . "'";
    performQuery( $query );
}

function CheckOut() {
    $query = "select top 1 ID from Dcr_checkin where sf_code='$sfCode' and cust_id=" . $vals[ "cust_id" ] . " order by ID DESC";
    $result = performQuery( $query );
    $id = $result[ 0 ][ 'ID' ];
    $query = "update Dcr_checkin set Checkout_time='$Date_Format1',Status='1',Checkout_Lat='" . $vals[ "lat" ] . "',Checkout_Long='" . $vals[ "long" ] . "',Checkout_addrs='" . $vals[ "cust_add" ] . "' where ID='$id'";
    performQuery( $query );
}

function TPAttendance() {
    if ( $_GET[ 'update' ] == 0 ) {
        $query = "exec Attendance_entry '$sfCode','$Owndiv','$Date_Format1','".$vals[ 'lat' ]."','".$vals[ 'long' ]."','$Date_Format2','".$vals[ 'address' ]."'";
        $result = performQuery( $query );
    } else {
        $query = "select id from TP_Attendance_App where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time)) = '$Date_Format2' order by id desc";
        $tr = performQuery( $query );
        $id = $tr[ 0 ][ 'id' ];

        $query = "update TP_Attendance_App set End_Lat='".$vals[ 'lat' ]."',End_Long='".$vals[ 'long' ]."',End_Time='$Date_Format1',End_addres='".$vals[ 'address' ]."' where id=$id";
        performQuery( $query );

        $query = "select ID from Attendance_history where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='$Date_Format2' order by id desc";
        $tr1 = performQuery( $query );
        $id1 = $tr1[ 0 ][ 'ID' ];

        $query = "update Attendance_history set End_Lat='".$vals[ 'lat' ]."',End_Long='".$vals[ 'long' ]."',End_Time='$Date_Format1', End_addres='".$vals[ 'address' ]."' where ID=$id1";
        performQuery( $query );
        $result = [];
        $result[ "msg" ] = "1";
    }
    outputJSON( $result );
}
?>