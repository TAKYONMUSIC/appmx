<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Helper
{
    public static function validate_cpf( $cpf ): bool
    {

        $cpf = str_pad( preg_replace( '/[^0-9]/', '', $cpf ), 11, '0', STR_PAD_LEFT );

        if ( strlen( $cpf ) != 11 ) {
            return false;
        }

        if ( preg_match( '/^(\d)\1{10}$/', $cpf ) ) {
            return false;
        }

        for ( $char = 9; $char < 11; $char++ ) {

            for ( $digit = 0, $column = 0; $column < $char; $column++ ) {
                $digit += $cpf[$column] * ( ( $char + 1 ) - $column );
            }

            $digit = ( ( 10 * $digit ) % 11 ) % 10;

            if ( $cpf[$column] != $digit ) {
                return false;
            }
        }

        return true;
    }

    public static function validate_card_number( $card_number ): bool
    {
        $card_number = preg_replace( '/[^0-9]/', '', $card_number );

        if ( strlen( $card_number ) != 16 ) {
            return false;
        }

        if ( preg_match( '/^(\d)\1{15}$/', $card_number ) ) {
            return false;
        }

        if (! Appmax_Payments_Helper::is_digit( $card_number ) ) {
            return false;
        }

        return true;
    }

    public static function is_digit( $number ): bool
    {
        return ctype_digit( $number );
    }

    public static function validate_ccv_credit_card( $ccv ): bool
    {
        if ( strlen( $ccv ) < 3 || strlen( $ccv ) > 4 ) {
            return false;
        }

        if (! Appmax_Payments_Helper::is_digit( $ccv ) ) {
            return false;
        }

        return true;
    }

    public static function cpf_unformatted( $value ): array|string
    {
        return str_replace( ["-", "."], "", $value );
    }

    public static function card_number_unformatted( $value ): array|string
    {
        return str_replace( " ", "", $value );
    }

    public static function get_currency_symbol(): string
    {
        return get_woocommerce_currency_symbol();
    }

    public static function get_price_decimals(): int
    {
        return wc_get_price_decimals();
    }

    public static function get_thousand_separator(): string
    {
        return wc_get_price_thousand_separator();
    }

    public static function get_decimal_separator(): string
    {
        return wc_get_price_decimal_separator();
    }

    public static function monetary_format( $value ): string
    {
        return sprintf("%s %s", Appmax_Payments_Helper::get_currency_symbol(), Appmax_Payments_Helper::number_format( $value ) );
    }

    public static function number_format( $value ): string
    {
        return number_format(
            $value,
            Appmax_Payments_Helper::get_price_decimals(),
            Appmax_Payments_Helper::get_decimal_separator(),
            Appmax_Payments_Helper::get_thousand_separator()
        );
    }

    public static function get_subtotal_cart(): float
    {
        return WC()->cart->get_subtotal();
    }

    public static function get_shipping_total_cart(): float
    {
        return WC()->cart->get_shipping_total();
    }

    public static function get_total_cart()
    {
        return WC()->cart->total;
    }

    public static function get_fee_total(): float
    {
        return WC()->cart->get_fee_total();
    }

    public static function get_discount_total(): float
    {
        return WC()->cart->get_discount_total();
    }

    public static function first_character_in_upper_case( $string ): string
    {
        return ucfirst( $string );
    }

    public static function get_translate_status( $status ): string
    {
        return wc_get_order_status_name( $status );
    }

    public static function encode_object( $object ): bool|string
    {
        return json_encode( $object );
    }

    public static function decode_object( $object )
    {
        return json_decode( $object );
    }

    public static function clear_input( $string ): string
    {
        return sanitize_text_field( $string );
    }

    public static function cpf_formatted( $value ): array|string|null
    {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $value);
    }

    public static function phone_formatted( $value ): array|string|null
    {
        return preg_replace("/(\d{2})(\d{5})(\d{4})/", "(\$1) \$2-\$3", $value);
    }

    public static function cep_formatted( $value ): array|string|null
    {
        return preg_replace("/(\d{5})(\d{3})/", "\$1-\$2", $value);
    }

    public static function date_time_formatted( $date, $format = 'd/m/Y H:i:s' ): string
    {
        return date( $format, strtotime( $date ) );
    }

    public static function get_ip()
    {
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
            return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
        }

        if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            return (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) );
        }

        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        return '';
    }

    public static function get_day_week_textual( $due_date ): string
    {
        return date('l', strtotime( $due_date ) );
    }

    public static function trim_event( $event ): string
    {
        $event = explode( "|", $event );
        return trim( $event[0] );
    }

    public static function unset_variables_post( $fields, $post )
    {
        foreach ( $fields as $field => $data ) {
            unset( $post[$field] );
        }
    }

    public static function get_template( string $template_name, array $args = array() )
    {
        wc_get_template( $template_name, $args, 'woocommerce/appmax/', Appmax_Payments::get_templates_path() );
    }

    public static function add_zeros_left( $value ): string
    {
        return str_pad( $value, 2, '0', STR_PAD_LEFT );
    }

    public static function current_class_event( $event ): string
    {
        $event_class = ltrim(preg_replace('/[A-Z]/', '_$0', $event ), '_');
        return "Appmax_Payments_{$event_class}";
    }

    public static function get_sku( $sku ): string
    {
        if (preg_match("/__/i", $sku)) {
            list($parent, $children) = explode("__", $sku);
            return $children;
        }

        return $sku;
    }

    public static function string_formatted( string $message, array $args ): string
    {
        return vsprintf( $message, $args );
    }

    public static function replace( $search, $replace, $value ): array|string
    {
        return str_replace( $search, $replace, $value );
    }
}
