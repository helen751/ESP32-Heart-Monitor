<?php
    $servername = "localhost";
    // Your Database name
    $dbname = "helefexb_esp_data";
    // Your Database user
    $username = "helefexb_espboard";
    // Your Database user password
    $password = "l3u070fCBXzP";


    //creating the function for updating the heart-readings every 3 seconds 
    
    if(isset($_POST["read_heart"])){
    
        global $servername, $username, $password, $dbname;
    
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $sql = "SELECT value, sensor FROM heart_readings ORDER BY id desc LIMIT 1";
    
        $result = $conn->query($sql);
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
        
       
    }

   // creating the function for sending a mail to the doctor, when the BPM is high or low 
   if(isset($_POST["send_mail"])){ 
       $response = array( 
            'status' => 0,
            'message' => "Something went wrong Please try again later!",
        );
    extract($_POST);
    // sending mail to doctor
    $to = "chukwufelix5@gmail.com"; 
    $from= 'admin@helencodes.com'; 
    $fromName = 'Heart Rate Monitor'; 
 
    $subject = 'EMERGENCY!';
    
        
        
    $htmlContent = ' 
        <html> 
        <head> 
            <title>Hello Doctor </title> 
        </head> 
        <body> 
    <div style="margin-top:3%; padding-left:5%; padding-right:5%;">
            <h4>Hello Doctor</h4>
        An emergency is reported from your Patient Heart Monitoring wearable device. the device read the patient heart as <strong>'. $bp.' (BPM)</strong> </div>
            </body> 
        </html>';
    
    // Set content-type header for sending HTML email 
    $headers = "MIME-Version: 1.0" . "\r\n"; 
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
    
    // Additional headers 
    $headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
    $headers .= 'Cc: '.$email . "\r\n"; 
    $headers .= 'Bcc: '.$email . "\r\n"; 
    
    // Send email 
    if(mail($to, $subject, $htmlContent, $headers)){
        $response["status"] = 1;
    }
                
    echo json_encode($response);
        
       
    }

?>

