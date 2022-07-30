<?php

namespace App\Http\Controllers;

use QuickBooksOnline\API\DataService\DataService;

session_start();
use App\Models\MidGroup;
use App\Models\QuickAccounts;
use App\Models\Invoices;
use QuickBooksOnline\API\Facades\Invoice;
use Illuminate\Http\Request;
use DateTime;
use Auth;






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

    public function accounts_all()
    {
        return view('accounts_all');
    }


    public function quickbookConnect($midGroupId, $account_id, $status = '')
    {

        $config = array(
            'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
            'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
            'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
            'oauth_scope' => 'com.intuit.quickbooks.accounting',
            'oauth_redirect_uri' => env('APP_URL') . '/callback.php'
        );

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'RedirectURI' => $config['oauth_redirect_uri'],
            'scope' => $config['oauth_scope'],
            'baseUrl' => "development"
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

        if (isset($midGroupId) && isset($_SESSION['mid_info'])) {
            $j = count($_SESSION['mid_info']);

            for ($i = 0; $i < $j; $i++) {
                if (!isset($_SESSION['mid_info'][$i])) {
                    $i++;
                    continue;
                }
                if ($_SESSION['mid_info'][$i]['mid_group_id'] == "undefined") {
                    $_SESSION['mid_info'][$i]['mid_group_id'] = $midGroupId;
                }
            }
        }

        $_SESSION['midGroupId'] = $midGroupId;
        $_SESSION['account_id'] = $account_id;
        $_SESSION['authUrl'] = $authUrl;

        //set the access token using the auth object
        if (strlen($status) == 0 && isset($_SESSION['sessionAccessToken'])) {
            unset($_SESSION['sessionAccessToken']);
        }

        if (isset($_SESSION['sessionAccessToken'])) {
            $this->refreshToken($midGroupId);
            $accessToken = $_SESSION['sessionAccessToken'];

            $accessTokenJson = array(
                'token_type' => 'bearer',
                'access_token' => $accessToken->getAccessToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
                'expires_in' => $accessToken->getAccessTokenExpiresAt()
            );

            $datetime = new DateTime();
            $today = $datetime->format('Y-m-d h:i:s');

            $datetime1 = new DateTime($accessTokenJson['x_refresh_token_expires_in']);
            $ref_token_exp = $datetime1->format('Y-m-d h:i:s');

            if ($today >= $ref_token_exp) {
                $is_valid = false;
            } else {
                $dataService->updateOAuth2Token($accessToken);
                $CompanyInfo = $dataService->getCompanyInfo();
                $allAccounts = $dataService->FindAll('Account');
                $_SESSION['midGroupAccounts'] = [
                    'midGroupId' => $_SESSION['midGroupId'], 'account_id' => $_SESSION['account_id'],
                    'midGroupAccounts' => $allAccounts
                ];
                $is_valid = true;
            }



            return response()->json([
                'authUrl' => $authUrl, 'midGroupId' => $midGroupId,
                'account_id' => $account_id, 'all_accounts' => $allAccounts, 'token_array' => $accessTokenJson, 'is_valid' => $is_valid
            ]);
        } else {
            $accessTokenJson = null;
            return response()->json([
                'authUrl' => $authUrl, 'midGroupId' => $_SESSION['midGroupId'],
                'account_id' => $account_id, 'token_array' => $accessTokenJson, 'is_valid' => false, 'status' => $status
            ]);
        }


    }

    public function generateInvoice(Request $request)
    {
        $data = $request->all();
        $mid_group_id = $data[0]['id'];
        $length = count($data);

        $config = array(
            'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
            'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
            'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
            'oauth_scope' => 'com.intuit.quickbooks.accounting',
            'oauth_redirect_uri' => env('APP_URL') . '/callback.php'
        );

        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'RedirectURI' => $config['oauth_redirect_uri'],
            'scope' => $config['oauth_scope'],
            'baseUrl' => "development"
        ));

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
    
            // Store the url in PHP Session Object;
        $_SESSION['authUrl'] = $authUrl;
        if (isset($_SESSION['sessionAccessToken'])) {
            unset($_SESSION['sessionAccessToken']);
        }
    
            //set the access token using the auth object
        if (isset($_SESSION['sessionAccessToken'])) {
            $this->refreshToken($mid_group_id);
            $accessToken = $_SESSION['sessionAccessToken'];
            $accessTokenJson = array(
                'token_type' => 'bearer',
                'access_token' => $accessToken->getAccessToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
                'expires_in' => $accessToken->getAccessTokenExpiresAt()
            );

            $datetime = new DateTime();
            $today = $datetime->format('Y-m-d h:i:s');

            $datetime1 = new DateTime($accessTokenJson['x_refresh_token_expires_in']);
            $ref_token_exp = $datetime1->format('Y-m-d h:i:s');

            if ($today >= $ref_token_exp) {
                $is_valid = false;
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $mid_group_id = $data[$i]['id'];
                    $amount = $data[$i]['target_bank_balance'];
                    $amount = str_replace(',', '', $amount);
                    $amount = floatval($amount);
                    $theResourceObj = Invoice::create([
                        "Line" => [
                            [
                                "Amount" => $amount,
                                "DetailType" => "SalesItemLineDetail",
                                "SalesItemLineDetail" => [
                                    "ItemRef" => [
                                        "value" => 1,
                                        "name" => "Services"
                                    ]
                                ]
                            ]
                        ],
                        "CustomerRef" => [
                            "value" => 1
                        ]
                    ]);
                    $dataService->updateOAuth2Token($accessToken);
                    $resultingObj[] = $dataService->Add($theResourceObj);
                    $invoices[] = [
                        'user_id' => Auth::id(), 'invoice_number' => $resultingObj[$i]->Id, 'mid_group_id' => $mid_group_id,
                        'amount' => $resultingObj[$i]->Line[0]->Amount, 'created_at' => $resultingObj[$i]->MetaData->CreateTime,
                        'updated_at' => $resultingObj[$i]->MetaData->LastUpdatedTime
                    ];
                }
                $result = $this->insertInvoices($invoices);
                $is_valid = true;
            }

            return response()->json([
                'authUrl' => $authUrl,
                'invoice' => $resultingObj[0]->Id, 'token_array' => $accessTokenJson, 'is_valid' => $is_valid
            ]);

        } else {

            $accessTokenJson = null;
            $_SESSION['invoice_data'] = $data;
            return response()->json([
                'authUrl' => $authUrl, 'status' => 'to the processCode',
                'token_array' => $accessTokenJson, 'invoice' => 'not created yet!', 'is_valid' => false, 'mid_group_id' => $mid_group_id
            ]);
        }
    }

    public function refreshToken($mid_group_id)
    {

        $j = count($_SESSION['mid_info']);

        for ($i = 0; $i < $j; $i++) {
            if (!isset($_SESSION['mid_info'][$i])) {
                $i++;
                continue;
            }
            if ($_SESSION['mid_info'][$i]['mid_group_id'] == $mid_group_id) {
                $company_id = $_SESSION['mid_info'][$i]['mid_group_company'];
                break;
            } else {
                return;
            }
        }



        $config = array(
            'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
            'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
            'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
            'oauth_scope' => 'com.intuit.quickbooks.accounting',
            'oauth_redirect_uri' => env('APP_URL') . '/callback.php',
            'company_id' => $company_id
        );

        $accessToken = $_SESSION['sessionAccessToken'];
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
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

    }

    public function processCode()
    {
        $config = array(
            'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
            'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            'client_id' => 'ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz',
            'client_secret' => '2ct6zBGzsMUCqGj95Ob0BJG5fUaS9VtnNyvQaMpS',
            'oauth_scope' => 'com.intuit.quickbooks.accounting',
            'oauth_redirect_uri' => env('APP_URL') . '/callback.php'

        );
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
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
        /*
         * Setting the accessToken for session variable
         */
        //$_SESSION['current_company_id'] = $parseUrl['realmId'];


        $_SESSION['sessionAccessToken'] = $accessToken;
        $dataService->updateOAuth2Token($accessToken);
        $CompanyInfo = $dataService->getCompanyInfo();
        $allAccounts = $dataService->FindAll('Account');
        if (isset($_SESSION['invoice_data'])) {
            $data = $_SESSION['invoice_data'];
            $length = count($data);
            unset($_SESSION['invoice_data']);
            for ($i = 0; $i < $length; $i++) {
                $mid_group_id = $data[$i]['id'];
                $amount = $data[$i]['target_bank_balance'];
                $amount = str_replace(',', '', $amount);
                $amount = floatval($amount);
                $theResourceObj = Invoice::create([
                    "Line" => [
                        [
                            "Amount" => $amount,
                            "DetailType" => "SalesItemLineDetail",
                            "SalesItemLineDetail" => [
                                "ItemRef" => [
                                    "value" => 1,
                                    "name" => "Services"
                                ]
                            ]
                        ]
                    ],
                    "CustomerRef" => [
                        "value" => 1
                    ]
                ]);

                $resultingObj[] = $dataService->Add($theResourceObj);
                $invoices[] = [
                    'user_id' => Auth::id(), 'invoice_number' => $resultingObj[$i]->Id, 'mid_group_id' => $mid_group_id,
                    'amount' => $resultingObj[$i]->Line[0]->Amount, 'created_at' => $resultingObj[$i]->MetaData->CreateTime,
                    'updated_at' => $resultingObj[$i]->MetaData->LastUpdatedTime
                ];
            }
            return $this->insertInvoices($invoices);

        } else {
            $_SESSION['mid_info'][] = ['mid_group_company' => $parseUrl['realmId'], 'mid_group_id' => $_SESSION['midGroupId']];

            $_SESSION['midGroupAccounts'] = [
                'midGroupId' => $_SESSION['midGroupId'],
                'account_id' => $_SESSION['account_id'],
                'midGroupAccounts' => $allAccounts
            ];
        }        
    }

    public static function parseAuthRedirectUrl($url)
    {
        parse_str($url, $qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    // add selected bank account and mid-group id to session 
    public function bankAccounts()
    {
        if (isset($_SESSION['midGroupAccounts'])) {
            if ($_SESSION['midGroupAccounts']['account_id'] == 0) {
                $mid_group_id = $_SESSION['midGroupAccounts']['midGroupId'];
                $a = count($_SESSION['midGroupAccounts']['midGroupAccounts']);
                for ($i = 0; $i < $a; $i++) {
                    $accounts[$i]['user_id'] = Auth::id();
                    $accounts[$i]['mid_group_id'] = $_SESSION['midGroupAccounts']['midGroupId'];
                    $accounts[$i]['account_name'] = $_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->Name;
                    $accounts[$i]['account_id'] = $_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->Id;
                }
                unset($_SESSION['midGroupAccounts']);
                return $this->addQuickAccounts($accounts, $mid_group_id);

            }
            $b = count($_SESSION['midGroupAccounts']['midGroupAccounts']);
            for ($i = 0; $i < $b; $i++) {
                if ($_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->Id == $_SESSION['midGroupAccounts']['account_id']) {
                    $accounts['quickbalance'] = $_SESSION['midGroupAccounts']['midGroupAccounts'][$i]->CurrentBalance;
                    $accounts['midGroupId'] = $_SESSION['midGroupAccounts']['midGroupId'];
                    break;
                } else {
                    continue;
                }
            }

            if (isset($_SESSION['info'])) {
                $n = count($_SESSION['info']);

                for ($i = 0; $i < $n; $i++) {
                    if (!isset($_SESSION['info'][$i])) {
                        $n++;
                        continue;
                    }
                    if ($_SESSION['info'][$i]['midGroupId'] == $accounts['midGroupId']) {
                        unset($_SESSION['info'][$i]);
                    }

                }
            }
            $_SESSION['info'][] = $accounts;
            unset($_SESSION['midGroupAccounts']);

        }

        if (isset($_SESSION['info'])) {
            return response()->json(['accounts' => $_SESSION['info']]);

        } else {
            return response()->json(['accounts' => null]);
        }

    }
    // add data in quick accounts table in database
    public function addQuickAccounts($accounts, $mid_group_id)
    {
        $data = QuickAccounts::where('mid_group_id', $mid_group_id)->get();
        $length = count($data);
        if ($length > 0) {
            QuickAccounts::where('mid_group_id', $mid_group_id)->update($accounts);
            return response()->json(['updated Accounts' => $accounts], 200);
        } else {
            QuickAccounts::insert($accounts);
            return response()->json(['Added Accounts' => $accounts], 200);
        }

    }

    public function insertInvoices($invoices)
    {
        Invoices::insert($invoices);
        return response()->json(['invoices' => $invoices, 'status' => 'Invoices Created'], 200);
    }

    // update the mid-group table in database(the quick_balance field)
    public function updateQuickBalance(Request $request, $id = '')
    {
        if (!empty($id)) {
            QuickAccounts::where('mid_group_id', $id)->delete();
            $mid_group = MidGroup::find($id);
            $mid_group->quick_balance = $request->quick_balance;
            $mid_group->update();
            $j = count($_SESSION['mid_info']);

            for ($i = 0; $i < $j; $i++) {
                if (!isset($_SESSION['mid_info'][$i])) {
                    $i++;
                    continue;
                }
                if ($_SESSION['mid_info'][$i]['mid_group_id'] == $id) {
                    unset($_SESSION['mid_info'][$i]);
                }
            }

            return response()->json(['disconnected QuickBooks for midGroup id#' => $id], 200);
        }
        if (empty($request->accounts)) {
            return response()->json(['updated content for account balance' => 'Nothing to update'], 200);
        }


        $n = count($request->accounts);
        for ($i = 0; $i < $n; $i++) {
            if (!isset($request->accounts[$i])) {
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
        return response()->json(['updated content for account balance' => $request->accounts], 200);
    }

    // update the mid-group id in quick accounts table
    public function updateQuickAccounts(Request $request, $mid_group_id)
    {

        QuickAccounts::where('mid_group_id', 0)->update(['mid_group_id' => $mid_group_id]);

        return response()->json(['in quick_ccountsMid-group-id updated to #' => $mid_group_id], 200);
    }

    public function checkQuickAccounts()
    {
        $data = QuickAccounts::where('mid_group_id', 0)->get();
        $length = count($data);
        if ($length > 0) {
            QuickAccounts::where('mid_group_id', 0)->delete();
            return response()->json(['status' => $length . ' null records were deleted from quick_accounts', 'data' => $data], 200);
        }
        return response()->json(['status' => 'no null records were found in quick_accounts'], 200);
    }


    public function accountNames($mid_group_id)
    {
        $data = QuickAccounts::where('mid_group_id', $mid_group_id)->get();
        return response()->json(['accountNames' => $data], 200);
    }

    public function getInvoices($mid_group_id)
    {
        $data = Invoices::where('mid_group_id', $mid_group_id)->get();
        return response()->json(['invoices' => $data], 200);
    }
}
