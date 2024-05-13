
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
    if ($count === 0 ) {
        // Account already exists, redirect to edit page
        echo '<script type="text/javascript">
        alert("You have not created an account. please create an account.");
        window.location.href="createaccount.php";
        </script>';
    } 
}

$currentDate = date("Y-d-m");

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Extracting form data
        $amount = $_POST['amount'];
        $paymentid = '381';  
        $session = '2023/2024';
        
        // Posting Values to REST WebService
        // Initialize SOAP XML request
        $user = 'anty';
        $token = 'antymi';
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <geninvAsync_forstaff xmlns="http://paymentsys.portal.yabatech.edu.ng/">
                    <amount>'.$amount.'</amount>
                    <name>'.$name.'</name>
                    <phone>'.$sphone.'</phone>
                    <email>'.$semail.'</email>
                    <description>'.$description.'</description>
                    <staffid>'.$staffy.'</staffid>
                    <paymentid>'.$paymentid.'</paymentid>
                    <session>'.$session.'</session>
                    <user>'.$user.'</user>
                    <token>'.$token.'</token>
                </geninvAsync_forstaff>
            </soap:Body>
        </soap:Envelope>';
        
        // Initialize cURL session for posting SOAP request
        $url = 'https://portal.yabatech.edu.ng/paymentsys/webservice1.asmx?op=geninvAsync_forstaff';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:text/xml"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        // Execute cURL session and get the response
        $result = curl_exec($curl);
        
        // Process the SOAP response
        $cleanData = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3' , $result); 
        $convertToString = simplexml_load_string($cleanData);
        $encodingToJson = json_encode($convertToString); 
        $decodeJson = json_decode($encodingToJson); 
        $data = ($decodeJson->soapBody->geninvAsync_forstaffResponse->geninvAsync_forstaffResult);

        // Close cURL session
        curl_close($curl);
        
        // Construct redirection URL
        $url2 = "https://onlinepay.yabatech.edu.ng/?v1=$data";  

        // Insert into 'wallet_trans' table
        $query = "INSERT INTO [Bus_Booking].[dbo].[wallet_trans] (staffid, amount, remita_rrr, trans_date) VALUES (?, ?, ?, ?)";
        $params = array($staffy, $amount, $data, $currentDate);   
        $noway3 = sqlsrv_query($conn, $query, $params);
        
        if ($noway3 === false) {
            // Insertion failed
            echo '<script type="text/javascript">alert("Incomplete wallet funding, Please try again");</script>';
        } else {
            // Check if the record with the staffid exists in the 'Finance' table
            $query2 = "SELECT * FROM [Bus_Booking].[dbo].[Finance] WHERE staffid = ?";
            $params2 = array($staffy);
            $result = sqlsrv_query($conn, $query2, $params2);
            
            if ($result === false) {
                // Error occurred while checking existing finance records
                echo '<script type="text/javascript">alert("Error occurred while checking existing finance records");</script>';
            } else {
                if (sqlsrv_has_rows($result)) {
                    // Record exists, update the existing record with the new amount
                    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $existingAmount = $row['amount'];
                    $newAmount = $existingAmount + $amount;
        
                    $updateQuery = "UPDATE [Bus_Booking].[dbo].[Finance] SET amount = ? WHERE staffid = ?";
                    $updateParams = array($newAmount, $staffy);
                    $updateResult = sqlsrv_query($conn, $updateQuery, $updateParams);
        
                    if ($updateResult === false) {
                        // Error occurred while updating the record
                        echo '<script type="text/javascript">alert("Error occurred while updating finance records");</script>';
                    } else {
                        // Update successful
                        echo '<script type="text/javascript">alert("PAY NOW, CLICK OK"); window.location.href="'.$url2.'";</script>';
                    }
                } else {
                    // Record does not exist, proceed with the insertion
                    $insertQuery = "INSERT INTO [Bus_Booking].[dbo].[Finance] (staffid, amount) VALUES (?, ?)";
                    $insertParams = array($staffy, $amount);
                    $insertResult = sqlsrv_query($conn, $insertQuery, $insertParams);
        
                    if ($insertResult === false) {
                        // Error occurred while inserting the record
                        echo '<script type="text/javascript">alert("Error occurred while inserting new finance record");</script>';
                    } else {
                        // Insertion successful
                        echo '<script type="text/javascript">alert("PAY NOW, CLICK OK"); window.location.href="'.$url2.'";</script>';
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // Handle exceptions
    echo '<script type="text/javascript">alert("An error occurred: '.$e->getMessage().'");</script>';
}



?>


<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Wallet</title>
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
            height: 60vh; /* Reduce height to bring it up */
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
            margin-bottom: 50px; /* Add space between form and table */
            
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
            echo "<p><i>please fund your wallet here </p></i>";?>
      
            <form action="" method="post">
                <div class="form-group">
                    <label for="amount">Enter Amount (₦):</label>
                    <input type="number" id="amount" name="amount" placeholder="Enter amount" required>
                </div>
                <button type="submit" class="btn-submit">Proceed to Pay</button>
            </form>
        </div>

        <!-- Table placed underneath the form -->     
     <script>
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('amount').addEventListener('input', function() {
    if (this.value <= 0) {
      this.setCustomValidity('Please enter a positive number greater than 0.');
    } else {
      this.setCustomValidity('');
    }
  });
});
</script>
</body>
</html>

</html>