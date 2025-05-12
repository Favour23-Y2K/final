<?php

session_start();

require_once('..\..\assets\apache\db_con.php');
require_once('..\..\assets\apache\functions.php');
require_once('..\..\assets\apache\constants.php');

if(isset($_SESSION['user'])) {
    $USER = decrypt($_SESSION['user'], $_SESSION['key']);
}

?>
    <div class="chats container" id="chats">
        <h1>chats</h1>
        <hr>
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
            $chatlist = [];
            $msgCount = '0';
            $sql = "SELECT * FROM chat_rel WHERE user_id = '$USER' && deleted IS NULL";
            $chat_rels = sendQuery($con, $sql);

            foreach ($chat_rels as $chat_rel) {
                $chatlist['chat' . $chat_rel['id']]['id'] = $chat_rel['chat_id'];
                $chatlist['chat' . $chat_rel['id']]['archived'] = $chat_rel['archived'];
                $chatlist['chat' . $chat_rel['id']]['last-action-time'] = $chat_rel['stat_time'];
                // $chatlist['chat' . $chat_rel['id']]['post_id'] = $chat_rel['post_id'];

                $thischat = $chat_rel['chat_id'];

                $sql = "SELECT * FROM messages WHERE chat_id = $thischat && deleted is NULL ORDER BY sent DESC";
                $message_last = (sendQuery($con, $sql));
                $latest_messages = sendQuery($con, $sql);

                if (mysqli_affected_rows($con) == '0') {
                    $chatlist['chat' . $chat_rel['id']]['message-time'] = NULL;
                } else {
                    $last_message = mysqli_fetch_assoc($latest_messages);
                    $chatlist['chat' . $chat_rel['id']]['sender-id'] = $last_message['user_id'];
                    $chatlist['chat' . $chat_rel['id']]['message-id'] = $last_message['id'];
                    $chatlist['chat' . $chat_rel['id']]['message-time'] = $last_message['sent'];
                    $chatlist['chat' . $chat_rel['id']]['caption'] = $last_message['caption'];
                    $chatlist['chat' . $chat_rel['id']]['media-url'] = $last_message['media_url'];
                    $chatlist['chat' . $chat_rel['id']]['media-type'] = $last_message['media_type'];
                    $chatlist['chat' . $chat_rel['id']]['deleted'] = $last_message['deleted'];
                    $chatlist['chat' . $chat_rel['id']]['post_id'] = $last_message['post_id'];
                    $chatlist['chat' . $chat_rel['id']]['old_name'] = $last_message['old_name'];
                    $chatlist['chat' . $chat_rel['id']]['unopened'] = FALSE;

                    $nOpened = '0';
                    foreach ($latest_messages as $the_messages) {
                        
                        if($the_messages['user_id'] != $USER) {

                            $message_url_id = 'message/' . $the_messages['id'];
    
                            $sql = "SELECT TRUE FROM delivered WHERE content_id_url = '$message_url_id' && user_id = '$USER'";
                            $delivered = sendQuery($con, $sql);
    
                            if (mysqli_affected_rows($con) != TRUE) {
                                $sql = "INSERT INTO delivered(content_id_url, user_id)VALUES('$message_url_id', '$USER')";
                                $query = mysqli_query($con, $sql);
                            }
    
                            $sql = "SELECT * FROM opened WHERE content_id_url = '$message_url_id' && user_id = '$USER'";
                            $open = sendQuery($con, $sql);

                            if (mysqli_affected_rows($con) != TRUE) {
                                $nOpened++;
                                $chatlist['chat' . $chat_rel['id']]['unopened'] = $nOpened;
                            }
                        }
                    }
                    // $msgCount += $nOpened;
                }
                $time = $chatlist['chat' . $chat_rel['id']]['message-time'];
            }
            
                if(count($chatlist) == 0) {
                    echo '
                    <p class="container text-center">Add friends or join groups to chat</p>
                    ';
                }

            usort($chatlist, build_sorter('message-time', 'last-action-time'));
        ?>

        <ul>
        <?php foreach($chatlist as $listitem): ?>
            <?php

            if($listitem['message-time'] != NULL) {
                if($listitem['unopened'] >= '1') {
                    $unread = '<small style="
                                        background-color:var(--secondary);
                                        color:white;
                                        border-radius:.5em;
                                ">' . $listitem['unopened'] . '</small>';
                } else {
                    $unread = FALSE;
                }
            }
                
            ?>
            <?php if($listitem['archived'] == '0'): ?>
                <?php
                
                $thischat = $listitem['id'];
                include('../../assets/part/icon.select.php.inc');
                
                ?>
                
            <li <?php if($listitem['message-time'] != NULL){if(isset($unread) && $unread != FALSE){echo 'class="unopened-message"';}} ?> >
                <div class="profile profile_left image-only">
                <?php
                if(isset($group)) {
                    $group_id = $group;
                    include('../../assets/part/profile.php.inc') ;
                }elseif(isset($other_user)) {
                    $user_id = $other_user['user_id'];
                    include('../../assets/part/profile.php.inc') ;
                }elseif (isset($advert)) {
                    $ad = TRUE;
                }elseif (isset($service)) {
                    $serv = TRUE;
                }
                
                 ?>
                </div>
                <a  data-fancybox="chat" class="wide chat-link" data-type="ajax" href="../chat.php?id=<?php echo $listitem['id'] * $_SESSION['safe']; ?>">
                    
                    <span class="chat-name"><?php echo $name; unset($name); ?></span>
                    <span class="chat-text">
                        <?php

                        if (isset($listitem['caption']) || isset($listitem['media-url'])) {
                            // echo $listitem['caption'];
                            if ($listitem['deleted'] != NULL) {
                                echo '<i class="fas fa-ban"></i>' . ' ' . '<i>Deleted Message</i>';
                            } else {
                                if(isset($group)) {
                                    if($listitem['sender-id'] != $USER) {
                                        $sender = $listitem['sender-id'];
                                        $sql = "SELECT username, dob FROM users WHERE id = $sender";
                                        $sendername = mysqli_fetch_assoc(sendQuery($con, $sql));
                                        echo $sendername['username'];
                                        $query['dob'] = $sendername['dob'];
                                        include('../../assets/part/zodiac_calculator.php.inc');
                                        echo $signinfo['sign-code'] . ':  ';
                                        unset($sender, $sendername, $query['dob']);
                                    }
                                }

                                if(isset($listitem['caption'])){
                                    $rawCaption = explode(PHP_EOL, $listitem['caption']);
                                    $currentMessage = isset($rawCaption[0]) ? $rawCaption[0] : (isset($rawCaption[1]) ? $rawCaption[1] : '');

                                    if ($listitem['sender-id'] != $USER) {
                                        $currentMessage = substr($currentMessage, 0, 30);

                                        if (strlen($listitem['caption']) > 30) {
                                            $currentMessage .= '...';
                                        }

                                        if ($listitem['media-url'] != NULL) {
                                            $currentMessage = substr($listitem['caption'], 0, 28);

                                            if (strlen($listitem['caption']) > 28) {
                                                $currentMessage .= '...';
                                            } elseif (strlen($listitem['caption']) > 55) {
                                                $currentMessage .= '...';
                                            }
                                        }
                                    } elseif ($listitem['sender-id'] == $USER) {
                                        $currentMessage = substr($listitem['caption'], 0, 42);

                                        if (strlen($listitem['caption']) > 42) {
                                            $currentMessage .= '...';
                                        }

                                        if ($listitem['media-url'] != NULL) {
                                            $currentMessage = substr($listitem['caption'], 0, 35);

                                            if (strlen($listitem['caption']) > 35) {
                                                $currentMessage .= '...';
                                            } elseif (strlen($listitem['caption']) > 55) {
                                                $currentMessage .= '...';
                                            }
                                        }
                                    }
                
                                }
                                

                                
                                if($listitem['post_id'] != NULL){
                                    echo '<i class="far fa-sticky-note"></i>';
                                    echo '<i class="fas fa-reply"></i>' . '  ';
                                }

                                if($listitem['media-url'] != NULL) {
                                    switch ($listitem['media-type']) {
                                        case 'attachment':
                                            echo '<i class="fas fa-paperclip"></i>' . ' ' . $listitem['old_name'];
                                            break;
                                            case 'image':
                                                echo '<i class="fas fa-image"></i>';
                                                echo (isset($listitem['caption']) && !empty($listitem['caption']) && $listitem['caption'] !== null && $listitem['caption'] !== 0) 
                                                    ? ' ' . $currentMessage 
                                                    : ' Photo';
                                                break;
                                            
                                            case 'video':
                                                echo '<i class="fas fa-video"></i>';
                                                echo (isset($listitem['caption']) && !empty($listitem['caption']) && $listitem['caption'] !== null && $listitem['caption'] !== 0) 
                                                    ? ' ' . $currentMessage 
                                                    : ' Video';
                                                break;
                                            
                                            case 'voicenote':
                                                echo '<i class="fas fa-microphone"></i>';
                                                echo  ' (' . $listitem['old_name'] . ')';
                                                break;                                            
                                    }
                                } else {
                                    echo $currentMessage;
                                }
                                echo '<span class="time">';
                                echo $unread;
                                echo '<small>' . smalldate($listitem['message-time']) . '</small>';
                                echo '</span>';
                                
                            }
                        } else {
                            echo '<i>empty</i>';
                        }
                        ?>
                    </span>
                </a>
            </li>
            <?php endif;  unset($group, $currentMessage, $nOpened, $theUser, $unread, $other_user, $delvered, $open); ?>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

