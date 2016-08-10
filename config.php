<?php


$PARTNER_NAME = 'Partner Name';
$PARTNER_BN = 'parner_bn';  //Optiona: You can ask PayPal Relationshop Manager for a "BN" code

if(isset($_GET['live'])){ //if it is live.

	//Enter Your Application Credentials
	$PP_ENVIRONEMNT_NAME = 'live';
	$PP_APP_CLIENT_ID = 'YOURS HERE';
	$PP_APP_SECRET = 'YOURS HERE';
	$PP_RETURN_URL ='YOURS HERE'; //Live return URL must match exactly what is set on developer.paypal.com for the REST APP

	$PRODUCTION_MODE = true; //since this is live

	$PAYPAL_REST_DOMAIN = 'https://api.paypal.com'; // Use this for production
	$PAYPAL_AUTH_DOMAIN = 'https://www.paypal.com'; // Use this for production

}else{ //otherwise lets use the sanbox.

	//Enter Your Sandbox Application Credentials
	$PP_ENVIRONEMNT_NAME = 'sandbox';
	$PP_APP_CLIENT_ID = 'YOURS HERE';
	$PP_APP_SECRET = 'YOURS HERE';
	$PP_RETURN_URL ='YOURS HERE'; //sandbox return URL must match exactly what is set on developer.paypal.com for the REST APP


	$PRODUCTION_MODE = false; //set to false for sandbox

	$PAYPAL_REST_DOMAIN = 'https://api.sandbox.paypal.com'; //Use this for sandbox
	$PAYPAL_AUTH_DOMAIN = 'https://www.sandbox.paypal.com'; //Use this for sandbox

}

//Lets Create the OAuth Login link for later. 
$OAUTH_LINK =  $PAYPAL_AUTH_DOMAIN.'/webapps/auth/protocol/openidconnect/v1/authorize?';
$OAUTH_LINK .= 'scope='.urlencode('https://uri.paypal.com/services/paypalhere email phone profile openid https://uri.paypal.com/services/paypalattributes address https://uri.paypal.com/services/paypalattributes/business'); //These are the scopes required separated by spaces. Ensure your REST APP is enabled for each on developer.paypal.com
$OAUTH_LINK .= '&response_type=code';
$OAUTH_LINK .= '&client_id='.$PP_APP_CLIENT_ID; //Your apps client ID from the config file
$OAUTH_LINK .= '&redirect_uri='.urlencode($PP_RETURN_URL); //Redirect URL must match exactly what is set on developer.paypal.com

//Lets import all the varios PayPal information for each country into a variable.
$PPH_DATA = json_decode(file_get_contents('config-paypal.json'));

// The US onboarding flow is special because a return URL and Merchant info can be passed into it.
// Sadly it is the only flow with these features at this time.
// lets add the $OAUTH_LINK as the return URL for the US flow to grant permisions right after.
$PPH_DATA->US->account_onboard_link = str_replace('{partner_name}', $PARTNER_NAME, $PPH_DATA->US->account_onboard_link);
$PPH_DATA->US->account_onboard_link = str_replace('{partner_id}', $PARTNER_BN, $PPH_DATA->US->account_onboard_link);
$PPH_DATA->US->account_onboard_link.='&returnurl='.urlencode($OAUTH_LINK);



// Want to include some very simple Encrypt / Decrypt functions we will use these for encrypting the refresh token we put in the SDK Token
// Make sure you update the salt to be unique and your own.

function encrypt($decrypted, $password="myPassword123", $salt='!kQm*mySaltGoesHere') { 
	// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
	$key = hash('SHA256', $salt . $password, true);
	// Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)
	srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
	if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
	// Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
	$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
	// We're done!
	return $iv_base64 . $encrypted;
} 

function decrypt($encrypted, $password="myPassword123", $salt='!kQm*mySaltGoesHere') {
	// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
	$key = hash('SHA256', $salt . $password, true);
	// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
	$iv = base64_decode(substr($encrypted, 0, 22) . '==');
	// Remove $iv from $encrypted.
	$encrypted = substr($encrypted, 22);
	// Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
	$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
	// Retrieve $hash which is the last 32 characters of $decrypted.
	$hash = substr($decrypted, -32);
	// Remove the last 32 characters from $decrypted.
	$decrypted = substr($decrypted, 0, -32);
	// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
	if (md5($decrypted) != $hash) return false;
	// Yay!
	return $decrypted;
}




