<?php
// 100% JESUS
session_start();

if (!isset($_SESSION['safe'])) {
    $_SESSION['safe'] = rand(999999, 9999999999);
}

$safeSeed = $_SESSION['safe'];

?>
<?php require_once('..\assets\apache\db_con.php'); ?>
<?php require_once('..\assets\apache\functions.php'); ?>
<?php require_once('..\assets\apache\constants.php'); ?>
<?php
if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);
}
$rawurl = explode( '/', $_SERVER['REQUEST_URI']);
// $urlfinder = $rawurl['1'];
if($rawurl['1'] != NULL) {
    if($rawurl['1'] == 'verify') {
        $veryfiCode = $rawurl['2'];
        // end();

        // Update the value in the database, e.g., set user_class to 1 for verified users
        $update = $con->prepare("UPDATE users SET user_class = ? WHERE verified = ?");
        $new_user_class = '1';  // Set this to the value you need, e.g., 1 for verified
        $update->bind_param("ss", $new_user_class, $veryfiCode);

        if ($update->execute()) {
            // echo "User status updated successfully.";
            // You can redirect or perform additional actions here
            $_SESSION['loginerror']['verification'] = 'Your email <b>' . $raw_email . '</b> has been verified. You can now login';
            header('Location: ../../');
        } else {
            $_SESSION['loginerror']['verification'] = 'We\'ve encountered an error while verifying your email. Please try again later.';
            header('Location: ../../');
            // echo "Failed to update user status.";
        }


    } elseif($rawurl['1'] == 'reset') {
        $veryfiCode = $rawurl['2'];

        if($_SESSION['user']) {
            // throw alert that password reset is not allowed while logged in
            echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    Password reset is not allowed while you are logged in. Please change your password instead.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
            // then clear the link
            echo '<script>
                    // Replace the current URL with the base URL
                    history.replaceState(null, "", window.location.origin);
                </script>';
        } else {
            // Prepare and execute the SQL statement
            $stmt = $con->prepare("SELECT * FROM password_retrieve WHERE retrieve_code = ?");
            $stmt->bind_param("s", $veryfiCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $urlresource = $result->fetch_assoc();
            
            // Initialize session variables
            $_SESSION['pass_retrieve'] = [
                'status' => 'invalid',
                'message' => '',
            ];
            
            // Check if any result was found
            if ($result->num_rows == 0) {
                // No code found
                $_SESSION['pass_retrieve']['message'] = 'This is an invalid reset link. Please ensure that you are using one provided by the system in your email';
            } else {
                $reset_variables = $urlresource;
            
                if (isset($reset_variables['expired'])) {
                    $_SESSION['pass_retrieve']['message'] = 'Expired reset link. Please Use the last reset link sent to your email or request a new one';
                } else {
                    // Check if the reset link is expired (older than 8 hours)
                    if ($reset_variables['created'] < date('Y-m-d H:i:s', strtotime('-8 hours'))) {
                        $_SESSION['pass_retrieve']['message'] = 'This reset link has expired. Please request a new one with your email';
                    } else {
                        // Link is valid
                        $_SESSION['pass_retrieve']['status'] = 'valid';
                        $_SESSION['pass_retrieve']['values'] = $reset_variables;
                    }
                }
            }
            
            // Redirect after processing
            // Determine what to do if logged in
    
            header('Location: /action.php');
        }

        

    } else {
        if (isset($_SERVER['PATH_INFO'])) {
            $_SESSION['request_url'] = $_SERVER['PATH_INFO'];
        }
        if(!isset($USER)){
            $_SESSION['failed_login'] = $_SERVER['REQUEST_URI'];
        }
        // dump($_SERVER);
        header('Location: /action.php');
    }
    
}

// if(isset($_SESSION['pass_retrieve'])){unset($_SESSION['pass_retrieve']);}

// dump($urlfinder);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/animate.css" />
    <link rel="stylesheet" href="/css/jquery-ui.min.css" />
    <link rel="stylesheet" href="/css/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="/css/style.css" />
    <link rel="stylesheet" href="/css/media.css" />
    <link rel="stylesheet" href="/css/cropper.min.css" />
    <link rel="stylesheet" href="/css/jquery.emojipicker.css" />
    <link rel="stylesheet" href="/css/jquery.emojipicker.a.css" />
    <link rel="stylesheet" href="/css/theming.css" />
    <!-- <script type="text/javascript" src="/js/scr.php"></script> -->
    <script src="/js/jquery-3.4.1.min.js"></script>
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/js/fontawesome.min.js"></script>
    <script src="/js/masonry.pkgd.min.js"></script>
    <script src="/js/wow.min.js"></script>
    <script src="/js/jquery.fancybox.min.js"></script>
    <!-- <script src="/js/fancybox.umd.js"></script> -->
    <script src="/js/jquery-ui.min.js"></script>
    <script src="/js/jquery.ui.touch-punch.min.js"></script>
    <script src="/js/jstz.min.js"></script>
    <script src="/js/clipboard.min.js"></script>
    <script src="/js/cropper.min.js"></script>
    <script src="/js/jquery.emojipicker.js" ></script>
    <script src="/js/jquery.emojis.js"></script>
    <script src="/js/html5-qrcode.min.js"></script>
    <script src="/js/qrcode.min.js"></script>
    <script src="/js/linkify.min.js"></script>
    <script src="/js/linkify-html.min.js"></script>
    <script src="/js/load-image.all.min.js"></script>
    <script src="/js/wavesurfer.min.js"></script>


    <title>chat app</title>
</head>
<body class="contaner-fluid">



    <?php include_once('..\assets\part\llyne_connect.php.inc');?>

    <header id="top">
        <h4>Llyne Line</h4>
        <a data-toggle="modal" data-target="#llyne_connect">
            <i class="fas fa-link"></i><?php if(isset($USER)){$notif = $pending; include('..\assets\part\badge.php.inc');} ?>
        </a>
    </header>


<?php if(isset($_SESSION['request_url']['url_search_type']) && ($_SESSION['request_url']['url_search_type'] == 'invalid')): ?>
    <script>
        $('document').ready(function(){
            alert("Invalid URI");
        });
    </script>
<?php unset($_SESSION['request_url']); elseif(isset($_SESSION['request_url']['url_search_type']) && ($_SESSION['request_url']['url_search_type'] != 'invalid')): ?>

<?php
if(isset($_SESSION['request_url']['url_search_id']))
{$urlid = $_SESSION['request_url']['url_search_id'];}
  
  if($_SESSION['request_url']['url_search_type'] == 'user') {
    $sql = "SELECT * FROM users WHERE id = '$urlid'";
    $query = mysqli_fetch_assoc(sendQuery($con, $sql));
    if(mysqli_affected_rows($con) != 1) {
        echo '<script>
                $(\'document\').ready(function(){
                    alert("Invalid URI");
                });
            </script>
        ';
        $_SESSION['request_url']['url_search_type'] = 'deleted_row';
    } else {
      include('../assets/part/zodiac_calculator.php.inc');
      $profile2['id'] = $query['id'];
      $profile2['name'] = $query['username'];
      $profile2['name'] .= '<span id="zee" tabindex="0" role="button" data-toggle="popover" data-placement="bottom" data-trigger="focus"
            title="' . $signinfo['sign'] . ' - ' . $signinfo['nickname'] . ' ' . $signinfo['sign-code'] . '" data-content="The
             ' . $signinfo['mod-code'] .' '.  $signinfo['mod'] . ' ' . $signinfo['elem-code'] .' '.  $signinfo['elem'] . ' sign.
              From ' . $signinfo['start'] . ' to ' . $signinfo['end'] . ' ">' . $signinfo['sign-code'] . '</span>';
      $profile2['bio'] = $query['bio'];
      $profile2['active'] = $query['active'];
      $profile2['photo'] = $query['avatar'];
      $profile2['private'] = $query['private'];
      $profile2['dob'] = $query['dob'];
      $profile2['email'] = $query['email'];
      $profile2['spec'] = md5($profile2['name']) . md5($profile2['id']);
    //   dump($profile);
    }

  } elseif ($_SESSION['request_url']['url_search_type'] == 'chat'){
        $sql = "SELECT * FROM chat WHERE id = '$urlid'";
        $query = mysqli_fetch_assoc(sendQuery($con, $sql));
    if(mysqli_affected_rows($con) != 1) {
        echo '<script>
                $(\'document\').ready(function(){
                    alert("Invalid URI");
                });
            </script>
            ';
            $_SESSION['request_url']['url_search_type'] = 'deleted_row';
    } else {
        $profile2['id'] = $query['id'];
        $profile2['name'] = $query['name'];
        $profile2['creator'] = $query['creator_id'];
        $profile2['created'] = $query['created'];
        $profile2['photo'] = $query['icon'];
        $profile2['bio'] = $query['description'];
        $profile2['private'] = $query['anon'];
        $profile2['spec'] = md5($profile2['creator']) . md5($profile2['name']);
    //   dump($profile);
    }
  }

  
  if(isset($profile2)){
    $usedId = $profile2['id'] * $_SESSION['safe'];
  };
