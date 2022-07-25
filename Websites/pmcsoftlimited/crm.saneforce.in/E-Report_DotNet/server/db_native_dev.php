<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
session_start();
$URL_BASE = "/";
date_default_timezone_set("Asia/Kolkata");
 
include "dbConn_dev.php";
include "utils3.php";

function send_fcm_notify($reg_id, $message,$sf_name) {
    // define("GOOGLE_API_KEY", "AAAAl-mXc5A:APA91bGw8j05oyRFT5n0Ek_jIvCJD2kjl4oSV7ysWL1IBOjmXdlEQcli-X2xSnX9faoHu2jUjTH-QlQF5jaSSuxLRz9y9gZGad1erYqS3S_mTGJiQupuL8KguHsI201yychEgtyXGDwQ");
    define("GOOGLE_API_KEY", "AAAA72Fk1cA:APA91bFCX24_-3-x6qKu5bHHaL3THqXSPlxwd-847vBm1eFdF0lFpeNGF4OtEfbp3Rms6dtJ38VGniX4vM3RHi-E5NxpyO_MAgYRjTtoZ5swG-5x849BW8QKb5MzkbJU0w6Z6z6Lpite");
    define("GOOGLE_FCM_URL", "https://fcm.googleapis.com/fcm/send"); 
    $fields = array(
        'registration_ids' => array($reg_id),
        'notification' => array("body" => $message, "title" => $sf_name),'priority' => 'high');
 
    $headers = array(
        'Authorization: key=' . GOOGLE_API_KEY,
        'Content-Type: application/json'
    );


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_FCM_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Problem occurred: ' . curl_error($ch));
    }

    curl_close($ch);
    //echo $result;
} 
function send_notification_body($sfcode,$msg,$type)
{
		$sql = "select a.Sf_Name name,b.app_device_id reg_id from Mas_Salesforce a left JOIN Access_table b ON a.Sf_Code = b.sf_code where a.sf_code='$sfcode'"; 
            $device = performQuery($sql);  
            $sf_name = $device[0]['name'];
            $reg_id = $device[0]['reg_id'];
			$message = 'Hi '.$device[0]['name'] . ' ' .$msg;
		send_fcm_notify($reg_id, $message,'SANSFE');
}
function getTourPlan(){
    global $data;
 	$data=json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
    $Mnth=(string) $data['Month'];
    $Yr=(string) $data['Year'];
    $SyncType=(string) $data['Type'];
	if(isset($data['divisionCode'])){
		$divi=(string) str_replace(",", "", $data['divisionCode']);
	}else{
		$sql="select division_Code from mas_salesforce where sf_code='".$sfCode."'";
		$res=performQuery($sql);
		$divi = (string) str_replace(",", "", $res[0]['division_Code']);
	}
	$sql="select * from Tourplan_detail where SFCode='".$sfCode."' and cast(Mnth as int)='".$Mnth."' and cast(Yr as int)='".$Yr."'";
	$res1=performQuery($sql);
	if(count($res1)==0){
		if($SyncType=='previous'){
			holidays_weekly($sfCode,$divi,'previous');
		}else if($SyncType=='current'){
			holidays_weekly($sfCode,$divi,'current');
		}else if($SyncType=='next'){
			holidays_weekly($sfCode,$divi,'next');
		}
	}
	$query="select  SFCode,SFName,Div,Mnth,Yr,dayno,Change_Status,isnull(Rejection_Reason,'')Rejection_Reason,convert(varchar,TPDt,20)TPDt,WTCode,WTCode2,WTCode3,WTName,WTName2,WTName3,ClusterCode,ClusterCode2,ClusterCode3,ClusterName,ClusterName2,ClusterName3,ClusterSFs,ClusterSFNms,JWCodes,JWNames,JWCodes2,JWNames2,JWCodes3,JWNames3,Dr_Code,Dr_Name,Dr_two_code,Dr_two_name,Dr_three_code,Dr_three_name,Chem_Code,Chem_Name,Chem_two_code,Chem_two_name,Chem_three_code,Chem_three_name,Stockist_Code,Stockist_Name,Stockist_two_code,Stockist_two_name,Stockist_three_code,Stockist_three_name,Day,Tour_Month,Tour_Year,tpmonth,tpday,DayRemarks,DayRemarks2,DayRemarks3,access,EFlag,FWFlg,FWFlg2,FWFlg3,HQCodes,HQNames,HQCodes2,HQNames2,HQCodes3,HQNames3,submitted_time,Entry_mode, sf_TP_Active_Dt from Tourplan_detail T left  outer join mas_salesforce M  ON T.SFCode= M.SF_Code  where SFCode='".$sfCode."' and cast(Mnth as int)='".$Mnth."' and cast(Yr as int)='".$Yr."' order by cast(dayno as int) ASC";
	//echo $query;
    $res=performQuery($query);
	$result=array();
	$resTP=array();
	if (count($res)>0){
		for ($il=0;$il<count($res);$il++){
			$sWTCd=explode("~~~",$res[$il]["WTCode"]."~~~".$res[$il]["WTCode2"]."~~~".$res[$il]["WTCode3"]);
			$sWTNm=explode("~~~",$res[$il]["WTName"]."~~~".$res[$il]["WTName2"]."~~~".$res[$il]["WTName3"]);
			$sPlCd=explode("~~~",$res[$il]["ClusterCode"]."~~~".$res[$il]["ClusterCode2"]."~~~".$res[$il]["ClusterCode3"]);
			$sPlNm=explode("~~~",$res[$il]["ClusterName"]."~~~".$res[$il]["ClusterName2"]."~~~".$res[$il]["ClusterName3"]);
			$sHQCd=explode("~~~",$res[$il]["HQCodes"]."~~~".$res[$il]["HQCodes2"]."~~~".$res[$il]["HQCodes3"]);
			$sHQNm=explode("~~~",$res[$il]["HQNames"]."~~~".$res[$il]["HQNames2"]."~~~".$res[$il]["HQNames3"]);
			$sJWCd=explode("~~~",$res[$il]["JWCodes"]."~~~".$res[$il]["JWCodes2"]."~~~".$res[$il]["JWCodes3"]);
			$sJWNm=explode("~~~",$res[$il]["JWNames"]."~~~".$res[$il]["JWNames2"]."~~~".$res[$il]["JWNames3"]);
			$sDRCd=explode("~~~",$res[$il]["Dr_Code"]."~~~".$res[$il]["Dr_two_code"]."~~~".$res[$il]["Dr_three_code"]);
			$sDRNm=explode("~~~",$res[$il]["Dr_Name"]."~~~".$res[$il]["Dr_two_name"]."~~~".$res[$il]["Dr_three_name"]);
			$sCHCd=explode("~~~",$res[$il]["Chem_Code"]."~~~".$res[$il]["Chem_two_code"]."~~~".$res[$il]["Chem_three_code"]);
			$sCHNm=explode("~~~",$res[$il]["Chem_Name"]."~~~".$res[$il]["Chem_two_name"]."~~~".$res[$il]["Chem_three_name"]);
			$sSTCd=explode("~~~",$res[$il]["Stockist_Code"]."~~~".$res[$il]["Stockist_two_code"]."~~~".$res[$il]["Stockist_three_code"]);
			$sSTNm=explode("~~~",$res[$il]["Stockist_Name"]."~~~".$res[$il]["Stockist_two_name"]."~~~".$res[$il]["Stockist_three_name"]);
			$sRmks=explode("~~~",$res[$il]["DayRemarks"]."~~~".$res[$il]["DayRemarks2"]."~~~".$res[$il]["DayRemarks3"]);
			$FWFlg=explode("~~~",$res[$il]["FWFlg"]."~~~".$res[$il]["FWFlg2"]."~~~".$res[$il]["FWFlg3"]);
			
			$obj_code=explode("~~~",$res[$il]["Objcode1"]."~~~".$res[$il]["Objcode2"]."~~~".$res[$il]["Objcode3"]);
			$obj_name=explode("~~~",$res[$il]["ObjName1"]."~~~".$res[$il]["ObjName2"]."~~~".$res[$il]["ObjName3"]);
			$dypl=array();
			for($ij=0;$ij<count($sWTCd);$ij++){
				if($sWTCd[$ij]!="" && $sWTCd[$ij]!="0"){
					array_push($dypl,array(
					'ClusterCode' => $sPlCd[$ij],
					'ClusterName' =>$sPlNm[$ij],
					'ClusterSFNms' => $sJWNm[$ij],
					'ClusterSFs' => $sJWCd[$ij],
					'FWFlg' => $FWFlg[$ij],
					'DayRemarks' => $sRmks[$ij],
					'HQCodes' => $sHQCd[$ij],
					'HQNames' => $sHQNm[$ij],
					'JWCodes' => $sJWCd[$ij],
					'JWNames' => $sJWNm[$ij],
					'Dr_Code' => $sDRCd[$ij],
					'Dr_Name' => $sDRNm[$ij],
					'Chem_Code' => $sCHCd[$ij],
					'Chem_Name' => $sCHNm[$ij],
					'Stck_Code' => $sSTCd[$ij],
					'Stck_Name' => $sSTNm[$ij], 
					'WTCode' => $sWTCd[$ij],
					'WTName' => $sWTNm[$ij],
					'ObjectiveCode' => $obj_code[$ij],
					'ObjectiveName' => $obj_name[$ij]));
				}
			}
			array_push($resTP,array('DayPlan' => $dypl,'EFlag' =>$res[$il]["EFlag"],'TPDt' =>$res[$il]["TPDt"],'access' =>$res[$il]["access"],'Day' =>$res[$il]["Day"],'Tour_Month' =>$res[$il]["Tour_Month"],'Tour_Year' =>$res[$il]["Tour_Year"],'tpmonth' =>$res[$il]["tpmonth"],'tpday' =>$res[$il]["tpday"],
			'dayno' =>$res[$il]["dayno"]));
		}
		array_push($result,array('SFCode' => $sfCode,'SFName' =>$res[0]["SFName"],'DivCode' =>$res[0]["Div"],'status' =>$res[0]["Change_Status"],'TPDatas' =>$resTP,'TPFlag' => '0','TPMonth' => $res[0]["Mnth"],'TPYear' => $res[0]["Yr"],'Reject_reason' => $res[0]["Rejection_Reason"],'joining_date' =>$res[0]["sf_TP_Active_Dt"]));
    }
    return $result;
}
function holidays_weekly($sfCode,$Owndiv,$tourmonth){
//echo $sfCode;echo $tourmonth;echo $Owndiv; 
//$cMonth=date('m', strtotime('-1 month'));
//$cYear=date('Y', strtotime('-1 month'));
	$cMonth = date('m');
	$cYear = date('Y');
	if($tourmonth=='next'){
		if($cMonth==12){
			$cMonth=1;
			$cYear=$cYear+1;
		}else{
			$cMonth=$cMonth+1;
		}
	}
	if($tourmonth=='previous'){
		if($cMonth==1){
			$cMonth=12;
			$cYear=$cYear-1;
		}else{
			$cMonth=$cMonth-1;
		}
	}
	$sql = "exec getHolidays '$sfCode','$Owndiv','$cMonth','$cYear'";
	$holidays = performQuery($sql);
	for($i=0;$i<count($holidays);$i++){
		$tourDate=$holidays[$i]['Holiday_Date'];
		$remarks=$holidays[$i]['Holiday_Name'];
		$sql = "exec postHolidayWeeklyof_tourplan '$sfCode','$Owndiv','$tourDate','$remarks','H'";
		performQuery($sql);
	}
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	$maxday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	$startday = $thismonth['wday'];
	for ($i=0; $i<($maxday+$startday); $i++){
		if($i < $startday){
			echo "";
		}elseif(date("N F", mktime(0,0,0,$cMonth,($i - $startday + 1),$cYear)) == 7){ 
			$tourDate=date('Y-m-d', mktime(0,0,0,$cMonth,($i - $startday + 1),$cYear));
			$sql = "exec postHolidayWeeklyof_tourplan '$sfCode','$Owndiv','$tourDate','','W'";
			performQuery($sql);	
		}
	}
	if($tourmonth=='next')
		$query = "select  * from vwTourPlan where SF_Code='$sfCode' and worktype_code!='0'";
	if($tourmonth=='current')
		$query = "select  * from vwTourPlan_current where SF_Code='$sfCode'  and worktype_code!='0'";
	if($tourmonth=='previous')
		$query = "select  * from vwTourPlan_previous where SF_Code='$sfCode'  and worktype_code!='0'";
	return performQuery($query);
}
function getConversation(){
    global $data;
	$data = json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
    $msgDt=(string) $data['MsgDt'];
    $query="exec iOS_GetMsgConversation '".$sfCode."','".$msgDt."'";
    $result=performQuery($query);
    $query="exec iOS_GetMsgConversationFiles '".$sfCode."','".$msgDt."'";
    $result1=performQuery($query);
	for($il=0;$il<count($result);$il++){
		$msgId=$result[$il]["Msg_Id"];
		$rArry=array_filter($result1, function($item) use ($msgId){
				return ($item["Msg_Id"]===$msgId);
			});
			$nAry=array();
			foreach($rArry as $key => $value){
				$nAry[]=$rArry[$key];
			}
			$result[$il]["Files"]=$nAry;
	}
    return $result;
}
function svConversation(){
    global $data;
	$data = json_decode($_POST['data'], true);
    $sXML="<Root>";
    $sXML=$sXML."<Msg SF=\"".$data['SF']."\" Dt=\"".$data["MsgDt"]."\" To=\"".$data["MsgTo"]."\" ToName=\"".$data["MsgToName"]."\" mTxt=\"".$data["MsgText"]."\" mPID=\"".$data["MsgParent"]."\" />";
	$sXML=$sXML."</Root>";
    $sfcode =$data['MsgTo'];
	$msg=$data["MsgText"];
	$sfcodefm=$data['SF'];
    $query="exec iOS_SvMsgConversation '".$sXML."'";
    $result=performQuery($query);
	send_push_message($sfcode,$msg,$sfcodefm,0);
	return($result);
}

function send_push_message($sfcode,$msg,$sfcodefm,$type){
	$sql = "select a.Sf_Name name,b.DeviceRegId reg_id from Mas_Salesforce a left JOIN Access_table b ON a.Sf_Code = b.sf_code where a.sf_code='$sfcode'"; 
    $device = performQuery($sql);  
    $sql1 = "select Sf_Code code,Sf_Name name from Mas_Salesforce where sf_code='$sfcodefm'"; 
    $device_fm=performQuery($sql1); 
    $sf_name = $device[0]['name'];
    $reg_id = $device[0]['reg_id'];
	// $message = 'Hi, '.$device_fm[0]['name'] . ' ' .$msg;
	$message = 'Hi, '.'~'.$device_fm[0]['code'].'~'.$device_fm[0]['name'].'~'.' has send you a Message'.'~'.$msg;
	$sf_name_fm = $device_fm[0]['name'] ;
	send_fcm_notify($reg_id, $message,'SANSFE');
		
}

function getActivity_date(){
	$result=array();
	return $result;
}
function getEdit_Activity($sf,$hd,$det){
	
	 $result=array();	
	
	$qury="select Group_id aa from DCR_Detail_Activity  where sf_code='".$sf."' and Trans_Main_Sl_No='".$hd."' and Trans_Detail_Slno='".$det."' group by Group_id"; 
	$restt=performQuery($qury);
	//echo count($qury);
	if(count($restt)>0)
	{ 
		for($ilk=0;$ilk<count($restt);$ilk++)
		{
			$query="select a.Trans_SlNo,a.Group_id,a.SF_Code,'1' Status,a.SF_Emp_ID,a.Division_Code,a.Activity_SlNo,a.Control_Id,a.Creation_Id,a.Creation_Code,a.Creation_Name,cast(convert(varchar,a.Activity_Date,101) as datetime) Activity_Date,a.Trans_Main_Sl_No,a.Trans_Detail_Slno,a.Trans_Detail_Info_Type,a.Trans_Detail_Info_Code,b.Activity_Name from DCR_Detail_Activity a left outer join Mas_Activity b on a.Activity_SlNo=b.Activity_SlNo where a.Group_id ='".$restt[$ilk]['aa']."'";
			//echo $query;
			$rest=performQuery($query);
			
					if (count($rest)>0) 
					{
						$Tpdypl=array();
						for ($il=0;$il<count($rest);$il++)
						{
							array_push($Tpdypl,array(
							'Sf_Code' =>$rest[$il]["SF_Code"],
							'Ctrl_id' =>$rest[$il]["Control_Id"],
							'Creation_id' =>$rest[$il]["Creation_Id"],
							'Creation_Code' =>$rest[$il]["Creation_Code"],
							'Creation_Name' =>$rest[$il]["Creation_Name"]));
						}	
						array_push($result,array('Main_id' =>$rest[0]["Activity_SlNo"],'Main_Name' =>$rest[0]["Activity_Name"],'DCR_Date' =>$rest[0]["Activity_Date"],'Sf_code' =>$rest[0]["SF_Code"],'Group_id' =>$rest[0]["Group_id"],'Status' =>$rest[0]["Status"],'Activity_data' => $Tpdypl));
						
					}
		}
	}
	return $result;
}
function actionLogin() {
    global $URL_BASE;
 $data = json_decode($_POST['data'], true);

    $data = json_decode($_POST['data'], true);
    $username = (string) $data['name'];
    $password = (string) $data['password'];
 
    $DeviceRegId = (string) $data['AppDeviceRegId'];
	$version = $data['versionNo'];
	$login_mode = $data['mode'];
	$AppDeviceRegId=$data['device_id'];
	$Device_version ='';
	$Device_name = '';
	if((isset($data['Device_name'])) && (isset($data['Device_version']))){
		$Device_version =$data['Device_version'];
		$Device_name = $data['Device_name'];
	}
/*	if($AppDeviceRegId==null){
	        $respon['success'] = false;
	        $respon['msg'] = "Please Enable Permission into your mobile setting like goto settings->apps->sansfa app->permission-> enable all the elements";
	        return outputJSON($respon);
	die;
	}*/
    $query = "exec LoginAPP '$username','$password'";
    global $conn;
    $arr;
    $res = sqlsrv_query($conn, $query);
    if ($res) {
        $result = array();
        while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $result[] = $row;
        }

        $arr = $result;
    }
    //sqlsrv_close();
    $respon = array();
    $count = count($arr);
	
    if ($count == 1) {
        $respon['success'] = true;
        $respon['sfCode'] = $arr[0]['SF_Code'];
        $sfName = utf8_encode(trim(preg_replace("/[\r\n]+/", " ", $arr[0]['SF_Name'])));
        $respon['sfName'] = $sfName;
	//	$respon['sftype'] = $sftype;
        $respon['divisionCode'] = $arr[0]['Division_Code'];
        $respon['call_report'] = $arr[0]['call_report'];
        $respon['desigCode'] = $arr[0]['desig_Code'];
        $respon['HlfNeed'] = $arr[0]['MGRHlfDy'];
        // if (!empty($DeviceRegId)) {
            $sql = "update Access_Table set  DeviceRegId='$DeviceRegId' where sf_code='" . $arr[0]['SF_Code'] . "'";
            performQuery($sql);
        // }
        if ($arr[0]['desig_Code'] == "MR") {
            $query = "Select count(SF_Code) Cnt from Salesforce_Master where sf_Tp_Reporting='" . $arr[0]['SF_Code'] . "'";
            $dsgc = performQuery($query);
            if ($dsgc[0]['Cnt'] > 0)
                $respon['desigCode'] = "AM";
            else
                $respon['HlfNeed'] = $arr[0]['MRHlfDy'];
        }
		$dat=date('Y-m-d');
		$ty = date('Y-m-d H:i:s');
		$sql1 ="insert into version_ctrl select '".$arr[0]['SF_Code']."','$dat','$ty','$version','$login_mode','$Device_version','$Device_name'";
		performQuery($sql1);
		// $sql="select * from TP_Attendance_App where Sf_Code='" . $arr[0]['SF_Code'] . "' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='$dat'";
		// $attendance=performQuery($sql);
		// if(count($attendance)==0)
		// $respon['attendanceView'] = 0;
		// else
		$respon['attendanceView'] = 1;

        $respon['AppTyp'] = 0;
		$respon['Attendance'] = $arr[0]['Attendance'];
        $respon['TBase'] = $arr[0]['TBase'];
		$respon['UserN'] = $username;
		$respon['Pass'] = $password;
        $respon['GeoChk'] = $arr[0]['GeoChk'];
        $respon['ChmNeed'] = $arr[0]['ChmNeed'];
        $respon['StkNeed'] = $arr[0]['StkNeed'];
        $respon['UNLNeed'] = $arr[0]['UNLNeed'];
        $respon['DPNeed'] = $arr[0]['DPNeed'];
        $respon['DINeed'] = $arr[0]['DINeed'];
        $respon['CPNeed'] = $arr[0]['CPNeed'];
        $respon['CINeed'] = $arr[0]['CINeed'];
        $respon['SPNeed'] = $arr[0]['SPNeed'];
        $respon['SINeed'] = $arr[0]['SINeed'];
        $respon['NPNeed'] = $arr[0]['NPNeed'];
        $respon['NINeed'] = $arr[0]['NINeed'];
        $respon['DrCap'] = $arr[0]['DrCap'];
        $respon['ChmCap'] = $arr[0]['ChmCap'];
        $respon['StkCap'] = $arr[0]['StkCap'];
        $respon['NLCap'] = $arr[0]['NLCap'];
        $respon['DFNeed'] = $arr[0]['DFNeed'];
        $respon['CFNeed'] = $arr[0]['CFNeed'];
		$respon['SFNeed'] = $arr[0]['SFNeed'];
		$respon['RcpaNd'] = $arr[0]['RcpaNd'];
   		$respon['NFNeed'] = $arr[0]['NFNeed'];
        $respon['Catneed'] = $arr[0]['Catneed'];
		$respon['DisRad'] = $arr[0]['DisRad'];
		$respon['VstNd'] = $arr[0]['VstNd'];
        $respon['MsdEntry'] = $arr[0]['MsdEntry'];
		$respon['CHEBase'] = $arr[0]['CHEBase'];
		$respon['chm_ad_qty'] = $arr[0]['chm_ad_qty'];
		$respon['Campneed'] = $arr[0]['Campneed'];
		$respon['GEOTagNeed'] = $arr[0]['GEOTagNeed'];
		$respon['Approveneed'] = $arr[0]['Approveneed'];
		$respon['Expenseneed'] = $arr[0]['Expenseneed'];
		$respon['GEOTagNeedche'] = $arr[0]['GEOTagNeedche'];
		$respon['GEOTagNeedstock'] = $arr[0]['GEOTagNeedstock'];
	    $respon['MCLDet'] = $arr[0]['MCLDet'];
		$respon['username'] = $arr[0]['UsrDfd_UserName'];
		$respon['call_report_from_date'] = $arr[0]['call_report_from_date'];
		$respon['call_report_to_date'] = $arr[0]['call_report_to_date'];

 		$respon['DrRxNd'] = $arr[0]['DrRxNd'];
		$respon['DrSampNd'] = $arr[0]['DrSampNd'];
        $respon['DRxCap'] = $arr[0]['DrRxQCap'];
        $respon['DSmpCap'] = $arr[0]['DrSmpQCap'];
        //$respon['CQCap'] = $arr[0]['ChmQCap'];
        $respon['CQCap'] = 'Rx';
        $respon['SQCap'] = $arr[0]['StkQCap'];
        $respon['NRxCap'] = $arr[0]['NLRxQCap'];
        $respon['NSmpCap'] = $arr[0]['NLSmpQCap'];
        $respon['SFStat'] = $arr[0]['SFStat'];
		$respon['sftype'] = $arr[0]['sf_type'];
        $respon['days'] = $arr[0]['days'];
        $respon['No_of_TP_View'] = $arr[0]['No_of_TP_View'];
        $respon['SFTPDate'] = $arr[0]['SFTPDate'];
 		$respon['circular'] = $arr[0]['circular'];
		$respon['doctor_dobdow'] = $arr[0]['doctor_dobdow'];
		$respon['Doc_Pob_Mandatory_Need'] = $arr[0]['Doc_Pob_Mandatory_Need'];
		$respon['Chm_Pob_Mandatory_Need'] = $arr[0]['Chm_Pob_Mandatory_Need'];
		$respon['product_pob_need_msg'] = $arr[0]['product_pob_need_msg'];
		$respon['product_pob_need'] = $arr[0]['product_pob_need'];
		$respon['multiple_doc_need'] = $arr[0]['multiple_doc_need'];
		$respon['mailneed'] = $arr[0]['mailneed'];
		$respon['TPDCR_Deviation_Appr_Status'] = $arr[0]['TPDCR_Deviation_Appr_Status'];
		$respon['TPDCR_Deviation'] = $arr[0]['TPDCR_Deviation'];
		$respon['TPDCR_MGRAppr'] = $arr[0]['TPDCR_MGRAppr'];
		$respon['NextVst'] = $arr[0]['NextVst'];
		$respon['NextVst_Mandatory_Need'] = $arr[0]['NextVst_Mandatory_Need'];
        $respon['TP_Mandatory_Need'] = $arr[0]['TP_Mandatory_Need'];
        $respon['Appr_Mandatory_Need'] = $arr[0]['Appr_Mandatory_Need'];
        $respon['RCPAQty_Need'] = $arr[0]['RCPAQty_Need'];
        $respon['Prod_Stk_Need'] = $arr[0]['Prod_Stk_Need'];
		$respon['Tp_Start_Date'] = $arr[0]['Tp_Start_Date'];
		$respon['Tp_End_Date'] = $arr[0]['Tp_End_Date'];
		$respon['currentDay'] = $arr[0]['currentDay'];
		$respon['dayplan_tp_based'] = $arr[0]['dayplan_tp_based'];
		$respon['Doc_Pob_Need'] = $arr[0]['Doc_Pob_Need'];
		$respon['Chm_Pob_Need'] = $arr[0]['Chm_Pob_Need'];
		$respon['Stk_Pob_Need'] = $arr[0]['Stk_Pob_Need'];
		$respon['Ul_Pob_Need'] = $arr[0]['Ul_Pob_Need'];
		$respon['Stk_Pob_Mandatory_Need'] = $arr[0]['Stk_Pob_Mandatory_Need'];
		$respon['Ul_Pob_Mandatory_Need'] = $arr[0]['Ul_Pob_Mandatory_Need'];
		$respon['Doc_jointwork_Need'] = $arr[0]['Doc_jointwork_Need'];
		$respon['Chm_jointwork_Need'] = $arr[0]['Chm_jointwork_Need'];
		$respon['Stk_jointwork_Need'] = $arr[0]['Stk_jointwork_Need'];
		$respon['Ul_jointwork_Need'] = $arr[0]['Ul_jointwork_Need'];
		$respon['Doc_jointwork_Mandatory_Need'] = $arr[0]['Doc_jointwork_Mandatory_Need'];
		$respon['Chm_jointwork_Mandatory_Need'] = $arr[0]['Chm_jointwork_Mandatory_Need'];
		$respon['Stk_jointwork_Mandatory_Need'] = $arr[0]['Stk_jointwork_Mandatory_Need'];
		$respon['Ul_jointwork_Mandatory_Need'] = $arr[0]['Ul_jointwork_Mandatory_Need'];
		$respon['Doc_Product_caption'] = $arr[0]['Doc_Product_caption'];
		$respon['Chm_Product_caption'] = $arr[0]['Chm_Product_caption'];
		$respon['Stk_Product_caption'] = $arr[0]['Stk_Product_caption'];
		$respon['Ul_Product_caption'] = $arr[0]['Ul_Product_caption'];
		$respon['Doc_Input_caption'] = $arr[0]['Doc_Input_caption'];
		$respon['Chm_Input_caption'] = $arr[0]['Chm_Input_caption'];
		$respon['Stk_Input_caption'] = $arr[0]['Stk_Input_caption'];
		$respon['Ul_Input_caption'] = $arr[0]['Ul_Input_caption'];   
		$respon['prdfdback'] = $arr[0]['prdfdback'];
        $respon['Sep_RcpaNd'] =$arr[0]['Sep_RcpaNd'];
		$respon['tp_need'] = $arr[0]['tp_need'];        
	    $respon['tp_new'] = $arr[0]['tp_new'];
		$respon['CmpgnNeed'] = $arr[0]['CmpgnNeed'];
		$respon['quiz_need'] =$arr[0]['quiz_need'];
        $respon['chm_ad_qty'] =$arr[0]['chm_ad_qty'];
        $respon['Rcpa_Competitor_extra'] =$arr[0]['Rcpa_Competitor_extra'];
		$respon['stp'] =$arr[0]['stp'];
$respon['Location_track'] =$arr[0]['Location_track'];
$respon['tracking_time'] =$arr[0]['tracking_time'];
$respon['Order_management'] = $arr[0]['Order_management'];
$respon['Order_caption'] = $arr[0]['Order_caption'];
$respon['Primary_order_caption'] = $arr[0]['Primary_order_caption'];
$respon['Secondary_order_caption'] = $arr[0]['Secondary_order_caption'];
$respon['Primary_order'] = $arr[0]['Primary_order'];
$respon['Secondary_order'] = $arr[0]['Secondary_order'];
$respon['Gst_option'] = $arr[0]['Gst_option'];
$respon['cip_need'] = $arr[0]['cip_need'];
$respon['CIP_PNeed'] =$arr[0]['CIP_PNeed'];
	$respon['CIP_INeed'] =$arr[0]['CIP_INeed'];
	$respon['CIP_FNeed'] =$arr[0]['CIP_FNeed'];
	$respon['CIP_ENeed'] =$arr[0]['CIP_ENeed'];
	$respon['CIP_QNeed'] =$arr[0]['CIP_QNeed'];
	$respon['CIP_jointwork_Need'] =$arr[0]['CIP_jointwork_Need'];
	$respon['CIP_Caption'] =$arr[0]['CIP_Caption'];
	$respon['wrk_area_Name'] =$arr[0]['wrk_area_Name'];
	$respon['hosp_need'] =$arr[0]['hosp_need'];
	$respon['hosp_caption'] =$arr[0]['hosp_caption'];
	$respon['Taxname_caption'] =$arr[0]['Taxname_caption'];
	$respon['Product_Rate_Editable'] =$arr[0]['Product_Rate_Editable'];
	$respon['secondary_order_discount'] =$arr[0]['secondary_order_discount'];
$respon['DrPrdMd'] =$arr[0]['DrPrdMd'];
$respon['DrInpMd'] =$arr[0]['DrInpMd'];
$respon['GeoTagNeedcip'] =$arr[0]['GeoTagNeedcip'];
$respon['misc_expense_need'] =$arr[0]['misc_expense_need']; 
$respon['dashboard'] =$arr[0]['dashboard'];
$respon['SurveyNd'] =$arr[0]['SurveyNd'];
$respon['ActivityNd'] =$arr[0]['ActivityNd'];
$respon['past_leave_post'] =$arr[0]['past_leave_post'];
$respon['SrtNd'] =$arr[0]['SrtNd'];
$respon['DS_name'] =$arr[0]['DS_name'];
$respon['GEOTagNeedunlst'] =$arr[0]['GEOTagNeedunlst'];
$respon['chmsamQty_need'] = $arr[0]['chmsamQty_need'];
$respon['ChmSmpCap'] = $arr[0]['ChmSmpCap'];
$respon['quote_Text'] = $arr[0]['quote_Text']; 
$respon['geoTagImg'] = $arr[0]['geoTagImg']; 
$respon['TPbasedDCR'] = $arr[0]['TPbasedDCR'];
$respon['rcpaextra'] = $arr[0]['rcpaextra'];
$respon['Target_report_md'] = $arr[0]['Target_report_md'];
$respon['RCPA_unit_nd'] = $arr[0]['RCPA_unit_nd'];
$respon['Chm_RCPA_Need'] = $arr[0]['Chm_RCPA_Need'];
$respon['DrRCPA_competitor_Need'] = $arr[0]['DrRCPA_competitor_Need'];
$respon['ChmRCPA_competitor_Need'] = $arr[0]['ChmRCPA_competitor_Need'];
$respon['Currentday_TPplanned'] = $arr[0]['Currentday_TPplanned'];
$respon['Doc_cluster_based'] =$arr[0]['Doc_cluster_based'];
$respon['Chm_cluster_based'] =$arr[0]['Chm_cluster_based'];
$respon['Stk_cluster_based'] =$arr[0]['Stk_cluster_based'];
$respon['UlDoc_cluster_based'] =$arr[0]['UlDoc_cluster_based'];
$respon['CustSrtNd'] =$arr[0]['CustSrtNd'];
$respon['DlyCtrl'] =$arr[0]['DlyCtrl']; 
$respon['DENeed'] =$arr[0]['DENeed'];
$respon['CENeed'] =$arr[0]['CENeed'];
$respon['SENeed'] =$arr[0]['SENeed'];
$respon['NENeed'] =$arr[0]['NENeed'];
$respon['HENeed'] =$arr[0]['HENeed'];
$respon['RmdrNeed'] =$arr[0]['RmdrNeed'];
$respon['multi_cluster'] =$arr[0]['multi_cluster'];
$respon['RcpaMd_Mgr'] =$arr[0]['RcpaMd_Mgr'];
$respon['RcpaMd'] =$arr[0]['RcpaMd'];
// $respon['DrFeedMd'] =$arr[0]['DrFeedMd'];
if($arr[0]['DrFeedMd'] == '0') {$respon['DrFeedMd'] ='1';} else {$respon['DrFeedMd']  ='0';}
$respon['TempNd'] =$arr[0]['TempNd'];
$respon['cntRemarks'] =$arr[0]['cntRemarks'];
$respon['Terr_based_Tag'] =$arr[0]['Terr_based_Tag'];
$respon['quote_Text'] =$arr[0]['quote_Text'];
$respon['Pwdsetup'] =$arr[0]['Pwdsetup'];
$respon['DrNeed'] ='0';
$respon['sfMobile'] =  $arr[0]['sfMobile'];
$respon['sfEmail'] =  $arr[0]['sfEmail'];
$respon['faq'] =  '0';
$respon['prod_det_need'] =$arr[0]['prod_det_need'];
$respon['edit_holiday'] =$arr[0]['edit_holiday'];
$respon['edit_weeklyoff'] =$arr[0]['edit_weeklyoff'];
$respon['Target_report_Nd'] =$arr[0]['Target_report_Nd'];
$respon['DcrLockDays'] = $arr[0]['DcrLockDays'];

$respon['Doc_pob_caption'] = 'POB';
$respon['Stk_pob_caption'] = 'POB';
$respon['Chm_pob_caption'] = 'POB';
$respon['Uldoc_pob_caption'] = 'POB'; 
$respon['Hosp_pob_caption'] = 'POB'; 
$respon['CIP_pob_caption'] = 'POB'; 
$respon['Remainder_call_cap'] = $arr[0]['Remainder_call_cap'];
//$respon['Remainder_geo'] = $arr[0]['Remainder_geo'];
$respon['Remainder_geo'] = $arr[0]['Remainder_geo'];
$respon['sequential_dcr'] = $arr[0]['sequential_dcr'];
$respon['mydayplan_need'] = $arr[0]['mydayplan_need'];
$respon['pro_det_need'] = $arr[0]['pro_det_need'];
$respon['DrEvent_Md'] = $arr[0]['DrEvent_Md'];
$respon['ChmEvent_Md'] = $arr[0]['ChmEvent_Md'];
$respon['StkEvent_Md'] = $arr[0]['StkEvent_Md'];
$respon['UlDrEvent_Md'] = $arr[0]['UlDrEvent_Md'];
$respon['CipEvent_Md'] = $arr[0]['CipEvent_Md'];
$respon['HospEvent_Md'] = $arr[0]['HospEvent_Md'];

$respon['missedDateMand'] = $arr[0]['missedDateMand'];
$respon['HosPOBNd'] = $arr[0]['HosPOBNd'];
$respon['HosPOBMd'] = $arr[0]['HosPOBMd'];
$respon['HPNeed'] = $arr[0]['HPNeed'];
$respon['HINeed'] = $arr[0]['HINeed'];
$respon['HFNeed'] = $arr[0]['HFNeed'];
$respon['CIPPOBNd'] = $arr[0]['CIPPOBNd'];
$respon['CIPPOBMd'] = $arr[0]['CIPPOBMd'];
$respon['Leave_entitlement_need'] = $arr[0]['Leave_entitlement_need'];
$respon['Remainder_prd_Md'] = $arr[0]['Remainder_prd_Md'];
$respon['quiz_heading'] = $arr[0]['quiz_heading'];
$respon['entryFormNeed'] = $arr[0]['entryFormNeed'];
$respon['entryFormMgr'] = $arr[0]['entryFormMgr'];
$respon['quiz_need_mandt'] = $arr[0]['quiz_need_mandt'];
$respon['Dcr_summary_need'] = $arr[0]['Dcr_summary_need'];
$respon['primarysec_need'] = $arr[0]['primarysec_need'];
$respon['mediaTrans_Need'] = $arr[0]['mediaTrans_Need'];
$respon['tracking_interval'] = $arr[0]['tracking_interval'];
$respon['DrSmpQMd'] = $arr[0]['DrSmpQMd'];
$respon['DrRxQMd'] = $arr[0]['DrRxQMd'];
$respon['myplnRmrksMand'] = $arr[0]['myplnRmrksMand'];
$respon['Territory_VstNd'] = $arr[0]['Territory_VstNd'];
$respon['Dcr_firstselfie'] = $arr[0]['Dcr_firstselfie'];
$respon['CipSrtNd'] = $arr[0]['CipSrtNd'];
$respon['travelDistance_Need'] = $arr[0]['travelDistance_Need'];
$respon['State_Code'] = $arr[0]['State_Code'];
$respon['sf_emp_id'] = $arr[0]['sf_emp_id'];

		if($arr[0]['app_device_id']=="" && $arr[0]['SFStat']==0){				
			$sql="update access_table set app_device_id='$AppDeviceRegId' where Sf_Code='" . $arr[0]['SF_Code'] . "'";
			performQuery($sql);
		}
		else if($arr[0]['app_device_id']!=$AppDeviceRegId && $arr[0]['DeviceId_Need']==0){
			$respon=array();
			$respon['success'] = false;
		    $respon['msg'] = "Device Not Valid..";
		    return outputJSON($respon);
			die;
		}
		return outputJSON($respon);
		    } else {
				$respon['success'] = false;
		        $respon['msg'] = "Check User and Password";
		        return outputJSON($respon);
		    }
		}
