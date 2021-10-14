<?php


namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use mysql_xdevapi\Collection;

/**
 * Class SaleService
 * @package App\Services
 */
class SaleService extends OrderService
{

    /**
     * @var Order
     */
    private $order;
    /**
     * @var mixed
     */
    private $details;

    /**
     * @var float
     */
    private $total;

    /**
     *
     */
    const free_cat_id = 2;
    const free_cat_min_qty = 6;
    const discount_cat_id = 1;
    const discount_cat_min_qty = 2;

    /**
     * SaleService constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->details = $order->details;
        $this->total = $this->total($this->details->toArray());
    }

    /**
     * @return array|null
     */
    public function sale_10_PERCENT_OVER_1000()
    {
        $response = null;
        if ($this->total > 1000) {
            $response = [
                'discountReason' => __FUNCTION__,
                'discountAmount' => number_format((10 / 100) * $this->total,
                        2, '.', ','),
                'subtotal' => number_format($this->total - (10 / 100) * $this->total,
                        2, '.', ','),
            ];
        }

        return $response;
    }

    /**
     * @return array|null
     */
    public function sale_BUY_6_GET_1()
    {
        $response = null;
        foreach ($this->details as $detail) {
            if ($this->helper($detail, self::free_cat_id) && $detail->quantity == self::free_cat_min_qty) {
                $response[] = [
                    'discountReason' => __FUNCTION__,
                    'discountAmount' => number_format($this->getProduct($detail->productId)->price,
                        2, '.', ','),
                    'subtotal' => number_format($this->total - $this->getProduct($detail->productId)->price,
                        2, '.', ','),
                ];
            }
        }
        return $response;
    }

    /**
     * * @return array|null
     */
    public function sale_20_PERCENT_BUY_1_OR_2()
    {
        $response = null;
        foreach ($this->details as $detail) {
            if ($this->helper($detail, self::discount_cat_id)
                && $detail->quantity >= self::discount_cat_min_qty) {
                $response[] = [
                    'discountReason' => __FUNCTION__,
                    'discountAmount' => number_format((20 / 100) * $this->cheaperProduct()->total,
                            2, '.', ','),
                    'subtotal' => number_format($this->total - (20 / 100) * $this->cheaperProduct()->total,
                        2, '.', ','),
                ];
            }
        }
        return $response;
    }

    /**
     * @param Collection $detail
     * @param int $categoryId
     * @return bool
     * calculates how many products from a spesific category are sold
     */
    public function helper($detail, $categoryId)
    {
        $product = Product::find($detail->productId);
        if ($product->category == $categoryId) {
            return $detail->quantity;
        }
        return false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     * calculates cheapest product in a specific order
     */
    public function cheaperProduct()
    {
        return $this->order->details()->orderBy('unitPrice')->first();
    }

    /**
     * @return array
     * returns all discounts of an order
     */
    public function discounts()
    {
        $discount = [];

        if ($this->sale_10_PERCENT_OVER_1000() !== null)$discount[] = $this->sale_10_PERCENT_OVER_1000();
        if ($this->sale_BUY_6_GET_1() !== null)$discount[] = $this->sale_BUY_6_GET_1();
        if ($this->sale_20_PERCENT_BUY_1_OR_2() != null)$discount[] = $this->sale_20_PERCENT_BUY_1_OR_2();

        return $discount;

    }

    /**
     * @return int|mixed
     */
    public function totalDiscount()
    {
        $total = 0;
        if (!empty($this->discounts())) {
            foreach ($this->discounts() as $discount) {
                if (is_array($discount)) {
                    if (count($discount) == count($discount, COUNT_RECURSIVE)) {
                        $total += $discount['discountAmount'];
                    } else {
                        foreach ($discount as $value) {
                            $total += $value['discountAmount'];
                        }
                    }
                }
            }
        }
        return $total;
    }

}
