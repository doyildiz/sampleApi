<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApiController
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    /**
     * ApiController constructor.
     */
    private $orderService;

    function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required | numeric | min:0',
            'items.*.productId' => 'required | numeric | min:0',
            'items.*.quantity' => 'required | numeric | min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(json_encode($validator->errors()->getMessages()));
        }

        $response = $this->orderService->saveOrder($request->all());

        return response()->json([
            'code' => $response->getStatusCode(),
            json_decode($response->getContent())
        ]);

    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function listAllOrders()
    {
        $orders = Order::with(['details'])->get()->toJson(JSON_PRETTY_PRINT);
        return response($orders, 200);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        if (Order::where('id', $id)->exists()) {
            $order = Order::find($id);
            $order->delete();

            return response()->json([
                "message" => "Order deleted"
            ], 202);
        } else {

            return response()->json([
                "message" => "Order not found"
            ], 404);
        }
    }

    /**
     * @param $orderId
     * @return array
     */
    public function calculateDiscount($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                "data" => null,
                "message" => 'Order not found'
            ], 404);
        }
        $saleService = new \App\Services\SaleService($order);
        $discounts = $saleService->discounts();

        $response = [
            'orderId' => $orderId,
            'discounts' => $discounts,
            'totalDiscount' => number_format($saleService->totalDiscount(),
                2, '.', ','),
            'discountedTotal' => number_format($this->orderService->total(Order::find($orderId)->details->toArray()),
                2, '.', ',')
        ];

        return response()->json([
            "data" => $response,
            "message" => 'Discount calculated'
        ], 200);
    }


}