function getProducts() {
    $sfCode = $_GET['sfCode'];
    $DivisionCode = $_GET['divisionCode'];

    $query = "exec getAppProd '" . $sfCode . "'"; //,'".$DivisionCode."'";
    return performQuery($query);
}

function getAPPSetups() {
    $rqSF = $_GET['rSF'];
    $query = "exec getAPPSetups '" . $rqSF . "'";
    return performQuery($query);
}
function svDCRActivity($Dact){ 
//echo 'hi';
 //	global $data; 
 $respon=array();
 if($Dact==undefined || $Dact == null || $Dact ==''){
		$data= json_decode($_POST['data'], true); 
		$val=$data['val'];}
else
{
	$val= $Dact;
 }

 $sql="select isnull(Max(cast(Max_slno as int)),0)+1 transslno from Activity_Group_SlNo with (INDEX(Idx_Activity_Group_SlNo))";
	$tr=performQuery($sql);
	$gid=$tr[0]['transslno'];
	$sql="update Activity_Group_SlNo set Max_slno ='$gid'";
	performQuery($sql);
	$g_status=0;
	
for ($i = 0; $i < count($val); $i++) 
{
	$det_no="0";
	$main_no="0";
	$type_val="0";
	$cust_code="0";
	$value=$val[$i];
	$sf=$value["SF"];
	$div=$value["div"];
	$act_date=$value["act_date"];
	$update_time=$value["update_time"];
	$slno=$value["slno"];
	$ctrl_id=$value["ctrl_id"];
	$create_id=$value["creat_id"];
	$va=$value["values"];
	$codes=$value["codes"];
	$type_val=$value["type"];
	$dt=$value["dcr_date"];
	
	if($type_val!="0"){
		if($type_val=='1'	||	$type_val=='2'	||	$type_val=='3'	||	$type_val=='4' ||	$type_val==''){
		$query="exec svDCRMain_App '".$sf."','".$dt."','".$value['WT']."','".$value['Pl']."','".$div."','','','Apps'";
		$respon["MQry"]=$query;
		performQuery($query);
		
      
      $query="select Trans_SlNo from vwActivity_Report where Sf_Code='".$sf."' and  cast(Activity_Date as datetime)=cast('".$dt."' as datetime)";
      $arr = performQuery($query);
      $respon["SlQry"]=$query;
	  $respon["valQry"]=$arr[0]["Trans_SlNo"];
      $det_no=$arr[0]["Trans_SlNo"];
	  $cust_code=$value["cus_code"];
	  }
	  
	  if($type_val=='1'){  
		$query="exec svDCRLstDet_App '".$det_no."',0,'".$sf."',1,'".$cust_code."','".$value['cusname']."','".$dt."',0,'','','','','','','','','','','','','".$div."',0,'".$dt."','".$value['lat']."','".$value['lng']."','".$value['DataSF']."','NA','Apps'";
		performQuery($query);
		$query="select Trans_Detail_Slno from vwActivity_MSL_Details where Trans_SlNo='".$det_no."' and Trans_Detail_Info_Code='".$cust_code."'";
        $arr = performQuery($query);
        $main_no=$arr[0]["Trans_Detail_Slno"];
		}

	if($type_val=='2' || $type_val=='3' ){
    	$query="exec svDCRCSHDet_App '".$det_no."',0,'".$sf."','".$type_val."','".$cust_code."','".$value['cusname']."','".$dt."',0,'','','','','','','','".$div."',0,'".$dt."','".$value['lat']."','".$value['lng']."','".$value['DataSF']."','NA','Apps'";
	    		//$params = array(array($ARDCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));

    	$result["CQry"]=$query;
      	performQuery($query);//performQueryWP($query, $params);
        $query="select Trans_Detail_Slno from vwActivity_CSH_Detail where Trans_SlNo='".$det_no."' and Trans_Detail_Info_Code='".$cust_code."'";
        $arr = performQuery($query);
        $main_no=$arr[0]["Trans_Detail_Slno"];
     } 
	if($type_val=='4'){
    	$query="exec svDCRUnlstDet_App '".$det_no."',0,'".$sf."','".$type_val."','".$cust_code."','".$value['cusname']."','".$dt."',0,'','','','','','','','','','','','','".$div."',0,'".$dt."','".$value['lat']."','".$value['lng']."','".$value['DataSF']."','NA','Apps'";
		//$params = array(array($ARDCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));
    	$result["NQry"]=$query;
      	performQuery($query);//performQueryWP($query, $params);
        $query="select Trans_Detail_Slno from vwActivity_Unlst_Detail where Trans_SlNo='".$det_no."' and Trans_Detail_Info_Code='".$cust_code."'";
        $arr = performQuery($query);
		//echo $query;
        $main_no=$arr[0]["Trans_Detail_Slno"];
     }
	}
	$query="exec svDcrActivity '$sf','$div','$act_date','$update_time','$slno','$ctrl_id','$create_id','$va','$codes','$det_no','$main_no','$type_val','$cust_code','$gid',$g_status";
//echo $query;
	$arr = performQuery($query);
	$respon["finalQry"]=$arr;
	
}
$respon['success'] = true;
return	$respon;
}
function getSubordinateMgr() {
    $sfCode = $_GET['rSF'];
    $param = array($sfCode);
    $query = "exec getHyrSF_APP '" . $sfCode . "'";
    return performQuery($query);
}

function getSubordinate() {
    $sfCode = $_GET['rSF'];
    $param = array($sfCode);
    $query = "exec getBaseLvlSFs_APP '" . $sfCode . "'";
    return performQuery($query);
}

function getJointWork() {
    $sfCode = $_GET['sfCode'];
    $rqSF = $_GET['rSF'];
    $query = "exec getJointWork_App '" . $sfCode . "','" . $rqSF . "'";
    return performQuery($query);
}

function getDtTPview() {
    $data = json_decode($_POST['data'], true);
    $sfCode = (string) $data['sfCode'];
    $t = strtotime(str_replace("Z", "", str_replace("T", " ", $data['tpDate'])));
    $TpDt = date('Y-m-d 00:00:00', $t);
    $Qry = "exec spTPViewDtws '$sfCode','$TpDt'";
    $respon = performQuery($Qry);
    return outputJSON($respon);
}

function getTPview() {
    $data = json_decode($_POST['data'], true);
    $sfCode = (string) $data['sfCode'];
    $t = strtotime(str_replace("Z", "", str_replace("T", " ", $data['mnthYr'])));
    $TpDt = date('Y-m-d 00:00:00', $t);
    $Qry = "SELECT convert(varchar,Tour_Date,103) [date],Worktype_Name_B wtype,replace(isnull(Tour_Schedule1,''),'0','') towns,replace(isnull(Tour_Schedule1,''),'0','') PlnNo,Worktype_Name_B1 wtype2,replace(isnull(Tour_Schedule2,''),'0','') towns2,replace(isnull(Tour_Schedule2,''),'0','') PlnNo2
,Worktype_Name_B2 wtype3,replace(isnull(Tour_Schedule3,''),'0','') towns3,replace(isnull(Tour_Schedule2,''),'0','') PlnNo3,SF_Code sf_code from Trans_TP T where sf_code='$sfCode' and Tour_Month=month('$TpDt') and Tour_year=year('$TpDt') order by Tour_Date";
    $respon = performQuery($Qry);
    return outputJSON($respon);
}

function getMonthSummary() {
    $sfCode = $_GET['rptSF'];
    $dyDt = $_GET['rptDt'];
    $query = "exec getMonthSummaryApp '" . $sfCode . "','" . $dyDt . "'";
    return performQuery($query);
}
function getOrders() {
    $sfCode = $_GET['sfCode']; 
    $query = "exec getOrderApp '" . $sfCode . "'";
    return performQuery($query);
}
function getDayReport() {
    $sfCode = $_GET['sfCode'];
    $dyDt = $_GET['rptDt'];
    $query = "exec getDayReportApp '" . $sfCode . "','" . $dyDt . "'"; 
    return performQuery($query);
}
function getCheckInReport() {
    $sfCode = $_GET['sfCode'];
    $dyDt = $_GET['rptDt'];
    
	$query = "exec getCheckInReportApp '" . $sfCode . "','" . $dyDt . "'";
    return performQuery($query);
}
function getDayReport_Geo() {
    $sfCode = $_GET['sfCode'];
    $dyDt = $_GET['rptDt'];
    $query = "exec getDayReportApp_Geo '" . $sfCode . "','" . $dyDt . "'"; 
    return performQuery($query);
}
function getvwChkTPStatus() {
	$sfCode = $_GET['sfCode'];
	$mMonth = $_GET['month'];
	$mYear = $_GET['year'];
	$sql="select TP_Entry_Count,TP_Flag, CASE WHEN TP_Flag = 1 THEN 'TP Not Approved. Contact Line Manager/Admin'
 WHEN TP_Flag = 2 THEN 'Tour Plan Rejected' WHEN TP_Flag = 0 THEN 'Prepare Tour Plan' Else '3' END AS TP_Status From vwCheckTPStatus where SF_Code='".$sfCode."' and Tour_Month ='".$mMonth."' and Tour_Year='".$mYear."'";
	return performQuery($sql);			  
}
function getRouteLocation() {
	$sfCode=$_GET['sfCode'];
    $rSF=$_GET['rSF'];
    $divCode = str_replace(",,", ",", $_GET['divisionCode']);
    $ReportDate=$_GET['ReportDate'];
    $query = "SELECT TL.SF_code,MS.Sf_Name,MS.SF_Mobile,TL.Emp_Id,TL.Employee_Id,CAST(CONVERT(VARCHAR,TL.DtTm,21) AS DATETIME) DtTm,FORMAT(TL.DtTm, 'hh:mm tt') as TrackTime,TL.Lat,TL.Lon,CASE WHEN TL.Addr='' THEN 'No Address Found!' ELSE TL.Addr END as Addr,TL.Auc,TL.EMod,CASE WHEN CAST(TL.Battery AS INT) BETWEEN 80 AND 100 THEN 'FULL' WHEN CAST(TL.Battery AS INT) BETWEEN 60 AND 80 THEN 'MEDIUM' WHEN CAST(TL.Battery AS INT) BETWEEN 40 AND 60  THEN 'LOW' ELSE 'VERY LOW' END AS BatteryStatus,CASE WHEN TL.Battery='' THEN '0' ELSE TL.Battery END as Battery,TL.Mock FROM Mas_Salesforce_AM MSAM INNER JOIN Mas_Salesforce MS ON MSAM.Sf_Code = MS.Sf_Code INNER JOIN tbTrackLoction TL ON TL.SF_code=MS.SF_code WHERE TL.EMod='Apps' AND CAST(TL.DtTm AS DATE)='".$ReportDate."' AND MSAM.DCR_AM='".$sfCode."' ORDER BY TL.SF_code, TL.DtTm DESC";
	$result = performQuery($query);
    return $result;
}
function getMissedReport() {
    $sfCode = $_GET['sfCode'];
    $dyDt = $_GET['rptDt']; 
	$first ="SELECT cast(format(cast('$dyDt' as datetime), 'yyyy-MM-01') as varchar) fdate"; 
	$res = performQuery($first);
	$fst_date =$res[0]["fdate"];
    $query = "exec Missedreport_app '" . $sfCode . "','" . $dyDt . "','" . $fst_date . "'"; 
    return performQuery($query);
}
function getDayCheckIn_Report() {
    $sfCode = $_GET['sfCode'];
    $dyDt = $_GET['rptDt'];
	$query = "exec getDaycheckInReportApp '" . $sfCode . "','" . $dyDt . "'";
    return performQuery($query);
}
function getMissedReportDetail() {
    $sfCode = $_GET['sfCode'];
	$div = $_GET['divisionCode'];
	$Rptdt = $_GET['report_date'];
	$year = date('Y', strtotime($Rptdt));
	$month = date('n', strtotime($Rptdt));
    $query = "exec Missedcall_report_app '" . $div . "','" . $sfCode . "','" . $month . "','" . $year . "','" . $Rptdt . "'"; 
     return performQuery($query);
}
function getvstDetail() {
    $sfCode = $_GET['sfCode'];
	$div = $_GET['divisionCode'];
	$Rptdt = $_GET['vst_date'];
	//$year = date('Y', strtotime($Rptdt));
	$year = date('Y', strtotime($Rptdt));
	$month = date('n', strtotime($Rptdt));
//Dashboard_Native_App 12, 'MR3802', 4, 2018, '05-01-2018'
   // $query = "exec Dashboard_Native_App '" . $div . "','" . $sfCode . "','" . $month . "','" . $year . "','" . $Rptdt . "'"; 
    $query = "exec Dashboard_Native_App '" . $div . "','" . $sfCode . "','9','2021','" . $Rptdt . "'"; 
	
	//echo $query;
     return performQuery($query);
}
function getVisitCover() {
    $sfCode = $_GET['sfCode'];
    $cMnth = $_GET['month'];
	$cYr = $_GET['year'];
	$div_code = $_GET['divisionCode'];
	$sf_type = $_GET['sf_type'];
    $query = "exec Visit_Coverage_Analysis_App '" . $div_code . "','" . $sfCode . "','" . $cMnth . "','" . $cYr . "'"; 
    return performQuery($query);
}


function SaveGeoTag(){

global $data;
 $data = json_decode($_POST['data'], true);
$drcode=(string) $data['cuscode'];
$div=(string) str_replace(",", "", $data['divcode']);
$lat=(string) $data['lat'];
$long=(string) $data['long'];
$cust=(string) $data['cust'];
$addr=(string) $data['Addr'];
$imgname=(string) $data['imge_name'];
$taggedtime=(string) $data['tagged_time'];
$sfName=(string) $data['sfname'];
$sfCode=(string) $data['sfcode'];
$mode=(string) $data['Mode'];
if(($taggedtime=='null') OR ($taggedtime=='NULL') OR ($taggedtime=="")) {
	$taggedtime=date('Y-m-d H:i:s');
}


if($cust=='D'){


	$query = "exec Map_geotag '".$drcode."','".$div."','".$lat."','".$long."','".$addr."','".$imgname."','".$taggedtime."','".$sfCode."','".$sfName."','".$mode."' ";

 	performQuery($query);
	$result["cat"]="D";
}
else if($cust=='C'){
$query = "exec Map_Chem_geotag '".$drcode."','".$div."','".$lat."','".$long."','".$addr."','".$imgname."','".$taggedtime."','".$sfCode."','".$sfName."','".$mode."' ";
performQuery($query);
$result["cat"]="C";
}
else if($cust=='S'){
$query = "exec Map_Stock_geotag '".$drcode."','".$div."','".$lat."','".$long."','".$addr."','".$imgname."','".$taggedtime."','".$sfCode."','".$sfName."','".$mode."' ";
//echo $query;
performQuery($query);
$result["cat"]="S";
}
else{
$query = "exec Map_Unlist_geotag '".$drcode."','".$div."','".$lat."','".$long."','".$addr."','".$imgname."','".$taggedtime."','".$sfCode."','".$sfName."','".$mode."' ";
performQuery($query);
$result["cat"]="U";
}
$result["Msg"]="Tag Submitted Successfully...";
			$result["success"]=true;
			return $result;

}


function getVstDets() {
    $ACd = $_GET['ACd'];
    $typ = $_GET['typ'];
    $query = "exec spGetVstDetApp '" . $ACd . "','" . $typ . "'";
    return performQuery($query);
}
function getVstDets_Geo() {
    $ACd = $_GET['ACd'];
    $sfCode = $_GET['sfCode'];
    $query = "exec spGetVstDetApp_Geo '".$sfCode."','" . $ACd . "' ";
    return performQuery($query);
}
function getTpSetup(){	
	global $data;
	$div = $_GET['divisionCode'];
    $divs = explode(",", $div . ",");
    $Owndiv = (string) $divs[0];
	//$query = "select * from tpSetup where div='".$Owndiv."'";	
	$query = "select SF_code,isnull(AddsessionNeed,1)AddsessionNeed,isnull(AddsessionCount,1)AddsessionCount,isnull(DrNeed,1)DrNeed,isnull(ChmNeed,1)ChmNeed,isnull(JWNeed,1)JWNeed,isnull(ClusterNeed,1)ClusterNeed,isnull(clustertype,1)clustertype,div,isnull(StkNeed,1)StkNeed,isnull(Cip_Need,1)Cip_Need,isnull(HospNeed,1)HospNeed,isnull(FW_meetup_mandatory,1)FW_meetup_mandatory,isnull(max_doc,0)max_doc,isnull(tp_objective,1)tp_objective,isnull(Holiday_Editable,0)Holiday_Editable,isnull(Weeklyoff_Editable,0)Weeklyoff_Editable from tpSetup where div='".$Owndiv."'";	
//echo $query;
	return performQuery($query);
}

function Reports_Activity() {
	$Arc = $_GET['arc'];
    $Arcdt = $_GET['arc_dt'];
	$result=array();		
	$qury="select DT.Activity_SlNo,H.Activity_Name,DT.Group_id from DCR_Detail_Activity DT  left outer join mas_activity H on DT.Activity_SlNo = H.Activity_SlNo where Trans_Main_Sl_No='".$Arc."' and Trans_Detail_Slno='".$Arcdt."' group by DT.Activity_SlNo,H.Activity_Name,DT.Group_id"; 
	$restt=performQuery($qury);
	if(count($restt)>0)
	{ 
		for($ilk=0;$ilk<count($restt);$ilk++)
		{
			$query="select ROW_NUMBER() over (ORDER BY Group_id ASC) as slno,DT.Group_id,DH.Field_Name,DH.Order_by,DT.Creation_Name,cast(convert(varchar,DT.Activity_Date,101) as datetime) Activity_Date,DT.Updated_Time,DT.SF_Code,DT.Control_Id from mas_dynamic_screen_creation DH left outer join DCR_Detail_Activity DT ON DH.Activity_SlNo=DT.Activity_SlNo and  DH.Creation_Id=DT.Creation_Id and DH.Control_id=DT.Control_id and DH.Activity_SlNo='".$restt[$ilk]['Activity_SlNo']."' where DT.Group_id='".$restt[$ilk]['Group_id']."'  order by Order_by Asc";
			//echo $query;
			$rest=performQuery($query);
			
					if (count($rest)>0) 
					{
						$Rptact=array();
						for ($il=0;$il<count($rest);$il++)
						{
							array_push($Rptact,array(
							'slno' =>$rest[$il]["slno"],
							'Group_id' =>$rest[$il]["Group_id"],
							'Field_Name' =>$rest[$il]["Field_Name"],
							'Creation_Name' =>$rest[$il]["Creation_Name"],
							'Activity_Date' =>$rest[$il]["Activity_Date"],
							'Updated_Time' =>$rest[$il]["Updated_Time"],
							'SF_Code' =>$rest[$il]["SF_Code"],
							'Control_Id' =>$rest[$il]["Control_Id"],
							'Order_by' =>$rest[$il]["Order_by"]));
						}	
						array_push($result,array('Main_id' =>$restt[$ilk]['Activity_SlNo'],'Main_Name' =>$restt[$ilk]['Activity_Name'],'Group_id' =>$restt[$ilk]["Group_id"],'Activity_data' => $Rptact));						
					}
		}
	}
	return $result;
}


function getOrderDets() {
     $SF = $_GET['sfCode'];
    $ordid = $_GET['Ord_No'];
    $query = "exec getOrderSummaryApp '" . $SF . "','" . $ordid . "'";
//echo $query;
    return performQuery($query);
}
function getDoctorDet(){
    $SF = $_GET['sfCode'];
    $MSL = $_GET['Msl_No'];

    $query = "select Doc_Cat_Code,Doc_Cat_ShortName,Doc_QuaCode, visit_hours,visit_days,REPLACE(visit_days,'/',',') visit_days1,REPLACE(visit_hours,'/',',') visit_hours1,ListedDr_Address3,Doc_Qua_Name,Doc_Special_Code,Doc_Spec_ShortName,Hospital_Address,convert(nvarchar(MAX), ListedDr_DOB, 23) ListedDr_DOB,convert(nvarchar(MAX), ListedDr_DOW, 23) ListedDr_DOW,ListedDr_Hospital,ListedDr_Sex,ListedDr_RegNo,Visiting_Card,Dr_Potential,Dr_Contribution from mas_listeddr where ListedDrCode='" . $MSL . "'";
$result=performQuery($query);
 return outputJSON($result);
}
function getPreCallDet() {
    $SF = $_GET['sfCode'];
    $MSL = $_GET['Msl_No'];

    $result = array();
    $query = "select SLVNo SVL,Doc_Cat_ShortName DrCat,Doc_Spec_ShortName DrSpl,isnull(stuff((select ', '+Doc_SubCatName from Mas_Doc_SubCategory S where CHARINDEX(cast(Doc_SubCatCode as varchar),D.Doc_SubCatCode)>0 for XML Path('')),1,2,''),'') DrCamp,isnull(stuff((select ', '+Product_Detail_Name from Map_LstDrs_Product M	inner join Mas_Product_Detail P on M.Product_Code=P.Product_Detail_Code and P.Division_Code=M.Division_Code where Listeddr_Code=D.ListedDrCode for XML Path('')),1,2,''),'') DrProd from mas_listeddr D where ListedDrCode='" . $MSL . "'";
    $as = performQuery($query);
    if (count($as) > 0) {
        $result['SVL'] = (string) $as[0]['SVL'];
        $result['DrCat'] = (string) $as[0]['DrCat'];
        $result['DrSpl'] = (string) $as[0]['DrSpl'];
        $result['DrCamp'] = (string) $as[0]['DrCamp'];
        $result['DrProd'] = (string) $as[0]['DrProd'];
        $result['success'] = true;

        $query = "select Trans_SlNo,Trans_Detail_Slno,convert(varchar,Activity_Date,0) Adate,Time DtTm1,convert(varchar,cast(convert(varchar,Activity_Date,101)+' '+Time  as datetime),20) as DtTm,isnull(CalFed,'') CalFed,Activity_Remarks,products,gifts,isnull(nextvstdate,'') nextvstdate from vwLastVstDet where rw=1 and Trans_Detail_Info_Code='" . $MSL . "' and SF_Code='" . $SF . "'";

        $as = performQuery($query);


        if (count($as) > 0) {
$dat=$as[0]['DtTm1'];
   $result['LVDt'] =date_format($dat,'d / m / Y g:i a');
            //$result['LVDt'] = date('d / m / Y g:i a', strtotime((string) $as[0]['DtTm']));
		$nextvstdate=$as[0]['nextvstdate'];	
		 	$result['next_visit_date'] =$nextvstdate;
            $Prods = (string) $as[0]['products'];
            $sProds = explode("#", $Prods . '#');
            $sSmp = '';
            $sProm = '';
            for ($il = 0; $il < count($sProds); $il++) {
                if ($sProds[$il] != '') {
                    $spr = explode("~", $sProds[$il]);
                    $Qty = 0;
                    if (count($spr) > 0) {
                        $QVls = explode("$", $spr[1]);
                        $Qty = $QVls[0];
                        $Vals = $QVls[1];
                    }
                    if ($Qty > 0)
                        $sSmp = $sSmp . $spr[0] . " ( " . $Qty . " )" . (($Vals > 0) ? " ( " . $Vals . " )," : ",");
                    else
                        $sProm = $sProm . $spr[0] . ", ";
                }
            }

            $result['CallFd'] = (string) $as[0]['CalFed'];
            $result['Rmks'] = (string) $as[0]['Activity_Remarks'];
            $result['ProdSmp'] = $sSmp;
            $result['Prodgvn'] = $sProm;
            $result['DrGft'] = (string) $as[0]['gifts'];
        }else {
			$result['CallFd'] = '';
            $result['Rmks'] = '';
            $result['ProdSmp'] = '';
            $result['Prodgvn'] = '';
            $result['DrGft'] = '';
			$result['next_visit_date'] ='';
			$result['LVDt'] ='';
            $result['success'] = false;
        }
    } else {
        $result['success'] = false;
    }
    return outputJSON($result);
}
function SvNewDr(){
    global $data;
$data = json_decode($_POST['data'], true);
	$SF=(string) $data['SF'];
    $DivCodes = (string) $data['DivCode'];
    $DivCode = explode(",", $DivCodes.",");
	$DrName=(string) $data['DrName'];
	$DrQCd=(string) $data["DrQCd"];
	$DrQNm=(string) $data["DrQNm"];
	$DrClsCd=(string) $data["DrClsCd"];
	$DrClsNm=(string) $data["DrClsNm"];
	$DrCatCd=(string) $data["DrCatCd"];
    $CatNm=(string)  $data["DrCatNm"];
	$DrSpcCd=(string) $data["DrSpcCd"];
	$DrSpcNm=(string) $data["DrSpcNm"];
	$DrAddr=(string) $data["DrAddr"];
	$DrTerCd=(string) $data["DrTerCd"];
	$DrTerNm=(string) $data["DrTerNm"];
	$DrPincd=(string) $data["DrPincd"];
	$DrPhone=(string) $data["DrPhone"];
	$DrMob=(string) $data["DrMob"];
	$Uid=(string) $data["Uid"];
	$query="exec svNewCustomer_App 0,'','".$DrName."','".$DrAddr."','".$DrTerCd."','".$DrTerNm."','".$DrCatCd."','".$CatNm."','".$DrSpcCd."','".$DrSpcNm."','".$DrClsCd."','".$DrClsNm."','".$DrQCd."','".$DrQNm."','U','".$SF."','','','".$DrPincd."','".$DrPhone."','".$DrMob."','".$Uid."'";
  //echo $query;
	$output=performQuery($query);
	$result["Qry"]=$output[0]['Msg'];
    $result["success"]=true;
    return $result;

}

