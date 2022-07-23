<?php

function tourplan() {
  global $data;
  $data = json_decode( $_POST[ 'data' ], true );
  $sfCode = ( string )$data[ 'SF' ];
  $Mnth = ( string )$data[ 'Month' ];
  $Yr = ( string )$data[ 'Year' ];
  $SyncType = ( string )$data[ 'Type' ];
  if ( isset( $data[ 'divisionCode' ] ) ) {
    $divi = ( string )str_replace( ",", "", $data[ 'divisionCode' ] );
  } else {
    $sql = "select division_Code from mas_salesforce where sf_code='" . $sfCode . "'";
    $res = performQuery( $sql );
    $divi = ( string )str_replace( ",", "", $res[ 0 ][ 'division_Code' ] );
  }
  $sql = "select COUNT(SFCode) as mRow from Tourplan_detail where SFCode='" . $sfCode . "' and cast(Mnth as int)='" . $Mnth . "' and cast(Yr as int)='" . $Yr . "'";
  $res1 = performQuery( $sql );
  if ( $res1[ 'mRow' ] == 0 ) {
    if ( $SyncType == 'previous' ) {
      holidays_weekly( $sfCode, $divi, 'previous' );
    } else if ( $SyncType == 'current' ) {
      holidays_weekly( $sfCode, $divi, 'current' );
    } else if ( $SyncType == 'next' ) {
      holidays_weekly( $sfCode, $divi, 'next' );
    }
  }
  $query = "select  SFCode,SFName,Div,Mnth,Yr,dayno,Change_Status,isnull(Rejection_Reason,'')Rejection_Reason,convert(varchar,TPDt,20)TPDt,WTCode,WTCode2,WTCode3,WTName,WTName2,WTName3,ClusterCode,ClusterCode2,ClusterCode3,ClusterName,ClusterName2,ClusterName3,ClusterSFs,ClusterSFNms,JWCodes,JWNames,JWCodes2,JWNames2,JWCodes3,JWNames3,Dr_Code,Dr_Name,Dr_two_code,Dr_two_name,Dr_three_code,Dr_three_name,Chem_Code,Chem_Name,Chem_two_code,Chem_two_name,Chem_three_code,Chem_three_name,Stockist_Code,Stockist_Name,Stockist_two_code,Stockist_two_name,Stockist_three_code,Stockist_three_name,Day,Tour_Month,Tour_Year,tpmonth,tpday,DayRemarks,DayRemarks2,DayRemarks3,access,EFlag,FWFlg,FWFlg2,FWFlg3,HQCodes,HQNames,HQCodes2,HQNames2,HQCodes3,HQNames3,submitted_time,Entry_mode, sf_TP_Active_Dt from Tourplan_detail T left  outer join mas_salesforce M  ON T.SFCode= M.SF_Code  where SFCode='" . $sfCode . "' and cast(Mnth as int)='" . $Mnth . "' and cast(Yr as int)='" . $Yr . "' order by cast(dayno as int) ASC";
  $res = performQuery( $query );
  $result = array();
  $resTP = array();
  if ( count( $res ) > 0 ) {
    for ( $il = 0; $il < count( $res ); $il++ ) {
      $sWTCd = explode( "~~~", $res[ $il ][ "WTCode" ] . "~~~" . $res[ $il ][ "WTCode2" ] . "~~~" . $res[ $il ][ "WTCode3" ] );
      $sWTNm = explode( "~~~", $res[ $il ][ "WTName" ] . "~~~" . $res[ $il ][ "WTName2" ] . "~~~" . $res[ $il ][ "WTName3" ] );
      $sPlCd = explode( "~~~", $res[ $il ][ "ClusterCode" ] . "~~~" . $res[ $il ][ "ClusterCode2" ] . "~~~" . $res[ $il ][ "ClusterCode3" ] );
      $sPlNm = explode( "~~~", $res[ $il ][ "ClusterName" ] . "~~~" . $res[ $il ][ "ClusterName2" ] . "~~~" . $res[ $il ][ "ClusterName3" ] );
      $sHQCd = explode( "~~~", $res[ $il ][ "HQCodes" ] . "~~~" . $res[ $il ][ "HQCodes2" ] . "~~~" . $res[ $il ][ "HQCodes3" ] );
      $sHQNm = explode( "~~~", $res[ $il ][ "HQNames" ] . "~~~" . $res[ $il ][ "HQNames2" ] . "~~~" . $res[ $il ][ "HQNames3" ] );
      $sJWCd = explode( "~~~", $res[ $il ][ "JWCodes" ] . "~~~" . $res[ $il ][ "JWCodes2" ] . "~~~" . $res[ $il ][ "JWCodes3" ] );
      $sJWNm = explode( "~~~", $res[ $il ][ "JWNames" ] . "~~~" . $res[ $il ][ "JWNames2" ] . "~~~" . $res[ $il ][ "JWNames3" ] );
      $sDRCd = explode( "~~~", $res[ $il ][ "Dr_Code" ] . "~~~" . $res[ $il ][ "Dr_two_code" ] . "~~~" . $res[ $il ][ "Dr_three_code" ] );
      $sDRNm = explode( "~~~", $res[ $il ][ "Dr_Name" ] . "~~~" . $res[ $il ][ "Dr_two_name" ] . "~~~" . $res[ $il ][ "Dr_three_name" ] );
      $sCHCd = explode( "~~~", $res[ $il ][ "Chem_Code" ] . "~~~" . $res[ $il ][ "Chem_two_code" ] . "~~~" . $res[ $il ][ "Chem_three_code" ] );
      $sCHNm = explode( "~~~", $res[ $il ][ "Chem_Name" ] . "~~~" . $res[ $il ][ "Chem_two_name" ] . "~~~" . $res[ $il ][ "Chem_three_name" ] );
      $sSTCd = explode( "~~~", $res[ $il ][ "Stockist_Code" ] . "~~~" . $res[ $il ][ "Stockist_two_code" ] . "~~~" . $res[ $il ][ "Stockist_three_code" ] );
      $sSTNm = explode( "~~~", $res[ $il ][ "Stockist_Name" ] . "~~~" . $res[ $il ][ "Stockist_two_name" ] . "~~~" . $res[ $il ][ "Stockist_three_name" ] );
      $sRmks = explode( "~~~", $res[ $il ][ "DayRemarks" ] . "~~~" . $res[ $il ][ "DayRemarks2" ] . "~~~" . $res[ $il ][ "DayRemarks3" ] );
      $FWFlg = explode( "~~~", $res[ $il ][ "FWFlg" ] . "~~~" . $res[ $il ][ "FWFlg2" ] . "~~~" . $res[ $il ][ "FWFlg3" ] );
      $obj_code = explode( "~~~", $res[ $il ][ "Objcode1" ] . "~~~" . $res[ $il ][ "Objcode2" ] . "~~~" . $res[ $il ][ "Objcode3" ] );
      $obj_name = explode( "~~~", $res[ $il ][ "ObjName1" ] . "~~~" . $res[ $il ][ "ObjName2" ] . "~~~" . $res[ $il ][ "ObjName3" ] );
      $dypl = array();
      for ( $ij = 0; $ij < count( $sWTCd ); $ij++ ) {
        if ( $sWTCd[ $ij ] != "" && $sWTCd[ $ij ] != "0" ) {
          array_push( $dypl, array(
            'ClusterCode' => $sPlCd[ $ij ],
            'ClusterName' => $sPlNm[ $ij ],
            'ClusterSFNms' => $sJWNm[ $ij ],
            'ClusterSFs' => $sJWCd[ $ij ],
            'FWFlg' => $FWFlg[ $ij ],
            'DayRemarks' => $sRmks[ $ij ],
            'HQCodes' => $sHQCd[ $ij ],
            'HQNames' => $sHQNm[ $ij ],
            'JWCodes' => $sJWCd[ $ij ],
            'JWNames' => $sJWNm[ $ij ],
            'Dr_Code' => $sDRCd[ $ij ],
            'Dr_Name' => $sDRNm[ $ij ],
            'Chem_Code' => $sCHCd[ $ij ],
            'Chem_Name' => $sCHNm[ $ij ],
            'Stck_Code' => $sSTCd[ $ij ],
            'Stck_Name' => $sSTNm[ $ij ],
            'WTCode' => $sWTCd[ $ij ],
            'WTName' => $sWTNm[ $ij ],
            'ObjectiveCode' => $obj_code[ $ij ],
            'ObjectiveName' => $obj_name[ $ij ] ) );
        }
      }
      array_push( $resTP, array( 'DayPlan' => $dypl, 'EFlag' => $res[ $il ][ "EFlag" ], 'TPDt' => $res[ $il ][ "TPDt" ], 'access' => $res[ $il ][ "access" ], 'Day' => $res[ $il ][ "Day" ], 'Tour_Month' => $res[ $il ][ "Tour_Month" ], 'Tour_Year' => $res[ $il ][ "Tour_Year" ], 'tpmonth' => $res[ $il ][ "tpmonth" ], 'tpday' => $res[ $il ][ "tpday" ],
        'dayno' => $res[ $il ][ "dayno" ] ) );
    }
    array_push( $result, array( 'SFCode' => $sfCode, 'SFName' => $res[ 0 ][ "SFName" ], 'DivCode' => $res[ 0 ][ "Div" ], 'status' => $res[ 0 ][ "Change_Status" ], 'TPDatas' => $resTP, 'TPFlag' => '0', 'TPMonth' => $res[ 0 ][ "Mnth" ], 'TPYear' => $res[ 0 ][ "Yr" ], 'Reject_reason' => $res[ 0 ][ "Rejection_Reason" ], 'joining_date' => $res[ 0 ][ "sf_TP_Active_Dt" ] ) );
  }
  return outputJSON( $result );
}
?>