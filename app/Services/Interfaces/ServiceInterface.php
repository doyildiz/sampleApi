<?php

namespace App\Services\Interfaces;

/**
 * Interface ServiceInterface
 *
 * @package App\Services\Interfaces
 */
interface ServiceInterface
{

    /**
     * @param $data
     * @return mixed
     */
    public function saveOrder($data);

    /**
     * @param int $customerId
     * @return array
     */
    public function getCustomer($customerId);


    /**
     * Get product
     *
     * @param int $productId
     * @return array
     */
    public function getProduct($productId);

    /**
     * @param int $productId
     * @param $inventory
     * @return boolean
     */
    public function inventoryCheck($productId, $inventory);

    /**
     * @param $products
     * @return float
     */
    public function total($products);


    /**
     * @param $order
     * @return void
     */
    public function inventoryChanges($order);
}
