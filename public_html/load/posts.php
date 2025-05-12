<?php

session_start();

require_once('..\..\assets\apache\db_con.php');
require_once('..\..\assets\apache\functions.php');
require_once('..\..\assets\apache\constants.php');

if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);
}

?>


<div class="all-posts" id="posts">
        <h1 class="container">posts</h1>

<?php if(!isset($_SESSION['user'])): ?>
    <br>
    <br>
    <br>
    <br>
    <br>
  <div class="container d-flex ">
    <div class="profile profile_center">
        <?php $user_id = NULL; include('..\..\assets\part\profile.php.inc'); ?>
    </div>
  </div>
<?php else: ?>
        <?php
        $n = '0';
        $posts = [];
        $friendList = [];
        $friendList = $_SESSION['friends'];
        if(!in_array($USER, $friendList)) {
          $friendList[$USER]['user_id'] = $USER;
        }
        foreach ($friendList as $friend => $key) {
          $thisFriend = $key['user_id'];
          $sql = "SELECT * FROM posts WHERE deleted is NULL && user_id = '$thisFriend'";
          $userPosts = sendQuery($con, $sql);
          foreach($userPosts as $posting) {
            array_push($posts, $posting);
          }
        }
        if(count($posts) == 0) {
          echo '
          <p class="container text-center">Add some friends to see posts</p>
          ';
        } else {
          usort($posts, sortByTimeOnly('created'));
        }
        echo '<div class="posts">';
        include('../posts.php');
        echo '</div>';
        unset($posts);
        ?>
        <script>var msnry = new Masonry('.posts');</script>
<?php endif; ?>


</div>

<script>
    
  $(function () {
    $('[data-toggle="popover"]').popover(),
    $('.popover-dismiss').popover({
      trigger: 'focus'
    }),
    $('.example-popover').popover({
      container: 'body'
    })
  });
  
  $.fancybox.defaults.hash = false; 
  
  $('[data-fancybox].slides').fancybox({
    // closeExisting: false,
      buttons: false,
      buttons: [
        "close"
      ],
      infobar: true,
      image: {
          preload: false
      },
      closeExisting: false,
      // hideScrollbar: true,
  });
  
  $('[data-fancybox].profile-pic').fancybox({
    buttons: false,
    buttons: [
      "close"
    ],
    baseClass: "profile-view",
    closeExisting: false,
    // hideScrollbar: true,
    infobar: false,
    arrows: false,
    smallBtn: false
  });
  
  $('[data-fancybox].wide').fancybox({
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
        closeExisting: false,
        // hideScrollbar: true,
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
      }
  });
  
  
  
  // window.onbeforeunload = function () {
  //   return "Refreshing Page might Cause you to Lose Data";
  // }
  
</script>