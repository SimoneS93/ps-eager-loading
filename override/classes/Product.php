<?php

/**
 * @author Salerno Simone
 */
class Product extends ProductCore {
 
	/**
     * Implementa l'"eager loading"
     * @param int $id_product
     * @param Context $context
     * @return int
     */
    public static function getIdTaxRulesGroupByIdProduct($id_product, Context $context = null)
    {
		if (!Context::getContext()->controller instanceof CategoryController)
			return parent::getIdTaxRulesGroupByIdProduct($id_product, $context);
		
        $query = (new DbQueryEager(__METHOD__))
                ->from('product_shop')
                ->shop()
                ->addEagerParam('id_product', $id_product);
        
        return $query->value('id_tax_rules_group');
    }
	
  
  public static function getDefaultAttribute($id_product, $minimum_quantity = 0)
  {
		if (!Context::getContext()->controller instanceof CategoryController)
			return parent::getDefaultAttribute($id_product, $minimum_quantity);
		
		if (!Combination::isFeatureActive())
				return 0;

		$joinProductAttribute = Shop::addSqlAssociation('product_attribute', 'pa');
		$joinStock = $minimum_quantity > 0 ? Product::sqlStock('pa', 'pa') : '';
		$query = (new DbQueryEager(__METHOD__.__LINE__))
				->from('product_attribute', 'pa')
				->join($joinProductAttribute)
				->join($joinStock)
				->equals('product_attribute_shop.default_on', 1)
				->where($minimum_quantity > 0 ? ' AND IFNULL(stock.quantity, 0) >= '.(int)$minimum_quantity : '')
				->addEagerParam('id_product', $id_product);
		$result = $query->value('pa.id_product_attribute');
		
		

		if (!$result)
		{
			$query = (new DbQueryEager(__METHOD__.__LINE__))
					->from('product_attribute', 'pa')
					->join($joinProductAttribute)
					->join($joinStock)
					->where($minimum_quantity > 0 ? 'IFNULL(stock.quantity, 0) >= '.(int)$minimum_quantity : '')
					->addEagerParam('id_product', $id_product);
			$result = $query->value('pa.id_product_attribute');
		}

		if (!$result)
		{
			$query = (new DbQueryEager(__METHOD__.__LINE__))
					->from('product_attribute', 'pa')
					->join($joinProductAttribute)
					->equals('product_attribute_shop.default_on', 1)
					->addEagerParam('id_product', $id_product);
			$result = $query->value('pa.id_product_attribute');
		}

		if (!$result)
		{
			$query = (new DbQueryEager(__METHOD__.__LINE__))
					->from('product_attribute', 'pa')
					->join($joinProductAttribute)
					->addEagerParam('id_product', $id_product);
			$result = $query->value('pa.id_product_attribute');
		}

		return $result;
  }
}