<?php //dump($msgCount); ?>

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
    
    // $.fancybox.open('<div class="message"><h2>Hello!</h2><p>You are awesome!</p></div>');
    
    $('[data-fancybox].profile-pic').fancybox({
      buttons: false,
      buttons: [
        "close"
      ],
      baseClass: "profile-view",
      closeExisting: false,
    //   hideScrollbar: true,
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
    
    
    // window.onbeforeunload = function () {
    //   return "Refreshing Page might Cause you to Lose Data";
    // }

    // WebSocket Client
    // Only create the WebSocket if it hasn't been created already
    if (typeof socket === 'undefined') {
        // WebSocket Client
        const socket = new WebSocket('ws://localhost:8081'); // Adjust URL if needed
    
        socket.onopen = function() {
            console.log("Connected to WebSocket server");
        };
    
        socket.onmessage = function(event) {
            const message = JSON.parse(event.data);
            console.log('Received message:', message);
            displayMessage(message);
        };
    
        socket.onerror = function(error) {
            console.error("WebSocket Error: ", error);
        };
    
        socket.onclose = function(event) {
            if (event.wasClean) {
                console.log(`Closed cleanly, code=${event.code}, reason=${event.reason}`);
            } else {
                console.error(`Connection error: ${event.code}`);
            }
        };
    }
    
    // Function to display the message in the chat UI
    function displayMessage(message) {
        const chatContainer = document.getElementById('chat-container');
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.textContent = message.text;
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Example of sending a message through WebSocket (called on form submission or button click)
    function sendMessage(text) {
        const message = {
            action: 'send_message',
            text: text,
            user: 'John Doe',  // Adjust according to your user logic
            timestamp: new Date().toISOString()
        };
    
        socket.send(JSON.stringify(message));  // Send message as JSON
    }
    
    // Function to close the WebSocket connection when it's no longer needed
    // function closeSocket() {
    //     if (socket.readyState === WebSocket.OPEN) {
    //         socket.close();  // Close the connection
    //         console.log("WebSocket connection manually closed.");
    //     }
    // }
    
    // Call `closeSocket()` when you want to close the connection, e.g., on page unload
    // window.addEventListener("beforeunload", closeSocket);

</script>