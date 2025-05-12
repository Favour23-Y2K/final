<?php

// Zodiac calculator

$zodiac = array(
  "Aries" => array(
              "nickname"=>'The Ram',
              "start"=>'03-21',
              "end"=>'04-19',
              "modality"=>'cardinal',
              "element"=>'fire',
              "color"=>'red',
              "code"=>'&#9800',
            ),
  "Taurus" => array(
              "nickname"=>'The Bull',
             "start"=>'04-20',
             "end"=>'05-20',
             "modality"=>'fixed',
             "element"=>'earth',
             "color"=>'green',
             "code"=>'&#9801',
  ),
  "Gemini" => array(
              "nickname"=>'The Twins',
             "start"=>'05-21',
             "end"=>'06-20',
             "modality"=>'mutable',
             "element"=>'air',
             "color"=>'white',
             "code"=>'&#9802',
  ),
  "Cancer" => array(
              "nickname"=>'The Crab',
             "start"=>'06-21',
             "end"=>'07-22',
             "modality"=>'cardinal',
             "element"=>'water',
             "color"=>'blue',
             "code"=>'&#9803',
  ),
  "Leo" => array(
              "nickname"=>'The Lion',
              "start"=>'07-23',
              "end"=>'08-22',
              "modality"=>'fixed',
              "element"=>'fire',
              "color"=>'gold',
              "code"=>'&#9804',
  ),
  "Virgo" => array(
              "nickname"=>'The Virgin',
              "start"=>'08-23',
              "end"=>'09-22',
              "modality"=>'mutable',
              "element"=>'earth',
              "color"=>'grey',
              "code"=>'&#9805',
  ),
  "Libra" => array(
              "nickname"=>'The Scales',
              "start"=>'09-23',
              "end"=>'10-22',
              "modality"=>'cardinal',
              "element"=>'air',
              "color"=>'black',
              "code"=>'&#9806',
  ),
  "Scorpio" => array(
              "nickname"=>'The Scorpion',
              "start"=>'10-23',
              "end"=>'11-21',
              "modality"=>'fixed',
              "element"=>'water',
              "color"=>'violet',
              "code"=>'&#9807',
  ),
  "Sagittarius" => array(
              "nickname"=>'The Archer',
              "start"=>'11-22',
              "end"=>'12-21',
              "modality"=>'mutable',
              "element"=>'fire',
              "color"=>'sienna',
              "code"=>'&#9808',
  ),
  "Capricorn" => array(
              "nickname"=>'The Goat',
              "start"=>'12-22',
              "end"=>'01-19',
              "modality"=>'cardinal',
              "element"=>'earth',
              "color"=>'navy',
              "code"=>'&#9809',
  ),
  "Aquarius" => array(
              "nickname"=>'The Water Carrier',
              "start"=>'01-20',
              "end"=>'02-18',
              "modality"=>'fixed',
              "element"=>'air',
              "color"=>'lilac',
              "code"=>'&#9810',
  ),
  "Pisces" => array(
              "nickname"=>'The Fish',
             "start"=>'02-19',
             "end"=>'03-20',
             "modality"=>'mutable',
             "element"=>'water',
             "color"=>'lavender',
             "code"=>'&#9811',
  ),
);

$modality = array(
  "cardinal" => array(
            "keywords"=>'Action, Dynamic, Initiative, Great force',
            "code"=>'&#128781',
  ),
  "fixed" => array(
            "keywords"=>'Resistance to Change, Great Willpower, Inflexible',
            "code"=>'&#128788',
  ),
  "mutable" => array(
            "keywords"=>'Adaptability, Flexibility, Resourcefulness',
            "code"=>'&#9791',
  ),
);

$elements = array(
    "air" => array(
              "direction"=>'East',
              "keywords"=>'Communication, Socialization, Conceptualization',
              "code"=>'&#128769',
              "polarity"=>'male (positive)',
    ),
    "fire" => array(
              "direction"=>'South',
              "keywords"=>'Assertion, Drive, Willpower',
              "code"=>'&#128770',
              "polarity"=>'male (positive)',
    ),
    "earth" => array(
              "direction"=>'North',
              "keywords"=>'Practicality, Caution, Material World',
              "code"=>'&#128771',
              "polarity"=>'female (negative)',
    ),
    "water" => array(
              "direction"=>'West',
              "keywords"=>'Emotion, Empathy, Sensitivity',
              "code"=>'&#128772',
              "polarity"=>'female (negative)',
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
  }

  unset($starsign, $date, $mod, $elem, $start, $end, $daystuff);

}



?>
