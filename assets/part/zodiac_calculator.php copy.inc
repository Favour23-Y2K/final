<?php

//Zodiac calculator

// $zodiac = array(
//   "Aries" => array(
//               "start"=>date('03-21'),
//               "end"=>date('04-19'),
//               "code"=>'&#9800',
//             ),
//   "Taurus" => array(
//              "start"=>date('04-20'),
//              "end"=>date('05-20'),
//              "code"=>'&#9801',
//   ),
//   "Gemini" => array(
//              "start"=>date('05-21'),
//              "end"=>date('06-20'),
//              "code"=>'&#9802',
//   ),
//   "Cancer" => array(
//              "start"=>date('06-21'),
//              "end"=>date('07-22'),
//              "code"=>'&#9803',
//   ),
//   "Leo" => array(
//               "start"=>date('07-23'),
//               "end"=>date('08-22'),
//               "code"=>'&#9804',
//   ),
//   "Virgo" => array(
//               "start"=>date('08-23'),
//               "end"=>date('09-22'),
//               "code"=>'&#9805',
//   ),
//   "Libra" => array(
//               "start"=>date('09-23'),
//               "end"=>date('10-22'),
//               "code"=>'&#9806',
//   ),
//   "Scorpio" => array(
//               "start"=>date('10-23'),
//               "end"=>date('11-21'),
//               "code"=>'&#9807',
//   ),
//   "Sagitaurus" => array(
//               "start"=>date('11-22'),
//               "end"=>date('12-21'),
//               "code"=>'&#9808',
//   ),
//   "Capricon" => array(
//               "start"=>date('12-22'),
//               "end"=>date('01-19'),
//               "code"=>'&#9809',
//   ),
//   "Aquarius" => array(
//               "start"=>date('01-20'),
//               "end"=>date('02-18'),
//               "code"=>'&#9810',
//   ),
//   "Pisces" => array(
//              "start"=>date('02-19'),
//              "end"=>date('03-20'),
//              "code"=>'&#9811',
//   ),
// );

$zodiac = array(
  "Aries" => array(
              "nickname"=>'The Ram',
              "start"=>date('04-19'),
              "end"=>date('05-13'),
              "modality"=>'cardinal',
              "element"=>'fire',
              "color"=>'red',
              "code"=>'&#9800',
            ),
  "Taurus" => array(
              "nickname"=>'The Bull',
             "start"=>date('05-14'),
             "end"=>date('06-19'),
             "modality"=>'fixed',
             "element"=>'earth',
             "color"=>'green',
             "code"=>'&#9801',
  ),
  "Gemini" => array(
              "nickname"=>'The Twins',
             "start"=>date('06-20'),
             "end"=>date('07-20'),
             "modality"=>'mutable',
             "element"=>'air',
             "color"=>'white',
             "code"=>'&#9802',
  ),
  "Cancer" => array(
              "nickname"=>'The Crab',
             "start"=>date('07-21'),
             "end"=>date('08-09'),
             "modality"=>'cardinal',
             "element"=>'water',
             "color"=>'blue',
             "code"=>'&#9803',
  ),
  "Leo" => array(
              "nickname"=>'The Lion',
              "start"=>date('08-10'),
              "end"=>date('09-15'),
              "modality"=>'fixed',
              "element"=>'fire',
              "color"=>'gold',
              "code"=>'&#9804',
  ),
  "Virgo" => array(
              "nickname"=>'The Virgin',
              "start"=>date('09-16'),
              "end"=>date('10-29'),
              "modality"=>'mutable',
              "element"=>'earth',
              "color"=>'grey',
              "code"=>'&#9805',
  ),
  "Libra" => array(
              "nickname"=>'The Scales',
              "start"=>date('10-30'),
              "end"=>date('11-22'),
              "modality"=>'cardinal',
              "element"=>'air',
              "color"=>'black',
              "code"=>'&#9806',
  ),
  "Scorpio" => array(
              "nickname"=>'The Scorpion',
              "start"=>date('11-23'),
              "end"=>date('11-28'),
              "modality"=>'fixed',
              "element"=>'water',
              "color"=>'violet',
              "code"=>'&#9807',
  ),
  "Ophiuchus" => array(
              "nickname"=>'Serpent Bearer',
              "start"=>date('11-30'),
              "end"=>date('12-17'),
              "modality"=>false,
              "element"=>false,
              "color"=>'cornflowerblue',
              "code"=>'&#9934',
  ),
  "Sagitaurus" => array(
              "nickname"=>'The Archer',
              "start"=>date('12-18'),
              "end"=>date('01-18'),
              "modality"=>'mutable',
              "element"=>'fire',
              "color"=>'sienna',
              "code"=>'&#9808',
  ),
  "Capricon" => array(
              "nickname"=>'The Goat',
              "start"=>date('01-19'),
              "end"=>date('02-15'),
              "modality"=>'cardinal',
              "element"=>'earth',
              "color"=>'navy',
              "code"=>'&#9809',
  ),
  "Aquarius" => array(
              "nickname"=>'The Water Carrier',
              "start"=>date('02-16'),
              "end"=>date('03-11'),
              "modality"=>'fixed',
              "element"=>'air',
              "color"=>'lilac',
              "code"=>'&#9810',
  ),
  "Pisces" => array(
              "nickname"=>'The Fish',
             "start"=>date('03-12'),
             "end"=>date('04-17'),
             "modality"=>'mutable',
             "element"=>'water',
             "color"=>'lavender',
             "code"=>'&#9811',
  ),
);

