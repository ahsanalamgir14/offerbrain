<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

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
    public function pull_all_products()
    {
        $users = User::orderBy('id', 'asc')->get();
        foreach ($users as $user) {
            $created = 0;
            $updated = 0;
            $db_products = Product::pluck('product_id')->toArray();
            $username = $user->sticky_api_username;
            $password = Crypt::decrypt($user->sticky_api_key);
            $url = $user->sticky_url.'/api/v2/products';
            $model = new Product();
            $page = 1;

            $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page]);
            $response['products'] = $api_data['data'];
            $last_page = $api_data['last_page'];
        
            if ($response['products']) {
                foreach ($response['products'] as $result) {
                    $product = new Product();

                    $result['user_id'] = $user->id;
                    $result['product_id'] = $result['id'];
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
                        $product = Product::where(['product_id' => $result['product_id']])->first();
                        $product->update($result);
                    } else {
                        $created++;
                        $product->create($result);
                    }
                }
                if ($last_page > 1) {
                    $page++;
                    for ($page; $page <= $last_page; $page++) {

                        $response['products'] = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page])['data'];
                        foreach ($response['products'] as $result) {
                            $product = new Product();
                            $result['user_id'] = $user->id;
                            $result['product_id'] = $result['id'];
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
                                $product = Product::where(['product_id' => $result['product_id']])->first();
                                $product->update($result);
                            } else {
                                $created++;
                                $product->create($result);
                            }
                            $response = null;
                        }
                    }
                }
            }
        }
        return response()->json(['status' => true, 'new products created' => $created, 'Updated products' => $updated]);
    }
}