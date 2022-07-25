<?php

function Order_Management() {
    $data = $GLOBALS[ 'Data' ];
    $DivCode = explode( ",", $_GET[ 'divisionCode' ] . "," );
    $DivisionCode = ( string )$DivCode[ 0 ];
    $OrderDate = date( 'Y-m-d H:i:s' );
    $sql = "select isnull(max(Trans_SlNo),0)+1 sl_no from Trans_Order_Book_Head";
    $tr = performQuery( $sql );
    $orderData = $data[ 0 ][ "Order_Product" ];
    $orderDetail = $data[ 1 ][ "Order_Product_Details" ];
    $sql = "insert into Trans_Order_Book_Head (Sf_Code,Sf_Name,Division_Code,Stockist_Code,Stockist_Name,Mode_of_Order,DHP_Code,DHP_Name,Sub_Div_Code,Order_Date,Order_Month,Order_Year,Entry_Mode,Created_Date,Order_Flag,Order_type) select '" . $_GET[ 'sfCode' ] . "','" . $_GET[ 'sfName' ] . "','$DivisionCode','" . $orderData[ "Stockist_id" ] . "','" . $orderData[ "Stockist_name" ] . "','" . $orderData[ "Selected_mode" ] . "','" . $orderData[ "DHP_Code" ] . "','" . $orderData[ "DHP_Name" ] . "','48','" . $orderData[ "order_date" ] . "','" . $orderData[ "month" ] . "','" . $orderData[ "year" ] . "','Apps','$OrderDate','0','" . $orderData[ "Order_Type" ] . "'";
    performQuery( $sql );
    for ( $j = 0; $j < count( $orderDetail ); $j++ ) {
        $sql = "insert into Trans_Order_Book_Detail (Trans_SlNo,Sf_Code,Product_Code,Product_Name,Pack,Order_Sal_Qty,Order_Free_Qty,Order_Rate,Order_Value,NRV_Value,TotNet_Amt,Division_Code,Order_Sch_Qty,Order_Free_Value,Discount,Remarks,Order_tax,Order_discount) select '" . $tr[ 0 ][ 'sl_no' ] . "','" . $_GET[ 'sfCode' ] . "','" . $orderDetail[ $j ][ "product_code" ] . "','" . $orderDetail[ $j ][ "product_Name" ] . "','','" . $orderDetail[ $j ][ "Product_Order_Qty" ] . "','" . $orderDetail[ $j ][ "Additional_Qty" ] . "','" . $orderDetail[ $j ][ "product_Rate" ] . "','" . $orderDetail[ $j ][ "Order_value" ] . "','" . $orderDetail[ $j ][ "NRV" ] . "','','$DivisionCode','" . $orderDetail[ $j ][ "Scheme_Quantity" ] . "','" . $orderDetail[ $j ][ "FreeQTy_value" ] . "','" . $orderDetail[ $j ][ "product_Discount" ] . "','" . $orderDetail[ $j ][ "feedback" ] . "','" . $orderDetail[ $j ][ "product_Tax" ] . "','" . $orderDetail[ $j ][ "Discount" ] . "'";
        performQuery( $sql );
    }
}
?>