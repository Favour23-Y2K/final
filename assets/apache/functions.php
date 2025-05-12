<?php

function sendQuery($con, $sql)
{
    $query = mysqli_query($con, $sql);
 
    if (!$query) {
        echo mysqli_error($con);
        exit;
    } else {
        return $query;
    }
}


function dump($stuff)
{
    echo '<pre>';
    print_r($stuff);
    echo '</pre>';
}


function searchzodiac($date, $array) {
  foreach($array as $key => $val) {
    if ($val['start'] <= $date && $date <= $val['end']) {
      return $key;
    }elseif(($val['start'] > $val['end']) && ($val['start'] <= $date || $val['end'] >= $date)){
      return $key;
    }
  }
  return null;
}

function build_sorter($priority, $second) {
  return function ($a, $b) use ($priority, $second) {
      if ($priority == NULL) {
          return strnatcmp($b[$second], $a[$priority]);
      }else {
        return strnatcmp(
          isset($b[$priority]) ? $b[$priority] : '',
          isset($a[$priority]) ? $a[$priority] : ''
        );      
      }
  };
}


function normalizeDate($date) {

  if (is_string($date)) {
    try {
      $date = new DateTime($date, new DateTimeZone('UTC'));
    } catch (Exception $e) {
      // Handle invalid date format or other errors
      return null; // Return null or handle the error as needed
    }
  }
  
  // echo $date;
  if (isset($_COOKIE['timezone']) && !empty($_COOKIE['timezone'])) {
    try {
      // Set the user's timezone from the cookie
      $date->setTimezone(new DateTimeZone($_COOKIE['timezone']));
    } catch (Exception $e) {
      // Handle invalid timezone case
      return $date->format('Y-m-d H:i:s'); // Return the original date in case of error
    }
  }
  // echo $date;
  // return $date;
  return $date;
}



function datemodifier($date) {
  
  $date = normalizeDate($date);

  $diff = date_diff($date, date_create_from_format('y-m-d', date('y-m-d')));

  if ($diff->y >= '1') { // is a year or more

    return $date->format('h\:i A - d/m/y');
    
  } elseif ($diff->y == '0') { // is within a year
    
    if ($diff->d == '1' || (date('d') - $date->format('d') == '1')) { // is yesterday

      return $date->format('h\:i A') . ' yesterday';
      
    } elseif ($diff->h <= '24' && $diff->d == '0') { // is within today (24 hrs)
      
      if ($diff->h == '0') { // is within an hour
      
        if ($diff->i == '0') { // is within a minute
          return $diff->s . ' seconds ago';
        }
        if ($diff->i == '1') {
          return '1 minute ago';
        }
        return $diff->i . ' minutes ago';
      }

      if ($diff->h == '1') {
        return 'an hour ago';
      } else {
        return $diff->h . ' hours ago';
      }

    }

    return $date->format('h\:i A - l\, jS F');
    
  }
}

function sayday($date) {

  $date = normalizeDate($date);

  $diff = date_diff($date, date_create_from_format('y-m-d', date('y-m-d')));

  if ($diff->y >= '1') { // is a year or more

    return $date->format('h\:i A - d/m/y');
    
  } elseif ($diff->y == '0') { // is within a year
    
    if ($diff->d == '1' || (date('d') - $date->format('d') == '1')) { // is yesterday

      return 'yesterday';
      
    } elseif ($diff->h <= '24' && $diff->d == '0') { // is within today (24 hrs)
      
      return 'today';

    }

    return $date->format('l\, jS F');
  }
}

function saydaysmall($date) {

  $date = normalizeDate($date);

  $diff = date_diff($date, date_create_from_format('y-m-d', date('y-m-d')));

    if ($diff->d == '1' || (date('d') - $date->format('d') == '1')) { // is yesterday
      
      return 'yesterday';
      
    } elseif ($diff->h <= '24' && $diff->d == '0') { // is within today (24 hrs)
      
      return 'today';

    }

    return 'older';
}

