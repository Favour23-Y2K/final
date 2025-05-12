<?php session_start(); ?>

<?php require_once('..\assets\apache\db_con.php'); ?>
<?php require_once('..\assets\apache\functions.php'); ?>


<?php if (!isset($_GET['id'])):  header('Location: ../');?>
   <h1>This Page Cannot Be Processed</h1>
<?php elseif (isset($_GET['id'])):?>

<?php

if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);
    $thischat = $_GET['id'] / $_SESSION['safe'];
} else {
    echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}

if($thischat != $USER){
  echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}
    



if($_SERVER['HTTP_SEC_FETCH_SITE'] != 'same-origin') {
    header('Location: ../');
}


$boombox = md5(rand() . $thischat);

?>


<div class="open">
     
  <header>
      <a>
          <button data-fancybox-close ><i class="fas fa-chevron-circle-left"></i></button>
      </a>
      <div class="profile profile_center mt-3 mb-2">
          <h5>SETTINGS <i class="fas fa-wrench"></i></h5>
      </div>
      <div class="dropdown">
        <a id="profile<?php echo $boombox; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <button>
            <i class="fas fa-ellipsis-v fa-sm"></i>
          </button>
        </a>
        <div class="dropdown-menu" style="margin-top: -4em" aria-labelledby="profile<?php echo $boombox; ?>">
          <a class="dropdown-item" href="#bioData">Edit Bio Data</a> 
          <a class="dropdown-item" href="#notifications">Notifications</a>
          <a class="dropdown-item" href="#accountSettings">Account Settings</a>
          <a class="dropdown-item" href="../logout.php">Logout</a>
        </div>
      </div>
  </header>

  <div class="hidden-input">
    <?php
    $reuseProfile = TRUE;
    $user_id = $thischat; include('../assets/part/profile.php.inc');
    if($thischat == $USER){
      $reuseProfile['connections'] =  count($_SESSION['friends']);
    }
    $posHeadData = [];
    $posHeadData = $reuseProfile;
    unset($reuseProfile);
    ?>
  </div>

  
  <?php
    // dump($posHeadData);
  ?>

  <div class="body">
    <div class="container division" id="bioData">
  
      <div class="header mt-2">
        <div class="bioImage input-group">
        <?php if($posHeadData['photo'] != NULL): ?>
          <img style="background-image: url('<?php echo PROFILE_IMAGE . $posHeadData['photo']; ?>');" class="privatePostImage">
        <?php else: ?>
          <img style="background-image:url(<?php if(isset($posHeadData['active'])){echo '../img/idle_profile.png';} elseif(isset($posHeadData['creator'])){echo '../img/idle_group.png';}?>);" class="privatePostImage" />
        <?php endif; ?>
        </div>
        <div class="input-group">
          <input type="file" name="profilePicture" id="profilePicture">
          <label for="profilePicture"><small>Change Profile Photo</small></label>
        </div>
      </div>

      <div class="content p-1">
        <div class="form-row no-gutters form-group">
          <input class="form-control" value="<?php echo $posHeadData['name'] ?>" type="text" name="display_name" id="display_name" placeholder="Name CANNOT be left Blank" />
          <label class="text-muted" for="display_name"><small>Change Display Name</small></label>
        </div>
        <div class="form-row no-gutters form-group">
          <input class="form-control" value="<?php echo $posHeadData['name'] ?>" type="text" name="display_name" id="display_name" placeholder="Name CANNOT be left Blank" />
          <label class="text-muted" for="display_name"><small>To revoke old profile URL</small></label>
        </div>
        <div class="form-row no-gutters form-group">
          <textarea class="form-control" name="bio" id="bio" cols="30" rows="10" draggable="false" placeholder="Bio CANNOT be Empty" ><?php echo $posHeadData['bio'] ?></textarea>
          <label class="text-muted" for="bio"><small>Edit Bio</small></label>
        </div>
        <div class="form-row no-gutters form-group">
          <input disabled class="form-control disabled col-11" value="<?php echo $posHeadData['dob'] ?>" type="date" name="dob" id="dob" placeholder="Date of Birth" />
          <label class="text-muted" for="dob"><small>Date of Birth</small></label>
          <div class="col-1 p-2"><?php echo $posHeadData['zod'] ?></div>
        </div>
        <input type="submit" value="Save Changes" class="float-right btn btn-primary" />
        <div class="row"></div>
      </div>

    </div>


    <div class="container division" id="notifications">
      <header class="h5 title" id="notifications">Notifications</header>
      <div class="content p-1">
        <div class="input-group">
          <div class="form-control">
            <input type="checkbox" class="checkbox" name="pushNotif" id="pushNotif">
            <label for="pushNotif">Enable Push Notofication</label>
          </div>
        </div>
        <small class="form-text text-muted">(Keep this box CHECKED to Receive NOTIFICATIONS via you Browser)</small>
      </div>
    </div>


    <div class="container division" id="accountSettings">
      <header class="h5 title" id="accountSettings">Account Settings</header>
      <div class="content p-1">

        <div class="form-row no-gutters form-group">
          <input disabled class="form-control" id="email" value="<?php echo $posHeadData['email'] ?>" type="email" name="email" id="display_name" placeholder="You need to supply an Email Address" />
          <label class="text-muted" for="email"><small> Email Address </small></label>
        </div>

        <div class="form-row no-gutters form-group">
          <div class="input-group">
            <div class="form-control">
              <input type="checkbox" class="checkbox" <?php if($posHeadData['private'] == TRUE){ ?> checked <?php } ?> name="privacy" id="privacy">
              <label for="privacy">Set Account as Private</label>
            </div>
          </div>
          <small class="form-text text-muted">(When this box is CHECKED, other Users will not be able to connect with you from Groups except the Group Admins; You can always share your Profile Link for other users to connect with you)</small>
        </div>

        <div class="form-row no-gutters">
          <a style="width:100%; display:block; border:solid thin var(--light); border-radius: .3em; padding: .45em;" class="form-check-label font-weight-bolder text-muted" data-toggle="collapse" href="#restrictedAccounts" aria-expanded="false" aria-controls="restrictedAccounts">
            Restricted Accounts
            <span class="float-right">
              <i id="dropdownToggle" class="fas fa-chevron-circle-down"></i>
            </span>
            <?php
               $sql = "SELECT * FROM users_rel WHERE restriction_id = '$USER'";
               $restricted = sendQuery($con, $sql);
              //  dump(mysqli_fetch_assoc($restricted));
            ?>
          </a>
          <div class="collapse row no-gutters" id="restrictedAccounts">
            <div class="row no-gutters justify-content-start">
              <?php if(mysqli_affected_rows($con) >= 1): ?>
                <?php foreach($restricted as $ress): ?>
                  <div class="finesse col-6 m-0">
                    <div class="profile profile_left p-1" style="font-size:xx-small !important">
                      <?php
                        if($ress['user_id_1'] == $USER){
                          $user_id = $ress['user_id_2'];
                        }elseif($ress['user_id_2'] == $USER){
                          $user_id = $ress['user_id_1'];
                        }else{
                          $user_id = NULL;
                        }
                        include('../assets/part/profile.php.inc');
                      ?>
                    </div>
                    <div class="dropleft">
                      <a id="profile<?php echo $boombox.$user_id; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm"></i>
                      </a>
                      <div class="dropdown-menu" style="margin-top: -4em" aria-labelledby="profile<?php echo $boombox.$user_id; ?>">
                        <a class="dropdown-item small" href="#">Unrestrict <?php echo $name ?> </a> 
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-12 mx-auto" style="font-size:small"> <p class="text-center font-xx-small"> You have not Restricted any User. <i class="text-muted">If you have, you will see them here</i></p> </div>
              <?php endif; ?>
            </div>
          </div>
        </div>


        <div class="row no-gutters">
          <div class="col-form-label title col-sm-2 pt-0">
            <label for="passwordToggle">Change Password</label>
            <input type="checkbox" class="checkbox" name="passwordToggle" id="passwordToggle">
          </div>
          <form action="">
            <input type="text" name="email" value="<?php echo $ress['émail']; ?>" class="hidden-input" autocomplete="false" disabled>
            <fieldset id="passwordChange" disabled>
              <!-- <div class="row no-gutters"> -->
                <small id="changeinfo" style="font-size:.75em; color: var(--muted)">Check the above box to Change your Password below</small>
              <!-- </div> -->
              <div class="col-sm-10">
                <div class="form-row no-gutters input-group">
                  <input class="form-control" type="password" name="oldPassword" id="oldPassword" placeholder="••••••••••" required autocomplete="password" />
                  <label class="text-muted" for="oldPassword"><small>Old Password</small></label>
                </div>
                <div class="form-row no-gutters input-group">
                  <input class="form-control" type="password" name="newPassword" id="newPassword" placeholder="••••••••••" required autocomplete="new-password" />
                  <label class="text-muted" for="newPassword"><small>New Password</small></label>
                </div>
              </div>
              <div class="form-row no-gutters">
                <span>Forgotten password?</span><i class='text-muted'><pre> Logout and attempt login in to reset password</pre></i>
              </div>
              <input type="submit" value="Save Changes" class="float-right btn btn-primary" />
            </fieldset>
          </form>
        </div>
        
      </div>
    </div>



  </div>

