<?php

namespace Webiny\Htpl\DeviceDetection;


use Webiny\Htpl\HtplException;

class DeviceDetection
{
    private static $devices;

    const DEVICE_PHONE = 'phone';
    const DEVICE_TABLET = 'tablet';

    public static function isPhone()
    {
        $dd = new self();
        return $dd->matchDevice(self::DEVICE_PHONE);
    }

    static public function isTablet()
    {
        $dd = new self();
        return $dd->matchDevice(self::DEVICE_TABLET);
    }

    public function matchDevice($deviceTypeToMatch)
    {
        if ($deviceTypeToMatch != self::DEVICE_PHONE && $deviceTypeToMatch != self::DEVICE_TABLET) {
            throw new HtplException('Unknown device type. The device type can either be "' . self::DEVICE_PHONE . '" or "' . self::DEVICE_TABLET . '"'
            );
        }

        // get user agent
        if (!($ua = $this->_getUserAgent())) {
            return false; // if we can't get the user agent, the check if negative
        }

        // check the device type from session
        if (($deviceType = $this->_getDeviceFromSession())) {
            return $deviceType == $deviceTypeToMatch;
        }

        // load the device list and do the match based on the user agent
        $this->_loadDeviceList();
        $isMatched = false;
        foreach (self::$devices['uaMatch'][$deviceTypeToMatch.'s'] as $phone => $pattern) {
            if (preg_match('/' . preg_quote($pattern) . '/is', $ua)) {
                $isMatched = true;
                break;
            }
        }

        if (!$isMatched) {
            return false;
        }

        // save the match for later
        $this->_saveDeviceTypeToSession($deviceTypeToMatch);
    }

    private function _getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
    }

    private function _loadDeviceList()
    {
        if (is_null(self::$devices)) {
            self::$devices = include __DIR__ . '/MobileDetectDb/Db.php';
        }
    }

    private function _rebuildDatabase()
    {
        $list = json_decode(file_get_contents(__DIR__ . '/MobileDetectDb/Mobile_Detect.json'), true);
        $arr = var_export($list, true);
        file_put_contents(__DIR__ . '/MobileDetectDb/Db.php', '<?php return ' . $arr . ';');
    }

    private function _getDeviceFromSession()
    {
        $this->_startSession();

        if (!isset($_SESSION['htplDeviceType'])) {
            return false;
        }

        // check if the user agent is still the same
        $ua = md5($this->_getUserAgent());
        $deviceType = str_replace($ua . '-', '', $_SESSION['htplDeviceType']);
        if ($deviceType == self::DEVICE_PHONE || $deviceType == self::DEVICE_TABLET) {
            return $deviceType;
        }

        return false;
    }

    private function _saveDeviceTypeToSession($deviceType)
    {
        $this->_startSession();
        $ua = md5($this->_getUserAgent());
        $_SESSION['htplDeviceType'] = $ua . '-' . $deviceType;

    }

    private function _startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}