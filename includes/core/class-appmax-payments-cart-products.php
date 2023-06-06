<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Cart_Products
 */
class Appmax_Payments_Cart_Products
{
    /**
     * @var array
     */
    private array $items;

    public function __construct()
    {
        $this->items = WC()->cart->get_cart();
    }

    /**
     * @param $interest
     * @return array
     * @throws Exception
     */
    public function get_products_cart( $interest ): array
    {
        $array_products = [];

        foreach ($this->items as $values) {
            array_push( $array_products, $this->get_current_product( $values ) );
        }

        $interest_service = new Appmax_Payments_Interest($array_products, $interest);
        $interest_service->distribute();

        $array_products = $interest_service->get_products();

        $tax_total = (float) Appmax_Payments_Helper::get_fee_total();

        if (! $tax_total) {
            return $array_products;
        }

        return (new Appmax_Payments_Tax())->distribute_tax( $array_products, $interest );
    }

    /**
     * @param $values
     * @return array
     * @throws Exception
     */
    private function get_current_product( $values ): array
    {
        $product = wc_get_product( $values['product_id'] );

        if ( ! $product->get_sku() ) {
            throw new Appmax_Payments_Undefined_Product_Sku_Exception( "Produto do carrinho {$product->get_title()} não possui código SKU registrado." );
        }

        if ( empty( $values['variation_id'] ) && count( $values['variation'] ) == 0 ) {
            return $this->get_information_product( $product, $values );
        }

        return $this->get_information_product_variation( $product->get_sku(), $values );
    }

    /**
     * @param WC_Product $product
     * @param $values
     * @return array
     */
    private function get_information_product( WC_Product $product, $values ): array
    {
        $price = (float) $product->get_price();

        if ( ! empty( $values['rn_entry'] ) ) {
            $totals = $values['rn_entry']->Totals;

            $price = (float) $product->get_price() + $totals->OptionsTotal;
        }

        return [
            "sku" => $product->get_sku(),
            "price" => $price,
            "name" => $product->get_title(),
            "qty" => $values['quantity'],
            "description" => $product->get_description(),
            "image" => get_the_post_thumbnail_url( $values['product_id'] ),
        ];
    }

    /**
     * @param $skuParentProduct
     * @param $values
     * @return array
     */
    private function get_information_product_variation( $skuParentProduct, $values ): array
    {
        $variation = wc_get_product( $values['variation_id'] );

        $price = (float) $variation->get_price();

        if ( ! empty( $values['rn_entry'] ) ) {
            $totals = $values['rn_entry']->Totals;

            $price = (float) $variation->get_price() + $totals->OptionsTotal;
        }

        return [
            "sku" => "{$skuParentProduct}__{$variation->get_sku()}",
            "price" => $price,
            "name" => $variation->get_name(),
            "qty" => $values['quantity'],
            "description" => $variation->get_description(),
            "image" => get_the_post_thumbnail_url( $values ),
        ];
    }

}