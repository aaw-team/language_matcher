<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Tests\Unit\Http\Middleware;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use AawTeam\LanguageMatcher\Cache\CacheFactory;
use AawTeam\LanguageMatcher\Http\Middleware\LanguageRecognitionMiddleware;
use AawTeam\LanguageMatcher\Service\LanguageMatcherService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Http\RequestHandler;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * LanguageRecognitionMiddleware
 */
class LanguageRecognitionMiddlewareTest extends UnitTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @param bool $withUserAgentTest
     * @param bool $returnValueForUserAgentTest
     * @param string $userAgent
     * @return MockObject
     */
    protected function getCacheFactoryMock(bool $withExpectations = true, string $userAgent = null, bool $userAgentIsBot = false): MockObject
    {
        $cacheFactory = $this->createMock(CacheFactory::class);
        $cache = $this->createMock(NullFrontend::class);

        if ($withExpectations) {
            if ($userAgent !== null) {
                $cacheId = 'ib-' . md5($userAgent);
                $cache->expects(self::once())->method('has')->with($cacheId)->willReturn(true);
                $cache->expects(self::once())->method('get')->with($cacheId)->willReturn($userAgentIsBot);
            } else {
                $cache->expects(self::once())->method('has')->willReturn(true);
                $cache->expects(self::once())->method('get')->willReturn($userAgentIsBot);
            }
            $cacheFactory->expects(self::once())->method('getCache')->willReturn($cache);
        } else {
            $cache->method('has')->willReturn(true);
            $cache->method('get')->willReturn($userAgentIsBot);
            $cacheFactory->method('getCache')->willReturn($cache);
        }

        return $cacheFactory;
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @dataProvider requestWithMissingAttributeThrowsExceptionDataProvider
     */
    public function requestWithMissingAttributeThrowsException(array $attributes)
    {
        $languageMatcherService = $this->createMock(LanguageMatcherService::class);
        $cacheFactory = $this->getCacheFactoryMock(false);
        $context = $this->createMock(Context::class);
        $tsfe = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $languageRecognitionMiddleware = new LanguageRecognitionMiddleware($languageMatcherService, $cacheFactory, $context, $tsfe);
        $languageRecognitionMiddleware->setLogger($this->createMock(Logger::class));

        $request = new ServerRequest();
        foreach ($attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $languageRecognitionMiddleware->process($request, $this->createMock(RequestHandler::class));
    }

    /**
     * @return array
     */
    public function requestWithMissingAttributeThrowsExceptionDataProvider(): array
    {
        return [
            'site-is-missing' => [[
                'routing' => $this->createMock(PageArguments::class),
                'language' => $this->createMock(SiteLanguage::class),
            ]],
            'routing-is-missing' => [[
                'site' => $this->createMock(Site::class),
                'language' => $this->createMock(SiteLanguage::class),
            ]],
            'language-is-missing' => [[
                'site' => $this->createMock(Site::class),
                'routing' => $this->createMock(PageArguments::class),
            ]],
            'site-is-the-wrong-type' => [[
                'site' => new \stdClass(),
                'routing' => $this->createMock(PageArguments::class),
                'language' => $this->createMock(SiteLanguage::class),
            ]],
            'routing-is-the-wrong-type' => [[
                'site' => $this->createMock(Site::class),
                'routing' => new \stdClass(),
                'language' => $this->createMock(SiteLanguage::class),
            ]],
            'language-is-the-wrong-type' => [[
                'site' => $this->createMock(Site::class),
                'routing' => $this->createMock(PageArguments::class),
                'language' => new \stdClass(),
            ]],
        ];
    }

    /**
     * @test
     */
    public function disabledConfiguration()
    {
        $languageMatcherService = $this->createMock(LanguageMatcherService::class);
        $cacheFactory = $this->getCacheFactoryMock(false);
        $context = $this->createMock(Context::class);
        $tsfe = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('debug')->with('Language matching is not enabled for this site');

        $languageRecognitionMiddleware = new LanguageRecognitionMiddleware($languageMatcherService, $cacheFactory, $context, $tsfe);
        $languageRecognitionMiddleware->setLogger($logger);

        $siteConfiguration = [
            'enableLanguageMatching' => false,
        ];
        $site = $this->createMock(Site::class);
        $site->expects(self::atLeastOnce())->method('getConfiguration')->willReturn($siteConfiguration);

        $request = new ServerRequest();
        $request = $request
            ->withAttribute('site', $site)
            ->withAttribute('routing', $this->createMock(PageArguments::class))
            ->withAttribute('language', $this->createMock(SiteLanguage::class));

        $languageRecognitionMiddleware->process($request, $this->createMock(RequestHandler::class));
    }

    /**
     * @test
     */
    public function tooFewSiteLanguages()
    {
        $languageMatcherService = $this->createMock(LanguageMatcherService::class);
        $cacheFactory = $this->getCacheFactoryMock(false);
        $context = $this->createMock(Context::class);
        $tsfe = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('debug')->with('This site does not have more than one language');

        $languageRecognitionMiddleware = new LanguageRecognitionMiddleware($languageMatcherService, $cacheFactory, $context, $tsfe);
        $languageRecognitionMiddleware->setLogger($logger);

        $siteConfiguration = [
            'enableLanguageMatching' => true,
        ];
        $site = $this->createMock(Site::class);
        $site->expects(self::atLeastOnce())->method('getConfiguration')->willReturn($siteConfiguration);
        $site->expects(self::once())->method('getLanguages')->willReturn([$this->createMock(SiteLanguage::class)]);

        $request = new ServerRequest();
        $request = $request
            ->withAttribute('site', $site)
            ->withAttribute('routing', $this->createMock(PageArguments::class))
            ->withAttribute('language', $this->createMock(SiteLanguage::class));

        $languageRecognitionMiddleware->process($request, $this->createMock(RequestHandler::class));
    }

    /**
     * @test
     */
    public function noAcceptLanguageHeader()
    {
        $languageMatcherService = $this->createMock(LanguageMatcherService::class);
        $cacheFactory = $this->getCacheFactoryMock(false);
        $context = $this->createMock(Context::class);
        $tsfe = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('info')->with('Skip language matching: no accept-language header');

        $languageRecognitionMiddleware = new LanguageRecognitionMiddleware($languageMatcherService, $cacheFactory, $context, $tsfe);
        $languageRecognitionMiddleware->setLogger($logger);

        $siteConfiguration = [
            'enableLanguageMatching' => true,
        ];
        $site = $this->createMock(Site::class);
        $site->expects(self::atLeastOnce())->method('getConfiguration')->willReturn($siteConfiguration);
        $site->expects(self::once())->method('getLanguages')->willReturn([[], []]);

        $request = new ServerRequest();
        $request = $request
            ->withAttribute('site', $site)
            ->withAttribute('routing', $this->createMock(PageArguments::class))
            ->withAttribute('language', $this->createMock(SiteLanguage::class));

        $languageRecognitionMiddleware->process($request, $this->createMock(RequestHandler::class));
    }

    /**
     * @test
     */
    public function skipForBots()
    {
        $userAgent = 'I am a Bot';
        $languageMatcherService = $this->createMock(LanguageMatcherService::class);
        $cacheFactory = $this->getCacheFactoryMock(true, $userAgent, true);
        $context = $this->createMock(Context::class);
        $tsfe = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->createMock(Logger::class);
        $logger->expects(self::once())->method('info')->with('Skip language matching: found bot');

        $languageRecognitionMiddleware = new LanguageRecognitionMiddleware($languageMatcherService, $cacheFactory, $context, $tsfe);
        $languageRecognitionMiddleware->setLogger($logger);

        $siteConfiguration = [
            'enableLanguageMatching' => true,
        ];
        $site = $this->createMock(Site::class);
        $site->expects(self::atLeastOnce())->method('getConfiguration')->willReturn($siteConfiguration);
        $site->expects(self::once())->method('getLanguages')->willReturn([[], []]);

        $headers = [
            'accept-language' => 'en',
            'user-agent' => $userAgent,
        ];
        $request = new ServerRequest(null, null, null, $headers);
        $request = $request
            ->withAttribute('site', $site)
            ->withAttribute('routing', $this->createMock(PageArguments::class))
            ->withAttribute('language', $this->createMock(SiteLanguage::class));

        $languageRecognitionMiddleware->process($request, $this->createMock(RequestHandler::class));
    }

    /**
     * @todo
     */
    public function doNotRedirectToSameLanguage()
    {}

    /**
     * @todo
     */
    public function doNotRedirectToUnavailablePage()
    {}

    /**
     * @todo
     */
    public function configureRedirectStatusCode()
    {}
}
