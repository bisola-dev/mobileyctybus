<?php
require_once('cann2.php');
require_once('envar2.php');

// Function to get max seat number
function getMaxSeatNumber($conn, $rid, $currentDate) {
    // Get the number of seats already booked for the current date
    $bookedSeatsQuery = "SELECT COUNT(*) AS booked_seats FROM [Bus_Booking].[dbo].[Transactions] WHERE rid=? AND booking_date=?";
    $params = array($rid, $currentDate);
    $bookedSeatsResult = sqlsrv_query($conn, $bookedSeatsQuery, $params);
    if ($bookedSeatsResult === false) {
        throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
    }
    $bookedSeatsRow = sqlsrv_fetch_array($bookedSeatsResult, SQLSRV_FETCH_ASSOC);
    $bookedSeats = $bookedSeatsRow['booked_seats'];

    // Get the total capacity (seating capacity + standing capacity)
    $totalCapacityQuery = "SELECT seat_capacity, stand_capacity FROM [Bus_Booking].[dbo].[Routes] WHERE rid=?";
    $params = array($rid);
    $totalCapacityResult = sqlsrv_query($conn, $totalCapacityQuery, $params);
    if ($totalCapacityResult === false) {
        throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
    }
    $totalCapacityRow = sqlsrv_fetch_array($totalCapacityResult, SQLSRV_FETCH_ASSOC);
    $seatCapacity = $totalCapacityRow['seat_capacity'];
    $standCapacity = $totalCapacityRow['stand_capacity'];
    $totalCapacity = $seatCapacity + $standCapacity;

    // Calculate the next available seat number
    $nextSeatNumber = $bookedSeats + 1;

    return $nextSeatNumber;
}

// Function to insert booking with retry on seat constraint violation
function insertBooking($conn, $staffy, $currentDate, $rid, $seatNumber, $ticket_type, $maxRetry = 3, $retryCount = 0) {
    $tstamp = date("Y-m-d");
    $sql = "INSERT INTO [Bus_Booking].[dbo].[Transactions] (staffid, booking_date, rid, seat_no, ticket_type) VALUES (?, ?, ?, ?, ?)";
    $params = array($staffy, $tstamp, $rid, $seatNumber, $ticket_type);
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if ($stmt) {
        if (sqlsrv_execute($stmt)) {
            return true; // Return true to indicate booking success
        } else {
            // Check if the error is due to a constraint violation (e.g., seat already booked)
            $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    if ($error['SQLSTATE'] == '23000') { // SQLSTATE '23000' represents integrity constraint violation
                        // Attempt to retry with a new seat number
                        if ($retryCount < $maxRetry) {
                            $newSeatNumber = getMaxSeatNumber($conn, $rid, $currentDate); // Get next available seat number
                            return insertBooking($conn, $staffy, $currentDate, $rid, $newSeatNumber, $ticket_type, $maxRetry, $retryCount + 1);
                        } else {
                            // Max retry limit reached
                            return false; // Return false to indicate booking failure
                        }
                    }
                }
            }
            return false; // Return false for other errors during execution
        }
    } else {
        // Error preparing the SQL query
        throw new Exception("Failed to prepare SQL statement");
    }
}

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
    $amountQuery = "SELECT amount FROM [Bus_Booking].[dbo].[Finance] WHERE staffid=?";
    $params = array($staffy);
    $amountQueryResult = sqlsrv_query($conn, $amountQuery, $params);

    if ($amountQueryResult === false) {
        $response = array("status" => "error", "message" => "Failed to execute amount query.");
    } else {
        $row = sqlsrv_fetch_array($amountQueryResult, SQLSRV_FETCH_ASSOC);

        if ($row !== false && isset($row['amount'])) {
            $existingAmount = intval($row['amount']);

            if ($incomingAmount <= $existingAmount) {
                // Attempt booking with retry mechanism
                $bookingSuccessful = insertBooking($conn, $staffy, $currentDate, $rid, $seatNumber, $ticket_type);

                if ($bookingSuccessful) {
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
                    $response = array("status" => "error", "message" => "Maximum retry limit reached. Please try again later.");
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
