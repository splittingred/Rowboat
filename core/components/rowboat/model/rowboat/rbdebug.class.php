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
 * Handles all Rowboat debugging information
 * 
 * @package rowboat
 */
class rbDebug {
    public $oldLogTarget = null;
    public $oldLogLevel = null;
    public $rowboat = null;
    public $modx = null;

    public $messages = array();
    public $results = array();
    public $query = null;
    public $total = 0;

    function __construct(Rowboat &$rowboat,array $config = array()) {
        $this->rowboat =& $rowboat;
        $this->modx =& $rowboat->modx;
        $this->config = array_merge(array(

        ),$config);
        
        $this->oldLogTarget = $this->modx->getLogTarget();
        $this->oldLogLevel = $this->modx->getLogLevel();
        $this->modx->setLogTarget('ECHO');
        $this->modx->setLogLevel(modX::LOG_LEVEL_INFO);
    }

    /**
     * Finish debug mode and return the rendered debug info
     * 
     * @return string A nice HTML table of debugging info
     */
    public function finish() {
        $this->modx->setLogTarget($this->oldLogTarget);
        $this->modx->setLogLevel($this->oldLogLevel);
        return $this->getOutput();
    }

    /**
     * Set the rbQuery object for debugging information
     * 
     * @param rbQuery $query
     */
    public function setQuery(rbQuery &$query) {
        $this->query =& $query;
    }

    /**
     * Set the total count of results
     * 
     * @param int $total
     */
    public function setTotal($total) {
        $this->total = intval($total);
    }

    /**
     * Set the results array for debugging
     *
     * @param array $results An array of results returned
     */
    public function setResults(array $results = array()) {
        $this->results = $results;
    }

    /**
     * Add a message to the Rowboat debug message queue
     * @param string $message
     */
    public function addMessage($message) {
        $this->messages[] = $message;
    }

    /**
     * Format the debugging information into a nice, presentable format
     *
     * @return string The rendered HTML for the debug info
     */
    public function getOutput() {
        $output = array();
        $output[] = '<style>.rb-debug td, .rb-debug th { border: 1px solid #aaa; padding: 3px;}.rb-debug td { text-align: left; }.rb-debug th { font-weight: bold; background-color: #eee; vertical-align: top; }.rb-debug { border: 1px solid #aaa; }</style>';
        $output[] = '<table class="rb-debug" cellspacing="0">';

        $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.header'));

        if (!empty($this->query)) {
            $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.table'),$this->query->getTable());
            $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.query'),nl2br($this->query->toSql()));
            $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.pdo_query'),nl2br($this->query->toSql(false)));
            $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.params'),'<pre>'.var_export($this->query->getParams(),true).'</pre>');
            $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.total'),$this->total);
        }

        if (!empty($this->messages)) {
            $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.messages'));
            foreach ($this->messages as $message) {
                $output[] = $this->renderRow('',$message);
            }
        }
        
        $output[] = $this->renderRow($this->modx->lexicon('rowboat.debug.results'));

        $idx = 0;
        foreach ($this->results as $result) {
            $row = array();
            $row[] = '<table>';
            foreach ($result as $col => $val) {
                $row[] = $this->renderRow($col,$val == '' ? ' ' : $val);
            }
            $row[] = '</table>';
            $output[] = $this->renderRow('#'.$idx,implode("\n",$row));
            $idx++;
        }
        $output[] = '</table>';
        return implode("\n",$output);
    }

    /**
     * Render a row for the debug table
     * 
     * @param string $title The header of the row
     * @param string $message The content of the row
     * @return string The rendered row
     */
    public function renderRow($title = '',$message = '') {
        $o = array();
        $o[] = '<tr>';
        if ($title != '' && $message != '') {
            $o[] = '<th>'.$title.'</th>';
            $o[] = '<td>'.$message.'</td>';
        } else if ($title != '') {
            $o[] = '<th colspan="2">'.$title.'</th>';
        } else {
            $o[] = '<td colspan="2">'.$message.'</td>';
        }
        $o[] = '</tr>';
        return implode("\n",$o);
    }
}
