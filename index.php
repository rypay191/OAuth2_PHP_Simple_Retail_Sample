
<? include_once('config.php'); 

//For this example we will let the merchant country be passed in if they want. 
if(isset($_GET['country'])){
  $merchant_country = strtoupper($_GET['country']);
}else{
  $merchant_country = 'US';
}

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
        <p>Other Retail Payment Option </p>
      </div>
      <div class="jumbotron">
        <h1><img src="img/pp_here_flat.png" height="40" alt="PayPal Here" /></h1>

        <? if(in_array($merchant_country, $PPH_DATA->card_countries)){ //if the merchant is in a PPH Compatible Country ?>
        <p class="lead">The fast and easy way to accept credit cards in-store.</p>
        <img src="img/readers/<?=$PPH_DATA->$merchant_country->reader_image; ?>" alt="PayPal Credit Card Reader" />
        <p style="font-size:12px;">Simple flat rate pricing starting at <?=$PPH_DATA->$merchant_country->rate_standard; ?>*, no hidden cost, and no long term commitments. </p>
        <p>
          <a class="btn btn-primary" href="<?=$PPH_DATA->$merchant_country->account_onboard_link; ?>" target="<?=$PPH_DATA->$merchant_country->onboard_target; ?>" role="button">
            <?=$PPH_DATA->$merchant_country->account_onboard_text; ?>
          </a>
        </p>
        <hr/>
        <p>Already created a PayPal Account for your Business? </p>
        <p style="font-size:12px;">Use the button below to connect it to <?=$PARTNER_NAME; ?>: </p>
          <a href="<?=$OAUTH_LINK; ?>" class="ui-link">
          <img src="https://www.paypalobjects.com/webstatic/en_US/developer/docs/lipp/loginwithpaypalbutton.png" alt="Log in with PayPal">
        </a>
        <? }else{ //we can tell them PayPal Here is not availbale in their country ?>
         <p class="lead">Sorry. PayPal Here is only available for card processing in (<?=implode(", ", $PPH_DATA->card_countries); ?>) at this time.</p>
        <? } //end if PayPal Here is not available. ?>
      </div>
      <div class="jumbotron">
        <p>Other Retail Payment Option </p>
      </div>

      <footer class="footer">
        <p>&copy; <?=$PARTNER_NAME; ?> <?=date('Y'); ?>    |    <a href="../pph-onboard-global.zip">Download Sample Code</a></p>
      </footer>

    </div> <!-- /container -->


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>


  </body>
</html>
