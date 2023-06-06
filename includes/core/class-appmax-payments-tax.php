<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Tax
 */
class Appmax_Payments_Tax
{
    /**
     * @param $products
     * @param $interest
     * @return array
     */
    public function distribute_tax( $products, $interest ): array
    {
        $tax_total = (float) Appmax_Payments_Helper::get_fee_total();

        $distribute_tax = round(($tax_total / count($products)), 3);

        $new_products = array_map(function($item) use ($distribute_tax) {
            $price = $item['price'] + ($distribute_tax / $item['qty']);
            $item['price'] = round($price, 3);
            return $item;
        }, $products);

        $total_sum_products = $this->get_total_sum_products( $new_products );

        $discount = (float) number_format( Appmax_Payments_Helper::get_discount_total(), 2, '.', ',' );

        $total_cart = (float) Appmax_Payments_Helper::get_total_cart();

        $difference = (float) round((($total_sum_products - $discount - $interest) - $total_cart), 2);

        if ($difference == 0.00) {
            return $new_products;
        }

        uasort($new_products, function($a, $b) {
            if ($a['price'] == $b['price']) {
                return 0;
            }
            return ($a['price'] < $b['price']) ? 1 : -1;
        });

        $new_products = array_values($new_products);

        foreach ($new_products as $key => $product) {
            if ($product['qty'] == 1) {
                $new_products[$key]['price'] -= $difference;
                break;
            }
            if ($product['qty'] > 1) {
                $new_products[$key]['price'] -= $difference / $product['qty'];
                break;
            }
        }

        return $new_products;
    }

    /**
     * @param $products
     * @return mixed
     */
    private function get_total_sum_products( $products )
    {
        return array_reduce($products, function($total, $item) {
            $total += (float) ($item['price'] * $item['qty']);
            return $total;
        });
    }
}