?>

<div class="modal profile-open fade" id="profileurlrequest" tabindex="-1" role="dialog" aria-labelledby="urlrequestTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
    <?php if($_SESSION['request_url']['url_search_type'] == 'expired'): ?>
            <div class="modal-header">
                <p>Expired Link</p>
            </div>
            <p class="container text-monospace"><small>User / Admin has revoked this link (Request for the current / active one from source)</small></p>
    <?php elseif($_SESSION['request_url']['url_search_type'] == 'deleted_row'): ?>
            <div class="modal-header">
                <p>Not found</p>
            </div>
            <p class="container text-monospace"><small>This link did not find any significant results</small></p>
    <?php elseif($_SESSION['request_url']['url_search_type'] == 'validation_link'): ?>
            <div class="modal-header">
                <p>Not found</p>
            </div>
            <p class="container text-monospace"><small>This link did not find any significant results</small></p>
    <?php else: ?>
        <div class="modal-header">
        <small class="position-absolute"><?php if(isset($profile2['active'])){if(date($profile2['active']) == date('r')){echo '(green dot) online';}else{echo 'Last Seen: ' . '<br>' . datemodifier($profile2['active']);}} ?></small>
            <div class="holder position-relative mx-auto">
              <?php if($profile2['photo'] != NULL): ?>
                <a data-fancybox="profile-photo<?php echo $profile2['spec'] . rand(); ?>" href="<?php echo PROFILE_IMAGE . $profile2['photo'] ?>"  class="large_photo mx-auto profile-pic" data-options={"class" : "profile-view"} style="background-image: url('<?php echo PROFILE_IMAGE . $profile2['photo']; ?>');"></a>
              <?php else: ?>
                <img class="large_photo mx-auto" style="background-image:url(<?php if(isset($profile2['active'])){echo '../img/idle_profile.png';} elseif(isset($profile2['creator'])){echo '../img/idle_group.png';}?>);" />
              <?php endif; ?>
                <span class="mx-auto name"><?php echo $profile2['name']; ?></span>
            </div>
        </div>
        <?php if(isset($_SESSION['user'])): ?>
        <div class="dropdown options position-absolute">
          <i class="fas fa-ellipsis-v dropdown-toggle" id="profile<?php echo $profile2['spec'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></i>
          <div class="dropdown-menu" aria-labelledby="profile<?php echo $profile2['spec'] ?>">
            <a class="dropdown-item" href="#">Action</a> 
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a>
          </div>
        </div>
        <?php else: ?>
        <?php endif; ?>
        <div class="row mx-auto">
            <?php if(isset($profile2['active'])): ?>
                
                <?php
                  $id = $profile2['id'];
                  $sql = "SELECT id FROM users_rel WHERE  (user_id_1 = '$id' || user_id_2 = '$id') && status = '2'";
                  $connections = mysqli_fetch_assoc(sendQuery($con, $sql));
                ?>
                <small><?php echo mysqli_affected_rows($con); ?> | <i class="fas fa-link"></i></small> <br>

            <?php elseif(isset($profile2['creator'])): ?>
            <?php
              $id = $profile2['id'];
              $sql = "SELECT id FROM chat_rel WHERE  chat_id = '$id' && (status = '2' || status = '1')";
              $connections = mysqli_fetch_assoc(sendQuery($con, $sql));
            ?>
                <small><?php echo mysqli_affected_rows($con); ?> | <i class="fas fa-users"></i></small>
                <?php
                  $creator = $profile2['creator'];
                  $sql = "SELECT username, dob FROM users WHERE id = '$creator'";
                  $creator_search = mysqli_fetch_assoc(sendQuery($con, $sql));
                  $query['dob'] = $creator_search['dob'];
                  include('../assets/part/zodiac_calculator.php.inc');
                  $creatorname = $creator_search['username']. $signinfo['sign-code'];
                //   unset($signinfo);
                ?>
                <div class="row">
                    <small><?php echo 'Created by '; if(isset($USER) && $profile2['creator'] == $USER){echo 'you';}else{echo $creatorname; }?>, <?php echo datemodifier($profile2['created']); ?></small>
                </div>
                  
            <?php endif; ?>
          </div>


        <div class="row mx-auto">
            <p class="row container">
                <pre>
