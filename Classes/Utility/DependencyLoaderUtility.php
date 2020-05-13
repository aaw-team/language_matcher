<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Utility;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Composer\Autoload\ClassLoader;
use DeviceDetector\DeviceDetector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DependencyLoaderUtility
 */
class DependencyLoaderUtility
{
    /**
     * @var ClassLoader
     */
    private static $deviceDetectorLoader;

    /**
     * Registers an autoloader that knows about the DeviceDetector library. This
     * will be most probably used in non-composer environments.
     */
    public static function loadDeviceDetector(): void
    {
        if (!class_exists(DeviceDetector::class)) {
            self::getDeviceDetectorLoader()->register();
        }
    }

    /**
     * @return ClassLoader
     */
    private static function getDeviceDetectorLoader(): ClassLoader
    {
        if (self::$deviceDetectorLoader === null) {
            self::$deviceDetectorLoader = require 'phar://' . GeneralUtility::getFileAbsFileName('EXT:language_matcher/Resources/Private/PHP/DeviceDetector/device-detector.phar/vendor/autoload.php');
        }
        return self::$deviceDetectorLoader;
    }
}