function getDocSpec() {
    global $data;
$data = json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
    $query="exec iOS_getDocSpec '".$sfCode."'";
    return performQuery($query);
}

function getDocCats() {
    global $data;
$data = json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
    $query="exec iOS_getDocCats '".$sfCode."'";
    return performQuery($query);
}

function getDocClass() {
    global $data;
$data = json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
	
    $query="exec iOS_getDocClass '".$sfCode."'";
    return performQuery($query);
}

function getDocQual() {
    global $data;
$data = json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
    $query="exec iOS_getDocQual '".$sfCode."'";
    return performQuery($query);
}
function SvMyTodayTP(){
    global $data;
$data = json_decode($_POST['data'], true);
    $DivCodes = (string) $data['Div'];
    $DivCode = explode(",", $DivCodes.",");
    $sfCode=(string) $data['SF'];
    $SFMem=(string) $data['SFMem'];
    $TPDt=(string) $data['TPDt'];
    $PlnCd=(string) $data['Pl'];
    $PlnNM=(string) $data['PlNm'];
    $WT=(string) $data['WT'];
    $WTNM=(string) $data['WTNMm'];
    $Rem=(string) $data['Rem'];
    $loc=(string) $data['location'];
	$TpVwFlg=(string) $data['TpVwFlg'];
	$TpDrc=(string) $data['TP_Doctor'];
	$TpCluster=(string) $data['TP_DocCluster'];
	$TpWrktype=(string) $data['TP_Worktype'];
	
	if($TpCluster =='' || $TpCluster=="''" || $TpCluster== null){
	$TpCluster ='';$TpWrktype='';}
    // $query="select SF_type,SF_Status,sf_TP_Active_Flag  from Mas_Salesforce where Sf_Code='".$sfCode."'";
    // $ExisArr = performQuery($query);
	  // $SFTy=$ExisArr[0]["SF_type"];
	  // $SFst=$ExisArr[0]["SF_Status"];
	  // $SFtst=$ExisArr[0]["sf_TP_Active_Flag"];
	  // if($SFst !=0 || $SFtst !=0){
		// $result["Msg"]="User status Changed.Contact Administrator...";
		// $result["success"]=false;
		// return $result;
		  
	  // }
    $InsMode=(string) $data['InsMode'];
    
		$HeaderId = (isset($_GET['Head_id']) && strlen($_GET['Head_id']) == 0) ? null : $_GET['Head_id'];       
		if($HeaderId !=null)
		{
			$query = "exec Delete_reject_dcr '$HeaderId' ";
			performQuery($query); 
		}
	$qry="select count(Leave_Id)Lcnt from mas_Leave_Form where SF_code='".$sfCode."' and Leave_Active_Flag<>1 and (cast(From_Date as date)<=cast('$TPDt' as date) and cast('$TPDt' as date)<=To_Date or cast(From_Date as date)<=cast('$TPDt' as date) and cast('$TPDt' as date)<=To_Date or cast(From_Date as date)>=cast('$TPDt' as date) and To_Date<=cast('$TPDt' as date))";
	//echo $qry;
    $Lary = performQuery($qry);
	  if ($Lary[0]["Lcnt"]>0){
			  $result["Msg"]="Today Already Leave Posted...";
			  $result["success"]=false;
			  return $result;
	  }	  
		
	  $query="select Count(Trans_SlNo) Cnt from vwActivity_Report where Sf_Code='".$sfCode."' and Confirmed <>'2' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('".$TPDt."' as datetime),101) as datetime) and FWFlg='L'";
    $ExisArr = performQuery($query);
	  if ($ExisArr[0]["Cnt"]>0){
			  $result["Msg"]="Today Already Leave Posted...";
			  $result["success"]=false;
			  return $result;
	  }else{
        $query="select Count(Trans_SlNo) Cnt from vwActivity_Report where Sf_Code='".$sfCode."' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('".$TPDt."' as datetime),101) as datetime) and Work_Type<>'".$WT."'";
		    $ExisArr = performQuery($query);
        $result["cqry"]=$query;
		if ($ExisArr[0]["Cnt"]>0 && $InsMode=="0"){
			$result["Msg"]="Already you are submitted your work. Now you are deviate. Do you want continue?";
			$result["update"]=true;
			$result["success"]=false;
		}
		else{
			$query="exec iOS_svTodayTP '".$sfCode."','".$SFMem."','".$PlnCd."','".$PlnNM."','".$WT."','".$WTNM."','".$Rem."','".$loc."','".$TPDt."','".$TpVwFlg."','".$TpDrc."','".$TpCluster."','".$TpWrktype."'";
			//echo $query;
			performQuery($query);
			if ($InsMode=="2")
			{
				$query="select Work_Type,WorkType_Name,FWFlg,Half_Day_FW from vwActivity_Report where Sf_Code='".$sfCode."' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('".$TPDt."' as datetime),101) as datetime) and Work_Type<>'".$WT."'";
				$ExisArr = performQuery($query);
				$PwTy=$ExisArr[0]["Work_Type"];
				$PwTyNm=$ExisArr[0]["WorkType_Name"];
				$PwFl=$ExisArr[0]["FWFlg"];
				$HwTy=$ExisArr[0]["Half_Day_FW"];
				$query="select FWFlg,Wtype from vw_all where SFTyp='". $SFTy."' and type_code='".$WT."'";
				$ExisArr = performQuery($query);
				
				$query="update DCRMain_Trans set ";
				if($PwFl!="F"  ){
					$HwTy = $HwTy . $PwTy . ",";
					$query=$query." Work_type='" . $WT . "',FieldWork_Indicator='".$ExisArr[0]["FWFlg"]."',WorkType_Name='" . $ExisArr[0]["Wtype"] . "',";
				}else{
					$HwTy = $HwTy . $WT . ",";
				}
				$query=$query."Half_Day_FW='" . $HwTy . "' where Sf_Code='".$sfCode."' and cast(convert(varchar,Activity_Date,101) as datetime)=cast(convert(varchar,cast('".$TPDt."' as datetime),101) as datetime)";
				performQuery($query);
        performQuery(str_replace("DCRMain_Trans", "DCRMain_Temp", $query));
			}
			else
			{ 
				if ($InsMode=="1")
				{
					$query="exec DelDCRTempByDt '".$sfCode."','" . date('Y-m-d 00:00:00.000', strtotime($TPDt)) ."'";
					performQuery($query);
				}
        
				$query="exec svDCRMain_App '" . $sfCode . "','" . date('Y-m-d 00:00:00.000', strtotime($TPDt)) . "','" . $WT . "','" . $PlnCd . "','" . $DivCode[0] . "','" . $Rem . "','','app'";
				$result["aqry"]=$query;
				performQuery($query);
			}
			$result["Msg"]="Today Work Plan Submitted Successfully...";
			$result["success"]=true;
		}
			return $result;
	}
}
function getTPApproval(){
    global $data;
$data = json_decode($_POST['data'], true);
    $sfCode=(string) $data['SF'];
    $query="exec iOS_getTPApproval '".$sfCode."'"; 
    return performQuery($query);
}
function SvTPReject(){
    global $data;
	$data= json_decode($_POST['data'], true); 
    $sfCode=(string) $data['SF'];
	$Reason=(string) $data['Reason'];
	$query="exec iOS_svTPReject '".$sfCode."','".$data['TPMonth']."','".$data['TPYear']."','".$Reason."'";
//echo $query;
	performQuery($query);
	$result["Qry"]=$query;
    $result["success"]=true;
    return $result;
	$msg ="Your Tourplan is rejected for ".$Reason."";			
	send_notification_body($sfCode,$msg,0);
}

function getDynamicActivity(){	
//	global $data;
	$data= json_decode($_POST['data'], true);  
	$division = (string) $data['div'];	
	//$query = "select * from mas_activity where Division_Code='".$division."' and Active_Flag='0'"; 
	$query = "select Activity_SlNo,Activity_Mode,replace(Activity_Desig, ' ', '') Activity_Desig,Activity_SName,Activity_Name,Activity_OrderBy,Division_Code,Creation_date,Active_Flag,Activity_For,Activity_Available,Other_Multi_Activity_Name,Related_Activity_SlNo,Approval_Needed,Approved_By,Transaction_Involved,Editable from mas_activity where Division_Code='".$division."' and Active_Flag='0'"; 
	return performQuery($query);
}
function getDynamicView(){	
	global $data;	
	$data= json_decode($_POST['data'], true); 
	$Act_slno = (string) $data['slno'];	
	//$query = "select * from mas_dynamic_screen_creation where Activity_SlNo='".$Act_slno."' and Active_Flag='0'";	
	$query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID from  mas_dynamic_screen_creation where Activity_SlNo='".$Act_slno."' and Active_Flag='0'";	
	return performQuery($query);
}
function getDynamicViewDetail(){	
	global $data;	
	$data= json_decode($_POST['data'], true);
	$sfCode = (string) $data['slno'];	
	$div = (string) $data['div'];
	$sf = (string) $data['SF'];
	$div = str_replace(",", "", $div);
	$query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID    from mas_dynamic_screen_creation where Activity_SlNo='".$sfCode."' and Division_Code='".$div."' and Active_Flag='0' order by Order_by Asc";	
	
	$res=performQuery($query);
	if (count($res)>0) 
	{
		for ($il=0;$il<count($res);$il++)
		{
			$id=$res[$il]["Control_Id"];
		
		if($id=="8"	||	$id=="9"){
			if($res[$il]["Control_Para"]=="Mas_ListedDr"){
			$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where Division_Code='".$div."' and Sf_Code='".$sf."'";	
			}
			else if($res[$il]["Control_Para"]=="Mas_Product_Detail"){
				//$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where cast(SUBSTRING(Division_Code,1,CHARINDEX(',',Division_Code)-1) as int) ='".$div."'";	
				$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where Division_Code='".$div."' and Product_Active_Flag='0'";
			}
			else{
				$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where Division_Code='".$div."'";	
			}
			
			$res[$il]['inputss']=$qu;
			$res[$il]['input']=performQuery($qu);
		}
		else	if($id=="12"	||	$id=="13"){
			$qu = "select Sl_No from Mas_Customized_Table_Name where Name_Table='".$res[$il]["Control_Para"]."'";
				$res[$il]['inputss']=$qu;
			$cus=performQuery($qu);
			//$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from Mas_Customized_Table where Name_Table_Slno='".$cus[0]["Sl_No"]."'";
			$qu = "select Mas_Sl_No,Customized_Name from Mas_Customized_Table where Name_Table_Slno='".$cus[0]["Sl_No"]."'";
			$cus=performQuery($qu);
			$res[$il]['input']=$cus;
		}
		else{
			$res[$il]['input']=array();
		}
		}
	}
	
	return $res;
}
function getDynamicViewDetail_tp(){	
	global $data;	
	$data= json_decode($_POST['data'], true);
	$sfCode = (string) $data['slno'];	
	$div = (string) $data['div'];
	$sf = (string) $data['SF'];
	$div = str_replace(",", "", $div);
	$query = "select Creation_Id,Activity_SlNo,Field_Name,Control_Id,Control_Name,Control_Para,Division_Code,Activity_Name,Created_date,Order_by,Updated_Date,Active_Flag,Table_code,Table_name,Mandatory,For_act, (case when Group_Creation_ID='' then 0 else Group_Creation_ID end )Group_Creation_ID    from mas_dynamic_screen_creation where Activity_SlNo='".$sfCode."' and Division_Code='".$div."' and Active_Flag='0' and  Activity_For !='D,' order by Order_by Asc";	
	
	$res=performQuery($query);
	if (count($res)>0) 
	{
		for ($il=0;$il<count($res);$il++)
		{
			$id=$res[$il]["Control_Id"];
		
		if($id=="8"	||	$id=="9"){
			if($res[$il]["Control_Para"]=="Mas_ListedDr"){
			$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where Division_Code='".$div."' and Sf_Code='".$sf."'";	
			}
			else if($res[$il]["Control_Para"]=="Mas_Product_Detail"){
				//$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where cast(SUBSTRING(Division_Code,1,CHARINDEX(',',Division_Code)-1) as int) ='".$div."'";	
				$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where Division_Code='".$div."' and Product_Active_Flag='0'";
			}
			else{
				$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from ".$res[$il]["Control_Para"]." where Division_Code='".$div."'";	
			}
			
			$res[$il]['inputss']=$qu;
			$res[$il]['input']=performQuery($qu);
		}
		else	if($id=="12"	||	$id=="13"){
			$qu = "select Sl_No from Mas_Customized_Table_Name where Name_Table='".$res[$il]["Control_Para"]."'";
				$res[$il]['inputss']=$qu;
			$cus=performQuery($qu);
			//$qu = "select ".$res[$il]["Table_code"].",".$res[$il]["Table_name"]." from Mas_Customized_Table where Name_Table_Slno='".$cus[0]["Sl_No"]."'";
			$qu = "select Mas_Sl_No,Customized_Name from Mas_Customized_Table where Name_Table_Slno='".$cus[0]["Sl_No"]."'";
			$cus=performQuery($qu);
			$res[$il]['input']=$cus;
		}
		else{
			$res[$il]['input']=array();
		}
		}
	}
	
	return $res;
}
function SvTourPlan_approve(){

	$data= json_decode($_POST['data'], true); 

    $TPDatas=$data[0]['TPDatas'];
	$sfCode=(string) $data[0]['SFCode'];
    $sfName=(string) $data[0]['SFName'];
    $DivCodes = (string) $data[0]['DivCode'];
    $DivCode = explode(",", $DivCodes.",");  
	
	$sql = "update Tourplan_detail set Change_Status='3',Approve_mode='Apps',Approved_time=getdate() where SFCode='".$sfCode."' and cast(Mnth as int)='".$data[0]['TPMonth']."' and cast(Yr as int)='".$data[0]['TPYear']."'";
    performQuery($sql);
	$sql = "insert into Trans_TP select  SF_Code,Tour_Month,Tour_Year,Submission_date,Tour_Date,WorkType_Code_B,Worktype_Name_B,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,Objective,Worked_With_SF_Code,Division_Code,'1',getdate(),Rejection_Reason,Change_Status,Territory_Code1,Territory_Code2,Territory_Code3,Worked_With_SF_Name,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,TP_Sf_Name,TP_Approval_MGR,Entry_mode,Dr_Code,Dr_Name,Chem_Code,Chem_Name,Stockist_Code,Stockist_Name,Hosptial_Code,Hosptial_Name,Others_Code,Others_Name,Deviate,Dr_two_code,Dr_two_name,Dr_three_code,Dr_three_name,Chem_two_code,Chem_two_name,Chem_three_code,Chem_three_name,Unlistdr_one_code,Unlistdr_one_name,Unlistdr_two_code,Unlistdr_two_name,Unlistdr_three_code,Unlistdr_three_name,Remark_two,Remark_three,Jointwork_two_code,Jointwork_two_name,Jointwork_three_code,Jointwork_three_name,Approval_mode,Stockist_two_code,Stockist_two_name,Stockist_three_code,Stockist_three_name,HQCodes,HQNames,Objective_id1,Objective_Name1,Objective_id2,Objective_Name2,Objective_id3,Objective_Name3,Hosptial_two_code,Hosptial_two_name,Hosptial_three_code,Hosptial_three_name,WTFlg,WTFlg1,WTFlg2,Hosptial_Code1,Hosptial_Name1,Hosptial_Code2,Hosptial_Name2,HQCodes1,HQNames1,HQCodes2,HQNames2 from Trans_TP_One where SF_Code ='".$sfCode."' and Tour_Month='".$data[0]['TPMonth']."' and Tour_Year='".$data[0]['TPYear']."'";
    performQuery($sql);
	$sql = "delete from Trans_TP_One where SF_Code ='".$sfCode."' and Tour_Month='".$data[0]['TPMonth']."' and Tour_Year='".$data[0]['TPYear']."'";
    performQuery($sql);	
	$month=$data[0]['TPMonth']+1;
	$year=$data[0]['TPYear'];
	$tdate=$year.'-'.$month.'-01';
	$sql = "update mas_salesforce_dcrtpdate set Last_TP_Date='$tdate' where sf_Code='".$sfCode."' and '$tdate'>Last_TP_Date";
	performQuery($sql);
    $result["success"]=true;
    return $result;
} 

function SvTPApprovalNew(){
		$data= json_decode($_POST['data'], true); 

    $TPDatas=$data[0]['TPDatas'];
	$sfCode=(string) $data[0]['SFCode'];
    $sfName=(string) $data[0]['SFName'];
    $DivCodes = (string) $data[0]['DivCode'];
    $DivCode = explode(",", $DivCodes.",");  
	for ($i = 0; $i < count($TPDatas); $i++) 
    {
		$TPData=$TPDatas[$i];
		if($TPData["dayno"]!="")
		{
			$TPDet=$TPData["DayPlan"];
			$TPWTCd=array();$TPWTNm=array();$TPSFCd=array();$TPSFNm=array();$TPPlCd=array();$TPPlNm=array();
			$TPDRCd=array();$TPDRNm=array();$TPCHCd=array();$TPCHNm=array();$TPJWCd=array();$TPJWNm=array();$TPRmks=array();
			for($il=0;$il<count($TPDet);$il++){
				array_push($TPWTCd,$TPDet[$il]["WTCode"]);
				array_push($TPWTNm,$TPDet[$il]["WTName"]);
				
				array_push($TPSFCd,$TPDet[$il]["HQCodes"]);
				array_push($TPSFNm,$TPDet[$il]["HQNames"]);

				array_push($TPPlCd,$TPDet[$il]["ClusterCode"]);
				array_push($TPPlNm,$TPDet[$il]["ClusterName"]);

				array_push($TPJWCd,$TPDet[$il]["ClusterSFs"]);
				array_push($TPJWNm,$TPDet[$il]["ClusterSFNms"]);
				array_push($TPDRCd,$TPDet[$il]["Dr_Code"]);
				array_push($TPDRNm,$TPDet[$il]["Dr_Name"]);
				array_push($TPCHCd,$TPDet[$il]["Chem_Code"]);
				array_push($TPCHNm,$TPDet[$il]["Chem_Name"]);
				array_push($TPRmks,$TPDet[$il]["DayRemarks"]);
			}
			$query="exec iOS_svTourApprovalNew '".$sfCode."','".$sfName."','".$data['TPMonth']."','".$data['TPYear']."','".$TPData["TPDt"]."','".$Stat."','".$TPWTCd[0]."','".$TPWTCd[1]."','".$TPWTCd[2]."','".$TPWTNm[0]."','".$TPWTNm[1]."','".$TPWTNm[2]."','".$TPSFCd[0]."','".$TPSFCd[1]."','".$TPSFCd[2]."','".$TPSFNm[0]."','".$TPSFNm[1]."','".$TPSFNm[2]."','".$TPPlCd[0]."','".$TPPlCd[1]."','".$TPPlCd[2]."','".$TPPlNm[0]."','".$TPPlNm[1]."','".$TPPlNm[2]."','".$TPJWCd[0]."','".$TPJWCd[1]."','".$TPJWCd[2]."','".$TPJWNm[0]."','".$TPJWNm[1]."','".$TPJWNm[2]."','".$TPDRCd[0]."','".$TPDRCd[1]."','".$TPDRCd[2]."','".$TPDRNm[0]."','".$TPDRNm[1]."','".$TPDRNm[2]."','".$TPCHCd[0]."','".$TPCHCd[1]."','".$TPCHCd[2]."','".$TPCHNm[0]."','".$TPCHNm[1]."','".$TPCHNm[2]."','".$TPRmks[0]."','".$TPRmks[1]."','".$TPRmks[2]."','".$DivCode[0]."'";
			performQuery($query);
			$result["Qry"]=$query;
		} 
	}
    $result["success"]=true;
    return $result;
}
function SvTourPlanNew($Stat){ 
  global $data;
  $data= json_decode($_POST['data'], true); 
  $sfCode=$_GET['sfCode'];
  $sfCode=(string) $data['SF'];
  $sfName=(string) $data['SFName'];
  $DivCodes = (string) $data['DivCode'];
  $DivCode = explode(",", $DivCodes.",");
  $TPDatas=$data['TPDatas'];
  for ($i = 0; $i < count($TPDatas); $i++){
	$TPData=$TPDatas[$i];
	if($TPData["dayno"]!=""){
	  $TPDet=$TPData["DayPlan"];
	  $TPWTCd=array();$TPWTNm=array();$TPSFCd=array();$TPSFNm=array();$TPPlCd=array();$TPPlNm=array();
	  $TPDRCd=array();$TPDRNm=array();$TPCHCd=array();$TPCHNm=array();
	  $TPJWCd=array();$TPJWNm=array();$TPRmks=array();$TPSTCd=array();$TPSTNm=array();$TPOBJCd=array();$TPOBJNm=array();
	  for($il=0;$il<count($TPDet);$il++){
		array_push($TPWTCd,$TPDet[$il]["WTCd"]);
		array_push($TPWTNm,$TPDet[$il]["WTNm"]);
		array_push($TPSFCd,$TPDet[$il]["HQCd"]);
		array_push($TPSFNm,$TPDet[$il]["HQNm"]);
		array_push($TPPlCd,$TPDet[$il]["TerrCd"]);
		array_push($TPPlNm,$TPDet[$il]["TerrNm"]);
		array_push($TPJWCd,$TPDet[$il]["JWCd"]);
		array_push($TPJWNm,$TPDet[$il]["JWNm"]);
		array_push($TPDRCd,$TPDet[$il]["DRCd"]);
		array_push($TPDRNm,$TPDet[$il]["DRNm"]);
		array_push($TPCHCd,$TPDet[$il]["CHCd"]);
		array_push($TPCHNm,$TPDet[$il]["CHNm"]);
		array_push($TPSTCd,$TPDet[$il]["STCd"]);
		array_push($TPSTNm,$TPDet[$il]["STNm"]);
		array_push($TPRmks,$TPDet[$il]["DayRmk"]);
		array_push($TPOBJCd,$TPDet[$il]["objectiveid"]);
		array_push($TPOBJNm,$TPDet[$il]["objective"]);
	  }
	  $query="exec iOS_svTourPlanNew '".$sfCode."','".$sfName."','".$data['TPMonth']."','".$data['TPYear']."','".$TPData["TPDt"]."','".$Stat."','".$TPWTCd[0]."','".$TPWTCd[1]."','".$TPWTCd[2]."','".$TPWTNm[0]."','".$TPWTNm[1]."','".$TPWTNm[2]."','".$TPSFCd[0]."','".$TPSFCd[1]."','".$TPSFCd[2]."','".$TPSFNm[0]."','".$TPSFNm[1]."','".$TPSFNm[2]."','".$TPPlCd[0]."','".$TPPlCd[1]."','".$TPPlCd[2]."','".$TPPlNm[0]."','".$TPPlNm[1]."','".$TPPlNm[2]."','".$TPJWCd[0]."','".$TPJWCd[1]."','".$TPJWCd[2]."','".$TPJWNm[0]."','".$TPJWNm[1]."','".$TPJWNm[2]."','".$TPDRCd[0]."','".$TPDRCd[1]."','".$TPDRCd[2]."','".$TPDRNm[0]."','".$TPDRNm[1]."','".$TPDRNm[2]."','".$TPCHCd[0]."','".$TPCHCd[1]."','".$TPCHCd[2]."','".$TPCHNm[0]."','".$TPCHNm[1]."','".$TPCHNm[2]."','".$TPRmks[0]."','".$TPRmks[1]."','".$TPRmks[2]."','".$DivCode[0]."','".$TPSTCd[0]."','".$TPSTCd[1]."','".$TPSTCd[2]."','".$TPSTNm[0]."','".$TPSTNm[1]."','".$TPSTNm[2]."','".$TPOBJCd[0]."','".$TPOBJNm[0]."','".$TPOBJCd[1]."','".$TPOBJNm[1]."','".$TPOBJCd[2]."','".$TPOBJNm[2]."','0','Apps'";
	  performQuery($query);
	  $query="exec svTourPlan_detail '".$sfCode."','".$sfName."','".$data['TPMonth']."','".$data['TPYear']."','".$TPData["TPDt"]."','".$Stat."','".$TPWTCd[0]."','".$TPWTCd[1]."','".$TPWTCd[2]."','".$TPWTNm[0]."','".$TPWTNm[1]."','".$TPWTNm[2]."','".$TPSFCd[0]."','".$TPSFCd[1]."','".$TPSFCd[2]."','".$TPSFNm[0]."','".$TPSFNm[1]."','".$TPSFNm[2]."','".$TPPlCd[0]."','".$TPPlCd[1]."','".$TPPlCd[2]."','".$TPPlNm[0]."','".$TPPlNm[1]."','".$TPPlNm[2]."','".$TPJWCd[0]."','".$TPJWCd[1]."','".$TPJWCd[2]."','".$TPJWNm[0]."','".$TPJWNm[1]."','".$TPJWNm[2]."','".$TPDRCd[0]."','".$TPDRCd[1]."','".$TPDRCd[2]."','".$TPDRNm[0]."','".$TPDRNm[1]."','".$TPDRNm[2]."','".$TPCHCd[0]."','".$TPCHCd[1]."','".$TPCHCd[2]."','".$TPCHNm[0]."','".$TPCHNm[1]."','".$TPCHNm[2]."','".$TPRmks[0]."','".$TPRmks[1]."','".$TPRmks[2]."','".$DivCode[0]."','".$TPSTCd[0]."','".$TPSTCd[1]."','".$TPSTCd[2]."','".$TPSTNm[0]."','".$TPSTNm[1]."','".$TPSTNm[2]."',0,'Apps','','','','','',''";
	  performQuery($query);
	  $result["Qry"]=$query;
	} 
  }
  $result["success"]=true;
  return $result;
}
function SvTourPlan_fullmonth($Stat){
	$data= json_decode($_POST['data'], true); 
    $TPDatas=$data[0]['TPDatas'];
	$sfCode=(string) $data[0]['SFCode'];
    $sfName=(string) $data[0]['SFName'];
    $DivCodes = (string) $data[0]['DivCode'];
    $DivCode = explode(",", $DivCodes.",");
	$tpmonth=$TPDatas[0]['Tour_Month'];
	$tpyear=$TPDatas[0]['Tour_Year'];	
	$query ="update trans_tp_one set Change_Status='1' where sf_code='".$sfCode."' and Tour_Month='".$tpmonth."' and Tour_Year='".$tpyear."'"; 
	 performQuery($query);	
	$query ="update Tourplan_detail set Change_Status='1' where SFCode='".$sfCode."' and cast(Mnth as int)='".$tpmonth."' and cast(Yr as int)='".$tpyear."'"; 
	 performQuery($query);
	$result["success"]=true;
    return $result;
}
function getEntryCount() {
    $sfCode = $_GET['sfCode'];
    $eDate = $_GET['eDate'];
	
	if($eDate =='' || $eDate == null)	
    $today = date('Y-m-d 00:00:00');
	else 
	$today = date("Y-m-d 00:00:00", strtotime($eDate));

    $results = array();
    $query = "select Count(Trans_Detail_Info_Code) doctor_count from vwActivity_MSL_Details D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)";

    $temp = performQuery($query);
    $results[] = $temp[0];
    $query = "select Count(Trans_Detail_Info_Code) chemist_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=2";
 
    $temp = performQuery($query);
    $results[] = $temp[0];
    $query = "select Count(Trans_Detail_Info_Code) stockist_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=3";
    $temp = performQuery($query);
    $results[] = $temp[0];
    $query = "select Count(Trans_Detail_Info_Code) uldoctor_count from vwActivity_Unlst_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=4";
    $temp = performQuery($query);
    $results[] = $temp[0];
    $query = "select isnull((SELECT top 1 isnull(remarks,'') from vwActivity_Report where sf_code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)),'') as remarks";
    $temp = performQuery($query);
    $results[] = $temp[0];
    $query = "select isnull((SELECT top 1 Half_Day_FW from vwActivity_Report where sf_code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime)),'') as halfdaywrk";
    $temp = performQuery($query);
    $results[] = $temp[0];
    $query = "select Count(Trans_Detail_Info_Code) hospital_count from vwActivity_CSH_Detail D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=5";
    $temp = performQuery($query);
    $results[] = $temp[0];
$query = "select Count(Trans_Detail_Info_Code) cip_count from vwActivity_CIP_Details D inner join vwActivity_Report H on H.Trans_SlNo=D.Trans_SlNo where H.SF_Code='" . $sfCode . "' and cast(convert(varchar,activity_date,101) as datetime)=cast(convert(varchar,'$today',101) as datetime) and Trans_Detail_Info_Type=6";
    $temp = performQuery($query);
    $results[] = $temp[0];
    return $results;
}

