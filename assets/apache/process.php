<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


$timenow = date("Y-m-d H:i:s");

if (isset($_POST['signup'])) {

    

    $raw_username = trim($_POST['username'], " \t\n\r\0\x0B");
    $raw_dob = trim($_POST['dob']);
    $raw_email = trim($_POST['email']);
    $plain_pass = $_POST['pass'];
    $pass = md5(md5($plain_pass));

    $username = filter_var($raw_username, FILTER_DEFAULT);
    $dob = filter_var($raw_dob, FILTER_DEFAULT);
    $email = filter_var($raw_email, FILTER_VALIDATE_EMAIL);

    if (empty($username) || empty($dob) || empty($email) || empty($plain_pass) || strlen($raw_username) > '16'){
        $_SESSION['signuperror'] = [];
        if (empty($username)) {
            $_SESSION['signuperror']['username'] = 'Invalid Username';
        }
        if (empty($dob)) {
            $_SESSION['signuperror']['dob'] = 'Input correct Birthday';
        }
        if (empty($email)) {
            $_SESSION['signuperror']['email'] = 'Please Use a Valid Email';
        }
        if (empty($plain_pass)) {
            $_SESSION['signuperror']['pass'] = 'Password is required';
        }

        if(strlen($raw_username) > '15') {
            $_SESSION['signuperror']['username'] = 'Username Cannot be Longer than 15 Characters';
        }

        if($username != NULL) {
            $_SESSION['signupformdata']['username'] = $raw_username;
        }
        if($dob != NULL) {
            $_SESSION['signupformdata']['dob'] = $raw_dob;
        }
        if($email != NULL) {
            $_SESSION['signupformdata']['email'] = $raw_email;
        }
        header('Location: ../../');

    } else {

        $verification = md5(rand() . date('r'));
        $verification_url = 'http://' . $_SERVER['HTTP_HOST'] . '/verify/' . $verification;
        // $verification = 'false=' . $verification;
                

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // Prepare and execute the INSERT statement
            $signup = $con->prepare("INSERT INTO users(username, verified, dob, email, password) VALUES(?, ?, ?, ?, ?)");
            $signup->bind_param("sssss", $username, $verification, $dob, $email, $pass);
            $signup->execute();

            $service_id = $signup->insert_id;
            $signup->close();
        
            // Generate user URL and connect ID for email verification
            $service_id_url = 'user/' . $service_id;
            $connect_id = md5(date('r') . $service_id_url);
            $user_url = $_SERVER['HTTP_HOST'] . '/' . $connect_id;

            // print_r($verification_url);
            // end();
        
            // Send verification email
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'efua2chilled@gmail.com';
                $mail->Password = 'vinc lkfp zkla vhlf';  // Replace with your secure App Password
            
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
            
                $mail->setFrom('efua2chilled@gmail.com', 'Ailegbotor Efua');   
                $mail->addAddress($email, $username);
                $mail->CharSet = 'UTF-8';
            
                $mail->isHTML(true);
                $mail->Subject = 'Verification for chat app';
                $mail->Body = '
                    <h1>Welcome, ' . $username . ' to Chat App</h1>
                    <p>Please click on the link below to verify your account</p>
                    <a href="' . $verification_url . '">Verify Account</a>
                ';

                $mail->SMTPDebug = 3;  // For detailed SMTP debugging
            
                $mail->send();
            
                $_SESSION['signinalert']['proceed'] = $user_url;
                $_SESSION['signinalert']['proceed'] = 'verify';
            
                // Insert connect_id and service_id_url into connect_id table
                $createid = "INSERT INTO connect_id(connect_id, service_id_url) VALUES('$connect_id', '$service_id_url')";
                mysqli_query($con, $createid);
            
                header('Location: ../../');
                exit();

            } catch (Exception $e) {
                echo 'Error sending email: ' . $mail->ErrorInfo;
            }

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {  // Duplicate entry error code
                $_SESSION['signuperror']['email'] = 'This email has been used before';
                $_SESSION['signupformdata']['username'] = $raw_username ?? '';
                $_SESSION['signupformdata']['dob'] = $raw_dob ?? '';
                $_SESSION['signupformdata']['email'] = $raw_email ?? '';

                header('Location: ../../');
                exit();
            } else {
                // $_SESSION['signuperror']['fatal'] = $e;
                $_SESSION['signuperror']['fatal'] = "Oops! Try that again. \n If problem persists, contact admin \n email: ailegbotorefua@gmail.com";
                header('Location: ../../');
                exit();
            }
        }

    }
} elseif(isset($_POST['reset'])) {
    $raw_email = trim($_POST['email']);
    $email = filter_var($raw_email, FILTER_VALIDATE_EMAIL);
    if($email === false) {
        $_SESSION['signuperror']['email'] = 'Invalid email';
        $_SESSION['signupformdata']['email'] = $raw_email;
        header('Location: ../../');
        exit();
    }


    // Maximum of four password attempts within 24 hours for a single email
    // Update expired in every other entry once a new reset is requested
    
    $stmt = $con->prepare("SELECT * FROM password_retrieve WHERE email = ? AND created >= NOW() - INTERVAL 1 DAY");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $urlresource = $result->fetch_assoc();



    if ($result->num_rows >= 4) {
        
        // old links present
        $_SESSION['loginerror']['fatal'] = "You have reached the maximum number of password reset requests. Use any of the links previously sent to your email address or try again later.";
        header('Location: ../../');
        exit();
        
    } else {

        $currentTime = date('Y-m-d H:i:s');

        if ($result->num_rows >= 0) {
            // set previous links to expired
            foreach ($result as $row) {
                $stmt = $con->prepare("UPDATE password_retrieve SET expired = '$currentTime' WHERE retrieve_code = ? AND expired IS NULL");
                $stmt->bind_param("s", $row['retrieve_code']);
                $stmt->execute();
            }   
        }

        $verification = md5(rand() . date('r'));
        $verification_url = 'http://' . $_SERVER['HTTP_HOST'] . '/reset/' . $verification;
        // $verification = 'false=' . $verification;
                
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            // Prepare and execute the INSERT statement
            $reset = $con->prepare("INSERT INTO password_retrieve(retrieve_code, email, created) VALUES(?, ?, ?)");
            $reset->bind_param("sss", $verification, $email, date('Y-m-d H:i:s'));
            $reset->execute();
    
        
            // Send verification email
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'efua2chilled@gmail.com';
                $mail->Password = 'vinc lkfp zkla vhlf';  // Replace with your secure App Password
            
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
            
                $mail->setFrom('efua2chilled@gmail.com', 'Ailegbotor Efua');   
                $mail->addAddress($email, $username);
                $mail->CharSet = 'UTF-8';
            
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset for Chat App';
                $mail->Body = '
                    <h1>Hello, ' . $email . ' having problems signing in?</h1>
                    <p>Please, click on the link below reset your password</p>
                    <a href="' . $verification_url . '">Reset Password</a>
    
                    <p>Please, if you have not requested a change of password, ignore this messsgae</p>
                    <p><small>This code expires in 8 hours</small></p>
                ';
                $mail->SMTPDebug = 3;  // For detailed SMTP debugging
            
                $mail->send();
            
                $_SESSION['loginerror']['fatal'] = "A Reset link has been sent to your email, $email";
                header('Location: ../../');
                exit();
            } catch (Exception $e) {
                echo 'Error sending email: ' . $mail->ErrorInfo;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {  // Duplicate entry error code
                $_SESSION['signuperror']['email'] = 'This email has been used before';
                $_SESSION['signupformdata']['username'] = $raw_username ?? '';
                $_SESSION['signupformdata']['dob'] = $raw_dob ?? '';
                $_SESSION['signupformdata']['email'] = $raw_email ?? '';
                header('Location: ../../');
                exit();
            } else {
                // $_SESSION['signuperror']['fatal'] = $e;
                $_SESSION['signuperror']['fatal'] = "Oops! Try that again. \n If problem persists, contact admin \n email: ailegbotorefua@gmail.com";
                header('Location: ../../');
                exit();
            }
        }
    }



} elseif(isset($_POST['login'])) {
    $raw_email = trim($_POST['email']);
    $plain_pass = $_POST['pass'];
    $pass = md5(md5($plain_pass));

    $email = filter_var($raw_email, FILTER_VALIDATE_EMAIL);

    if (empty($email) || empty($plain_pass)){
        $_SESSION['loginerror'] = [];
        if (empty($email)) {
            $_SESSION['loginerror']['email'] = 'Please Use a Valid Email';
        }
        if (empty($plain_pass)) {
            $_SESSION['loginerror']['pass'] = 'Password is required';
        }

        if($email != NULL) {
            $_SESSION['loginformdata']['email'] = $raw_email;
        }
        header('Location: ../../');

    } else {

        
        $login = "SELECT id, user_class FROM users WHERE email = '$email' && password = '$pass'";
        $query = mysqli_query($con, $login);
        
        if(mysqli_affected_rows($con) == '0') {
            $_SESSION['loginerror'] = [];

            $email = "SELECT id FROM users WHERE email = '$email'";
            $query = mysqli_query($con, $email);

            if(mysqli_affected_rows($con) == '0') {
                $_SESSION['loginerror']['email'] = 'This Email is Not Registered';
                $_SESSION['loginerror']['email'] .= '    ';
                $_SESSION['loginerror']['email'] .= '    <b class="text-muted" id="Sign-Up-tab" data-toggle="tab" href="#Sign-Up" role="tab" aria-controls="Sign-Up" aria-selected="false">Sign-Up?<small class="text-muted">(New User)</small></b>';
                $_SESSION['loginformdata']['email'] = $raw_email;
                header('Location: ../../');
            } else {
                $_SESSION['loginformdata']['email'] = $raw_email;
                $_SESSION['loginerror']['pass'] = 'Password is incorrect';
                header('Location: ../../');
            }
        } elseif (mysqli_affected_rows($con) == '1') {
            $login_poll = mysqli_fetch_assoc($query);
            // print_r(mysqli_fetch_assoc($query));
            if($login_poll['user_class'] == '0') {
                $_SESSION['loginerror']['verification'] = 'This User is not yet verified. Click the link in your email <b>' . $raw_email . '</b> to verify your account';
                header('Location: ../../');
            }elseif ($login_poll['user_class'] == '1') {
                $user_id = $login_poll['id'];
                $_SESSION['key'] = openssl_random_pseudo_bytes(16);
                $_SESSION['user'] = encrypt($user_id, $_SESSION['key']);
    
                // If the user had a profile url they earlier clicked before login attempt
                if(isset($_SESSION['failed_login'])) {
                    header('Location: ' . $_SESSION['failed_login']);
                    unset($_SESSION['failed_login']);
                } else {
                    header('Location: ../../');
                }
            }
        } else {
            $_SESSION['signuperror']['fatal'] = "Oops! Try that again. \n If problem persists, contact admin \n email: ailegbotorefua@gmail.com";
            header('Location: ../');
        }

    }
} elseif(isset($_POST['pass_change'])){
    if(empty($_POST['pass']) || empty($_POST['pass2'])) {
        $_SESSION['retrieval'] = $_SESSION['saved_retrieval'];
        $_SESSION['retrieval']['error'] = 'Please fill all fields';
    }else{
        if($_POST['pass'] != $_POST['pass2']) {
            // first check if the pass and pass2 are the same
            // throw error if they are not
            $_SESSION['retrieval'] = $_SESSION['saved_retrieval'];
            $_SESSION['retrieval']['error'] = 'Passwords do not match';
        } else {
            // check if the new password is same as old one
            // if same, throw error, else, continue
            $pass = md5(md5($_POST['pass']));
            $email = filter_var($_SESSION['saved_retrieval']['values']['email'], FILTER_VALIDATE_EMAIL);
            
            $mail = "SELECT email, username, password FROM users WHERE email = '$email'";
            $query = mysqli_query($con, $mail);
            
            if(mysqli_affected_rows($con) == '0'){
                $_SESSION['pass_retrieve']['status'] = 'invalid';
                $_SESSION['pass_retrieve']['message'] = 'Something went Wrong';
            }else{
                $values = mysqli_fetch_assoc($query);
                $username = $values['username'];
                if($pass == $values['password']){
                    $_SESSION['retrieval'] = $_SESSION['saved_retrieval'];
                    $_SESSION['retrieval']['error'] = 'You Cannot use Same Password as Previous one';
                }else{
                    // update the password
                    $sql = "UPDATE users SET password = '$pass' WHERE email = '$email'";
                    $query = mysqli_query($con, $sql);
            
                    if (mysqli_affected_rows($con) == 1) {
                        $code = $_SESSION['saved_retrieval']['values']['retrieve_code'];
                        $currentTime = date('Y-m-d H:i:s');
                        $sql = "UPDATE password_retrieve SET expired = '$currentTime' WHERE retrieve_code = '$code'";
                        
                        $query = mysqli_query($con, $sql);
                        
                        if ($query && mysqli_affected_rows($con) == 1) {                            
                            $_SESSION['loginerror']['fatal'] = 'Password Changed Successfully. Login to Continue';
                            $_SESSION['loginformdata']['email'] = $email;
                            // send email that password was changed successfully
                            try {
                                $mail = new PHPMailer(true);
                                $mail->isSMTP();
                                $mail->Host = 'smtp.gmail.com';
                                $mail->SMTPAuth = true;
                                $mail->Username = 'efua2chilled@gmail.com';
                                $mail->Password = 'vinc lkfp zkla vhlf';  // Replace with your secure App Password
                            
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                $mail->Port = 465;
                            
                                $mail->setFrom('efua2chilled@gmail.com', 'Ailegbotor Efua');   
                                $mail->addAddress($email, $username);
                                $mail->CharSet = 'UTF-8';
                            
                                $mail->isHTML(true);
                                $mail->Subject = 'Password Change notification';
                                $mail->Body = '
                                    <h1>Hello, ' . $username . '</h1>
                                    <p>Your Password was just changed. If that wasn\'t you, please reset your password with your email address NOW</p>
                                ';
                
                                $mail->SMTPDebug = 3;  // For detailed SMTP debugging
                            
                                $mail->send();
                
                            } catch (Exception $e) {
                                echo 'Error sending email: ' . $mail->ErrorInfo;
                            }
                        } else {
                            $_SESSION['loginerror']['fatal'] = "Oops! Try that again. \n If the problem persists, chances are that the email is not registered.";
                        }
                    } else {
                        $_SESSION['loginerror']['fatal'] = "Oops! Try that again. \n If the problem persists, chances are that the email is not registered.";
                    }
                    
                }
            }

        }
    }
    unset($_SESSION['saved_retrieval']);
    header('Location: ../../');
} elseif(isset($_SESSION['request_url'])) {

    $rawurl = explode( '/', $_SESSION['request_url']);
    $urlfinder = $rawurl['1'];
    $sql = "SELECT * FROM connect_id WHERE connect_id = '$urlfinder'";
    $urlresource = mysqli_fetch_assoc(sendQuery($con, $sql));
    // dump($urlresource);
    $_SESSION['request_url'] = [];
    if (mysqli_affected_rows($con) == '0') {
        $_SESSION['request_url']['url_search_type'] = 'invalid';
    } else {
        if (isset($urlresource['expired'])) {
            $_SESSION['request_url']['url_search_type'] = 'expired';
        } else {
            $urlbroken = explode( '/', $urlresource['service_id_url']);
            $type = $urlbroken['0'];
            $urlid = $urlbroken['1'];
            $_SESSION['request_url']['url_search_type'] = $type;
            $_SESSION['request_url']['url_search_id'] = $urlid;
        }
    }
    // dump($_SESSION['request_url']);
    header('Location: ../../');

} elseif(isset($_SESSION['pass_retrieve'])){
    $_SESSION['retrieval'] = $_SESSION['pass_retrieve'];
    header('Location: ../../');
    unset($_SESSION['pass_retrieve']);
    // Determine what to do if logged in
}

