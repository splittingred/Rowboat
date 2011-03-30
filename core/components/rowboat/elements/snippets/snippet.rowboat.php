<?php
/**
 * The base Rowboat snippet.
 *
 * @package rowboat
 */
$rowboat = $modx->getService('rowboat','Rowboat',$modx->getOption('rowboat.core_path',null,$modx->getOption('core_path').'components/rowboat/').'model/rowboat/',$scriptProperties);
if (!($rowboat instanceof Rowboat)) return '';

/* setup default properties */
$tpl = $modx->getOption('tpl',$scriptProperties,'');
$table = $modx->sanitizeString($modx->getOption('table',$scriptProperties,''));
if (empty($table)) return '';
$columns = $modx->getOption('columns',$scriptProperties,'');
$where = $modx->getOption('where',$scriptProperties,'');
$sortBy = $modx->getOption('sortBy',$_REQUEST,$modx->getOption('sortBy',$scriptProperties,''));
$sortBy = $modx->sanitizeString($sortBy);
$sortDir = $modx->getOption('sortDir',$_REQUEST,$modx->getOption('sortDir',$scriptProperties,'ASC'));
$sortDir = $modx->sanitizeString($sortDir);
$limit = $modx->getOption('limit',$_REQUEST,$modx->getOption('limit',$scriptProperties,10));
$offset = $modx->getOption('offset',$_REQUEST,$modx->getOption('offset',$scriptProperties,0));
$cacheResults = $modx->getOption('cacheResults',$scriptProperties,1);
$cacheTime = $modx->getOption('cacheTime',$scriptProperties,3600);
$outputSeparator = $modx->getOption('outputSeparator',$scriptProperties,"\n");
$placeholderPrefix = $modx->getOption('placeholderPrefix',$scriptProperties,'rowboat.');
$debug = $modx->getOption('debug',$scriptProperties,false);

$total = 0;
$results = array();

if (!empty($debug)) {
    $rowboat->loadDebugMode();
}

/* build query */
$c = $rowboat->newQuery($table);
if (empty($c)) {
    return $modx->lexicon('rowboat.no_driver',array('dbtype' => $modx->config['dbtype']));
}
if ($columns != '*') {
    $columns = $modx->fromJSON($columns);
    if (!empty($columns)) {
        $c->select($columns);
    }
}
if (!empty($where)) {
    $where = $modx->fromJSON($where);
    if (!empty($where)) {
        $c->where($where);
    }
}

if (!empty($sortBy)) {
    $c->sortby($sortBy,$sortDir);
}
if (intval($limit) > 0) {
    $cc = clone $c;
    $c->limit($limit,$offset);
}
$sql = $c->toSql();

if (!empty($debug)) {
    $rowboat->debug->setQuery($c);
}

/* check for cache */
$cached = false;
$cacheKey = false;
if (!empty($cacheResults)) {
    $cacheKey = 'rowboat/'.base64_encode($sql);
    $cacheArray = $modx->cacheManager->get($cacheKey);
    if (!empty($cacheArray)) {
        $cached = true;
        $results = $cacheArray['results'];
        $total = $cacheArray['total'];
    }
    if (!empty($debug)) {
        $rowboat->debug->addMessage($modx->lexicon('rowboat.debug.cached_results'));
    }
}

if (!empty($rowboat->debug)) {
    $rowboat->debug->setTotal($total);
}

/* run query */
if (empty($cached)) {
    if ($c->execute()) {
        $results = $c->getResults();
        if (!empty($cc)) {
            $total = $cc->count();
        } else {
            $total = count($results);
        }
        $c->close();
        if (!empty($cacheResults) && !empty($results) && !empty($cacheKey)) {
            $cacheArray = array(
                'results' => $results,
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
            );
            $modx->cacheManager->set($cacheKey,$cacheArray,$cacheTime);
        }
    }
}

if (!empty($rowboat->debug)) {
    $rowboat->debug->setResults($results);
}

/* iterate across results */
$output = array();
if (is_array($results)) {
    $ct = count($results);
    $idx = 0;
    foreach ($results as $row) {
        $row['_idx'] = $idx;
        $row['_alt'] = $idx % 2;
        if ($idx == 0) $row['_first'] = true;
        if ($idx == $ct-1) $row['_last'] = true;
        
        if (!empty($tpl)) {
            $output[] = $rowboat->getChunk($tpl,$row);
        } else {
            $output[] = print_r($row,true);
        }
        $idx++;
    }
}

/* set placeholders */
$placeholders = array();
$placeholders['total'] = $total;
$placeholders['offset'] = $offset;
$placeholders['limit'] = $limit;
$modx->setPlaceholders($placeholders,$placeholderPrefix);

/* output */
$output = implode($outputSeparator,$output);
if (!empty($rowboat->debug)) {
    $output .= $rowboat->debug->finish();
}
$toPlaceholder = $modx->getOption('toPlaceholder',$scriptProperties,false);
if (!empty($toPlaceholder)) {
    /* if using a placeholder, output nothing and set output to specified placeholder */
    $modx->setPlaceholder($toPlaceholder,$output);
    return '';
}
/* by default just return output */
return $output;