function getaddress($lat, $lng) {
    $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&sensor=false';
    $json = @file_get_contents($url);
    $data = json_decode($json);
    $status = $data->status;
    if ($status == "OK") {
        return $data->results[0]->formatted_address;
    } else {
        return false;
    }
}

function updEntry() {
    $today = date('Y-m-d 00:00:00');
    $data = json_decode($_POST['data'], true);
    $SFCode = (string) $data[0]['Activity_Report']['SF_code'];
    $sql = "select SF_Code from vwActivity_report where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$today' as datetime)";

    $result = performQuery($sql);
    if (count($result) < 1) {
        $result = array();
        $result['success'] = false;
        $result['type'] = 2;
        $result['msg'] = 'No Call Report Submited...';
        outputJSON($result);
        die;
    }

    $Remarks = (string) $data[0]['Activity_Report']['remarks'];
    $HalfDy = (string) $data[0]['Activity_Report']['HalfDay_FW_Type'];


    $sql = "update DCRMain_Temp set Remarks='$Remarks',Half_Day_FW='$HalfDy' where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$today' as datetime)";
    
$result = performQuery($sql);
    $sql = "update DCRMain_Trans set Remarks='$Remarks',Half_Day_FW='$HalfDy' where sf_Code='$SFCode' and cast(activity_date as datetime)=cast('$today' as datetime)";
    $result = performQuery($sql);
    $resp["success"] = true;
    echo json_encode($resp);
}
function ViewGeoTag(){
global $data;
$data = json_decode($_POST['data'], true);
$SF=(string) $data['SF'];
$cust=(string)$data['cust'];
$query="exec getViewTag '".$SF."','".$cust ."'";
//echo $query;
return performQuery($query);
}

function Geteditdates(){
global $data;
$data = json_decode($_POST['data'], true);
$SF=(string) $data['SF'];
$Div=(string)$data['Div'];
$query="exec GetDlyReEntryDts_App '".$SF."','".$Div ."'";
//echo $query;
return performQuery($query);
}
function getFromTableWR($tableName, $coloumns, $divisionCode, $sfCode = null, $orderBy = null, $where = null, $join = null, $today=null, $wt = null) { 

    $query = "SELECT " . join(",", $coloumns) . " FROM $tableName as tab";
    if (!is_null($join)) {
        $query.=" join " . join(" join ", $join);
    }
    $query.= " WHERE tab.Division_Code=" . $divisionCode;

    if (!is_null($where)) {
        $query.=" and " . join(" or ", $where);
    }

    if (!is_null($today)) {
       $today = date('Y-m-d 00:00:00');

        $query.="and cast(tab.activity_date as datetime)=cast('$today' as datetime)";
    }

    if (!is_null($orderBy)) {
        $query .=" ORDER BY " . join(", ", $orderBy);
    }
 //echo $query;
    return performQuery($query); 
 //echo $query;
}

function getFromTable($tableName, $coloumns, $divisionCode, $sfCode = null, $orderBy = null, $where = null, $join = null, $today, $wt = null) {
 
$query = "SELECT " . join(",", $coloumns) . " FROM $tableName as tab";
    if (!is_null($join)) {
        $query.=" join " . join(" join ", $join);
    }
    if (!is_null($sfCode)) {
        $query .=" WHERE tab.SF_Code='$sfCode'";
    } else {
        $query.= " WHERE tab.Division_Code=" . $divisionCode;
    }
    if (!is_null($where)) {
        $query.=" and " . join(" and ", $where);
    }
    if (!is_null($today)) {
        //$today = date('Y-m-d 00:00:00');
        $query.=" and cast(tab.activity_date as datetime)=cast('$today' as datetime)";
    }
    if (!is_null($orderBy)) {
        $query .=" ORDER BY " . join(",", $orderBy);
    }

        
        //echo $query;
      return performQuery($query);
}
function updateEntry($sfCode) {
    $dt = date('Y-m-d');
    $sql = "select SUM(cast(Amount as int)) amt from Trans_Additional_Exp where CAST(Created_Date as date)='$dt' and Cal_Type=0 and Sf_Code='$sfCode'";
    $positiveVal = performQuery($sql);
    $sql = "select SUM(cast(Amount as int)) amt from Trans_Additional_Exp where CAST(Created_Date as date)='$dt' and Cal_Type=1 and Sf_Code='$sfCode'";
    $negativeVal = performQuery($sql);
    $updateAdditionalAmt = $positiveVal[0]['amt'] - $negativeVal[0]['amt'];
    $sql = "select Expense_Allowance,Expense_Distance,Expense_Fare,Expense_Total from  Trans_FM_Expense_Detail where CAST(Created_Date as date)='$dt' and Sf_Code='$sfCode'";
    $expenseDetail = performQuery($sql);
    $query = "delete from Trans_Additional_Exp where CAST(Created_Date as date)='$dt' and Sf_Code='$sfCode'";
    performQuery($query);
    $query = "delete from Trans_FM_Expense_Detail where CAST(Created_Date as date)='$dt' and Sf_Code='$sfCode'";
    performQuery($query);
    $query = "delete from Trans_FM_Expense_Head where CAST(snd_dt as date)='$dt' and Sf_Code='$sfCode'";
    performQuery($query);
    $sql = "select Total_Allowance,Total_Distance,Total_Fare,Total_Expense,Total_Additional_Amt,Grand_Total from Trans_Expense_Amount_Detail where Month=MONTH('$dt') and Year=YEAR('$dt') and Sf_Code='$sfCode'";
    $amountDetail = performQuery($sql);
    $Total_Allowance = $amountDetail[0]['Total_Allowance'] - $expenseDetail[0]['Expense_Allowance'];
    $Total_Distance = $amountDetail[0]['Total_Distance'] - $expenseDetail[0]['Expense_Distance'];
    $Total_Fare = $amountDetail[0]['Total_Fare'] - $expenseDetail[0]['Expense_Fare'];
    $Total_Expense = $amountDetail[0]['Total_Expense'] - $expenseDetail[0]['Expense_Total'];
    $Total_Additional_Amt = $amountDetail[0]['Total_Additional_Amt'] - $updateAdditionalAmt;
    $Grand_Total = $Total_Expense - $Total_Additional_Amt;
    $sql = "update Trans_Expense_Amount_Detail set Total_Allowance=$Total_Allowance,Total_Distance=$Total_Distance,Total_Fare=$Total_Fare,Total_Expense=$Total_Expense,Total_Additional_Amt=$Total_Additional_Amt,Grand_Total=$Grand_Total where Month=MONTH('$dt') and Year=YEAR('$dt') and Sf_Code='$sfCode'";
    performQuery($sql);
}
function deleteEntry($arc, $amc) {
    if (!is_null($amc)) {
        $sql = "DELETE FROM DCRDetail_Lst_Temp where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);
        $sql = "DELETE FROM DCRDetail_Lst_Trans where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);

        $sql = "DELETE FROM DCRDetail_CSH_Temp where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);
        $sql = "DELETE FROM DCRDetail_CSH_Trans where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);

        $sql = "DELETE FROM DCRDetail_Unlst_Temp where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);
        $sql = "DELETE FROM DCRDetail_Unlst_Trans where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);

        $sql = "delete from Trans_LdrNxtVst_Det where trans_slno='" . $amc . "'";
        performQuery($sql);
		$sql = "delete from Trans_RCPA_Head where AR_Code='" . $arc . "' and ARMSL_Code='" . $amc . "'";
        performQuery($sql);
		$sql = "delete from Trans_RCPA_Detail where DCR_id='" . $arc . "' and Dcrdetail_id='" . $amc . "'";
        performQuery($sql);
		$sql = "delete from DCR_Detail_Activity where Trans_Detail_Slno='" . $amc . "'";
        performQuery($sql);
        /* $sql = "DELETE FROM DCREvent_Captures where Trans_Detail_Slno='".$amc."'";performQuery($sql); */
    }
}

function delAREntry($SF, $WT, $Dt) {

    $sqlH = "SELECT Trans_SlNo FROM vwActivity_Report where SF_Code='" . $SF . "' and lower(Work_Type) <> lower(" . $WT . ") and cast(activity_date as datetime)=cast('$Dt' as datetime)";
    $sql = "DELETE FROM DCRDetail_Lst_Temp where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);
    $sql = "DELETE FROM DCRDetail_Lst_Trans where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);

    $sql = "DELETE FROM DCRDetail_CSH_Temp where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);
    $sql = "DELETE FROM DCRDetail_CSH_Trans where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);

    $sql = "DELETE FROM DCRDetail_Unlst_Temp where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);
    $sql = "DELETE FROM DCRDetail_Unlst_Trans where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);

    $sql = "DELETE FROM DCREvent_Captures where Trans_SlNo in (" . $sqlH . ")";
    performQuery($sql);
	$sql = "DELETE FROM DCR_Detail_Activity where Trans_Main_Sl_No in (" . $sqlH . ")";
    performQuery($sql);

    $sql = "DELETE FROM DCRMain_Temp where SF_Code='" . $SF . "' and lower(Work_Type) <> lower(" . $WT . ") and cast(activity_date as datetime)=cast('$Dt' as datetime)";
    performQuery($sql);
    $sql = "DELETE FROM DCRMain_Trans where SF_Code='" . $SF . "' and lower(Work_Type) <> lower(" . $WT . ") and cast(activity_date as datetime)=cast('$Dt' as datetime)";
    performQuery($sql);
}
function SvRCPAEntry($ARCd,$ARDCd,$mData,$RCPADt){
    global $data;
    $sfCode=$_GET['sfCode'];
    $sfName='';
    $CustCode=$mData['doctor_code'];
    $CustName='';

    $div = $_GET['divisionCode'];
    $divs = explode(",", $div . ",");
    $Owndiv = (string) $divs[0];

	$RCPADatas=$mData['RCPAEntry'];
    // $query="select 0";//isnull(Max(EID),0)+1 EID from Trans_RCPA_Head";
    // $arr = performQuery($query);
    $EID = 0; 
	for ($Ri = 0; $Ri < count($RCPADatas); $Ri++) 
    {
		$RCPAData=$RCPADatas[$Ri];
		if($CustCode==""|| $CustCode==null){
		 $CustCode=$RCPAData["doc_id"];
		}
			  
		//$Chms=$RCPAData["Chemists"];
		$ChmIds=$RCPAData["chemist_id"];
		$ChmNms=$RCPAData["chemist_name"];
			       $VstTime="";
                  $JWWrk="";
                  $lat="";
                  $lng="";
                  $DataSF="";
          if($ARDCd!="")
          {
              $query="select Trans_Detail_Slno,convert(varchar,time,20) tmv,Worked_with_Code,lati,long,DataSF,Division_code from vwActivity_MSL_Details where Trans_Detail_Slno='".$ARDCd."'";
              $arr = performQuery($query);
             
              if(count($arr[0])>0){
                  $VstTime=$arr[0]["tmv"];
                  $JWWrk=$arr[0]["Worked_with_Code"];
                  $lat=$arr[0]["lati"];
                  $lng=$arr[0]["long"];
                  $DataSF=$arr[0]["DataSF"];
              }
          }
       
          $query="exec svDCRCSHDet_App '".$ARCd."',0,'".$sfCode."','2','".$ChmIds."','".$ChmNms."','".$VstTime."',0,'".$JWWrk."','','','','','','','".$Owndiv."',0,'".$VstTime."','".$lat."','".$lng."','".$DataSF."','NA','App'";
	    		         $params = array(array($ARDCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));
      	          performQuery($query);
        $sXML="<ROOT>";
        $Comps=$RCPAData["compats"];
        for ($Rj = 0; $Rj < count($Comps); $Rj++) 
        {   
          $Comp=$Comps[$Rj];
          $sXML=$sXML."<Comp CCode=\"".$Comp["comptid"]."\" CName=\"".$Comp["comptname"]."\" CPCode=\"".$Comp["comptpbid"]."\" CPName=\"".$Comp["comptpname"]."\" CPQty=\"".$Comp["comptbqty"]."\" CPRate=\"".$Comp["comptbprice"]."\" CPValue=\"".$Comp["comptbamount"]."\" CPUnit=\"".$Comp["compunit"]."\" />";
        }
        $sXML=$sXML."</ROOT>";
			  $query="exec iOS_svRCPAEntry '".$sfCode."','".$sfName."','".$RCPADt."',".$CustCode.",'".$CustName."','".$ChmIds."','".$ChmNms."','".$RCPAData["obid"]."','".$RCPAData["obname"]."','".$RCPAData["obqty"]."','".$RCPAData["obprice"]."','".$RCPAData["tamount"]."','".$RCPAData["obunity"]."','".$ARCd."','".$ARDCd."','".$EID."','".$sXML."'";

			  performQueryWP($query,[]);
	  }
    //$result["success"]=true;
    return $result;
}


