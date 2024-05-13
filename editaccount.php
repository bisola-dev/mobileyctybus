<?php 
// Include necessary files
require_once('cann2.php');
require_once('envary.php');

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

$emailzx1 = "";
$phonezx1 = "";

    try {
    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve user input
        $phonezx = $_POST["phonezx"];
        $emailzx = $_POST["emailzx"];

        // Prepare the SQL query
        $sql = "UPDATE [Bus_Booking].[dbo].[Account] SET ";

        // Add email and phone to the query if they are not empty
        if (!empty($phonezx)) {
            $sql .= "phone='$phonezx', ";
        }
        if (!empty($emailzx)) {
            $sql .= "email='$emailzx', ";
        }

        // Trim the trailing comma and space
        $sql = rtrim($sql, ", ");

        // Add WHERE clause
        $sql .= " WHERE staffid='$staffy'";

        // Execute the SQL query
        $noway3 = sqlsrv_query($conn, $sql);

        // Check if the query execution was successful
        if ($noway3 === false) {
            // Update failed
            echo '<script type="text/javascript">alert("Account Update unsuccessful, Please try again");</script>';
        } else {
            // Check the number of rows affected
            $rowsAffected = sqlsrv_rows_affected($noway3);
            if ($rowsAffected > 0) {
                // Update successful
                echo '<script type="text/javascript">alert("Account successfully Updated");</script>';
            } else {
                // No rows affected, update failed
                echo '<script type="text/javascript">alert("No changes made to the account");</script>';
            }
        }
    }

    // Fetch existing email and phone for display
    $chin = "SELECT * FROM [Bus_Booking].[dbo].[Account] WHERE staffid='$staffy'";
    $kin = sqlsrv_query($conn, $chin);
    $hjz = sqlsrv_fetch_array($kin, SQLSRV_FETCH_ASSOC);

    if ($hjz <= 0) {
        echo "No records";
    } else {
        $emailzx1 = empty($hjz['email']) ? "" : $hjz['email'];
        $phonezx1 = empty($hjz['phone']) ? "" : $hjz['phone'];
    } 
} catch (Exception $e) {
    // Handle the exception here
    echo "An error occurred: " . $e->getMessage();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
  <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        /* Remove styles related to the sidebar */
        /* .sidebar {
            display: none;
        } */

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: auto; /* Adjust height as needed */
            width: 90%; /* Adjust width as needed */
            margin: 50px auto;
            overflow: hidden;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            margin: 0 auto; /* Center the form */
        }

        .form-container h2 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #008000;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
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
        <h2>Update Account</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="namezx" name="namezx" placeholder="Enter your name" value="<?php echo $name;?>" required disabled>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phonezx" name="phonezx" placeholder="Enter your phone number" value="<?php echo $phonezx1;?>" oninput="validatePhone(this)" pattern="[0-9]{11}">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="emailzx" name="emailzx" placeholder="Enter your email address" value="<?php echo $emailzx1;?>" required>
            </div>
            <button type="submit" class="btn-submit">Update Account</button>
        </form>
    </div>
    <script>
        function validatePhone(input) {
            var phoneNumber = input.value.replace(/\D/g, ''); // Remove non-digit characters

            // Check if the phone number has exactly 11 digits
            if (phoneNumber.length !== 11) {
                input.setCustomValidity("Phone number must have exactly 11 numeric digits");
            } else {
                input.setCustomValidity(""); // Clear any previous validation message
            }
        }
    </script>
</body>
</html>
        
   