<?php
/**
 * @file
 * Take the user when they return from Twitter. Get access tokens.
 * Verify credentials and redirect to based on response from Twitter.
 */

/* Start session and load lib */
session_start();
require_once('twitteroauth.php');
require_once('func.php');

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  $_SESSION['oauth_status'] = 'oldtoken';
  header('Location: /php/twitterclear.php');
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code) 
{

	$cred = $connection->get('account/verify_credentials');
	
	if ($cred)
	{
		
		$user = checkLoginCookies();		
		
		$wasLoggedIn = $user;
	
		if (!$user)
		{
			connect();
			$user = getTwitterUser($cred->id);
			
			if (!$user)
			{		
				$id = createUser("","",$cred->screen_name,$cred->name);
				
				$user = sqlObject("SELECT * FROM users WHERE id=$id");
				
			}
			
			login($user);
		}

		
		if ($user)
		{
			connect();
			
			if (!$user->avatar)
			{
				$avatar = file_get_contents($cred->profile_image_url);				
				makeAvatar($user,$avatar);			
			}
		
		
			
			sql("UPDATE users SET twitter_id=0 WHERE twitter_id=".sqlVar($cred->id));
			
		
			$q = "UPDATE users SET";
			$q .= " twitter_auth=".sqlVar($access_token['oauth_token']);
			$q .= ",twitter_name=".sqlVar($cred->screen_name);
			$q .= ",twitter_id=".sqlVar($cred->id);
			$q .= " WHERE id=".$user->id;			
			sql($q);			
			disconnect();
			
			//header("Location: /settings.php");			
		}		

		if ($wasLoggedIn)
			header("Location: /settings.php");		
		else	
			header("Location: /");		
	
	}else
 	 die("Sorry, could not get credentials");
	

    
} else {
  /* Save HTTP status for error dialog on connnect page.*/
  //header('Location: /php/twitterclear.php');
  
  die("Sorry, there was an error");
}
?>