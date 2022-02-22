<?php

namespace Apps\Core_MobileApi\Api\Form;


interface TransformerInterface
{

    /**
     * Convert client format to database format
     *
     * @param $value
     *
     * @return mixed
     */
    public function transform($value);

    /**
     * Convert database format to client format
     *
     * @param $data
     *
     * @return mixed
     */
    public function reverseTransform($data);

}