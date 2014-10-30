<?php

function match($query, $str){
    // transform the query
    $queryData = explode('@', $query);
    $tag = $queryData[0];
    $attData = explode('=', $queryData[1]);
    $attName = $attData[0];
    $attValue = $attData[1];

    // build the matching pattern
    preg_match_all('|<'.$tag.'([\W\S]+)</w-block>$|Um', $str, $matches);
    die(print_r($matches));
}

$str = '
<div>
<w-block name="content">

        <div style="float:left">
            <h1>Div left</h1>
            <w-block name="content-left">
                content-left: from-2col
            </w-block>
        </div>

        <div style="float:left">
            <h1>Div right</h1>
            <w-block name="content-right">
                content-right: from-2col
            </w-block>
        </div>

        <w-block name="content-middle">
            content-middle: from-2col
        </w-block>

    </w-block>
    </div>';

$result = match('w-block@name=content', $str);