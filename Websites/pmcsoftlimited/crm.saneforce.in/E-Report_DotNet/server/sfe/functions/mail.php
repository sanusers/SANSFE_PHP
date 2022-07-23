<?php
$SF_Code = $_GET[ 'sfCode' ];
$RSF_Code = $_GET[ 'rSF' ];
$DivCode = explode( ",", $_GET[ 'divisionCode' ] . "," );
$DivisionCode = ( string )$DivCode[ 0 ];

function CreateMail( $Date_Format2, $Date_Format4 ) {
    $data = $GLOBALS[ 'Data' ][ 0 ];
    $file = $data[ 'fileName' ];
    $fileName = "";
    if ( !empty( $file ) ) {
        $info = pathinfo( $file );
        $FileName1 = basename( $file, '.' . $info[ 'extension' ] );
        $ext = $info[ 'extension' ];
        $fileName = $FileName1 . "_" . $SF_Code . "_" . $Date_Format4 . "." . $ext;
    }
    $msg1 = urldecode( $data[ 'message' ] );
    $msg = trim( $msg1, '"' );
    $sub1 = urldecode( $data[ 'subject' ] );
    $sub = trim( $sub1, '"' );
    $sql = "select max(isnull(Trans_sl_no,0))+1 transslno from trans_mail_head";
    $tr = performQuery( $sql );
    $sql = "insert into trans_mail_head(Trans_sl_no,System_ip,Mail_SF_From,Mail_SF_To,Mail_Subject,Mail_Content,Mail_Attachement,Mail_CC,Mail_BCC,Division_Code,Mail_Sent_Time,To_SFName,CC_Sfname,Bcc_SfName,Mail_SF_Name,sent_flag) select '".$tr[ 0 ][ 'transslno' ]."','','" . $SF_Code . "','" . $data[ 'to_id' ] . "','$sub','$msg','$fileName','" . $data[ 'cc_id' ] . "','" . $data[ 'bcc_id' ] . "','" . $DivisionCode . "','$Date_Format2','" . $data[ 'to' ] . "','" . $data[ 'cc' ] . "','" . $data[ 'bcc' ] . "','" . $data[ 'from' ] . "',0";
    performQuery( $sql );
    $ToCcBcc = explode( ",", $data[ 'ToCcBcc' ] );
    for ( $i = 0; $i < count( $ToCcBcc ); $i++ ) {
        if ( $ToCcBcc[ $i ] ) {
            $sql = "insert into trans_mail_detail(Trans_Sl_no,open_mail_id,mail_active_flag,Division_code) select '".$tr[ 0 ][ 'transslno' ]."','" . str_replace( ",", "", $ToCcBcc[ $i ] ) . "',0,'" . $DivisionCode . "'";
            performQuery( $sql );
        }
    }
    $result[ "success" ] = true;
    outputJSON( $result );
}

function MailView( $Date_Format1 ) {
    $sql = "update trans_mail_detail set Mail_Active_Flag='10',Mail_Read_Date='$Date_Format1' where Trans_Sl_No='" . $_GET[ 'id' ] . "'";
    performQuery( $sql );
    $result[ 'success' ] = true;
    outputJSON( $result );
}

function MailMove( $Date_Format1 ) {
    $sql = "update trans_mail_detail set Mail_moved_to='" . $_GET[ 'folder' ] . "',Mail_Active_Flag='12',mail_moved_date='$Date_Format1' where Trans_Sl_No='" . $_GET[ 'id' ] . "'";
    performQuery( $sql );
    $result[ 'success' ] = true;
    outputJSON( $result );
}

function MailDelete( $Date_Format1 ) {
    if ( $_GET[ 'folder' ] == "Sent" ) {
        $sql = "update MailBox_Details set Mail_SentItem_DelFlag=1 where Trans_Sl_No='" . $_GET[ 'id' ] . "'";
    } else {
        $sql = "update trans_mail_detail set Mail_Active_Flag='-1',mail_delete_date='$Date_Format1' where Trans_Sl_No='" . $_GET[ 'id' ] . "'";
    }
    performQuery( $sql );
    $result[ 'success' ] = true;
    outputJSON( $result );
}

function GetMailApp() {
    $folder = $_GET[ 'folder' ];
    $fldr = $folder;
    if ( $folder != 'Inbox' && $folder != 'Sent Item' && $folder != 'Viewed' ) {
        $folder = 'Flder';
    }
    $query = "EXEC MailInbox_DivCode_New_App '" . $SF_Code . "','" . $DivisionCode . "','" . $folder . "','" . $fldr . "','" . $_GET[ 'year' ] . "','" . $_GET[ 'month' ] . "',''";
    outputJSON( performQuery( $query ) );
}

function MailFileUpload( $Date_Format4 ) {
    $file = $_FILES[ 'imgfile' ][ 'name' ];
    $info = pathinfo( $file );
    $file_name = basename( $file, '.' . $info[ 'extension' ] );
    $ext = $info[ 'extension' ];
    $fileName = $file_name . "_" . $_GET[ 'sf_code' ] . "_" . $Date_Format4 . "." . $ext;
    $file_src = '../MasterFiles/Mails/Attachment/' . $fileName;
    move_uploaded_file( $_FILES[ 'imgfile' ][ 'tmp_name' ], $file_src );
}
?>