<?php
$max='75';
if($profile2['bio'] != NULL):
echo '<pre>' . substr($profile2['bio'], 0, $max) . '</pre>';
echo '<i style="white-space:pre;width:100%;font-size:smaller;text-align:right!important;bottom:0"><small> </small></i>';
    if(strlen($profile2['bio']) >= $max) {
        $currentLength = $max;
        $specId = md5($profile2['id'] . $currentLength);
        echo <<<MULTI
            <i target="$specId" style="white-space:pre;width:100%;font-size:smaller;
            text-align:right!important;bottom:0" class="read_more"><small>...read more</small></i>
            MULTI;
        while ($currentLength <= strlen($profile2['bio'])) {
            $newLen = $currentLength + $max;
            $tip = md5($profile2['id'] . $currentLength);
            $statement = '<pre style="display:none !important" id="'.$tip.'">' . substr($profile2['bio'], $currentLength, $max) . '</pre>';
            $currentLength += $max;
            $target = md5($profile2['id'] . $currentLength);
            if($newLen <= strlen($profile2['bio'])) {
                $statement .= <<<MULTI
                <i target="$target" style="display:none;white-space:pre;width:100%;font-size:smaller;
                text-align:right!important;bottom:0" class="read_more $tip"><small>...read more</small></i>
                MULTI;
            }
            echo $statement;
        }
    }
