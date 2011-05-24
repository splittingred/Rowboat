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
 * Properties English Lexicon Entries for Rowboat
 *
 * @package rowboat
 * @subpackage lexicon
 */
$_lang['prop_rowboat.cacheresults_desc'] = 'If set to 1, will cache the results of the specific query.';
$_lang['prop_rowboat.cachetime_desc'] = 'If cacheResults is set to 1, the number of seconds to cache the query for.';
$_lang['prop_rowboat.columns_desc'] = 'The columns to pull from the query. If not set, will grab all the columns in the table.';
$_lang['prop_rowboat.debug_desc'] = 'If set to 1, will output the SQL that is generated for the query. Always leave at 0 for production sites.';
$_lang['prop_rowboat.limit_desc'] = 'The number of results to limit per page.';
$_lang['prop_rowboat.normal'] = 'Normal';
$_lang['prop_rowboat.offset_desc'] = 'The offset to start the results from.';
$_lang['prop_rowboat.outputseparator_desc'] = 'A string to separate each row with.';
$_lang['prop_rowboat.placeholderprefix_desc'] = 'The prefix to use for all the placeholders set by Rowboat.';
$_lang['prop_rowboat.returnformat_desc'] = 'Set this to return the data in an alternate format.';
$_lang['prop_rowboat.sortby_desc'] = 'The column to sort by.';
$_lang['prop_rowboat.sortdir_desc'] = 'The direction to sort by.';
$_lang['prop_rowboat.table_desc'] = 'The database table to query.';
$_lang['prop_rowboat.tpl_desc'] = 'The chunk to use for each row of results. If blank, will output an array of properties available for the tpl.';
$_lang['prop_rowboat.toplaceholder_desc'] = 'If set, will output the content to the placeholder specified in this property, rather than outputting the content directly.';
$_lang['prop_rowboat.where_desc'] = 'A JSON where statement, such as {"name":"value"}. See the docs for more examples.';
