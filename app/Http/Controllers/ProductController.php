<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Product::all();
        return response()->json(['status' => true, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function pull_user_products(Request $request)
    {
        $created = 0;
        $updated = 0;
        // return Auth::id();
        $db_products = Product::where(['user_id' => Auth::id()])->pluck('product_id')->toArray();
        // return $db_products;
        // $user = User::find(2);
        $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);
        $url = $user->sticky_url . '/api/v2/products';
        $page = 1;

        $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page]);
        $response['products'] = $api_data['data'];
        $last_page = $api_data['last_page'];
        // dd($last_page);
        if ($response['products']) {
            foreach ($response['products'] as $result) {
                $result['product_id'] = $result['id'];
                $result['user_id'] = Auth::id();
                $result['created_at'] = $result['created_at']['date'];
                if (isset($result['updated_at'])) {
                    $result['updated_at'] = $result['updated_at']['date'];
                }
                if (isset($result['vertical'])) {
                    $result['vertical'] = json_encode($result['vertical']);
                }
                $result['category'] = json_encode($result['category']);
                $result['custom_fields'] = json_encode($result['custom_fields']);
                $result['legacy_subscription'] = json_encode($result['legacy_subscription']);
                $result['images'] = json_encode($result['images']);
                if (in_array($result['product_id'], $db_products)) {
                    $updated++;
                    $product = Product::where(['product_id' => $result['product_id'], 'user_id' => Auth::id()])->first();
                    $product->update($result);
                } else {
                    $created++;
                    Product::create($result);
                }
            }
            if ($last_page > 1) {
                $page++;
                for ($page; $page <= $last_page; $page++) {

                    $response['products'] = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page])['data'];
                    foreach ($response['products'] as $result) {
                        $result['product_id'] = $result['id'];
                        $result['user_id'] = Auth::id();
                        $result['created_at'] = $result['created_at']['date'];
                        if (isset($result['updated_at'])) {
                            $result['updated_at'] = $result['updated_at']['date'];
                        }
                        if (isset($result['vertical'])) {
                            $result['vertical'] = json_encode($result['vertical']);
                        }
                        $result['category'] = json_encode($result['category']);
                        $result['custom_fields'] = json_encode($result['custom_fields']);
                        $result['legacy_subscription'] = json_encode($result['legacy_subscription']);
                        $result['images'] = json_encode($result['images']);
                        if (in_array($result['product_id'], $db_products)) {
                            $updated++;
                            $product = Product::where(['product_id' => $result['product_id'], 'user_id' => Auth::id()])->first();
                            $product->update($result);
                        } else {
                            $created++;
                            Product::create($result);
                        }
                        $response = null;
                    }
                }
            }
        }
        $products = DB::table('products')->select('product_id', 'name', 'price', DB::raw("CONCAT('#', product_id,' - ',name,' - $',price ) AS full_name"))->where(['user_id' => 2])->groupBy('product_id')->get();
        return response()->json(
            [
                'status' => true,
                'user_id' => Auth::id(),
                'new products created' => $created,
                'Updated products' => $updated,
                'data' => [
                    'products' => $products
                ],
            ]
        );
    }
}