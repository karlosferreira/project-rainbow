<?php

namespace Apps\Core_MobileApi\Api;

/**
 * Document resource common interface
 *
 * Interface ResourceInterface
 * @package Apps\Core_MobileApi\Api
 */
interface ResourceInterface
{
    function findAll($params = []);

    function findOne($params);

    function create($params);

    function update($params);

    function patchUpdate($params);

    function delete($params);

    function form($params = []);

    function loadResourceById($id, $returnResource = false);

    function approve($params);

    function feature($params);

    function sponsor($params);

}