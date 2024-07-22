<?php
require_once('cann2.php');
require_once('envar2.php');

// Fetch existing email and phone for display
$chin = "SELECT COUNT(*) AS count FROM [Bus_Booking].[dbo].[Account] WHERE staffid='$staffy'";
$kin = sqlsrv_query($conn, $chin);

// Check if the SQL query was successful
if ($kin === false) {
    // Handle SQL error
    echo "An error occurred while fetching account information.";
} else {
    // Fetch the count of rows
    $row = sqlsrv_fetch_array($kin, SQLSRV_FETCH_ASSOC);
    $count = $row['count'];

    // Check if the account exists
    if ($count === 0 ) {
        // Account already exists, redirect to edit page
        echo '<script type="text/javascript">
        alert("You have not created an account. please create an account.");
        window.location.href="createaccount.php";
        </script>';
    } 
}

// Get the current server time in Africa/Lagos timezone
date_default_timezone_set('Africa/Lagos');
$currentHour = date('H');
$currentMinute = date('i');
$currentDayOfWeek = date('N'); // 1 (Monday) through 7 (Sunday)

// Check if current day is Monday to Friday and time is between 3:00 PM and 3:50 PM
$allowAccess = ($currentDayOfWeek >= 1 && $currentDayOfWeek <= 5) // Monday to Friday
            && ($currentHour == 15 && $currentMinute >= 0 && $currentMinute <= 50); // 3:00 PM to 3:50 PM

if (!$allowAccess) {
    // If not within the allowed time range, display an error message and redirect to the dashboard
    echo '<script type="text/javascript">
          alert("Booking is only available between 3:00 and 3:50pm on weekdays (Africa/Lagos time).");
          window.location.href="busdashboard.php";
          </script>';
    exit; // Stop further execution
}

// Fetch descriptions for dropdown
$descriptionQuery = "SELECT DISTINCT description,rid FROM [Bus_Booking].[dbo].[Routes]";
$descriptionResult = sqlsrv_query($conn, $descriptionQuery);
if ($descriptionResult === false) {
    die(print_r(sqlsrv_errors(), true));
}
$descriptions = array();
$rids = array();
while ($row = sqlsrv_fetch_array($descriptionResult, SQLSRV_FETCH_ASSOC)) {
    $descriptions[] = $row['description'];
    $rids[] = $row['rid'];
}


