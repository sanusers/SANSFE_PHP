<?php

function Doctor_Master( $data ) {
    $primary_key = "ListedDrCode";
    $row_id = $data[ 'doctorCode' ];
    $data[ 'Update_Mode' ] = "'Apps'";
    foreach ( $data as $col => $val ) {
        $cols[] = $col . " = " . $val;
    }
    $query = "UPDATE Mas_ListedDr set " . join( ", ", $cols ) . " where $primary_key = $row_id";
    performQuery( $query );
}

function Chemist_Master( $sfCode, $vals ) {
    $query = "SELECT isNull(max(Chemists_Code),0)+2 as RwID FROM Mas_Chemists";
    $result = performQuery( $query );
    $pk = ( int )$result[ 0 ][ 'RwID' ];

    $query = "insert into Mas_Chemists(Chemists_Code,Chemists_Name,Chemists_Address1,Territory_Code,Chemists_Phone,Chemists_Contact,Division_Code,Cat_Code,Chemists_Active_Flag,Sf_Code,Created_Date,Created_By) select '" . $pk . "'," . $vals[ "chemists_name" ] . "," . $vals[ "Chemists_Address1" ] . "," . $vals[ "town_code" ] . "," . $vals[ "Chemists_Phone" ] . ",'','" . $Owndiv . "','','0','" . $sfCode . "','" . date( 'Y-m-d H:i:s' ) . "','Apps'";
    performQuery( $query );
}

function UnListed_Doc_Master( $sfCode, $vals ) {
    $query = "SELECT isNull(max(UnListedDrCode),0)+1 as RwID FROM Mas_UnListedDr";
    $result = performQuery( $query );
    $pk = ( int )$result[ 0 ][ 'RwID' ];
    $query = "insert into Mas_UnListedDr(UnListedDrCode,UnListedDr_Name,UnListedDr_Address1,UnListedDr_Address2, Doc_Special_Code, Doc_Cat_Code,Territory_Code,UnListedDr_Active_Flag,UnListedDr_Sl_No,Division_Code,SLVNo, Doc_QuaCode,Doc_ClsCode,Sf_Code,UnListedDr_Created_Date,Created_By,UnListedDr_PinCode,UnListedDr_Phone, UnListedDr_Mobile,UnListedDr_Email) select '" . $pk . "'," . $vals[ "unlisted_doctor_name" ] . "," . $vals[ "unlisted_doctor_addr1" ] . "," . $vals[ "unlisted_doctor_addr2" ] . "," . $vals[ "unlisted_specialty_code" ] . "," . $vals[ "unlisted_cat_code" ] . "," . $vals[ "town_code" ] . ",0,'" . $pk . "','" . $Owndiv . "','" . $pk . "'," . $vals[ "unlisted_qulifi" ] . "," . $vals[ "unlisted_class" ] . ",'" . $sfCode . "','" . date( 'Y-m-d H:i:s' ) . "','Apps'," . $vals[ "unlisted_doctor_pincode" ] . "," . $vals[ "unlisted_doctor_mobileno" ] . "," . $vals[ "unlisted_doctor_mobileno" ] . "," . $vals[ "unlisted_doctor_email" ] . "";
    performQuery( $query );
}
?>