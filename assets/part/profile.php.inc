<?php

$profile = [];

// session_start();

if(!isset($_SESSION['user'])) {
    $profile['id'] = 0;
    $profile['name'] = 'Login / Sign-up';
    $profile['photo'] = NULL;
    $profile['spec'] = md5($profile['name']) . md5($profile['id']);

    // if(isset($_SESSION['signuperror']) || isset($_SESSION['signinalert'])  || isset($_SESSION['loginerror']) || isset($_SESSION['retrieval'])) {
    //     $profile['spec'] = 'urlrequest';
    // }
    $errorKeys = ['signuperror', 'signinalert', 'loginerror', 'retrieval'];

    foreach ($errorKeys as $key) {
      if (isset($_SESSION[$key]) && ($_SESSION[$key] != NULL)) {
      // if (isset($_SESSION[$key])) {
          $profile['spec'] = 'urlrequest';
          break; // Stop checking once we find any of the keys set
      }
    }

    $profile['restricted'] = FALSE;
} else {
    if(isset($user_id)) {
        $sql = "SELECT * FROM users WHERE id = '$user_id'";
        $query = mysqli_fetch_assoc(sendQuery($con, $sql));
        if(mysqli_affected_rows($con) != 1) {
          echo 'invalid user profile';
        } else {
          include('zodiac_calculator.php.inc');
          $profile['id'] = $query['id'];
          $profile['name_raw'] = $query['username'];
          $profile['name'] = $query['username'];
          $profile['zod'] = '<span id="zee" tabindex="0" role="button" data-toggle="popover" data-placement="bottom" data-trigger="focus" title="' . $signinfo['sign'] . ' - ' . $signinfo['nickname'] . ' ' . $signinfo['sign-code'] . '" data-content="The ' . $signinfo['mod-code'] .' '.  $signinfo['mod'] . ' ' . $signinfo['elem-code'] .' '.  $signinfo['elem'] . ' sign. From ' . $signinfo['start'] . ' to ' . $signinfo['end'] . ' ">' . $signinfo['sign-code'] . '</span>';
          // $profile['name'] .= '<span id="zee" tabindex="0" role="button" data-toggle="popover" data-placement="bottom" data-trigger="focus" title="' . $signinfo['sign'] . ' - ' . $signinfo['nickname'] . ' ' . $signinfo['sign-code'] . '" data-content="The ' . $signinfo['mod-code'] .' '.  $signinfo['mod'] . ' ' . $signinfo['elem-code'] .' '.  $signinfo['elem'] . ' sign. From ' . $signinfo['start'] . ' to ' . $signinfo['end'] . ' ">' . $signinfo['sign-code'] . '</span>';
          $profile['name'] .= $profile['zod'];
          $profile['bio'] = $query['bio'];
          $profile['active'] = $query['active'];
          $profile['photo'] = $query['avatar'];
          $profile['private'] = $query['private'];
          $profile['dob'] = $query['dob'];
          $profile['email'] = $query['email'];
          $profile['spec'] = md5($profile['name']) . md5($profile['id']);
          $profile['color'] = $signinfo['color'];
          // dump($profile);
          $bio = $profile['bio'];
        }
    
      } elseif (isset($group_id)){
        $sql = "SELECT * FROM chat WHERE id = '$group_id'";
        $query = mysqli_fetch_assoc(sendQuery($con, $sql));
        if(mysqli_affected_rows($con) != 1) {
          echo 'invalid group';
        } else {

          $profile['id'] = $query['id'];
          $profile['name'] = $query['name'];
          $profile['creator'] = $query['creator_id'];
          $profile['created'] = $query['created'];
          $profile['photo'] = $query['icon'];
          $profile['bio'] = $query['description'];
          $profile['private'] = $query['anon'];
          $profile['spec'] = md5($profile['creator']) . md5($profile['name']);
          $profile['color'] = 'black';
          // dump($profile);
          $bio = $profile['bio'];
        }
      }
    }

    $dat = rand();
    $name = $profile['name']; 

?>

<?php if((mysqli_affected_rows($con) == true) || !isset($_SESSION['user'])): ?>

  <?php
    if(isset($profile['active'])){
      $unopendPost = 0;
      $id = $profile['id'];
      $sql = "SELECT id FROM posts WHERE user_id = '$id' ORDER BY id DESC LIMIT 5"; 
      $userPosts22 = sendQuery($con, $sql);
      if(mysqli_affected_rows($con) >= '1'){
        foreach ($userPosts22 as $posts) {
          $post_url_id3 = 'post/' . $posts['id'];
          $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$post_url_id3' && user_id = '$USER'";
          $viewedPost = sendQuery($con, $sql);
          if(mysqli_affected_rows($con) != TRUE) {
            $unopendPost++;
          }
        }
      }

      if(isset($_SESSION['friends']) && array_key_exists($id, $_SESSION['friends'])){

        $chat13 = $_SESSION['friends'][$id]['chat_id'];

        $nChecked2 = 0;
  
        $sql = "SELECT id FROM messages WHERE chat_id = $chat13 && user_id != $USER ORDER BY id DESC LIMIT 5";
        $message_last3 = sendQuery($con, $sql);
        foreach ($message_last3 as $dem) {
          $message_url_id3 = 'message/' . $dem['id'];
          $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$message_url_id3' && user_id = '$USER'";
          $open3 = sendQuery($con, $sql);
          if(mysqli_affected_rows($con) != TRUE) {
            $nChecked2++;
          }
        }
      }

      $id44 = $query['id'];
      $sql = "SELECT TRUE FROM users_rel WHERE( user_id_1 = '$USER' && user_id_2 = '$id44'  || user_id_1 = '$id44' && user_id_2 = '$USER') && restriction_id IS TRUE";
      $isRestricted = mysqli_fetch_assoc(sendQuery($con, $sql));  
      if($isRestricted == TRUE){
        $profile['restricted'] = TRUE;
      }else {
        $profile['restricted'] = FALSE;
      }
      unset($isRestricted);
    }elseif(isset($profile['creator'])){
      $id44 = $query['id'];
      $sql = "SELECT status FROM chat_rel WHERE user_id = '$USER' && chat_id = '$id44'";
      $isRestricted = mysqli_fetch_assoc(sendQuery($con, $sql));  
      if($isRestricted['status'] == '3'){
        $profile['restricted'] = TRUE;
      }else {
        $profile['restricted'] = FALSE;
      }
      unset($isRestricted);
    }

  ?>
