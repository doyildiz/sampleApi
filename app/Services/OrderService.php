<?php


namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Services\Interfaces\ServiceInterface;


class OrderService implements ServiceInterface
{

    /**
     * @param int $customerId
     * @return mixed
     */
    public function getCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            return $customer;
        }
        return false;
    }

    /**
     * Get product
     *
     * @param int $productId
     * @return mixed
     */
    public function getProduct($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            return $product;
        }
        return false;
    }

    /**
     * @param int $productId
     * @param int $inventory
     * @return boolean
     */
    public function inventoryCheck($productId, $inventory)
    {
        $product = Product::find($productId);
        if ($product && $product->stock > $inventory) {
            return true;
        }
        return false;
    }

    /**
     * @param $data
     * @return mixed|void
     */
    public function saveOrder($data)
    {
        if (!$this->getCustomer($data['customer_id'])) {
            return response()->json([
                "message" => "Customer not found"
            ], 404);
        }

        $items = $data['items'];

        foreach ($items as $item) {
            if (!$this->getProduct($item['productId'])) {
                return response()->json([
                    "message" => "Product not found"
                ], 404);
            }
            if (!$this->inventoryCheck((int)$item['productId'], (int)$item['quantity'])) {
                return response()->json([
                    "message" => "Not enough stock for product: " .
                        json_encode($this->getProduct($item['productId']))
                ], 200);
            }
        }

        $order = new Order();
        $order->customerId = $data['customer_id'];
        $order->total = $this->total($items);

        if ($order->save()) {
            $this->saveDetails($order->id, $items);
            $this->inventoryChanges($order);
        }
        return response()->json([
            "message" => "Order record created"
        ], 201);
    }

    /**
     * @param $products
     *  * @return float
     * calculates total price of an order
     */
    public function total($products)
    {
        $total = 0;

        foreach ($products as $product) {
            $product_price = $this->getProduct($product['productId'])->price;
            $total += $product_price * $product['quantity'];
        }

        return $total;

    }

    /**
     * @param $order_id
     * @param $items
     *  * @return void
     */
    public function saveDetails($order_id, $items)
    {
        foreach ($items as $item) {
            $product = $this->getProduct($item['productId']);
            $order_details = new OrderDetail();
            $order_details->orderId = $order_id;
            $order_details->productId = $item['productId'];
            $order_details->quantity = (int)$item['quantity'];
            $order_details->unitPrice = $product->price;
            $order_details->total = (int)$item['quantity'] * $product->price;
            $order_details->save();
        }
    }

    /**
     * @param $order
     * @return void
     * decreases inventory of a product when an order created
     */
    public function inventoryChanges($order)
    {
        $details = $order->details;
        foreach ($details as $detail) {
            $product = Product::find($detail->productId);
            $product->stock = $product->stock - $detail->quantity;
            $product->save();
        }
    }
}