dump($_GET);

function sendMessage($con, $sendMessage, $USER) {
    // Implement the logic to send a message or reply
    // This function should handle both chat messages and post replies
    // ... (implement message sending logic here)
    // Prepare the SQL statement

    print_r($sendMessage);
    // exit;

    $user_id = $USER;
    $chat_id = $sendMessage['chat_id'];
    $caption = $sendMessage['caption'];
    $media_type = $sendMessage['media_type'];
    $replied_message = $sendMessage['reply_id'];
    $replied_message_type = $sendMessage['replied_message_type'];
    $post_id = $sendMessage['post_id'];
    $fname = $sendMessage['original_filename'];
    // $media_size = $sendMessage['media_size'];
    if (isset($sendMessage['media_size']) && $sendMessage['media_size'] != NULL) {
        $fileSizeInBytes = $sendMessage['media_size']; // Size sent from JavaScript in bytes
        $media_size = formatFileSize($fileSizeInBytes);
    }else{
        $media_size = NULL;
    }
    if(isset($sendMessage['media_url']) && $sendMessage['media_url'] != NULL){
        $media_source = $sendMessage['media_url'];
        $parts = explode('/', $media_source);
        $last_part = '/' .  end($parts);
        $media_url = $last_part;
    }else{
        $media_url = NULL;
    }
    unset($media_source, $parts, $last_part);

    $sql = "INSERT INTO messages (user_id, chat_id, sent, caption, old_name, media_url, media_type, media_size,
                                  replied_message, replied_message_type, post_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        $timenow = date("Y-m-d H:i:s");
        // Correct the type definition string to match the number of variables
        mysqli_stmt_bind_param($stmt, "issssssssss", $user_id, $chat_id, $timenow, $caption, $fname, $media_url, $media_type, $media_size, $replied_message, $replied_message_type, $post_id);

        // add media type and media size
        $success = mysqli_stmt_execute($stmt);

        if ($success) {
            $message_id = mysqli_insert_id($con);
            $result = mysqli_query($con, "SELECT * FROM messages WHERE id = $message_id");

            if ($result) {
                $row = mysqli_fetch_assoc($result);
                return ['success' => true, 'data' => $row];
            } else {
                return ['success' => false, 'error' => 'Failed to retrieve the inserted row.'];
            }
        }
    } else {
        return ['success' => false, 'error' => mysqli_error($con)];
    }
    unset($sendMessage);
}