function addEntry() {  
    $sfCode = $_GET['sfCode'];
    $div = $_GET['divisionCode'];
	$MSL = $_GET['Msl_No']; 
    $divs = explode(",", $div . ",");
    $Owndiv = (string) $divs[0];
    $data = json_decode($_POST['data'], true);
    $today = date('Y-m-d 00:00:00');
    $temp = array_keys($data[0]);
    $vals = $data[0][$temp[0]];

	 $HeaderId = (isset($_GET['Head_id']) && strlen($_GET['Head_id']) == 0) ? null : $_GET['Head_id'];       
		if($HeaderId !=null)
		{
			$query = "exec Delete_reject_dcr '$HeaderId' ";
			performQuery($query); 
		}
	
    $sql = "SELECT Employee_Id,case sf_type when 1 then 'MR' else 'MGR' End SF_Type FROM Mas_Salesforce_One where SF_code='" . $sfCode . "'";
    $as = performQuery($sql);
    $IdNo = (string) $as[0]['Employee_Id'];
    $SFTyp = (string) $as[0]['SF_Type'];
    switch ($temp[0]) {
      case "tbMyDayPlan":
		if($vals["location"]==null)
		$location="";
		else
		$location=$vals["location"];
	if($vals["TP_Doctor"]== null)
		$vals["TP_Doctor"]="''";
	if($vals["TP_DocCluster"]== null)
		$vals["TP_DocCluster"]="''"; 
	if($vals["TP_Worktype"]== null)
		$vals["TP_Worktype"]="''"; 
	
		if($vals["dcr_activity_date"]!=null&&$vals["dcr_activity_date"]!=''){
			$today=str_replace("'", "", $vals["dcr_activity_date"]);
		} 		 
  
           /*$sql = "insert into tbMyDayPlan select '" . $sfCode . "'," . $vals["sf_member_code"] . ",'$today'," . $vals["cluster"] . "," . $vals["remarks"] . ",'" . $Owndiv . "'," . $vals["wtype"] . "," . $vals["FWFlg"] . "," . $vals["ClstrName"].",'','$location'"; 
 echo $sql;

	$query="exec iOS_svTodayTP '".$sfCode."','".$vals["sf_member_code"]."','".$vals["cluster"]."','".$vals["ClstrName"]."','".$vals["wtype"]."','".$vals["wtype_name"]."','".$vals["remarks"]."','".$location."'";
//echo $query;*/
		//	performQuery($query);
$sql = "insert into tbMyDayPlan select '" . $sfCode . "','" . $vals["sf_member_code"] . "','$today','" . $vals["cluster"] . "','" . $vals["remarks"] . "','" . $Owndiv . "','" . $vals["wtype"] . "','" . $vals["FWFlg"] . "','" . $vals["ClstrName"]."','" . $vals["wtype_name"] . "','$location','" . $vals["TpVwFlg"] . "','" . $vals["TP_Doctor"] . "','" . $vals["TP_DocCluster"] . "','" . $vals["TP_Worktype"] . "'";
//echo $sql;
            performQuery($sql);
			$today = date('Y-m-d 00:00:00',strtotime($today));
            if (str_replace("'", "", $vals["FWFlg"]) != "F") {
                $sql = "SELECT * FROM vwActivity_Report where SF_Code='" . $sfCode . "'  and cast(activity_date as datetime)=cast('$today' as datetime)";
                $result1 = performQuery($sql);
                if (count($result1) > 0) {
                    if ($result1[0]['FWFlg'] == 'L' && $result1[0]['Confirmed'] != 2 && $result1[0]['Confirmed'] != 3) {
$result = array();
                    $result['success'] = false;
                        $result['msg'] = 'Leave Post Already Updated';
                        outputJSON($result);
                        die;
                    } else {
                        delAREntry($sfCode, $vals["wtype"], $today);

                        $ARCd = "0";
                        $sql = "{call  svDCRMain_App(?,?," . $vals["wtype"] . ",'" . str_replace("'", "", $vals["cluster"]) . "',?,'" . str_replace("'", "", $vals["remarks"]) . "',?)}";
                        $params = array(array($sfCode, SQLSRV_PARAM_IN),
                            array($today, SQLSRV_PARAM_IN),
                            array($Owndiv, SQLSRV_PARAM_IN),
                            array(&$ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));
                        performQueryWP($sql, $params);
                    }
                } else {
                    delAREntry($sfCode, $vals["wtype"], $today);

                    $ARCd = "0";
                    $sql = "{call  svDCRMain_App(?,?," . $vals["wtype"] . ",'" . str_replace("'", "", $vals["cluster"]) . "',?,'" . str_replace("'", "", $vals["remarks"]) . "',?)}";
                    $params = array(array($sfCode, SQLSRV_PARAM_IN),
                        array($today, SQLSRV_PARAM_IN),
                        array($Owndiv, SQLSRV_PARAM_IN),
                        array(&$ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));
                    performQueryWP($sql, $params);
                }
            }
            break;
		case "Checkin": 
			$date=date('Y-m-d');
			$sql = "insert into Dcr_checkin(Cust_id,Cust_name,Sf_Code,Division_Code,Activity_date,Checkin_time,Checkin_Lat,Checkin_Long,Checkout_Lat,Checkout_Long,Status,Checkin_addrs) select '" . $vals["cust_id"] . "','" . $vals["cust_name"] . "','" . $sfCode . "','" . $Owndiv . "','$date','" . $vals["intime"] . "','" . $vals["lat"] . "','" . $vals["long"] . "','','','0','" . $vals["cust_add"] . "'"; 
			performQuery($sql);
  			break;
		case "Checkout":
			$date=date('Y-m-d');
			$dateTime=date('Y-m-d H:i');
			//	$sql="select top 1 ID from Dcr_checkin where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Checkin_time))='$date' and status ='0' order by ID desc";
			//	$sql="select TOP 1 (ID) ID from Dcr_checkin where sf_code='$sfCode' and Status=0 and Checkout_time IS NULL and Checkin_time IS NOT NULL order by ID DESC";

				$sql="select top 1 ID from Dcr_checkin where sf_code='$sfCode' and cust_id=".$vals["cust_id"]." order by ID DESC";
				$tr1=performQuery($sql);
				$id1=$tr1[0]['ID'];
				$sql1="update Dcr_checkin set Checkout_time='$dateTime',Status='1',Checkout_Lat='" . $vals["lat"] . "',Checkout_Long='" . $vals["long"] . "',Checkout_addrs='" . $vals["cust_add"] . "' where ID='$id1'";
				performQuery($sql1);
				break;
	
			case "TP_Attendance": 
				$dateTime=date('Y-m-d H:i');
				$date=date('Y-m-d');
				$lat=$vals['lat'];
				$long=$vals['long'];
				$Day_addr=$vals['address'];
				$update=$_GET['update'];
				//echo $update;
				if($update==0){
				$sql ="exec Attendance_entry '$sfCode','$Owndiv','$dateTime','$lat','$long','$date','$Day_addr'";
				//echo $sql;
				$result=performQuery($sql); 
				}
				else{
				$sql="select id from TP_Attendance_App where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='$date' order by id desc";
				$tr=performQuery($sql);
				$id=$tr[0]['id'];

				$sql="update TP_Attendance_App set End_Lat=$lat,End_Long=$long,End_Time='$dateTime',End_addres='$Day_addr' where id=$id";
				performQuery($sql);

				$sql1="select ID from Attendance_history where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='$date' order by id desc";
				$tr1=performQuery($sql1);
				$id1=$tr1[0]['ID'];
				$sql1="update Attendance_history set End_Lat=$lat,End_Long=$long,End_Time='$dateTime',End_addres='$Day_addr' where ID=$id1";
				performQuery($sql1);
				$result=[];
				$result["msg"]="1";
				}

				outputJSON($result);
				die;
             break;
        case "chemists_master":
            $sql = "SELECT isNull(max(Chemists_Code),0)+2 as RwID FROM Mas_Chemists";
            $tRw = performQuery($sql);
            $pk = (int) $tRw[0]['RwID'];

            $sql = "insert into Mas_Chemists(Chemists_Code,Chemists_Name,Chemists_Address1,Territory_Code,Chemists_Phone,Chemists_Contact,Division_Code,Cat_Code,Chemists_Active_Flag,Sf_Code,Created_Date,Created_By) select '" . $pk . "'," . $vals["chemists_name"] . "," . $vals["Chemists_Address1"] . "," . $vals["town_code"] . "," . $vals["Chemists_Phone"] . ",'','" . $Owndiv . "','','0','" . $sfCode . "','" . date('Y-m-d H:i:s') . "','Apps'";
            performQuery($sql);
			//echo $sql;
            break;
		case "Expense_miscellaneous": 
			for($i=0;$i<count($vals);$i++){
					/*	$sql = "SELECT isNull(max(Sl_No),0)+1 as RwID FROM Exp_miscellaneous_zoom";
			            $tRw = performQuery($sql);
			            $pk = (int) $tRw[0]['RwID'];*/
					$sql1 = "insert into Exp_miscellaneous_zoom (Expense_typ,Expense_Date,Expense_Parameter_Code,Expense_Parameter_Name,Amt,SF_Code,Expense_month,	expense_year,Division_Code) select '".$vals[$i]['Expense_type']."','".$vals[$i]['Expense_date']."','".$vals[$i]['Expense_Parameter_Code']."','".$vals[$i]['Expense']."','".$vals[$i]['amount']."','" . $sfCode . "','".$vals[$i]['Expense_month']."','".$vals[$i]['Expense_year']."','" . $Owndiv . "'";
			performQuery($sql1);
			}
		break;
  case "unlisted_doctor_master":
            $sql = "SELECT isNull(max(UnListedDrCode),0)+1 as RwID FROM Mas_UnListedDr";
            $tRw = performQuery($sql);
            $pk = (int) $tRw[0]['RwID'];
			if($vals["unlisted_doctor_pincode"] == null || $vals["unlisted_doctor_pincode"]=="undefined" )
			{
				$vals["unlisted_doctor_pincode"]="''";
				$vals["unlisted_doctor_mobileno"]="''";
				$vals["unlisted_doctor_email"]="''";
				
			}
			if($vals["unlisted_doctor_addr1"] == null || $vals["unlisted_doctor_addr1"]=="undefined" )
			{
				$vals["unlisted_doctor_addr1"]="''";
				$vals["unlisted_doctor_addr2"]="''";
			}

            $sql = "insert into Mas_UnListedDr(UnListedDrCode,UnListedDr_Name,UnListedDr_Address1,UnListedDr_Address2,Doc_Special_Code,Doc_Cat_Code,Territory_Code,UnListedDr_Active_Flag,UnListedDr_Sl_No,Division_Code,SLVNo,Doc_QuaCode,Doc_ClsCode,Sf_Code,UnListedDr_Created_Date,Created_By,UnListedDr_PinCode,UnListedDr_Phone,UnListedDr_Mobile,UnListedDr_Email,UnListedDr_DOB,UnListedDr_Address3) select '" . $pk . "'," . $vals["unlisted_doctor_name"] . "," . $vals["unlisted_doctor_addr1"] . "," . $vals["unlisted_doctor_addr2"] . "," . $vals["unlisted_specialty_code"] . "," . $vals["unlisted_cat_code"] . "," . $vals["town_code"] . ",0,'" . $pk . "','" . $Owndiv . "','" . $pk . "'," . $vals["unlisted_qulifi"] . "," . $vals["unlisted_class"] . ",'" . $sfCode . "','" . date('Y-m-d H:i:s') . "','Apps'," . $vals["unlisted_doctor_pincode"] . "," . $vals["unlisted_doctor_mobileno"] . "," . $vals["unlisted_doctor_mobileno"] . "," . $vals["unlisted_doctor_email"] . "," . $vals["unlisted_doctor_dob"] . "," . $vals["unlisted_doctor_city"] . "";
			//echo $sql;
            performQuery($sql);
            break;
		case "Quiz_Results":
			$quizresults=$vals[0];
			$first=$vals[1][0];
			$surveyId=$first['survey_id'];
			$firstStartTime=$first['start'];
			$firstEndTime=$first['end'];				 
			
			if($first['NoOfAttempts']=="2"){
				$second=$vals[2][0];
				$secStartTime=$second['start'];
				$secEndTime=$second['end'];
			}else{
				$secStartTime="";
				$secEndTime="";
			}

			for($i=0;$i<count($quizresults);$i++){
				$quesid=$quizresults[$i]['Question_Id'];
				$inputid=$quizresults[$i]['input_id'];
				$secinputid=$quizresults[$i]['Sec_input_id'];
				$sql1="select isnull(max(max_sl_no),0)+1 id from Quiz_MaxSlNo where sf_code='$sfCode'";
				$tr=performQuery($sql1);
				$id=$tr[0]['id'];
				$code=$sfCode.'-'.$id;
				$sql1="select sf_name sfName from mas_salesforce where sf_code='$sfCode'";
				$tr=performQuery($sql1);
				$sfName=$tr[0]['sfName'];

			if($id=="1"){
				$sql1="insert into Quiz_MaxSlNo select '$sfCode',$Owndiv,$id";
				performQuery($sql1);
			}
			else{
				$sql1="update Quiz_MaxSlNo set max_sl_no=$id where sf_code='$sfCode'";
				performQuery($sql1);
			}

				$sql1="delete from quiz_result where Sf_Code='$sfCode' and Quiz_Id='$quesid' and Division_Code='$Owndiv' and Survey_Id='$surveyId'";
				performQuery($sql1);
				$sql1="insert into quiz_result(Result_Id,Sf_Code,Sf_Name,Division_Code,Quiz_Id,Input_Id,Status,Survey_Id,Created_Date,Second_Input_Id,First_Start_time,First_End_time,Second_Start_time,Second_End_time) select'$code','$sfCode','$sfName','$Owndiv','$quesid','$inputid',0,'$surveyId',getdate(),'$secinputid','$firstStartTime','$firstEndTime','$secStartTime','$secEndTime'";
				performQuery($sql1);
				//echo $sql1;
				$sql1="insert into trackquiz_result(Result_Id,Sf_Code,Sf_Name,Division_Code,Quiz_Id,Input_Id,Status,Survey_Id,Created_Date,Second_Input_Id,First_Start_time,First_End_time,Second_Start_time,Second_End_time) select
				'$code','$sfCode','$sfName','$Owndiv','$quesid','$inputid',0,'$surveyId',getdate(),'$secinputid','$firstStartTime','$firstEndTime','$secStartTime','$secEndTime'";
				performQuery($sql1);

			}
				$sql="update Processing_UserList set Process_Status='F' where SurveyId='$surveyId' and sf_code='$sfCode'";           	   
				performQuery($sql);
				$result['success'] = true;
				return outputJSON($result);
			break;


case "MCL_Details":

$primary_key="ListedDrCode";
$row_id=$data[0]['MCL_Details']['doctorCode'];
$data[0]['MCL_Details']['Update_Mode']="'Apps'";
//$data[0]['MCL_Details']['Visiting_Card']="'"."~/Visiting_Card/".str_replace("'", "", $data[0]['MCL_Details']['Visiting_Card'])."'";
unset($data[0]['MCL_Details']['doctorCode']);
unset($data[0]['MCL_Details']['workPlace']);
unset($data[0]['MCL_Details']['Pri_Appt_Meet']);
unset($data[0]['MCL_Details']['Drs_Meet_Day']);
unset($data[0]['MCL_Details']['Add_Hos_Nur']);
unset($data[0]['MCL_Details']['email']);
unset($data[0]['MCL_Details']['mobile']);
 foreach ($data[0]['MCL_Details'] as $col => $val) {
//$val=str_replace("''","",$val);
            $cols[] = $col . " = " . $val;
//            $values[] = $val;
        }

        $sql = "UPDATE Mas_ListedDr set "
                . join(", ", $cols)
                . " where $primary_key = $row_id";

    performQuery($sql);
//$result = array();
                   // $result['success'] = false;
//$result['msg'] = "dsjdjhd";
 //outputJSON($result);
// die;
break;
case "savecamp_approval":

 	 $sql = "SELECT isNull(max(Trans_sl_No),0)+1 as RwID FROM Trans_opd_camp_approval";
            $tRw = performQuery($sql);
            $pk = (int) $tRw[0]['RwID'];

		$div = $vals["Division_Code"];
		        $divs = explode(",", $div . ",");
 $Owndiv = (string) $divs[0];



		$query="insert into Trans_opd_camp_approval(Trans_sl_No,Division_Code,Camp_Name,Camp_Code,Camp_Type,Doctor_Name,Doctor_Code,Date_Camp,Place_Camp,Expected_Patients,Exp_Bussiness,ROI_From_Month,ROI_From_Year,ROI_To_Month,ROI_To_Year,Entry_Date,Sf_Code,Sf_Name,Entry_Sf_Code,Entry_Sf_name,Camp_Status,Entry_Mode)
		select '$pk','$Owndiv','" . $vals["Camp_Name"] . "','" . $vals["Camp_Code"] . "','" . $vals["Camp_Type"] . "','" . $vals["Doctor_Name"] . "','" . $vals["Doctor_Code"] . "','" . $vals["Date_Camp"] . "','" . $vals["Place_Camp"] . "','" . $vals["Expected_Patients"] . "','" . $vals["Exp_Bussiness"] . "',
		'" . $vals["ROI_From_Month"] . "','" . $vals["ROI_From_Year"] . "','" . $vals["ROI_To_Month"] . "','" . $vals["ROI_To_Year"] . "','" . $vals["Entry_Date"] . "','" . $vals["Sf_Code"] . "','" . $vals["Sf_Name"] . "','" . $vals["Entry_Sf_Code"] . "','" . $vals["Entry_Sf_name"] . "','0','Apps'";

		performQuery($query);
//echo $query;
		$query1="update Map_OPDCamp_Drs_Details set doccamp_flag='1' where SF_Code='" . $vals["Sf_Code"] . "' and OPD_Code='" . $vals["Camp_Code"] . "' and DRCode='" . $vals["Doctor_Code"] . "'";

 		performQuery($query1);
 
break;

case "savecamp_cme_approval":

 	 $sql = "SELECT isNull(max(Trans_sl_No),0)+1 as RwID FROM Trans_opd_camp_approval";
            $tRw = performQuery($sql);
            $pk = (int) $tRw[0]['RwID'];

			$query="insert into Trans_opd_camp_approval(Trans_sl_No,Camp_Name,Camp_Code,Camp_Type,CME_Participant_List,CME_Date,CME_Venue,CME_Start_Date,CME_End_Date,
			CME__Other_Speaker_Name,CME_Speaker_Code,CME_Speaker_Name,Entry_Date,Sf_Code,Sf_Name,Entry_Sf_Code,Entry_Sf_name,Camp_Status,Entry_Mode)
			select '$pk','" . $vals["Camp_Name"] . "','" . $vals["Camp_Code"] . "','" . $vals["Camp_Type"] . "','" . $vals["CME_Participant_List"] . "','" . $vals["CME_Date"] . "','" . $vals["CME_Venue"] . "','" . $vals["CME_Start_Date"] . "','" . $vals["CME_End_Date"] . "','" . $vals["CME__Other_Speaker_Name"] . "',
			'" . $vals["CME_Speaker_Code"] . "','" . $vals["CME_Speaker_Name"] . "','" . $vals["Entry_Date"] . "','" . $vals["Sf_Code"] . "','" . $vals["Sf_Name"] . "','" . $vals["Entry_Sf_Code"] . "','" . $vals["Entry_Sf_name"] . "','0','Apps'";
			//echo $query;
			performQuery($query);

		//$query1="update Map_OPDCamp_Drs_Details set doccamp_flag='1' where SF_Code='" . $vals["Sf_Code"] . "' and OPD_Code='" . $vals["Camp_Code"] . "' and DRCode='" . $vals["Doctor_Code"] . "'";

 		performQuery($query1);
 
break;

case "Camp_TagApproval":
 
   	$div = $_GET['divisionCode'];
 //	$campcode = $_GET['campcode'];
    $divs = explode(",", $div . ",");
	$data = json_decode($_POST['data'], true);
	$tag_data=$data[0]['Camp_TagApproval'];
//	echo count($tag_data);
//	print_r($tag_data[0]['SF_Code']);
	$query2="delete from  Map_OPDCamp_Drs_Details where OPD_Code='" . $tag_data[0]['OPD_Code'] . "' and  Division_Code='" . $tag_data[0]['Division_Code'] . "'";
	performQuery($query2); 


	for($i=0;$i<count($tag_data);$i++)
	 {
	$query="update mas_listeddr set Doc_SubCatCode=replace(Doc_SubCatCode,'," . $tag_data[$i]['OPD_Code'] . ",',',')+'" . $tag_data[$i]['OPD_Code'] . ",' where ListedDrCode='" . $tag_data[$i]['DRCode'] . "'";
	performQuery($query); 
	$query="insert into Map_OPDCamp_Drs_Details(SF_Code,OPD_Code,DRCode,Active_Flag,Division_Code,Map_Date,ApproveDt,doccamp_flag)
	select '". $tag_data[$i]['SF_Code'] . "','" . $tag_data[$i]['OPD_Code'] . "','" . $tag_data[$i]['DRCode'] . "','" . $tag_data[$i]['Active_Flag'] . "','" . $tag_data[$i]['Division_Code'] . "','" . $tag_data[$i]['Map_Date'] . "',null,'0'";
	performQuery($query); 
	} 
	$query1="select * from  mas_campaign_lock where Doc_SubCatCode='" . $tag_data[0]['OPD_Code'] . "' and  Division_Code='" . $tag_data[0]['Division_Code'] . "'";
	$result=performQuery($query1);

	if(count($result)>0){
	$query2="update  mas_campaign_lock set Campaign_Lock_flag='1' where Doc_SubCatCode='" . $tag_data[0]['OPD_Code'] . "' and  Division_Code='" . $tag_data[0]['Division_Code'] . "'";
	performQuery($query2); 
	}
	else{
	$query3="insert into mas_campaign_lock(SF_Code,Division_Code,Campaign_Lock_flag,Doc_SubCatCode,Camp_Mode,Entry_Mode,Entry_Mode_ref)
	select '". $tag_data[0]['SF_Code'] . "','" . $tag_data[0]['Division_Code'] . "','1','" . $tag_data[0]['OPD_Code'] . "','Campaign','Apps','TR'";
	performQuery($query3); 
	}


	break;


case "approvereject_tagcamp":
//echo hi;
 	$div = $_GET['divisionCode'];
 	$divs = explode(",", $div . ",");
	$mode = $_GET['mode'];
	$OPD_Code = $_GET['OPD_Code'];
	//$data = json_decode($_POST['data'], true);
	//$tag_data=$data[0]['Camp_TagApproval'];
	if($mode=='approve'){
	$query1="update  mas_campaign_lock set Campaign_Lock_flag='2' where Doc_SubCatCode='$OPD_Code' and  Division_Code='$div'";
	//echo $query1;
	performQuery($query1);
 	//	$result['msg'] = 'Doctors approved';
	}else{
	$query2="update  mas_campaign_lock set Campaign_Lock_flag='3'where Doc_SubCatCode='$OPD_Code' and  Division_Code='$div'";
	performQuery($query2);
 //	$result['msg'] = 'Doctors rejected';
	}

break;
case "approvereject_camp":
 	$div = $_GET['divisionCode'];
 	$divs = explode(",", $div . ",");
	$mode = $_GET['mode'];
	$Camp_Code = $_GET['Camp_Code'];
	$Sf_code = $_GET['Sf_code'];
	$Camp_Type = $_GET['Camp_Type'];
	$dr_code = $_GET['dr_code'];
	if($mode=='approve'){
	$query1="update  Trans_opd_camp_approval set Camp_Status='1' where Division_Code='$div' and Camp_Code='$Camp_Code' and Sf_code='$Sf_code' and Camp_Type='$Camp_Type'";
	performQuery($query1);
$query2="update Map_OPDCamp_Drs_Details set doccamp_flag='2' where SF_Code='$Sf_code' and OPD_Code='$Camp_Code' and DRCode='$dr_code'";

performQuery($query2);
//echo $query2;
	}else{
	$query2="update  Trans_opd_camp_approval set Camp_Status='2' where Division_Code='$div' and Camp_Code='$Camp_Code' and Sf_code='$Sf_code' and Camp_Type='$Camp_Type'";
	performQuery($query2);
	$query1="update Map_OPDCamp_Drs_Details set doccamp_flag='3' where SF_Code='$Sf_code' and OPD_Code='$Camp_Code' and DRCode='$dr_code'";
	performQuery($query1);
//echo $query2;
	}

break;


case "Map_GEO_Customers":
					$addr="'".getaddress(str_replace("'","",$vals["lat"]),str_replace("'","",$vals["long"]))."'";
					$sql = "SELECT isNull(max(MapId),0)+1 as MapId FROM Map_GEO_Customers";
					//echo $sql; 
                    $topr = performQuery($sql);
                    $pk = (int) $topr[0]['MapId'];

             $sql = "insert into Map_GEO_Customers(MapId, Cust_Code, lat, long, addrs, StatFlag, Division_code) select $pk,".$vals["Cust_Code"].",".$vals["lat"].",".$vals["long"].",".$addr.",".$vals["StatFlag"].",$Owndiv";
            performQuery($sql); 
     
					break;

        case "tbRCPADetails":
            $sql = "insert into tbRCPADetails select '" . $sfCode . "','" . date('Y-m-d H:i:s') . "'," . $vals["RCPADt"] . "," .
                    $vals["ChmId"] . "," . $vals["DrId"] . "," . $vals["CmptrName"] . "," . $vals["CmptrBrnd"] . "," . $vals["CmptrPriz"] . "," .
                    $vals["ourBrnd"] . "," . $vals["ourBrndNm"] . "," . $vals["Remark"] . ",'" . $div . "'," . $vals["CmptrQty"] . "," . $vals["CmptrPOB"] . "," . $vals["ChmName"] . "," . $vals["DrName"]."";
            performQuery($sql);
            break;
        case "tbRemdrCall":
            $sql = "SELECT isNull(max(cast(replace(RwID,'RC/" . $IdNo . "/','') as numeric)),0)+1 as RwID FROM tbRemdrCall where RwID like 'RC/" . $IdNo . "/%'";
            $tRw = performQuery($sql);
			//echo $sql;
            $pk = (int) $tRw[0]['RwID'];

              $sql = "insert into tbRemdrCall(RwID,SF_Code,CallDate,ListedDrCode,WWith,WWithNm,Prods,ProdsNm,Remarks,location,Division_Code) select 'RC/" . $IdNo . "/" . $pk . "','" . $sfCode . "','" . date('Y-m-d H:i:s') . "','" . $vals["Doctor_ID"] . "','" .$vals["WWith"] . "','" . $vals["WWithNm"] . "','" . $vals["Prods"] . "','" . $vals["ProdsNm"] . "','" . $vals["Remarks"] . "','" .$vals["location"] . "','" . $div . "'"; 
			 //$sql = "insert into tbRemdrCall values ('RC/" . $IdNo . "/" . $pk . "','" . $sfCode . "','" . date('Y-m-d H:i:s') . "','" . $vals["Doctor_ID"] . "','" .$vals["WWith"] . "','" . $vals["WWithNm"] . "','" . $vals["Prods"] . "','" . $vals["ProdsNm"] . "','" . $vals["Remarks"] . "','" .$vals["location"] . "','" . $div . "')"; 
            performQuery($sql);
			   
            break;

	 
  case "expense":
            $res = $data[0]['expense'];
            $date = date('Y-m-d H:i:s');
            $update = $_GET['update'];
            $dcrdate = date('d-m-Y');

            $divCode = $_GET['divisionCode'];
            $divisionCode = explode(",", $divCode);
            $desig = $_GET['desig'];
            $sfCode = $_GET['sfCode'];
            $sfName = $res['sfName'];
            $expenseAllowance = $res['allowance'];
            $expenseDistance = $res['distance'];
            $expenseFare = $res['fare'];
            $total = $res['tot'];
            $additionalTot = $res['additionalTot'];
            $wcode = $res['worktype'];
            $wname = $res['worktype_name'];
            $place = $res['place'];
            $placeno = $res['placeno'];
            $sql = "SELECT isNull(max(sl_no),0)+1 as RwID FROM Trans_FM_Expense_Head";
            $tRw = performQuery($sql);
            $pk = (int) $tRw[0]['RwID'];
            if ($update == 1) {
                updateEntry($sfCode);
            }

            $sql = "insert into Trans_FM_Expense_Head(Sf_Code,Month,Year,sndhqfl,Division_Code,snd_dt,Sf_Name) select '$sfCode',MONTH('$date'),YEAR('$date'),0,$divisionCode[0],'$date'," . $sfName . "";
          
  performQuery($sql);

            $sql = "insert into Trans_FM_Expense_Detail(DCR_Date,Expense_wtype_Code,Expense_wtype_Name,Place_of_Work,Expense_Place_No,Division_Code,Expense_Allowance,Expense_Distance,Expense_Fare,Created_Date,LastUpdt_Date,Sf_Name,Sf_Code,Expense_Total) select '$dcrdate',$wcode,$wname,$place,$placeno,$divisionCode[0],$expenseAllowance,$expenseDistance,$expenseFare,'$date','$date',$sfName,'$sfCode',$total";
            performQuery($sql);

            $sql = "SELECT * FROM Trans_Expense_Amount_Detail where Month=MONTH('$date') and year=YEAR('$date') and Sf_Code='$sfCode'";
            $tRw = performQuery($sql);
            if (empty($tRw)) {
                $additionalAmount = $additionalTot + $total;
                $sql = "insert into Trans_Expense_Amount_Detail(Sf_Code,Month,Year,Division_Code,Sf_Name,Total_Allowance,Total_Distance,Total_Fare,Total_Expense,Total_Additional_Amt,Grand_Total) select '$sfCode',MONTH('$date'),YEAR('$date'),$divisionCode[0], $sfName,$expenseAllowance,$expenseDistance,$expenseFare,$total,$additionalTot,$additionalAmount";
                performQuery($sql);
            } else {
                $totAllowance = $tRw[0]['Total_Allowance'] + $expenseAllowance;
                $totDistance = $tRw[0]['Total_Distance'] + $expenseDistance;
                $totFare = $tRw[0]['Total_Fare'] + $expenseFare;
                $totalExpense = $tRw[0]['Total_Expense'] + $total;
                $totAdditionalAmt = $tRw[0]['Total_Additional_Amt'] + $additionalTot;
                $grandTotal = $totalExpense + $totAdditionalAmt;
                $slNo = $tRw[0]['sl_no'];
                $sql = "update Trans_Expense_Amount_Detail set Total_Allowance=$totAllowance,Total_Distance=$totDistance,Total_Fare=$totFare,Total_Expense=$totalExpense,Total_Additional_Amt=$totAdditionalAmt,Grand_Total=$grandTotal where Sl_No='$slNo'";
              
 performQuery($sql);
            }
            $extraDet = $res['extraDetails'];
            for ($i = 0; $i < count($extraDet); $i++) {
                $parameterName = $extraDet[$i]['parameter'];

                $amount = $extraDet[$i]['amount'];
                $type = $extraDet[$i]['type'];
                if ($type == true)
                    $type = 0;
                else
                    $type = 1;
                if (!empty($parameterName))
                    $sql = "insert into Trans_Additional_Exp(Sf_Code,Month,Year,Division_Code,Created_Date,LastUpdt_Date,Created_By,Parameter_Name,Amount,Cal_Type,Confirmed) select '$sfCode',MONTH('$date'),YEAR('$date'),$divisionCode[0],'$date','$date','$sfCode','$parameterName','$amount','$type',0";
                performQuery($sql);
            }
            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
    
        case "Tour_Plan":
            $divCode = $_GET['divisionCode'];
            $divisionCode = explode(",", $divCode);
            $desig = $_GET['desig'];
            $objective = $data[0]['Tour_Plan']['objective'];
            $tourDate = $data[0]['Tour_Plan']['Tour_Date'];
            $worktype_code = $data[0]['Tour_Plan']['worktype_code'];
            $worktype_name = $data[0]['Tour_Plan']['worktype_name'];
            $worktype_code2 = $data[0]['Tour_Plan']['worktype_code2'];
            $worktype_name2 = $data[0]['Tour_Plan']['worktype_name2'];
            $worktype_code3 = $data[0]['Tour_Plan']['worktype_code3'];
            $worktype_name3 = $data[0]['Tour_Plan']['worktype_name3'];
            $worked_with_code = $data[0]['Tour_Plan']['Worked_with_Code'];
            $worked_with_name = $data[0]['Tour_Plan']['Worked_with_Name'];
            $RouteCode = $data[0]['Tour_Plan']['RouteCode'];
            $RouteName = $data[0]['Tour_Plan']['RouteName'];
            $RouteCode2 = $data[0]['Tour_Plan']['RouteCode2'];
            $RouteName2 = $data[0]['Tour_Plan']['RouteName2'];
            $RouteCode3 = $data[0]['Tour_Plan']['RouteCode3'];
            $RouteName3 = $data[0]['Tour_Plan']['RouteName3'];
            $sfName = $data[0]['Tour_Plan']['sfName'];
            $sql = "delete from Trans_TP_One WHERE SF_Code ='" . $sfCode . "' and Tour_Date=cast($tourDate as datetime)";
            //  print_r($sql);die;
            performQuery($sql);
            $sql = "insert into Trans_TP_One(SF_Code,Tour_Month,Tour_Year,Submission_date,Tour_Date,WorkType_Code_B,Worktype_Name_B,Territory_Code1,Objective,Worked_With_SF_Code,Division_Code,Tour_Schedule1,Worked_With_SF_Name,TP_Sf_Name,Confirmed,Territory_Code2,Tour_Schedule2,Territory_Code3,Tour_Schedule3,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,Change_Status) select '" . $sfCode . "',MONTH($tourDate),YEAR($tourDate),'" . date('Y-m-d') . "',$tourDate,$worktype_code,$worktype_name,$RouteCode,$objective,$worked_with_code," . $divisionCode[0] . ",$RouteName,$worked_with_name,$sfName,0,$RouteCode2,$RouteName2,$RouteCode3,$RouteName3,$worktype_code2,$worktype_name2,$worktype_code3,$worktype_name3,0";
            performQuery($sql);
            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
        case "TourPlanSubmit":
            $month = $_GET['month'];
            $year = $_GET['year'];
            $sql = "update Trans_TP_One set Change_Status=1 where Tour_Month=$month and Tour_Year=$year and Sf_Code='$sfCode'";
            performQuery($sql);
            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
		 case "DevApproval":
			$slno = $_GET['slno'];
			$sql = "update DCR_MissedDates set status=4 where sl_no='$slno'";		
			performQuery($sql);
		 break;	
        case "TPApproval":
            $month = $_GET['month'];
            $year = $_GET['year'];
            $code = $_GET['code'];

			global $data,$conn,$NeedRollBack;
			if ( sqlsrv_begin_transaction( $conn ) === false ) {
			die( print_r( sqlsrv_errors(), true ));
			}
			$NeedRollBack=false;

            //$sql = "delete from Trans_TP where sf_Code='$code' and Tour_Month=$month and Tour_Year=$year";
            //performQuery($sql);

            $sql = "insert into Trans_TP(Division_Code,SF_Code,Worked_With_SF_Code,Worked_With_SF_Name,Tour_Date,Tour_Month,Tour_Year,WorkType_Code_B,Worktype_Name_B,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,Objective,Confirmed,Confirmed_Date,Rejection_Reason,Territory_Code1,Territory_Code2,Territory_Code3,TP_Sf_Name,TP_Approval_MGR,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,Submission_date,Change_Status)
		                          select Division_Code,SF_Code,Worked_With_SF_Code,Worked_With_SF_Name,Tour_Date,Tour_Month,Tour_Year,WorkType_Code_B,Worktype_Name_B,WorkType_Code_B1,Worktype_Name_B1,WorkType_Code_B2,Worktype_Name_B2,Objective,1,GETDATE(),Rejection_Reason,Territory_Code1,Territory_Code2,Territory_Code3,TP_Sf_Name,TP_Approval_MGR,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,Submission_date,1
		from Trans_TP_One where sf_Code='$code' and Tour_Month=$month and Tour_Year=$year";
//echo $sql;
            $trs = performQuery($sql);

			if($NeedRollBack==true ){
		    sqlsrv_rollback( $conn );
		     	$result["success"]=false;
		    }else{
		    sqlsrv_commit( $conn );
		     	$result["success"]=true;
		    }



           if (count($trs) > 0) {
                $sql = "delete from Trans_TP_One where sf_Code='$code' and Tour_Month=$month and Tour_Year=$year";
                performQuery($sql);

if($month=="12"){
$year=$year+1;
$month=1;
}
else
$month=$month+1;
$date=$year.'-'.$month.'-01';
$sql = "update mas_salesforce_dcrtpdate set Last_TP_Date='$date' where sf_Code='$code' and '$date'>Last_TP_Date";
            performQuery($sql);

            }

            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
        case "TPReject":
            $month = $_GET['month'];
            $year = $_GET['year'];
            $code = $_GET['code'];
            $sql = "insert into TP_Reject_B_Mgr(SF_Code,Tour_Month,Tour_Year,Reject_date,Division_Code,Rejection_Reason) select '" . $code . "',$month,$year,'" . date('Y-m-d H:i') . "',$Owndiv," . $vals['reason'] . "";
            performQuery($sql);

            $sql = "update Trans_TP_One set Change_Status=2,Confirmed=0,Rejection_Reason=" . $vals['reason'] . " where Tour_Month=$month and Tour_Year=$year and Sf_Code='$code'";
            performQuery($sql);
            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
        case "LeaveApproval":
            $leaveid = $_GET['leaveid'];
            $RSF = $_GET['sfCode'];
			$sql = "exec iOS_svLeaveAppRej  '" . $leaveid  . "','0','','".$RSF."','Apps'";
			//echo $sql;
			performQuery($sql);
            $resp["success"] = true;
            echo json_encode($resp);die;
            break;
          /*  $leaveid = $_GET['leaveid'];
            $sql = "update Mas_Leave_Form set Leave_Active_Flag=0 where Leave_Id=$leaveid";
            performQuery($sql);
            $sql = "SELECT sf_type FROM Mas_Salesforce_One where Sf_Code='" . $vals['Sf_Code'] . "'";
            $sfType = performQuery($sql);
            $days = $vals['No_of_Days'];
            $date = preg_split("[/]", $vals['From_Date']);
            $date = $date[2] . "-" . $date[1] . "-" . $date[0];
            for ($i = 1; $i <= $days; $i++) {
                $query = "exec ChkandPostLeaveDt 0,'" . $vals['Sf_Code'] . "'," . $sfType[0]['sf_type'] . ",$Owndiv,'$date','','apps'";
                $results = performQuery($query);
                $date = date('Y-m-d', strtotime($date . ' + 1 days'));
            }*/ 
        case "LeaveReject":
            global $data;
			$SF=(string) $vals['Sf_Code'];
			$LvID=(string) $_GET['leaveid'];
			$query="exec iOS_svLeaveAppRej  '".$LvID."','1','".$vals['reason']."','".$SF."'";
			performQuery($query);
			$result["Qry"]=$query;
			$result["success"]=true;
			$msg ="Your leave request is rejected for ".$vals['reason']."";			
			send_notification_body($SF,$msg,0);
			//return $result;

            /*$leaveid = $_GET['leaveid'];
            $sql = "update Mas_Leave_Form set Leave_Active_Flag=1,Rejected_Reason=" . $vals['reason'] . " where Leave_Id=$leaveid";
            performQuery($sql);
            $sql = "SELECT sf_type FROM Mas_Salesforce_One where Sf_Code='" . $vals['Sf_Code'] . "'";
            $sfType = performQuery($sql);
            $days = $vals['No_of_Days'];
            $date = preg_split("[/]", $vals['From_Date']);
            $date = $date[2] . "-" . $date[1] . "-" . $date[0];
            for ($i = 1; $i <= $days; $i++) {
                $query = "exec ChkandPostLeaveDt 0,'" . $vals['Sf_Code'] . "'," . $sfType[0]['sf_type'] . ",$Owndiv,'$date','','apps'";
                $results = performQuery($query);
                $date = date('Y-m-d', strtotime($date . ' + 1 days'));
            }*/
            break;
        case "LeaveForm":
            $name = $_GET['sf_name'];

             $sql = "SELECT isNull(max(Leave_Id),0)+1 as RwID FROM Mas_Leave_Form";
            $tRw = performQuery($sql);
            $pk = (int) $tRw[0]['RwID'];
			if($vals['Leave_Type']=='' || $vals['Leave_Type']==null)
			{
				 
				die;
			}
 $query="exec iOS_svLeaveApp '".$sfCode."','" . $vals['From_Date'] . "','".$vals['To_Date']."','".$vals['No_of_Days']."','".$vals['Leave_Type']."','".$vals['Reason']."','".$vals['address']."'";
//echo $query;
    performQuery($query);
            $sql = "SELECT DeviceRegId FROM Access_Table where sf_code=(select Reporting_To_SF from mas_salesforce_one where Sf_Code='$sfCode')";
            $device = performQuery($sql);
            $reg_id = $device[0]['DeviceRegId'];
            if (!empty($reg_id)) {
                //   $msg = $name . " Applied Leave for " . $vals['No_of_Days'] . " days";
                $msg = "Leave Application Received";
				send_notification_body($sfCode,$msg,0);
                //send_gcm_notify($reg_id, $msg);
            }
            $sql = "SELECT sf_type FROM Mas_Salesforce_One where Sf_Code='$sfCode'";
            $sfType = performQuery($sql);
            $days = $vals['No_of_Days'];
            $date = $vals['From_Date'];
            for ($i = 1; $i <= $days; $i++) {
                $query = "exec ChkandPostLeaveDt 0,'$sfCode'," . $sfType[0]['sf_type'] . ",$Owndiv,'$date','','apps'";
                $results = performQuery($query);
                $date = date('Y-m-d', strtotime($date . ' + 1 days'));
            } 
            break;

		case "RCPAEntry" :
			$mData = json_decode($_POST['data'], true);
			$RCPADt = date('Y-m-d 00:00:00',strtotime($today));
			$ARCD='';
			$ARDCd=0;
			$sql = "{call  svDCRMain_App(?,?,'-1','',?,'',?)}";
            $params = array(array($sfCode, SQLSRV_PARAM_IN),
                array($today, SQLSRV_PARAM_IN),
                array($Owndiv, SQLSRV_PARAM_IN),
				array(&$ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));
            performQueryWP($sql, $params);
			SvRCPAEntry($ARCd,$ARDCd,$mData[0]["RCPAEntry"],$RCPADt);
            break;
	case "Order_Product":
		$sfCode = $_GET['sfCode'];
		$sfName = $_GET['sfName'];
		$div = $_GET['divisionCode'];
        $divs = explode(",", $div . ",");
        $Owndiv = (string) $divs[0];	
		$ord_date=date('Y-m-d H:i:s');
		$pData = json_decode($_POST['data'], true);

		$sql="select isnull(max(Trans_SlNo),0)+1 sl_no from Trans_Order_Book_Head";
		$tr=performQuery($sql);
		$trans_slh=$tr[0][sl_no];
		$orderData=$pData[0]["Order_Product"];
$ordmonth= $orderData['order_date'];
		$orderDetail = $pData[1]["Order_Product_Details"]; 
$sql = "insert into Trans_Order_Book_Head (Sf_Code,Sf_Name,Division_Code,Stockist_Code,Stockist_Name,Mode_of_Order,DHP_Code,DHP_Name,Sub_Div_Code,Order_Date,Order_Month,Order_Year,Entry_Mode,Created_Date,Order_Flag,Order_type) select '$sfCode','$sfName','$Owndiv','".$orderData["Stockist_id"]."','".$orderData["Stockist_name"]."','".$orderData["Selected_mode"]."','".$orderData["DHP_Code"]."','".$orderData["DHP_Name"]."','48','".$orderData["order_date"]."','".$orderData["month"]."','".$orderData["year"]."','Apps','$ord_date','0','".$orderData["Order_Type"]."'";
performQuery($sql);
//echo $sql;
for ($j = 0; $j < count($orderDetail); $j++) {

 

		$sql="insert into Trans_Order_Book_Detail (Trans_SlNo,Sf_Code,Product_Code,Product_Name,Pack,Order_Sal_Qty,Order_Free_Qty,Order_Rate,Order_Value,NRV_Value,TotNet_Amt,Division_Code,Order_Sch_Qty,Order_Free_Value,Discount,Remarks,Order_tax,Order_discount) select '$trans_slh','$sfCode','".$orderDetail[$j]["product_code"]."','".$orderDetail[$j]["product_Name"]."','','".$orderDetail[$j]["Product_Order_Qty"]."','".$orderDetail[$j]["Additional_Qty"]."','".$orderDetail[$j]["product_Rate"]."','".$orderDetail[$j]["Order_value"]."','".$orderDetail[$j]["NRV"]."','','$Owndiv','".$orderDetail[$j]["Scheme_Quantity"]."','".$orderDetail[$j]["FreeQTy_value"]."','".$orderDetail[$j]["product_Discount"]."','".$orderDetail[$j]["feedback"]."','".$orderDetail[$j]["product_Tax"]."','".$orderDetail[$j]["Discount"]."'";
		performQuery($sql);  
		}


		break;
        case "DCRApproval":
            $date = $_GET['date'];
            $code = $_GET['code'];
            $date = str_replace('/', '-', $date);
            $date = date('Y-m-d', strtotime($date));
            $sql = "exec ApproveDCRByDt '" . $code . "','$date'";
            performQuery($sql);
            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
		case "DCRReject":
            $date = $_GET['date'];
            $code = $_GET['code'];
            $date = str_replace('/', '-', $date);
            $date =  date('Y-m-d 00:00:00', strtotime($date));
			$sql = "exec App_DcrReject '$code','$date'," . $vals['reason'] . "";
			//echo $sql;
            performQuery($sql);
			$msg =",Your ".$date." DCR Rejected for ".$vals['reason']."";
			send_notification_body($code,$msg,0);
            $resp["success"] = true;
            echo json_encode($resp);
            die;
            break;
		case "DCRTPDevReason":
		    $Reason=$vals['reason'];
			$TPWType=$vals['wtype'];
			$TPAreaCode=$vals['clusterid'];
			$TPArea=$vals['ClstrName'];
			$ADate = date('Y-m-d');
			$status=$vals['status'];
		    $sql="exec svDCRTPDevReason '$sfCode','$TPWType','$TPAreaCode','$TPArea','$ADate','$Reason','$status'";  
			//echo $sql;
			performQuery($sql);
			break;
			
			case "Survey_App": 
			$data = json_decode($_POST['data'], true);  
			$sfCode = $_GET['sfCode'];	
			$div = $_GET['divisionCode'];
			$Owndiv = str_replace(",", "", $div);

			for($i=0;$i<count($vals);$i++) { 
			$sql="exec sv_Survey '" . date('Y-m-d 00:00:00')."','$Owndiv','" . $vals[$i]['Survey_Id'] . "','" . $vals[$i]['Question_Id'] . "','$sfCode','" . $vals[$i]['Doctor_code'] . "','" . $vals[$i]['Chemist_code'] . "','" . $vals[$i]['Trans_month'] . "','" . $vals[$i]['Trans_year'] . "','" .date('Y-m-d H:i:s')."','" . $vals[$i]['Answer'] . "'"; 
			performQuery($sql); 
			}
			break;
			
        case "Activity_Report_APP": 

$username=$vals['username'];

$AppDeviceRegId=$vals['app_device_id'];
$sql="select * from mas_salesforce where UsrDfd_UserName='$username' and SF_Status=0";
$tr=performQuery($sql);

      
if(count($tr)==0&&$AppDeviceRegId!=null){
 $respon = array();
        $respon['success'] = false;
 $respon['type'] = 3;
        $respon['msg'] = "User Status Changed,. Kindly Login Again....";
        return outputJSON($respon);
die;
}
$sql="select app_device_id from access_table where Sf_Code='$sfCode'";
$arr=performQuery($sql);
if($arr[0]['app_device_id']==""&&$AppDeviceRegId!=null)
{
$sql="update access_table set app_device_id='$AppDeviceRegId' where Sf_Code='$sfCode'";
performQuery($sql);
}
else if($arr[0]['app_device_id']!=$AppDeviceRegId&&$AppDeviceRegId!=null){
$sql="select DeviceId_Need from Access_Master where division_code='$Owndiv'";
$tr=performQuery($sql);
if($tr[0]['DeviceId_Need']=="2"){
$respon=array();
 $respon['success'] = false;
 $respon['type'] = 3;
        $respon['msg'] = "Device Not Valid..";
        return outputJSON($respon); die;
}
}
if($vals["dcr_activity_date"]!=null&&$vals["dcr_activity_date"]!=''){
$today=$vals["dcr_activity_date"];
}
// $data_dcr = json_encode($_POST['data'], true);
// $data_dcr2 =  $_POST['data'];
// $data_dcr1 = str_replace("'", "",$data_dcr);
// $sql="insert into tracking_dcr select '$sfCode','$Owndiv','$data_dcr1',getdate(),'$today','Native'";
// performQuery($sql);
$vals["Worktype_code"]="'".str_replace("'", "",$vals["Worktype_code"])."'";
            $sql = "SELECT * FROM vwActivity_Report where SF_Code='" . $sfCode . "' and lower(Work_Type) <>lower(" . $vals["Worktype_code"] . ")  and cast(activity_date as datetime)=cast('$today' as datetime)";
            $result1 = performQuery($sql);
       
 $sql = "SELECT * FROM dcrmain_temp where SF_Code='" . $sfCode . "' and  cast(activity_date as datetime)=cast('$today' as datetime) and confirmed=2 and fieldwork_indicator='L'";
 $leavereg = performQuery($sql);
 if (count($leavereg) > 0) {
 $sql = "delete FROM dcrmain_temp where SF_Code='" . $sfCode . "' and  cast(activity_date as datetime)=cast('$today' as datetime) and confirmed=2 and fieldwork_indicator='L'";
 performQuery($sql);
}
            if (count($result1) > 0) {
                if (!isset($_GET['replace'])) {
                    $result = array();
                    $result['success'] = false;
                    if ($result1[0]['FWFlg'] == 'L' && $result1[0]['Confirmed'] != 2 && $result1[0]['Confirmed'] != 3) {
                        $result['type'] = 2;
                        $result['msg'] = 'Leave Post Already Updated';
                    } else {
                        $result['type'] = 1;
                        $result['msg'] = 'Already There is a Data For other Work do you want to replace....?';
                    }
                    $result['data'] = $data;
                    outputJSON($result);
                    die;
                } else {

                    delAREntry($sfCode, $vals["Worktype_code"], $today);
                }
            }

//echo 'hilo_test';
            $pProd = '';
            $npProd = '';
            $pGCd = '';
            $pGNm = '';
            $pGQty = '';
            $SPProds = '';
            $nSPProds = '';
            $Inps = '';
            $nInps = '';
            $vTyp = 0;
			$VstFlag = 0;
			$Ydat=date("Y");
			$Mdat= date('m');
//print_r(count($data));
//print_r($data);
            for ($i = 1; $i < count($data); $i++) {

                $tableData = $data[$i];
                if (isset($tableData['Activity_Doctor_Report'])) { 
                    $vTyp = 1;
                    $DetTB = $tableData['Activity_Doctor_Report']; 
                    $cCode = $DetTB["doctor_code"];
					$cName = $DetTB["doctor_name"];
                    //$vTm = $DetTB["Doc_Meet_Time"];
                    //$vTm = date('Y-m-d H:i:s');
					if($DetTB["Doc_Meet_Time"]=="null" || $DetTB["Doc_Meet_Time"]==null || $DetTB["Doc_Meet_Time"]==''){
						$vTm = date('Y-m-d H:i:s');
					} else{
						$vTm = $DetTB["Doc_Meet_Time"];
					}
					if($DetTB["modified_time"]=="null" || $DetTB["modified_time"]==null || $DetTB["modified_time"]==''){
						$mTm = date('Y-m-d H:i:s');
					} else{
						$mTm = $DetTB["modified_time"];
					}
                    $pob = $DetTB["Doctor_POB"];
					$tvist = $DetTB["Tlvst"];
					$tvs = str_replace("'", "",$tvist);

			$nextVisitDate=$DetTB['nextVisitDate'];
					if($nextVisitDate=="null" || $nextVisitDate==null || $nextVisitDate=='')	
						$nextVisitDate="''";
					$hospitalcode=$DetTB['hospital_code'];

$hospitalname=$DetTB['hospital_name'];
if($hospitalcode==null || $hospitalcode== ''){
						$hospitalcode="''";
$hospitalname="''";
}

                    $proc = "svDCRLstDet_App";
					//echo $tvs;
					//print_r($tvs);	
				//$query = "SELECT COUNT(Trans_Detail_Info_Code) as Visit_tl FROM vwActivity_MSL_Details WHERE division_code='$Owndiv' and SF_Code='$sfCode' and Trans_Detail_Info_Code=$cCode and year(time) = '$Ydat' and month(time) ='$Mdat'";
					//	print_r($c_no);
  					//$c_no = performQuery($query);
					//$check=$c_no[0]["Visit_tl"];
					$check=0;
					//	print_r($check);	
					if($check >= $tvs)	$VstFlag = 1;
					//echo $VstFlag;	
					//$sql = "SELECT ListedDr_Name name from Mas_ListedDr where ListedDrCode=" . $cCode."";
                    //$sql = "SELECT Doctor_Name name from vwDoctor_Master_APP where Doctor_Code=" . $cCode;
					 if($cName=='' || $cName=="''") {
						 $sql_name = "SELECT ListedDr_Name name from Mas_ListedDr where ListedDrCode='" . $cCode."'";} 
						 if($cCode=='' || $cCode=="''" ) {
						 $name_sql = "SELECT ListedDrCode cd from Mas_ListedDr where ListedDr_Name='" . $cName."'";
						 //echo $name_sql;
						 $drname= performQuery($name_sql);
						 $cCode = $drname[0]["cd"];
						 $cName = $DetTB["doctor_name"];
						 }

                }
                if (isset($tableData['Activity_Chemist_Report'])) {
                    $vTyp = 2;
                    $DetTB = $tableData['Activity_Chemist_Report'];
                    $cCode = $DetTB["chemist_code"];
					$cName = $DetTB["chemist_name"];
                   // $vTm = $DetTB["Chm_Meet_Time"];
				   if($DetTB["Chm_Meet_Time"]=="null" || $DetTB["Chm_Meet_Time"]==null || $DetTB["Chm_Meet_Time"]==''){
						$vTm = date('Y-m-d H:i:s');
					} else{
						$vTm = $DetTB["Chm_Meet_Time"];
					}
					if($DetTB["modified_time"]=="null" || $DetTB["modified_time"]==null || $DetTB["modified_time"]==''){
						$mTm = date('Y-m-d H:i:s');
					} else{
						$mTm = $DetTB["modified_time"];
					}
					//echo $vTm;
                    //$vTm = date('Y-m-d H:i:s');
                    $pob = $DetTB["Chemist_POB"];
                    //$sql = "SELECT Chemists_Name name from vwChemists_Master_APP where Chemists_Code=" . $cCode;
					if($cName=='' || $cName=="''") {
					$sql_name = "SELECT Chemists_Name name from vwChemists_Master_APP where Chemists_Code='" . $cCode."'";} 
					
					 if($cCode=='' || $cCode=="''" ) {
					 $name_sql = "SELECT Chemists_Code cd from Mas_Chemists where Chemists_Name='" . $cName."'";
					 //echo $name_sql;
					 $chmName= performQuery($name_sql);
					 $cCode = $chmName[0]["cd"];
					 $cName = $DetTB["chemist_name"];
					 }
                }
                if (isset($tableData['Activity_Stockist_Report'])) {
                    $vTyp = 3;
                    $DetTB = $tableData['Activity_Stockist_Report'];
                    $cCode = $DetTB["stockist_code"];
                   // $vTm = $DetTB["Stk_Meet_Time"];
				   if($DetTB["Stk_Meet_Time"]=="null" || $DetTB["Stk_Meet_Time"]==null || $DetTB["Stk_Meet_Time"]==''){
						$vTm = date('Y-m-d H:i:s');
					} else{
						$vTm = $DetTB["Stk_Meet_Time"];
					}
					if($DetTB["modified_time"]=="null" || $DetTB["modified_time"]==null || $DetTB["modified_time"]==''){
						$mTm = date('Y-m-d H:i:s');
					} else{
						$mTm = $DetTB["modified_time"];
					}
                    //$vTm = date('Y-m-d H:i:s');
                    $pob = $DetTB["Stockist_POB"];
                    $sql_name = "SELECT stockiest_name name from vwstockiest_Master_APP where stockiest_code=" . $cCode;
                }
                if (isset($tableData['Activity_UnListedDoctor_Report'])) {
                    $vTyp = 4;
                    $DetTB = $tableData['Activity_UnListedDoctor_Report'];
                    $cCode = $DetTB["uldoctor_code"];
                    //$vTm = $DetTB["UnListed_Doc_Meet_Time"];
					if($DetTB["UnListed_Doc_Meet_Time"]=="null" || $DetTB["UnListed_Doc_Meet_Time"]==null || $DetTB["UnListed_Doc_Meet_Time"]==''){
						$vTm = date('Y-m-d H:i:s');
					} else{
						$vTm = $DetTB["UnListed_Doc_Meet_Time"];
					}
					if($DetTB["modified_time"]=="null" || $DetTB["modified_time"]==null || $DetTB["modified_time"]==''){
						$mTm = date('Y-m-d H:i:s');
					} else{
						$mTm = $DetTB["modified_time"];
					}
                   //$vTm = date('Y-m-d H:i:s');
                    $pob = $DetTB["UnListed_Doctor_POB"];
                    $proc = "svDCRUnlstDet_App";
                    $sql_name = "SELECT unlisted_doctor_name name from vwunlisted_doctor_master_APP where unlisted_doctor_code=" . $cCode;
                }
if (isset($tableData['Activity_Hosp_Report'])) {
                    $vTyp = 5;
                    $DetTB = $tableData['Activity_Hosp_Report'];
                    $cCode = $DetTB["hospital_code"];
                   // $vTm = $DetTB["Hosp_Meet_Time"];
					if($DetTB["Hosp_Meet_Time"]=="null" || $DetTB["Hosp_Meet_Time"]==null || $DetTB["Hosp_Meet_Time"]==''){
						$vTm = date('Y-m-d H:i:s');
					} else{
						$vTm = $DetTB["Hosp_Meet_Time"];
					}
					if($DetTB["modified_time"]=="null" || $DetTB["modified_time"]==null || $DetTB["modified_time"]==''){
						$mTm = date('Y-m-d H:i:s');
					} else{
						$mTm = $DetTB["modified_time"];
					}
                    $pob = $DetTB["Hosp_POB"];
                    $sql_name = "SELECT hospital_name name from vwHosp_Master_APP where hospital_code=" . $cCode;
                }
 if (isset($tableData['Activity_Cip_Report'])) {

                    $vTyp = 6;

                    $DetTB = $tableData['Activity_Cip_Report'];
                    $cCode = $DetTB["doctor_code"];
                   // $vTm = $DetTB["Doc_Meet_Time"];
					if($DetTB["Doc_Meet_Time"]=="null" || $DetTB["Doc_Meet_Time"]==null || $DetTB["Doc_Meet_Time"]==''){
						$vTm = date('Y-m-d H:i:s');
					} else{
						$vTm = $DetTB["Doc_Meet_Time"];
					}
					if($DetTB["modified_time"]=="null" || $DetTB["modified_time"]==null || $DetTB["modified_time"]==''){
						$mTm = date('Y-m-d H:i:s');
					} else{
						$mTm = $DetTB["modified_time"];
					}
                    $pob = $DetTB["Doctor_POB"];
					$tvist = $DetTB["Tlvst"];
					$tvs = str_replace("'", "",$tvist);

$nextVisitDate=$DetTB['nextVisitDate'];
					if($nextVisitDate=="null" || $nextVisitDate==null || $nextVisitDate=='')	
						$nextVisitDate="''";

$hospitalcode=$DetTB['hospital_code'];

$hospitalname=$DetTB['hospital_name'];
if($hospitalcode==null || $hospitalcode== ''){
						$hospitalcode="''";
$hospitalname="''";
}

                    $proc = "svDCRCIPDet_App";
					//$cCode="1";
 
                    $sql_name = "SELECT name from vwCIP_APP where id=" . $cCode;


                }
				
	if(isset($tableData["Activity_Event_Captures"]))
				{
					$Event_Captures = $tableData["Activity_Event_Captures"];
					
				}

                // $tRw = performQuery($sql);
                // $cName = $tRw[0]["name"];
				if($sql_name !="" || $sql_name !=null){
                $tRw = performQuery($sql_name);
                $cName = $tRw[0]["name"];
			}

                if (isset($tableData['Activity_Sample_Report']) || isset($tableData['Activity_Unlistedsample_Report'])) {

                    if (isset($tableData['Activity_Sample_Report']))
                        $samp = $tableData['Activity_Sample_Report'];
                    if (isset($tableData['Activity_Unlistedsample_Report']))
                        $samp = $tableData['Activity_Unlistedsample_Report'];

                    for ($j = 0; $j < count($samp); $j++) {
                        $feedback=$samp[$j]["feedback"];
		        if($feedback==null)
			$feedback="0";
                        $rcpa_qty=$samp[$j]["Product_Rcpa_Qty"];
		        if($rcpa_qty==null)
			$rcpa_qty="0";
			$prodfeedback_id = $samp[$j]["feedbk_id"]; 
			$prodfeedback_text = $samp[$j]["feedbk"]; 

                        //if ($j < 3) {
							if($samp[$j]["Product_Rx_Qty"]=="") $samp[$j]["Product_Rx_Qty"]="0";
                            $pProd = $pProd . (($pProd != "") ? "#" : '') . $samp[$j]["product_code"] . "~" . $samp[$j]["Product_Sample_Qty"] . "$" . $samp[$j]["Product_Rx_Qty"] . "$" . $feedback. "^" . $rcpa_qty . "$" . $prodfeedback_id;
                            $npProd = $npProd . (($npProd != "") ? "#" : '') . $samp[$j]["product_Name"] . "~" . $samp[$j]["Product_Sample_Qty"] . "$" . $samp[$j]["Product_Rx_Qty"] . "$" . $feedback. "^" . $rcpa_qty . "$" . $prodfeedback_text;
                       /* } else {
                            $SPProds = $SPProds . $samp[$j]["product_code"] . "~" . $samp[$j]["Product_Sample_Qty"] . "$" . $samp[$j]["Product_Rx_Qty"] . "$" . $feedback . "^" . $rcpa_qty. "#";
                            $nSPProds = $nSPProds . $samp[$j]["product_Name"] . "~" . $samp[$j]["Product_Sample_Qty"] . "$" . $samp[$j]["Product_Rx_Qty"] . "$" . $feedback. "^" . $rcpa_qty . "#";
                        }*/
                    }
                }

               if (isset($tableData['Activity_POB_Report']) || isset($tableData['Activity_Stk_POB_Report'])) {

                    if (isset($tableData['Activity_POB_Report']))
                        $samp = $tableData['Activity_POB_Report'];
                    if (isset($tableData['Activity_Stk_POB_Report']))
                        $samp = $tableData['Activity_Stk_POB_Report'];

                    for ($j = 0; $j < count($samp); $j++) {
                        $SPProds = $SPProds . $samp[$j]["product_code"] . "~" . $samp[$j]["Qty"] . "#";
                        $nSPProds = $nSPProds . $samp[$j]["product_Name"] . "~" . $samp[$j]["Qty"] . "#";
                    }
                }
                if (isset($tableData['Activity_Input_Report']) || isset($tableData['Activity_Chm_Sample_Report']) || isset($tableData['Activity_Stk_Sample_Report']) || isset($tableData['activity_unlistedGift_Report'])) {
                    if (isset($tableData['Activity_Input_Report']))
                        $inp = $tableData['Activity_Input_Report'];
                    if (isset($tableData['Activity_Chm_Sample_Report']))
                        $inp = $tableData['Activity_Chm_Sample_Report'];
                    if (isset($tableData['Activity_Stk_Sample_Report']))
                        $inp = $tableData['Activity_Stk_Sample_Report'];
                    if (isset($tableData['activity_unlistedGift_Report']))
                        $inp = $tableData['activity_unlistedGift_Report'];

                    for ($j = 0; $j < count($inp); $j++) {
                        if ($j == 0 && ($vTyp == 1 || $vTyp == 4 )) {
					 	
                            $pGCd = $inp[$j]["Gift_Code"];  
                            $pGNm = $inp[$j]["Gift_Name"];
                            $pGQty = $inp[$j]["Gift_Qty"];
                        } else {
                            $Inps = $Inps . $inp[$j]["Gift_Code"] . "~" . $inp[$j]["Gift_Qty"] . "#";
                            $nInps = $nInps . $inp[$j]["Gift_Name"] . "~" . $inp[$j]["Gift_Qty"] . "#";
                        }
                    }
                }
            }


            $ARCd = "";
            $ARDCd = (strlen($_GET['amc']) == 0) ? "0" : $_GET['amc'];
			$sql = "SELECT trans_slno FROM vwActivity_Report where SF_Code='" . $sfCode . "' and cast(activity_date as date)=cast('$today' as date)";
            $trans = performQuery($sql);

			if(count($trans)==0){
            $sql = "{call  svDCRMain_App(?,?," . $vals["Worktype_code"] . ",'" . str_replace("'", "", $vals["Town_code"]) . "',?,'" . str_replace("'", "", $vals["Daywise_Remarks"]) . "',?)}";
            $params = array(array($sfCode, SQLSRV_PARAM_IN),
                array($today, SQLSRV_PARAM_IN),
                array($Owndiv, SQLSRV_PARAM_IN),
				array(&$ARCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(50)));
            performQueryWP($sql, $params);
			}
			else
			$ARCd=$trans[0]['trans_slno'];

            $loc = explode(":", str_replace("'", "", $DetTB["location"]) . ":");
            $lat = $loc[0]; //latitude
            $lng = $loc[1]; //longitude
            $address = getaddress($lat, $lng);
            if ($address) {
                $DetTB["geoaddress"] = $address;
            } else {
                $DetTB["geoaddress"] = "NA";
            }
$apps="'261'";
$vst="0";

            $sqlsp = "{call  ";
            if ($vTyp != 0) {
                if ($vTyp == 2 || $vTyp == 3|| $vTyp == 5) $proc = "svDCRCSHDet_App";
 

                if ($pob == '')
                    $pob = '0';
                $sqlsp = $sqlsp . $proc . " (?,?,?," . $vTyp . "," . $cCode . ",'" . $cName . "','" . str_replace("'", "", $vTm) . "'," . $pob . ",'" . str_replace("'", "", $DetTB["Worked_With"]) . "',?,?,?,?,";
                if ($vTyp == 1 || $vTyp == 4|| $vTyp == 6)
                    $sqlsp = $sqlsp . "?,?,?,?,?,";
               	if ($vTyp == 1|| $vTyp == 6)   {
				  $sqlsp = $sqlsp . "'" . str_replace("'", "", $vals["Town_code"]) . "','" . str_replace("'", "", $vals["Daywise_Remarks"]) . "',?,'" . str_replace("'", "", $vals["rx_t"]) . "','" . $mTm . "',?,?,'" . str_replace("'", "", $vals["DataSF"]) . "','" . $DetTB["geoaddress"] . "',".$apps.",".$vst.",'" . str_replace("'", "",$nextVisitDate ). "')}";

				} 
				else
				 $sqlsp = $sqlsp . "'" . str_replace("'", "", $vals["Town_code"]) . "','" . str_replace("'", "", $vals["Daywise_Remarks"]) . "',?,'" . str_replace("'", "", $vals["rx_t"]) . "','" . $mTm . "',?,?,'" . str_replace("'", "", $vals["DataSF"]) . "','" . $DetTB["geoaddress"] . "')}";

//$resp["dqry"] = $sqlsp;
   $params = array(array($ARCd, SQLSRV_PARAM_IN),
array(&$ARDCd, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_NVARCHAR(50)),
                    array($sfCode, SQLSRV_PARAM_IN));
				
                if ($vTyp == 1 || $vTyp == 4|| $vTyp == 6) {
                    array_push($params, array($pProd, SQLSRV_PARAM_IN));
                    array_push($params, array($npProd, SQLSRV_PARAM_IN));
                }

                array_push($params, array($SPProds, SQLSRV_PARAM_IN));
                array_push($params, array($nSPProds, SQLSRV_PARAM_IN));

                if ($vTyp == 1 || $vTyp == 4|| $vTyp == 6) {
                    array_push($params, array($pGCd, SQLSRV_PARAM_IN));
                    array_push($params, array($pGNm, SQLSRV_PARAM_IN));
                    array_push($params, array($pGQty, SQLSRV_PARAM_IN));
                }
                array_push($params, array($Inps, SQLSRV_PARAM_IN));
                array_push($params, array($nInps, SQLSRV_PARAM_IN));
                array_push($params, array($Owndiv, SQLSRV_PARAM_IN));
                array_push($params, array($loc[0], SQLSRV_PARAM_IN));
                array_push($params, array($loc[1], SQLSRV_PARAM_IN));
//$resp["success"] = true;
 //$resp["sqls"] =$sqlsp;
 //$resp["par"] =$params;
//outputJSON($resp);die;
                performQueryWP($sqlsp, $params);
    
				if (sqlsrv_errors() != null) {
                    //echo($sqlsp . "<br>");
                    print_r($params);
                    outputJSON(sqlsrv_errors());
                    die;
                } 
			
				for ($j = 0; $j < count($Event_Captures); $j++) {
	                	
						$ev_imgurl=$sfCode."_".$Event_Captures[$j]["imgurl"];
				 		$ev_title=$Event_Captures[$j]["title"];
				 		$ev_remarks=$Event_Captures[$j]["remarks"];
						$sql = "insert into DCREvent_Captures(Trans_slno,Trans_detail_slno,imgurl,title,remarks,Division_Code,sf_code) select '" . $ARCd . "','" . $ARDCd . "','" . $ev_imgurl . "','" . $ev_title . "','" . $ev_remarks . "','" . $Owndiv . "','$sfCode'";
		            	performQuery($sql);
	
					}	
				SvRCPAEntry($ARCd,$ARDCd,$DetTB,$today);
                if (sqlsrv_errors() != null) {
					// echo outputJSON(sqlsrv_errors());
                    //echo($sqlsp . "<br>");
                    outputJSON($params . "<br>");
                    outputJSON(sqlsrv_errors());
                    die;
                }
                if ($ARDCd == "Exists") {
                    $resp["msg"] = "Call Already Exists";
                    $resp["success"] = false;
                    echo json_encode($resp);
                    die;
                }
				if (isset($tableData['Dynamic_Activity_App'])){
				
			for($d = 0; $d < count($tableData['Dynamic_Activity_App']); $d++)
			{
				$Dact =	$tableData['Dynamic_Activity_App'][$d]['val']; 
				$resp["ErQry"] = "Dynamic_Activity_App";
				svDCRActivity($Dact); 
			}   
        }
            }
            break;
    }

    $resp["success"] = true;
    echo json_encode($resp);
}

