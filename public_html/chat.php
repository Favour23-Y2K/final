<?php session_start(); ?>

<?php require_once('..\assets\apache\db_con.php'); ?>
<?php require_once('..\assets\apache\functions.php'); ?>


<?php if (!isset($_GET['id'])):  header('Location:../'); ?>
   <h1>This Page Cannot Be Processed</h1>
<?php elseif (isset($_GET['id'])):?>

<?php

if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);
    $thischat = $_GET['id'] / $_SESSION['safe'];
} else {
    echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}


include('../assets/part/icon.select.php.inc');

$sql = "SELECT * FROM messages WHERE chat_id = '$thischat' && deleted is NULL ORDER BY sent";
$messages = sendQuery($con, $sql);

if(mysqli_affected_rows($con) == '0') {
    $nomessage = TRUE;
} else {
    $nomessage = FALSE;
}

$sql = "SELECT * FROM chat_rel WHERE chat_id = '$thischat'";
$ischat = sendQuery($con, $sql);


$members = [];
$n = 1;

    foreach ($ischat as $key) {
        $members[$n] = $key['user_id'];
        $n++;
    }
    



if($_SERVER['HTTP_SEC_FETCH_SITE'] != 'same-origin') {
    header('Location: ../');
}

if(!in_array($USER, $members)) {
    echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';
}


?>