endif;?>
                </pre>
            </p>
            <?php if(isset($USER)): ?>
                <?php if(isset($profile2['active'])): ?>
                    <?php
                        $id = $profile2['id'];
                        $sql = "SELECT chat_id, status, user_id FROM chat_rel JOIN chat ON chat_rel.chat_id = chat.id 
                                WHERE chat_type = '2' && (user_id = '$id' || user_id = '$USER')";
                        $userGroups = sendQuery($con, $sql);
                        if(mysqli_affected_rows($con) == true){
                            $simChat = [];
                            foreach($userGroups as $group){
                                if(!in_array($group['chat_id'], $simChat)){
                                    array_push($simChat, $group['chat_id']);
                                }
                            }
                            // dump($simChat);

                            $corChat = [];

                            foreach ($simChat as $chatty) {
                                $corChat[$chatty] = [];
                                foreach ($userGroups as $group) {
                                    // dump($group);
                                    if($group['chat_id'] == $chatty){
                                        array_push($corChat[$chatty], $group['user_id']);
                                        // Halle Berry
                                        // Anna Doip
                                        // Meghan Good
                                        // Zoey Solvana
                                        // Khelani
                                        // Chilombo
                                    }
                                }
                            }
                            // dump($corChat);
                            foreach ($corChat as $chat => $val) {
                                if(in_array($id, $val) && in_array($USER, $val)){
                                    $sql = "SELECT * FROM chat where id = '$chat'";
                                    $groupChat[] = mysqli_fetch_assoc(sendQuery($con, $sql));
                                }
                            }
                        }
                    ?>
                    <?php if(isset($groupChat) && ($groupChat != NULL)): $n = 1; ?>
                        <p class="row mb-0 align"><small>Groups in Common:</small></p>
                        <?php foreach($groupChat as $key): ?>
                            <?php if($n <= '5'): ?>
                            <div class="row finesse">
                                <div class="profile profile_left">
                                    <?php $group_id = $key['id']; include('..\assets\part\profile.php.inc'); ?>
                                </div>
                                <i><small><pre><?php echo substr($bio, 0, 27); if(strlen($bio) >= '27'){echo '...';} ?></pre></small></i>
                            </div>
                            <?php endif; ?>
                        <?php $n++; endforeach; ?>
                        <?php
                        if($n >= '6'){
                            echo '<i>+ ' . ($n - 6) . 'more</i>';
                        }
                        ?>
                    
                    <?php endif; ?>
                <?php elseif(isset($profile2['creator'])): ?>
                <div class="row">
                    <?php
                    $id = $profile2['id'];
                    $sql = "SELECT * from users JOIN chat_rel ON users.id = chat_rel.user_id WHERE chat_rel.chat_id = '$id' && ( chat_rel.status = '1' || chat_rel.status = '2')";
                    $chatMembers = sendQuery($con, $sql);
                        if(mysqli_affected_rows($con) == true){
                            echo '<small class="text-muted m_group_listing">Group Members:</small>';
                            $n = 1;
                            foreach($chatMembers as $groupies) {
                                echo '<div style="position:relative;">';
                                if($n <= '5'){
                                    if($groupies['avatar'] == NULL){
                                        $imageUrl = '../img/idle_profile.png';
                                    } else {
                                        $imageUrl = PROFILE_IMAGE.$groupies['avatar'];
                                    }
                                    if($groupies['status'] == '1'){
                                        echo '
                                            <i class="fas fa-crown fa-sm" 
                                            data-toggle="popover" data-placement="bottom" data-content="Group Admin" data-trigger="hover"
                                            style="
                                                color:gold;
                                                position:absolute;
                                                left:-.55em;
                                                bottom:.45em;
                                                font-size:.75em;
                                            ">
                                            </i>
                                        ';
                                    }
                                    echo <<<MULTI
                                    <div class="profile">
                                        <div class="small_photo">
                                            <img style="background-image:url('$imageUrl')">
                                        </div>
                                    </div>
                                    MULTI;
                                    $query['dob'] = $groupies['dob'];
                                    include('../assets/part/zodiac_calculator.php.inc');
                                    echo '<small
                                            style="
                                                color:gold;
                                                position:absolute;
                                                right:-.05em;
                                                top:-.45em;
                                                font-size:xx-small;
                                            ">'
                                                . $signinfo['sign-code'] .
                                            '</small>';
                                }
                                $n++;
                                echo '</div>';
                            }
                            
                            if($n >= '6'){
                                echo '<i>+ ' . ($n - 6) . 'more</i>';
                            }
                        }
                    ?>
                  </div>

                    <?php
                    $n = '1';
                    $chatList = [];
                    if($profile2['creator'] == $USER){
                        foreach ($chatMembers as $groupies) {
                            $chatList[$n] = [];
                            if(!in_array($groupies['user_id'], $chatList[$n])){
                                $a = array('user_id', 'status');
                                $b = array($groupies['user_id'], $groupies['status']);
                                $chatList[$n] = array_combine($a, $b);
                            }else{
                                unset($chatList[$n]);
                            }
                            $n++;
                        }
                    }else{
                        foreach ($chatMembers as $groupies) {
                            $chatList[$n] = [];
                            if($groupies['status'] == '1'){
                                if(!in_array($groupies['user_id'], $chatList[$n])){
                                    $a = array('user_id', 'status');
                                    $b = array($groupies['user_id'], $groupies['status']);
                                    $chatList[$n] = array_combine($a, $b);
                                }
                            }elseif(array_key_exists($groupies['user_id'], $_SESSION['friends']) || $groupies['user_id'] == $USER) {
                                if(!in_array($groupies['user_id'], $chatList[$n])){
                                    $a = array('user_id', 'status');
                                    $b = array($groupies['user_id'], $groupies['status']);
                                    $chatList[$n] = array_combine($a, $b);
                                }
                            }else{
                                unset($chatList[$n]);
                            }
                            $n++;
                        }
                    }
                    if($chatList != NULL) {
                        usort($chatList, sortByAdmin('status'));
                        $u = '1';
                        $max = '5';
                    }
                    ?>
                    
                    <?php foreach($chatList as $guys => $values): $u++;?>
                        <?php if($guys <= $max): ?>
                        <div class="row finesse">
                            <div class="profile profile_left">
                                <?php $user_id = $values['user_id']; include('..\assets\part\profile.php.inc'); ?>
                            </div>
                            <i style="bottom:.75em">
                                <?php
                                    if($values['status'] == '1'){
                                        echo '
                                            <i class="fas fa-crown" 
                                            data-toggle="popover" data-placement="bottom" data-content="Group Admin" data-trigger="hover"
                                            style="
                                                color:gold;
                                                left:-.55em;
                                                bottom:.45em;
                                            ">
                                            </i>
                                        ';
                                    }
                                    if(array_key_exists($values['user_id'], $_SESSION['friends'])){
                                        echo '
                                            <i class="fas fa-link" 
                                            data-toggle="popover" data-placement="bottom" data-content="Connected with You" data-trigger="hover"
                                            style="
                                                color:var(--info);
                                                left:-.55em;
                                                bottom:.45em;
                                            ">
                                            </i>
                                        ';
                                    }elseif($values['user_id'] == $USER){
                                        echo 'you';
                                    }
                                ?>
                            </i>
                        </div>
                        <?php endif; ?>
                    <?php $n=$u; endforeach; ?>
                    
                    <div class="collapse row" style="margin-bottom: -2.25em" id="viewersList">
                    <?php foreach($chatList as $guys => $values): ?>
                        <?php if($guys >= ($max + 1)): ?>
                            <div class="row finesse bg-white">
                                <div class="profile profile_left">
                                    <?php $user_id = $values['user_id']; include('..\assets\part\profile.php.inc'); ?>
                                </div>
                                <i style="bottom:.75em">
                                    <?php
                                        if($values['status'] == '1'){
                                            echo '
                                                <i class="fas fa-crown" 
                                                data-toggle="popover" data-placement="bottom" data-content="Group Admin" data-trigger="hover"
                                                style="
                                                    color:gold;
                                                    left:-.55em;
                                                    bottom:.45em;
                                                ">
                                                </i>
                                            ';
                                        }
                                        if(array_key_exists($values['user_id'], $_SESSION['friends'])){
                                            echo '
                                                <i class="fas fa-link" 
                                                data-toggle="popover" data-placement="bottom" data-content="Connected with You" data-trigger="hover"
                                                style="
                                                    color:var(--info);
                                                    left:-.55em;
                                                    bottom:.45em;
                                                ">
                                                </i>
                                            ';
                                        }elseif($values['user_id'] == $USER){
                                            echo 'you';
                                        }
                                    ?>
                                </i>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    if($chatList != NULL) {
                        if($n >= ($max + 1)) {
                            $theRest = $n - ($max+2);
                            echo <<<MULTI
                                <div class="row" style="padding:.45em" data-toggle="collapse" data-target="#viewersList" aria-expanded="false" aria-controls="viewersList">
                                    <span><i class="fas fa-chevron-down fa-sm"></i> + $theRest More</span>
                                </div>
                            MULTI;
                        }
                    }
                    ?>
                    

              <?php endif; ?>
            <?php endif; ?>
            </div>
            
    <div class="modal-footer">
        <?php if(isset($_SESSION['user'])): ?>
            <!-- //check if already friends or part of the group -->
            <?php if(isset($profile2['active'])): ?>
                <?php if($profile2['id'] == $USER): ?>
                    <i>Your Profile</i>
                <?php elseif(array_key_exists($profile2['id'], $_SESSION['friends'])): ?>
                    <i>You are Connected with this User</i>
                <?php else: ?>
                    <?php
                        $id = $profile2['id'];
                        $sql = "SELECT * FROM users_rel WHERE (user_id_1 = '$id' && user_id_2 = '$USER') || (user_id_1 = '$USER' && user_id_2 = '$id')";
                        $relationship = mysqli_fetch_assoc(sendQuery($con, $sql));
                        if(mysqli_affected_rows($con) == 1){
                            if($relationship['status'] == '0'){
                                //declined or removed
                            }elseif($relationship['status'] == '1'){
                                if($relationship['user_id_1'] == $USER){
                                    echo '<i>You have Requested a Connection with this User</i>';
                                    echo <<<MULTI
                                    <div class="actionkeys">
                                        <a href="" class="bg-warning" data-toggle="popover" data-placement="top" data-content="cancel request" data-trigger="hover">
                                            <i class="far fa-stop-circle"></i>
                                        </a>
                                    </div>
                                    MULTI;
                                    // echo '<a type="button" href="action.php/?name=user&id=' . $profile2['id'] * $_SESSION['safe'] . '" class="btn btn-secondary">Connect</a>';
                                }elseif($relationship['user_id_2'] == $USER){
                                    echo '<i>This User has Requested a Connection</i>';
                                    echo <<<MULTI
                                    <div class="actionkeys">
                                    <a href=""class="bg-danger" data-toggle="popover" data-placement="top" data-content="decline request" data-trigger="hover">
                                        <i class="far fa-times-circle"></i>
                                    </a>
                                    <a href="" class="bg-success" data-toggle="popover" data-placement="top" data-content="accept request" data-trigger="hover">
                                        <i class="far fa-check-circle"></i>
                                    </a>
                                    </div>
                                    MULTI;
                                }else{
                                    echo '<i>This doesn\'t seem right; contact the site Administrator</i>';
                                }
                            }elseif($relationship['status'] == '2'){
                                echo '<i>You are Connected with this User</i>';
                            }elseif($relationship['status'] == '3'){
                                //blocked
                                if($relationship['restriction_id'] == $USER){
                                    //you blocked the user
                                    echo '<i>You blocked this User</i>';
                                }elseif($relationship['restriction_id'] != $USER){
                                    //this user blocked you
                                    echo '<i>You have been Blocked by this User</i>';
                                }else{
                                    //e nor really make any sense...lol
                                    echo '<i>This doesn\'t seem right; contact the site Administrator</i>';
                                }
                            }
                        }elseif(mysqli_affected_rows($con) == false){
                            echo '<i>Connect with this User</i>';
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                    
                            echo <<<MULTI
                            <div class="actionkeys">
                            <a href="action.php/?name=user&id= $usedId " class="bg-primary" data-toggle="popover" data-placement="top" data-content="connect now" data-trigger="hover">
                                <i class="fas fa-link"></i>
                            </a>
                            </div>
                            MULTI;
                        }
                    ?>
                <?php endif; ?>
            <?php elseif(isset($profile2['creator'])): ?>
                <?php
                $id = $profile2['id'];
                $sql = "SELECT status, stat_time FROM chat_rel WHERE chat_id = '$id' && user_id = '$USER'";
                $groupStat = mysqli_fetch_assoc(sendQuery($con, $sql));    
                ?>
                <?php if(mysqli_affected_rows($con) == '1'): ?>
                    <?php
                        if($groupStat['status'] == '0'){
                            $date = normalizeDate($groupStat['stat_time']);
                            $diff = date_diff($date, date_create_from_format('y-m-d', date('y-m-d')));
                            if($diff->d >= '1') {
                                echo '<a type="button" href="action.php/?name=group&id=' . $usedId . '" class="btn btn-secondary">Join Group</a>';
                            } elseif ($diff->h <= '24') {
                                $open = normalizeDate($groupStat['stat_time']);
                                $open->modify("+24 hours");
                                echo 'You have recently been a participant of this group and cannot be added within 24hrs of exit (' . $open->format('h\:i A - l\, jS F') . ')';
                                echo '<a type="button" href="action.php/?name=group&id=' . $usedId . '" class="btn btn-secondary">Appeal Ban</a>';
                            }
                        }elseif($groupStat['status'] == ('1' || '2')){
                            echo '<i>You are Already a Member of this Group</i>';
                        }elseif($groupStat['status'] == '3'){
                            echo '<i>You have been Banned from this Group</i>';
                        }
                    ?>
                <?php elseif(mysqli_affected_rows($con) >= '1'): ?>
                    <i>An error was found; contact the site Administrator</i>
                <?php elseif(mysqli_affected_rows($con) == false):
                            echo '<i>Request to Join Group</i>';
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                            // Start implementing adding of friends and joining groups
                    
                            echo <<<MULTI
                            <div class="actionkeys">
                            <a href="action.php/?name=user&id= $usedId " class="bg-primary" data-toggle="popover" data-placement="top" data-content="connect now" data-trigger="hover">
                                <i class="fas fa-link"></i>
                            </a>
                            </div>
                            MULTI;
                        ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <i>You need to be logged in to interact with this profile</i>
        <?php endif; ?>
    </div>
    <?php endif;?>
    </div>
  </div>
