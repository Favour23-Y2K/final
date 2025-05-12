<?php

$sql = "SELECT chat_type, locked FROM chat WHERE id = '$thischat'";
$chats = mysqli_fetch_assoc(sendQuery($con, $sql));

if (mysqli_affected_rows($con) == '0') {
    echo 'An Error Occured. Reload the Page';
} else {
    if($chats['chat_type'] == '1') {
        $sql2 = "SELECT user_id FROM chat_rel WHERE chat_id = '$thischat' && user_id != '$USER'";
        $other_user = mysqli_fetch_assoc(sendQuery($con, $sql2));
    }elseif($chats['chat_type'] == '2') {
        $group = $thischat;
    }elseif($chats['chat_type'] == '3') {
        $advert = TRUE;
    }elseif($chats['chat_type'] == '0') {
        $service = TRUE;
    }
}