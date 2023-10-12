<!DOCTYPE HTML>
<html>
  <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="heart-style.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <title>Heart Rate Monitor</title>
  </head>

  <body>
    <h2 style="margin-top:10%">Heart Rate Monitor</h2>
    <p><strong>Steps to start montoring your Heart Rate</strong></p>
    <ol style="text-align:left !important">
      <li>Get the Wearable device</li>
      <li>Wear it carefully on your wrist</li>
      <li>Check here to see live Heart Readings</li>
      <li>Reading updates here every 3 Seconds</li>
    </ol>
    
    <div id="chart">
          
    </div>

    <?php
      $servername = "localhost";
      // Your Database name
      $dbname = "helefexb_esp_data";
      // Your Database user
      $username = "helefexb_espboard";
      // Your Database user password
      $password = "l3u070fCBXzP";
      global $servername, $username, $password, $dbname;

      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);
      // Check connection
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }
        
      // fetchng the Pulse sensor data from the database
      $sql2 = "SELECT value, sensor FROM heart_readings ORDER BY id desc LIMIT 1";
      $result2 = mysqli_query($conn,$sql2);
      $count2 = mysqli_num_rows($result2);
    
      $row = mysqli_fetch_array($result2, MYSQLI_ASSOC);
      $sensor = $row["sensor"];
      $sval = $row["value"];
    
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

      
    <script>
      myAudio = new Audio('https://www.soundjay.com/buttons/sounds/beep-05.mp3');
      var hpb = false;
      var send_mail = true;
      var sv = <?php echo $sval ?>;
      var vl = parseInt(sv)/200 * 100;
      var options = {

        series: [vl],
        chart: {
          type: 'radialBar',
        },
        plotOptions: {
          radialBar: {
            startAngle: -90,
            endAngle: 90,
            track: {
              background: "#e7e7e7",
              strokeWidth: '97%',
              dropShadow: {
                enabled: true,
                top: 0,
                left: 0,
                color: '#999',
                opacity: 1,
                blur: 2
              }
            },
                
            dataLabels: {
              name: {
              show: true,
              },
              value: {
                show: true,
                fontSize: '20px',
                formatter: function (val) {
                return sv + ' BPM'
                }
              },
            
            }
        
          }
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'light',
            shadeIntensity: 0.4,
            inverseColors: false,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
          },
        },
            labels: ['Heart Rate'],
      };

      var chart = new ApexCharts(document.querySelector("#chart"), options);
      chart.render();
          
      window.onload = () => Swal.fire('Welcome! Please make sure the Wearable device is Turned on').then((result) => {
        if (result.isConfirmed) {
          setInterval(function () {
            const formData = new FormData();
            formData.append('read_heart', true);
            const options = {
              method: "Post",
              body: formData,
            }

            fetch('esp-database.php', options)
            .then(data => data.json())
            .then(res => {
              var sv = parseInt(res[0].value);
              if((sv >= 100) || (sv <= 55)){
                hbp = true;
              }
              else{
                hbp = false;
              }
              var vl = sv/200 * 100;
        
              chart.updateOptions({
                plotOptions: {
                  radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                      background: "#e7e7e7",
                      strokeWidth: '97%',
                      dropShadow: {
                        enabled: true,
                        top: 0,
                        left: 0,
                        color: '#999',
                        opacity: 1,
                        blur: 2
                      }
                    },
                
                    dataLabels: {
                      name: {
                        show: true,
                      },
                      value: {
                        show: true,
                        fontSize: '20px',
                        formatter: function (val) {
                          return sv + ' BPM'
                        }
                      },
            
                    }
        
                  }
                },
          
              })
              chart.updateSeries([vl])
              console.log(sv)
              if(hbp == true){
                if (typeof myAudio.loop == 'boolean'){
                  myAudio.loop = true;
                }
                else{
                  myAudio.addEventListener('ended', function() {
                    this.currentTime = 0;
                    this.play();
                  }, false);
                }
                myAudio.play();
                Swal.fire({
                  position: 'top-end',
                  icon: 'warning',
                  title: 'EMERGENCY!',
                  showConfirmButton: false,
                  timer: 5000
                })
              }
              else{
                myAudio.loop = false;
                myAudio.pause();
                myAudio.currentTime = 0;
              }
                
              if(((sv >= 100) || (sv <= 55)) && (send_mail == true)){
                send_mail_now(sv);
                send_mail = false;
                setTimeout(function(){send_mail = true}, 300000)
              }
                
            });
          }, 3000);
        }
      })
            
          
      // creating the function for sending a mail to the doctor, when the BPM is high or low 
      function send_mail_now(bp){
        const formData = new FormData();
        formData.append('send_mail', true);
        formData.append('bp', bp);
        const options = {
          method: "Post",
          body: formData,
        }

        fetch('esp-database.php', options)
        .then(data => data.json())
        .then(res => {
          if(res.status == 1){
            swal.fire("An emergency Email alert has been sent to the Doctor");
          }
          else{
            alert("Failed to send Emergency Email Alert to Doctor");
          }
        })
      }
    </script>

  </body>
</html>