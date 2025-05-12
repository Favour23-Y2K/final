<?php session_start(); ?>

<?php require_once('..\assets\apache\db_con.php'); ?>
<?php require_once('..\assets\apache\functions.php'); ?>

<?php


if($_SERVER['HTTP_SEC_FETCH_SITE'] != 'same-origin') {
    header('Location: ../');
}

?>

<?php if (!isset($_GET['id'])):  header('Location: ../');?>
   <h1>This Page Cannot Be Processed</h1>
<?php elseif (isset($_GET['id'])):?>

<div id="wrapper" class="open">    
        <?php
    
        $USER = decrypt($_SESSION['user'], $_SESSION['key']);

        $post_id = $_GET['id'] / $_SESSION['safe'];
        $sql = "SELECT * FROM posts WHERE id = $post_id";
        $post = mysqli_fetch_assoc(sendQuery($con, $sql));

        $post_url_id = "post/" . $post['id'];
        
        $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$post_url_id' && user_id = '$USER'";
        $opened = sendQuery($con, $sql);

        if (mysqli_affected_rows($con) != TRUE) {
            $sql = "INSERT INTO opened(content_id_url, user_id)VALUES('$post_url_id', '$USER')";
            $query = mysqli_query($con, $sql);
        }

        ?>



    <?php //if($post['user_id'] != $USER && $post['comments'] == 1): ?>
    <?php $target = 'post_' . $_GET['id']; include_once('../assets/part/chatbox.php.inc') ?>
    <?php //endif; ?>
    <header>
        
        <a href="">
            <button data-fancybox-close ><i class="fas fa-chevron-circle-left"></i></button>
        </a>
        
        <div class="profile profile_center">
            <?php $user_id = $post['user_id']; include('../assets/part/profile.php.inc'); unset($user_id); ?>
        </div>

        <a href="#menu-toggle<?php echo $post['id']; ?>" id="menu-toggle<?php echo $post['id']; ?>">
            <button>
                <div class="menu"></div>
                <div class="menu"></div>
                <div class="menu"></div>
            </button>
        </a>
    </header>
    
    <div id="page-content-wrapper" class="content">
<div>

</div>
        <div class="post-content container">

        <?php if($post['caption'] !== null): ?>
        <span class="caption caption-content">
            <pre><?php echo $post['caption']; ?></pre>
        </span>
    <?php endif; ?>

    <?php if($post['media_url'] !== null): ?>

        <div class="outer mx-auto col-12">
            <div class="media">
                <?php
                    $media_fields = ['media_url', 'media2_url', 'media3_url', 'media4_url'];
                    $slide_count = 1;

                    foreach ($media_fields as $media_field):
                        if ($post[$media_field] !== null):
                            $media = explode('.', $post[$media_field]);
                            $type_of = end($media);
                            $is_video = in_array($type_of, ['mp4', 'avi', 'gif']);
                            $filename = basename($post[$media_field]); // Get filename without path
                            $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension
                            // die($thumbnail);

                        ?>
                        <?php if($is_video): ?>
                            <a data-fancybox="media-large<?php echo $post['id']; ?>" href="<?php echo POST_VIDEO . $post[$media_field]; ?>" class="slides slide<?php echo $slide_count; ?>" style="background-image: url('<?php echo $thumbnail; ?>');">
                                <div class="video"></div>
                            </a>
                        <?php else: ?>
                            <a data-fancybox="media-large<?php echo $post['id']; ?>" href="<?php echo POST_IMAGE . $post[$media_field]; ?>" class="slides slide<?php echo $slide_count; ?>" style="background-image: url('<?php echo $thumbnail; ?>');"></a>
                        <?php endif; ?>

                    <?php
                        $slide_count++;
                        endif;
                    endforeach;
                    ?>
            </div>
        </div>

    <?php endif; ?>

        </div>
        
        <div class="post-footer">
            <?php
            $creator = $post['user_id'];
            $sql = "SELECT user_id, time FROM opened WHERE content_id_url = '$post_url_id' && user_id != '$creator'";
            $opened = sendQuery($con, $sql);
            ?>
            <?php if($post['user_id'] != $USER): ?>
            <div>
                <i class="fas fa-eye"></i>
                <span aria-label="viewers"><?php echo mysqli_affected_rows($con);?></span>
            </div>
            <?php else: ?>
            <div data-toggle="collapse" data-target="#viewersList" aria-expanded="false" aria-controls="viewersList">
                <i class="fas fa-eye fa-lg"></i>
                <span class="sr-only">Enagagements</span>
                <span><?php echo mysqli_affected_rows($con);?></span>
            </div>
            <?php endif; ?>
            <i><?php echo datemodifier($post['created']); ?></i>
        </div>
    
        <?php if(mysqli_affected_rows($con) != 0): ?>
        <div class="collapse" id="viewersList">
            <div class="card card-body">
                <?php foreach($opened as $user): ?>

                <div class="viewer-each d-flex">
                    <div class="profile profile_left">
                        <?php $user_id = $user['user_id']; include('../assets/part/profile.php.inc'); unset($user_id); ?>
                    </div>

                    <small class="opened-time"><?php echo datemodifier($user['time']); ?></small>
                </div>

                <?php endforeach; unset($user); ?>
            </div>
        </div>
        <?php else: ?>
        <div class="collapse" id="viewersList">
            <div class="card card-body">
                <div class="viewer-each d-flex">
                    <small class="text-center col-12">You Have no Views yet</small>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php
        
        $sql = "SELECT * FROM messages WHERE post_id = '$post_id' && deleted is NULL ORDER BY sent";
        $messages = sendQuery($con, $sql);
        if(mysqli_affected_rows($con) == '0') {
            $nomessage = TRUE;
        } else {
            $nomessage = FALSE;
        }
        $group = true;

        if($nomessage == FALSE): $w = '1'; $d = [];
        ?>
        

        <?php
        // Initialize the variable $n before using it
        $n = 0; // Set a default value

        foreach ($messages as $message):
        ?>

