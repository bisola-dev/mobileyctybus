<?php
require_once('cann2.php');
require_once('envar2.php');
require_once 'phpqrcode/qrlib.php';


$staffid = base64_decode($_GET['staffid']);
$seat_no = base64_decode($_GET['seat_no']);
$booking_date = base64_decode($_GET['booking_date']);
$description = base64_decode($_GET['description']);
$amount = base64_decode($_GET['amount']);
$ticket_type = base64_decode($_GET['ticket_type']);

$bookingDetails = "BookingDate: $booking_date, Name: $name, Route: $description, Amount: $amount , Seat Number:$seat_no,Ticket type:$ticket_type";
// Function to generate QR code
function generateQRCode($bookingDetails, $fileName) {
    // File path where you want to save the QR code image
    $filePath = 'image/' . $fileName;

    // Generate the QR code image
    QRcode::png($bookingDetails, $filePath, QR_ECLEVEL_L, 4);

    // Return the file path
    return $filePath;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: auto;
            width: 90%;
            max-width: 600px;
            margin: 10px auto;
            background: linear-gradient(135deg, #FFFF00, #008000);
            border: 2px solid #ccc;
            border-radius: 10px;
            padding: 15px;
        }

        .booking-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin-bottom: 20px;
        }

        .detail {
            margin-bottom: 20px;
            text-align: left;
        }

        .detail label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .qr-code {
            margin-top: 20px;
        }

        .print-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #008000;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-button:hover {
            background-color: #006400;
        }

        /* Hide sidebar and footer when printing */
        @media print {
            .sidebar,
            footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <?php include "sidebar.php"; ?>
        </div>
        <div class="container">
            <h1>Booking Details</h1>
            <div class="booking-details">
                <div class="detail">
                    <label>Booking dateDate(Y-M-D):</label>
                    <span><?php echo $booking_date;?></span>
                </div>
                <div class="detail">
                    <label>Staff Name:</label>
                    <span><?php echo $name; ?></span>
                </div>
                <div class="detail">
                    <label>Route:</label>
                    <span><?php echo $description; ?></span>
                </div>
                <div class="detail">
                    <label>Amount:</label>
                    <span><?php echo $amount; ?></span>
                </div>
                <!-- Uncomment the following block if you have the seat number -->
                <div class="detail">
                    <label>Seat Number:</label>
                    <span><?php echo $ticket_type .' '.$seat_no;?></span>
                </div> 
                <div class="qr-code" style="position: relative;">
                    <?php 
                    $qrCodeFilePath = generateQRCode($bookingDetails, 'booking_qrcode.png');
                    ?>
                    <!-- Display the QR code image -->
                    <img src="<?php echo $qrCodeFilePath; ?>" alt="QR Code" style="display: block; margin: 0 auto; width: 150px;">
                    <!-- Insert the logo here -->
                    <img src="glaze/yabayctlogo.png" alt="Yabayct Logo" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 30px; height: auto;">
                </div>
            </div>
            <button class="print-button" onclick="printDetails()">Print</button>
           

        </div>
    </div>

    <script>
        function printDetails() {
            window.print();
        }
    </script>


</body>
</html>