$axn = $_GET['axn'];
$value = explode(":", $axn);
switch ($value[0]) {
    case "login":
        actionLogin();
        break;
	case "get/expensedetails":
		$divCode = $_GET['divisionCode'];
		$sfCode=$_GET['sfCode'];
    	$monthexp = $_GET['monthexp'];
		$query = "select * FROM Trans_Expense_Head_App  where division_code ='".$divCode."' and SF_Code='".$sfCode."' and Expense_Month='".$monthexp."'"; 
	 	$result=performQuery($query); 
        $res  = $result[0]['Sl_No'];
		//echo $res;
		$query1 = "select * FROM Trans_Expense_Detail_App  where Sl_No ='".$res."'"; 
	 	$result1=performQuery($query1); 
		if($result1){
		outputJSON($result1);
		}
		else{
		outputJSON([]);
		}
	    break;
	case "get/tpdetail";
	      outputJSON(getTourPlan());
	      break;
	case "get/tpActivitydetail"; 
	  outputJSON(getActivity_date());
	  break;
	case "get/camp_apprlist":
		$divcode = $_GET['Division_Code'];
		$Sf_code=$_GET['Sf_code'];
    	$Camp_Type = $_GET['Camp_Type'];
		$Camp_Code = $_GET['Camp_Code'];
		$query ="Select * from Trans_opd_camp_approval where Division_Code='".$divcode."' and Camp_Code='".$Camp_Code."' and Sf_code='".$Sf_code."' and Camp_Type='".$Camp_Type."'";
		// echo $query;
		$result=performQuery($query); 
		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}
	    break;

case "get/manager_camplist":
		$divcode = $_GET['Division_Code'];
		$Sf_code=$_GET['Sf_code'];
		$Camp_Status=$_GET['Camp_Status'];
 		$query ="Select * from Trans_opd_camp_approval where Division_Code='$divcode' and Sf_code='$Sf_code' and Camp_Status='$Camp_Status'";
		//echo $query;
	 	$result=performQuery($query); 
		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}
	    break;

	case "get_campopdapprlist":
				$SF = $_GET['SF'];
				$Div = $_GET['Div'];
                $query = "exec getCamp_opd_approvelist '" . $SF. "','" . $Div . "'";
				//echo $query;
				$results = performQuery($query);
			if($results){
			outputJSON($results);
			}
			else{
			outputJSON([]);
			}
                break;
case "get/Camp_taggedlist":
	$divisionCode = $_GET['divisionCode'];
		$OPD_Code=$_GET['OPD_Code'];
		$query ="Select * from Map_OPDCamp_Drs_Details where OPD_Code='".$OPD_Code."' and Division_Code='".$divisionCode."'"; 
		$result1=performQuery($query); 
		if($result1){
		outputJSON($result1);
		}
		else{
		outputJSON([]);
		}
break;
	case "get/campdetails":
	$divCode = $_GET['divisionCode'];
	$sfCode=$_GET['sfCode'];
	$Campaign_Lock_flag=$_GET['Campaign_Lock_flag'];
	$dateTime=date('Y-m-d 00:00:000');
	//$query = "exec Get_camplist '$sfCode','$divCode','Campaign','$dateTime','$Campaign_Lock_flag'";
//getCamp_approvelist 'MGR0148',19,'2020-02-19 00:00:00.000','0' 
$query = "exec getCamp_approvelist '$sfCode','$divCode','$dateTime','$Campaign_Lock_flag'";
//echo $query;
	$result=performQuery($query); 
		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}

	break;
	case "get/campaigndetails":
	$divCode = $_GET['divisionCode'];
	$sfCode=$_GET['sfCode'];
	$Campaign_Lock_flag=$_GET['Campaign_Lock_flag'];
	$dateTime=date('Y-m-d 00:00:000');
	
	$query = "exec getCampaign_approvelist '$sfCode','$divCode','$dateTime','$Campaign_Lock_flag'";

	$result=performQuery($query); 
		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}

	break;


case "get/campagindetails":
	$divCode = $_GET['divisionCode'];
	$dateTime=date('Y-m-d 00:00:000');
	$sfCode=$_GET['sfCode'];
	$Campaign_Lock_flag=$_GET['Campaign_Lock_flag'];
	$query = "exec Get_campaginlist '$sfCode','$divCode','Campaign','$dateTime','$Campaign_Lock_flag'";
 
	$result=performQuery($query); 
		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}

	break;	
    case "deleteEntry":
        $data = json_decode($_POST['data'], true);
        $arc = (isset($data['arc']) && strlen($data['arc']) == 0) ? null : $data['arc'];
        $amc = (isset($data['amc']) && strlen($data['amc']) == 0) ? null : $data['amc'];
        $result = deleteEntry($arc, $amc);
        break;
	case "get/expenselist":
		$divCode = $_GET['divisionCode'];
		$param_type= $_GET['param_type'];
		$query = "select * FROM fixed_variable_expense_setup  where division_code ='".$divCode."' and param_type !='F'"; 
	 	//select * from fixed_variable_expense_setup where division_code=15 and param_type!='F'
		//echo $query;
		$result=performQuery($query); 

		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}
		break;	

	case "get/visitcountlist1":
	$SF_Code= $_GET['SF_Code'];
	$Yr= $_GET['Yr'];
	$Mnth= $_GET['Mnth'];
	$Division_Code= $_GET['Division_Code'];
	$query ="Select Cust_Code,Cust_Type,Mnth,Yr,VstCnt from Trans_VisitCnt_Details where SF_Code='".$SF_Code."' and Yr='".$Yr."' and Mnth='".$Mnth."' and Division_Code='".$Division_Code."'";
		$result=performQuery($query); 

		if($result){
		outputJSON($result);
		}
		else{
		outputJSON([]);
		}
		break;	
    case "get/doctorCount":
        outputJSON(getDoctorPCount());
        break;
    case "get/setup1":
        outputJSON(getAPPSetups());
        break;
   case "fileAttachment_mail":
        $sf = $_GET['sf_code'];
        $file = $_FILES['imgfile']['name'];
        $info = pathinfo($file);
        $file_name = basename($file, '.' . $info['extension']);
        $ext = $info['extension'];
        $fileName = $file_name . "_" . $sf . "_" . date('d_m_Y') . "." . $ext;
        $file_src = '../MasterFiles/Mails/Attachment/' . $fileName;
        move_uploaded_file($_FILES['imgfile']['tmp_name'], $file_src);
        break;
case "fileAttachment":
 $doctorCode = $_GET['doctor_code'];
        $file = $_FILES['imgfile']['name'];
        $info = pathinfo($file);
        $file_name = basename($file, '.' . $info['extension']);
        $ext = $info['extension'];
        $fileName =$doctorCode . "_" . date('d-m-Y') . "_". $file_name . "." . $ext;
        $file_src = '../Visiting_Card/' . $fileName;
        move_uploaded_file($_FILES['imgfile']['tmp_name'], $file_src);
break;
    case "imgupload":
        $sf = $_GET['sf_code'];
        print_r(sf);
        //move_uploaded_file($_FILES["imgfile"]["tmp_name"], "../photos/" . $sf . "_" . $_FILES["imgfile"]["name"]);
		move_uploaded_file($_FILES["imgfile"]["tmp_name"], "../photos/". $_FILES["imgfile"]["name"]);
        break;
	case "profileupload":
        $sf = $_GET['sf_code'];
        print_r($sf);
        move_uploaded_file($_FILES["imgfile"]["tmp_name"], "../Profile_Imgs/" . $sf . "_" . $_FILES["imgfile"]["name"]);
        break;	
 case "fileAttachment_record":
        $sf = $_GET['sfCode'];