function smalldate($date) {

  $date = normalizeDate($date);

  $diff = date_diff($date, date_create_from_format('y-m-d', date('y-m-d')));

  if ($diff->days >= '2') {
    return $date->format('d/m/y');
  } elseif ($diff->days == '1' || (date('d') - $date->format('d') == '1')) {
    return 'yesterday';
  } else {
    return $date->format('h\:i A');
  }
}

function statusTime($date) {

  $date = normalizeDate($date);

  $diff = date_diff($date, date_create_from_format('y-m-d', date('y-m-d')));

  if ($diff->y >= '1') { // is a year or more

    return $date->format('d/m/y - h\:i A');
    
  } elseif ($diff->y == '0') { // is within a year
    
    if ($diff->d == '1' || (date('d') - $date->format('d') == '1')) { // is yesterday

      return 'yesterday, ' . $date->format('h\:i A');
      
    } elseif ($diff->h <= '24' && $diff->d == '0') { // is within today (24 hrs)
      
      if ($diff->h == '0') { // is within an hour
      
        if ($diff->i == '0') { // is within a minute
          return $diff->s . 'seconds ago';
        }
        if ($diff->i == '1') {
          return '1 minute ago';
        }
        return $diff->i . ' minutes ago';
      }

      $today = new DateTime('today');
      $todaysDate = $today->format("Y-m-d");
      $realDay = normalizeDate($todaysDate);
      if($date->format('d') == $realDay->format('d')) {
        return 'today, ' . $date->format('h\:i A');
      } else {
        return 'yesterday, ' . $date->format('h\:i A');
      }
    }

    return $date->format('jS F, h\:i A');
    
  }
}

function encrypt ($plaintext, $key) {
    
  $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
  $iv = openssl_random_pseudo_bytes($ivlen);
  $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
  $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
  $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );

  return $ciphertext;

}

function decrypt($ciphertext, $key) {

  $c = base64_decode($ciphertext);
  $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
  $iv = substr($c, 0, $ivlen);
  $hmac = substr($c, $ivlen, $sha2len=32);
  $ciphertext_raw = substr($c, $ivlen+$sha2len);
  $real_data = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
  $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
  {
      return $real_data;
  }
}

function sortByTimeOnly($key){
  return function($a, $b) use ($key) {
    return strnatcmp($b[$key], $a[$key]);
  };
}

function sortByAdmin($key){
  return function($a, $b) use ($key) {
    return strnatcmp($a[$key], $b[$key]);
  };
}

// function getChat($theUser, $array){
  
//   foreach ($array as $chats) {
//     $chat = $chats['chat_id'];
//     $sql = "SELECT chat_id FROM chat_rel WHERE chat_id = '$specifeek' && user_id = '$this_user' ORDER BY ASC LIMIT 1";
//     $na_eem =sendQuery($con, $sql);
//     if(mysqli_affected_rows($con) == TRUE){
//         return 'chat';
//     }
//   }

// }


// function getThumbnailUrl($mediaUrl, $isVideo = false) {
//   // Extract the filename from the URL
//   $filename = basename($mediaUrl);
  
//   // Determine the media type prefix
//   $mediaTypePrefix = $isVideo ? POST_VIDEO : POST_IMAGE;
  
//   // Generate the MD5 hash of the media type prefix and filename
//   $thumbnailName = md5($mediaTypePrefix . $filename);
  
//   // Construct and return the thumbnail URL
//   return THUMBNAILS . '/' . $thumbnailName . '.png';
// }

// function generateVideoThumbnail($videoPath, $thumbnailPath) {
//     $ffmpeg = '/usr/bin/ffmpeg';
//     $command = "$ffmpeg -i $videoPath -vframes 1 -an -s 720x720 -ss 1 $thumbnailPath";
//     exec($command);
//     return $thumbnailPath;
// }