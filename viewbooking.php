

<?php
require_once('cann.php');
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
    if ($count === 0) {
        // Account already exists, redirect to edit page
        echo '<script type="text/javascript">
        alert("You have not created an account. please create an account.");
        window.location.href="createaccount.php";
        </script>';
    } 
}





?>
<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>view transaction</title>
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
            height:auto; /* Reduce height to bring it up */
    width: 100%; /* Full width */
    margin-top: 1px; /* Add some top margin */
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 45%; /* Adjusted width */
            text-align: center;
            margin-bottom: 20px; /* Add space between form and table */
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

        .form-group input {
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

        /* Table styles */
        table {
            width: 47%; /* Adjusted width */
            border-collapse: collapse;
            border: 1px solid #ccc;
            margin-bottom: 20px; /* Add space between form and table */
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #008000;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <?php include "sidebar.php"; ?>
    </div>

    <?php
    echo "<p><b>WELCOME, $name </p></b>";
    echo "<p><i>View bookings </p></i>";

// Check if the 'staffid' parameter is provided
if (isset($staffy)) {
    // Query to fetch bookings based on the staffid
    $query = "SELECT * FROM [Bus_Booking].[dbo].[Transactions] WHERE staffid = ? ORDER BY booking_date DESC";

    // Prepare and execute the query with the staffid parameter
    $params = array($staffy);
    $result = sqlsrv_query($conn, $query, $params);

    // Check if the query execution was successful
    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Check if any rows were returned
    if (sqlsrv_has_rows($result)) {
        // Output table headers
        echo "<table>";
        echo "<thead>";
        echo "<tr><th>Staff ID</th><th>Seat Number</th><th>Booking DateDate(Y-M-D)</th><th>Route Description</th><th>Amount</th><th>Viewbooking</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        // Output each row of bookings
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['staffid'] . "</td>";
            echo "<td>" . $row['ticket_type'] . " " . $row['seat_no'] . "</td>";   
            echo "<td>" . $row['booking_date']->format('Y-m-d') . "</td>"; // Assuming booking_date is a DateTime object
            "<td>" . $row['ticket_type'] . "</td>";
            // Fetch additional information based on 'rid'
            $rid = $row['rid'];
            $query2 = "SELECT description, amount FROM [Bus_Booking].[dbo].[Routes] WHERE rid = ?";
            $params2 = array($rid);
            $result2 = sqlsrv_query($conn, $query2, $params2);
            
            if ($result2 !== false && sqlsrv_has_rows($result2)) {
                while ($row2 = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC)) {
                    $description = $row2['description'];
                    $amount = $row2['amount'];
            
                    echo "<td>$description</td>";
                    echo "<td>$amount</td>";

                    $encoded_staffid = base64_encode($row['staffid']);
                    $encoded_seat_no = base64_encode($row['seat_no']);
                    $encoded_ticket_type = base64_encode($row['ticket_type']);
                    $encoded_booking_date = base64_encode($row['booking_date']->format('Y-m-d'));
                    $encoded_description = base64_encode($description);
                    $encoded_amount = base64_encode($amount);

              $link = "view.php?staffid=$encoded_staffid&seat_no=$encoded_seat_no&ticket_type=$encoded_ticket_type&booking_date=$encoded_booking_date&description=$encoded_description&amount=$encoded_amount";
                    echo "<td><a href=\"$link\"><button>View Details</button></a></td>";
            
                }
            }

            else {
                echo "<td>No route description found for RID: $rid</td>";
             
            }

            echo "</tr>";
        }

        // Close table body and table
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No records found for staff ID: $staffy.";
    }
} else {
    echo "Error: No 'staffid' parameter provided.";
}
?>
<div>
</body>
</html>