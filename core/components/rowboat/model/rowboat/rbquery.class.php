<?php
/**
 * Rowboat
 *
 * Copyright 2011 by Shaun McCormick <shaun+rowboat@modx.com>
 *
 * Rowboat is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Rowboat is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Rowboat; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package rowboat
 */
/**
 * Abstract class for Query generation for Rowboat. Must be extended per driver and never loaded directly.
 * 
 * @package rowboat
 */
abstract class rbQuery {
    const SEPARATOR = '.';

    protected $_prepared = false;
    protected $_table = '';
    protected $_tableAlias = '';
    protected $_sql = '';
    protected $_columns = array();
    protected $_limit = 0;
    protected $_offset = 0;
    protected $_sort = array();
    protected $_where = array();
    protected $_params = array();
    protected $_preparedSql = '';
    
    protected $_operators= array (
        '=',
        '!=',
        '<',
        '<=',
        '>',
        '>=',
        '<=>',
        'LIKE',
        'IS NULL',
        'IS NOT NULL',
        'BETWEEN',
        'IN',
        'IN(',
        'NOT(',
        'NOT (',
        'NOT IN ',
        'NOT IN(',
        'EXISTS (',
        'EXISTS(',
        'NOT EXISTS (',
        'NOT EXISTS(',
        'COALESCE(',
        'GREATEST(',
        'INTERVAL(',
        'LEAST(',
        'MATCH(',
        'MATCH ('
    );
    protected $_conditionals = array(
        'AND',
        'OR',
    );
    public $stmt;
    
    function __construct(modX &$modx,$table = '',array $config = array()) {
        $this->modx = $modx;
        $this->setTable($table);
        $this->config = array_merge(array(

        ),$config);
    }

    /**
     * Set the SQL table to pull data from
     * 
     * @param string $table The table to pull from
     */
    public function setTable($table) {
        $this->_table = $table;
    }

    /**
     * Set the table alias for the query
     * 
     * @param string $tableAlias A valid table alias
     */
    public function setTableAlias($tableAlias) {
        $this->_tableAlias = $tableAlias;
    }

    /**
     * Prepare the query for execution
     * @return string The prepared SQL string
     * @abstract
     */
    abstract public function prepare();

    /**
     * Prepare the SELECT statement for the query
     * @abstract
     */
    abstract protected function prepareSelect();
    /**
     * Prepare the SORT statement for the query
     * @abstract
     */
    abstract protected function prepareSort();
    /**
     * Prepare the LIMIT statement for the query
     * @abstract
     */
    abstract protected function prepareLimit();
    /**
     * Build and append a WHERE statement to the query
     * @abstract
     */
    abstract protected function prepareWhere();

    /**
     * Add a parameter to the statement
     * 
     * @param string $k The column in the table
     * @param string $v The value to set to the parameter
     * @return string The key that was set
     */
    public function addParam($k,$v) {
        if (!empty($v)) {
            $kz = explode('.',$k);
            $k = !empty($kz[1]) ? $kz[1] : $kz[0];
            if (array_key_exists($k,$this->_params)) {
                $k = $k.uniqid($k);
            }

            $this->_params[$k] = $v;
            $k = ':'.$k;
        } else { $k = '""'; }
        return $k;
    }

    /**
     * Execute a prepared statement
     * 
     * @return bool True if successful
     */
    public function execute() {
        if (!$this->_prepared) {
            $this->prepare();
        }
        $this->stmt = $this->modx->prepare($this->_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!empty($this->_params) && is_array($this->_params)) {
            foreach ($this->_params as $k => $v) {
                $this->stmt->bindValue(':'.$k,$v);
            }
        }
        $this->stmt->execute();
        return $this->success();
    }

    /**
     * Close the cursor of this query
     */
    public function close() {
        return $this->stmt->closeCursor();
    }

    /**
     * Determine if the statement was a successful query
     * 
     * @return bool True if successful
     */
    public function success() {
        return $this->stmt && $this->stmt instanceof PDOStatement;
    }

