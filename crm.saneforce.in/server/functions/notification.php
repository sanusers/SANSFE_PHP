<?php

function notification( $SFCode, $Msge, $Type ) {
    $query = "select a.Sf_Name name,b.app_device_id reg_id from Mas_Salesforce a left JOIN Access_table b ON a.Sf_Code = b.sf_code where a.sf_code='$SFCode'";
    $response = performQuery( $query );
    $SFName = $response[ 0 ][ 'name' ];
    $FCMToken = $response[ 0 ][ 'reg_id' ];
    $Message = 'Hi ' . $SFName . ' ' . $Msge;
    FCM( $FCMToken, $Message, 'SANSFE' );
}

function Chat( $SFCodeFrom, $SFCodeTo, $Msge, $Type ) {
    $query_1 = "select a.Sf_Name name,b.DeviceRegId reg_id from Mas_Salesforce a left JOIN Access_table b ON a.Sf_Code = b.sf_code where a.sf_code='".$SFCodeTo."'";
    $response_1 = performQuery( $query_1 );
    $FCMToken = $response_1[ 0 ][ 'reg_id' ];

    $query_2 = "select Sf_Code code,Sf_Name name from Mas_Salesforce where sf_code='".$SFCodeFrom."'";
    $response_2 = performQuery( $query_2 );
    
	$Message = 'Hi, ' . '~' . $response_2[ 0 ][ 'code' ] . '~' . $response_2[ 0 ][ 'name' ] . '~' . ' has send you a Message' . '~' . $Msge;
    FCM( $FCMToken, $Message, 'SANSFE' );
}

function FCM( $FCMToken, $Message, $Title ) {
    define( "GOOGLE_API_KEY", "AAAA72Fk1cA:APA91bFCX24_-3-x6qKu5bHHaL3THqXSPlxwd-847vBm1eFdF0lFpeNGF4OtEfbp3Rms6dtJ38VGniX4vM3RHi-E5NxpyO_MAgYRjTtoZ5swG-5x849BW8QKb5MzkbJU0w6Z6z6Lpite" );
    define( "GOOGLE_FCM_URL", "https://fcm.googleapis.com/fcm/send" );
    $postobject = array( 'registration_ids' => array( $FCMToken ), 'notification' => array( "body" => $Message, "title" => $Title ), 'priority' => 'high' );
    $headers = array( 'Authorization: key=' . GOOGLE_API_KEY, 'Content-Type: application/json' );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, GOOGLE_FCM_URL );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $postobject ) );
    $result = curl_exec( $ch );
    if ( $result === FALSE ) {
        die( 'Problem occurred: ' . curl_error( $ch ) );
    }
    curl_close( $ch );
}
?>