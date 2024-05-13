
// booking_functions.js
function handleStandingBooking(nextStandingNumber, conn, staffy, currentDate, rid, amount,ticket_type) {
    var confirmStanding = confirm("Only standing tickets are available for this route. Would you like to proceed?");
    console.log("Confirm standing: ", confirmStanding);

    if (confirmStanding) {
        // Proceed with standing ticket booking
        console.log("Proceeding with standing ticket booking...");
        var seatNumber = nextStandingNumber;
        console.log("New seat number: ", seatNumber);
        
        // Create a FormData object to store the form data
        var formData = new FormData();
        formData.append('staffy', staffy);
        formData.append('currentDate', currentDate);
        formData.append('rid', rid);
        formData.append('seatNumber', seatNumber);
        formData.append('amount', amount);
        formData.append('ticket_type', ticket_type);


        // Make AJAX request to insert_booking.php
       var xhr = new XMLHttpRequest();
    xhr.open("POST", "insert_booking.php", true);
      xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
        if (xhr.status === 200) {
            // Parse the JSON response from the server
            var response = JSON.parse(xhr.responseText);

            // Check if the response status is "success"
            if (response.status === "success") {
                // Display success message to the user
                alert("You have successfully booked a ticket of choice.");
                // Redirect to viewbooking.php
                window.location.href = "viewbooking.php";
            } else {
                // Display the error message from the server
                alert("Booking failed: " + response.message);
            }
        } else {
            // Display error message to the user
            alert("An error occurred while processing your request. Please try again later.");
        }
    }
};

// Send the request with the form data
xhr.send(formData);

    }
}

