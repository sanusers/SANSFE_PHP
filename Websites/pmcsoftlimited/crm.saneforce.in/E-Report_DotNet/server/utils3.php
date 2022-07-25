<?php

function outputJSON($array) {
	global $conn;
  //  echo json_encode($array);
  echo str_replace(".000000","",json_encode($array));
  sqlsrv_close($conn);
}

/**
 * Returns an  array of results. result signature : array( array( DB_COLOUMN1=>VALUE1,DB_COLOUMN2=>VALU2   ) )
 * @global {sqlsrv_conn} $conn db conection object
 * @param {String} $query sql query string
 * @return Boolean|Array
 */
function performQuery($query) {
    global $conn,$NeedRollBack;
    $result = array();
    if ($res = sqlsrv_query($conn, $query)) {
		 if (sqlsrv_errors() != null || !$res){
		$NeedRollBack=true;
		//echo str_replace(".000000","",json_encode(sqlsrv_errors()));
	
		    }
        $qt = explode(" ", $query);
        if (strcmp(strtolower($qt[0]), "select") != 0 && strcmp($qt[0], "exec") != 0  ) {
            if (sqlsrv_errors() != null)
                return false;
            else
                return true;
        }
        else {

           $result = array();
            while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
                $arr = array();
                foreach ($row as $key => $value) {
                    if (is_string($value))
                        $arr[$key] = utf8_encode(trim(preg_replace("/[\r\n]+/", " ", $value)));
                    else if(is_int($value)){
						$arr[$key] = (string)$value;
					}
					else {
                        $arr[$key] = $value;
                    }
                }

                $result[] = $arr;
            }

            return $result;
        }
    }
}


function performQueryWP($query,$pram) {
    global $conn,$NeedRollBack;
    $result = array();
	    if ($res = sqlsrv_query($conn, $query,$pram)) {
	 if (sqlsrv_errors() != null || !$res){
		$NeedRollBack=true;
	//	echo str_replace(".000000","",json_encode(sqlsrv_errors()));
	    }		
        $qt = explode(" ", $query);
        if (sqlsrv_errors() != null)
		{
			outputJSON(sqlsrv_errors());
        	return false;
		}
        else
            return true;
        
    }
}
