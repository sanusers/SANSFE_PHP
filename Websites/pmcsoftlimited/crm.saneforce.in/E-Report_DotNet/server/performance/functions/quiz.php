<?php

function Quiz( $SF_Code, $DivCode ) {
    $query = "SELECT survey_id, quiz_title, SUBSTRING(filepath, CHARINDEX(')', filepath)+1, LEN(filepath)) [FileName] FROM QuizTitleCreation WHERE division_code='" . $DivCode . "' AND active=0 AND MONTH(effective_date)=MONTH(GETDATE())AND YEAR(effective_date)=YEAR(GETDATE())AND CAST(effective_date AS DATE)<=CAST(GETDATE() AS DATE) ORDER BY survey_id DESC";
    $quiztitle = performQuery( $query );
    $quiztitle1 = array();
    $processUser1 = array();
    for ( $i = 0; $i < count( $quiztitle ); $i++ ) {
        $surveyid = $quiztitle[ $i ][ 'survey_id' ];
        $query = "SELECT NoOfAttempts [type], [Type] NoOfAttempts, timelimit FROM Processing_UserList WHERE surveyid='" . $surveyid . "' AND sf_code='" . $SF_Code . "' AND process_status='P' AND CAST(from_date AS DATE)<=CAST(GETDATE() AS DATE)AND CAST(to_date AS DATE)>=CAST(GETDATE() AS DATE)";
        $processUser = performQuery( $query );
        if ( count( $processUser ) > 0 ) {
            $processUser1 = $processUser;
            $quiztitle1 = $quiztitle[ $i ];
            $quiztitle = array();
        }
    }
    $processUser = array();
    $processUser = $processUser1;
    $quiztitle = array();
    $quiztitle[ 0 ] = $quiztitle1;
    $surveyid = $quiztitle[ 0 ][ 'survey_id' ];
    if ( $quiztitle[ 0 ][ 'FileName' ] != "" ) {
        if ( $extn == "png" || $extn == "jpg" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "image/png";
        } else if ( $extn == "doc" || $extn == "dot" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "application/msword";
        } else if ( $extn == "docx" || $extn == "DOCX" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "application/msword";
        } else if ( $extn == "xls" || $extn == "xlt" || $extn == "xla" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "application/vnd.ms-excel";
        } else if ( $extn == "xlsx" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        } else if ( $extn == "mp4" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "video/mp4";
        } else if ( $extn == "pptx" ) {
            $quiztitle[ 0 ][ 'mimetype' ] = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        } else {
            $quiztitle[ 0 ][ 'mimetype' ] = "application/" + $extn;
        }
        $extn = end( explode( '.', $quiztitle[ 0 ][ 'FileName' ] ) );
    }
    $query = "SELECT Question_Type_id, Question_Id, Question_Text, surveyid FROM AddQuestions WHERE surveyid='" . $surveyid . "' ORDER BY question_id ASC";
    $questions = performQuery( $query );
    $query = "SELECT input_id, Question_Id, Input_Text, Correct_Ans FROM AddInputOptions WHERE question_id IN(SELECT question_id FROM AddQuestions WHERE surveyid='" . $surveyid . "') ORDER BY question_id ASC";
    $answers = performQuery( $query );
    $results = array();
    $results[ 'quiztitle' ][ 0 ] = $quiztitle[ 0 ];
    $results[ 'processUser' ] = $processUser;
    $results[ 'questions' ] = $questions;
    $results[ 'answers' ] = $answers;
    if ( count( $processUser ) == 0 )
        $results = array();
    outputJSON( $results );
}

function Quiz_Result( $SF_Code, $DivCode ) {
    $data = $GLOBALS[ 'Data' ];
    $temp = array_keys( $data[ 0 ] );
    $vals = $data[ 0 ][ $temp[ 0 ] ];
    $quizresults = $vals[ 0 ];
    $first = $vals[ 1 ][ 0 ];
    $surveyId = $first[ 'survey_id' ];
    $firstStartTime = $first[ 'start' ];
    $firstEndTime = $first[ 'end' ];
    if ( $first[ 'NoOfAttempts' ] == "2" ) {
        $second = $vals[ 2 ][ 0 ];
        $secStartTime = $second[ 'start' ];
        $secEndTime = $second[ 'end' ];
    } else {
        $secStartTime = "";
        $secEndTime = "";
    }
    for ( $i = 0; $i < count( $quizresults ); $i++ ) {
        $quesid = $quizresults[ $i ][ 'Question_Id' ];
        $inputid = $quizresults[ $i ][ 'input_id' ];
        $secinputid = $quizresults[ $i ][ 'Sec_input_id' ];

        $query = "select isnull(max(max_sl_no),0)+1 id from Quiz_MaxSlNo where sf_code='$SF_Code'";
        $tr = performQuery( $query );
        $id = $tr[ 0 ][ 'id' ];
        $code = $SF_Code . '-' . $id;

        $query = "select sf_name sfName from mas_salesforce where sf_code='$SF_Code'";
        $tr = performQuery( $query );
        $sfName = $tr[ 0 ][ 'sfName' ];

        if ( $id == "1" ) {
            $query = "insert into Quiz_MaxSlNo select '$SF_Code',$DivCode,$id";
            performQuery( $query );
        } else {
            $query = "update Quiz_MaxSlNo set max_sl_no=$id where sf_code='$SF_Code'";
            performQuery( $query );
        }

        $query = "delete from quiz_result where Sf_Code='$SF_Code' and Quiz_Id='$quesid' and Division_Code='$DivCode' and Survey_Id='$surveyId'";
        performQuery( $query );

        $query = "insert into quiz_result(Result_Id,Sf_Code,Sf_Name,Division_Code,Quiz_Id,Input_Id,Status,Survey_Id,Created_Date,Second_Input_Id,First_Start_time,First_End_time,Second_Start_time,Second_End_time) select '$code','$SF_Code','$sfName','$DivCode','$quesid','$inputid',0,'$surveyId',getdate(),'$secinputid','$firstStartTime','$firstEndTime','$secStartTime','$secEndTime'";
        performQuery( $query );
    }
    $query = "update Processing_UserList set Process_Status='F' where SurveyId='$surveyId' and sf_code='$SF_Code'";
    performQuery( $query );
    $result[ 'success' ] = true;
    outputJSON( $result );
}
?>