<a data-toggle="modal" style="position:relative" class="small_photo" data-target="#profile<?php echo $profile['spec']; if(isset($_SESSION['user'])){echo $dat;} ?>">
    <div style="position:absolute; bottom:0">
    </div>
    <img style="background-image:url(
        <?php if($profile['photo'] != NULL) {
            echo PROFILE_IMAGE . $profile['photo'];
          } else { 
            if(isset($profile['creator'])) {
              echo '../img/idle_group.png';
            } else {
              echo '../img/idle_profile.png';
            }
          }
        ?>
      );
      
      <?php     
        if(isset($profile['active']) && $profile['id'] != $USER){
          if((isset($nChecked2) && $nChecked2 >= '1') || $unopendPost>='1') {
            echo 'border: solid thin var(--warning)';
          }
        } 
      ?>
      
    " />
    <span  style="text-shadow:<?php echo isset($profile['color']) ? $profile['color'] : 'black'; ?> -.95em 0em 4.5em">
      <small><?php echo $profile['name']; ?></small>
    </span>
</a>

<?php if($profile['restricted'] != TRUE): ?>

  <div class="modal profile-open fade" id="profile<?php echo $profile['spec']; if(isset($_SESSION['user'])){echo $dat;} ?>" tabindex="-1" role="dialog" aria-labelledby="profileLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
