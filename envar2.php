<?php
$surn = $_SESSION['SURNAME'];
$firs = $_SESSION['FIRSTNAME'];
$midd = $_SESSION['MIDDLENAME'];

$staffy = $_SESSION['staffy']; 

$name= $surn.' '.$firs.' '.$midd; 
            

        $description='wallet funding for '.$name;

            if ($staffy == ""){header("Location:logout.php");}
            

$rexr = "SELECT * FROM [Bus_Booking].[dbo].[Account] WHERE staffid='$staffy'";
$din = sqlsrv_query($conn, $rexr);

// Check if the query was successful
if ($din === false) {
    // Handle SQL error
    echo "An error occurred while fetching account information.";
} else {
    // Fetch phone and email
    $rowz = sqlsrv_fetch_array($din, SQLSRV_FETCH_ASSOC);
    if ($rowz !== null && isset($rowz['phone']) && isset($rowz['email'])) {
        // Accessing array elements
        $sphone = $rowz['phone'];
        $semail = $rowz['email'];
    } else {
        // No record found
        echo "No account found for the staff ID.";
    }
}

            ?>