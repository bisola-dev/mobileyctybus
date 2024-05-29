<?php
require_once('cann2.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input
    $staffy = $_POST["staffy"];
    $surn = $_POST["surn"];
    
    // Check if staff ID and password are provided
    if (!empty($staffy) && !empty($surn)) {
        // Prepare SQL query
        $bintu = "SELECT * FROM [Bus_Booking].[dbo].[stafflist] WHERE STAFFNUMBER = ? AND SURNAME=?";
        $params = array($staffy, $surn);
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

                    // Store user data in session
                    $_SESSION['SURNAME'] = $surn;
                    $_SESSION['FIRSTNAME'] = $firs;
                    $_SESSION['MIDDLENAME'] = $midd;
                    $_SESSION['staffy'] = $staffy;

                    $clot = $surn . ' ' . $firs . ' ' . $midd;
                }
                // Redirect user on successful login
                echo '<script type="text/javascript">
                        alert("Login successful!");
                        window.location.href="busdashboard.php";
                      </script>';
            } else {
                // Notify user about incorrect staff ID or password
                echo '<script type="text/javascript">
                        alert("Incorrect staff ID or password!");
                      </script>';
            }
        } else {
            // Handle query execution error
            echo '<script type="text/javascript">
                    alert("Error executing query!");
                  </script>';
        }
    } else {
        // Handle empty staff ID or password
        echo '<script type="text/javascript">
                alert("Please provide both staff ID and password!");
              </script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yaba Tech Staff Bus portal - Login</title>
    <style>
        

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
        background-color: #fff;
         padding: 20px;
         border-radius: 8px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
         max-width: 400px; /* Adjusted width for better mobile responsiveness */
         width: 90%; /* Adjusted width for better mobile responsiveness */
        text-align: center;
         margin: auto; /* Center the container horizontally */

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

        .password-input-container {
    position: relative;
}
#showPasswordBtn {
    position: absolute;
    right: 10px; /* Adjust as needed */
    top: 50%; /* Adjust as needed */
    transform: translateY(-50%);
    background-color: #333; /* Darker color */
    border: none; /* Remove border */
    border-radius: 50%; /* Circle shape */
    width: 20px; /* Diameter of the circle */
    height: 20px; /* Diameter of the circle */
    cursor: pointer;
    outline: none;
}

#showPasswordBtn:hover {
    background-color: #e0e0e0; /* Change color on hover if needed */
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
        <h2>YabaTech Staff Bus Portal</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username_or_email">Please log in with your staff number:</label>
                <input type="text" id="staffy" name="staffy" required>
            </div>

            <div class="form-group">
         <label for="Password">Password:</label>
         <div class="password-input-container">
        <input type="password" id="surn" name="surn" placeholder="Your surname is your password" required>
        <button type="button" id="showPasswordBtn" onclick="togglePasswordVisibility()"></button>
    </div>
</div>

        <button type="submit" class="btn-login">Login</button>
        </form>
        <footer>
            For complaints, please send a mail to busticketing@yabatech.edu.ng
            <p>Yaba College of Technology, CITM Software &copy; <?php echo date("Y"); ?></p>
        </footer>
    </div>


    <script>
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("surn");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }
    }
</script>

</body>
</html>
