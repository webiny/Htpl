<?php

namespace Webiny\Htpl\Functions;

use Webiny\Htpl\Processor\Selector;
use Webiny\Htpl\HtplException;

class WImage extends FunctionAbstract
{

    /**
     * Return the html tag that the function is attached to.
     *
     * @return string
     */
    public static function getTag()
    {
        return 'img';
    }

    /**
     * This is a callback method when we match the tag that the function is registered for.
     * The method will receive a list of attributes that the tag has associated.
     * The method should return a string that should replace the matching tag.
     * If the method returns false, no replacement will occur.
     *
     * @param string     $content
     * @param array|null $attributes
     *
     * @throws HtplException
     * @return string|bool
     */
    public static function parseTag($content, $attributes)
    {
        if ($content == '') {
            //standard behaviour
            $properties = self::_parseProperties($attributes['style']);
            $width = isset($properties['width']) ? $properties['width'] : '100%';
            $height = isset($properties['height']) ? $properties['height'] : '100%';

            $output = ' echo \Webiny\Htpl\Functions\WImage::getImage("' . $attributes['src'] . '", "' . $width . '", "' . $height . '"';
            if (isset($properties['resize'])) {
                $output .= ', "' . $properties['resize'] . '"';
            }

            if (isset($properties['fill'])) {
                $output .= ', "' . $properties['fill'] . '"';
            }

            $output .= ');';
        }else{
            // get default image
            $defaultData = Selector::select($content, '//default');
            $default = false;
            if (count($defaultData) > 0) {
                $default = $defaultData[0]['content'];
            }

            // get main image
            $main = isset($attributes['src']) ? $attributes['src'] : false;

            // extract images
            $imageTypes = [
                'desktop',
                'tablet',
                'mobile'
            ];
            $images = [];
            foreach ($imageTypes as $it) {
                $imageData = Selector::select($content, '//' . $it);
                if (count($imageData) > 0) {
                    $i = $imageData[0];
                    $images[$it] = [
                        'src' => isset($i['attributes']['src']) ? $i['attributes']['src'] : ($main ? $main : $default)
                    ];

                    // width and height from attributes
                    if (isset($i['attributes']['width']) || isset($i['attributes']['height'])) {
                        $images[$it]['width'] = isset($i['attributes']['width']) ? $i['attributes']['width'] : '100%';
                        $images[$it]['height'] = isset($i['attributes']['height']) ? $i['attributes']['height'] : '100%';
                    }

                    // parse style attributes
                    $properties = self::_parseProperties($i['attributes']['style']);
                    $images[$it] = array_merge($images[$it], $properties);
                }
            }

            // responsive behaviour
            $output = '';
            foreach ($images as $device => $i) {
                if ($device == 'mobile') {
                    $output .= ' if(\Webiny\Htpl\DeviceDetection\DeviceDetection::isMobile()){' . "\n";
                } elseif ($device == 'tablet') {
                    $output .= ' if(\Webiny\Htpl\DeviceDetection\DeviceDetection::isTablet()){' . "\n";
                } else {
                    $output .= ' if(\Webiny\Htpl\DeviceDetection\DeviceDetection::isDesktop()){' . "\n";
                }

                $output .= ' echo \Webiny\Htpl\Functions\WImage::getImage("' . $i['src'] . '", "' . $i['width'] . '", "' . $i['height'] . '"';
                if (isset($i['resize'])) {
                    $output .= ', "' . $i['resize'] . '"';
                }

                if (isset($i['fill'])) {
                    $output .= ', "' . $i['fill'] . '"';
                }

                $output .= ');';
                $output .= "\n" . '}' . "\n";
            }
        }

        return [
            'openingTag' => '',
            'content'    => self::_outputFunction($output),
            'closingTag' => ''
        ];
    }

    public static function getImage($img, $width, $height, $resize = '', $fill = '')
    {

    }
}