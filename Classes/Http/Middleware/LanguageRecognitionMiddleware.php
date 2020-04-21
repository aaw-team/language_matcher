<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Http\Middleware;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use AawTeam\LanguageMatcher\Cache\CacheFactory;
use AawTeam\LanguageMatcher\Cache\TYPO32DeviceDetectorCacheBridge;
use AawTeam\LanguageMatcher\Context\MatchedLanguageAspect;
use AawTeam\LanguageMatcher\Service\LanguageMatcherService;
use AawTeam\LanguageMatcher\Utility\DependencyLoaderUtility;
use DeviceDetector\Parser\Bot as BotParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LanguageRecognitionMiddleware
 */
class LanguageRecognitionMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const REDIRECT_COOKIE_NAME = 'language-matcher-redirect';

    /**
     * @var LanguageMatcherService
     */
    protected $languageMatcherService;

    /**
     * @var CacheFactory
     */
    protected $cacheFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @param CacheFactory $cacheFactory
     * @param Context $context
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    public function __construct(LanguageMatcherService $languageMatcherService = null, CacheFactory $cacheFactory = null, Context $context = null, TypoScriptFrontendController $typoScriptFrontendController = null)
    {
        $this->languageMatcherService = $languageMatcherService ?? GeneralUtility::makeInstance(LanguageMatcherService::class);
        $this->cacheFactory = $cacheFactory ?? GeneralUtility::makeInstance(CacheFactory::class);
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->typoScriptFrontendController = $typoScriptFrontendController ?? $GLOBALS['TSFE'];
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->canProcessLanguageMatching($request)) {
            // Early return when the system is not in the expected state. Make
            // sure the aspect exists to prevent errors.
            $this->context->setAspect('matchedLanguage', new MatchedLanguageAspect());
            return $handler->handle($request);
        }

        $matchingLanguage = null;
        if ($this->shouldProcessLanguageMatching($request)) {

            $matchingLanguage = $this->languageMatcherService->getMatchingLanguage(
                $request->getAttribute('site'),
                $this->languageMatcherService->getAcceptableLanguageCandidates(
                    $request->getHeaderLine('accept-language')
                )
            );

            if ($matchingLanguage !== null && $this->shouldRedirectToLanguage($request, $matchingLanguage)) {
                $response = $this->getLanguageRedirectionResponse($request, $matchingLanguage);
                $this->logger->info('Sending language redirect response', [
                    'accept-language-header' => $request->getHeaderLine('accept-language'),
                    'target-language-id' => $matchingLanguage->getLanguageId(),
                    'request-uri' => (string)$request->getUri(),
                    'redirect-location-uri' => $response->getHeaderLine('Location')
                ]);
                return $response;
            }
        }

        // Create the aspect
        if ($matchingLanguage !== null) {
            $aspect = new MatchedLanguageAspect(
                $matchingLanguage,
                $this->equalsCurrentSiteLanguage($request, $matchingLanguage),
                $this->canPageBeRequestedInLanguage($request, $matchingLanguage)
            );
        } else {
            $aspect = new MatchedLanguageAspect();
        }
        $this->context->setAspect('matchedLanguage', $aspect);

        // Generate the response
        $response = $handler->handle($request);

        // When redirection is enabled, set a cookie to prevent redirection loops
        if ($this->getSiteConfiguration($request)['enableLanguageRedirection'] && !$request->getCookieParams()[self::REDIRECT_COOKIE_NAME]) {
            $response = $this->addRedirectCookieToResponse($response);
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @throws \RuntimeException
     * @return bool
     */
    protected function canProcessLanguageMatching(ServerRequestInterface $request): bool
    {
        try {
            $siteObjectIsOk = false;
            $site = $request->getAttribute('site');
            if (!$site instanceof Site) {
                $type = gettype($site) === 'object' ? get_class($site) : gettype($site);
                throw new \RuntimeException('Request attribute "site" is expected to be ' . Site::class . '. Got ' . $type . ' instead.');
            }
            $siteObjectIsOk = true;
            $pageArguments = $request->getAttribute('routing');
            if (!$pageArguments instanceof PageArguments) {
                $type = gettype($pageArguments) === 'object' ? get_class($pageArguments) : gettype($pageArguments);
                throw new \RuntimeException('Request attribute "routing" is expected to be ' . PageArguments::class . '. Got ' . $type . ' instead.');
            }
            $currentSiteLanguage = $request->getAttribute('language');
            if (!$currentSiteLanguage instanceof SiteLanguage) {
                $type = gettype($currentSiteLanguage) === 'object' ? get_class($currentSiteLanguage) : gettype($currentSiteLanguage);
                throw new \RuntimeException('Request attribute "language" is expected to be ' . SiteLanguage::class . '. Got ' . $type . ' instead.');
            }
        } catch(\RuntimeException $e) {
            $this->logger->critical($e->getMessage(), [
                'site-identifier' => $siteObjectIsOk ? $site->getIdentifier() : null,
                'request-uri' => (string)$request->getUri(),
            ]);
            if ($siteObjectIsOk && $site->getConfiguration()['failQuietOnSystemErrors']) {
                return false;
            }
            throw $e;
        }

        return true;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function shouldProcessLanguageMatching(ServerRequestInterface $request): bool
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');
        if (!$this->getSiteConfiguration($request)['enableLanguageMatching']) {
            $this->logger->debug('Language matching is not enabled for this site', [
                'site-identifier' => $site->getIdentifier(),
            ]);
            return false;
        }
        if (count($site->getLanguages()) <= 1) {
            $this->logger->warning('This site does not have more than one language', [
                'site-identifier' => $site->getIdentifier(),
            ]);
            return false;
        }

        // Check the request data
        if (!$request->hasHeader('accept-language')) {
            $this->logger->info('Skip language matching: no accept-language header');
            return false;
        } elseif ($this->isBotRequest($request)) {
            $this->logger->info('Skip language matching: found bot', [
                'user-agent' => $request->getHeaderLine('user-agent'),
            ]);
            return false;
        }

        return true;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function addRedirectCookieToResponse(ResponseInterface $response): ResponseInterface
    {
        $cookie = self::REDIRECT_COOKIE_NAME . '=1';
        $cookie .= '; Path=/';
        $cookie .= '; HttpOnly';
        $cookie .= '; SameSite=Lax';
        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    /**
     * @param ServerRequestInterface $request
     * @param SiteLanguage $language
     * @return ResponseInterface
     */
    protected function getLanguageRedirectionResponse(ServerRequestInterface $request, SiteLanguage $language): ResponseInterface
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');
        /** @var PageArguments $pageArguments */
        $pageArguments = $request->getAttribute('routing');

        // Create URI generation parameters
        $parameters = $pageArguments->getArguments();
        $parameters['_language'] = $language;

        // Assemble the URI for the redirection
        $uri = $site->getRouter()->generateUri(
            $pageArguments->getPageId(),
            $parameters
        );

        // HTTP status
        $status = (int)$this->getSiteConfiguration($request)['languageRedirectionStatus'];
        if (!in_array($status, [302, 303])) {
            $status = 303;
        }

        return GeneralUtility::makeInstance(RedirectResponse::class, (string)$uri)
            ->withStatus($status)
            ->withHeader('Location', (string)$uri)
            ->withAddedHeader('Vary', 'user-agent,accept-language');
    }

    /**
     * @param ServerRequestInterface $request
     * @param SiteLanguage $targetLanguage
     * @return bool
     */
    protected function shouldRedirectToLanguage(ServerRequestInterface $request, SiteLanguage $targetLanguage): bool
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');
        if (!$this->getSiteConfiguration($request)['enableLanguageRedirection']) {
            $this->logger->debug('Should redirect: NO (language redirection is not enabled for this site)', [
                'site-identifier' => $site->getIdentifier(),
            ]);
            return false;
        }

        if ($request->getCookieParams()[self::REDIRECT_COOKIE_NAME]) {
            $this->logger->info('Should redirect: NO (cookie is set)');
            return false;
        }

        if ($this->equalsCurrentSiteLanguage($request, $targetLanguage)) {
            $this->logger->debug('Should redirect: NO (target language equals current language)');
            return false;
        }

        if (!$this->canPageBeRequestedInLanguage($request, $targetLanguage)) {
            $this->logger->debug('Should redirect: NO (page cannot be requested in target language)');
            return false;
        }

        $this->logger->debug('Should redirect: YES (page can be requested in target language)');
        return true;
    }

    /**
     * Check if the page can be requested (would be accessible) in
     * $targetLanguage.
     *
     * @param ServerRequestInterface $request
     * @param SiteLanguage $targetLanguage
     * @return bool
     */
    protected function canPageBeRequestedInLanguage(ServerRequestInterface $request, SiteLanguage $targetLanguage): bool
    {
        if ($this->equalsCurrentSiteLanguage($request, $targetLanguage)) {
            $this->logger->debug('Page is available in target language (target language equals current language)');
            return true;
        }

        /** @var Site $site */
        $site = $request->getAttribute('site');
        if ($site->getDefaultLanguage() === $targetLanguage) {
            $this->logger->debug('Page is available in target language (target language is the default language)');
            return true;
        }

        // Check if the page would be accessible in $language
        $page = $this->typoScriptFrontendController->page;
        $targetLanguageOverlay = $this->typoScriptFrontendController->sys_page->getPageOverlay($page, $targetLanguage->getLanguageId());

        if ($targetLanguageOverlay['_PAGES_OVERLAY']) {
            // page is available in $language (a translation exists)
            $this->logger->debug('Page is available in target language', [
                'target-language-id' => $targetLanguage->getLanguageId(),
            ]);
            return true;
        } elseif (GeneralUtility::hideIfNotTranslated($page['l18n_cfg']) !== true && $targetLanguage->getFallbackType() !== 'strict') {
            // page can be requested in $language although it is not translated
            $this->logger->debug('Page is not translated to target language but is available anyway', [
                'target-language-id' => $targetLanguage->getLanguageId(),
            ]);
            return true;
        }
        $this->logger->debug('Page is not available in target language');
        return false;

    }

    /**
     * @param ServerRequestInterface $request
     * @param SiteLanguage $targetLanguage
     * @return bool
     */
    protected function equalsCurrentSiteLanguage(ServerRequestInterface $request, SiteLanguage $targetLanguage): bool
    {
        /** @var SiteLanguage $currentSiteLanguage */
        $currentSiteLanguage = $request->getAttribute('language');
        return $currentSiteLanguage === $targetLanguage;
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getSiteConfiguration(ServerRequestInterface $request): array
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');
        return $site->getConfiguration();
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isBotRequest(ServerRequestInterface $request): bool
    {
        $userAgent = $request->getHeaderLine('user-agent');
        if ($userAgent === '') {
            return false;
        }

        $userAgentCacheIdentifier = 'ib-' . md5($userAgent);
        $cache = $this->cacheFactory->getCache();

        if ($cache->has($userAgentCacheIdentifier)) {
            $isBot = $cache->get($userAgentCacheIdentifier);
        } else {
            DependencyLoaderUtility::loadDeviceDetector();
            $botParser = new BotParser();
            $botParser->setUserAgent($userAgent);
            $botParser->discardDetails();
            $botParser->setCache(GeneralUtility::makeInstance(TYPO32DeviceDetectorCacheBridge::class, $cache));
            $result = $botParser->parse();
            $isBot = $result !== null;
            $cache->set($userAgentCacheIdentifier, $isBot);
        }

        return $isBot;
    }
}
