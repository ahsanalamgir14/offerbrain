<?php

// require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

session_start();

function refreshToken()
{

    // Create SDK instance
    // $config = include('config.php');

      $config =array(
        'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
        'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
        'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
        'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
        'oauth_scope' => 'com.intuit.quickbooks.accounting',
        'oauth_redirect_uri' => 'http://offer-brain.test/callback.php',
        'company_id' => '4620816365232978110'
    );
     /*
     * Retrieve the accessToken value from session variable
     */
    $accessToken = $_SESSION['sessionAccessToken'];
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => $config['oauth_redirect_uri'],
        'baseUrl' => "development",
        'refreshTokenKey' => $accessToken->getRefreshToken(),
        'QBORealmID' => $config['company_id'],
    ));

    /*
     * Update the OAuth2Token of the dataService object
     */
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();
    $dataService->updateOAuth2Token($refreshedAccessTokenObj);

    $_SESSION['sessionAccessToken'] = $refreshedAccessTokenObj;

    print_r($refreshedAccessTokenObj);
    return $refreshedAccessTokenObj;
}

$result = refreshToken();

?>