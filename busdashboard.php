<?php 
session_start();
require_once('envary.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        
        #sidebar {
            width: 250px; /* Adjusted sidebar width for desktop */
            height: 100%;
            background-color: #008000; /* Green */
            position: fixed;
            left: 0;
            top: 0;
            overflow-x: hidden;
            padding-top: 20px;
            transition: width 0.5s; /* Add transition for smooth animation */
        }

        #sidebar.collapsed {
            width: 60px; /* Adjusted sidebar width when collapsed for mobile */
        }
        
        #sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        #sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        #sidebar ul li {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        #sidebar ul li a {
            color: #fff;
            text-decoration: none;
        }
        
        #content {
            margin-left: 250px; /* Adjusted margin for desktop */
            padding: 20px;
        }

        @media screen and (max-width: 768px) {
            #sidebar {
                width: 100px; /* Adjusted sidebar width for mobile */
            }
            #content {
                margin-left: 80px; /* Adjusted content margin for mobile */
            }
        }
        
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="logo">
            <img src="glaze/yabayctlogo.png" alt="Yabayct Logo" style="width: 70px; height: auto; margin-top: 22px;">
        </div>
        <ul>
            <li><a href="busdashboard.php">Home</a></li>
            <li><a href="createaccount.php">Create Account</a></li>
            <li><a href="editaccount.php">Edit Account</a></li>
            <li><a href="funding.php">Fund Wallet</a></li>
            <li><a href="viewtransc.php">View Wallet Transaction</a></li>
            <li><a href="booking.php">Book Bus</a></li>
            <li><a href="viewbooking.php">View Booking</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div id="content">
        <h3 style="text-align: center;">Welcome, <?php echo $name;?>, Do enjoy a seamless bus ticket request.</h3>
        <p style="text-align: center;">This is the YCT Bus Booking Dashboard.</p>
    </div>

    <script>
        // Toggle sidebar collapse on small screens
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var content = document.getElementById('content');
            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                content.style.marginLeft = '60px'; // Adjusted content margin when sidebar is collapsed
            } else {
                content.style.marginLeft = '250px'; // Adjusted content margin for desktop view
            }
        }
    </script>
</body>
</html>
