<?php
    // The values of $notif are got from periquisite requested data
    if($notif == TRUE) {
        if(is_array($notif)){
            $notif_count = count($notif);
            echo '<i class="badge">' . $notif_count . '</i>';
        }elseif(is_bool($notif)) {
            echo '<i class="badge" style="padding:.5em"> </i>';
        }else{
            echo '<i class="badge">' . $notif . '</i>';
        }
    }