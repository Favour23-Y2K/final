<?php session_start(); ?>

<?php require_once('..\assets\apache\db_con.php'); ?>
<?php require_once('..\assets\apache\functions.php'); ?>


<?php if (!isset($_GET['id'])): echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';  header('Location: ../');?>
   <h1>This Page Cannot Be Processed</h1>
<?php elseif (isset($_GET['id'])):?>

<?php

if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);
    $thischat = $_GET['id'] / $_SESSION['safe'];
} else {
  echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}

if(!array_key_exists($thischat, $_SESSION['friends']) && ($thischat != $USER)){
  echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}
    


if($_SERVER['HTTP_SEC_FETCH_SITE'] != 'same-origin') {
  echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}


$boombox = md5(rand() . $thischat);

?>

<div class="open">
     
    <header>
        <a>
            <button data-fancybox-close ><i class="fas fa-chevron-circle-left"></i></button>
        </a>
        <div class="profile profile_center mt-3 mb-2">
            <h5>LLYNE LINE</h5>
        </div>
        <div class="dropdown">
          <a id="profile<?php echo $boombox; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button>
              <i class="fas fa-ellipsis-v fa-sm"></i>
            </button>
          </a>
          <div class="dropdown-menu" style="margin-top: -4em" aria-labelledby="profile<?php echo $boombox; ?>">
            <a class="dropdown-item" href="#">Action</a> 
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a>
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

    <div class="header no-gutters row mt-2">
      <div class="bioImage row col-4">
      <?php if($posHeadData['photo'] != NULL): ?>
        <img style="background-image: url('<?php echo PROFILE_IMAGE . $posHeadData['photo']; ?>');" class="privatePostImage">
      <?php else: ?>
        <img style="background-image:url(<?php if(isset($posHeadData['active'])){echo '../img/idle_profile.png';} elseif(isset($posHeadData['creator'])){echo '../img/idle_group.png';}?>);" class="privatePostImage" />
      <?php endif; ?>
        <div class="row">
          <small>
            <?php if(isset($posHeadData['connections'])): ?>
              <?php echo $posHeadData['connections']; ?>
            <?php else: ?>
              <i class="fas fa-ban"></i>
            <?php endif; ?> 
              | <i class="fas fa-link"></i></small>
        </div>
      </div>
      <div class="col-8 bioData">
       <h5 style="text-shadow:<?php echo $posHeadData['color']; ?> -.95em 0em 4.5em"><?php echo $posHeadData['name']; ?></h5>
       <p>
         <?php echo $posHeadData['bio']; ?>
       </p>
      </div>
    </div>

    <div class="body">
      <?php
        $posts = [];
        $theUserPosting = $posHeadData['id'];
        $sql = "SELECT * FROM posts WHERE deleted is NULL && user_id = '$theUserPosting'";
        $userPosts = sendQuery($con, $sql);
        foreach($userPosts as $posting) {
          array_push($posts, $posting);
        }
        if(count($posts) == 0) {
          if($thischat == $USER){
            echo '
            <p class="container text-center">You have no Posts Yet</p>
            ';
          }else{
            echo '
            <p class="container text-center">There are no Posts Yet by this User</p>
            ';
          }
        } else {
          usort($posts, sortByTimeOnly('created'));
        }
      ?>
      <div class="Personalposts" style="margin:.25em">
      <?php $pool = '0'; foreach ($posts as $post): $post_url_id = "post/" . $post['id'];?>
        <div href="" class="post-item" id="<?php echo 'post' . $post['id'] ; ?>">
            <div class="post-content">

                <?php
                $post_url_id = 'post/' . $post['id'];
                $sql = "SELECT TRUE FROM delivered WHERE content_id_url = '$post_url_id' && user_id = '$USER'";
                $open = sendQuery($con, $sql);
                if(mysqli_affected_rows($con) != TRUE) {
                    $sql = "INSERT INTO delivered(content_id_url, user_id)VALUES('$post_url_id', '$USER')";
                    $query = mysqli_query($con, $sql);
                }
                ?>

                <div class="profile profile_left">
                  <a class="small_photo" >
                    <img style="background-image: url('<?php if($posHeadData['photo'] != NULL){ echo PROFILE_IMAGE . $posHeadData['photo'];}else{ echo '../img/idle_profile.png'; } ?>');" class="small_photo">
                    <span style="text-shadow:<?php echo $posHeadData['color']; ?> -.95em 0em 4.5em"><small><?php echo $posHeadData['name']; ?></small></span>
                  </a>
                </div>

                <div class="dropright options">
                  <i class="fas fa-ellipsis-v fa-sm dropdown-toggle" id="profile<?php echo $post['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></i>
                  <div class="dropdown-menu" aria-labelledby="profile<?php echo $post['id'] ?>">
                    <a class="dropdown-item" href="#">Action</a> 
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                  </div>
                </div>

                <?php if($post['caption'] !== null): ?>
                    <span class="caption">
                        <pre><?php echo $post['caption']; ?></pre>
                    </span>
                <?php endif; ?>


                <a  data-fancybox="post" class="wide post" data-type="ajax" href="../post.php?id=<?php echo $post['id'] * $_SESSION['safe']; ?>&post=true"></a>

                <?php if($post['media_url'] !== null): ?>
                  <div class="outer">
                      <div class="media">
                          <?php
                          $media_urls = array_filter([$post['media_url'], $post['media2_url'], $post['media3_url'], $post['media4_url']]);
                          foreach ($media_urls as $index => $media_url):
                              $filename = basename($media_url);
                              $type_of = pathinfo($filename, PATHINFO_EXTENSION);
                              $is_video = in_array($type_of, ['mp4', 'avi', 'gif']);
                              $slide_class = 'slide' . ($index + 1);
                              $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension
                          ?>
                              <?php if($is_video): ?>
                                  <a data-fancybox="media<?php echo $post['id']; ?>" href="<?php echo POST_VIDEO . $media_url; ?>" class="slides <?php echo $slide_class; ?>" style="background-image: url('<?php echo $thumbnail; ?>');">
                                      <div class="video"></div>
                                  </a>
                              <?php else: ?>
                                  <a data-fancybox="media<?php echo $post['id']; ?>" href="<?php echo POST_IMAGE . $media_url; ?>" class="slides <?php echo $slide_class; ?>" style="background-image: url('<?php echo $thumbnail; ?>');"></a>
                              <?php endif; ?>
                          <?php endforeach; ?>
                      </div>
                  </div>
                <?php endif; ?>

                <div class="post-footer">
                    <div>
                        <i class="fas fa-eye"></i>
                        <span class="sr-only">Engagements</span>
                        <span>
                            <?php

                                $creator = $post['user_id'];
                                $sql = "SELECT user_id, time FROM opened WHERE content_id_url = '$post_url_id' && user_id != '$creator'";
                                $opened = sendQuery($con, $sql);
                                    
                                echo mysqli_affected_rows($con);
                            ?>
                        </span>
                    </div>
                    <i><?php echo datemodifier($post['created']); ?></i>
                </div>
            </div>
        </div>
        <?php $pool++ ?>
        <?php if($pool == '7'): ?>
            <div class="post-item">
                <div class="post-content">
                    <div class="profile profile-left">
                        <i class="fab fa-google"></i>
                        <small>Google AD</small>
                    </div>
                    <div>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, ipsa corporis, incidunt reru odit.
                    </div>
                </div>
            </div>
        <?php $pool = '0'; endif; ?>

    <?php endforeach;?>
      </div>
    </div>
    
<div class="row">
  <br>
  <br>
  <br>
</div>
    
</div>


<script>


// var msnry = new Masonry('.Personalposts');

$(document).ready(function() {
    var $grid = $('.Personalposts').masonry({
        itemSelector: '.post-item',
        percentPosition: true,
        columnWidth: '.post-item'
    });

    // Layout Masonry after each image loads
    $grid.imagesLoaded().progress(function() {
        $grid.masonry('layout');
    });

    // Force layout recalculation after a short delay
    setTimeout(function() {
        $grid.masonry('layout');
    }, 500);

    // Recalculate layout on window resize
    $(window).on('resize', function() {
        $grid.masonry('layout');
    });
});


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

</script>

<?php endif; unset($posHeadData, $posts); ?>