<?php if(!isset($_SESSION['user'])): ?>

        <div class="modal-header">

          <?php if(isset($_SESSION['retrieval'])): ?>
            <small class="text-muted"><i class="fas fa-lock"></i></small>
          <?php else: ?>
          <nav>
            <div class="nav" id="nav-tab" role="tablist">
              <a class="nav-item nav-link <?php if (!isset($_SESSION['signuperror'])){echo 'active';} ?>" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">Login</a>
              <a class="nav-item nav-link  <?php if (isset($_SESSION['signuperror'])){echo 'active';} ?>" id="Sign-Up-tab" data-toggle="tab" href="#Sign-Up" role="tab" aria-controls="Sign-Up" aria-selected="false">Sign-Up 
                <small class="text-muted">(New User)</small>
              </a>
            </div>
          </nav>
          <?php endif; ?>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <?php
            $errorKeys = ['signuperror', 'loginerror'];
                      
            foreach ($errorKeys as $errorKey) {
                if (isset($_SESSION[$errorKey]['fatal'])) {
                    echo "<h5 class='col-12 text-center text-muted'>{$_SESSION[$errorKey]['fatal']}</h5>";
                }
            }
          ?>
          <?php if(isset($_SESSION['signinalert']['proceed']) && $_SESSION['signinalert']['proceed'] == 'verify'): ?>
              <div class="firstlink mb-2">
                <small class="mx-auto text-center">Your Profile has been created</small>
                <span class="mx-auto text-center">Click the link in your email to verify</span>
              </div>
          <?php endif; ?>
          <div class="tab-content" id="nav-tabContent">

              <?php if(isset($_SESSION['retrieval']) && $_SESSION['retrieval']['status'] == 'valid'): ?>
                <?php $_SESSION['saved_retrieval'] = $_SESSION['retrieval']; ?>
                <form action="action.php" method="POST">
                  <h1 class="h4 mb-3 font-weight-normal">Password Reset for: </h1>

                  <input autocomplete="false" name="email" disabled value="<?= $_SESSION['retrieval']['values']['email'] ?>" id="inputPassword0" class="form-control" />
                  <small>
                    Not your Email Adress? <i class="text-muted">Refresh the page and attempt logging in again with your email</i>
                  </small>

                  <label for="inputPassword" class="sr-only">Enter New Password</label>
                  <input autocomplete="false" name="pass" type="password" id="inputPassword" class="password <?php if(isset($_SESSION['retrieval']['error'])){echo 'error-input';} ?> form-control" placeholder="Enter New Password" required />
                  <div class="toggle-visibility" id="visibility" style="cursor:pointer"><i class="fas fa-eye-slash"></i></div>
                  
                  <label for="inputPassword2" class="sr-only">Re-enter new Password</label>
                  <input autocomplete="false" name="pass2" type="password" id="inputPassword2" class="password <?php if(isset($_SESSION['retrieval']['error'])){echo 'error-input';} ?> form-control" placeholder="Re-enter New Password" required />
                  <div class="toggle-visibility" id="visibility2" style="cursor:pointer"><i class="fas fa-eye-slash"></i></div>
                  <?php if(isset($_SESSION['retrieval']['error'])){echo '<span class="error-message display-block mb-2">' . $_SESSION['retrieval']['error'] . '</span>';} ?>
                  
                  <div class="mb-3"></div>
                  <button name="pass_change" class="btn btn-lg btn-primary btn-block" type="submit">Change Password</button>
                </form>
              <?php elseif(isset($_SESSION['retrieval']) && $_SESSION['retrieval']['status'] == 'invalid'): ?>
                <h4><?= $_SESSION['retrieval']['message'] ?></h4>
                <small class="text-muted">(Refresh the page to proceed to Login)</small>
              <?php else: ?>

                <div class="tab-pane fade <?php if (!isset($_SESSION['signuperror'])){echo 'show active';} ?> " id="login" role="tabpanel" aria-labelledby="login-tab">

                  <form action="action.php" method="POST">
                    <h1 class="h3 mb-3 font-weight-normal">Login</h1>
                    <?php if(isset($_SESSION['loginerror']['verification'])): ?>
                      <span><?= $_SESSION['loginerror']['verification']; ?></span>
                    <?php endif; ?>
                    <label for="email" class="sr-only">Email</label>
                    <input name="email" type="email" id="email" class="<?php if(isset($_SESSION['loginerror']['email'])){echo 'error-input';} ?> form-control" placeholder="Email"  autofocus <?php if(isset($_SESSION['loginformdata']['email'])){echo 'value="' . $_SESSION['loginformdata']['email'] . '"';} ?> required />
                    <?php if(isset($_SESSION['loginerror']['email'])){echo '<span class="error-message display-block mb-2">' . $_SESSION['loginerror']['email'] . '</span>';} ?>
                    <label for="inputPassword" class="sr-only">Password</label>
                    <input autocomplete="false" name="pass" type="password" id="inputPassword" class="password <?php if(isset($_SESSION['loginerror']['pass'])){echo 'error-input';} ?> form-control" placeholder="Password"  <?php if(isset($_SESSION['loginformdata']['pass'])){echo 'value="' . $_SESSION['loginformdata']['pass'] . '"';} ?> <?php if(!isset($_SESSION['loginerror']['pass'])){ echo 'required'; } ?> />
                    <div class="toggle-visibility" id="visibility" style="cursor:pointer"><i class="fas fa-eye-slash"></i></div>
                    <?php if(isset($_SESSION['loginerror']['pass'])): ?>
                      <div class="mb-3">
                        <span class="error-message display-block mb-2"><?= $_SESSION['loginerror']['pass'] ?></span>
                        <input type="submit" name="reset" class="btn btn-outline-dark" value="Forgot Password?" />
                        <!-- Since the email is already in the database, we can just send a link to reset the password -->
                      </div>
                      <?php else: ?>
                        <div class="mb-3"></div>
                        <?php endif; ?>
                    <button name="login" class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
                  </form>

                </div>
                
                <div class="tab-pane fade <?php if (isset($_SESSION['signuperror'])){echo 'show active';} ?>" id="Sign-Up" role="tabpanel" aria-labelledby="Sign-Up-tab">
                  <form action="action.php" method="POST">
                    <h1 class="h3 mb-3 font-weight-normal">Sign Up</h1>
                    <label for="username" class="sr-only">Username</label>
                    <input name="username" type="text" id="username" class="<?php if(isset($_SESSION['signuperror']['username'])){echo 'error-input';} ?> form-control" placeholder="Username (16)"  autofocus <?php if(isset($_SESSION['signupformdata']['username'])){echo 'value="' . $_SESSION['signupformdata']['username'] . '"';} ?> required />
                    <?php if(isset($_SESSION['signuperror']['username'])){echo '<span class="error-message display-block mb-2">' . $_SESSION['signuperror']['username'] . '</span>';} ?>
                    <label for="birthday" class="sr-only">Birthday</label>
                    <input name="dob" type="date" id="birthday" class="<?php if(isset($_SESSION['signuperror']['dob'])){echo 'error-input';} ?> form-control" <?php if(isset($_SESSION['signupformdata']['dob'])){echo 'value="' . $_SESSION['signupformdata']['dob'] . '"';} ?> required />
                    <?php if(isset($_SESSION['signuperror']['dob'])){echo '<span class="error-message display-block mb-2">' . $_SESSION['signuperror']['dob'] . '</span>';} ?>
                    <label for="email2" class="sr-only">Email</label>
                    <input name="email" type="email" id="email2" class="<?php if(isset($_SESSION['signuperror']['email'])){echo 'error-input';} ?> form-control" placeholder="Email"  autofocus <?php if(isset($_SESSION['signupformdata']['email'])){echo 'value="' . $_SESSION['signupformdata']['email'] . '"';} ?> required />
                    <?php if(isset($_SESSION['signuperror']['email'])){echo '<span class="error-message display-block mb-2">' . $_SESSION['signuperror']['email'] . '</span>';} ?>
                    <label for="inputPassword2" class="sr-only">Password</label>
                    <input autocomplete="false" name="pass" type="password" id="inputPassword2" class="password <?php if(isset($_SESSION['signuperror']['pass'])){echo 'error-input';} ?> form-control" placeholder="Password"  <?php if(isset($_SESSION['signupformdata']['pass'])){echo 'value="' . $_SESSION['signupformdata']['pass'] . '"';} ?> required />
                    <div class="toggle-visibility" id="visibility2" style="cursor:pointer"><i class="fas fa-eye-slash"></i></div>
                    <?php if(isset($_SESSION['signuperror']['pass'])){echo '<span class="error-message display-block mb-2">' . $_SESSION['signuperror']['pass'] . '</span>';} ?>
                    <div class="mb-3">
                    </div>
                    <button name="signup" class="btn btn-lg btn-primary btn-block" type="submit">Sign Up</button>
                  </form>
                </div>

              <?php endif; ?>

          </div>
        </div>

        <?php  unset($_SESSION['signinalert']['proceed']); ?>


        <div class="modal-footer">
          <b class="text-muted">LLYNE Line &copy; <?php echo date('Y'); ?></b>
        </div>
        
