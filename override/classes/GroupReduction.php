<?php

/**
 * @author Salerno Simone
 */
class GroupReduction extends GroupReductionCore {
    
    /**
     * Implements Eager loading
     * @param int $id_product
     * @param int $id_group
     */
    public static function getValueForProduct($id_product, $id_group) {
        if (!Context::getContext()->controller instanceof CategoryController)
            return parent::getValueForProduct ($id_product, $id_group);
            
        //$id_group is "static" for each requests
        //$id_product changes on every call -> eager param
        $query = (new DbQueryEager(__METHOD__))
                ->addEagerParam('id_product', $id_product)
                ->from('product_group_reduction_cache')
                ->naturalJoin('product_shop')
                ->equals('id_group', (int)$id_group)
                ->shop() //adds id_shop = ? clause
                ->active(); // adds active = 1 clause
        return $query->value('reduction');
    }
}