</div>
<?php  unset($_SESSION['request_url'], $simChat, $creatorname, $imageUrl, $query, $bio, $profile2, $urlid, $id, $usedId);  endif; ?>

<style>
    #profileurlrequest .modal-footer {
        position: relative !important;
    }
    #profileurlrequest .actionkeys {
        position: relative !important;
        top: 0 !important;
        left: 0 !important;
    }
    #profileurlrequest .firstlink .full-link .urlcopy {
        height: 2.45em !important;
    }
    .finesse{
        display: flex;
        /* align-content: flex-end; */
        justify-content: flex-start;
        padding: .45em;
        padding-left: .75em;
        margin: .75em;
        margin-top: .25em;
        margin-bottom: .25em;
        border: solid thin var(--light);
        border-radius: .45em;
        position: relative;
    }
    .finesse i{
        position: absolute;
        right: .5em;
        bottom: .5em;
    }.m_group_listing {
        position: absolute;
        left: 2.25em;
    }
    #profileurlrequest .profile>.small_photo>img {
        margin-left: -.35em;
    }
</style>





<div id="target-area"></div>







    <div id="home" class="container">
        <div id="alert-container" class="container mt-3"></div>
        <header>
            <h4>Home</h4>
        </header>
        
        <body>
            <?php if(!isset($_SESSION['user'])): ?>
            <div class="profile profile_right float-right">
                <?php $user_id = NULL; include('..\assets\part\profile.php.inc'); ?>
            </div>
            <?php else: ?>
            <div class="profile profile_right float-right">
                <?php $user_id = $USER; include('..\assets\part\profile.php.inc'); ?>
            </div>
            <?php endif; ?>
            <br>
            <hr>
            <form id="postForm" action="action.php" class="form">
        <fieldset>
            <textarea name="postContent" id="" draggable="false" class="post-textarea col-12 form-control"
            placeholder="Express Yourself a bit!" rows="5"></textarea>
            <div class="col-12 d-flex justify-content-between">
                <div>
                    <input type="file" id="file" name="media[]" multiple accept="image/*,video/*" />
                    <label for="file">
                        <i class="fas fa-photo-video"></i>
                    </label>
                </div>
                <div id="mediaPreview" class="d-flex flex-wrap"></div>
            </div>
        </fieldset>
    </form>