<?php else: ?>
        <div class="modal-header">
     <small class="position-absolute">
          <?php
            // Check if profile is active (for users) or creator (for groups)
            if (isset($profile['active']) || isset($profile['creator'])) {
                if (isset($profile['active'])) {
                    // For users, check if it's the current user and if online
                    if ($profile['id'] == $USER) {
                        $user_serve = 'user/' . $USER;
                        $sql = "SELECT connect_id FROM connect_id WHERE service_id_url = ?";
                        $stmt = $con->prepare($sql);
                        $stmt->bind_param('s', $user_serve);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $theConnID = $result->fetch_assoc();
                        $label = "Profile";
          ?>
                        <?= $label ?> Code
                        <br>
                        <input type="text" class="d-none" id="urlcopy<?= $profile['spec'].$dat ?>" value="http://<?= $_SERVER['HTTP_HOST'] . '/' . $theConnID['connect_id'] ?>" readonly />
                        <div class="btn" data-toggle="popover" data-placement="top" data-trigger="hover" onclick="openQRCodeModal('urlcopy<?= $profile['spec'].$dat ?>', '<?= $profile['spec'] ?>', '<?= $dat ?>')">
                          <i class="fas fa-qrcode fa-sm"></i>
                        </div>
                        <!-- QR Code modal -->
                        <div class="QRCodeModal" id="qrcodeModal<?= $profile['spec'].$dat ?>" style="display:none;">
                          <div id="qrcode<?= $profile['spec'].$dat ?>" style="margin-bottom: 20px;" class="hidden-input"></div> <!-- QR Code Display -->
                          <img id="final-image-display<?= $profile['spec'].$dat ?>" alt="Final Image with QR" style="display:none; max-width: 750px; width: 100%; height: auto;" />
                          <button disabled class="btn col-12" data-clipboard-action="copy" data-clipboard-target="#final-image-display<?= $profile['spec'].$dat ?>" data-toggle="popover" data-placement="top" data-content="Tap to Share your <?= $label ?> URL" data-trigger="hover">
                            <i class="fas fa-link fa-sm"></i> Tap to Share!
                          </button>
                        </div>

          <?php
                    } else {
                        // Show online/offline status for other users
                        if (date($profile['active']) == date('r')) {
                            echo '(green dot) online';
                        } else {
                            echo 'Last Seen: <br>' . datemodifier($profile['active']);
                        }
                    }
                } elseif (isset($profile['creator'])) {
                    // Group logic: check if the user is admin
                    $groupID = $profile['id'];
                    $sql = "SELECT status FROM chat_rel WHERE chat_id = ? AND user_id = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param('ii', $groupID, $USER);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $query = $result->fetch_assoc();
                    $isAdmin = ($query['status'] == '1');
                
                    if ($isAdmin) {
                        // Admin group QR code logic
                        $user_serve = 'chat/' . $profile['id'];
                        $sql = "SELECT connect_id FROM connect_id WHERE service_id_url = ?";
                        $stmt = $con->prepare($sql);
                        $stmt->bind_param('s', $user_serve);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $theConnID = $result->fetch_assoc();
                        $label = "Group";
          ?>
                        <?= $label ?> Code
                        <br>
                        <input type="text" class="d-none" id="urlcopy<?= $profile['spec'].$dat ?>" value="https://<?= $_SERVER['HTTP_HOST'] . '/' . $theConnID['connect_id'] ?>" readonly />
                        <div class="btn" data-toggle="popover" data-placement="top" data-trigger="hover"  onclick="openQRCodeModal('urlcopy<?= $profile['spec'].$dat ?>', '<?= $profile['spec'] ?>', '<?= $dat ?>')">
                          <i class="fas fa-qrcode fa-sm"></i>
                        </div>
                        <!-- QR Code modal -->                        
                        <div id="qrcodeModal<?= $profile['spec'].$dat ?>" style="display:none;">
                          <div id="qrcode<?= $profile['spec'].$dat ?>" style="margin-bottom: 20px;" class="hidden-input"></div> <!-- QR Code Display -->
                          <img id="final-image-display<?= $profile['spec'].$dat ?>" alt="Final Image with QR" style="display:none; max-width: 750px; width: 100%; height: auto;" />
                          <button disabled class="btn col-12" data-clipboard-action="copy" data-clipboard-target="#final-image-display<?= $profile['spec'].$dat ?>" data-toggle="popover" data-placement="top" data-content="Tap to Share your <?= $label ?> URL" data-trigger="hover">
                            <i class="fas fa-link fa-sm"></i> Tap to Share!
                          </button>
                        </div>

          <?php
                    }
                }
            }
          ?>
        </small>



          
          <div class="holder position-relative mx-auto">
            <?php if($profile['photo'] != NULL): ?>
              <a data-fancybox="profile-photo<?php echo $profile['spec'] . rand(); ?>" href="<?php echo PROFILE_IMAGE . $profile['photo']; ?>"  class="large_photo mx-auto profile-pic" data-options={"class" : "profile-view"} style="background-image: url('<?php echo PROFILE_IMAGE . $profile['photo']; ?>');"></a>
            <?php else: ?>
              <img class="large_photo mx-auto" style="background-image:url(<?php if(isset($profile['active'])){echo '../img/idle_profile.png';} elseif(isset($profile['creator'])){echo '../img/idle_group.png';}?>);" />
            <?php endif; ?>
            <span class="row mx-auto name"><?php echo $profile['name']?></span>
          </div>

        </div>
        <div class="dropdown options position-absolute">
          <i class="fas fa-ellipsis-v dropdown-toggle" id="profile<?php echo $profile['spec'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></i>
          <?php
            if(isset($profile['active']) && $profile['id'] != $USER){
              if((isset($nChecked2) && $nChecked2 >= '1') || $unopendPost>='1') {
                $notif = TRUE;
                include('badge.php.inc');
              }
            }
          ?>
          <div class="dropdown-menu" aria-labelledby="profile<?php echo $profile['spec'] ?>">
            <?php if(isset($profile['dob']) && $profile['id'] == $USER): ?>
              <a data-dismiss="modal" data-fancybox="profileEdit<?php echo $profile['spec'].$dat ?>" class="dropdown-item quickChat" data-type="ajax" href="profileEdit.php?id=<?php echo $profile['id'] * $_SESSION['safe']; ?>">
                Settings   <i class="fas fa-wrench fa-sm text-muted"></i>
              </a>
            <?php elseif(isset($_SESSION['friends']) && (isset($profile['dob']) && array_key_exists($profile['id'], $_SESSION['friends']) && !isset($_GET['post']))): ?>
              <a data-dismiss="modal" data-fancybox="posts<?php echo $profile['spec'].$dat ?>" class="dropdown-item quickChat" data-type="ajax" href="postPage.php?id=<?php echo $profile['id'] * $_SESSION['safe']; ?>">
                View All Posts
                <?php
                  if($unopendPost>='1') {
                    $notif = TRUE;
                    include('badge.php.inc');
                  }
                ?>
              </a>
            <?php endif; ?>
            <?php if(isset($_SESSION['friends']) && (isset($profile['dob']) && array_key_exists($profile['id'], $_SESSION['friends']) && isset($profile['dob']))): ?>
              <?php
                if(isset($nChecked2) && $nChecked2>='1') {
                  $notif = TRUE;
                }else{
                  $notif = FALSE;
                }
                $fancyLink = $chat13 * $_SESSION['safe'];
                $resourceControl = $profile['spec'].$dat;
                  echo <<<MULTI
                        <a data-dismiss="modal" data-fancybox="DM$resourceControl" class="dropdown-item quickChat" data-type="ajax" href="chat.php?id=$fancyLink">
                          Direct Message 
                        MULTI;
                        include('badge.php.inc');
                  echo <<<MULTI
                        </a>
                        MULTI;
              ?>
              <a class="dropdown-item">Disconnect</a>
            <?php endif; ?>
            <?php if(isset($profile['creator'])): ?>
            <?php if($isAdmin == TRUE): ?>
              <a data-dismiss="modal" data-fancybox="profileEdit<?php echo $profile['spec'].$dat ?>" class="dropdown-item quickChat" data-type="ajax" href="groupEdit.php?id=<?php echo $profile['id'] * $_SESSION['safe']; ?>">
                Edit Group Profile   <i class="fas fa-wrench fa-sm text-muted"></i>
              </a>
            <?php endif; ?>
              <a class="dropdown-item" href="#">
                Exit Group
              </a>
            <?php endif; ?>
            <?php if(isset($profile['dob']) && $profile['id'] != $USER): ?>
              <a class="dropdown-item" href="#">Restrict User</a> 
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-body mx-auto row">
          <div>
            <?php if(isset($profile['active'])): ?>
                <?php
                  $id = $profile['id'];
                  $sql = "SELECT id FROM users_rel WHERE  (user_id_1 = '$id' || user_id_2 = '$id') && status = '2' && restriction_id IS NULL";
                  $connections = mysqli_fetch_assoc(sendQuery($con, $sql));
                  $profile['connections'] = mysqli_affected_rows($con);
                ?>
                <small><?php echo mysqli_affected_rows($con); ?> | <i class="fas fa-link"></i></small> <br>

            <?php elseif(isset($profile['creator'])): ?>
            <?php
              $id = $profile['id'];
              $sql = "SELECT id FROM chat_rel WHERE  chat_id = '$id' && (status = '2' || status = '1')";
              $connections = mysqli_fetch_assoc(sendQuery($con, $sql));
            ?>
                <small><?php echo mysqli_affected_rows($con); ?> | <i class="fas fa-users"></i></small>
            
            <?php endif; ?>
          </div>

            <div class="row mx-auto">
              <?php if(isset($profile['creator'])): ?>
                <?php
                  $creator = $profile['creator'];
                  $sql = "SELECT username, dob FROM users WHERE id = '$creator'";
                  $creator_search = mysqli_fetch_assoc(sendQuery($con, $sql));
                  $query['dob'] = $creator_search['dob'];
                  include('zodiac_calculator.php.inc');
                  $creatorname = $creator_search['username']. $signinfo['sign-code'];
                //   unset($signinfo);
                ?>
                  <small><?php echo 'Created by '; if($profile['creator'] == $USER){echo 'you';}else{echo $creatorname; }?>, <?php echo datemodifier($profile['created']); ?></small>
              <?php endif; ?>
            <p class="row">
                <pre>