    /**
     * Return an array of parameters set to this query
     * 
     * @return array An associative array of parameters assigned to this query
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * Return the table name
     *
     * @return string The SQL database table name
     */
    public function getTable() {
        return $this->_table;
    }

    /**
     * Prefix a field with the table alias
     *
     * @param string $field A column to prefix with
     * @return string The prefixed field
     */
    public function prefixWithTableAlias($field) {
        return !empty($this->_tableAlias) ? $this->escape($this->_tableAlias).rbQuery::SEPARATOR.$field : $field;
    }

    /**
     * Escape a field name or value
     *
     * @param string $v The value or field to escape
     * @return array|string The properly escaped value
     */
    public function escape($v) {
        $escape = !preg_match('/\bAS\b/i', $v) && !preg_match('/\(/', $v);
        if (!$escape) return $v;

        if (strpos($v,rbQuery::SEPARATOR) !== false) {
            $parts = explode(rbQuery::SEPARATOR,$v);
            $v = array();
            foreach ($parts as $part) {
                $v[] = $this->modx->escape($part);
            }
            $v = implode(rbQuery::SEPARATOR,$v);
        } else {
            $v = $this->modx->escape($v);
        }

        return $v;
    }

    /**
     * Add a WHERE statement to the query object
     * 
     * @param string|array $criteria The criteria to add, either in string or array format
     */
    public function where($criteria) {
        if (is_array($criteria)) {
            foreach ($criteria as $col => $v) {
                $this->condition(array($col => $v));
            }
        } else {
            $this->condition($criteria);
        }
    }

    /**
     * Add a condition to the query
     *
     * @param string $criteria
     */
    protected function condition($criteria) {
        $this->_where[] = $criteria;
    }

    /**
     * Add a SORT BY statement to the query
     *
     * @param string $col The column or phrase to sort by
     * @param string $dir The direction to sort by
     */
    public function sortby($col,$dir = '') {
        $this->_sort[]= array ('column' => $col, 'direction' => $dir);
    }

    /**
     * An array or string of columns to select for this query
     *
     * @param string $columns The columns to select
     */
    public function select($columns = '*') {
        if (!is_array($columns)) {
            $columns= trim($columns);
            $columns= explode(',', $columns);
            foreach ($columns as $column => $alias) $columns[$column] = trim($alias);
        }
        if (is_array ($columns)) {
            if (!is_array($this->_columns)) {
                $this->_columns= $columns;
            } else {
                $this->_columns = array_merge($this->_columns,$columns);
            }
        }
    }

    /**
     * Set the columns for this query. Use ->select instead.
     *
     * @param array $columns An array of columns.
     */
    public function setColumns(array $columns = array()) {
        $this->_columns = $columns;
    }

    /**
     * Add a limit statement to the query
     *
     * @param int $limit The number of results to limit
     * @param int $offset The starting index of the limited results
     */
    public function limit($limit = 0,$offset = 0) {
        $limit = intval($limit);
        if ($limit > 0) {
            $this->_limit = $limit;
            $offset = intval($offset);
            if ($offset > 0) {
                $this->_offset = $offset;
            }
        }
    }

    /**
     * Run the PDO fetch command to get the results of the prepared statement
     * 
     * @param int $fetchStyle The style by which to fetch the results
     * @return array An array of returned results
     */
    public function getResults($fetchStyle = PDO::FETCH_ASSOC) {
        $results = array();
        while ($row = $this->stmt->fetch($fetchStyle)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Return the formatted, prepared SQL query statement in text format
     *
     * @return string The string SQL query
     */
    public function toSql($replaceParams = true) {
        if (!$this->_prepared) {
            $this->prepare();
        }
        $sql = implode("\n ",$this->_preparedSql);
        if ($replaceParams) {
            foreach ($this->_params as $key => $value) {
                $v = $value;
                if (!is_int($v)) {
                    $v = '"'.$v.'"';
                }
                $sql = str_replace(':'.$key,$v,$sql);
            }
        }
        return $sql;
    }

    /**
     * Get a count of results for this statement
     *
     * @abstract
     * @return int The number of results for the query
     */
    abstract public function count();
}
