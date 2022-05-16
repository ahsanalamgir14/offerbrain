<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderProduct;
use App\Models\Order;

class OrderProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = OrderProduct::pluck('name')->toArray();
        $data = array_unique($data);
        // dd($data);
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
     * @param  \App\Models\OrderProducts  $orderProducts
     * @return \Illuminate\Http\Response
     */
    public function show(OrderProducts $orderProducts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderProducts  $orderProducts
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderProducts $orderProducts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderProducts  $orderProducts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderProducts $orderProducts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderProducts  $orderProducts
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderProducts $orderProducts)
    {
        //
    }
    public function populate_products_from_orders()
    {
        $db_order_products = OrderProduct::orderBy('id', 'desc')->pluck('order_id')->toArray();
        // dd($db_order_products);

        Order::chunk(1000, function ($orders) use ($db_order_products) {
            foreach ($orders as $order) {
                if (in_array($order->order_id, $db_order_products)) {
                    continue;
                } else {
                    $order_product = new OrderProduct();
                    $result = [];
                    $order->products = unserialize($order->products);

                    $result['order_id'] = $order->order_id;
                    if (isset($order->products) && $order->products != "") {
                        $result['product_id'] = $order->products[0]->product_id;
                        $result['sku'] = $order->products[0]->sku;
                        $result['price'] = $order->products[0]->price;
                        $result['product_qty'] = $order->products[0]->product_qty;
                        $result['name'] = $order->products[0]->name;
                        $result['is_recurring'] = $order->products[0]->is_recurring;
                        $result['is_terminal'] = $order->products[0]->is_terminal;
                        $result['recurring_date'] = $order->products[0]->recurring_date;
                        $result['subscription_id'] = $order->products[0]->subscription_id;
                        $result['next_subscription_product'] = $order->products[0]->next_subscription_product;
                        $result['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                        $result['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                        $result['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                        $result['billing_model_discount'] = $order->products[0]->billing_model_discount;
                        $result['is_add_on'] = $order->products[0]->is_add_on;
                        $result['is_in_trial'] = $order->products[0]->is_in_trial;
                        $result['step_number'] = $order->products[0]->step_number;
                        $result['is_shippable'] = $order->products[0]->is_shippable;
                        $result['is_full_refund'] = $order->products[0]->is_full_refund;
                        $result['refund_amount'] = $order->products[0]->refund_amount;
                        $result['on_hold'] = $order->products[0]->on_hold;
                        $result['hold_date'] = $order->products[0]->hold_date;
                        if (isset($order->products[0]->billing_model)) {
                            $result['billing_model_id'] = $order->products[0]->billing_model->id;
                            $result['billing_model_name'] = $order->products[0]->billing_model->name;
                            $result['billing_model_description'] = $order->products[0]->billing_model->description;
                        }
                        if (isset($order->products[0]->offer)) {
                            $result['offer_id'] = $order->products[0]->offer->id;
                            $result['offer_name'] = $order->products[0]->offer->name;
                        }
                        $order_product->create($result);
                    }
                }
            }
        });
    }
}
