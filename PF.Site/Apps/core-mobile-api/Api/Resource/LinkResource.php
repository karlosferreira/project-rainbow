<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;

class LinkResource extends ResourceBase
{

    const RESOURCE_NAME = "link";

    public $resource_name = self::RESOURCE_NAME;

    public $title;
    public $description;
    public $image;
    public $default_image;
    public $link;
    public $embed_code;
    public $duration;
    public $user;
    public $host;

    public $module_id;
    public $item_id;

    public function getLink()
    {
        return $this->link;
    }
    public function getShortFields()
    {
        return ['resource_name', 'id', 'title', 'description', 'image', 'link', 'host'];
    }

    public function getImage()
    {
        if ($this->image && strpos($this->image, 'http') === false) {
            $this->image = (PHPFOX_IS_HTTPS ? 'https:' : 'http:') . $this->image;
        }
        return $this->image ? htmlspecialchars_decode($this->image) : $this->image;
    }

    public function getDescription()
    {
        if ($this->description) {
            return TextFilter::pureText($this->description, 255, true);
        }
        return null;
    }

    public function getHost()
    {
        $parts = parse_url($this->link);
        $this->host = isset($parts['host']) ? $parts['host'] : '';
        return $this->host;
    }

    public function getDefaultImage($isCover = false, $resource = null)
    {
        if (!empty($this->rawData['default_image'])) {
            $this->default_image = $this->rawData['default_image'];
        }
        return $this->default_image ? htmlspecialchars_decode($this->default_image) : $this->default_image;
    }
}