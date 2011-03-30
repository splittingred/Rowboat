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
require_once dirname(dirname(__FILE__)).'/rbquery.class.php';
/**
 * @package rowboat
 */
class rbQuery_mysql extends rbQuery {
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

    /**
     * Prepare the query for execution
     * 
     * {@inheritDoc}
     */
    public function prepare() {
        $this->_preparedSql = array();
        $this->prepareSelect();
        
        if (!empty($this->_where)) {
            $this->prepareWhere();
        }
        if (!empty($this->_sort)) {
            $this->prepareSort();
        }
        if (!empty($this->_limit)) {
            $this->prepareLimit();
        }

        $this->_sql = trim(implode(" ",$this->_preparedSql));
        $this->_prepared = true;
        return $this->_sql;
    }

    /**
     * Prepare the SELECT statement for the query
     *
     * {@inheritDoc}
     */
    protected function prepareSelect() {
        $columns = '*';
        if (!empty($this->_columns)) {
            $cs = array();
            foreach ($this->_columns as $column => $alias) {
                if (empty($alias)) $alias = $column;
                $cs[] = $alias != $column && !is_integer($column) ? $this->escape($column).' AS '.$this->escape($alias) : $this->escape($alias);
            }
            $columns = implode(',',$cs);
        }
        
        $sql = 'SELECT '.$columns.' FROM '.$this->escape($this->_table);
        if (!empty($this->_tableAlias)) $sql .= ' '.$this->escape($this->_tableAlias);
        $this->_preparedSql[] = $sql;
    }

    /**
     * Build and append a WHERE statement to the query
     *
     * {@inheritDoc}
     */
    protected function prepareWhere() {
        $tw = array();
        $sql = 'WHERE';
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
        $sql .= ' '.ltrim(implode(" ",$tw),' AND ');
        $this->_preparedSql[] = $sql;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareSort() {
        $sort= reset($this->_sort);
        $sql = 'ORDER BY ';
        $sql .= $this->escape($sort['column']);
        if ($sort['direction']) $sql .= ' ' . $sort['direction'];
        while ($sortby= next($this->_sort)) {
            $sql .= ', ';
            $sql .= $this->escape($sortby['column']);
            if ($sortby['direction']) $sql .= ' ' . $sortby['direction'];
        }
        $this->_preparedSql[] = $sql;
    }

    /**
     * Build out the LIMIT statement
     * 
     * {@inheritDoc}
     */
    protected function prepareLimit() {
        $this->_preparedSql[] = 'LIMIT '.(!empty($this->_offset) ? $this->_offset.',' : '').$this->_limit;
    }

    /**
     * Get a count of results for this statement
     *
     * @return int The number of results for the query
     */
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
