<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Interest
 */
class Appmax_Payments_Interest
{
    /**
     * @var array
     */
    private array $products;

    /**
     * @var float
     */
    private float $interest;

    /**
     * @param $products
     * @param $interest
     */
    public function __construct($products, $interest)
    {
        $this->products = $products;
        $this->interest = $interest;
    }

    /**
     * @return array
     */
    public function get_products(): array
    {
        return $this->products;
    }

    /**
     * @return float
     */
    public function get_interest(): float
    {
        return $this->interest;
    }

    /**
     * @return $this
     */
    public function distribute(): static
    {
        $this->products = array_map(fn($item) => $this->distribute_interest_per_item($item, round(($this->get_interest() / count($this->get_products())), 3)), $this->get_products());

        return $this;
    }

    /**
     * @param $item
     * @param $distribute_interest
     * @return mixed
     */
    public function distribute_interest_per_item( $item, $distribute_interest ): mixed
    {
        $price = $item['price'] + ($distribute_interest / $item['qty']);
        $item['price'] = round($price, 3);
        return $item;
    }
}
