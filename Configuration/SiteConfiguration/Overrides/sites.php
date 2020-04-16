<?php
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

$siteColumns = [
    'enableLanguageRedirection' => [
        'label' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.enableLanguageRedirection',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ],
    ],
    'languageRedirectionStatus' => [
        'label' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.languageRedirectionStatus',
        'description' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.languageRedirectionStatus.description',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'default' => '303',
            'items' => [
                [ '302 Found', 302 ],
                [ '303 See Other', 303 ],
            ],
        ],
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns'] = array_merge($GLOBALS['SiteConfiguration']['site']['columns'], $siteColumns);
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = preg_replace('~(,\\s*languages,\\s*)(--div--)~', '$1
    --palette--;LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.palette.languagematcher;languagematcher,
$2', $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']);

$GLOBALS['SiteConfiguration']['site']['palettes']['languagematcher'] = [
    'showitem' => '
        enableLanguageRedirection, languageRedirectionStatus,
    ',
];
