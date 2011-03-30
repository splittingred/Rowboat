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
 * The base class for Rowboat.
 *
 * @package rowboat
 */
class Rowboat {
    /**
     * @var rbDebug A reference to the rbDebug object
     */
    public $debug = null;
    /**
     * @var modX A reference to the modX object.
     */
    public $modx = null;
    /**
     * @var array An array of configuration options
     */
    public $config = array();

    function __construct(modX &$modx,array $config = array()) {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('rowboat.core_path',$config,$this->modx->getOption('core_path').'components/rowboat/');
        $assetsUrl = $this->modx->getOption('rowboat.assets_url',$config,$this->modx->getOption('assets_url').'components/rowboat/');
        $connectorUrl = $assetsUrl.'connector.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl.'css/',
            'jsUrl' => $assetsUrl.'js/',
            'imagesUrl' => $assetsUrl.'images/',

            'connectorUrl' => $connectorUrl,

            'corePath' => $corePath,
            'modelPath' => $corePath.'model/',
            'chunksPath' => $corePath.'elements/chunks/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath.'elements/snippets/',
            'processorsPath' => $corePath.'processors/',
        ),$config);

        $this->modx->addPackage('rowboat',$this->config['modelPath']);
        $this->modx->lexicon->load('rowboat:default');
    }

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name,array $properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->modx->getObject('modChunk',array('name' => $name),true);
            if (empty($chunk)) {
                $chunk = $this->_getTplChunk($name,$this->config['chunkSuffix']);
                if ($chunk == false) return false;
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }
    /**
     * Returns a modChunk object from a template file.
     *
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.chunk.tpl by default.
     * @param string $suffix The suffix to add to the chunk filename.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function _getTplChunk($name,$suffix = '.chunk.tpl') {
        $chunk = false;
        $f = $this->config['chunksPath'].strtolower($name).$suffix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name',$name);
            $chunk->setContent($o);
        }
        return $chunk;
    }

    /**
     * Load a new rbQuery object
     *
     * @param string $table The table to base the query object on
     * @return rbQuery
     */
    public function newQuery($table) {
        $c = null;
        $driverClass = 'rbQuery_'.$this->modx->config['dbtype'];
        if ($this->modx->loadClass('rowboat.drivers.'.$driverClass,$this->config['modelPath'],true,true)) {
            $c = new $driverClass($this->modx,$table);
        }
        return $c;
    }

    /**
     * Load Debugging mode
     */
    public function loadDebugMode() {
        if ($this->modx->loadClass('rowboat.rbDebug',$this->config['modelPath'],true,true)) {
            $this->debug = new rbDebug($this);
        }
        return $this->debug;
    }
}