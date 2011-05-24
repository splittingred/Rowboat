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
 * Properties for the Rowboat snippet.
 *
 * @package rowboat
 * @subpackage build
 */
$properties = array(
    array(
        'name' => 'tpl',
        'desc' => 'prop_rowboat.tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'table',
        'desc' => 'prop_rowboat.table_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'columns',
        'desc' => 'prop_rowboat.columns_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'where',
        'desc' => 'prop_rowboat.where_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'sortBy',
        'desc' => 'prop_rowboat.sortby_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'name',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'sortDir',
        'desc' => 'prop_rowboat.sortdir_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'ASC',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'limit',
        'desc' => 'prop_rowboat.limit_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 10,
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'offset',
        'desc' => 'prop_rowboat.offset_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 0,
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'cacheResults',
        'desc' => 'prop_rowboat.cacheresults_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => true,
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'cacheTime',
        'desc' => 'prop_rowboat.cachetime_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 3600,
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'outputSeparator',
        'desc' => 'prop_rowboat.outputseparator_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'returnFormat',
        'desc' => 'prop_rowboat.returnformat_desc',
        'type' => 'textfield',
        'options' => array(
            array('text' => 'prop_rowboat.normal','value' => ''),
            array('text' => 'JSON','value' => 'json'),
        ),
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'placeholderPrefix',
        'desc' => 'prop_rowboat.placeholderprefix_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'rowboat.',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'toPlaceholder',
        'desc' => 'prop_rowboat.toplaceholder_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    array(
        'name' => 'debug',
        'desc' => 'prop_rowboat.debug_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => false,
        'lexicon' => 'rowboat:properties',
    ),
/*
    array(
        'name' => '',
        'desc' => 'prop_rowboat.',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'rowboat:properties',
    ),
    */
);

return $properties;