<?php
$max='75';
echo '<pre>' . substr($profile['bio'], 0, $max) . '</pre>';
echo '<i style="white-space:pre;width:100%;font-size:smaller;text-align:right!important;bottom:0"><small> </small></i>';
                if(strlen($profile['bio']) >= $max) {
                    $currentLength = $max;
                    $specId = md5($profile['id'] . $currentLength . $dat);
                    echo <<<MULTI
                    <i target="$specId" style="white-space:pre;width:100%;font-size:smaller;text-align:right!important;bottom:0" class="read_more"><small>...read more</small></i>
                    MULTI;
                    while ($currentLength <= strlen($profile['bio'])) {
                        $newLen = $currentLength + $max;
                        $tip = md5($profile['id'] . $currentLength . $dat);
                        $statement = '<pre style="display:none !important" id="'.$tip.'">' . substr($profile['bio'], $currentLength, $max) . '</pre>';
                        $currentLength += $max;
                        $target = md5($profile['id'] . $currentLength . $dat);
                        if($newLen <= strlen($profile['bio'])) {
                            $statement .= <<<MULTI
                            <i target="$target" style="display:none;white-space:pre;width:100%;font-size:smaller;text-align:right!important;bottom:0" class="read_more $tip"><small>...read more</small></i>
                            MULTI;
                        }
                        echo $statement;
                    }
                }?></pre>
            </p>
            </div>

          <?php if(isset($profile['active']) && $profile['id'] != $USER): ?>
                    <?php
                        $id = $profile['id'];
                        $sql = "SELECT chat_id, status, user_id FROM chat_rel JOIN chat ON chat_rel.chat_id = chat.id 
                                WHERE chat_type = '2' && (user_id = '$id' || user_id = '$USER')";
                        $userGroups2 = sendQuery($con, $sql);
                        if(mysqli_affected_rows($con) == true){
                            $simChat2 = [];
                            foreach($userGroups2 as $group2){
                                if(!in_array($group2['chat_id'], $simChat2)){
                                    array_push($simChat2, $group2['chat_id']);
                                }
                            }

                            $corChat2 = [];

                            foreach ($simChat2 as $chatty) {
                                $corChat2[$chatty] = [];
                                foreach ($userGroups2 as $group2) {
                                    if($group2['chat_id'] == $chatty){
                                        array_push($corChat2[$chatty], $group2['user_id']);
                                        // Halle Berry
                                        // Anna Doip
                                        // Meghan Good
                                        // Zoey Solvana
                                        // Khelani
                                        // Chilombo
                                    }
                                }
                            }
                            foreach ($corChat2 as $chat => $val) {
                                if(in_array($id, $val) && in_array($USER, $val)){
                                    $sql = "SELECT * FROM chat where id = '$chat'";
                                    $groupChat2[] = mysqli_fetch_assoc(sendQuery($con, $sql));
                                }
                            }
                        }
                    ?>
                    <?php if(isset($groupChat2) && ($groupChat2 != NULL)): $n = '1'; ?>
                        <?php foreach($groupChat2 as $key): ?>
                            <?php if($n <= '5' && (!isset($thischat) || ($thischat != $key['id']))): ?>
                            <?php
                              if($n == '1'){
                                echo '<p class="row mb-0 align"><small>Groups in Common:</small></p>';
                              }
                            ?>
                            <a data-type="ajax" href="chat.php?id=<?php echo $key['id'] * $_SESSION['safe']; ?>"  data-dismiss="modal" data-fancybox="chat<?php echo $profile['spec'].$dat ?>"  class="row quickChat finesse cool">
                                <div class="profile profile_left">
                                  <?php
                                  $groups = $key['id'];
                                  $sql = "SELECT * FROM chat WHERE id = '$groups'";
                                  $groupQuery2 = mysqli_fetch_assoc(sendQuery($con, $sql));
                                  // dump($groupQuery2);
                                  echo '<div style="position:relative">';
                                      if($n <= '5'){
                                        $name3 = $groupQuery2['name'];
                                          if($groupQuery2['icon'] == NULL){
                                              $imageUrl = '../img/idle_group.png';
                                          } else {
                                              $imageUrl = PROFILE_IMAGE.$groupQuery2['icon'];
                                          }
                                          
                                          echo <<<MULTI
                                          <div class="small_photo" style="diaplay:flex; flex-direction:row">
                                              <img style="background-image:url('$imageUrl')">
                                              <span>
                                                <small> $name3 </small>
                                              </span>
                                          </div>
                                          MULTI;
                                      }
                                      $n++;
                                      echo '</div>';
                                        
                                  ?>
                                </div>
                                <span style="position:absolute; right:.75em">
                                  <i class="fas fa-comments fa-sm"></i>
                                  <small style="position:absolute; bottom:.75em; right:-.45em">
                                  <?php
                                  $nChecked = '0';
                                  $thischat3 = $groupQuery2['id'];
                                  $sql = "SELECT id FROM messages WHERE chat_id = $thischat3 && user_id != $USER";
                                  $message_last2 = sendQuery($con, $sql);
                                  foreach ($message_last2 as $dem) {
                                    $message_url_id2 = 'message/' . $dem['id'];
                                    $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$message_url_id2' && user_id = '$USER'";
                                    $open2 = sendQuery($con, $sql);
                                    if(mysqli_affected_rows($con) != TRUE) {
                                      $nChecked++;
                                    }
                                  }
                                  if($nChecked>='1') {
                                    $notif = TRUE;
                                  }else{
                                    $notif = FALSE;
                                  }
                                  include('badge.php.inc');
                                  // // if(isset($posts)){
                                  // //   include('..\..\assets\part\badge.php.inc');
                                  // // }else{
                                  // //   include('..\assets\part\badge.php.inc');
                                  // // }

                                  // $scriptName = explode( '/', $_SERVER['SCRIPT_NAME']);
                                  // // $p = array_key_last($scriptName);
                                  // // $script = $scriptName[$p];
                                  // $script = end($scriptName);
                                  
                                  // if($_SERVER['SCRIPT_NAME'] == ('/load/'.$script)){
                                  //   include('..\..\assets\part\badge.php.inc');
                                  // }else{
                                  //   include('..\assets\part\badge.php.inc');
                                  // }
                                  ?>
                                  </small>
                                </span>
                                <i><small><pre><?php echo substr($groupQuery2['description'], 0, 27); if(strlen($groupQuery2['description']) >= '27'){echo '...';} //marquee ?></pre></small></i>
                                
                            </a>
                            <?php endif; ?>
                        <?php $n++; endforeach; ?>
                        <?php
                        if($n >= '6'){
                            echo '<i>+ ' . ($n - 6) . 'more</i>';
                        }
                        ?>
                    
                    <?php endif; ?>
              
          <?php elseif(isset($profile['creator'])): ?>
            
                  <div class="row position-relative">
                    <?php
                    $id = $profile['id'];
                    $sql = "SELECT * from users JOIN chat_rel ON users.id = chat_rel.user_id WHERE chat_rel.chat_id = '$id' && ( chat_rel.status = '1' || chat_rel.status = '2')";
                    $chatMembers2 = sendQuery($con, $sql);
                        if(mysqli_affected_rows($con) == true){
                            echo '<small class="text-muted m_group_listing">Group Members:</small>';
                            $n = 1;
                            foreach($chatMembers2 as $groupies) {
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
                                    <div class="profile" style="margin-left: -.35em;">
                                        <div class="small_photo">
                                            <img style="background-image:url('$imageUrl')">
                                        </div>
                                    </div>
                                    MULTI;
                                    $query['dob'] = $groupies['dob'];
                                    include('zodiac_calculator.php.inc');
                                    echo '<small
                                            style="
                                                color:gold;
                                                position:absolute;
                                                right:-.05em;
                                                top:0;
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
                    $chatList2 = [];
                    // foreach ($chatMembers2 as $goodies) {
                    //   if ($goodies['user_id'] == $USER){
                    //     if($goodies['status'] == '1'){
                    //       $isAdmin = TRUE;
                    //     }else{
                    //       $isAdmin = FALSE;
                    //     }
                    //   break;
                    //   }
                    // }
                    if($isAdmin == TRUE){
                        foreach ($chatMembers2 as $groupies) {
                            $chatList2[$n] = [];
                            if(!in_array($groupies['user_id'], $chatList2[$n])){
                                $a = array('user_id', 'status', 'privacy');
                                $b = array($groupies['user_id'], $groupies['status'], $groupies['private']);
                                $chatList2[$n] = array_combine($a, $b);
                            }else{
                                unset($chatList2[$n]);
                            }
                            $n++;
                        }
                    }else{
                        foreach ($chatMembers2 as $groupies) {
                            $chatList2[$n] = [];
                            if($groupies['status'] == '1'){
                                if(!in_array($groupies['user_id'], $chatList2[$n])){
                                    $a = array('user_id', 'status');
                                    $b = array($groupies['user_id'], $groupies['status']);
                                    $chatList2[$n] = array_combine($a, $b);
                                }
                            }elseif(array_key_exists($groupies['user_id'], $_SESSION['friends']) || $groupies['user_id'] == $USER) {
                                if(!in_array($groupies['user_id'], $chatList2[$n])){
                                    $a = array('user_id', 'status');
                                    $b = array($groupies['user_id'], $groupies['status']);
                                    $chatList2[$n] = array_combine($a, $b);
                                }
                            }else{
                                unset($chatList2[$n]);
                            }
                            $n++;
                        }
                    }
                    if($chatList2 != NULL) {
                        usort($chatList2, sortByAdmin('status'));
                        $u = '1';
                        $max = '5';
                    }
                    ?>
                    <?php foreach($chatList2 as $guys => $values): $u++;?>
                        <?php if($guys <= $max): ?>
                        <div class="row finesse" <?php if($values['user_id'] == $USER){echo 'style="order:2"';}else{echo 'style="order:1"';} ?>>
                            <div class="profile profile_left">
                                <?php
                                $users = $values['user_id'];
                                $sql = "SELECT * FROM users WHERE id = '$users'";
                                $userQuery = mysqli_fetch_assoc(sendQuery($con, $sql));
                                // dump($userQuery);
                                echo '<div style="position:relative">';
                                      if($n <= '5'){
                                        $name2 = $userQuery['username'];
                                          if($userQuery['avatar'] == NULL){
                                              $imageUrl = '../img/idle_profile.png';
                                          } else {
                                              $imageUrl = PROFILE_IMAGE.$userQuery['avatar'];
                                          }
                                          
                                          echo <<<MULTI
                                          <div class="small_photo" style="diaplay:flex; flex-direction:row">
                                              <img style="background-image:url('$imageUrl')">
                                              <span>
                                                <small> $name2 </small>
                                              </span>
                                          </div>
                                          MULTI;
                                      }
                                      $n++;
                                      echo '</div>';
                                ?>
                                
                                <div class="dropright options position-absolute" style="top:-.5em">
                                  <i class="fas fa-ellipsis-v fa-sm dropdown-toggle" id="profile<?php echo $profile['spec'] . md5($name2); if(isset($_SESSION['user'])){echo $dat;}  ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></i>
                                  <div class="dropdown-menu" aria-labelledby="profile<?php echo $profile['spec'] . md5($name2); if(isset($_SESSION['user'])){echo $dat;}  ?>">
                                  <?php if($isAdmin == TRUE && $values['user_id'] != $USER){ ?>
                                      <?php if($values['status'] == '1'): ?>
                                      <a class="dropdown-item" href="#chats">Remove <?php echo $name2; ?> from Admins</a>
                                      <?php else: ?>
                                      <a class="dropdown-item" href="#chats">Make <?php echo $name2; ?> an Admin</a>
                                      <?php endif; ?>
                                      <a class="dropdown-item" href="#chats">Remove <?php echo $name2; ?> from Group</a>
                                  <?php } ?>


                                  <?php if($values['user_id'] == $USER): ?>

                                    <a class="dropdown-item" href="#chats">Exit Group</a>

                                  <?php else: ?>

                                    <?php if(isset($_SESSION['friends'])): ?>

                                      <?php if(array_key_exists($values['user_id'], $_SESSION['friends'])): ?>

                                        <?php
                                        $preChat = $_SESSION['friends'][$values['user_id']]['chat_id'];
                                        // dump($preChat);
                                        $nChecked4 = 0;

                                        $sql = "SELECT id FROM messages WHERE chat_id = $preChat && user_id != $USER ORDER BY id DESC LIMIT 5";
                                        $message_last3 = sendQuery($con, $sql);
                                        foreach ($message_last3 as $dem) {
                                          $message_url_id3 = 'message/' . $dem['id'];
                                          $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$message_url_id3' && user_id = '$USER'";
                                          $open3 = sendQuery($con, $sql);
                                          if(mysqli_affected_rows($con) != TRUE) {
                                            $nChecked4++;
                                          }
                                        }

                                        if(isset($nChecked4) && $nChecked4>='1') {
                                          $notif = TRUE;
                                        }else{
                                          $notif = FALSE;
                                        }

                                        $fancyLink = $preChat * $_SESSION['safe'];

                                        $resourceControl = md5($profile['spec'].$dat);
                                          echo <<<MULTI
                                                <a data-dismiss="modal" data-fancybox="DM$resourceControl" class="dropdown-item quickChat" data-type="ajax" href="chat.php?id=$fancyLink">
                                                  Send $name2 Direct Message 
                                                MULTI;
                                                include('badge.php.inc');
                                          echo <<<MULTI
                                                </a>
                                                MULTI;
                                        ?>

                                      <?php else: ?>
                                      <?php
                                        $otherUserHere = $values['user_id'];
                                        $sql = "SELECT restriction_id FROM users_rel WHERE (user_id_1 = '$USER' && user_id_2 = '$otherUserHere') || (user_id_2 = '$USER' && user_id_1 = '$otherUserHere')";
                                        $restrictionStat = mysqli_fetch_assoc(sendQuery($con, $sql));
                                      ?>

                                      <?php if($restrictionStat == FALSE && ($profile['private'] == FALSE || $isAdmin == TRUE || $values['status'] == '1')): ?>
                                        <a class="dropdown-item" href="#chats">Connect with <?php echo $name2; ?></a>
                                      <?php else: ?>
                                        <?php if($restrictionStat['restriction_id'] == $USER): ?>
                                          <a class="dropdown-item">You have Restricted this User</a>
                                        <?php elseif($restrictionStat['restriction_id'] == $otherUserHere): ?>
                                          <a class="dropdown-item">You are Restricted by this User</a>
                                        <?php endif; ?>
                                      <?php endif; ?>

                                      <?php endif; ?>
                                    <?php endif; ?>

                                  <?php endif; ?>
                                  </div>
                                </div>
                            </div>
                            <i style="bottom:.25em; font-size:smaller">
                                <?php
                                    if($values['status'] == '1'){
                                        echo '
                                            <i class="fas fa-crown fa-sm" 
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
                                            <i class="fas fa-link fa-sm" 
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
                      <?php foreach($chatList2 as $guys => $values): ?>
                        <?php if($guys >= ($max + 1)): ?>
                            <div class="row finesse bg-white">
                                <div class="profile profile_left">
                                    <?php $user_id = $values['user_id']; dump($user_id) ?>
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
                    if($chatList2 != NULL) {
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
            <br>
        </div>
        <div class="modal-footer">
            <?php
              if(isset($relationships) && !isset($connected)){
                if($relationship['status'] == '1' && $relationship['user_id_1'] == $USER){
                  echo <<<MULTI
                        <div class="actionkeys">
                          <a href="" class="bg-warning" data-toggle="popover" data-placement="top" data-content="cancel request" data-trigger="hover">
                              <i class="far fa-stop-circle"></i>
                          </a>
                        </div>
                        MULTI;
                }elseif($relationship['status'] == '1' && $relationship['user_id_1'] != $USER){
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
                }
              }else{
                if(isset($profile['active'])){
                  if($profile['id'] == $USER){
                    echo 'Your Profile';
                  }elseif(isset($_SESSION['friends'])){
                    if(array_key_exists($profile['id'], $_SESSION['friends'])){
                      echo 'You are Connected With this User';
                    }else {
                      if(isset($ischat)){
                        $usedId = $profile['id'];
                        $sql = "SELECT anon FROM chat WHERE id = '$thischat'";
                        $render = mysqli_fetch_assoc(sendQuery($con, $sql));
                        if($render['anon'] == TRUE){
                          $sql = "SELECT status FROM chat_rel WHERE user_id = '$USER' && chat_id = '$thischat'";
                          $isYouAdmin = mysqli_fetch_assoc(sendQuery($con, $sql));
                          if($isYouAdmin['status'] == '1'){
                            echo <<<MULTI
                            <div class="actionkeys">
                            <a href="action.php/?name=user&id= $usedId " class="bg-primary" data-toggle="popover" data-placement="top" data-content="connect now" data-trigger="hover">
                                <i class="fas fa-link"></i>
                            </a>
                            </div>
                            MULTI;
                          }else{
                            echo '<i>LLYNE LINE</i>';
                          }
                        }else{
                          if($profile['private'] == TRUE){
                            echo '<i>LLYNE LINE</i>';
                          }else{
                            // Check if there was any previous connection  request
                            // $usedId = $profile['id'];
                            if(isset($_SESSION['pending']) && in_array($usedId, $_SESSION['pending'])){
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
                            }elseif(isset($_SESSION['requests']) && in_array($usedId, $_SESSION['requests'])){
                              echo <<<MULTI
                              <div class="actionkeys">
                                  <a href="" class="bg-warning" data-toggle="popover" data-placement="top" data-content="cancel request" data-trigger="hover">
                                      <i class="far fa-stop-circle"></i>
                                  </a>
                              </div>
                              MULTI;
                            }elseif(isset($_SESSION['friends']) && !array_key_exists($usedId, $_SESSION['friends'])){
                              echo <<<MULTI
                              <div class="actionkeys">
                              <a href="action.php/?name=user&id= $usedId " class="bg-primary" data-toggle="popover" data-placement="top" data-content="connect now" data-trigger="hover">
                                  <i class="fas fa-link"></i>
                              </a>
                              </div>
                              MULTI;
                            }else{
                              echo '<i>LLYNE LINE</i>';
                            }
                          }
                        }
                      }
                    }
                  }else{
                    // Will create availability for mentioned contacts on next update
                    echo '<i>LLYNE LINE</i>';
                  }
                }elseif(isset($profile['creator'])){
                  echo '<i>You are a Member of this Group</i>';
                }
              }
            ?>
        </div>
<?php endif; ?>
      </div>
    </div>
  </div>

<?php endif; ?>

<?php endif; ?>


<?php 
if(isset($reuseProfile)){
  $reuseProfile = [];
  $reuseProfile = $profile;
  if($profile['name_raw']){
    $reuseProfile['name'] = $profile['name_raw'];
  }
}
unset(
    $isAdmin,
    $user_id,
    $group_id, 
    $profile,
    $isRestricted,
    $group2,
    $groupChat2,
    $dat,
    $query,
    $_SESSION['url_search_type'], 
    $_SESSION['loginformdata'], 
    $_SESSION['signupformdata'], 
    $_SESSION['signinalert'], 
    $_SESSION['loginerror'], 
    $_SESSION['signuperror'],
    $_SESSION['retrieval']
  ); 

  // if(isset($_SESSION['retrieval']['values']) ?? ''){
  //   unset($_SESSION['retrieval']['values']);
  // }
?>


<script>
         
         $('[data-fancybox].quickChat').fancybox({
      protect: true,
        ajax: {
            // Object containing settings for ajax request
            settings: {
              // This helps to indicate that request comes from the modal
              // Feel free to change naming
              data: {
                fancybox: true
              }
            }
          },
          closeExisting: true,
        //   hideScrollbar: true,
          buttons: false,
          infobar: false,
          baseClass: "wide-open",
          arrows: false,
          smallBtn: false,
          touch: {
            vertical: false, // Disallow to drag content vertically
          },
      
          errorTpl: '<div class="swipe-error"><p>{{ERROR}}</p></div>',
          
        lang: "en",
        i18n: {
          en: {
            CLOSE: "Close",
            NEXT: "Next",
            PREV: "Previous",
            ERROR: "Something Went Wrong; Please check your connection. <br/> Please try again later.",
            PLAY_START: "Start slideshow",
            PLAY_STOP: "Pause slideshow",
            FULL_SCREEN: "Full screen",
            THUMBS: "Thumbnails",
            DOWNLOAD: "Download",
            SHARE: "Share",
            ZOOM: "Zoom"
          }
        },
    
        autofocus: false,
        
        // aftershow: function() {
        //     $(".chat-box").activate()
        // }
    });

$(document).ready(function() {

  $('#visibility').click(function() {
      var x = document.getElementById("inputPassword");
      var y = $("#visibility>svg");

      if(x.type === "password") {
          x.type = "text";
          $(y).removeClass("fa-eye-slash");
          $(y).addClass("fa-eye");
        }else {
          x.type = "password";
          $(y).removeClass("fa-eye");
          $(y).addClass("fa-eye-slash");
      }
  });

  $('#visibility2').click(function() {
      var z = document.getElementById("inputPassword2");
      var w = $("#visibility2>svg");

      if(z.type === "password") {
          z.type = "text";
          $(w).removeClass("fa-eye-slash");
          $(w).addClass("fa-eye");
        }else {
          z.type = "password";
          $(w).removeClass("fa-eye");
          $(w).addClass("fa-eye-slash");
      }
  });

});


</script>