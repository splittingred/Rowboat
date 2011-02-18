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
 * @package rowboat
 */
class rbQuery {
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

    public function setTable($table) {
        $this->_table = $table;
    }
    public function setTableAlias($tableAlias) {
        $this->_tableAlias = $tableAlias;
    }

    public function prepare() {
        if (!empty($this->_columns)) {
            $cs = array();
            foreach ($this->_columns as $column => $alias) {
                if (empty($alias)) $alias = $column;
                $cs[] = $alias != $column && !is_integer($column) ? $this->escape($column).' AS '.$this->escape($alias) : $this->escape($alias);
            }
            $columns = implode(',',$cs);
        } else { $columns = '*'; }

        $sql = 'SELECT '.$columns.' FROM '.$this->escape($this->_table);
        if (!empty($this->_tableAlias)) $sql .= ' '.$this->escape($this->_tableAlias);

        $sql = $this->_buildWhere($sql);

        if (!empty($this->_sort)) {
            $sort= reset($this->_sort);
            $sql .= ' ORDER BY ';
            $sql .= $this->escape($sort['column']);
            if ($sort['direction']) $sql .= ' ' . $sort['direction'];
            while ($sortby= next($this->_sort)) {
                $sql.= ', ';
                $sql.= $this->escape($sortby['column']);
                if ($sortby['direction']) $sql.= ' ' . $sortby['direction'];
            }
        }
        if (!empty($this->_limit)) {
            $sql .= ' LIMIT '.(!empty($this->_offset) ? $this->_offset.',' : '').$this->_limit;
        }
        
        $this->_sql = trim($sql);
        $this->_prepared = true;
    }

    protected function _buildWhere($sql) {
        //var_dump($this->_where);
        $tw = array();
        if (!empty($this->_where)) {
            $sql .= ' WHERE';
            foreach ($this->_where as $condition) {
                if (is_array($condition)) {
                    foreach ($condition as $k => $v) {
                        $operand = empty($tw) ? '' : 'AND';
                        $operator = '=';
                        if ($k === 0) {
                            $tw[] = $operand.' '.$v;
                        } else if (is_string($k)) {
                            $op = explode(':',$k);
                            if (count($op) == 1) {
                                $field = $op[0];

                            } else if (in_array($op[0],$this->_conditionals)) {
                                $operand = 'OR';
                                $field = $op[1];
                                if (!empty($op[2])) {
                                    $operator = $op[2];
                                }
                            } else {
                                if (!empty($op[1])) {
                                    $operator = $op[1];
                                }
                                $field = $op[0];
                            }
                            $tw[] = $operand.' '.$this->escape($field).' '.$operator.' '.$this->addParam($field,$v);
                        }
                    }
                }
            }
        }
        $sql .= ' '.ltrim(implode(" ",$tw),' AND ');

        return $sql;
    }

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

    public function execute() {
        if (!$this->_prepared) {
            $this->prepare();
        }
        $this->stmt = $this->modx->prepare($this->_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if (!empty($this->_params) && is_array($this->_params)) {
            foreach ($this->_params as $k => $v) {
                $this->stmt->bindParam(':'.$k,$v);
            }
        }
        $this->stmt->execute();
        return $this->success();
    }

    public function close() {
        return $this->stmt->closeCursor();
    }
    public function success() {
        return $this->stmt && $this->stmt instanceof PDOStatement;
    }


    public function prefixWithTableAlias($field) {
        return !empty($this->_tableAlias) ? $this->escape($this->_tableAlias).rbQuery::SEPARATOR.$field : $field;
    }

    public function escape($v) {
        $escape = !preg_match('/\bAS\b/i', $v) && !preg_match('/\(/', $v);
        if (!$escape) return $v;

        if (strpos($v,'.') !== false) {
            $parts = explode('.',$v);
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

    public function where($criteria) {
        if (is_array($criteria)) {
            foreach ($criteria as $col => $v) {
                $this->condition(array($col => $v));
            }
        } else {
            $this->condition($criteria);
        }
    }

    protected function condition($criteria) {
        $this->_where[] = $criteria;
    }

    public function sortby($col,$dir = '') {
        $this->_sort[]= array ('column' => $col, 'direction' => $dir);
    }

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

    public function setColumns(array $columns = array()) {
        $this->_columns = $columns;
    }

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

    public function getResults($fetchStyle = PDO::FETCH_ASSOC) {
        $results = array();
        while ($row = $this->stmt->fetch($fetchStyle)) {
            $results[] = $row;
        }
        return $results;
    }


    public function toSql() {
        if (!$this->_prepared) {
            $this->prepare();
        }
        return $this->_sql;
    }

    public function count() {
        $total = 0;
        $this->setColumns(array('COUNT(*) '.$this->modx->escape('ct')));
        if ($this->execute()) {
            $count = $this->getResults();
            if (!empty($count) && !empty($count[0]['ct'])) {
                $total = intval($count[0]['ct']);
            }
            $this->close();
        }
        return $total;
    }
}
