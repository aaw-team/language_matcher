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

use DeviceDetector\Cache\CacheInterface as DeviceDetectorCacheInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * TYPO32DeviceDetectorCacheBridge
 */
class TYPO32DeviceDetectorCacheBridge implements DeviceDetectorCacheInterface
{
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

    public function contains(string $id): bool
    {
        return $this->typo3CacheFrontend->has($id);
    }

    public function fetch(string $id)
    {
        return $this->contains($id) ? $this->typo3CacheFrontend->get($id) : false;
    }

    public function save(string $id, $data, int $lifeTime = 0): bool
    {
        $this->typo3CacheFrontend->set($id, $data, [], $lifeTime);
        return true;
    }

    public function flushAll(): bool
    {
        $this->typo3CacheFrontend->flush();
        return true;
    }

    public function delete(string $id): bool
    {
        return $this->typo3CacheFrontend->remove($id);
    }
}
