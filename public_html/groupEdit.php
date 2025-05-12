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

$sql = "SELECT status FROM chat_rel WHERE chat_id = '$thischat' && user_id = '$USER'";
$query = mysqli_fetch_assoc(sendQuery($con, $sql));
if(mysqli_affected_rows($con) != TRUE || $query['status'] != '1' ){
  echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}

    
if($_SERVER['HTTP_SEC_FETCH_SITE'] != 'same-origin') {
    header('Location: ../');
}


$boombox = md5(rand() . $thischat);

// dump($thischat);

?>

<div class="open">
  

  <div class="hidden-input">
    <?php
    $reuseProfile = TRUE;
    $group_id = $thischat; include('../assets/part/profile.php.inc');
    $posHeadData = [];
    $posHeadData = $reuseProfile;
    unset($reuseProfile);
    ?>
  </div>
       
  <header>
    <a>
        <button data-fancybox-close ><i class="fas fa-chevron-circle-left"></i></button>
    </a>
    <div class="profile profile_center mt-3 mb-2">
        <h5><?php echo $posHeadData['name']; ?> <i class="fas fa-wrench fa-sm"></i></h5>
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
      </div>
    </div>
  </header>
  
  <?php dump($posHeadData); ?>

</div>




<?php endif; unset($posHeadData, $posts); ?>