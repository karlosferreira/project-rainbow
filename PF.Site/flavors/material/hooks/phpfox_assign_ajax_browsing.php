<?php

if (!empty($aBreadCrumbs) && empty($aBreadCrumbTitle)) {
    list($value, $key) = [end($aBreadCrumbs), key($aBreadCrumbs)];
    unset($aBreadCrumbs[$key]);
    $aBreadCrumbTitle = [$value, $key, 1];
}