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

use DeviceDetector\Cache\Cache as DeviceDetectorCache;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TYPO32DeviceDetectorCacheBridge
 */
class TYPO32DeviceDetectorCacheBridge implements DeviceDetectorCache
{
    public const CACHE_IDENTIFIER = 'language-matcher';

    /**
     * @var FrontendInterface
     */
    protected $typo3CacheFrontend;

    /**
     * @param FrontendInterface $typo3CacheFrontend
     */
    public function __construct(FrontendInterface $typo3CacheFrontend)
    {
        $this->typo3CacheFrontend = $typo3CacheFrontend;
    }

    public static function factory(): self
    {
        return new self(GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_IDENTIFIER));
    }

    public function contains($id)
    {
        return $this->typo3CacheFrontend->has($id);
    }

    public function fetch($id)
    {
        return $this->contains($id) ? $this->typo3CacheFrontend->get($id) : false;
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $this->typo3CacheFrontend->set($id, $data, [], $lifeTime);
        return true;
    }

    public function flushAll()
    {
        $this->typo3CacheFrontend->flush();
        return true;
    }

    public function delete($id)
    {
        return $this->typo3CacheFrontend->remove($id);
    }
}
