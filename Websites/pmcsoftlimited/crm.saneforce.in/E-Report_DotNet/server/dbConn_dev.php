<?php

$serverName = "134.119.179.50, 10433";
/*$serverName = "0.0.0.0, 10433";*/
$connectionInfo = array(
    "Database" => "CRMSANeFORCE_ProductionServer",
    "LoginTimeout" => 30,
    "UID" => "sa",
    "PWD" => "kJn%4b!aSZxs"
);

/* Connect using Windows Authentication. */
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    echo "Unable to connect.</br>";
    die(print_r(sqlsrv_errors(), true));
}
?>


