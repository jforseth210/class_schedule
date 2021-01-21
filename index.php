<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';


    function readArrayFromJSON($filename){
        $fileContents = file_get_contents($filename);
        $readArray = json_decode($fileContents, true);
        return $readArray;
    };
    if (isset($_POST['newClass'])) {
        $period = intVal($_POST['period']);
        $class = $_POST['class'];
        $link = $_POST['link'];
        $code = $_POST['code'];
        $newClassArray = array(
            "Name" => $class,
            "Video Call" => $link
        );

        //Make a copy
        date_default_timezone_set("America/Denver");
        $backupName = "backups/backup_" . date("m-d-Y_h:i:s_A") . ".json";
        copy("courses.json",$backupName);

        $masterArray = readArrayFromJSON("courses.json");
        $periods = array_keys($masterArray);

        $logFile=fopen("activityLog.txt","a");
        $codeArray = readArrayFromJSON("codes.json");

        $logString="";
        if (array_key_exists($code,$codeArray)){
            $logString = date("m-d-Y_h:i:s_A") . ": {$codeArray[$code]} added {$periods[$period]} {$newClassArray["Name"]} at {$newClassArray["Video Call"]}\n";
            array_push($masterArray[$periods[$period]], $newClassArray);
        }

        fwrite($logFile, $logString);
        fclose($logFile);


        $writeString = json_encode($masterArray, JSON_PRETTY_PRINT);
        $writeFile = fopen("courses.json", "w");
        fwrite($writeFile, $writeString);
        fclose($writeFile);
    }

    if (isset($_POST['deleteClass'])){
        $class = $_POST["class"];
        $code = $_POST["code"];

        //Make a copy
        date_default_timezone_set("America/Denver");
        $backupName = "backups/backup_" . date("m-d-Y_h:i:s_A") . ".json";
        copy("courses.json",$backupName);

        $fileContents = file_get_contents("courses.json");
        $codeArray = readArrayFromJSON("codes.json");

        $masterArray = json_decode($fileContents, true);

        $periods = array_keys($masterArray);

        $logFile=fopen("activityLog.txt","a");
        $writeFile = fopen("courses.json", "w");
        $logString="";
        for ($period = 0; $period <= sizeof($masterArray) -1; $period++){
            for ($course = 0; $course <= sizeof($masterArray[$periods[$period]]) -1; $course++){
                if ($masterArray[$periods[$period]][$course]["Name"] == $class && array_key_exists($code,$codeArray)){
                    $logString = date("m-d-Y_h:i:s_A") . ": {$codeArray[$code]} deleted {$periods[$period]} {$masterArray[$periods[$period]][$course]["Name"]} at {$masterArray[$periods[$period]][$course]["Video Call"]}\n";
                    unset($masterArray[$periods[$period]][$course]);
                }
            }
        }


        $writeString = json_encode($masterArray, JSON_PRETTY_PRINT);
        fwrite($writeFile, $writeString);
        fclose($writeFile);
        fwrite($logFile, $logString);
        fclose($logFile);
    }
    if (isset($_POST['updateClass'])){
        $class = $_POST["class"];
        $link = $_POST["link"];
        $code = $_POST["code"];

        //Make a copy
        date_default_timezone_set("America/Denver");
        $backupName = "backups/backup_" . date("m-d-Y_h:i:s_A") . ".json";
        copy("courses.json",$backupName);

        $fileContents = file_get_contents("courses.json");
        $codeArray = readArrayFromJSON("codes.json");

        $masterArray = json_decode($fileContents, true);

        $periods = array_keys($masterArray);

        $logFile=fopen("activityLog.txt","a");
        $writeFile = fopen("courses.json", "w");
        $logString="";
        for ($period = 0; $period <= sizeof($masterArray) -1; $period++){
            for ($course = 0; $course <= sizeof($masterArray[$periods[$period]]) -1; $course++){
                if ($masterArray[$periods[$period]][$course]["Name"] == $class && array_key_exists($code,$codeArray)){
                    $logString = date("m-d-Y_h:i:s_A") . ": {$codeArray[$code]} updated {$periods[$period]} {$masterArray[$periods[$period]][$course]["Name"]} from {$masterArray[$periods[$period]][$course]["Video Call"]} to $link\n";
                    $masterArray[$periods[$period]][$course]["Video Call"] = $link;
                }
            }
        }


        $writeString = json_encode($masterArray, JSON_PRETTY_PRINT);
        fwrite($writeFile, $writeString);
        fclose($writeFile);
        fwrite($logFile, $logString);
        fclose($logFile);
    }
    if (isset($_POST['getAuthorizationCode'])){
        $phone = $_POST['phone'];

        echo "<script>console.log('Phone $phone');</script>";

        $phone = str_replace("1 (","",$phone);
        $phone = str_replace("1(","",$phone);
        $phone = str_replace("1406","406",$phone);
        $phone = str_replace("1 406","406",$phone);
        $phone = str_replace("(","",$phone);
        $phone = str_replace(")","",$phone);
        $phone = str_replace("-","",$phone);
        $phone = str_replace(" ","",$phone);
        $phone = str_replace("+","",$phone);

        $six_digit_random_number = mt_rand(100000, 999999);

        echo "<script>console.log('Phone $phone');</script>";

        $fileContents = file_get_contents("codes.json");
        $codeArray = json_decode($fileContents, true);
        while (array_key_exists($six_digit_random_number, $codeArray)){
            $six_digit_random_number = mt_rand(100000, 999999);
        }
        $codeArray[$six_digit_random_number] = $phone;

        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'mail.jforseth.tech';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'support';                     // SMTP username
            $mail->Password   = 'badger123isabadpassword!';                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('support@jforseth.tech');
            $mail->addAddress($phone.'@vtext.com');
            $mail->addAddress($phone.'@tmomail.net');
            $mail->addAddress($phone.'@txt.att.net');
            $mail->addAddress($phone.'@smsmyboostmobile.com');
            $mail->addAddress($phone.'@sms.cricketwireless.net');
            $mail->addAddress($phone.'@messaging.sprintpcs.com');
            $mail->addAddress($phone.'@email.uscc.net');
            $mail->addAddress($phone.'@vmobl.com');
            //$mail->addAddress('justin@jforseth.tech');

            // Content
            $mail->isHTML(false);                                  // Set email format to HTML
            $mail->Subject = '';
            $mail->Body = 'Your authorization code is: ' . $six_digit_random_number;

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        $writeString = json_encode($codeArray, JSON_PRETTY_PRINT);
        $writeFile = fopen("codes.json", "w");
        fwrite($writeFile, $writeString);
        fclose($writeFile);
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="shortcut icon" href="https://classes.jforseth.tech/favicon.ico"  type="image/icon ico">
    <title>FHS Online Classes</title>

    <link rel="stylesheet" href="style.css"/>

  </head>
  <body>
    <audio id=bellsound controls hidden>
        <source src="bell.mp3" type="audio/mpeg">
    </audio>
    <nav class="navbar navbar-expand-lg navbar-dark bg-eagle-blue">
      <a class="navbar-brand" href="#">
        <!--<img src="https://www.fairfield.k12.mt.us/assets/apptegy_cms/themes/fairfieldmt_redesign/logo-cbc4e3d99b88c38a21dfee8db829a97e.png" width="79" height="44" class="d-inline-block align-top" alt="">-->
        <div class="d-inline-block align-middle"><b><h3>FHS Online Classes</b></h3></div>
      </a>
    </nav>
    <br/>
    <br/>
    <br/>
    <div class=container>
        <h1>Instructions</h1>
        <p>Select your classes and leave this tab open to automatically have them open when the "bell rings", or click "Go to class" to open the class now.</p>
        <p>Link changed? Your class missing? Get an authorization code to update this page.</p>
        <!--<p><b><i>I recently discovered a bug with the sign-up system, which has since been fixed. If you're having problems adding or modifying classes, please request a new code. Sorry for the inconvience! If you run into any problems, feel free to email me at <a href="mailto:support@jforseth.tech">support@jforseth.tech</a>.</i></b></p>-->
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#authModal">Get Authorized</button>
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">Delete a class</button>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#updateModal">Update a link</button>
    </div>
    <br/>
    <br/>
    <br/>
    <!--Allow extensions to remember schedule-->
    <form id="scheduleForm"></form>
    <?php
         $courseArray = readArrayFromJSON("courses.json");
         $periods = array_keys($courseArray);
         for ($period = 0; $period <= sizeof($courseArray) - 1; $period++){
            $currentPeriod = $periods[$period];
            echo "
                <div class=container>
                     <h1>{$periods[$period]}</h1>
                     <div class=row>
            ";
            for ($course = 0; $course <= sizeof($courseArray[$currentPeriod]) - 1; $course++){
                echo "
                <div class=\"col-md-3\">
                <label>
                <input form=\"scheduleForm\" name=\"$period\" type=\"radio\" value=\"{$courseArray[$currentPeriod][$course]["Name"]}\" class=\"card-input-element d-none\" id=\"classcardp" . $period . "c" .$course . "\" />
                <div class=\"card mx-auto\" style=\"height:100px !important\">
                    <div class=\"card-body\">
                        <h4 class=\"card-title\">{$courseArray[$currentPeriod][$course]["Name"]}</h4>
                        <a href=\"{$courseArray[$currentPeriod][$course]["Video Call"]}\" target=\"_blank\">Go to class</a>
                    </div>
                </div>
                </label>
                </div>
                ";
            }
            echo "
        <div class=\"col-md-3\">
                <div class=\"card mx-auto\">
                    <div class=\"card-body\">
                        <form method=\"POST\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) ."\">
                        <h4 class=\"text-primary\">Add a Class</h4>
                        <h4 class=\"card-title\"><input name=\"class\" class='form-control' placeholder='Class Name' /></h4>
                        <h4 class=\"card-title\"><input name=\"link\" class='form-control' placeholder='https://' /></h4>
                        <h4 class=\"card-title\"><input name=\"code\" class='form-control' placeholder='Auth Code' /></h4>
                        <input hidden name=\"period\" value=\"$period\" />
                        <input hidden name=\"newClass\"type=\"submit\" style=\"display: none\" />
                        </form>
                    </div>
                </div>
        </div>
        </div>
        </div>
        </div>

            ";
        }
        ?>
        <!-- Modal -->
        <div class="modal fade" id="authModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Get Authorization</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Enter your phone number below</p>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
                            <input style="display:inline" name="phone" type=tel class="form-control w-75">
                            <input style="display:inline" name="getAuthorizationCode" type=submit class="btn btn-success" value="Get Code"/>
                        </form>
                        <br/>
                        <br/>
                        <p>You'll be texted a six digit code. Enter this code when you want to add/remove/modify classes.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Delete a Class</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Delete a class:</p>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
                            <select name="class" class="form-control w-75">
                                <?php
                                    $fileContents = file_get_contents("courses.json");
                                    $readArray = json_decode($fileContents, true);
                                    $periods = array_keys($readArray);
                                    for ($period = 0; $period <= sizeof($readArray) -1; $period++){
                                        for ($course = 0; $course <= sizeof($readArray[$periods[$period]]) -1; $course++){
                                            echo "<option>". $readArray[$periods[$period]][$course]["Name"] . "</option>";
                                        }
                                    }
                                ?>
                            </select>
                            <input style="display:inline" class="form-control w-75" name="code" placeholder="Authorization Code"/>
                            <input style="display:inline" name="deleteClass" type=submit class="btn btn-danger" value="Delete Class"/>
                        </form>
                        <br/>
                        <p>All activity is recorded. Malicious deletion will be reported.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Update a Class</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Update a class:</p>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
                            <select name="class" class="form-control w-75">
                                <?php
                                    $fileContents = file_get_contents("courses.json");
                                    $readArray = json_decode($fileContents, true);
                                    $periods = array_keys($readArray);
                                    for ($period = 0; $period <= sizeof($readArray) -1; $period++){
                                        for ($course = 0; $course <= sizeof($readArray[$periods[$period]]) -1; $course++){
                                            echo "<option>". $readArray[$periods[$period]][$course]["Name"] . "</option>";
                                        }
                                    }
                                ?>
                            </select>
                            <input style="display:inline" class="form-control w-75" name="link" placeholder="https://"/>
                            <input style="display:inline" class="form-control w-75" name="code" placeholder="Authorization Code"/>
                            <input style="display:inline" name="updateClass" type=submit class="btn btn-danger" value="Update Class"/>
                        </form>
                        <br/>
                        <p>All activity is recorded. Malicious modification will be reported.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <footer class="text-center">
          <small>Bell Sound &#169; John Sauter</small><br/>
          <small>Website designed and hosted by Justin Forseth</small>
        </footer>
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="script.js"></script>
  </body>
</html>
