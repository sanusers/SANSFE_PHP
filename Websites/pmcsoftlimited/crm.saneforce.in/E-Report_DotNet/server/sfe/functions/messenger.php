<?php

function SaveMessage() {
    $sXML = "<Root>";
    $sXML = $sXML . "<Msg SF=\"" . $GLOBALS[ 'Data' ][ 'SF' ] . "\" Dt=\"" . $GLOBALS[ 'Data' ][ "MsgDt" ] . "\" To=\"" . $GLOBALS[ 'Data' ][ "MsgTo" ] . "\" ToName=\"" . $GLOBALS[ 'Data' ][ "MsgToName" ] . "\" mTxt=\"" . $GLOBALS[ 'Data' ][ "MsgText" ] . "\" mPID=\"" . $GLOBALS[ 'Data' ][ "MsgParent" ] . "\" />";
    $sXML = $sXML . "</Root>";
    $sql = "EXEC iOS_SvMsgConversation '" . $sXML . "'";
    outputJSON( performQuery( $sql ) );
	include 'notification.php';
    Chat( $GLOBALS[ 'Data' ][ 'SF' ], $GLOBALS[ 'Data' ][ 'MsgTo' ],$GLOBALS[ 'Data' ][ "MsgText" ], 0 );
}

function GetMessage($SFCD) {
    $sql = "EXEC iOS_GetMsgConversation '" . $SFCD . "','" . $GLOBALS[ 'Data' ][ 'MsgDt' ] . "'";
    $result = performQuery( $sql );
    $sql = "EXEC iOS_GetMsgConversationFiles '" . $SFCD . "','" . $GLOBALS[ 'Data' ][ 'MsgDt' ] . "'";
    $result1 = performQuery( $sql );
    for ( $il = 0; $il < count( $result ); $il++ ) {
        $msgId = $result[ $il ][ "Msg_Id" ];
        $rArry = array_filter( $result1, function ( $item )use( $msgId ) {
            return ( $item[ "Msg_Id" ] === $msgId );
        } );
        $nAry = array();
        foreach ( $rArry as $key => $value ) {
            $nAry[] = $rArry[ $key ];
        }
        $result[ $il ][ "Files" ] = $nAry;
    }
    outputJSON( $result );
}
?>