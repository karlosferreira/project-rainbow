<?php

namespace Apps\Core_MobileApi\Api\Form\Validator\Filter;

use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use HTMLPurifier;
use HTMLPurifier_Config;

class TextFilter
{

    public static function pureText(&$text, $limit = null, $output = false)
    {
        if ($output) {
            $text = \Phpfox::getService(ParseInterface::class)->parseOutput($text, false);
        }
        $text = html_entity_decode($text, ENT_QUOTES);
        $text = preg_replace('/(<div\sclass=\"newline\"><\/div>)+/', ' ', $text);
        $text = preg_replace('/<[^img][^>]*>/', '', $text);
        //We should limit text
        $limit = (($limit === null) ? 255 : $limit);
        if ($limit > 0 && strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit);
            $text .= '...';
        }
        $text = trim($text);
        if ($limit > 0) {
            $text = \Phpfox::getService(ParseInterface::class)->parseTwaEmoji($text);
        }
        return $text;
    }

    public static function pureHtml(&$html, $output = false)
    {
        if ($output) {
            $html = \Phpfox::getService(ParseInterface::class)->parseOutput($html);
            if (!\Phpfox_Request::instance()->get('api_form')) {
                $html = str_replace('<div class="newline"></div>', '<br/>', $html);
            }
        }
        $html = self::createFilter('html')->purify($html);
        $html = trim($html);
        if (!\Phpfox_Request::instance()->get('api_form')) {
            $html = \Phpfox::getService(ParseInterface::class)->parseTwaEmoji($html);
        }
        return $html;
    }

    public static function createFilter($type)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        if ($type == 'text') {
        } else if ($type == 'html') {
            $config->set('HTML.Allowed', 'div, *[style|class|id], img[src], ul, li, ol, strong, b, i, em, pre, h1, h2, h3, h4, h5, hr, u, br, span, p, a[href|title], table[class|width|cellpadding], tr, th, td, tbody, thead, blockquote, iframe[src|width|height|class|frameborder], caption');
            $config->set('Attr.EnableID', true);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^https://%'); //allow YouTube and Vimeo
            if (!\Phpfox_Request::instance()->get('api_form')) {
                $config->set('AutoFormat.AutoParagraph', true);
            }
        }

        return (new HTMLPurifier($config));
    }

    public static function secureText($value)
    {
        if (function_exists('mb_ereg_replace')) {
            $value = mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]', '\\\0', $value);
        } else {

            $value = preg_replace('~[\x00\x0A\x0D\x1A\x22\x27\x5C]~u', '\\\$0', $value);
        }
        return $value;
    }
}