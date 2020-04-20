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
    'enableLanguageMatching' => [
        'label' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.enableLanguageMatching',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => false,
        ],
    ],
    'enableLanguageRedirection' => [
        'label' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.enableLanguageRedirection',
        'displayCond' => 'FIELD:enableLanguageMatching:REQ:true',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => false,
        ],
    ],
    'languageRedirectionStatus' => [
        'label' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.languageRedirectionStatus',
        'description' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.languageRedirectionStatus.description',
        'displayCond' => 'FIELD:enableLanguageMatching:REQ:true',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'default' => 303,
            'items' => [
                [ '302 Found', 302 ],
                [ '303 See Other', 303 ],
            ],
        ],
    ],
    'failQuietOnSystemErrors' => [
        'label' => 'LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.failQuietOnSystemErrors',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => false,
        ],
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns'] = array_merge($GLOBALS['SiteConfiguration']['site']['columns'], $siteColumns);
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = preg_replace('~(,\\s*languages,\\s*)(--div--)~', '$1
    --palette--;LLL:EXT:language_matcher/Resources/Private/Language/backend.xlf:site.palette.languagematcher;languagematcher,
$2', $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']);

$GLOBALS['SiteConfiguration']['site']['palettes']['languagematcher'] = [
    'showitem' => '
        enableLanguageMatching, failQuietOnSystemErrors, --linebreak--,
        enableLanguageRedirection, languageRedirectionStatus,
    ',
];
