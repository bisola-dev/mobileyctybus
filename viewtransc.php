

<?php

require_once('cann2.php');
require_once('envar2.php');

// Check if the account exists
$chin = "SELECT COUNT(*) AS count FROM [Bus_Booking].[dbo].[Account] WHERE staffid='$staffy'";
$kin = sqlsrv_query($conn, $chin);

if ($kin === false) {
    // Handle SQL error
    echo "An error occurred while fetching account information.";
} else {
    $row = sqlsrv_fetch_array($kin, SQLSRV_FETCH_ASSOC);
    $count = $row['count'];

    if ($count === 0) {
        // Account does not exist, redirect to create account page
        echo '<script type="text/javascript">
        alert("You have not created an account. Please create an account.");
        window.location.href="createaccount.php";
        </script>';
        exit; // Stop further execution
    }
}

// Fetch wallet entries from the database and populate the table rows
$walletQuery = "SELECT * FROM [Bus_Booking].[dbo].[wallet_trans] WHERE staffid = ? AND status = 1";
$params = array($staffy);
$walletResult = sqlsrv_query($conn, $walletQuery, $params);

if ($walletResult === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the available balance from the Finance table
$balanceQuery = "SELECT amount FROM [Bus_Booking].[dbo].[Finance] WHERE staffid = ?";
$params2 = array($staffy);
$result = sqlsrv_query($conn, $balanceQuery, $params2);

if ($result === false) {
    // Error occurred while checking existing finance records
    echo '<script type="text/javascript">alert("Error occurred while checking existing finance records");</script>';
} else {
    $Balance = 0; // Default value for balance

    if (sqlsrv_has_rows($result)) {
        // Record exists, update the existing record with the new amount
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        $Balance = $row['amount'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>view transaction</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
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
            height: auto; /* Reduce height to bring it up */
          width: 100%; /* Full width */
          margin-top: 10px; /* Add some top margin */
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 45%; /* Adjusted width */
            text-align: center;
             margin: 0 auto; /* Center the form */
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


        /* New table styles */
.wallet-table {
    width: 30%; /* Adjusted width */
    border-collapse: collapse;
    border: 1px solid #ccc;
    margin-top: 20px; /* Add space between previous table and new table */
}

.wallet-table th, .wallet-table td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: center;
    font-weight: bold;
}

.wallet-table th {
    background-color: #008000;
    color: #fff;
}

#balanceTable {
    width: 90%; /* Adjusted width */
    border-collapse: collapse;
    border: 1px solid #ccc;
    margin-top: 50px; /* Add more space between tables */
    margin-bottom: 20px; /* Add space between form and table */
    margin-left: 20px; /* Add margin to justify table to the left */
}

#balanceTable th,
#balanceTable td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: center;
    font-weight: bold;
}

#balanceTable th {
    background-color: #008000;
    color: #fff;
}

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            table {
                font-size: 14px; /* Decrease font size for smaller screens */
            }

            .container {
                padding: 5px; /* Reduce padding for smaller screens */
            }
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
    echo "<p><i>View wallet transactions </p></i>";

    // Display wallet transactions table
    if (sqlsrv_has_rows($walletResult)) {
        echo "<table id='walletTable'>"; // Set table ID to walletTable
        echo "<thead>";
        echo "<tr>";
        echo "<th>Staff ID</th>";
        echo "<th>Amount in wallet</th>";
        echo "<th>Remitta_rrr</th>";
        echo "<th>Date(Y-M-D)</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = sqlsrv_fetch_array($walletResult, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['staffid'] . "</td>";
            echo "<td>₦" . $row['amount'] . "</td>";
            echo "<td>" . $row['remita_rrr'] . "</td>";
            echo "<td>" . $row['trans_date']->format('Y-m-d') . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo '<div class="no-transactions">No transactions to display.</div>';
    }

    // Free the result set
    sqlsrv_free_stmt($walletResult);
    ?>

    <!-- Display available balance -->
    <table id="balanceTable"> <!-- Set table ID to balanceTable -->
        <thead>
            <tr>
                <th>Available Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>₦<?php echo $Balance; ?></td>
            </tr>
        </tbody>
    </table>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#walletTable').DataTable();
        $('#balanceTable').DataTable({
            "paging": false, // Disable pagination for balance table
            "searching": false, // Disable searching for balance table
            "info": false // Disable table info for balance table
        });
    });
</script>
</body>
</html>