$modality = array(
  "cardinal" => array(
            "keywords"=>'Action, Dynamic, Initiative, Great force',
            // "symbol"=>'U+1f70D',
            "code"=>'&#128781',
  ),
  "fixed" => array(
            "keywords"=>'Resistance to Change, Great Willpower, Inflexible',
            // "symbol"=>'U+1f714',
            "code"=>'&#128788',
  ),
  "mutable" => array(
            "keywords"=>'Adaptability, Flexibility, Resourcefulness',
            // "symbol"=>'U+263F',
            "code"=>'&#9791',
  ),
);

$elements = array(
  
    "air" => array(
              "direction"=>'East',
              "keywords"=>'Communication, Socialization, Conceptualization',
              "code"=>'&#128769',
              "polarity"=>'male(positive)',
    ),
    "fire" => array(
              "direction"=>'South',
              "keywords"=>'Assertion, Drive, Willpower',
              "code"=>'&#128770',
              "polarity"=>'male(positive)',
    ),

    "earth" => array(
              "direction"=>'North',
              "keywords"=>'Practicality, Caution, Material World',
              "code"=>'&#128771',
              "polarity"=>'female(negative)',
    ),
    "water" => array(
              "direction"=>'West',
              "keywords"=>'Emotion, Empathy, Sensitivity',
              "code"=>'&#128772',
              "polarity"=>'female(negative)',
    ),

);

if(isset($query['dob'])) {
  $signinfo = [];

  $date = date('m-d', strtotime($query['dob']));
  $starsign = searchzodiac($date, $zodiac);

  $mod = $zodiac[$starsign]['modality'];
  $elem = $zodiac[$starsign]['element'];
  
  $rawstart = explode( '-', $zodiac[$starsign]['start']);
  $rawend = explode( '-', $zodiac[$starsign]['end']);

  $start = '2000'. '-' . $rawstart['0'];
  $start .= '-' . $rawstart['1'];

  $end = '2000'. '-' . $rawend['0'];
  $end .= '-' . $rawend['1'];

  $signinfo['sign'] = $starsign;
  $signinfo['nickname'] = $zodiac[$starsign]['nickname'];
  $signinfo['sign-code'] = $zodiac[$starsign]['code'];
  $signinfo['start'] = date("jS F", strtotime($start));
  $signinfo['end'] = date("jS F", strtotime($end));
  $signinfo['color'] = $zodiac[$starsign]['color'];
 
  if($mod && $elem != false) {
    $signinfo['mod'] = $mod;
    $signinfo['mod-code'] = $modality[$mod]['code'];
    $signinfo['mod-keyword'] = $modality[$mod]['keywords'];
    $signinfo['elem'] = $elem;
    $signinfo['elem-code'] = $elements[$elem]['code'];
    $signinfo['elem-polarity'] = $elements[$elem]['polarity'];
    $signinfo['elem-direction'] = $elements[$elem]['direction'];
    $signinfo['elem-keyword'] = $elements[$elem]['keywords'];
  }elseif($starsign == 'Ophiuchus') {
    $signinfo['mod'] = 'zero modality';
    $signinfo['mod-code'] = false;
    $signinfo['elem'] = 'non-elemental';
    $signinfo['elem-code'] = false;
  }
  
  unset($starsign, $date, $mod, $elem, $start, $end, $daystuff);
}

?>