<?php


$PARTNER_NAME = 'Partner Name';
$PARTNER_BN = 'parner_bn';

if(isset($_GET['live'])){ //if it is live.

	//Enter Your Application Credentials
	$PP_APP_CLIENT_ID = '';
	$PP_APP_SECRET = '';
	$PP_RETURN_URL =''; //Live return URL must match exactly what is set on developer.paypal.com for the REST APP

	$PRODUCTION_MODE = true; //since this is live

	$PAYPAL_REST_DOMAIN = 'https://api.paypal.com'; // Use this for production
	$PAYPAL_AUTH_DOMAIN = 'https://www.paypal.com'; // Use this for production

}else{ //otherwise lets use the sanbox.

	//Enter Your Sandbox Application Credentials
	$PP_APP_CLIENT_ID = '';
	$PP_APP_SECRET = '';
	$PP_RETURN_URL =''; //sandbox return URL must match exactly what is set on developer.paypal.com for the REST APP


	$PRODUCTION_MODE = false; //set to false for sandbox

	$PAYPAL_REST_DOMAIN = 'https://api.sandbox.paypal.com'; //Use this for sandbox
	$PAYPAL_AUTH_DOMAIN = 'https://www.sandbox.paypal.com'; //Use this for sandbox

}

//Lets Create the OAuth Login link for later. 
$OAUTH_LINK =  $PAYPAL_AUTH_DOMAIN.'/webapps/auth/protocol/openidconnect/v1/authorize?';
$OAUTH_LINK .= 'scope='.urlencode('https://uri.paypal.com/services/paypalhere email phone profile openid https://uri.paypal.com/services/paypalattributes address'); //These are the scopes required separated by spaces. Ensure your REST APP is enabled for each on developer.paypal.com
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







