<?php

namespace App\Http\Controllers;
use QuickBooksOnline\API\DataService\DataService;
use App\Models\MidGroup;
use App\Models\QuickAccounts;
use Illuminate\Http\Request;
session_start();
class Quickbook extends Controller
{
    public function index()
    {
        return view('quickbook');
    }

    public function processCode1()
    {
        return view('callback');
    }

    public function apicall()
    {
        return view('apiCall');
    }
    
    public function refreshToken()
    {
        return view('refreshToken');
    }

    public function accounts_all()
    {
        return view('accounts_all');
    }
    

    public function quickbookConnect($midGroupId, $account_id)
    {

        $config =array(
            'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
            'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
            'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
            'oauth_scope' => 'com.intuit.quickbooks.accounting',
            'oauth_redirect_uri' => env('APP_URL').'/callback.php'
        );

        // return response()->json(['config'=>$config]);
        // exit;

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' =>  $config['client_secret'],
            'RedirectURI' => $config['oauth_redirect_uri'],
            'scope' => $config['oauth_scope'],
            'baseUrl' => "development"
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

        // Store the url in PHP Session Object;
        $_SESSION['authUrl'] = $authUrl;
       

        //set the access token using the auth object
        if (isset($_SESSION['sessionAccessToken'])) {

        $accessToken = $_SESSION['sessionAccessToken'];
        $accessTokenJson = array('token_type' => 'bearer',
        'access_token' => $accessToken->getAccessToken(),
        'refresh_token' => $accessToken->getRefreshToken(),
        'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
        'expires_in' => $accessToken->getAccessTokenExpiresAt()
        );
        $dataService->updateOAuth2Token($accessToken);
        $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
        $CompanyInfo = $dataService->getCompanyInfo();
        }
        $_SESSION['midGroupId'] = $midGroupId;
        $_SESSION['account_id'] = $account_id;
        return response()->json(['authUrl'=>$authUrl,'midGroupId'=>$midGroupId,
        'account_id'=>$account_id]);

    }

    public function processCode()
    {
        $config =array(
            'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
            'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
            'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
            'oauth_scope' => 'com.intuit.quickbooks.accounting',
            'oauth_redirect_uri' => env('APP_URL').'/callback.php',
            'company_id' => '4620816365232978110'
        );
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' =>  $config['client_secret'],
            'RedirectURI' => $config['oauth_redirect_uri'],
            'scope' => $config['oauth_scope'],
            'baseUrl' => "development"
        ));
    
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $parseUrl = self::parseAuthRedirectUrl(htmlspecialchars_decode($_SERVER['QUERY_STRING']));
    
        /*
         * Update the OAuth2Token
         */
        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
        $dataService->updateOAuth2Token($accessToken);
    
        /*
         * Setting the accessToken for session variable
         */
        $_SESSION['sessionAccessToken'] = $accessToken;
        //var_dump($_SESSION['sessionAccessToken']);
        $dataService->updateOAuth2Token($accessToken);
        $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
        $CompanyInfo = $dataService->getCompanyInfo();
        $allAccounts = $dataService->FindAll('Account');
        
        //return response()->json(['accounts'=>$allAccounts]);
        $_SESSION['midGroupAccounts'] = ['midGroupId'=>$_SESSION['midGroupId'],'account_id'=>$_SESSION['account_id'],
        'midGroupAccounts'=>$allAccounts];
        // var_dump($_SESSION['midGroupAccounts']);
        
    }

   public static function parseAuthRedirectUrl($url)
    {
    parse_str($url,$qsArray);
    return array(
        'code' => $qsArray['code'],
        'realmId' => $qsArray['realmId']
    );
    }


    // add selected bank account and mid-group id to session 
    public function bankAccounts()
    {
        if(isset($_SESSION['midGroupAccounts']))
        {

            if($_SESSION['midGroupAccounts']['midGroupId']==0)
            {
                $a = count($_SESSION['midGroupAccounts']['midGroupAccounts']);
                for($i=0; $i<$a; $i++)
                {
                    $accounts[$i]['account_name'] = $_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->Name;
                    $accounts[$i]['account_id'] = $_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->Id;
                }
                return $this->updateQuickAccounts($accounts);

            }
            $b = count($_SESSION['midGroupAccounts']['midGroupAccounts']);
            for($i=0; $i<$b; $i++)
            {
                if($_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->Id == $_SESSION['midGroupAccounts']['account_id'])
                {
                    $accounts['quickbalance'] = $_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->CurrentBalance;
                    $accounts['midGroupId'] = $_SESSION['midGroupAccounts']['midGroupId'];
                    break;
                }
                else{
                    continue;
                }
            }
            
            
            if(isset($_SESSION['info']))
            {
                $n = count($_SESSION['info']);
                
                for($i=0;$i<$n;$i++)
                {
                    if(!isset($_SESSION['info'][$i]))
                    {
                        $n++;
                        continue;
                    }
                    if($_SESSION['info'][$i]['midGroupId']==$accounts['midGroupId'])
                    {
                        // $_SESSION['info'][$i] = ' ';
                        unset($_SESSION['info'][$i]);
                    }
                
                }
            }
            $_SESSION['info'][] = $accounts;
            unset($_SESSION['midGroupAccounts']);
            
        }
        
        if(isset($_SESSION['info']))
        {
            //unset($_SESSION['info']);
            return response()->json(['accounts'=>$_SESSION['info']]);
           
        }
        else
        {
            return response()->json(['accounts'=>null]);
        }
     
    }
    // update the quick accounts table in database
    public function updateQuickAccounts($accounts)
    {
        QuickAccounts::truncate();
        QuickAccounts::insert($accounts);
        return response()->json(['Refreshed Accounts'=>$accounts],200);
    }

    // update the mid-group table in database(the quick_balance field)
    public function updateQuickBalance(Request $request, $id='')
    {
        if(!empty($id))
        {
            $mid_group = MidGroup::find($id);
            $mid_group->quick_balance = $request->quick_balance;
            $mid_group->update();

            return response()->json(['disconnected QuickBooks for midGroup id#'=>$id],200);
        }
        if(empty($request->accounts))
        {
            return response()->json(['updated content for account balance'=>'Nothing to update'],200);
        }
        
        
        $n = count($request->accounts);
        for($i=0; $i<$n; $i++)
        {
            if(!isset($request->accounts[$i]))
            {
                $n++;
                continue;
            }
            $id = $request->accounts[$i]['midGroupId'];
            $quick_balance = $request->accounts[$i]['quickbalance'];
            $mid_group = MidGroup::find($id);
            $mid_group->quick_balance = $quick_balance;
            $mid_group->update();
        }
        unset($_SESSION['info']);
        return response()->json(['updated content for account balance'=>$request->accounts],200);
    }

    public function accountNames()
    {
        $data = QuickAccounts::all();
        return response()->json(['accountNames'=>$data],200);
    }

  

}
