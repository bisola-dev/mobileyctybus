<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <style>
        /* Common styles for sidebar and content */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        /* Sidebar styles */
        #sidebar {
            width: 180px;
            height: 100%;
            background-color: #008000; /* Green */
            position: fixed;
            left: 0;
            top: 0;
            overflow-x: hidden;
            padding-top: 20px;
            transition: width 0.5s; /* Add transition for smooth animation */
            z-index: 1000; /* Ensure sidebar is above other content */
        }


 @media screen and (max-width: 768px) {
    #sidebar {
        width: 100%; /* Make sidebar full width on small screens */
        min-width: 0; /* Remove minimum width on small screens */
    }
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

        /* Content styles */
        #content {
    padding: 20px;
    transition: margin-left 0.5s;
    margin-left: 180px; /* Adjust according to sidebar width */
}

@media screen and (max-width: 768px) {
    #content {
        margin-left: 0; /* Ensure no margin when sidebar is hidden */
    }
}
        /*#content {
            padding: 20px;
            transition: margin-left 0.5s; /* Add transition for smooth animation 
        }/*

        /* Toggle button styles */
        .toggle-sidebar {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 2; /* Ensure the button is above the sidebar */
        }
    </style>
</head>
<body>
    <div id="sidebar" style="display: none;"> <!-- Initially hidden -->
        <div class="logo">
        <img src="glaze/yabayctlogo.png" alt="Yabayct Logo" style="width: 80px; height: auto; margin-top: 22px;">
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
    <button class="toggle-sidebar" onclick="toggleSidebar()" style="background-color: yellow;">Show Sidebar</button>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            var content = document.getElementById("content");
            var toggleButton = document.querySelector(".toggle-sidebar");
    
           
     if (sidebar.style.display === "none" || sidebar.style.display === "") {
        sidebar.style.display = "block";
        content.style.marginLeft = "180px"; /* Adjusted to sidebar width */
        toggleButton.textContent = "Hide Sidebar";
        } else {
        sidebar.style.display = "none";
        content.style.marginLeft = "0";
        toggleButton.textContent = "Show Sidebar";
    }
        }
    </script>
</body>
</html>