$div = $_GET['divisionCode'];
  $contentype= $_GET['contenttype'];
        $divs = explode(",", $div . ",");
        $Owndiv = (string) $divs[0];
        $file = $_FILES['mediafile']['name'];
        $info = pathinfo($file);
      
        $file_name = basename($file, '.' . $info['extension']);
	$file_name = str_replace("%20", "_",$file_name);
        $ext = $info['extension'];
        $fileName = $file_name . "_" . $sf . "_" . date('d_m_Y') . "." . $ext;
        $file_src = '../MasterFiles/media_recorder/' . $fileName;
    $result=array();
        if(move_uploaded_file($_FILES['mediafile']['tmp_name'], $file_src)){
 $query = "select reporting_to_sf from mas_salesforce where sf_code='" . $sf . "'";
$rep=performQuery($query);
$reprtTo=$rep[0]['reporting_to_sf'];
 $query = "insert into Mas_MediaFiles_Info select '" . $sf . "','" . $reprtTo . "','".$contentype."','".  $fileName."',getdate(),'".$Owndiv."',0";
               performQuery($query);
 $result['success'] = true;
}
else{
 $result['success'] = $_FILES['mediafile']['error'];



}
  outputJSON($result);
        break;
    case "get/jointwork":
        outputJSON(getJointWork());
        break;
    case "get/subordinate":
        outputJSON(getSubordinate());
        break;
    case "get/submgr":
        outputJSON(getSubordinateMgr());
        break;
    case "get/uldoctorCount":
        outputJSON(getUlDoctorPCount());
        break;
    case "get/chemistCount":
        outputJSON(getChemistPCount());
        break;
    case "get/stockistCount":
        outputJSON(getStockistPCount());
        break;


 case "table/list1":
                   $respon=array();
$res=array();
$res[0]['id']="dfdf";
$res[0]['name']=$Owndiv;
  $respon['success']    = true;
        $respon['msg']        = "SyncSuccess";
        $respon['MasterSyncMe'] = $res;
        
        
       outputJSON($respon);die;
break;

case "save/geotag";
outputJSON(SaveGeoTag());
break;
	case "get/geotag":
		outputJSON(ViewGeoTag());
		break;

	case "get/editdates":
		outputJSON(Geteditdates());
		break;
    case "table/list":
        $data = json_decode($_POST['data'], true);
        $sfCode =$data['sfCode'];
        $RSF =$data['sfCode'];
        $div =$data['divisionCode'];
        $divs = explode(",", $div . ",");
        $Owndiv = (string) $divs[0];
        switch (strtolower($data['tableName'])) {
 
            case "mas_worktype":
                $query = "exec GetWorkTypes_App '" . $RSF . "'";
                $results = performQuery($query);
				// for($i=0;$i<count($results);$i++) {
					// if($results[$i]['name']=="Leave"){
						// unset( $results[$i]);			   
							// }
						// }
				// $results = array_values($results);
                break;

            case "product_master":
                $results = getProducts();
                break;
			case "mas_superstockist":
                $query = "select * from vwSuper_stockist_App where Division_code='" . $Owndiv . "'";

                $results = performQuery($query);
            break;
			case "quiz1":
                $query = "select survey_id,quiz_title,substring(filepath,charindex(')',filepath)+1,len(filepath)) FileName from QuizTitleCreation where division_code='" . $Owndiv . "' and survey_id='5' order by effective_date desc";/* and month(effective_date)=Month(GETDATE()) and year(effective_date)=Year(GETDATE())";*/
				
                $quiztitle = performQuery($query);
				$surveyid=$quiztitle[0]['survey_id'];
if($quiztitle[0]['FileName']!=""){
$extn=end(explode('.', $quiztitle[0]['FileName']));

 if ($extn == "png" || $extn == "jpg")
             $quiztitle[0]['mimetype']= "image/png";
 else if($extn=="doc"||$extn=="dot")
			  $quiztitle[0]['mimetype']= "application/msword";
 else if($extn=="docx"|| $extn == "DOCX")
			  $quiztitle[0]['mimetype']="application/msword";
 else if ($extn == "xls"||$extn == "xlt"||$extn == "xla")
             $quiztitle[0]['mimetype']= "application/vnd.ms-excel";
		  else if ($extn == "xlsx")
             $quiztitle[0]['mimetype']= "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
         else if ($extn == "mp4")
             $quiztitle[0]['mimetype']= "video/mp4";
else if($extn == "pptx")
  $quiztitle[0]['mimetype']= "application/vnd.openxmlformats-officedocument.presentationml.presentation";
         else
             $quiztitle[0]['mimetype']= "application/" + $extn;

}
                $query = "select NoOfAttempts type,Type NoOfAttempts,timelimit from Processing_UserList where surveyid='" . $surveyid . "' and sf_code='" . $sfCode . "' and status=0";
                $processUser = performQuery($query);
                $query = "select Question_Type_id,Question_Id,Question_Text,surveyid from AddQuestions where surveyid='" . $surveyid . "' order by question_id asc";
                $questions = performQuery($query);
                $query = "select input_id,Question_Id,Input_Text,Correct_Ans from AddInputOptions where question_id in (select question_id from AddQuestions where surveyid='5') order by question_id asc";
                $answers = performQuery($query);
				$results=array();
				$results['quiztitle'][0]=$quiztitle[0];
				$results['processUser']=$processUser;
				$results['questions']=$questions;
				$results['answers']=$answers;
				if(count($processUser)==0)
				$results=array();
				 outputJSON($results);
                die;
                break;
		case "quiz":
				$query = "select survey_id,quiz_title,substring(filepath,charindex(')',filepath)+1,len(filepath)) FileName from QuizTitleCreation where division_code='" . $Owndiv . "' and active=0 and month(effective_date)=Month(GETDATE()) and year(effective_date)=Year(GETDATE()) and cast(effective_date as date)<=cast(GETDATE() as date) order by survey_id desc";  
				$quiztitle = performQuery($query);
				$quiztitle1=array();
				//echo count($quiztitle);
				$processUser1=array();
					for($i=0;$i<count($quiztitle);$i++){
					$surveyid=$quiztitle[$i]['survey_id'];
					$query = "select NoOfAttempts type,Type NoOfAttempts,timelimit from Processing_UserList where surveyid='" . $surveyid . "' and sf_code='" . $sfCode . "' and process_status='P' and cast(from_date as date)<=cast(GETDATE() as date) and cast(to_date as date)>=cast(GETDATE() as date)";
					//echo $query;
					$processUser = performQuery($query);
						if(count($processUser)>0){
						$processUser1=$processUser;
						$quiztitle1=$quiztitle[$i];
						$quiztitle=array();
						}
					}
				$processUser=array();
				$processUser=$processUser1;
				$quiztitle=array();	
				$quiztitle[0]=$quiztitle1;	

				$surveyid=$quiztitle[0]['survey_id'];
				if($quiztitle[0]['FileName']!=""){
				$extn=end(explode('.', $quiztitle[0]['FileName']));

					if ($extn == "png" || $extn == "jpg")
					$quiztitle[0]['mimetype']= "image/png";
					else if($extn=="doc"||$extn=="dot")
					$quiztitle[0]['mimetype']= "application/msword";
					else if($extn=="docx"|| $extn == "DOCX")
					$quiztitle[0]['mimetype']="application/msword";
					else if ($extn == "xls"||$extn == "xlt"||$extn == "xla")
					$quiztitle[0]['mimetype']= "application/vnd.ms-excel";
					else if ($extn == "xlsx")
					$quiztitle[0]['mimetype']= "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
					else if ($extn == "mp4")
					$quiztitle[0]['mimetype']= "video/mp4";
					else if($extn == "pptx")
					$quiztitle[0]['mimetype']= "application/vnd.openxmlformats-officedocument.presentationml.presentation";
					else
					$quiztitle[0]['mimetype']= "application/" + $extn;

				}
				$query = "select Question_Type_id,Question_Id,Question_Text,surveyid from AddQuestions where surveyid='" . $surveyid . "' order by question_id asc";
				$questions = performQuery($query);
				$query = "select input_id,Question_Id,Input_Text,Correct_Ans from AddInputOptions where question_id in (select question_id from AddQuestions where surveyid='" . $surveyid . "') order by question_id asc";
				$answers = performQuery($query);
				$results=array();
				$results['quiztitle'][0]=$quiztitle[0];
				$results['processUser']=$processUser;
				$results['questions']=$questions;
				$results['answers']=$answers;
				if(count($processUser)==0)
				$results=array();
				break;
	case "vwmydayplan":
				 $date=date("Y-m-d");
				$RSF = $_GET['rSF'];
				$cdate = $_GET['cdate'];
				if($cdate == '' || $cdate=="''" || $cdate==null)
                $cdate =date("Y-m-d");
				
                $query = "exec getTodayTP_native_App '" . $RSF. "','" . $cdate . "'";
				$results = performQuery($query);
                break;
			case "rcpadetail":
				$arc=$_GET['arc'];
				$arc_dt=$_GET['arc_dt'];
				$sfCode=$_GET['sfCode'];
				//$query = "select * from Trans_RCPA_head where division_code='" . $Owndiv . "' and survey_id='5' order by effective_date desc";
				$sql="select * from Trans_RCPA_Head head inner join Trans_RCPA_Detail detail on head.pk_id=detail.fk_pk_id where sf_code='$sfCode' and AR_Code='$arc' and ARMSL_Code='$arc_dt' and ARMSL_Code !='Exists' and Dcrdetail_id !='Exists' ";
				$results = performQuery($sql);  
				break;
			case "rcpadetail_new":
				$arc=$_GET['arc'];
				$arc_dt=$_GET['arc_dt'];
				$sfCode=$_GET['sfCode'];	
				$Rcpa=[];	 		
				$sql="select * from Trans_RCPA_Head  where sf_code='$sfCode' and AR_Code='$arc' and ARMSL_Code='$arc_dt' and ARMSL_Code !='Exists' ";
				$Rcpa_det = performQuery($sql);  
				for($i=0;$i<count($Rcpa_det);$i++){
					$Rcpa[$i]= $Rcpa_det[$i];
					$Rcpa_id = $Rcpa_det[$i]['PK_ID']; 
					$query = "select * from Trans_RCPA_Detail detail where FK_PK_ID='$Rcpa_id'"; 
					$Rcpa[$i]['RcpaComp'] = performQuery($query);
					//array_push();			
				}
				$results =$Rcpa;
			break;
			case "rcpadetail_report":
				$arc=$_GET['arc'];
				$arc_dt=$_GET['arc_dt'];
				$sfCode=$_GET['sfCode'];
				//$query = "select * from Trans_RCPA_head where division_code='" . $Owndiv . "' and survey_id='5' order by effective_date desc";
				$sql="select H.DrName,H.ChmName,H.OPName,H.OPQty,H.OPUnit,D.CompName,D.CompPName,D.CPQty,D.CPUnit  from Trans_RCPA_Head H inner join Trans_RCPA_Detail D on H.pk_id=D.fk_pk_id where sf_code='$sfCode' and ARMSL_Code='$arc_dt'";
				$results = performQuery($sql);                
				break;	
				case "vwedit_activity":
				$arc=$_GET['arc'];
				$arc_dt=$_GET['arc_dt'];
				$sfCode=$_GET['sfCode'];				
				$results = getEdit_Activity($sfCode,$arc,$arc_dt);   		 
			break;
			
			case "event_captures_report":
				$arc=$_GET['arc'];
				$arc_dt=$_GET['arc_dt'];
				$sfCode=$_GET['sfCode'];
				//$query = "select * from Trans_RCPA_head where division_code='" . $Owndiv . "' and survey_id='5' order by effective_date desc";
				$sql="select sf_code,('photos/'+imgurl)Eventimg,title,remarks from DCREvent_Captures where sf_code='$sfCode' and Trans_SlNo='$arc' and Trans_Detail_Slno='$arc_dt'";
				$results = performQuery($sql);                
				break;	
				// case "rcpa_detail":
				// $arc=$_GET['arc'];
				// $arc_dt=$_GET['arc_dt'];
				// $sfCode=$_GET['sfCode'];
				// $query = "select * from Trans_RCPA_Head head where SF_Code ='$sfCode' and AR_Code='$arc' and ARMSL_Code='$arc_dt'";
				// $rcpa = performQuery($query);
				// $rcpaid=$rcpa[0]['PK_ID'];				 
				
				// $query = "select * from Trans_RCPA_Detail where FK_PK_ID='$rcpaid'";
                // $rcpa_det = performQuery($query);
				 
				 // $results=[];
				// $results['rcpa_head']=$rcpa;
				// $results['rcpa_detail']=$rcpa_det;
				// break;
            case "category_master":
                $query = "exec GetProdBrand_App '" . $div . "'";
                $results = performQuery($query);
                break;
            case "vwdcr_misseddates":
                $query = "exec Get_MissedDates_App '" . $sfCode . "'";
                $results = performQuery($query);
                break;
            case "gift_master":
                $query = "exec getAppGift '" . $sfCode . "'";
                $results = performQuery($query);
                break;
            case "doctor_category":
                $query = "select Doc_Cat_Code id,Doc_Cat_Name name from Mas_Doctor_Category where Division_code='" . $Owndiv . "' and Doc_Cat_Active_Flag=0";
                $results = performQuery($query);
                break;
            case "doctor_specialty":
                $query = "select Doc_Special_Code id,Doc_Special_Name name from Mas_Doctor_Speciality where Division_code='" . $Owndiv . "' and Doc_Special_Active_Flag=0";
                $results = performQuery($query);
                break;
            case "mas_doc_class":
                $query = "select Doc_ClsCode id,Doc_ClsSName name from Mas_Doc_Class where Division_code='" . $Owndiv . "' and Doc_Cls_ActiveFlag=0";
                $results = performQuery($query);
                break;
            case "mas_doc_qualification":
                $query = "select Doc_QuaCode id,Doc_QuaName name from Mas_Doc_Qualification where Division_code='" . $Owndiv . "' and Doc_Qua_ActiveFlag=0";
                $results = performQuery($query);
                break;
	case "prod_feedbk":
			$query = "select FeedBack_Id id,FeedBack_Name name from Mas_Product_Feedback where Division_code='" . $Owndiv . "' and Active_flag=0";
	        $results=performQuery($query); 
		break;
case "vwfolders":
            $result = array();
            $sql = "select Move_MailFolder_Id id,Move_MailFolder_Name name from Mas_Mail_Folder_Name where division_code='$Owndiv'";
          
$result = performQuery($sql);

            array_unshift($result, array("id" => "inbox", "name" => "Inbox"), array("id" => "sent", "name" => "Sent Item"), array("id" => "view", "name" => "Viewed"));
            outputJSON($result);
 
            die;
break;
        case "getmailsf":
            $sfCode = $_GET['sfCode'];
            $divCode = $_GET['divisionCode'];
            $sql = "exec getFullHryList '$sfCode'";
            $mailsSF = performQuery($sql);
           /* foreach ($mailsSF as $k => $v) {
                $mailsSF[$k] ['id'] = $mailsSF[$k] ['sf_code'];
                unset($mailsSF[$k]['sf_code']);
                $mailsSF[$k] ['name'] = $mailsSF[$k] ['Sf_Name'];
                unset($mailsSF[$k]['Sf_Name']);
            }*/
            outputJSON($mailsSF);
            die;
        break;
            case "vwtourplan":
				$sfCode=$_GET['rSF'];								
				$div = $_GET['divisionCode'];
				$divs = explode(",", $div . ",");
				$Owndiv = (string) $divs[0];
				$current=array();
				$next=array();
				$previous=array();
				
                $query = "select  * from vwTourPlan where SF_Code='$sfCode' and worktype_code!='0'";
                $next = performQuery($query);
				
				// $query = "select  * from vwTourPlan_current where SF_Code='$sfCode'  and worktype_code!='0' order by tour_date";
				//echo $query;
				// $current = performQuery($query);
				// $query = "select  * from vwTourPlan_previous where SF_Code='$sfCode'  and worktype_code!='0'";
                // $previous = performQuery($query);
				
				if(count($next)==0)
				$next=holidays_weekly($sfCode,$Owndiv,'next');
				$query = "select  * from vwTourPlan_current where SF_Code='$sfCode'  and worktype_code!='0'";
                $current = performQuery($query);
				if(count($current)==0)
				$current=holidays_weekly($sfCode,$Owndiv,'current');
				$query = "select  * from vwTourPlan_previous where SF_Code='$sfCode'  and worktype_code!='0'";
                $previous = performQuery($query);
				if(count($previous)==0)
				$previous=holidays_weekly($sfCode,$Owndiv,'previous');
				
$result=array();
$result['current']=$current;
$result['next']=$next;
$result['previous']=$previous;
                outputJSON($current);
                die;
                break;

            case "gettpdet":
                $date = date('Y-m-d');
                $query = "select * from vwGetTodayTP where Sf_Code='$RSF'";
                $results = performQuery($query);
                outputJSON($results);
                die;
            case "getexpensedet":
                $date = date('Y-m-d');
                $sfCode = $_GET['rSF'];
                $query = "select Expense_Allowance,Expense_Distance,Expense_Fare,Expense_Total from  Trans_FM_Expense_Detail where cast(Created_Date as date)='$date' and Sf_Code='$sfCode'";
                $head = performQuery($query);
                $query = "select Cal_Type type,Parameter_Name parameter,cast(Amount as int) amount from Trans_Additional_Exp
             		where cast(Created_Date as date)='$date' and Sf_Code='$sfCode'";
                $additional = performQuery($query);
                $results = array();
                if (!empty($head)) {
                    $results['head'] = $head[0];
                    $results['extraDetails'] = $additional;
                }
                outputJSON($results);
                die;
				break;
        
            case "vwleavetype":

                $query = "select Leave_Code id,Leave_SName name,Leave_Name from vwLeaveType where Division_code='" . $Owndiv . "'";
                $results = performQuery($query);
                break;
/*nainar */
		case "map_competitor_product":
				$div = $_GET['divisionCode'];
		        $divs = explode(",", $div . ",");
		        $Owndiv = (string) $divs[0];
				$query = "select Comp_Sl_No as id,Comp_Name as name,Comp_Prd_Sl_No as pid,Comp_Prd_name as pname from Map_Competitor_Product where Division_code='" . $Owndiv . "' and Active_Flag=0"; 
 $results = performQuery($query);
                break;	
/*nainar */
 case "vwhosp_master_app":

                $query = "select Leave_Code id,Leave_SName name,Leave_Name from vwLeaveType where Division_code='" . $Owndiv . "'";
                $results = performQuery($query);
                break;
            case "vwactivity_csh_detail":
                $or = (isset($data['or']) && $data['or'] == 0) ? null : $data['or'];
                $where = isset($data['where']) ? json_decode($data['where']) : null;
                $query = "select * from vwActivity_CSH_Detail where Trans_Detail_Info_Type=" . $or . " and " . join(" or ", $where) . " order by vstTime";

                $results = performQuery($query);
                break;
            default:
                $sfCode = (isset($data['sfCode']) && $data['sfCode'] == 0) ? null : $_GET['sfCode'];

				$div = $_GET['divisionCode'];
		        $divs = explode(",", $div . ",");
		        $Owndiv = (string) $divs[0];
              	$divisionCode = (int) $Owndiv;
               // $divisionCode = $_GET['divisionCode'];

                $today = (isset($data['today']) && $data['today'] == 0) ? null : $data['today'];
                $or = (isset($data['or']) && $data['or'] == 0) ? null : $data['or'];
                $wt = (isset($data['wt']) && $data['wt'] == 0) ? null : $data['wt'];
                $tableName = $data['tableName'];
                $coloumns = json_decode($data['coloumns']);

                $where = isset($data['where']) ? json_decode($data['where']) : null;

                $join = isset($data['join']) ? $data['join'] : null;
                $orderBy = isset($data['orderBy']) ? json_decode($data['orderBy']) : null;

                if (!is_null($or)) {
                    $results = getFromTableWR($tableName, $coloumns, $divisionCode, $sfCode, $orderBy, $where, $join, $today, $wt);
                } else {
                    $results = getFromTable($tableName, $coloumns, $divisionCode, $sfCode, $orderBy, $where, $join, $today, $wt);
                }
                break;
        }

        outputJson($results);
        break;
    case "dcr/updateEntry":
        $data = json_decode($_POST['data'], true);
        $arc = (isset($_GET['arc']) && strlen($_GET['arc']) == 0) ? null : $_GET['arc'];
        $amc = (isset($_GET['amc']) && strlen($_GET['amc']) == 0) ? null : $_GET['amc'];
        deleteEntry($arc, $amc);
        addEntry();
        break;
	case "dcr/reEntry":
        $data = json_decode($_POST['data'], true);
        $arc = (isset($_GET['arc']) && strlen($_GET['arc']) == 0) ? null : $_GET['arc'];
       // $amc = (isset($_GET['amc']) && strlen($_GET['amc']) == 0) ? null : $_GET['amc'];
        //deleteEntry($arc, $amc);
			$query = "exec Delete_reject_dcr '$arc' ";
			performQuery($query); 
        addEntry();
    case "dcr/callReport":
        outputJSON(getCallReport());
        break;
    case "tpview":
        getTPview();
        break;
    case "tpviewdt":
        getDtTPview();
        break;
    case "dcr/updRem":
        updEntry();
        break;

case "get/doctor":
        getDoctorDet();
        break;

    case "dcr/save":
        addEntry();
        break;
		case "save/dcract"; 
$Dact ='';
outputJSON(svDCRActivity($Dact));
break;
case "mailView":
$date=date('Y-m-d H:i:s');
        $id = $_GET['id'];
        $sql = "update trans_mail_detail set Mail_Active_Flag='10',Mail_Read_Date='$date' where Trans_Sl_No=$id";
        performQuery($sql);
        $result['success'] = true;
        outputJSON($result);
        break;
    case "mailMove":
        $folder = $_GET['folder'];
        $id = $_GET['id'];
$date=date('Y-m-d H:i:s');
        $sql = "update trans_mail_detail set Mail_moved_to='$folder',Mail_Active_Flag='12',mail_moved_date='$date' where Trans_Sl_No=$id";
        performQuery($sql);
        $result['success'] = true;
        outputJSON($result);
        break;
    case "mailDel":
        $folder = $_GET['folder'];
$date=date('Y-m-d H:i:s');
        $id = $_GET['id'];
        if ($folder == "Sent")
            $sql = "update MailBox_Details set Mail_SentItem_DelFlag=1 where Trans_Sl_No=$id";
        else
            $sql = "update trans_mail_detail set Mail_Active_Flag='-1',mail_delete_date='$date' where Trans_Sl_No=$id";

        performQuery($sql);
        $result['success'] = true;
        outputJSON($result);
        break;

case "createMail":
        $sf = $_GET['sfCode'];
		$div = $_GET['divisionCode'];
        $divs = explode(",", $div . ",");
        $Owndiv = (string) $divs[0];
        $date = date('Y-m-d H:i');
		$data = json_decode($_POST['data'], true);
		$temp = array_keys($data[0]);
		$vals = $data[0];
        $divCode = $_GET['divisionCode'];
        $file = $vals['fileName'];
		
        if (!empty($file)) {
            $info = pathinfo($file);
            $file_name = basename($file, '.' . $info['extension']);
            $ext = $info['extension'];
            $fileName = $file_name . "_" . $sf . "_" . date('d_m_Y') . "." . $ext;
        } else
            $fileName = "";
        $msg1 = urldecode($vals['message']);
        $msg = trim($msg1, '"');
        $sub1 = urldecode($vals['subject']);
        $sub = trim($sub1, '"');
$sql="select max(isnull(Trans_sl_no,0))+1 transslno from trans_mail_head";
  $tr=performQuery($sql);
$transslno=$tr[0]['transslno'];
        $sql = "insert into trans_mail_head(Trans_sl_no,System_ip,Mail_SF_From,Mail_SF_To,Mail_Subject,Mail_Content,Mail_Attachement,Mail_CC,Mail_BCC,Division_Code,Mail_Sent_Time,To_SFName,CC_Sfname,Bcc_SfName,Mail_SF_Name,sent_flag)
        select '$transslno','','$sf','" . $vals['to_id'] . "','$sub','$msg','$fileName','" . $vals['cc_id'] . "','" . $vals['bcc_id'] . "','$Owndiv','$date','" . $vals['to'] . "','" . $vals['cc'] . "','" . $vals['bcc'] . "','" . $vals['from'] . "',0"; 
		
   performQuery($sql);
      
      
        $ToCcBcc = explode(",", $vals['ToCcBcc']);
		
        for ($i = 0; $i < count($ToCcBcc); $i++) {
            if ($ToCcBcc[$i]) {
                //  Mail_int_Det_No,Mail_View_Color
                $sql = "insert into trans_mail_detail(Trans_Sl_no,open_mail_id,mail_active_flag,Division_code)
                                                   select '$transslno','" . str_replace(",","",$ToCcBcc[$i]) . "',0,'$Owndiv'";

                performQuery($sql);
            }
        }
        $result["success"] = true;
        outputJSON($result);
        break;
    case "fileAttachment":
        $sf = $_GET['sf_code'];
        $file = $_FILES['imgfile']['name'];
        $info = pathinfo($file);
        $file_name = basename($file, '.' . $info['extension']);
        $ext = $info['extension'];
        $fileName = $file_name . "_" . $sf . "_" . date('d_m_Y') . "." . $ext;
        $file_src = '../MasterFiles/Mails/Attachment/' . $fileName;
        move_uploaded_file($_FILES['imgfile']['tmp_name'], $file_src);
        break;
 
    case "get/precall":
        getPreCallDet();
        break;
    case "get/MnthSumm":
        outputJSON(getMonthSummary());
        break;
	case "get/Orders":
        outputJSON(getOrders());
        break;
	case "get/OrderDet":
		outputJSON(getOrderDets());
		break;
    case "get/DayRpt":
        outputJSON(getDayReport());
        break;
	case "get/CheckInRpt":
	    outputJSON(getCheckInReport());
        break;	
	case "get/DayRpt_Geo":
        outputJSON(getDayReport_Geo());
        break;	
	case "get/vwChkTPStatus":
        outputJSON(getvwChkTPStatus());
        break;
	case "get/route_location":
        outputJSON(getRouteLocation());
        break;	
    case "get/vwVstDet":
        outputJSON(getVstDets());
        break;
	case "get/vwVstDet_Geo":
        outputJSON(getVstDets_Geo());
        break;	
	case "get/Rpt_ActivityDet":
		outputJSON(Reports_Activity());
		break;
 	case "get/Visit_monitor":
        outputJSON(getVisitCover());
        break;
	case "get/MissedRpt":
        outputJSON(getMissedReport());
        break;
	case "get/MissedRpt_view":
        outputJSON(getMissedReportDetail());
        break;
	case "get/vist_analysis":
        outputJSON(getvstDetail());
        break;
 	case "save/newdr";
        outputJSON(SvNewDr());
        break;
case "get/class";
        outputJSON(getDocClass());
        break;
    case "get/quali";
        outputJSON(getDocQual());
        break;
case "get/DayCheckInRpt":
	    outputJSON(getDayCheckIn_Report());
        break;
 case "get/categorys";
        outputJSON(getDocCats());
        break;
    case "get/speciality";
        outputJSON(getDocSpec());
        break;
 case "save/todayTP";
        outputJSON(SvMyTodayTP());
        break;
  case "save/tpdaynew";
        outputJSON(SvTourPlanNew(0));
        break;
   case "save/tourplannew";
        outputJSON(SvTourPlanNew(1));
        break;
 case "save/tourplan_fullmonth";
        outputJSON(SvTourPlan_fullmonth(1));
        break;
 case "get/tpapproval";
	      outputJSON(getTPApproval());
	      break;
case "save/tpapprovalnew"; 
        outputJSON(SvTourPlan_approve());
        break;
 case "save/tpreject";
        outputJSON(SvTPReject());
        break; 
case "save/converstion";
        outputJSON(svConversation());
        break;
case "get/conversation";
	      outputJSON(getConversation());
	      break;		
 case "getDoctor_Dob_Dow":
        $sfCode = $_GET['sfCode'];
      //$sfCode = $_GET['month'];
        $sql = "exec getDoctor_Dob_Dow '$sfCode'";
        $result = performQuery($sql);
        outputJSON($result);
        break;
	case "getDoctorNextVisit":
         $sfCode = $_GET['sfCode'];
         $month= $_GET['month'];
		$year= $_GET['year'];
        $sql = "select * from vwDoctorNextVisit where sfcode='$sfCode' and month(date)=$month and year(date)=$year";
        $result = performQuery($sql);
        outputJSON($result);
        break;
	case "getTPAppr":
        $sfCode = $_GET['sfCode'];
	

	
		$sql = "select cast(DT.tp_date as date) tp_date,DT.NextMonth monthname,DT.tpmonth from vwTP_Current_Next DT where DT.sf_code='$sfCode'";
		$currnext= performQuery($sql);

		
        $tp = array();
        
         $tp['currnext'] = $currnext;
          
         $results = array();
  $apprCount = array();
         $query = "select Count(*) dcrappr_count  from DCRMain_Temp d
            inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code
             where d.Confirmed=1 and s.Reporting_To_SF='$sfCode' and cast(Activity_Date as date)<cast(GETDATE() as date)";
//echo $query;
    $temp = performQuery($query);
    $apprCount[] = $temp[0];
    $query = "select Count(*) tpappr_count from vwChkTransApproval where Reporting_To_SF='$sfCode'";
    $temp = performQuery($query);
    $apprCount[] = $temp[0];
    $query = "select Count(*) leaveappr_count from vwLeave vl INNER JOIN vwLeaveType vw ON vl.Leave_Type = vw.leave_code where Reporting_To_SF='$sfCode'";
    $temp = performQuery($query);
    $apprCount[] = $temp[0];
 $query = "select count(*) devappr_count from DCR_MissedDates d inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code where  d.status=3 and Reporting_To_SF='$sfCode'";
    $temp = performQuery($query);
    $apprCount[] = $temp[0];
