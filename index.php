<?php
require_once('cann.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input
    $staffy = $_POST["staffy"];
    // Check if staff ID is provided
    if (!empty($staffy)) {
        // Prepare SQL query
        $bintu = "SELECT * FROM [Registry].[dbo].[stafflist] WHERE STAFFNUMBER = ?";
        $params = array($staffy);
        $options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

        // Execute SQL query
        $stmt = sqlsrv_query($conn, $bintu, $params, $options);
        
        // Check if query executed successfully
        if ($stmt !== false) {
            // Check if any rows were returned
            $row_count = sqlsrv_num_rows($stmt);
            if ($row_count > 0) {
                // Fetch the data
                while ($rowz = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $surn = $rowz['SURNAME'];
                    $firs = $rowz['FIRSTNAME'];
                    $midd = $rowz['MIDDLENAME'];


                    $surn = $_SESSION['SURNAME']=$surn;
                    $firs = $_SESSION['FIRSTNAME']= $firs;
                    $midd = $_SESSION['MIDDLENAME']= $midd; 
                    $staffy = $_SESSION['staffy']= $staffy; 
                    
                    
                    $clot = $surn . ' ' . $firs . ' ' . $midd;
                }
                // Redirect user on successful login
                echo '<script type="text/javascript">
                        alert("Login successful!");
                        window.location.href="busdashboard.php";
                      </script>';
            } else {
                // Notify user about unknown staff ID
                echo '<script type="text/javascript">
                        alert("Unknown staff ID!");
                      </script>';
            }
        } else {
            // Handle query execution error
            echo '<script type="text/javascript">
                    alert("Error executing query!");
                  </script>';
        }
    } else {
        // Handle empty staff ID
        echo '<script type="text/javascript">
                alert("Please provide a staff ID!");
              </script>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yaba Tech Staff Bus Tracker - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 90%; /* Adjusted width for better mobile responsiveness */
            width: 400px;
            text-align: center;
        }
        .logo {
            margin-bottom: 20px;
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
            width: calc(100% - 22px); /* Adjusted for padding and border */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #008000; 
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #FFFF00; /* Yellow */
            color: #000; /* Black */
        }

        .forgot-password,
        .create-account {
            text-align: center;
            margin-top: 20px; /* Add margin for better spacing */
        }
        .forgot-password a,
        .create-account a {
            color: #007bff;
            text-decoration: none;
        }
        footer {
    text-align: center;
    font-family: Arial, sans-serif;
    color: #888; /* Soft gray color */
    margin-top: 20px; /* Add margin for better spacing */
    font-weight: bold; /* Make the font bold */
    font-size: 0.8em; /* Adjust the font size */
}

    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="yabanewlogo.png" alt="Yaba College of Technology Logo">
        </div>
        <h2>Yaba Tech Staff Bus Tracker</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username_or_email">Please log in with your staff number:</label>
                <input type="text" id="staffy" name="staffy" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
        <footer>
            <p>Yaba College of Technology, CITM Software &copy; <?php echo date("Y"); ?></p>
        </footer>
    </div>
</body>
</html>