<button name="post" id="sendPost" class="form-control col-3 text-center float-right btn-secondary" disabled>Post <i class="fas fa-rocket fa-sm"></i></button>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-load-image/5.16.0/load-image.all.min.js"></script>



<script>



    $(document).ready(function() {
    $('.post-textarea').emojiPicker({
    });
    let uploadedFiles = [];
    let uploadPromises = [];
    const MAX_FILE_SIZE = 40 * 1024 * 1024; // 40MB in bytes
    const SAFE_SEED = '<?php echo $_SESSION['safe']; ?>'; // Get the PHP session safe seed

    function generateUniqueId() {
        const timestamp = Date.now();
        const random = Math.floor(Math.random() * 10000);
        return `${SAFE_SEED}_${timestamp}_${random}`;
    }

    function updateSendButton() {
        let content = $('textarea[name="postContent"]').val().trim();
        let isUploading = uploadPromises.length > 0;
        let hasContent = content !== '' || uploadedFiles.length > 0;

        if (isUploading) {
            $('#sendPost').prop('disabled', true).text('Uploading...');
        } else {
            $('#sendPost').prop('disabled', !hasContent)
                          .html('Post <i class="fas fa-rocket fa-sm"></i>');
        }
    }

    $('#file').on('change', function(e) {
        let files = e.target.files;
        if (files.length > 4 || uploadedFiles.length + files.length > 4) {
            alert('You can only upload a maximum of 4 files.');
            $('#file').val('');
            return;
        }

        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            if (file.size > MAX_FILE_SIZE) {
                alert(`File ${file.name} is too large. Maximum file size is 40MB.`);
                continue;
            }
            let fileHash = generateUniqueId();

            if (file.type.startsWith('image/')) {
                loadImage(
                    file,
                    function (img) {
                        let canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        canvas.getContext('2d').drawImage(img, 0, 0, img.width, img.height);
                        addMediaPreview(canvas.toDataURL(), fileHash, 'image');
                        uploadResizedImage(canvas, file.name, fileHash);
                    },
                    {
                        maxWidth: 1080,
                        maxHeight: 1080,
                        canvas: true,
                        orientation: true
                    }
                );
            } else if (file.type.startsWith('video/')) {
                let video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = function() {
                    video.currentTime = 1;
                }
                video.onseeked = function() {
                    let canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    addMediaPreview(canvas.toDataURL(), fileHash, 'video');
                    URL.revokeObjectURL(video.src);
                    uploadFile(file, fileHash);
                }
                video.src = URL.createObjectURL(file);
            }

            uploadedFiles.push({file: file, hash: fileHash});
        }

        updateSendButton();
    });

    function addMediaPreview(src, hash, type) {
        let preview = $('<div class="mr-2 mb-2 position-relative" data-hash="' + hash + '" style="width: 100px;">' +
                        '<img src="' + src + '" style="width: 100%; height: 100px; object-fit: cover;">' +
                        '<button class="delete-media btn btn-sm btn-danger position-absolute" style="top: 0; right: 0;">X</button>' +
                        '<div class="progress mt-1" style="height: 5px;">' +
                        '<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
                        '</div>' +
                        (type === 'video' ? '<i class="fas fa-play-circle position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2em; color: white;"></i>' : '') +
                        '</div>');
        $('#mediaPreview').append(preview);

        preview.find('.delete-media').on('click', function(e) {
            e.preventDefault();
            let hash = $(this).parent().data('hash');
            deleteMedia(hash);
        });
    }

    function deleteMedia(hash) {
        uploadedFiles = uploadedFiles.filter(file => file.hash !== hash);
        $(`[data-hash="${hash}"]`).remove();
        updateSendButton();

        let pendingUploadIndex = uploadPromises.findIndex(p => p.hash === hash);
        if (pendingUploadIndex !== -1) {
            uploadPromises[pendingUploadIndex].abort();
            uploadPromises.splice(pendingUploadIndex, 1);
        }

        if (uploadedFiles.length === 0) {
            $('#file').val('');
        }
    }

function uploadResizedImage(canvas, fileName, hash) {
    canvas.toBlob(function(blob) {
        let formData = new FormData();
        formData.append('media', blob, hash + '_' + fileName);
        formData.append('type', 'image');
        
        // Generate and append thumbnail
        let thumbnailCanvas = document.createElement('canvas');
        let ctx = thumbnailCanvas.getContext('2d');
        let ratio = Math.min(720 / canvas.width, 720 / canvas.height);
        thumbnailCanvas.width = canvas.width * ratio;
        thumbnailCanvas.height = canvas.height * ratio;
        ctx.drawImage(canvas, 0, 0, thumbnailCanvas.width, thumbnailCanvas.height);
        
        thumbnailCanvas.toBlob(function(thumbBlob) {
            formData.append('thumbnail', thumbBlob, 'thumb_' + hash + '_' + fileName);
            uploadData(formData, hash);
        }, 'image/jpeg', 0.8);
    }, 'image/jpeg', 0.9);
}

function uploadFile(file, hash) {
    let formData = new FormData();
    formData.append('media', file, hash + '_' + file.name);
    formData.append('type', file.type.startsWith('image/') ? 'image' : 'video');

    if (file.type.startsWith('video/')) {
        let video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = function() {
            video.currentTime = 1;
        }
        video.onseeked = function() {
            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');
            let ratio = Math.min(720 / video.videoWidth, 720 / video.videoHeight);
            canvas.width = video.videoWidth * ratio;
            canvas.height = video.videoHeight * ratio;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            canvas.toBlob(function(thumbBlob) {
                formData.append('thumbnail', thumbBlob, 'thumb_' + hash + '_' + file.name.replace(/\.[^/.]+$/, ".jpg"));
                uploadData(formData, hash);
            }, 'image/jpeg', 0.8);

            URL.revokeObjectURL(video.src);
        }
        video.src = URL.createObjectURL(file);
    } else {
        uploadData(formData, hash);
    }
}

function uploadData(formData, hash) {
    let xhr = new XMLHttpRequest();
    let uploadPromise = new Promise((resolve, reject) => {
        xhr.open('POST', 'upload_media.php', true);
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                let percentComplete = (e.loaded / e.total) * 100;
                $(`[data-hash="${hash}"] .progress-bar`).width(percentComplete + '%');
            }
        };
        xhr.onload = function() {
            if (xhr.status === 200) {
                resolve(JSON.parse(xhr.responseText));
            } else {
                reject(xhr.statusText);
            }
        };
        xhr.onerror = function() {
            reject(xhr.statusText);
        };
        xhr.send(formData);
    });

        uploadPromise.hash = hash;
        uploadPromise.abort = function() {
            xhr.abort();
        };

        uploadPromises.push(uploadPromise);
        updateSendButton(); // Update button state when starting upload

        uploadPromise.then((response) => {
            console.log('Upload successful:', response);
            let fileIndex = uploadedFiles.findIndex(file => file.hash === hash);
            if (fileIndex !== -1) {
                uploadedFiles[fileIndex].path = response.path;
            }
        }).catch((error) => {
            console.error('Upload failed:', error);
            deleteMedia(hash);
        }).finally(() => {
            uploadPromises = uploadPromises.filter(p => p !== uploadPromise);
            updateSendButton(); // Update button state when upload finishes
        });
    }

    $('#sendPost').on('click', function(e) {
        e.preventDefault();

        if (uploadPromises.length > 0) {
            alert('Please wait for all media to finish uploading before posting.');
            return;
        }

        var postContent = $('textarea[name="postContent"]').val().trim();
        var mediaFiles = uploadedFiles.map(file => file.path || file.hash);

        if (postContent === '' && mediaFiles.length === 0) {
            alert('Please enter some content or add media before posting.');
            return;
        }

        // Create an object with the post data
        var postData = {
            action: 'create_post',
            content: postContent,
            media: JSON.stringify(mediaFiles),
            post: 'true'
        };

        // Send an AJAX request instead of submitting a form
        $.ajax({
            url: 'action.php',
            method: 'POST',
            data: postData,
            success: function(response) {
                console.log('Post created successfully:', response);
                // Clear the form and reset the state
                $('textarea[name="postContent"]').val('');
                $('#mediaPreview').empty();
                uploadedFiles = [];
                updateSendButton();
                // Optionally, you can update the UI to show the new post
                // or refresh the posts list here

                showAlert('Post created successfully!', 'success');
            },
            error: function(xhr, status, error) {
                console.error('Error creating post:', error);
                showAlert('An error occurred while creating the post. Please try again.', 'danger');
            }
        });

        console.log('Post data sent:', postData);
    });

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $('#alert-container').html(alertHtml);

        // Automatically dismiss the alert after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

    $('textarea[name="postContent"]').on('input', updateSendButton);
});
</script>