$results['tp']=$tp;
$results['apprCount']=$apprCount;
   outputJSON($results);
		break;
    case "vwLeaveStatus":
        $sfCode = $_GET['sfCode'];
        $sql = "select * from vwLeaveEntitle where Sf_Code='$sfCode'";
        $leave = performQuery($sql);
        outputJSON($leave);
        break;
    case "LeaveHistory":
        $sfCode = $_GET['sfCode'];
        $div = $_GET['divisionCode'];
		$divs = explode(",", $div . ",");
		$divc = (string) $divs[0];
        $sql = "select a.sf_code,b.division_code,(isnull(b.Leave_SName,'') +' - '+isnull(b.Leave_Name,'') )Leave_type,convert(varchar,a.From_Date,106)From_Date,convert(varchar,a.To_Date,106)To_Date,a.No_of_Days,convert(varchar,a.Created_Date,0) Apply_date,isnull(a.Rejected_Reason,'')Rejected_Reason,isnull(a.Reason,'') leave_Reason,isnull(a.Rejected_Reason,'')Rejected_Reason,a.Address,a.Leave_Active_Flag from mas_Leave_Form a left outer join mas_leave_type b on a.Leave_Type = b.Leave_code where  a.sf_code='$sfCode' and b.division_code='$divc' and year(a.Created_Date)= year(getdate())";
		//echo $sql;
        $leave_det = performQuery($sql);
        outputJSON($leave_det);
        break;
	case "getMailsApp":
        $sfCode = $_GET['sfCode'];
          $div = $_GET['divisionCode'];
        $divs = explode(",", $div . ",");
        $Owndiv = (string) $divs[0];
        $folder = $_GET['folder'];
        $month = $_GET['month'];
        $year = $_GET['year'];
       // $sql = "exec GetMails '$sfCode','$Owndiv','','$folder',$month,$year";
$fldr=$folder;
if($folder!='Inbox'&&$folder!='Sent Item'&&$folder!='Viewed'){
$folder='Flder';
}

$sql = "exec MailInbox_DivCode_New_App '$sfCode','$Owndiv','$folder','$fldr','$year','$month',''";
//print_r($sql);die;
        $mails = performQuery($sql);
        outputJSON($mails);
        die;
        break;
		case "vwProductDetailing":
		      $sfCode = $_GET['sfCode'];
		   		$div = $_GET['divisionCode'];
		        $divs = explode(",", $div . ",");
		        $Owndiv = (string) $divs[0];
		        $sql = "select * from File_info where div_code='$Owndiv'";
				//print_r($sql);die;
		        $medUpload = performQuery($sql);
		        outputJSON($medUpload);
		        die;
		        break;
case "media_inbox":
		        $sfCode = $_GET['sfCode'];
		   		$div = $_GET['divisionCode'];
		        $divs = explode(",", $div . ",");
		        $Owndiv = (string) $divs[0];
		        $sql = "select *,(case when media_sf_from='$sfCode' then 1 else 0 end) mode from Mas_MediaFiles_Info where (media_sf_from='$sfCode' or media_sf_to='$sfCode') and active_flag=0";
				//print_r($sql);die;
		        $medUpload = performQuery($sql);
		        outputJSON($medUpload);
		        die;
		        break;
		 case "vwMedUpdateUpload":
		        $sfCode = $_GET['sfCode'];
		   		$div = $_GET['divisionCode'];
		        $divs = explode(",", $div . ",");
		        $Owndiv = (string) $divs[0];
		        $sql = "select * from vwMedUpdateUpload where Division_Code='$Owndiv'";
				//print_r($sql);die;
		        $medUpload = performQuery($sql);
		        outputJSON($medUpload);
		        die;
		        break;
		case "vaccancyList":            
			$divCode = $_GET['divisionCode'];
	        $sql = "select Sf_HQ HQName,sf_name from  Mas_Salesforce where sf_tp_active_flag=1 and sf_type=1 and division_code='$divCode'";
	        $vaccancyList = performQuery($sql);
	        outputJSON($vaccancyList);
	        die;
	        break;
      case "vwLeave":
            $sfCode = $_GET['sfCode'];
            //$sql = "select * from vwLeave where Reporting_To_SF='$sfCode'";
		$sql = "select * from vwLeave vl INNER JOIN vwLeaveType vw ON vl.Leave_Type = vw.leave_code where Reporting_To_SF='$sfCode'";
        
            $leave = performQuery($sql);
            outputJSON($leave);
            break;
    case "vwCheckLeave":
        $sfCode = $_GET['sfCode'];
        $date = date('Y-m-d');
        $sql = "select From_Date,To_Date,No_of_Days from mas_Leave_Form where To_Date>='$date' and sf_code='$sfCode' and Leave_Active_Flag !=1 order by From_Date";
        $leaveDays = performQuery($sql);
        $currentDate = date_create($date);
        $disableDates = array();
        $sql = "SELECT * FROM vwActivity_Report where SF_Code='" . $sfCode . "' and cast(activity_date as datetime)=cast('$date' as datetime)";
        $dcrEntry = performQuery($sql);
        if (count($dcrEntry) > 0)
			array_push($disableDates);
           // array_push($disableDates, $currentDate->format('d/m/Y'));
            
        for ($i = 0; $i < count($leaveDays); $i++) {
            $fromDate = $leaveDays[$i]['From_Date'];
            $toDate = $leaveDays[$i]['To_Date'];
            $noOfDays = $leaveDays[$i]['No_of_Days'];
            if ($currentDate > $fromDate)
                $fromDate = $currentDate;
            $diff = date_diff($fromDate, $toDate, TRUE);
            $days = $diff->format("%a") + 1;
            for ($j = 0; $j < $days; $j++) {
                array_push($disableDates, $fromDate->format('d/m/Y'));
                $fromDate->modify('+1 day');
            }
        }
        outputJSON($disableDates);
        break;
    case "vwLeave":
        $sfCode = $_GET['sfCode'];
        $sql = "select * from vwLeave where Reporting_To_SF='$sfCode'";
        $leave = performQuery($sql);
        outputJSON($leave);
        break;
    case "vwDcr":
        $sfCode = $_GET['sfCode'];
        $sql = "select d.Plan_Name,d.Trans_SlNo,d.Sf_Code,d.FieldWork_Indicator,d.WorkType_Name,d.Sf_Name,convert(varchar,Activity_Date,103) Activity_Date,s.Reporting_To_SF from DCRMain_Temp d
            inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code
             where d.Confirmed=1 and s.Reporting_To_SF='$sfCode' and cast(Activity_Date as date)<cast(GETDATE() as date)";
        $dcr = performQuery($sql);
        outputJSON($dcr);
        break;
    case "vwDcrOne":
        $TransSlNo = $_GET['Trans_SlNo'];
        $sql = "exec getDCRApprovalApp '" . $TransSlNo . "'";
        $dcr = performQuery($sql);
        outputJSON($dcr);

        break;
    case "vwChkTransApproval":
        $sfCode = $_GET['sfCode'];
        $sql = "select * from vwChkTransApproval where Reporting_To_SF='$sfCode'";
        $tp = performQuery($sql);
        outputJSON($tp);
        break;
    case "vwChkTransApprovalOne":
        $sfCode = $_GET['code'];
        $month = $_GET['month'];
        $year = $_GET['year'];
        $sql = "select Objective,Worked_With_SF_Name,Worktype_Name_B,Worktype_Name_B1,Worktype_Name_B2,Tour_Schedule1,Tour_Schedule2,Tour_Schedule3,convert(varchar,Tour_Date,103) Tour_Date from Trans_TP_One where Sf_Code='$sfCode' and Tour_Month = '$month' order by Tour_Date";
        $tp = performQuery($sql);
        outputJSON($tp);
        break;
	case "vwChkDevApproval":
        $sfCode = $_GET['sfCode'];
		$month = $_GET['month'];
		$year = $_GET['year'];
        $sql = "select Dcr_Missed_Date,CONVERT(VARCHAR(10), Dcr_Missed_Date, 103) missed_date,s.sf_code,s.sf_name,sl_no,isnull(b.Deviation_Reason,'')Deviation_Reason from DCR_MissedDates d	
            inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code	
            inner join dcr_tpdev_reason b on d.sf_code= b.sf_code and cast( Dcr_Missed_Date as datetime)=cast( b.Activity_Date as datetime)
             where d.status=3 and Reporting_To_SF='$sfCode' and month(Dcr_Missed_Date)=(case when 'All'='".$month."'then month(Dcr_Missed_Date) else '".$month."' end) and year(Dcr_Missed_Date)=(case when 'All'='".$year."'then year(Dcr_Missed_Date) else '".$year."' end) and month(b.Activity_Date)=(case when 'All'='".$month."'then month(b.Activity_Date) else '".$month."' end) and year(b.Activity_Date)=(case when 'All'='".$year."'then year(b.Activity_Date) else '".$year."' end)";
//secho $sql;
/* $sql = "select Dcr_Missed_Date,CONVERT(VARCHAR(10), Dcr_Missed_Date, 103) missed_date,s.sf_code,s.sf_name,sl_no from DCR_MissedDates d
            inner join Mas_Salesforce_One s on d.Sf_Code=s.Sf_Code
             where d.status=3 and Reporting_To_SF='$sfCode' and month(Dcr_Missed_Date)=$month and year(Dcr_Missed_Date)=$year";*/

        $tp = performQuery($sql);
        outputJSON($tp);
        break;	

    case "entry/count":
        //$today = date('Y-m-d 00:00:00');
		$eDate = $_GET['eDate'];
		if($eDate =='' || $eDate == null)	
		$today = date('Y-m-d 00:00:00');
		else 
		$today = date("Y-m-d 00:00:00", strtotime($eDate));

       // $today = date('Y-m-d 00:00:00');
        $sfCode = $_GET['sfCode'];

        $sql = "SELECT Employee_Id,case sf_type when 1 then 'MR' else 'MGR' End SF_Type FROM Mas_Salesforce_One where SF_code='" . $sfCode . "'";
//echo $sql;
        $as = performQuery($sql);
        $SFTyp = (string) $as[0]['SF_Type'];

        $query = "SELECT work_Type worktype_code,Remarks daywise_remarks,Half_Day_FW halfdaywrk from vwActivity_Report H where SF_Code='" . $sfCode . "' and FWFlg <> 'F' and cast(activity_date as datetime)=cast('$today' as datetime)";
//echo $query;
        $data = performQuery($query);
        $result = array();
        if (count($data) > 0) {
            $result["success"] = false;
            $result['data'] = $data;
            outputJSON($result);
            die;
        }
        $result["success"] = true;
        $result['data'] = getEntryCount();
        outputJSON($result);
        break;

	case "callCount1":
	        $sfCode = $_GET['code'];
	        $month = $_GET['month'];
	        $year = $_GET['year'];	
			$divCode = $_GET['divisionCode'];
			$divisionCode = explode(",", $divCode);
			$MSL = $_GET['Msl_No'];	
			$trs_code =$_GET['trs_code'];	
			$sql = "SELECT '$MSL' Msl_No,COUNT(Trans_Detail_Info_Code) as Visit_tl FROM vwActivity_MSL_Details WHERE division_code='$divisionCode[0]' and sf_code='$sfCode' and Trans_Detail_Info_Code='$MSL' and year(time) = '$year' and month(time) ='$month' and Trans_Detail_Slno!='$trs_code' ";
//echo $sql;		
	        $cct = performQuery($sql);
        	outputJSON($cct);
			//$results = performQuery($sql);
			 //echo $results;
			//return $results;
	        break;
	case "mulGeotag":
		$sfCode = $_GET['sfCode'];
		$sql ="select ListedDrCode as id,count(ListedDrCode) tagcnt  from  Mas_ListedDr D INNER JOIN  vwMap_GEO_Customers g ON Cust_Code = ListedDrCode where sf_code='$sfCode' group by ListedDrCode";
  		$mtag = performQuery($sql);
		outputJSON($mtag);
		break;
	case "checkin_details":
		$sfCode = $_GET['sfCode'];
		$date=date("Y-m-d");
               $query = "select top 1 convert(varchar,id) id,LTRIM(RIGHT(CONVERT(VARCHAR(20), Start_Time, 100), 7)) name, (CASE WHEN End_Time IS NULL THEN '1' ELSE '0' END) AS status,CONVERT(VARCHAR, Start_Time, 100) Start_Time from Attendance_history where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='" . $date . "' order by id desc";
  		$chk = performQuery($query);
		outputJSON($chk);
		break;
		case "getsurvey": 
		$sfCode = $_GET['sfCode'];
		$div = $_GET['divisionCode'];
		$divs = explode(",", $div . ",");
		$Owndiv = (string) $divs[0];
		$survey_details=[];
		$query = "select  Survey_ID id,Survey_Title name,CONVERT(varchar,Effective_From_Date,23) as from_date,CONVERT(varchar,Effective_To_Date,23) as to_date from Mas_Question_Survey_Creation_Head where division_code='$Owndiv' and Close_flag='0' and Active_Flag='0' and cast(effective_from_date as date)<=cast(GETDATE() as date) and cast(effective_to_date as date)>=cast(GETDATE() as date) order by Survey_ID desc"; 
		$surveytitle = performQuery($query);


		for($i=0;$i<count($surveytitle);$i++){
			$survey_id = $surveytitle[$i]['id']; 
			//$survey_details[$i]['survey_title']= $surveytitle[$i];
			$query = "select Question_Id id,Survey_ID Survey,Doctor_Category DrCat,Doctor_Speclty DrSpl,Doctor_Cls DrCls,Hospital_Class HosCls,Chemist_Category ChmCat,Stockist_State Stkstate,Stockist_HQ StkHQ,Processing_Type Stype from Mas_Question_Survey_Creation_Detail where division_code='$Owndiv' and Survey_id='$survey_id' and  charindex(','+'$sfCode'+',',','+SF_Code+',')>0 and isNull(SF_Code,'')<>''";
			$surveyfor = performQuery($query);
			// if(count($surveyfor)>0){
			$survey_details[$i]= $surveytitle[$i];
			$survey_details[$i]['survey_for'] =[];
				for($j=0;$j<count($surveyfor);$j++){
				$Survey=$surveyfor[$j]['Survey'];
				//$survey_details[$i]['survey_for'] ='';
					if($survey_id==$Survey){
						$query = "select sc.Question_Id id,Survey_ID Survey,Doctor_Category DrCat,Doctor_Speclty DrSpl,Doctor_Cls DrCls,Hospital_Class HosCls,Chemist_Category ChmCat,Stockist_State Stkstate,Stockist_HQ StkHQ,Processing_Type Stype,Control_Id Qc_id,Control_Name Qtype,Control_Para Qlength,'0' Mandatory,Question_Name Qname,Question_Add_Names Qanswer,Active_Flag from Mas_Question_Survey_Creation_Detail sc
						inner join Mas_Question_Creation qc on qc.Question_Id=sc.Question_Id
						where sc.division_code='$Owndiv' and Survey_id='$survey_id' and  charindex(','+'$sfCode'+',',','+SF_Code+',')>0";
						$ssurveydetail  = performQuery($query);
						$survey_details[$i]['survey_for']=$ssurveydetail;
					}
				}	
			// }
		}
		outputJSON($survey_details);
		die;
		break;
		
		case "getdivision_ho_sf":
		$sfCode = $_GET['sfCode'];
		$HOID = $_GET['Ho_Id'];
		$query = "exec getDivision '" . $HOID . "','" . $sfCode . "'";
        $results = performQuery($query);	
		outputJSON($results);
		break;
		case "tp_objective":
			$divCode = $_GET['divisionCode'];
			$divisionCode = explode(",", $divCode);
/*			$query = "select id,objective_name name from mas_tp_objective where division_code='$divisionCode[0]' and status=0";*/
			$query = "select id,objective_name name from mas_tp_objective where status=0";
			$results = performQuery($query);	
			outputJSON($results);
	break;	
	case "delete_dcr":
		$sfCode = $_GET['sfCode'];
         $dcr_dt= $_GET['dcr_dt'];
		 $query="exec DelDCRTempByDt '".$sfCode."','" .$dcr_dt ."'"; 
		 performQuery($query);
		 $results['success'] = true;
		 outputJSON($results);
	break;
	case "user_update":   
		$data = json_decode($_POST['data'], true);
		$pass = $data['password'];
		$sfCode = $_GET['sfCode'];
		$divCode = $_GET['divisionCode'];
		$query = "update mas_salesforce set Sf_Password='".$pass."' where Sf_code='" . $sfCode . "' and Division_Code='" . $divCode . "' ";
		performQuery($query);
		//echo $query;
		// $results ["name"] = str_replace("'", "",$pass);
		  $results ["success"] = true;
		  $results ["qry"] = $query;
		  outputJSON($results);
		//die;
		break;
	case "get/last_checkindetails";
	$sfCode = $_GET['sfCode'];
			$divCode = $_GET['divisionCode'];
			$divisionCode = explode(",", $divCode);
			$date=date("Y-m-d");
			$sql ="select top 1 convert(varchar,id)id,LTRIM(RIGHT(CONVERT(VARCHAR(20), Start_Time, 100), 7)) name, (CASE WHEN End_Time IS NULL THEN '1' ELSE '0' END) AS status,Activity_date,CONVERT(VARCHAR, Start_Time, 100) Start_Time from Attendance_history where Sf_Code='$sfCode' and DATEADD(dd, 0, DATEDIFF(dd,0,Start_Time))='" . $date . "' order by id desc"; 
			$day_checkin=performQuery($sql);  
			$sql ="select top 1 convert(varchar,Cust_id)id,Cust_name name,Activity_date,Checkin_time,Checkout_time,convert(varchar,Status)Status from Dcr_checkin where sf_code='$sfCode' and Activity_date='" . $date . "' and status!=1 order by ID DESC"; 
			$Cust_checkin=performQuery($sql);  
			$results=array();
			$results['Day_Checkin']=$day_checkin;
			$results['Customer_Checkin']=$Cust_checkin;
	outputJSON($results);
	      break;	
	case "Leavevalidate":
	$data = json_decode($_POST['data'], true);
	//	$sf_code = (string) $data['sf_code'];
$sf_code = $_GET['sfCode'];
		$lv_type = (string) $data['lv_type'];
		$fdate = strtotime(str_replace("Z", "", str_replace("T", " ", $data['fdate'])));
		$todate = strtotime(str_replace("Z", "", str_replace("T", " ", $data['todate'])));
		$from =date('Y-m-d 00:00:00', $fdate);
		$todt =date('Y-m-d 00:00:00', $todate);
		$query = "exec iOS_getLvlValidate '".$sf_code."','".$from."','".$todt."','".$lv_type."' ";

		$result=performQuery($query); 
		outputJSON($result);	
		break;
	// case "get/Missedflag":
		// $sfCode = $_GET['sfCode'];
		// $divCode = $_GET['divisionCode'];
		// $divisionCode = explode(",", $divCode);
		// $query = "exec getLockflag '" . $sfCode . "','".$divisionCode[0]."'";
		// $arr =performQuery($query); 
		// //$results ["missflag"]=$arr[0]["missflag"];
		// $results ["missflag"]=1;
		// outputJSON($results);
		// break;
		case "get/Missedflag":
		$sfCode = $_GET['sfCode'];
		$divCode = $_GET['divisionCode'];
		$divisionCode = explode(",", $divCode);
		$query = "exec getLockflag_App '" . $sfCode . "','".$divisionCode[0]."'";
		$arr =performQuery($query);
		$results ["missflag"]=$arr[0]["missflag"];
		outputJSON($results);
		break;
	case "get/CatVstFrq":
        	$sfCode = $_GET['sfCode'];
		$query = "exec GetCatVstCMn '".$sfCode."'";
		outputJSON(performQuery($query));	
		break;
	case "get/DaySummCnt":
        	$sfCode = $_GET['sfCode'];
		$query = "exec getCusVstDet '".$sfCode."'";
		$RsArr=performQuery($query);

		$query = "exec getWTVstDet '".$sfCode."'";
		$RsArr1=performQuery($query);

		$rslt=[$RsArr,$RsArr1];
		outputJSON($rslt);	
		break;
	case "get/tpsetup":       
		outputJSON(getTpSetup());       
		break;
		case "version_validate":
				$results=array();
				$results['msg']='';
				$results['link']=''; 
		  outputJSON($results);
		  break;
		case "version_validate1":
			$data1 = json_decode($_POST['data'], true);//print_r("data:".$_POST['data']);
			$ver=$data1[0]['version_number'];
			if($ver!='1.1.6'){
				$results=array();
				$results['msg']='You are using old version.Kindly download & update your app';
				$results['link']='http://sansfe.info/apps/sansfe-v15.apk';//'http://sansfe.info/apps/SFE116.apk'; 
			}else{
                $results=array();
				$results['msg']='';
				$results['link']=''; 
            }
			outputJSON($results);
		break;
		case "get/dynactivity";
	      outputJSON(getDynamicActivity());
	      break;
   		case "get/dynview";
	      outputJSON(getDynamicView());
	      break;
 		case "get/dynviewDetail";
	      outputJSON(getDynamicViewDetail());
	      break;
		case "get/dynviewDetail_tp";
	      outputJSON(getDynamicViewDetail_tp());
	      break;
		  // case "rcpa_detail":
				// $arc=$_GET['arc'];
				// $arc_dt=$_GET['arc_dt'];
				// $sfCode=$_GET['sfCode'];
				// $call_rcpa_details=[];
				// $query = "select * from Trans_RCPA_Head head where SF_Code ='$sfCode' and AR_Code='$arc' and ARMSL_Code='$arc_dt'";
				// //echo $query;
				// $rcpa = performQuery($query);
				// $rcpaid=$rcpa[0]['PK_ID'];				 
				// //for loop need
				// $query = "select * from Trans_RCPA_Detail where FK_PK_ID='$rcpaid'";
                // $rcpa_det = performQuery($query);
				 
				 // //$results=[];
				// $call_rcpa_details[0]=$rcpa;
				// $call_rcpa_details[1]=$rcpa_det;
				// outputJSON($call_rcpa_details);
				
                // break;
		
    	case "save/trackloc1":
			$data1 = json_decode($_POST['data'], true);
			$TrcLocs=$data1[0]['TrackLoction'];
			$sfCode=$TrcLocs['SF_code'];
			$TLocs=$TrcLocs['TLocations'];
			$sql = "select sf_emp_id,Employee_Id from Mas_Salesforce where Sf_Code='$sfCode'";
			$sf = performQuery($sql);
			$empid = $sf[0]['sf_emp_id'];
			$employeeid = $sf[0]['Employee_Id'];
			for($ik=0;$ik<count($TLocs);$ik++){
				$lng = $TLocs[$ik]['Longitude'];
				$lat = $TLocs[$ik]['Latitude'];
				
				$address = getaddress($lat, $lng);
				$sql = "insert into tbTrackLoction(SF_code,Emp_Id,Employee_Id,DtTm,Lat,Lon,Addr,Auc,deg,DvcID) select '$sfCode','$empid','$employeeid','" . $TLocs[$ik]['Time'] . "','$lat','$lng','','" . str_replace("'", "", $TLocs[$ik]['Accuracy']) . "','" . str_replace("'", "", $TLocs[$ik]['Bearing']) . "','" . str_replace("'", "", $TrcLocs['DvcID']) . "'";
				
				performQuery($sql);
			}
			break;
		case "get/DocNxtVisit":
			$sfCode = $_GET['sfCode'];
			$rptSF = $_GET['rSF'];
			$divC = $_GET['divisionCode'];
			$query = "exec Get_DocNxtVist '".$rptSF."'"; 
			outputJSON(performQuery($query));
		break;
		case "save/livetrack":
			$sfCode= $_GET['sfCode'];
			$sql = "SELECT sf_emp_id,Employee_Id FROM Mas_Salesforce WHERE Sf_Code='$sfCode'";
			$sf = performQuery($sql);
			$empid = $sf[0]['sf_emp_id'];
			$employeeid = $sf[0]['Employee_Id'];
			$data = json_decode($_POST['data'], true);
			$TrcLocs=$data;
			for($ik=0;$ik<count($TrcLocs);$ik++){
				$sql = "insert into tbTrackLoction(SF_code,Emp_Id,Employee_Id,DtTm,Lat,Lon,Addr,Auc,EMod,Battery,SF_Mobile,updatetime,IsOnline) select '$sfCode','$empid','$employeeid','".$TrcLocs[$ik]['time']."','".$TrcLocs[$ik]['Latitude'] ."','".$TrcLocs[$ik]['Longitude']."','".$TrcLocs[$ik]['Address']."','','Apps','".$TrcLocs[$ik]['Battery']."','".$TrcLocs[$ik]['Mobile']."',getdate(),'".$TrcLocs[$ik]['IsOnline']."'";
				performQuery($sql);
			}
			$result = array();
			$result['success'] = true;
			outputJSON($result);			
		break;
		case "get/live_track_SF":
			$sfCode= $_GET['sfCode'];
			$Div = str_replace(",,", ",", $_GET['divisionCode']);
			$query = "SELECT Sf_Code,Sf_Name,SF_Mobile FROM Mas_Salesforce WHERE Reporting_To_SF='".$sfCode."'";
			$results = performQuery($query);	
			outputJSON($results);			
		break;
		case "save/stockistprimary":
				$SFCode = $_GET['sfCode'];
				$SFName= $_GET['sf_name'];
				$div = $_GET['divisionCode'];
				$divs = explode(",", $div . ",");
				$DivCode = (string) $divs[0];
				$JSONArray = json_decode($_POST['data'], true);
				for ($i = 0; $i < count($JSONArray); $i++) 
				{
					$Stockist_Code=(string) $JSONArray[$i]['Stockist_Code'];
					$Stockist_Name=(string) $JSONArray[$i]['Stockist_Name'];
					$Trans_Month=(string) $JSONArray[$i]['Trans_Month'];
					$Trans_Year=(string) $JSONArray[$i]['Trans_Year'];
					$Pri_Value=(string) $JSONArray[$i]['Pri_Value'];
					$Sec_Value=(string) $JSONArray[$i]['Sec_Value'];

					$sql = "select * from Trans_Pri_Sec_Sale where Trans_Month='$Trans_Month' and Trans_Year='$Trans_Year' and sf_code='$SFCode' and Division_Code='$DivCode' and Stockist_Code='$Stockist_Code'";
					$data1 = performQuery($sql);
					if (count($data1)>0) {	
						$query1 = "Update Trans_Pri_Sec_Sale set Pri_Value='$Pri_Value',Sec_Value='$Sec_Value',Updated_Date=getdate() where Trans_Month='$Trans_Month' and Trans_Year='$Trans_Year' and sf_code='$SFCode' and Division_Code='$DivCode' and Stockist_Code='$Stockist_Code'";
						performQuery($query1);
					}else{
						$query2 = "SELECT isNull(max(Sl_No),0)+1 as RwID FROM Trans_Pri_Sec_Sale";
						$trw = performQuery($query2);
						$pk = (int) $trw[0]['RwID'];
						$query3 = "insert into Trans_Pri_Sec_Sale(Sl_No,Stockist_Code,Stockist_Name,Trans_Month,Trans_Year,Pri_Value,Sec_Value,Division_Code,Created_Date,
						Approved_Flag, View_Flag, Entry_Mode,sf_code) select '$pk','$Stockist_Code','$Stockist_Name','$Trans_Month','$Trans_Year','$Pri_Value','$Sec_Value','$DivCode',getdate(),'0','0','Apps','$SFCode'";
						performQuery($query3);
					}
				}
				$results['success'] = true;
				outputJSON($results);
			break;
			
			case "get/stockistprimary":
				$SFCode = $_GET['sfCode'];
				$rptSF = $_GET['rSF'];
				$Trans_Month= $_GET['month'];
				$Trans_Year= $_GET['year'];
				$div = $_GET['divisionCode'];
				$divs = explode(",", $div . ",");
				$DivCode = (string) $divs[0];
				$query = "select Sl_No,Stockist_Code,Stockist_Name,Trans_Month,Trans_Year,Pri_Value,Sec_Value,Division_Code,Approved_Flag,View_Flag,sf_code from Trans_Pri_Sec_Sale where Trans_Month='$Trans_Month' and Trans_Year='$Trans_Year' and Division_Code='$DivCode'";
				$data = performQuery($query);
				$result = array();
				if (count($data) == 0) {
					$results[] = $data[0];
					outputJSON($result);
				}else{
					outputJSON($data);
				}
			break;	
		case "fieldForce_holiday":
			$sfCode = $_GET['sfCode'];
			$div = $_GET['divisionCode'];
			$year = $_GET['year'];
			
			$query = "exec getHolidays_SF '".$sfCode."','".$div."','".$year."'"; 
			outputJSON(performQuery($query));
			
			break;
		case "fieldforce_weekoff":
			$sfCode = $_GET['sfCode'];
			$div = $_GET['divisionCode'];
			$stateCode = $_GET['stateCode'];
			
			$query = "exec getWeeklyoff_SF '".$sfCode."','".$div."','".$stateCode."'"; 
			outputJSON(performQuery($query));
			break;
		case "get/visit_control":
			$SFCode = $_GET['sfCode'];

			$query = "select CustCode,CustType, convert(varchar, Vst_Date, 23)Dcr_dt,month(Vst_Date) Mnth,year(Vst_Date) Yr,CustName,isnull(SDP,'')town_code,isnull(SDP_Name,'')town_name,1 Dcr_flag from tbVisit_Details where SF_Code='$SFCode' and CustType=1 and  cast(CONVERT(varchar,Vst_Date,101)as datetime) >= DATEADD(mm, DATEDIFF(mm, 0, GETDATE()) - 1, 0) order by Vst_Date";
			//echo $query;
			$results = performQuery($query);	
			outputJSON($results);
			break;
		case "travel_Distance":
		$sfCode = $_GET['sfCode'];
		$data = json_decode($_POST['data'], true);
		$data1 = array_keys($data[0]);
		$vals = $data[0][$data1[0]];
		
		
		$sql = "select id from distance_Travelled where activity_date = '". $vals["date"] ."'";
		$idNo = performQuery($sql);
		
		$idValue = $idNo[0]['id'];
		
		if (count($idNo)>0){
			$query1 = "update distance_Travelled set travel_km = '". $vals["km"] ."' , remarks = '". $vals["remarks"] ."' , update_time = '". $vals["submitted_Time"] ."' where id ='$idValue'";
			performQuery($query1);
		}else {
				
			$query2 = "insert into distance_Travelled (sfName,sfCode,divisionCode,remarks,travel_km,emp_id,activity_date,submitted_time) select '". $vals["sfName"] ."','". $vals["sfCode"] ."','". $vals["divisionCode"] ."','". $vals["remarks"] ."','". $vals["km"] ."', sf_emp_id ,'". $vals["date"] ."','". $vals["submitted_Time"] ."' from Mas_Salesforce where Sf_Code = '$sfCode'";
			performQuery($query2);
		}
		$results['success'] = true;
		outputJSON($results);
		break;
	case "svfeedback_entry":  
		$Fdata = json_decode($_POST['data'], true);	
		$query = "insert into SF_Feedback_form (SF_Code,SF_name,Site,Division_Code,Feedback_remark,Created_dtm,status) select '".$Fdata['sfCode'] ."','".$Fdata['sf_name'] ."','".$Fdata['weburl'] ."','".$Fdata['divisionCode'] ."','".$Fdata['remarks'] ."',getdate(),'0'";
		//echo $query;
		performQuery($query);	
		$results['success'] = true;
		outputJSON($results);	
		break;
}

?>