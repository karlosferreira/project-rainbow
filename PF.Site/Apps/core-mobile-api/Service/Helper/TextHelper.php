<?php

namespace Apps\Core_MobileApi\Service\Helper;


use Phpfox;

class TextHelper
{
    public static function getHtml($text)
    {
        return Phpfox::getLib("parse.output")->parse($text);
    }

    public static function cleanHtml($text)
    {
        $text = strip_tags($text);
        $text = Phpfox::getLib("parse.input")->clean($text);
        return $text;
    }
}