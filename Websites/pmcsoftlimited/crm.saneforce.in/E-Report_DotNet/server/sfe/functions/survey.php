<?php

function Survey_App() {
    $data = $GLOBALS['Data'];
    $temp = array_keys( $data[ 0 ] );
    $vals = $data[ 0 ][ $temp[ 0 ] ];
    $DivisionCode = str_replace( ",", "", $_GET[ 'divisionCode' ]);
    for ( $i = 0; $i < count( $vals ); $i++ ) {
        $sql = "exec sv_Survey '" . date( 'Y-m-d 00:00:00' ) . "','$DivisionCode','" . $vals[ $i ][ 'Survey_Id' ] . "','" . $vals[ $i ][ 'Question_Id' ] . "','".$_GET[ 'sfCode' ]."','" . $vals[ $i ][ 'Doctor_code' ] . "','" . $vals[ $i ][ 'Chemist_code' ] . "','" . $vals[ $i ][ 'Trans_month' ] . "','" . $vals[ $i ][ 'Trans_year' ] . "','" . date( 'Y-m-d H:i:s' ) . "','" . $vals[ $i ][ 'Answer' ] . "'";
        performQuery( $sql );
    }
}

function Survey( $SF_Code, $DivisionCode ) {
    $survey_details = [];
    $query = "select  Survey_ID id,Survey_Title name,CONVERT(varchar,Effective_From_Date,23) as from_date,CONVERT(varchar,Effective_To_Date,23) as to_date from Mas_Question_Survey_Creation_Head where division_code='" . $DivisionCode . "' and Close_flag='0' and Active_Flag='0' and cast(effective_from_date as date)<=cast(GETDATE() as date) and cast(effective_to_date as date)>=cast(GETDATE() as date) order by Survey_ID desc";
    SurveyResult( $SF_Code, $DivisionCode, performQuery( $query ) );
}

function SurveyResult( $SF_Code, $DivisionCode, $SurveyTitle ) {
    for ( $i = 0; $i < count( $SurveyTitle ); $i++ ) {
        $query = "select Question_Id id,Survey_ID Survey,Doctor_Category DrCat,Doctor_Speclty DrSpl,Doctor_Cls DrCls,Hospital_Class HosCls,Chemist_Category ChmCat,Stockist_State Stkstate,Stockist_HQ StkHQ,Processing_Type Stype from Mas_Question_Survey_Creation_Detail where division_code='" . $DivisionCode . "' and Survey_id='" . $SurveyTitle[ $i ][ 'id' ] . "' and  charindex(','+'$SF_Code'+',',','+SF_Code+',')>0 and isNull(SF_Code,'')<>''";
        $surveyfor = performQuery( $query );
        $survey_details[ $i ] = $SurveyTitle[ $i ];
        $survey_details[ $i ][ 'survey_for' ] = [];
        for ( $j = 0; $j < count( $surveyfor ); $j++ ) {
            $Survey = $surveyfor[ $j ][ 'Survey' ];
            if ( $SurveyTitle[ $i ][ 'id' ] == $Survey ) {
                $query = "select sc.Question_Id id,Survey_ID Survey,Doctor_Category DrCat,Doctor_Speclty DrSpl,Doctor_Cls DrCls,Hospital_Class HosCls,Chemist_Category ChmCat,Stockist_State Stkstate,Stockist_HQ StkHQ,Processing_Type Stype,Control_Id Qc_id,Control_Name Qtype,Control_Para Qlength,'0' Mandatory,Question_Name Qname,Question_Add_Names Qanswer,Active_Flag from Mas_Question_Survey_Creation_Detail sc
						inner join Mas_Question_Creation qc on qc.Question_Id=sc.Question_Id
						where sc.division_code='" . $DivisionCode . "' and Survey_id='" . $SurveyTitle[ $i ][ 'id' ] . "' and  charindex(','+'$SF_Code'+',',','+SF_Code+',')>0";
                $ssurveydetail = performQuery( $query );
                $survey_details[ $i ][ 'survey_for' ] = $ssurveydetail;
            }
        }
    }
    outputJSON( $survey_details );
}
?>