<?php
preg_match_all('/(?<match>\[([\w]+)=([\d]+)\](?<name>[\p{L}\p{P}\p{S}\p{N}\s]+)\[\/([\w]+)\])/Umu', $sText, $matches);
if(isset($matches['match'])) {
    foreach($matches['match'] as $key=>$match) {
        if(isset($matches['name'][$key])) {
            $sText = str_replace($match, $matches['name'][$key], $sText);
        }
    }
}