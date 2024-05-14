<?php

require_once('cann2.php');
require_once('envar2.php');

// Check if all required parameters are provided
if (
    isset($_POST["staffy"]) && !empty($_POST["staffy"]) &&
    isset($_POST["currentDate"]) && !empty($_POST["currentDate"]) &&
    isset($_POST["rid"]) && !empty($_POST["rid"]) &&
    isset($_POST["seatNumber"]) && !empty($_POST["seatNumber"]) &&
    isset($_POST["ticket_type"]) && !empty($_POST["ticket_type"]) &&
    isset($_POST["amount"]) && !empty($_POST["amount"]) 
) {
    // Get the data from the POST request
    $staffy = $_POST["staffy"];
    $currentDate = $_POST["currentDate"];
    $rid = $_POST["rid"];
    $seatNumber = $_POST["seatNumber"];
    $ticket_type = $_POST["ticket_type"];
    $incomingAmount = intval($_POST["amount"]);

    // Fetch the existing amount from the database
    $amountQuery = "SELECT amount FROM [Bus_Booking].[dbo].[Finance] WHERE staffid='$staffy'";
    $amountQueryResult = sqlsrv_query($conn, $amountQuery);

    if ($amountQueryResult === false) {
        $response = array("status" => "error", "message" => "Failed to execute amount query.");
    } else {
        $row = sqlsrv_fetch_array($amountQueryResult, SQLSRV_FETCH_ASSOC);

        if ($row !== null && isset($row['amount'])) {
            $existingAmount = intval($row['amount']);

            if ($incomingAmount <= $existingAmount) {
                // Proceed with the booking
                $tstamp = date("Y-m-d");
                $sql = "INSERT INTO [Bus_Booking].[dbo].[Transactions] (staffid, booking_date, rid, seat_no, ticket_type) VALUES (?, ?, ?, ?, ?)";
                $stmt = sqlsrv_prepare($conn, $sql, array(&$staffy, &$tstamp, &$rid, &$seatNumber, &$ticket_type));

                if ($stmt) {
                    if (sqlsrv_execute($stmt)) {
                        // Deduct existing amount from incoming amount
                        $remainingAmount = $existingAmount - $incomingAmount;

                        // Update the amount in the database
                        $updateQuery = "UPDATE [Bus_Booking].[dbo].[Finance] SET amount = ? WHERE staffid = ?";
                        $updateParams = array($remainingAmount, $staffy);
                        $updateResult = sqlsrv_query($conn, $updateQuery, $updateParams);

                        if ($updateResult === false) {
                            $response = array("status" => "error", "message" => "Failed to update finance records.");
                        } else {
                            $response = array("status" => "success", "message" => "Booking successful.");
                        }
                    } else {
                        $response = array("status" => "error", "message" => "Failed to execute booking query.");
                    }
                } else {
                    $response = array("status" => "error", "message" => "Failed to prepare booking statement.");
                }
            } else {
                $response = array("status" => "error", "message" => "Insufficient funds.");
            }
        } else {
            $response = array("status" => "error", "message" => "Failed to fetch existing amount.");
        }
    }
} else {
    $response = array("status" => "error", "message" => "Missing or empty parameters.");
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);


?>
