<?php

namespace Deepcommerce\Phpshop\Helper;

use Deepcommerce\Phpshop\Core\Localize;

class ProductHelper {

    /**
     * Format a price
     * @param int $price
     * @return string
     */
    public static function format($price) {
        return Localize::numberFormat(intval($price)/100) . ' ' . Localize::$currencySymbol;
        //return number_format($price/100, 2, ',', '.') . ' €';
    }

    /**
     * Check if a product has a previous price
     * @param array $product
     * @return bool
     */
    public static function hasOldPrice($product) {
        return (!empty($product['price']['previous']));
    }

    /**
     * Return the price
     * @param array $product
     * @return int
     */
    public static function price($product) {
        return $product['price']['current'];
    }

    /**
     * Return the formatted price
     * @param array $product
     * @return string
     */
    public static function formattedPrice($product) {
        if (isset($product['price']['min'])) {
            return Localize::translate('From') . ' ' . static::format($product['price']['min']);
        }
        return static::format($product['price']['current']);
    }

    /**
     * Return the previous price
     * @param array $product
     * @return int
     */
    public static function oldPrice($product) {
        return $product['price']['previous'];
    }

    /**
     * Return the formatted previous price
     * @param array $product
     * @return string
     */
    public static function formattedOldPrice($product) {
        return static::format($product['price']['previous']);
    }

    /**
     * Return the price color class
     * @param array $product
     * @return string
     */
    public static function priceColorClass($product) {
        if (self::hasOldPrice($product)) {
            return 'discount-price';
        }
        return '';
    }

    /**
     * Retrun the formatted discount of a product
     * e.g. -10% or -8,00 €
     * Discounts above 10% will be displayed as percentage, otherwise as absolute value
     * 
     * @param array $product
     * @return string
     */
    public static function discount($product) {
        if (self::hasOldPrice($product)) {
            $oldPrice = self::oldPrice($product);
            $price    = self::price($product);
            $discount = $oldPrice - $price;
            $discountPercentage = round($discount / $oldPrice * 100);
            if ($discountPercentage >= 10) {
                return '-' . $discountPercentage . '%';
            }
            return '-' . static::format($discount);
        }
        return '';
    }

}