function getMaxSeatNumber($conn, $rid, $currentDate) {
    // Get the number of seats already booked for the current date
    $bookedSeatsQuery = "SELECT COUNT(*) AS booked_seats FROM [Bus_Booking].[dbo].[Transactions] WHERE rid='$rid' AND booking_date='$currentDate'";
    $bookedSeatsResult = sqlsrv_query($conn, $bookedSeatsQuery);
    if ($bookedSeatsResult === false) {
        throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
    }
    $bookedSeatsRow = sqlsrv_fetch_array($bookedSeatsResult, SQLSRV_FETCH_ASSOC);
    $bookedSeats = $bookedSeatsRow['booked_seats'];

    // Get the total capacity (seating capacity + standing capacity)
    $totalCapacityQuery = "SELECT seat_capacity, stand_capacity FROM [Bus_Booking].[dbo].[Routes] WHERE rid='$rid'";
    $totalCapacityResult = sqlsrv_query($conn, $totalCapacityQuery);
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


echo '<script src="booking_function.js"></script>';

function insertBooking($conn, $staffy, $currentDate, $rid, $nextSeatNumber, $ticket_type, $maxRetry = 3, $retryCount = 0) {
    $tstamp = date("Y-m-d");
    $sql = "INSERT INTO [Bus_Booking].[dbo].[Transactions] (staffid, booking_date, rid, seat_no, ticket_type) VALUES (?, ?, ?, ?, ?)";
    $params = array(&$staffy, &$tstamp, &$rid, &$nextSeatNumber, &$ticket_type);
    $stmt = sqlsrv_prepare($conn, $sql, $params);
    
    if ($stmt) {
        if (sqlsrv_execute($stmt)) {
            // Log success message to console
            echo '<script type="text/javascript">alert("You have successfully booked a ticket."); window.location.href = "viewbooking.php";</script>';
            return true; // Return true to indicate booking success
        } else {
            // Check if the error is due to a constraint violation (e.g., seat already booked)
            $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    if ($error['SQLSTATE'] == '23000') { // SQLSTATE '23000' represents integrity constraint violation
                        // Attempt to retry with a new seat number
                        if ($retryCount < $maxRetry) {
                            $newSeatNumber = getMaxSeatNumber($conn, $rid, $currentDate); // Implement getMaxSeatNumber function to get an available seat
                            return insertBooking($conn, $staffy, $currentDate, $rid, $newSeatNumber, $ticket_type, $maxRetry, $retryCount + 1);
                        } else {
                            // Max retry limit reached
                            echo '<script type="text/javascript">alert("Maximum retry limit reached. Please try again later.");</script>';
                            return false; // Return false to indicate booking failure
                        }
                    }
                }
            }
            // Log generic error message to console
            echo '<script type="text/javascript">alert("Ticket booking unsuccessful, please try again.");</script>';
            return false; // Return false to indicate booking failure
        }
    } else {
        // Error preparing the SQL query
        throw new Exception("Failed to prepare SQL statement");
    }
}


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $amount = $_POST['amount'];
        $rid = $_POST['selectedRid'];
    
        try {
            // Check if the user has already booked a ticket for the current date
            $currentDate = date("Y-m-d");
            $checkBookingQuery = "SELECT COUNT(*) AS booking_count FROM [Bus_Booking].[dbo].[Transactions] WHERE staffid='$staffy' AND booking_date='$currentDate'";
            $checkBookingResult = sqlsrv_query($conn, $checkBookingQuery);
            
            if ($checkBookingResult === false) {
                throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
            }
            
            $bookingRow = sqlsrv_fetch_array($checkBookingResult, SQLSRV_FETCH_ASSOC);
            $bookingCount = $bookingRow['booking_count'];
        
            if ($bookingCount > 0) {
                // User has already booked a ticket for the current date, display error message
                echo '<script type="text/javascript">alert("Sorry, you can only book a bus ticket once a day.");</script>';
            } else {
                // Retrieve existing amount
                $amountQuery = "SELECT amount FROM [Bus_Booking].[dbo].[Finance] WHERE staffid='$staffy'";
                $amountQueryResult = sqlsrv_query($conn, $amountQuery);
                
                if ($amountQueryResult === false) {
                    throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
                }
                
                $row = sqlsrv_fetch_array($amountQueryResult, SQLSRV_FETCH_ASSOC);
                
                if ($row !== null && isset($row['amount'])) {
                    $existingAmount = $row['amount'];
        
                    // Deduct existing amount from incoming amount
                    $remainingAmount = intval($existingAmount) - intval($amount);
    
        
                    if ($remainingAmount >= 0) {
                        // Proceed with the ticket booking
                        $seatCountQuery = "SELECT seat_capacity, stand_capacity FROM [Bus_Booking].[dbo].[Routes] WHERE rid='$rid'";
                        $seatCountResult = sqlsrv_query($conn, $seatCountQuery);
                        
                        if ($seatCountResult === false) {
                            throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
                        }
                        
                        $row = sqlsrv_fetch_array($seatCountResult, SQLSRV_FETCH_ASSOC);
                        $seat_capacity = $row['seat_capacity'];
                        $stand_capacity = $row['stand_capacity'];
        
                        // Calculate total capacity
                        $total_capacity = $seat_capacity + $stand_capacity;
        
                        // Ensure the total number of bookings does not exceed the total capacity
                        $nextSeatNumber = getMaxSeatNumber($conn, $rid, $currentDate);
        
                        if (empty($nextSeatNumber)) {
                            $nextSeatNumber = 0; // Set it to 0 to start numbering from 1
                        }
                         
                        if ($nextSeatNumber <= $seat_capacity) {
                            // Assign seating ticket
                            $ticket_type='sitting';
                            insertBooking($conn, $staffy, $currentDate, $rid, $nextSeatNumber,$ticket_type);
                            // Update existing amount in the finance table
                            $updateQuery = "UPDATE [Bus_Booking].[dbo].[Finance] SET amount = ? WHERE staffid = ?";
                            $updateParams = array($remainingAmount, $staffy);
                            $updateResult = sqlsrv_query($conn, $updateQuery, $updateParams);
        
                            if ($updateResult === false) {
                                // Error occurred while updating the record
                                echo '<script type="text/javascript">alert("Error occurred while updating finance records.");</script>';
                            } else {
                                // Update successful
                                echo '<script type="text/javascript">console.log("Update successful.");</script>';
                            } 
                        } elseif ($nextSeatNumber > $seat_capacity &&  $nextSeatNumber <= $total_capacity) {
                            // Seating capacity is exhausted, prompt for standing ticket
                            if ($stand_capacity > 0) {
                                $nextStandingNumber = $nextSeatNumber;
                                $ticket_type='standing';
                                // Call JavaScript function
                                echo '<script type="text/javascript">handleStandingBooking(' . ($nextStandingNumber) . ', "' . $conn . '", "' . $staffy . '", "' . $currentDate . '", "' . $rid . '", "' . $amount . '","' . $ticket_type.'",);</script>';

                            } else {
                                echo '<script type="text/javascript">alert("Sorry, no standing seats available for this route.");</script>';
                            }
                        } else {
                            // All seats are booked for this route
                            echo '<script type="text/javascript">alert("Sorry, all seats are fully booked.");</script>';
                        }
                    } else {
                        // User does not have enough balance
                        echo '<script type="text/javascript">alert("Sorry, you dont have enough funds for this destination. Please load your wallet.");</script>';
                    }
                } else {
                    // No data found for the user or 'amount' key not set
                    echo '<script type="text/javascript">alert("Please fund your wallet.");</script>';
                }
            }
        } catch (Exception $e) {
            // Handle the exception here
            echo "An error occurred: " . $e->getMessage();
        }
    }
    ?>
    
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            overflow-x: hidden; /* Prevent horizontal scrollbar */
        }

        .container {
            display: flex;
            justify-content: center; /* Center items horizontally */
            align-items: center;
            flex-direction: column; /* Stack items vertically */
            height: auto; /* Adjusted height */
            width: 100%; /* Full width */
            margin-top: 20px; /* Add some top margin */
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 45%; /* Adjusted width */
            text-align: center;
            margin-bottom: 10px; /* Add space between form and table */
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group select, .form-group input {
            width: calc(100% - 22px); /* Adjusted for padding */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #008000;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #FFFF00;
            color: #000;
        }
    </style>
</head>
<body>
<div class="container">
        <div class="sidebar">
            <?php include "sidebar.php";?>
        </div>
    <div class="form-container">
    <?php
          echo "<p><b>WELCOME,  $name </p></b>";
            echo "<p><i>please book a bus here </p></i>";?>

<form action="" method="post">
            <div class="form-group">
                <label for="description">Description:</label>
                <select id="description" name="selectedRid" required>
                <option value="">Select Description</option>
            <?php foreach ($descriptions as $index => $desc): ?>
                <option value="<?php echo $rids[$index]; ?>"><?php echo $desc; ?></option>
            <?php endforeach; ?>
        </select>
            </div>
            <div class="form-group">
                <label for="amount">Amount (₦):</label>
                <input type="text" id="amount" name="amount" placeholder="Amount" readonly>
            </div>
            <button type="submit" class="btn-submit">Book Bus</button>
        </form>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#description').change(function () {
            var selectedRid = $(this).val();
            if(selectedRid !== "") {
                $.ajax({
                    url: 'get_amount.php',
                    type: 'post',
                    data: {selectedRid: selectedRid},
                    success: function (response) {
                        $('#amount').val(response);
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                // Handle the case when no description is selected
                $('#amount').val(""); // Clear the amount field or do something else
            }
        });
    });
</script>

</body>
</html>