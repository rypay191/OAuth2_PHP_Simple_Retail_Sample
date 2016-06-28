<? include_once('config.php'); 

//For this page we will get the Merchant PayPal Aceess/Refresh Token from a Cookie.
//In your situation these would be stored in the Database.


if(isset($_COOKIE['PayPal-Merchant-Access'])){ //if we have an active access token lets use it.
  
  $access_token = $_COOKIE['PayPal-Merchant-Access'];
  $refresh_token = $_COOKIE['PayPal-Merchant-Refresh'];

}else if(isset($_COOKIE['PayPal-Merchant-Refresh'])){

  $refresh_token = $_COOKIE['PayPal-Merchant-Refresh'];

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
  $response = json_decode(curl_exec( $ch ));

  if(isset($response->access_token)){ //lets use this new token and update the cookie
    $access_token = $response->access_token;
    setcookie("PayPal-Merchant-Access", $response->access_token, time()+$response->expires_in);  /* expire in 8 hours */
  }else{
    header("location: index.php?error=unable+to+refresh+access");
  }

}else{ // We dont have this user in the "database" so lets send then back to connect.
  header("location: index.php?error=no+refresh+token");
}




// Lookup the PayPal Account Details"
$ch = curl_init( $PAYPAL_REST_DOMAIN."/v1/identity/openidconnect/userinfo/?schema=openid&access_token=" . $access_token );

curl_setopt_array( $ch,
  array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_SSL_VERIFYPEER => !$PRODUCTION_MODE // SSL_VERIFYPEER option only needed for sandbox
  )
);

$paypal_account_details = json_decode(curl_exec( $ch ));

$paypal_account_country = $paypal_account_details->address->country;

//Also lookup the Card Processing Status of the Account:
// For this request you need to specify the access token in the header.
$ch = curl_init( $PAYPAL_REST_DOMAIN."/retail/merchant/v1/status" );

curl_setopt_array( $ch,
  array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_SSL_VERIFYPEER => !$PRODUCTION_MODE, // SSL_VERIFYPEER option only needed for sandbox
    CURLOPT_HTTPHEADER => array(
      "accept: application/json",
      "authorization: Bearer ". $access_token
    )
  )
);

$paypal_account_retail_status = json_decode(curl_exec( $ch ));



?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title><?=$PARTNER_NAME; ?> - Connect PayPal Here</title>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <!-- Onboarding theme -->
    <link rel="stylesheet" href="css/jumbotron-narrow-paypal.css">


  </head>

  <body>

    <div class="container">
      <div class="header clearfix">
        <h3 class="text-muted"><?=$PARTNER_NAME; ?></h3>
      </div>
      <div class="jumbotron">
        <h1><img src="img/pp_here_flat.png" height="40" alt="PayPal Here" /></h1>
        <p class="lead">You have connected your PayPal Account:</p>
        <p style="font-size:12px;"><?=$paypal_account_details->email; ?></p>
        <a href="index.php">Connect a different account.</a>
      </div>

      <div class="row marketing">
        <div>

          <? if(in_array($paypal_account_country, $PPH_DATA->card_countries)){ ?>
          <div class="success-row">
            <h4>PayPal Account Country</h4>
            <p>Your PayPal Account is located in country that offers card processing.</p>
          </div>



          <? if(in_array($paypal_account_details->account_type, $PPH_DATA->$paypal_account_country->account_types)){ ?>
          <div class="success-row">
            <h4>PayPal Account Type</h4>
            <p>Your PayPal Account is of Premier or Business type.</p>
          </div>
          <? }else{ ?>
          <div class="warn-row">
            <h4>PayPal Account Type</h4>
            <p>Please <a href="<?=$PPH_DATA->$paypal_account_country->account_upgrade_link; ?>" target="_blank">upgrade your PayPal account</a>.</p>
          </div>
          <? } ?>


          <? if($paypal_account_retail_status->status == 'ready' && (in_array('chip', $paypal_account_retail_status->paymentTypes) || in_array('card', $paypal_account_retail_status->paymentTypes))){ ?>
          <div class="success-row">
            <h4>Physical Card Processing</h4>
            <p>Your PayPal Account is enabled for PayPal Here and ready to accept cards.</p>
          </div>
           <div style="text-align:center;">
            <h4>PayPal Card Readers</h4>
            <p>You can <a href="<?=$PPH_DATA->$paypal_account_country->webstore_link; ?>" target="_blank">order extra PayPal card readers online</a>.</p>
          </div>


          <? }else{ ?>
          <div class="warn-row">
            <h4>Physical Card Processing</h4>
            <p>You must <a href="<?=$PPH_DATA->$paypal_account_country->pph_onboard_link; ?>" target="_blank">apply for PayPal Here Card Processing</a>.</p>
            <p style="font-size:12px;">If you have already applied for PayPal Here it may take some time to activate. <br/>Please Contact PayPal at: <?=$PPH_DATA->$paypal_account_country->support_phone; ?>  to check your application status. </p>
          </div>
          <? } ?>




          <? }else{ //Looks like this Account is not in a valid Country ?>
          <div class="warn-row">
            <h4>PayPal Account Country</h4>
            <p>Sorry.  PayPal Card Processing is only available in (<?=implode(", ", $PPH_DATA->card_countries); ?>) at this time..</p>
          </div>
          <? } ?>

          <div class="panel-group" id="accordion">
          <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
              Developer / Testing Request and Responses </a>
            </h4>
          </div>
          <div id="collapse3" class="panel-collapse collapse">
            <div class="panel-body">

            <h5>Refresh Token:</h5>
            <pre><?=$refresh_token; ?></pre>
            <h5>Access Token:</h5>
            <pre><?=$access_token; ?></pre>
            <h5>Retail SDK Token:</h5>
            <? //lets quickly format the refresh URL for this token
              $refresh_url = 'https://nommio.com/app/sample/auth/refresh.php?'; //update this to URL endpoint.
              $refresh_url.='refresh_token='.urlencode(encrypt($refresh_token));  //We encrypt and append the refresh token, you might store this token a database instead.
              if(isset($_GET['live'])){
                $refresh_url.='&live=1'; //if this is a live token we can specify such.
              }
            ?>
            <pre><? echo $sdk_token_decoded = '["'.$access_token.'",28800,"'.$refresh_url.'"]'; ?></pre>
            <pre><?=$PP_ENVIRONEMNT_NAME; ?>:<?=base64_encode($sdk_token_decoded); ?></pre>
            <h5>UserInfo Response</h5>
            <pre><? print_r($paypal_account_details); ?></pre>
            <h5>Retail Status Response</h5>
            <pre><? print_r($paypal_account_retail_status); ?></pre>


            </div>
          </div>
        </div>
        </div>



        </div>

      <footer class="footer">
        <p>&copy; <?=$PARTNER_NAME; ?> <?=date('Y'); ?>    |    <a href="../pph-onboard-global.zip">Download Sample Code</a></p>
      </footer>

    </div> <!-- /container -->


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>


  </body>
</html>
