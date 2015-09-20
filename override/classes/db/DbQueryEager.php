<?php

/**
 * Implement Eager loading
 * Needs the DbQuery override
 * @author Salerno Simone
 */
class DbQueryEager extends DbQuery {
    const KEEP_ALL = 1;
    const KEEP_LAST = 2;
    public static $CACHE_STRATEGY = 1;
    
    /**
     * Uniqeu id used to identify records in the cache
     * @var string
     */
    private $id;
    /**
     * "eager" params
     * @var array
     */
    private $params;

    /**
     * @param string $id
     */
    public function __construct($id) {
        $this->id = $id;
        $this->params = [];
    }
    
    /**
     * Add "eager" param
     * @param string $name
     * @param mixed $value
     * @return \DbQueryEager
     */
    public function addEagerParam($name, $value) {
        $this->select($name);
        $this->params[$name] = $value;
        return $this;
    }
    
    /**
     * @see DbQuery::getRow()
     * actually implements eager loading
     * @return array
     */
    public function getRow() {
        //keep batch data in cache
        $cache = Cache::isStored(__CLASS__) ? Cache::retrieve(__CLASS__) : [];
        
        if (!isset($cache[$this->id])) {
            $results = Db::getInstance()->executeS($this);
            !$results && $results = [];
            $indexed = $this->indexResults($results);
            //empty cache if using KEEP_LAST strategy
            //@todo not working for KEEP_LAST
            if (static::$CACHE_STRATEGY === static::KEEP_LAST)
                $cache = [];
            
            $cache[$this->id] = $indexed;
            Cache::store(__CLASS__, $cache);
        }
        
        $currentIndex = $this->makeIndexFromValues(array_values($this->params));
        return isset($cache[$this->id][$currentIndex]) ? $cache[$this->id][$currentIndex] : [];
    }
    
    /**
     * Like getRow(), but let's you use params late binding
     * @param array $params
     * @return array
     */
    public function row(array $params = []) {
        $this->params = array_merge($this->params, $params);
        return $this->getRow();
    }

    /**
     * Like getValue(), but let's you use params late binding
     * @param string $name row attribute to get
     * @param array $params
     * @return mixed
     */
    public function value($name, array $params = []) {
        $this->select($name);
        $row = $this->row($params);
        return $row && isset($row[$name]) ? $row[$name] : NULL;
    }
    
    /**
     * Generate access key from params values
     * @param array $values
     * @return string
     */
    private function makeIndexFromValues(array $values) {
        return implode('|', $values);
    }

    /**
     * Like makeIndexFromValues(), but extracts "eager values" from db row
     * @param array $row
     * @return string
     */
    private function makeIndexFromRow(array $row) {
        //extract only the values from the "eager" keys
        //from: http://stackoverflow.com/questions/4260086/php-how-to-use-array-filter-to-filter-array-keys
        $extract = array_intersect_key($row, array_flip(array_keys($this->params)));
        return $this->makeIndexFromValues($extract);
    }

    /**
     * Index result array by "eager" values
     * @param array $results
     * @return array
     */
    private function indexResults(array $results) {
        $keys = array_map([$this, 'makeIndexFromRow'], $results);
        $values = array_values($results);
        return array_combine($keys, $values);
    }
}
