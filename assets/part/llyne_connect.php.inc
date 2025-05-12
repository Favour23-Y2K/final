<div class="modal fade" id="llyne_connect" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="LlyneConnectLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <?php if(!isset($USER)): ?>
            <div class="modal-content connect_content">
                <div class="modal-header">
                    <span id="exampleModalLongTitle">Connections <i class="far fa-user-circle"></i></span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body connect_body">
                    You are not logged in and cannot view any connections
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        <?php else: ?>
    <div class="modal-content connect_content">
      <div class="modal-header">
          <span id="exampleModalLongTitle">Connections <i class="far fa-user-circle"></i></span>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body connect_body">

        <?php
            $sql = "SELECT * FROM users_rel WHERE user_id_1 = '$USER' || user_id_2 = '$USER' && restriction_id IS NULL ORDER BY req_time DESC";
            $relationships = sendQuery($con, $sql);
            $pending = '0';
            $requested = '0';
            $connected2 = '0';
        ?>

        <body class="fluid">
            <div id="pending_div" class="content">

                <?php $p = 1; $b = []; $pend = []; ?>
                <?php foreach ($relationships as $relationship): if($relationship['status'] == '1' && $relationship['user_id_1'] != $USER): ?>
                    <?php

                    $pending++;

                    $date = normalizeDate($relationship['req_time']);

                    $b[$p]['date'] = $date->format('d/m/y');

                    if($p != 1 && ($b[$p]['date'] != $b[$p-1]['date'])) {
                        echo '<small class="req_time mx-auto">';
                        echo sayday($relationship['req_time']);
                        echo '</small>';
                    } elseif($p == 1) {
                        echo '<small class="req_time mx-auto">';
                        echo sayday($relationship['req_time']);
                        echo '</small>';
                    }
                    $p++

                    ?>
                    <div class="inside">
                        <div class="profile profile_left">
                        <?php
                            $user_id = $relationship['user_id_1']; 
                            array_push($pend, $user_id);
                            include('../assets/part/profile.php.inc'); unset($user_id);
                        ?>
                        </div>
                        <div class="actionkeys">
                            <a href=""class="bg-danger" data-toggle="popover" data-placement="top" data-content="decline request" data-trigger="hover">
                                <i class="far fa-times-circle"></i>
                            </a>
                            <a href="" class="bg-success" data-toggle="popover" data-placement="top" data-content="accept request" data-trigger="hover">
                                <i class="far fa-check-circle"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; endforeach;  $_SESSION['pending'] = $pend;?>
            </div>
            <div id="requested_div" class="content">

                <?php $o = 1; $c = [];  $reqq = []; ?>
                <?php foreach ($relationships as $relationship): if($relationship['status'] == '1' && $relationship['user_id_1'] == $USER):?>
                        <?php

                        $requested++;

                        $date = normalizeDate($relationship['req_time']);

                        $c[$o]['date'] = $date->format('d/m/y');

                        if($o != 1 && ($c[$o]['date'] != $c[$o-1]['date'])) {
                            echo '<small class="req_time mx-auto">';
                            echo sayday($relationship['req_time']);
                            echo '</small>';
                        } elseif($o == 1) {
                            echo '<small class="req_time mx-auto">';
                            echo sayday($relationship['req_time']);
                            echo '</small>';
                        }
                        $o++

                        ?>
                    <div class="inside">
                        <div class="profile profile_left">
                        <?php
                            $user_id = $relationship['user_id_2']; 
                            array_push($reqq, $user_id);
                            include('../assets/part/profile.php.inc'); unset($user_id);
                        ?>
                        </div>
                        <div class="actionkeys">
                            <a href="" class="bg-warning" data-toggle="popover" data-placement="top" data-content="cancel request" data-trigger="hover">
                                <i class="far fa-stop-circle"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; endforeach; $_SESSION['requests'] = $reqq;?>
            </div>
            <div id="connected_div" class="content">
                <?php
                $sql = "SELECT chat_id FROM chat_rel JOIN chat ON chat_rel.chat_id = chat.id WHERE chat_type = '1' && user_id = '$USER' && deleted IS NULL";
                $chat_rels = sendQuery($con, $sql);
                // foreach ($chat_rels as $key) {
                //     dump($key);
                // }
                ?>
                
                <?php $nz = 1; $d = []; $connected = []; ?>
                <?php foreach ($relationships as $relationship): if($relationship['status'] == '2'):?>
                        <?php

                        $connected2++;

                        $date = normalizeDate($relationship['req_time']);

                        $d[$nz]['date'] = $date->format('d/m/y');

                        if($nz != 1 && ($d[$nz]['date'] != $d[$nz-1]['date'])) {
                            echo '<small class="req_time mx-auto">';
                            echo sayday($relationship['req_time']);
                            echo '</small>';
                        } elseif($nz == 1) {
                            echo '<small class="req_time mx-auto">';
                            echo sayday($relationship['req_time']);
                            echo '</small>';
                        }
                        $nz++
                        ?>
                    <div class="inside">
                        <div class="profile profile_left">
                        <?php
                            if($relationship['user_id_1'] == $USER){
                                $user_id = $relationship['user_id_2'];
                            }elseif($relationship['user_id_2'] == $USER){
                                $user_id = $relationship['user_id_1'];
                            }
                            // array_push($connected, $user_id);
                            // array_push($connected[$user_id], $user_id);
                            $connected[$user_id]['user_id'] = $user_id;
                            $this_user = $user_id;
                            include('../assets/part/profile.php.inc'); unset($user_id);
                        ?>
                        </div>
                            <?php
                                foreach ($chat_rels as $chats) {
                                    $goodchat = $chats['chat_id'];
                                    $sql = "SELECT TRUE FROM chat_rel WHERE user_id = '$this_user' && chat_id = '$goodchat' LIMIT 1";
                                    $isTheChat = mysqli_fetch_assoc(sendQuery($con, $sql));
                                    if ($isTheChat == TRUE) {
                                        $privateChat = $goodchat;
                                    break;
                                    }
                                }
                                if(!isset($privateChat)){
                                    $sql =  "INSERT INTO chat(creator_id, chat_type) VALUES('0','1')";
                                    $query = mysqli_query($con, $sql);
                                    if(mysqli_affected_rows($con) == TRUE){
                                        $theNewChat = mysqli_insert_id($con);
                                        $sql = "INSERT INTO chat_rel(user_id, chat_id) VALUES('$this_user','$theNewChat')";
                                        $query = mysqli_query($con, $sql);
                                        if(mysqli_affected_rows($con) == TRUE){
                                            $sql = "INSERT INTO chat_rel(user_id, chat_id) VALUES('$USER','$theNewChat')";
                                            $query = mysqli_query($con, $sql);
                                            $privateChat = $theNewChat;
                                        }
                                    }
                                }
                                foreach($connected as $val => $key){
                                    // dump($key);
                                    if($key['user_id'] == $this_user){
                                        $connected[$val]['chat_id'] = $privateChat;
                                    }
                                }
                                // dump($connected);
                            ?>

                        <div class="actionkeys">
                            <a data-dismiss="modal" data-fancybox="chat" class="quickChat chat-link bg-info" data-type="ajax" href="chat.php?id=<?php echo $privateChat * $_SESSION['safe']; ?>" data-toggle="popover" data-placement="top" data-content="chat" data-trigger="hover">
                                <i class="fas fa-comment"></i>
                            </a>
                            <?php //echo $privateChat ?>

                            <?php 
                                $sql = "SELECT TRUE FROM posts WHERE user_id = '$this_user'";
                                $isPosts = sendQuery($con, $sql);
                            ?>
                            <?php if(mysqli_affected_rows($con) == TRUE): ?>
                                <a data-dismiss="modal" data-fancybox="posts" class="quickChat chat-link bg-secondary" data-type="ajax" href="postPage.php?id=<?php echo $this_user * $_SESSION['safe']; ?>" data-toggle="popover" data-placement="top" data-content="view posts" data-trigger="hover">
                                    <i class="fas fa-sticky-note"></i>
                                </a>
                            <?php endif; unset($this_user, $privateChat, $specifeek, $ourChat, $isPosts);?>
                        </div>
                    </div>
                <?php endif; endforeach; 
                unset($chat_rels);
                $_SESSION['friends'] = $connected;
                ?>
            </div>
            <div id="search_div" class="content">

                <script>
                    function showResult(str){
                        if (str.length==0) {
                            document.grtElementById("livesearch").innerHTML="";
                            document.grtElementById("livesearch").style.border="0px";
                            return;
                        }
                        var xmlhttp=new XMLHttpRequest();
                        xmlhttp.onreadystatechange=function() {
                            if (this.readyState==4 && this.status==200) {
                                document.getElementById("livesearch").innerHtml=this.responseText;
                                document.getElementById("livesearch").style.border="1px solid #A5ACB2";
                            }
                        }
                        xmlhttp.open("GET","livesearch.php?q="+str,true);
                        xmlhttp.send();
                    }
                </script>

                <form class="col-12">
                    <!-- <br /> -->
                    <div class="input-group col-12">
                        <div class="form-outline col-12">
                            <label class="form-label" for="search"><h4>Search <i class="mx-auto fas fa-search fa-xs"></i></h4></label>
                            <input class="col-12" id="search" type="search" onkeyup="showResult(this.value)" />
                        </div>
                    </div>
                    <div class="col-12" id="livesearch"></div>
                </form>

                <hr>

                <div class="qr">
                    <style>
                        #qr-reader {
                            margin: 0 auto;
                            width: 15em;
                            height: 15em;
                            background-color: var(--info);
                            object-fit: cover; /* Ensures the video covers the entire div */
                        }
                    </style>

                    <div id="qr-reader"></div>
                    <div class="qr-reader-results"></div>
                    <div class="col-12" style="text-align: center">
                        My QR Code
                    </div>
                </div>

                <hr>
                    
                <script>
                    var resultContainer = document.getElementById('qr-reader-results');
                    var lastResult, countResults = 0;
                    var html5QrcodeScanner;
                                    
                    function onScanSuccess(decodedText, decodedResult) {
                        if (decodedText !== lastResult) {
                            ++countResults;
                            lastResult = decodedText;
                            // Handle on success condition with the decoded message.
                            
                            // Redirect to the decoded URL
                            window.location.href = decodedText;
                            // console.log(`Scan result ${decodedText}`, decodedResult);
                        }
                    }
                
                    function startScanner() {
                        html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
                        html5QrcodeScanner.render(onScanSuccess);
                    }
                
                    function stopScanner() {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.clear();
                        }
                    }
                
                    // Set up Intersection Observer to watch for the QR reader's visibility
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                startScanner();  // Start scanning when in viewport
                            } else {
                                stopScanner();   // Stop scanning when not in viewport
                            }
                        });
                    });
                
                    // Observe the QR reader div
                    observer.observe(document.getElementById('qr-reader'));
                </script>
                

                <div class="quickAdd">
                    <h4 class="col-12">Quick Add</h4>
                    <?php
                        $quickADD = [];
                        foreach($_SESSION['friends'] as $getFriends){
                            $tofind = $getFriends['user_id'];
                            // dump($tofind);
                            $sql = "SELECT * FROM users_rel WHERE (user_id_1 = '$tofind' || user_id_2 = '$tofind') && (user_id_1 != $USER && user_id_2 != $USER) && (`status` = '2' && restriction_id IS NULL)";
                            $found = sendQuery($con, $sql);
                            foreach ($found as $else){
                                // dump($else);
                                if($tofind == $else['user_id_1']){
                                    array_push($quickADD, $else['user_id_2']);
                                }elseif($tofind == $else['user_id_2']){
                                    array_push($quickADD, $else['user_id_1']);
                                }
                            };
                        }
                        $relevance = array_count_values($quickADD);
                        arsort($relevance);
                        // Define removed suggestion (same as restricted) as user_rel status 4
                    ?>
                    <?php foreach($relevance as $meet => $count): ?>
                        <?php $sql = "SELECT * from users_rel WHERE (user_id_1 = '$meet' && user_id_2 = '$USER') || (user_id_1 = '$USER' && user_id_2 = '$meet') && (`status` = '2')"; ?>
                        <?php if(mysqli_fetch_assoc(sendQuery($con, $sql)) != TRUE): ?>
                            <div class="inside">
                                <div class="profile profile_left">
                                <?php
                                    $user_id = $meet;
                                    include('../assets/part/profile.php.inc'); unset($user_id);
                                ?>
                                </div>
                                <div class="actionkeys">
                                    <a href=""class="bg-secondary" data-toggle="popover" data-placement="top" data-content="Remove Suggestion" data-trigger="hover">
                                        <i class="far fa-times-circle"></i>
                                    </a>
                                    <a href="" class="bg-primary" data-toggle="popover" data-placement="top" data-content="Add User" data-trigger="hover">
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach;?>
                </div>
            </div>
        </body>
        
      </div>
      <?php unset($relationships); ?>
      <div class="modal-footer">
        <nav>
            <ul>
                <li id="pending" class="passive text-warning">
                    <span>pending</span>
                    <i class="mx-auto fas fa-cookie-bite fa-xs"></i>
                    <small>
                        <?php
                        if($pending >= '1') {
                            echo '(' . $pending . ')';
                        }
                        ?>
                    </small>
                </li>
                <li id="requested" class="passive text-danger">
                    <span>requested</span>
                    <i class="mx-auto fas fa-cocktail fa-xs"></i>
                    <small>
                        <?php
                        if($requested >= '1') {
                            echo '(' . $requested . ')';
                        }
                        ?>
                    </small>
                </li>
                <li id="connected" class="passive text-success">
                    <span>connected</span>
                    <i class="mx-auto fas fa-cannabis fa-xs"></i>
                    <small>
                        <?php
                        if($connected2 >= '1') {
                            echo '(' . $connected2 . ')';
                        }
                        ?>
                    </small>
                </li>
                <li id="search" class="passive text-info">
                    <span>
                        search <i class="mx-auto fas fa-search fa-xs"></i>
                    </span>
                    <i class="mx-auto fas fa-qrcode fa-xs"></i>
                    <small>
                        Quick Add
                    </small>
                </li>
            </ul>
        </nav>
      </div>
    </div>
        <?php endif; ?>
  </div>
</div>

<script>
    $(document).ready(function() {

        $('#llyne_connect>.modal-dialog>.modal-content>.modal-footer>nav>ul>li#pending').addClass('active');
        $('#llyne_connect>.modal-dialog>.modal-content>.modal-footer>nav>ul>li#pending').removeClass('passive');
        $('#pending_div').css('display', 'block');
        
        $('#llyne_connect>.modal-dialog>.modal-content>.modal-footer>nav>ul>li').click(function() {

            $('#llyne_connect>.modal-dialog>.modal-content>.modal-footer>nav>ul>li').addClass('passive');
            $('#llyne_connect>.modal-dialog>.modal-content>.modal-footer>nav>ul>li').removeClass('active');

            var theid = "#llyne_connect>.modal-dialog>.modal-content>.modal-footer>nav>ul>li" + "#" + this.id;
            $(theid).addClass('active');
            $(theid).removeClass('passive');
            
            $('div.content').css('display', 'none');
            var target = '#' + this.id + "_div";
            $(target).css('display', 'block');
            
        });
    
    });
</script>