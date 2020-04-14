<?php
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

defined('TYPO3_MODE') or die();

$bootstrap = function () {
    // Register cache
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\AawTeam\LanguageMatcher\Cache\TYPO32DeviceDetectorCacheBridge::CACHE_IDENTIFIER] = [
        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'options' => [
            'defaultLifetime' => 0,
        ],
        'groups' => ['system'],
    ];

    // Load extension configuration
    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get('language_matcher');

    // Register logger
    if (is_array($extConf) && array_key_exists('logLevel', $extConf)) {
        $logLevel = $extConf['logLevel'];

        // TYPO3 is not PSR-3 compliant before v10, see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-88799-IntroducedPSR-3CompatibleLoggingAPI.html
        // @todo remove this code when dropping support for TYPO3 < v10
        $psr3Levels2Typo3LogLevels = [
            \Psr\Log\LogLevel::EMERGENCY => \TYPO3\CMS\Core\Log\LogLevel::EMERGENCY,
            \Psr\Log\LogLevel::ALERT => \TYPO3\CMS\Core\Log\LogLevel::ALERT,
            \Psr\Log\LogLevel::CRITICAL => \TYPO3\CMS\Core\Log\LogLevel::CRITICAL,
            \Psr\Log\LogLevel::ERROR => \TYPO3\CMS\Core\Log\LogLevel::ERROR,
            \Psr\Log\LogLevel::WARNING => \TYPO3\CMS\Core\Log\LogLevel::WARNING,
            \Psr\Log\LogLevel::NOTICE => \TYPO3\CMS\Core\Log\LogLevel::NOTICE,
            \Psr\Log\LogLevel::INFO => \TYPO3\CMS\Core\Log\LogLevel::INFO,
            \Psr\Log\LogLevel::DEBUG => \TYPO3\CMS\Core\Log\LogLevel::DEBUG,
        ];
        if (array_key_exists($logLevel, $psr3Levels2Typo3LogLevels)) {
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['AawTeam']['LanguageMatcher']['writerConfiguration'] = [
                $psr3Levels2Typo3LogLevels[$logLevel] => [
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        'logFileInfix' => 'lm',
                    ],
                ],
            ];
        }
    }

};
$bootstrap();
unset($bootstrap);
