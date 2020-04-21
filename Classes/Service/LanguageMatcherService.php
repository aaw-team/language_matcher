<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Service;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * LanguageMatcherService
 */
class LanguageMatcherService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param Site $site
     * @param array $acceptableLanguageCandidates
     * @return SiteLanguage|null
     */
    public function getMatchingLanguage(Site $site, array $acceptableLanguageCandidates): ?SiteLanguage
    {
        // Early return when no candidates are provided
        if (empty($acceptableLanguageCandidates)) {
            return null;
        }

        $availableLanguages = $site->getLanguages();
        if (empty($availableLanguages)) {
            return null;
        }

        $matchingLanguage = null;
        foreach ($acceptableLanguageCandidates as $acceptableLanguageCandidate => $candidateQuality) {
            foreach ($availableLanguages as $availableLanguage) {
                /** @var SiteLanguage $availableLanguage */
                if (strtolower($availableLanguage->getHreflang()) === $acceptableLanguageCandidate) {
                    $matchingLanguage = $availableLanguage;
                    $this->logger->debug('Found matching language by hreflang', [
                        'candidate' => $acceptableLanguageCandidate,
                        'language-id' => $matchingLanguage->getLanguageId(),
                    ]);
                    break 2;
                } elseif (strtolower($availableLanguage->getTwoLetterIsoCode()) === $acceptableLanguageCandidate) {
                    $matchingLanguage = $availableLanguage;
                    $this->logger->debug('Found matching language by twoLetterIsoCode', [
                        'candidate' => $acceptableLanguageCandidate,
                        'language-id' => $matchingLanguage->getLanguageId(),
                    ]);
                    break 2;
                }
            }
        }

        if (!$matchingLanguage) {
            $this->logger->debug('No matching language found', [
                'candidates' => $acceptableLanguageCandidates,
            ]);
        }
        return $matchingLanguage;
    }

    /**
     * @param string $acceptLanguageHeader
     * @return array
     * @see https://tools.ietf.org/html/rfc7231#section-5.3.1
     */
    public function getAcceptableLanguageCandidates(string $acceptLanguageHeader): array
    {
        if ($acceptLanguageHeader === '') {
            return [];
        }

        $allAcceptedLanguageCandidates = [];
        foreach (explode(',', $acceptLanguageHeader) as $candidateString) {

            $candidateString = strtolower(trim($candidateString));
            $matches = [];
            $candidateQualityValue = 1.0;
            $qualityValueRegex = '~;\\s*q=(0(?:\\.[0-9]{1,3})?|1(?:\\.0{1,3})?)$~i';
            if (preg_match($qualityValueRegex, $candidateString, $matches)) {
                $candidateQualityValue = (float)$matches[1];
                $candidateString = trim(preg_replace($qualityValueRegex, '', $candidateString));
            }

            // Ignore "not acceptable"
            if ($candidateQualityValue > 0) {
                if (isset($allAcceptedLanguageCandidates[$candidateString])) {
                    if ($candidateQualityValue > $allAcceptedLanguageCandidates[$candidateString]) {
                        $allAcceptedLanguageCandidates[$candidateString] = $candidateQualityValue;
                    }
                } else {
                    $allAcceptedLanguageCandidates[$candidateString] = $candidateQualityValue;
                }
            }
        }

        uasort($allAcceptedLanguageCandidates, function(float $a, float $b): int {
            return $b <=> $a;
        });

        return $allAcceptedLanguageCandidates;
    }
}
