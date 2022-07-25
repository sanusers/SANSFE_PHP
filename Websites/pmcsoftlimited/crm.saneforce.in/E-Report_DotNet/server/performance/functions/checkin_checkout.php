<?php
date_default_timezone_set( "Asia/Kolkata" );
$Date_Format1 = date( 'Y-m-d H:i' );
$Date_Format2 = date( 'Y-m-d' );

function CheckIn( $SF_Code, $DivCode, $data ) {
    $query = "insert into Dcr_checkin(Cust_id,Cust_name,Sf_Code,Division_Code,Activity_date,Checkin_time,Checkin_Lat, Checkin_Long, Checkout_Lat,Checkout_Long,Status,Checkin_addrs) select '" . $data[ "cust_id" ] . "','" . $data[ "cust_name" ] . "','" . $SF_Code . "','" . $DivCode . "','$Date_Format2','" . $data[ "intime" ] . "','" . $data[ "lat" ] . "','" . $data[ "long" ] . "','','','0','" . $data[ "cust_add" ] . "'";
    performQuery( $query );
}

function CheckOut( $SF_Code ) {
    $query = "select top 1 ID from Dcr_checkin where sf_code='$SF_Code' and cust_id=" . $data[ "cust_id" ] . " order by ID DESC";
    $result = performQuery( $query );
    $id = $result[ 0 ][ 'ID' ];
    $query = "update Dcr_checkin set Checkout_time='$Date_Format1',Status='1',Checkout_Lat='" . $data[ "lat" ] . "',Checkout_Long='" . $data[ "long" ] . "',Checkout_addrs='" . $data[ "cust_add" ] . "' where ID='$id'";
    performQuery( $query );
}

function TPAttendance( $SF_Code, $DivCode, $data ) {
    if ( $_GET[ 'update' ] == 0 ) {
        $query = "exec Attendance_entry '$SF_Code','$DivCode','$Date_Format1','" . $data[ 'lat' ] . "','" . $data[ 'long' ] . "','$Date_Format2','" . $data[ 'address' ] . "'";
        $result = performQuery( $query );
    } else {
        $query = "select id from TP_Attendance_App where Sf_Code='$SF_Code' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time)) = '$Date_Format2' order by id desc";
        $tr = performQuery( $query );
        $id = $tr[ 0 ][ 'id' ];

        $query = "update TP_Attendance_App set End_Lat='" . $data[ 'lat' ] . "',End_Long='" . $data[ 'long' ] . "',End_Time='$Date_Format1',End_addres='" . $data[ 'address' ] . "' where id=$id";
        performQuery( $query );

        $query = "select ID from Attendance_history where Sf_Code='$SF_Code' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='$Date_Format2' order by id desc";
        $tr1 = performQuery( $query );
        $id1 = $tr1[ 0 ][ 'ID' ];

        $query = "update Attendance_history set End_Lat='" . $data[ 'lat' ] . "',End_Long='" . $data[ 'long' ] . "',End_Time='$Date_Format1', End_addres='" . $data[ 'address' ] . "' where ID=$id1";
        performQuery( $query );
        $result = [];
        $result[ "msg" ] = "1";
    }
    outputJSON( $result );
}
?>