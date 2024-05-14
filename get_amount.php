<?php
require_once('cann2.php');
require_once('envar2.php');

// Get the selected description from the AJAX request
$selectedRid = $_POST['selectedRid'];

// Query to fetch the amount based on the selected description
$query = "SELECT amount FROM [Bus_Booking].[dbo].[Routes] WHERE rid = ?";
$params = array($selectedRid);
$result = sqlsrv_query($conn, $query, $params);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the amount from the result
$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
$amount = $row['amount'];

// Return the amount as response
echo $amount;

// Close the database connection
sqlsrv_close($conn);
?>