function createPost($con, $USER, $caption, $media_urls) {
    // Prepare the SQL statement
    $sql = "INSERT INTO posts (user_id, created, caption, media_url, media2_url, media3_url, media4_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        $timenow = date("Y-m-d H:i:s");
        mysqli_stmt_bind_param($stmt, "issssss", $USER, $timenow, $caption, $media_urls[0], $media_urls[1], $media_urls[2], $media_urls[3]);
        
        $success = mysqli_stmt_execute($stmt);
        
        if ($success) {
            $post_id = mysqli_insert_id($con);
            return ['success' => true, 'post_id' => $post_id];
        } else {
            return ['success' => false, 'error' => mysqli_error($con)];
        }
    } else {
        return ['success' => false, 'error' => mysqli_error($con)];
    }
}

function formatFileSize($size) {
    // Define size units
    $units = array('bytes', 'KB', 'MB', 'GB', 'TB');
    $unitIndex = 0;

    // Convert size to higher units as long as it's greater than 1024 and not the last unit
    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    // Return size with 2 decimal places and appropriate unit
    return round($size, 2) . ' ' . $units[$unitIndex];
}

function handleMessage($formData, $files, $USER, $con) {
    $message_queue = [];
    $sendMessage = [];
    $firstPass = TRUE;

    dump($formData);
    // exit;

    if (isset($formData['target'])) {
        $target = explode('_', $formData['target']);
        $sendMessage['replied_message_type'] = $target[0];
    } else {
        // Handle the error or set a default value
        return ['success' => false, 'error' => 'Target not provided'];
    }

    if($sendMessage['replied_message_type'] == 'post') {
        // Handle post reply
        // Find the Post's Chat box
        // Make use of the particular post's id to reference it
        $find = explode('_', $formData['target']);
        $other = $find[1] / $_SESSION['safe'];

        $sendMessage['post_id'] = $other;
        $sendMessage['reply_id'] = NULL;

        // Use the post to look for chat_id that links $USER and $user_id [of post author]
        $sql = "SELECT user_id FROM posts WHERE id = '$other'";
        $gett = sendQuery($con, $sql);
        if(mysqli_affected_rows($con) == 0){
            return ['success' => false, 'error' => 'Failed to get User ID'];
        }else{
           // Fetch the author data
            $author = mysqli_fetch_assoc(sendQuery($con, $sql));

            // Assuming you want to use the author's 'id' field in the next query
            $author_id = $author['user_id'];  // Replace 'id' with the appropriate key from the fetched author data

            // Create the next SQL query
            $sql = "SELECT chat_id FROM chat_rel 
                    JOIN chat ON chat_rel.chat_id = chat.id 
                    WHERE chat_type = '1' AND user_id = '$author_id'";

            // Execute the query (if necessary)
            $result = sendQuery($con, $sql);

            $theDM = mysqli_fetch_assoc(sendQuery($con, $sql));
            // Found the DM of the poster

            // $ChatIds = sendQuery($con, $sql);
            if(mysqli_affected_rows($con) == 0) {
                return ['success' => false, 'error' => 'Failed to find chat'];
            } else {

                $sendMessage['chat_id'] = $theDM['chat_id'];
                // retrieved from database
            }
        }


    } elseif($sendMessage['replied_message_type'] == 'chat') {

        $sendMessage['post_id'] = NULL;
        $sendMessage['chat_id'] = $target[1] / $_SESSION['safe'];
        // retrieved from decoded post request

        if(isset($formData['reply']) && $formData['reply'] != null) {
            $direct = explode('/', $formData['reply']);
            $sendMessage['reply_id'] = $direct[1] / $_SESSION['safe'];
            // retrieved from decoded post request
        }else{
            $sendMessage['reply_id'] = NULL;
        }

    }
    
    if (!empty($formData['uploaded_files']) && is_array($formData['uploaded_files'])){

        foreach ($formData['uploaded_files'] as $att => $val) {

            // Ensure the keys exist in all arrays before using them
            if (isset($formData['media_types'][$att], $formData['media_sizes'][$att])) {
                $sendMessage['media_url'] = $formData['uploaded_files'][$att];
                $sendMessage['media_type'] = $formData['media_types'][$att];
                $sendMessage['media_size'] = $formData['media_sizes'][$att];
                $sendMessage['original_filename'] = $formData['original_filenames'][$att];

                if ($firstPass == TRUE) {
                    $sendMessage['caption'] = $formData['caption']; // Set your caption
                }else{
                    $sendMessage['caption'] = NULL; // Set your caption
                }
                
                $firstPass = false; // Mark first pass as complete
                $firstPass = null; // Mark first pass as complete
                $message_queue[] = $sendMessage;
                // unset($formData);
            }
            // $result = sendMessage($con, $sendMessage, $USER);
            // echo json_encode($result);
        }
    }else{
        $sendMessage['media_url'] = NULL;
        $sendMessage['media_type'] = NULL;
        $sendMessage['media_size'] = NULL;
        $sendMessage['original_filename'] = NULL;
        if(isset($formData['caption'])){
            $sendMessage['caption'] = $formData['caption'];
        }else{
            $sendMessage['caption'] = NULL;
        }

        $message_queue[] = $sendMessage;
    }


    foreach($message_queue as $key => $val) {

        $sendMessage['chat_id'] = $val['chat_id'];
        $sendMessage['caption'] = $val['caption'];
        $sendMessage['media_url'] = $val['media_url'];
        $sendMessage['media_type'] = $val['media_type'];
        $sendMessage['media_size'] = $val['media_size'];
        $sendMessage['original_filename'] = $val['original_filename'];
        $sendMessage['reply_id'] = $val['reply_id'];
        $sendMessage['replied_message_type'] = $val['replied_message_type'];
        $sendMessage['post_id'] = $val['post_id'];

        // print_r($val);

        $uploadData[] = sendMessage($con, $sendMessage, $USER);
    }


    return $uploadData;

    
}

function handlePost($postData, $files, $USER, $con) {
    $content = isset($postData['content']) ? $postData['content'] : '';
    $media = isset($postData['media']) ? json_decode($postData['media'], true) : [];

    $caption = $content;
    $media_urls = array_pad($media, 4, null);

    // Remove MEDIA_LOCALE path and get just the filename
    for ($i = 0; $i < 4; $i++) {
        if ($media_urls[$i]) {
            $media_urls[$i] = '/' . basename($media_urls[$i]);
        }
    }

    // Create the post
    return createPost($con, $USER, $caption, $media_urls);
}

// Look up JQuery AJAX method