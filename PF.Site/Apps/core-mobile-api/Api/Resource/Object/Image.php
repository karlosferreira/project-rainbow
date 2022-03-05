<?php

namespace Apps\Core_MobileApi\Api\Resource\Object;


class Image
{
    public $image_url;
    public $sizes;

    public static function createFrom($params, $sizes = [], $onlySquare = true)
    {
        $image = new Image();
        if (empty($params['file']) && empty($params['user']) && empty($params['theme'])) {
            return null;
        }
        if (!empty($params['user']) && empty($params['user']['user_image'])) { // Use has no image
            return null;
        }
        if (!empty($params['user']) && empty($params['user']['user_name'])) { // Use has no user name
            $params['user']['user_name'] = '';
        }
        $params['return_url'] = true;
        $image->image_url = \Phpfox_Image_Helper::instance()->display($params);
        if (substr($image->image_url, 0, 4) != "http") {
            return null;
        }
        if (!empty($sizes)) {
            //Prevent get empty
            foreach ($sizes as $size) {
                if (strpos($size, '_square') > -1) {
                    $image->sizes[$size] = \Phpfox_Image_Helper::instance()->display(array_merge($params, [
                        'suffix' => "_{$size}"
                    ]));
                } else {
                    if ($onlySquare) {
                        $image->sizes[$size] = \Phpfox_Image_Helper::instance()->display(array_merge($params,
                            [
                                'suffix' => "_{$size}_square"
                            ]));
                    } else {
                        $image->sizes[$size] = \Phpfox_Image_Helper::instance()->display(array_merge($params, [
                            'suffix' => "_{$size}"
                        ]));
                    }
                }
            }
        }

        return $image;
    }

    public function toArray()
    {
        if (empty($this->sizes)) {
            return $this->image_url;
        }
        $image = [
            'image_url' => $this->image_url
        ];
        foreach ($this->sizes as $size => $url) {
            $image[$size] = $url;
        }
        return $image;
    }
}