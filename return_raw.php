<?php

include_once('config.php'); 


//Get the code returned from the O-Auth redirect and exchange that for an access token and refresh token for the merchant
$code = $_GET['code'];

$ch = curl_init( $PAYPAL_REST_DOMAIN."/v1/identity/openidconnect/tokenservice" );

curl_setopt_array( $ch,
  array(
    CURLOPT_POST           => 1,
    CURLOPT_POSTFIELDS     => 'scope=http://uri.paypal.com/services/paypalhere&client_id='.$PP_APP_CLIENT_ID.'&client_secret='.$PP_APP_SECRET.'&grant_type=authorization_code&code=' . $code,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_SSL_VERIFYPEER => !$PRODUCTION_MODE // SSL_VERIFYPEER option only needed for sandbox
  )
);

$response = json_decode(curl_exec( $ch ));

if(!isset($response->refresh_token)){ //an error occured since you didnt get a code.  Check URL for error messages
  
  header("location: index.php?error=1" );

}else{

  //You would save these in your database in relation to the merchant account in your database.
  //For this example I am just storing them in a COOKIE.
  setcookie("PayPal-Merchant-Access", $response->access_token, time()+$response->expires_in);  /* expire in 8 hours */
  setcookie("PayPal-Merchant-Refresh", $response->refresh_token);

  //Store both of these in the database and use the refresh token to get new access tokens in the future. Access token included with all future calls.
  if(isset($_GET['live'])){
    header("location: status.php?live=1" );
  }else{
    header("location: status.php" );
  }

}