</div>

<style>
  div.division {
    padding-top: 4.5em;
    margin-top: -3.5em;
    font-family: cursive;
    margin-bottom: 3.75em;
  }
  div.dropleft {
    position: absolute;
    right: .75em;
  }
  div.form-row {
    position: relative;
    margin-bottom: 1.95em;
  }
  header.title {
    width: 100%;
    border-bottom: solid thin var(--light);
    color: var(--dark);
    font-variant: small-caps;
  }
  label.text-muted{
    position: absolute;
    left: 0;
    bottom: -2.05em;
    font-size: smaller;
  }
  .form-control {
    border: solid .045em var(--light);
  }
  input.form-control {
    border-bottom: solid thin var(--dark);
  }
  textarea.form-control {
    border-left: solid thin var(--dark);
  }
</style>

<script>
  $('#restrictedAccounts').on('shown.bs.collapse', function () {
    $('#dropdownToggle').removeClass('fa-chevron-circle-down');
    $('#dropdownToggle').addClass('fa-chevron-circle-up');
  });
  $('#restrictedAccounts').on('hidden.bs.collapse', function () {
    $('#dropdownToggle').addClass('fa-chevron-circle-down');
    $('#dropdownToggle').removeClass('fa-chevron-circle-up');
  })

  var passChange = $('#passwordChange');

  $('#passwordToggle').click(function(e){
    if(this.checked){
      passChange.attr('disabled', false);
      $('#changeinfo').css('display', 'none');
    }else{
      passChange.attr('disabled', true);
      $('#changeinfo').css('display', 'block');
      // $('#passwordChange input[type = "password"]').value = null;
    }
  });

</script>



<?php endif; unset($posHeadData, $posts); ?>