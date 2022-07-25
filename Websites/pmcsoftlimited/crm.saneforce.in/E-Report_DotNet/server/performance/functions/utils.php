<?php

function outputJSON( $array ) {
    $conn = $GLOBALS[ 'conn' ];
    echo str_replace( ".000000", "", json_encode( $array ) );
    sqlsrv_close( $conn );
}

function performQuery( $query ) {
    global $conn, $NeedRollBack;
    $result = array();
    if ( $res = sqlsrv_query( $conn, $query ) ) {
        if ( sqlsrv_errors() != null || !$res ) {
            $NeedRollBack = true;
        }
        $result = array();
        while ( $row = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC ) ) {
            $arr = array();
            foreach ( $row as $key => $value ) {
                if ( is_string( $value ) ) {
                    $arr[ $key ] = utf8_encode( trim( preg_replace( "/[\r\n]+/", " ", $value ) ) );
                } else if ( is_int( $value ) ) {
                    $arr[ $key ] = ( string )$value;
                } else {
                    $arr[ $key ] = $value;
                }
            }
            $result[] = $arr;
        }
        return $result;
    }
}

function performQueryWP( $query, $pram ) {
    global $conn, $NeedRollBack;
    $result = array();
    if ( $res = sqlsrv_query( $conn, $query, $pram ) ) {
        if ( sqlsrv_errors() != null || !$res ) {
            $NeedRollBack = true;
        }
        $qt = explode( " ", $query );
        if ( sqlsrv_errors() != null ) {
            outputJSON( sqlsrv_errors() );
            return false;
        } else {
            return true;
        }
    }
}
?>