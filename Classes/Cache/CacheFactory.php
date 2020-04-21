<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Cache;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheFactory
 */
class CacheFactory
{
    public const CACHE_IDENTIFIER = 'language-matcher';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager = null)
    {
        $this->cacheManager = $cacheManager ?? GeneralUtility::makeInstance(CacheManager::class);
    }

    /**
     * @return FrontendInterface
     */
    public function getCache(): FrontendInterface
    {
        return $this->cacheManager->getCache(self::CACHE_IDENTIFIER);
    }
}