<?php if(in_array($USER, $members, true)): ?>
    <div id="wrapper" class="open chatarea">
        <?php $target = 'chat_' . $_GET['id']; include_once('../assets/part/chatbox.php.inc'); ?>
        <?php $close = 'chat_' . $_GET['id']; include_once('../assets/part/chatbox.php.inc'); ?>
        <header>
            <a>
                <button data-fancybox-close><i class="fas fa-chevron-circle-left"></i></button>
            </a>
            <div class="profile profile_center">
                <?php
                if(isset($group)) {
                    $group_id = $group;
                    include('../assets/part/profile.php.inc') ;
                }elseif(isset($other_user)) {
                    $user_id = $other_user['user_id'];
                    include('../assets/part/profile.php.inc') ;
                }
                 ?>
            </div>
            <a id="menu-toggle<?php echo $_GET['id'] ?>">
                <button>
                    <div class="menu"></div>
                    <div class="menu"></div>
                    <div class="menu"></div>
                </button>
            </a>
        </header>

    <?php if (!isset($_GET['id'])):  echo '<script>parent.jQuery.fancybox.getInstance().close();</script>';?>
       <h1>This Page Cannot Be Processed</h1>
    <?php elseif (isset($_GET['id'])):?>


    <div id="page-content-wrapper" class="content">

    <div>
 
    </div>

    <?php if($nomessage == FALSE): $w = '1'; $d = []; ?>
        
    <?php foreach($messages as $message): ?>


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
                echo '<div class="row first"><small class="protected"><i class="fas fa-lock fa-sm"></i> Chats are Protected</small></div>';
            }

            $w++;

            ?>
            
            <?php
                    if(isset($message['post_id'])):
                    {
                        $post_id = $message['post_id'];
                        $sql = "SELECT * FROM posts WHERE id = $post_id";
                        $post = mysqli_fetch_assoc(sendQuery($con, $sql));
                    }
                ?>

                <div class="posts post-reply row col-7 <?php if($USER != $message['user_id']){echo 'other';} ?>">
                    <style>
                        div.posts.post-reply{
                            position: relative;
                            margin: unset;
                            background-color: rgba(0, 0, 0, 0.4);
                            border-radius: .8em;
                            /* float: right; */
                            left:43%;
                        }
                        div.posts.post-reply.other{
                            /* float: left; */
                            left:0;
                            /* background-color:red; */
                        }
                    </style>            
                    <div href="" class="post-item col-12" id="<?php echo 'post' . $post['id'] ; ?>">
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
                    <!-- 
                    <div class="profile profile_left">
                        <?php // $user_id = $post['user_id']; include('../assets/part/profile.php.inc');  unset($user_id); ?>
                    </div> -->
                    <?php if($post['caption'] !== null): ?>
                        <span class="caption">
                            <pre><?php echo $post['caption']; ?></pre>
                        </span>
                    <?php endif; ?>

                    <!-- <a href="#<?php// echo $post['id']; ?>" class="wide post">hey</a> -->


                    <a  data-fancybox="post" class="wide post" data-type="ajax" href="../post.php?id=<?php echo $post['id'] * $_SESSION['safe']; ?>"></a>

                    <?php if($post['media_url'] !== null): ?>
                        <div class="outer in-message">
                            <div class="media in-message">
                                <?php
                                $media_urls = array_filter([$post['media_url'], $post['media2_url'], $post['media3_url'], $post['media4_url']]);
                                foreach ($media_urls as $index => $media_url):
                                    $filename = basename($media_url);
                                    $type_of = pathinfo($filename, PATHINFO_EXTENSION);
                                    $is_video = in_array($type_of, ['mp4', 'avi', 'gif']);
                                    $media_path = $is_video ? POST_VIDEO : POST_IMAGE;
                                    $slide_class = 'slide' . ($index + 1);
                                    $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension
                                ?>
                                    <?php if($is_video): ?>
                                        <a data-fancybox="media<?php echo $post['id'] . $message['id']; ?>" href="<?php echo $media_path . $media_url; ?>" class="slides <?php echo $slide_class; ?>" style="background-image: url('<?php echo $thumbnail; ?>');">
                                            <div class="video"></div>
                                        </a>
                                    <?php else: ?>
                                        <a data-fancybox="media<?php echo $post['id'] . $message['id']; ?>" href="<?php echo $media_path . $media_url; ?>" class="slides <?php echo $slide_class; ?>" style="background-image: url('<?php echo $thumbnail; ?>');"></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <style>
                                div.in-message {
                                    margin: 0 !important;
                                    padding: 0 !important;
                                    width: 100% !important;
                                    height: 13em !important;
                                }
                            </style>
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
                </div>


                <?php endif; unset($post); ?>

        <div class="message-wrap <?php if($USER != $message['user_id']){echo 'other';} ?>">
            <div class="message  <?php if($USER != $message['user_id']){echo 'other';} ?>" id="msg<?php echo $message['id'] * $_SESSION['safe']; ?>">
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


                    <?php if($message['replied_message'] !== null): ?>

                        <?php
                            $replied_message = $message['replied_message'];    
                            $sql = "SELECT * FROM messages WHERE id = '$replied_message' && deleted is NULL";
                            $replied_message_contents = mysqli_fetch_assoc(sendQuery($con, $sql));
                        ?>

                        <?php if($replied_message_contents): ?>


                        <a href="#msg<?php echo $replied_message * $_SESSION['safe'] ?>" class="replyMessageLink mx-auto rep_mess reply-quoted">

                            <?php
    
                            echo '<p><small>';
                        
                                if($replied_message_contents['user_id'] == $USER) {
                                    echo 'You: ';
                                } else {
                                    $sender = $replied_message_contents['user_id'];
                                    $sql = "SELECT username, dob FROM users WHERE id = $sender";
                                    $sendername = mysqli_fetch_assoc(sendQuery($con, $sql));
                                    echo $sendername['username'];
                                    $profile['dob'] = $sendername['dob'];
                                    include('../assets/part/zodiac_calculator.php.inc');
                                    echo ':  ';
                                    unset($sender, $sendername, $profile['dob']);
                                }
                            
                            echo '</small></p>';
                            
                            
                            if(isset($replied_message_contents['caption'])){
                            
                                // $max='350';
                                $messagetogoRaw = explode(PHP_EOL, $replied_message_contents['caption'] ?? '');
                                if (!empty($messagetogoRaw)) {
                                    $currentMessage = $messagetogoRaw[0] ?? $messagetogoRaw[1] ?? '';
                                } else {
                                    $currentMessage = '';
                                }
                            
                                if ($replied_message_contents['caption'] !== null) {
                                    if (strlen($replied_message_contents['caption']) >= '55') {
                                        $currentMessage = substr($currentMessage, 0, 55) . '...';
                                    }else{
                                        $currentMessage = $replied_message_contents['caption'];
                                    }
                                }
                            
                                $currentMessage = '<pre>' . $currentMessage . '</pre>';
                            
                            }
                        
                                if($replied_message_contents['media_url'] != NULL) {
                                    $filename = basename($replied_message_contents['media_url']); // Get filename without path
                                    $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension
        
                                    if($replied_message_contents['media_type'] == 'attachment') {
                                        echo '<i class="fas fa-paperclip"></i>' . ' ' . $replied_message_contents['old_name'];
                                    }elseif($replied_message_contents['media_type'] == 'voicenote') {
                                        echo '<i class="fas fa-microphone"></i>' . ' ' . $replied_message_contents['old_name'];
                                    }elseif ($replied_message_contents['media_type'] == 'image') {
                                        echo '<img class="rep_view" src="' . $thumbnail . ' " />' . ' ';
                                        echo '<i class="fas fa-image"></i>';
                                        if (isset($replied_message_contents['caption']) && $replied_message_contents['caption'] !== '' ) {
                                            echo ' ' . $currentMessage;
                                        } else {
                                            echo ' ' . 'Photo';
                                        }
                                    }elseif ($replied_message_contents['media_type'] == 'video') {
                                        echo '<img class="rep_view" src="' . $thumbnail . ' " />' . ' ';
                                        echo '<i class="fas fa-video"></i>';
                                        if (isset($replied_message_contents['caption']) && $replied_message_contents['caption'] !== '' ) {
                                            echo ' ' . $currentMessage;
                                        } else {
                                            echo ' ' . 'Video';
                                        }
                                    }
                                } else {
                                    echo $currentMessage;
                                }
                                unset($currentMessage);
                            ?>
                            
                        </a>

                        
                        <?php else: ?>

                            <a href="#msg<?php echo $replied_message * $_SESSION['safe'] ?>" class="mx-auto rep_mess reply-quoted">
                                Deleted Message
                            </a>


                        <?php endif; ?>

                        <style>
                            .rep_mess {
                                background: white;
                                opacity: 0.7;
                                color: #888;
                                font-size: 12px;
                                font-style: italic;
                                font-weight: 400;
                                /* margin-top: 5px; */
                                display: block;
                                border-radius: .5em;
                                padding: .7em;
                                line-height: .5em;
                                z-index: 2 !important;
                            }
                        </style>
                    <?php endif; ?>





                    <a class="message-info" data-toggle="collapse" data-target="#msgm<?php echo md5($message['id'] . $n); ?>" aria-expanded="false" aria-controls="collapseExample"></a>
                    <?php if ($message['media_url'] != NULL): ?>
                        <?php
                            $filename = basename($message['media_url']); // Get filename without path
                            $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension
                        ?>
                        <?php if ($message['media_type'] == 'image'): //picture ?>
                            <a data-fancybox="media-large<?php echo md5($message['id'] . $n); ?>" href="<?php echo DIRECT_IMAGE . $message['media_url']; ?>"  class="message-media media" style="background-image: url('<?php echo $thumbnail ?>');"> <small class="media-size" ><?php echo $message['media_size']; ?></small> </a>
                        <?php elseif($message['media_type'] == 'video'): //video ?>
                            <div class="media">
                                <a data-fancybox="media<?php echo md5($message['id'] . $n); ?>" href="<?php echo DIRECT_VIDEO . $message['media_url']; ?>" class="media video-frame" style="background-image: url('<?php echo $thumbnail ?>');"> <small class="media-size" ><?php echo $message['media_size']; ?></small> <div class="video"></div></a>
                            </div>
                        <?php elseif($message['media_type'] == 'attachment'): //attachment?>
                            <a class="documentLink attachment" target="__blank" href="<?php echo ATTACHMENT . $message['media_url'] ?>" download="<?php echo $message['old_name'] ?>"><?php echo '<i class="fas fa-paperclip"></i>' . ' ' . $message['old_name']; ?> <small class="media-size" ><?php echo $message['media_size']; ?></small> </a>
                        <?php elseif($message['media_type'] == 'voicenote'): //voicenote?>
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
            <div class="row">
                <div class="collapse message-actions" id="msgm<?php echo md5($message['id'] . $n); ?>">
                    <div class="options">
                        <a>
                            <i class="delete fas fa-trash"></i>
                        </a>
                        <a class="no-reply" id="click-reply<?php echo md5($message['id'] . $n); ?>" data-toggle="collapse" data-target="#reply<?php echo md5($message['id'] . $n); ?>" role="button" aria-expanded="false" aria-controls="collapseExample">
                            <i class="reply fas fa-reply"></i>
                        </a>
                    </div>
                    <?php if($message['user_id'] == $USER): ?>
                    <div class="status">
                        <?php if(isset($group)): ?>
                            <?php
                            $sql = "SELECT * FROM opened WHERE content_id_url = '$message_url_id' && user_id != '$USER' ORDER BY time DESC";
                            $opened_by = sendQuery($con, $sql);
                            ?>
                            <?php if(mysqli_affected_rows($con) == TRUE): ?>
                                <p><small>Read:</small></p>
                                <?php foreach($opened_by as $user): ?>
                                <p>
                                    <small>
                                        <?php
                                        $reader = $user['user_id'];
                                        $sql = "SELECT username, dob FROM users WHERE id = $reader";
                                        $readername = mysqli_fetch_assoc(sendQuery($con, $sql));
                                        if(mysqli_affected_rows($con) == true){
                                            echo $readername['username'];
                                            $query['dob'] = $readername['dob'];
                                            include('../assets/part/zodiac_calculator.php.inc');
                                            echo $signinfo['sign-code'];
                                            echo ':  ';
                                            $timer = statusTime($user['time']);
                                            echo <<<MULTI
                                                <i> $timer </i>
                                            MULTI;
                                            unset($reader, $timer, $readername, $query['dob']);
                                        
                                        }
                                        ?>
                                    </small>
                                </p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php
                            $sql = "SELECT * FROM delivered WHERE content_id_url = '$message_url_id' && user_id != '$USER'";
                            $delivered = mysqli_fetch_assoc(sendQuery($con, $sql));
                            ?>
                            <?php if(mysqli_affected_rows($con) == TRUE): ?>
                            <p><small>Delivered: <i><?php echo statusTime($delivered['time']); ?></i></small></p>
                            <?php endif; ?>
                            <?php
                            $sql = "SELECT * FROM opened WHERE content_id_url = '$message_url_id' && user_id != '$USER'";
                            $opened = mysqli_fetch_assoc(sendQuery($con, $sql));
                            ?>
                            <?php if(mysqli_affected_rows($con) == TRUE): ?>
                            <p><small>Read: <i><?php echo statusTime($opened['time']); ?></i></small></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="collapse mx-auto fixed-bottom reply-quoted" id="reply<?php echo md5($message['id'] . $n); ?>">
                <?php
                
                echo '<p><small>';
                
                    if($message['user_id'] == $USER) {
                        echo 'You: ';
                    } else {
                        $sender = $message['user_id'];
                        $sql = "SELECT username, dob FROM users WHERE id = $sender";
                        $sendername = mysqli_fetch_assoc(sendQuery($con, $sql));
                        echo $sendername['username'];
                        $profile['dob'] = $sendername['dob'];
                        include('../assets/part/zodiac_calculator.php.inc');
                        echo ':  ';
                        unset($sender, $sendername, $profile['dob']);
                    }

                echo '</small></p>';

                
                if(isset($message['caption'])){
                    
                    // $max='350';
                    $messagetogoRaw = explode(PHP_EOL, $message['caption'] ?? '');
                    if (!empty($messagetogoRaw)) {
                        $currentMessage = $messagetogoRaw[0] ?? $messagetogoRaw[1] ?? '';
                    } else {
                        $currentMessage = '';
                    }

                    if ($message['caption'] !== null) {
                        // if (strlen($message['caption']) >= strlen($currentMessage)) {
                        //     $currentMessage .= '...';
                        // } elseif (strlen($message['caption']) >= '55') {
                        //     $currentMessage .= '...';
                        // }
                        if (strlen($message['caption']) >= '55') {
                            $currentMessage = substr($currentMessage, 0, 55) . '...';
                        }else{
                            $currentMessage = $message['caption'];
                        }
                    }

                    $currentMessage = '<pre>' . $currentMessage . '</pre>';

                }

                    if($message['media_url'] != NULL) {

                        $filename = basename($message['media_url']); // Get filename without path
                        $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension

                        if($message['media_type'] == 'attachment') {
                            echo '<i class="fas fa-paperclip"></i>' . ' ' . $message['old_name'];
                        }elseif($message['media_type'] == 'voicenote') {
                            echo '<i class="fas fa-microphone"></i>' . ' ' . $message['old_name'];
                        }elseif ($message['media_type'] == 'image') {
                            echo '<img class="rep_view" src="' . $thumbnail . ' " />' . ' ';
                            echo '<i class="fas fa-image"></i>';
                            if (isset($message['caption']) && $message['caption'] !== '' ) {
                                echo ' ' . $currentMessage;
                            } else {
                                echo ' ' . 'Photo';
                            }
                        }elseif ($message['media_type'] == 'video') {
                            echo '<img class="rep_view" src="' . $thumbnail . ' " />' . ' ';
                            echo '<i class="fas fa-video"></i>';
                            if (isset($message['caption']) && $message['caption'] !== '' ) {
                                echo ' ' . $currentMessage;
                            } else {
                                echo ' ' . 'Video';
                            }
                        }

                    } else {
                        echo $currentMessage;
                    }
                    unset($currentMessage);
                ?>

                <span class="close-quote">&times;</span>

            </div>

            <script>
                $("#page-content-wrapper").click(function(e) {
                  e.preventDefault();
                  $("#msgm<?php echo md5($message['id'] . $n); ?>").collapse('hide');
                });
                
                $(".no-reply, .close-quote").click(function(e) {
                    e.preventDefault();
                    $("#reply<?php echo md5($message['id'] . $n); ?>").collapse('hide');
                });
                
                $("#click-reply<?php echo md5($message['id'] . $n); ?>").click(function(event) {
                    // !!! Be careful for this
                    $("#reply<?= $thepost.$close ?>").val("<?php echo 'message/' . $message['id'] * $_SESSION['safe']; ?>");
                });
                

                  
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
                
            </script>
            
        </div>

        
    <?php endforeach; ?>
    <?php elseif($nomessage == TRUE): ?>
        <p class="mx-auto empty text-center">There are millions of possibilities from just one conversation. <br> A job, a car, a house, love, world peace. <br> Hold tight, you're in for a ride. <br>Say Hey!</p>
    <?php endif;  unset($nomessage); ?>

        
            <div class="row">
                <br>
                <br>
                <br>
                <br>
            </div>
        
        </div>
    <?php endif; ?>

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
   
    </div>

    <?php endif; ?>

    <script>
        
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

        $("#menu-toggle<?php echo $_GET['id'] ?>").click(function(e) {
          e.preventDefault();
          $("#wrapper").toggleClass("toggled");
        });

        $("#page-content-wrapper").click(function(e) {
          e.preventDefault();
          $("#wrapper").removeClass("toggled");
        });

        
        $('[data-fancybox].media').fancybox({
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

        $( '[data-fancybox="images"]' ).fancybox({
            caption : function( instance, item ) {
                var caption = $(this).data('caption') || '';

                if ( item.type === 'image' ) {
                    caption = (caption.length ? caption + '<br />' : '') + '<a href="' + item.src + '">Download image</a>' ;
                }

                return caption;
            }
        
        });

        // $('#<?php //echo $_GET['id']; ?>').ready(function(){            
        //     var chatArea = document.getElementById("page-content-wrapper");
        //     document.getElementById('<?php //echo $_GET['id']; ?>').scrollTop = chatArea.scrollHeight;
        // });

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
              touch: false,  // This disables all touch/swipe interactions
              clickContent: false,  // This prevents clicking to navigate
              clickSlide: false,  // This prevents clicking on the slide to close
              wheel: false,  // This disables mouse wheel navigation
              keyboard: false,  // This disables keyboard navigation
  
            
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



        $(document).on('click', '.fancybox-content a.replyMessageLink', function(e) {
            var href = $(this).attr('href');

            // If it's an anchor link (scrolling), prevent the default behavior
            if (href.startsWith('#')) {
                e.preventDefault();
            
                // Find the target element by ID and scroll it into view
                var targetElement = $(href);

                if (targetElement.length) {
                    // Scroll the element into view
                    targetElement[0].scrollIntoView({
                        behavior: 'smooth',  // Smooth scrolling
                        block: 'center'       // Align the top of the element with the top of the viewport
                    });
                }
            }
        });


        $(document).on('click', '.fancybox-content a.documentLink', function(e) {
            e.preventDefault();

            var href = $(this).attr('href');

            window.open(href, '_blank'); // Let the browser navigate to the link
        });

    </script>

<?php else: ?>
    <h1>
        You Are Not Permitted to View this Page
    </h1>
<?php endif; ?>

<?php unset($thischat); ?>

<style>
    .chatarea .profile a>span {
        z-index: 2;
    }
</style>