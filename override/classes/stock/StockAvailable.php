<?php

/**
 * @author Salerno Simone
 */
class StockAvailable extends StockAvailableCore {
	
	/**
    * @see parent
    * Implement Eager loading
    * @param int $id_product
    * @param int $id_product_attribute Optional
    * @param int $id_shop Optional : gets context by default
    * @return int Quantity
    */
   public static function getQuantityAvailableByProduct($id_product = null, $id_product_attribute = null, $id_shop = null)
   {
       if (!Context::getContext()->controller instanceof CategoryController)
           return parent::getQuantityAvailableByProduct($id_product, $id_product_attribute, $id_shop);
       
        // if null, it's a product without attributes
        if ($id_product_attribute === null)
                $id_product_attribute = 0;

        $query = (new DbQueryEager(__METHOD__))
                ->addEagerParam('id_product', (int)$id_product)
                ->addEagerParam('id_product_attribute', (int)$id_product_attribute)
                ->from('stock_available')
                ->groupBy('id_product, id_product_attribute');
        StockAvailable::addSqlShopRestriction($query, $id_shop);
        
        $quantity = (int)$query->value('SUM(quantity)');
        return $quantity;
   }
}
