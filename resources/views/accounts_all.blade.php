<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
session_start();

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;



$config =array(
    'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
    'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
    'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
    'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
    'oauth_scope' => 'com.intuit.quickbooks.accounting',
    'oauth_redirect_uri' => env('APP_URL').'/callback.php',
    'company_id' => '4620816365232978110'
);

$accessToken = $_SESSION['sessionAccessToken'];


$dataService = DataService::Configure(array(
  'auth_mode'       => 'oauth2',
  'ClientID'        => $config['client_id'],
  'ClientSecret'    => $config['client_secret'],
  'accessTokenKey'  => $accessToken,
  'refreshTokenKey' => $accessToken->getRefreshToken(),
  'QBORealmID'      => $config['company_id'],
  'baseUrl'         => "development"
));

$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");


$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

$accessToken = $OAuth2LoginHelper->refreshToken();
$error = $OAuth2LoginHelper->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
    return;
}
$dataService->updateOAuth2Token($accessToken);


$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");

// Iterate through all Accounts, even if it takes multiple pages
$i = 1;
while (1) {
    $allAccounts = $dataService->FindAll('Account', $i, 500);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        exit();
    }

    if (!$allAccounts || (0==count($allAccounts))) {
        break;
    }

    foreach ($allAccounts as $oneAccount) {
        echo "Account[".($i++)."]: {$oneAccount->Name}\n";
        echo "\t * Id: [{$oneAccount->Id}]\n";
        // echo "\t * AccountType: [{$oneAccount->AccountType}]\n";
        // echo "\t * AccountSubType: [{$oneAccount->AccountSubType}]\n";
        // echo "\t * Active: [{$oneAccount->Active}]\n";

        echo "\t Account balance: [{$oneAccount->CurrentBalance}]";

        echo "\n";
    }
}

/*

Example output:

Account[0]: Travel Meals
     * Id: NG:42315
     * AccountType: Expense
     * AccountSubType:

Account[1]: COGs
     * Id: NG:40450
     * AccountType: Cost of Goods Sold
     * AccountSubType:

...

*/
 ?>