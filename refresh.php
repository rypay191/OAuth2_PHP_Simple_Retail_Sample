<? include_once('config.php'); 

//This endpoint needs to handle the refresh requests from the PayPal Retail SDK
//In your situation you would likely use a session / authentication method that m
header('Content-Type: application/json');

if(isset($_GET['refresh_token'])){ //there should be the encrpted urlencoded refresh token included.  (You may have stored this in a databse, and simply have a way to identify user)

  $refresh_token = decrypt($_GET['refresh_token']);  

  // This is an example of how you can refresh the access token and check the status of Access tokens. 
  // You make this call to create fresh access tokens.

  $refresh_url = $PAYPAL_REST_DOMAIN.'/v1/identity/openidconnect/tokenservice?';
  $refresh_url .= 'grant_type=refresh_token';
  $refresh_url .= '&refresh_token='.urlencode($refresh_token);
  $refresh_url .= '&scope='.urlencode('https://uri.paypal.com/services/paypalhere');


  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $refresh_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $PP_APP_CLIENT_ID.":".$PP_APP_SECRET);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  $response = curl_exec( $ch );

  //we will just dump out the same format so the SDK can pick it up:
  echo $response;

}else{

 die("No Refrsh Token Was Provided");

}

