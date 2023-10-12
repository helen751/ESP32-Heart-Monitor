<?php
//  file that accepts the HTTP Request from the Esp32 microcontroller and uploads it to the database

$servername = "localhost";
$dbname = "helefexb_esp_data";
$username = "helefexb_espboard";
$password = "l3u070fCBXzP";

$api_key_value = "fT7gir34KLpuN";

$api_key= $sensor = $value = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);
    if($api_key == $api_key_value) {
        $sensor = test_input($_POST["sensor"]);
        $value = test_input($_POST["value"]);
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        
        $sql = "INSERT INTO heart_readings (sensor, value)
        VALUES ('" . $sensor . "', '" . $value . "')";
        
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    
        $conn->close();
    }
    else {
        echo "Wrong API Key provided.";
    }

}
else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>