<style>
.progress {
    background-color: #f8f9fa;
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    background-color: #007bff;
    transition: width 0.3s ease;
}
</style>
        <?php if(isset($_SESSION['user'])): ?>
            <footer>
                <div class="personal_posts fixed-bottom">
                    <a data-dismiss="modal" data-fancybox="Your_Posts" class="quickChat" data-type="ajax" href="postPage.php?id=<?php echo $USER * $_SESSION['safe']; ?>" data-toggle="popover" data-placement="top" data-content="view posts" data-trigger="hover">
                        Your Posts  <i class="fas fa-sticky-note fa-lg"></i>
                    </a>
                </div>
            </footer>
        <?php else: ?>
            <script>
                $('#sendPost').click(function(){
                    alert("HeLlo tHe uSer of The liCAtiOn, yOu hVae to the Login tO mAke a tHe pOst 🔥🌈🌠👽 ");
                });
            </script>
        <?php endif; ?>
        
        <?php 
            if (!isset($_COOKIE) || $_COOKIE == NULL) {
                echo '<script>
                    alert("Cookies are not enabled. The page will now reload.");
                    window.location.reload();
                </script>';
                exit; // Prevent further execution
            }
        ?>
    </div>
    

    <br>
    <br>
    <br>
    <br>
    <br>



    <footer id="bottom" class="fixed-bottom mx-auto">
        <div id="nav">


            <a  id="posts" class="nav">
                <i class="fas fa-sticky-note"></i>
                    <?php
                    if(isset($USER)){
                        $unseenPosts = '0';
                        foreach ($_SESSION['friends'] as $friend => $key) {
                            $partFriend = $key['user_id'];
                            $sql = "SELECT id FROM posts WHERE deleted is NULL && user_id = '$partFriend'";
                            $userPosts = sendQuery($con, $sql);
                            if (mysqli_affected_rows($con) >= '1') {
                                foreach($userPosts as $posting) {
                                    $post_url_id = 'post/' . $posting['id'];
                                    $sql = "SELECT TRUE FROM delivered WHERE content_id_url = '$post_url_id' && user_id = '$USER'";
                                    $open = sendQuery($con, $sql);
                                    if(mysqli_affected_rows($con) != TRUE) {
                                        $unseenPosts++;
                                    }
                                }
                            }
                        }
                        if($unseenPosts >= '1'){
                            $unseen = TRUE;
                        } else {
                            $unseen = FALSE;
                        }
                        $notif = $unseen; include('..\assets\part\badge.php.inc');
                    }
                    ?>
            </a>


            <a id="home" class="nav">
                <i class="fas fa-home"></i>
            </a>


            <a  id="chats" class="nav">
                <i class="fas fa-envelope"></i>
                    <?php
                    if(isset($USER)){
                        $unreadMessages = '0';
                        $sql = "SELECT chat_id FROM chat_rel WHERE user_id = '$USER' && deleted IS NULL && archived = '0'";
                        // deleted and archived processing
                        $chat_rels = sendQuery($con, $sql);
                        foreach ($chat_rels as $chats) {
                            $thischat = $chats['chat_id'];
                            $sql = "SELECT id FROM messages WHERE chat_id = $thischat && user_id != '$USER' ORDER BY sent DESC LIMIT 1";
                            $message_last = sendQuery($con, $sql);
                            $latest_messages = mysqli_fetch_assoc(sendQuery($con, $sql));
                            if(mysqli_affected_rows($con) == '1') {
                                $message_url_id = 'message/' . $latest_messages['id'];
                                $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$message_url_id' && user_id = '$USER'";
                                $open = sendQuery($con, $sql);
                                if(mysqli_affected_rows($con) != TRUE) {
                                    $unreadMessages++;
                                }
                            }
                        }
                        $notif = $unreadMessages; include('..\assets\part\badge.php.inc');
                    }
                    ?>
            </a>
            

        </div>
    </footer>

    <script src="/js/script.js"></script>
    
    <script src="/js/imagesloaded.pkgd.min.js"></script>
    <script src="/js/face-api.min.js"></script>
    <script src="/js/thumbnail-optimizer.js"></script>

</body>
</html>
