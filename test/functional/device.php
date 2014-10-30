<?php

require_once '/var/www/projects/composer/vendor/autoload.php';

\Webiny\Component\ClassLoader\ClassLoader::getInstance()->registerMap([
                                                                          'Webiny\Htpl\\' => '/var/www/projects/htpl/src/Webiny/Htpl'
                                                                      ]
);

\Webiny\Htpl\DeviceDetection\DeviceDetection::isPhone();