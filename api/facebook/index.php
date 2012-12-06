<?php
/**
 * Step 1: Require the Slim PHP 5 Framework
 *
 * If using the default file layout, the `Slim/` directory
 * will already be on your include path. If you move the `Slim/`
 * directory elsewhere, ensure that it is added to your include path
 * or update this file path as needed.
 */
require '../Slim/Slim.php';

require '../DataLayer.php';
require '../facebook.php';
require '../Browser.php';

date_default_timezone_set("UTC");

$app = new Slim();
$app->add(new Slim_Middleware_ContentTypes());

// posts 
$app->get('/postToWall', 'postToWall');
$app->get('/users/:accessToken', 'loginUser');
$app->post('/teamUp', 'postToWall');

$app->run();
 
 function loginUser($accessToken) {
 	$facebook = new Facebook(array(
  'appId'  => '275043119266165',
  'secret' => 'd73602b0a96bd94334433c6040a2d1e2',
));

 	$facebook->setAccessToken($accessToken);

 	$userFB = $facebook->getUser();
if ($userFB) {
  try {

    $browser = new Browser();

    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
    $title = $user_profile["name"];
$facebookId = $user_profile["id"];
$facebookUsername = $user_profile["username"];
$facebookLink = $user_profile["link"];
$timezone = $user_profile["timezone"];
$locale = $user_profile["locale"];
$gender = $user_profile["gender"]=="male"?1:0;


  $userParams = array(
            "title"=>$title,
            "facebookId"=>$facebookId,
            "facebookUsername"=>$facebookUsername,
            "facebookLink"=>$facebookLink,
            "timezone"=>$timezone,
            "locale"=>$locale,
            "gender"=>$gender,
            "browserName"=>$browser->getBrowser(),
            "browserVersion"=>$browser->getVersion(),
            "osName"=>$browser->getPlatform()
        );


$userId = fetchUser($userParams);

echo json_encode($userParams);

  } catch (FacebookApiException $e) {
    //echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
  }
}
 }

function postToWall(){
  $request = Slim::getInstance()->request();
  $teamupParams = $request->getBody();
  $postToWall = _postToWall($teamupParams);
}

function _postToWall($teamup) {
	$facebook = new Facebook(array(
  'appId'  => '275043119266165',
  'secret' => 'd73602b0a96bd94334433c6040a2d1e2',
));

// See if there is a user from a cookie
$user = $facebook->getUser();

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
	  $attachment = array(
	    'message' => $teamup['text']
		);

	$result = $facebook->api('/me/feed/', 'post', $attachment);

  } catch (FacebookApiException $e) {
    //echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $user = null;
  }
}

  
}

/*
$this->facebook = new Facebook($appapikey, $appsecret);
$this->facebook->set_user($fb_id, $sessionKey, null, $sessionSecret); 
*/

/*
  $attachment = array(
	    'message' => $teamup['text'],
	    'name' => 'This is my demo Facebook application!',
	    'caption' => "Caption of the Post",
	    'link' => 'http://mylink.com',
	    'description' => 'this is a description',
	    'picture' => 'http://mysite.com/pic.gif',
	    'actions' => array(
	        array(
	            'name' => 'Get Search',
	            'link' => 'http://www.google.com'
	        )
	    )
	);
	*/
 ?>