<?php

$date = normalizeDate($message['sent']);


$d[$w]['date'] = $date->format('d/m/y');

if($w != 1 && ($d[$w]['date'] != $d[$w-1]['date'])) {
    echo '<small class="day mx-auto">';
    echo sayday($message['sent']);
    echo '</small>';
} elseif($w == 1) {
    echo '<small class="day mx-auto init">';
    echo sayday($message['sent']);
    echo '</small>';
    // echo '<div class="row first"><small class="protected"><i class="fas fa-lock fa-sm"></i> Chats are Protected</small></div>';
}

$w++;

?>

<div class="message-wrap <?php if($USER != $message['user_id']){echo 'other';} ?>">
    <div class="message  <?php if($USER != $message['user_id']){echo 'other';} ?>">
        <?php if($USER != $message['user_id'] && isset($group)):?>
            <span class="other-user-icon">
                <div class="profile no-image">
                <?php
                $user_id = $message['user_id'];
                include('../assets/part/profile.php.inc');
                ?>
                </div>
            </span>
        <?php endif; ?>
        <?php //if (isset($message['deleted'])): ?>


        <?php //else: ?>

        <a class="message-info" data-toggle="collapse" data-target="#msgm<?php echo md5($message['id'] . $n); ?>" aria-expanded="false" aria-controls="collapseExample"></a>

            <?php if ($message['media_url'] != NULL): ?>
                <?php if ($message['media_type'] == 'image'): //picture ?>
                    <a data-fancybox="media-large<?php echo md5($message['id'] . $n); ?>" href="<?php echo DIRECT_IMAGE . $message['media_url']; ?>"  class="message-media media" style="background-image: url('<?php echo THUMBNAILS . '/' . md5(DIRECT_IMAGE . $message['media_url']) . '.png'; ?>');"> <small class="media-size" ><?php echo $message['media_size']; ?></small> </a>
                <?php elseif($message['media_type'] == 'video'): //video ?>
                <div class="media">
                    <a data-fancybox="media<?php echo md5($message['id'] . $n); ?>" href="<?php echo DIRECT_VIDEO . $message['media_url']; ?>" class="media video-frame" style="background-image: url('<?php echo THUMBNAILS . '/' . md5(DIRECT_VIDEO . $message['media_url']) . '.png'; ?>');"> <small class="media-size" ><?php echo $message['media_size']; ?></small> <div class="video"></div></a>
                </div>
                <?php elseif($message['media_type'] == 'attachment'): //attachment?>
                    <a class="attachment" target="__blank" href="<?php echo ATTACHMENT . $message['media_url'] ?>" download="<?php echo $message['old_name'] ?>"><?php echo '<i class="fas fa-paperclip"></i>' . ' ' . $message['old_name']; ?> <small class="media-size" ><?php echo $message['media_size']; ?></small> </a>
                <?php elseif($message['media_type'] == 'voicenote'): //attachment?>
                    <audio id="audio-<?= md5($message['id'] . $n) ?>" src="<?php echo VOICENOTES . $message['media_url'] ?>"></audio>
                    <div class="audio-controls" id="audio-controls<?= md5($message['id'] . $n) ?>">
                        <div class="col-9 audio-holder" id="audio-holder<?= md5($message['id'] . $n) ?>">
                            <div class="play-btn col-3" id="play-btn<?= md5($message['id'] . $n) ?>" onclick="togglePlay('<?= md5($message['id'] . $n) ?>')"><i class="fas fa-play"></i></div>
                            <div class="timeline col-9" id="timeline<?= md5($message['id'] . $n) ?>">
                                <div class="progress-timeline col-12" id="progress-timeline<?= md5($message['id'] . $n) ?>">
                                    <div id="progress-inner<?= md5($message['id'] . $n) ?>" class="progress-inner" style="width: 0%; background-color: black;"></div>
                                    <div id="waveform-<?= md5($message['id'] . $n) ?>" class="waveform"></div>
                                </div>
                                <div class="mini-info col-12" id="mini-info<?= md5($message['id'] . $n) ?>">
                                    <div class="vn-length col-7" id="vn-length<?= md5($message['id'] . $n) ?>">
                                        <small><?= $message['old_name'] ?></small>
                                    </div>
                                    <div class="vn-size col-5" id="vn-size<?= md5($message['id'] . $n) ?>">
                                        <small><?= $message['media_size'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3 audio-warp" id="audio-warp<?= md5($message['id'] . $n) ?>">
                            <div class="vn-speed row btn btn-outline-dark" id="vn-speed<?= md5($message['id'] . $n) ?>" onclick="toggleSpeed('<?= md5($message['id'] . $n) ?>')">x1</div>
                            <div class="countdown-timer" id="countdown-timer<?= md5($message['id'] . $n) ?>">00:00</div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($message['caption'] != NULL): ?>
                <span style="position:relative" class="caption-content">
                    <?php
                        $max='350';
                        echo '<pre>' . substr($message['caption'], 0, $max) . '</pre>';
                        if(strlen($message['caption']) >= $max) {
                            $currentLength = $max;
                            $specId = md5($message['id'] . $currentLength);
                            echo <<<MULTI
                            <i target="$specId" style="white-space:pre;width:100%;font-size:smaller;text-align:right!important;bottom:0" class="read_more"><small>...read more</small></i>
                            MULTI;
                            while ($currentLength <= strlen($message['caption'])) {
                                $newLen = $currentLength + $max;
                                $tip = md5($message['id'] . $currentLength);
                                $statement = '<pre style="display:none !important" id="'.$tip.'">' . substr($message['caption'], $currentLength, $max) . '</pre>';
                                $currentLength += $max;
                                $target = md5($message['id'] . $currentLength);
                                if($newLen <= strlen($message['caption'])) {
                                    $statement .= <<<MULTI
                                    <i target="$target" style="display:none;white-space:pre;width:100%;font-size:smaller;text-align:right!important;bottom:0" class="read_more $tip"><small>...read more</small></i>
                                    MULTI;
                                }
                                echo $statement;
                            }
                        }
                    ?>
                </span>

            <?php endif; ?>


            <?php 

                $message_url_id = 'message/' . $message['id'];

                $sql = "SELECT TRUE FROM opened WHERE content_id_url = '$message_url_id' && user_id = '$USER'";
                $open = sendQuery($con, $sql);
                    
                if (mysqli_affected_rows($con) != TRUE) {
                    if($message['user_id'] != $USER) {
                        $sql = "INSERT INTO opened(content_id_url, user_id)VALUES('$message_url_id', '$USER')";
                        $query = mysqli_query($con, $sql);
                    }
                }
            ?>
        <?php //endif; ?>
        <small class="time-read">
        <?php
            $time = normalizeDate($message['sent']);
            echo $time->format('h\:i A');
        ?>
        </small>
    </div>
</div>


<div class="row"></div>
<?php
    // Increment $n as needed
    $n++;
endforeach;  endif;  unset($nomessage);?>



<div class="row">
    <br>
    <br>
    <br>
    <br>
</div>


</div>


    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">
                <a href="#">
                    Start Bootstrap
                </a>
            </li>
            <li>
                <a href="#">Dashboard</a>
            </li>
            <li>
                <a href="#">Shortcuts</a>
            </li>
            <li>
                <a href="#">Overview</a>
            </li>
            <li>
                <a href="#">Events</a>
            </li>
            <li>
                <a href="#">About</a>
            </li>
            <li>
                <a href="#">Services</a>
            </li>
            <li>
                <a href="#">Contact</a>
            </li>
        </ul>
    </div>
    <!-- // check if user is logged in,  -->

</div>

<?php endif; ?>

<script>
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
        autoFocus: true,
        // trapFocus: true,
    });

    // $.fancybox.defaults.hash = false; 

    
    $(document).ready(function() {
      $("#reply<?= $thepost.$target ?>").val("<?php echo 'post/' . $_GET['id']; ?>");
    });

    $("#menu-toggle<?php echo $post['id']; ?>").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    
    $("#page-content-wrapper").click(function(e) {
      e.preventDefault();
      $("#wrapper").removeClass("toggled");
    });
    
    $(".post-content").click(function(e) {
      e.preventDefault();
      $("#wrapper").removeClass("toggled");
    });
    
</script>