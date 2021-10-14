<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use App\Services\SaleService;
use Illuminate\Http\Request;

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
        $data = [
            'customer_id' => 2,
            'items' => [
                [
                    'productId' => 102,
                    'quantity' => 2,
                ],
                [
                    'productId' => 103,
                    'quantity' => 7,
                ]
            ]
        ];
        if (!isset($data['customer_id']) || !isset($data['items']) || !isset($data['items']['productId'])
            || !isset($data['items']['quantity'])) {
            return response()->json([
                'code' => 400,
                'message' => 'Required parameters are missing'
            ]);
        }

        $response = $this->orderService->saveOrder($data);

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
        $saleService = new \App\Services\SaleService(Order::find($orderId));
        $discounts = $saleService->discounts();

        $response = [
            'orderId' => $orderId,
            'discounts' => $discounts,
            'totalDiscount' => $saleService->totalDiscount(),
            'discountedTotal' => $this->orderService->total(Order::find($orderId)->details->toArray())
        ];

        return $response;
    }


}
