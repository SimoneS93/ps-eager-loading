<?php

/**
 * @author Salerno Simone
 */
class SpecificPrice extends SpecificPriceCore {
    
    /**
     * Implements Eager loading
     * @param int $id_product
     * @param int $id_shop
     * @param int $id_currency
     * @param int $id_country
     * @param int $id_group
     * @param int $quantity
     * @param int $id_product_attribute
     * @param int $id_customer
     * @param int $id_cart
     * @param int $real_quantity
     * @return float
     */
    public static function getSpecificPrice($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute = null, $id_customer = 0, $id_cart = 0, $real_quantity = 0) {
        if (!SpecificPrice::isFeatureActive())
            return [];
        
        //on slow servers, $now may change if request take longer than 1s if referred to time()
        $now = date('Y-m-d H:i:s', filter_input(INPUT_SERVER, 'REQUEST_TIME'));
        $score = SpecificPrice::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer);
        $qty = (Configuration::get('PS_QTY_DISCOUNT_ON_COMBINATION') || !$id_cart || !$real_quantity) ? (int)$quantity : max(1, (int)$real_quantity);
        
        $query = (new DbQueryEager(__METHOD__))
                ->select('*, ' . $score)
                ->from('specific_price')
                ->addEagerParam('id_product', $id_product)
                ->addEagerParam('id_product_attribute', $id_product_attribute)
                ->in('id_shop', [0, (int)$id_shop])
                ->in('id_currency', [0, (int)$id_currency])
                ->in('id_country', [0, (int)$id_country])
                ->in('id_group', [0, (int)$id_group])
                ->in('id_customer', [0, (int)$id_customer])
                ->in('id_cart', [0, (int)$id_cart])
                ->where('IF(`from_quantity` > 1, `from_quantity`, 0) <= ' . $qty)
                ->where('`from` = \'0000-00-00 00:00:00\' OR \''. $now .'\' >= `from`')
                ->where('`to` = \'0000-00-00 00:00:00\' OR \''. $now .'\' <= `to`')
                //use reverse order so later records will override former ones
                ->orderBy('id_product_attribute DESC, `from_quantity` ASC, `id_specific_price_rule` ASC, `score` ASC');
        
        $price = $query->row() ?: 
                //use late param binding if no match is found
                $query->row(['id_product_attribute' => 0]) ?:
                $query->row(['id_product' => 0, 'id_product_attribute' => 0]);
        return $price;
    }
}
