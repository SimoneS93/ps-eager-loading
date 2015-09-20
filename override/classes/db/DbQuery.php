<?php

/**
 * Add shorts for query building
 * @author Salerno Simone
 */
class DbQuery extends DbQueryCore {
    
    /**
     * Short for "="
     * @param string $key
     * @param mixed $value
     * @return DbQuery
     */
    public function equals($key, $value) {
        $value = $this->escape($value);
        return $this->where("{$key} = {$value}");
    }
    
    /**
     * Adds several equals clauses
     * @param array $equals
     * @return DbQuery
     */
    public function equalsArray(array $equals) {
        array_map([$this, 'equals'], array_keys($equals), array_values($equals));
        return $this;
    }

    /**
     * Short for "!="
     * @param string $key
     * @param mixed $value
     * @return DbQuery
     */
    public function not($key, $value) {
        $value = $this->escape($value);
        return $this->where("{$key} != {$value}");
    }
	
	  /**
	   * Short for "IN"
	   * @param string $key
	   * @param array $values
	   * @return DbQuery
	   */
  	public function in($key, array $values) {
        $values = array_map([$this, 'escape'], $values);
        $values = implode(', ', $values);
        return $this->where("{$key} IN ({$values})");
    }

    /**
     * Adds "active = 1" clause
     * @param string $table
     * @return DbQuery
     */
    public function active($table=null) {
        return $this->bind($table, 'active', 1);
    }

    /**
     * Adds "id_lang = ?" clause
     * @param string $table
     * @return DbQuery
     */
    public function lang($table=null) {
        return $this->bind($table, 'id_lang', 'language');
    }
    
    /**
     * Adds "deleted = 0" clause
     * @param string $table
     * @return DbQuery
     */
    public function nonDeleted($table=null) {
        return $this->bind($table, 'deleted', 0);
    }
    
    /**
     * Adds "id_shop = ?" clause
     * @param string $table
     * @return DbQuery
     */
    public function shop($table=null) {
        return $this->bind($table, 'id_shop', 'shop');
    }
    
    /**
     * Short for Db::getIstance()->executeS()
     * @return array
     */
    public function executeS() {
        return Db::getInstance()->executeS($this);
    }
    
    /**
     * Short for Db::getIstance()->getRow()
     * @return array
     */
    public function getRow() {
        return Db::getInstance()->getRow($this);
    }
    
    /**
     * Short for Db::getIstance()->getValue()
     * @return mixed
     */
    public function getValue() {
        return Db::getInstance()->getValue($this);
    }

    /**
     * Binds value to attribute. Value can be from Context
     * @param string $table
     * @param string $attribute
     * @param int|string $value
     * @return DbQuery
     */
    private function bind($table, $attribute, $value) {
        $table && $table .= '.';
        $context = Context::getContext();
        $value = property_exists('Context', $value) ? $context->$value->id : $value;
        return $this->where(sprintf('%s%s = %d', $table, $attribute, $value));
    }
    
    /**
     * Escapes value
     * @param mixed $value
     * @return string
     */
    private function escape($value) {
        if (is_string($value)) {
            $value = pSQL($value);
        } else if (is_array($value)) {
            $value = '('.implode (',', array_map ([$this, 'escape'], $value)).')';
        }
        
        return $value;
    }
}
