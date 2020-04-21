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

use AawTeam\LanguageMatcher\Service\LanguageMatcherService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * LanguageMatcherServiceTest
 */
class LanguageMatcherServiceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function acceptableCandidateIsLowerCase()
    {
        $languageMatcherService = new LanguageMatcherService();
        $this->assertSame(
            ['en-us' => 1.0],
            $languageMatcherService->getAcceptableLanguageCandidates('en-US')
        );
    }

    /**
     * @test
     * @dataProvider candidatesQualityDataProvider
     */
    public function candidatesQuality(string $acceptLanguageHeader, float $expectedQuality, string $expectedCandidate)
    {
        $languageMatcherService = new LanguageMatcherService();
        $this->assertSame(
            [$expectedCandidate => $expectedQuality],
            $languageMatcherService->getAcceptableLanguageCandidates($acceptLanguageHeader)
        );
    }

    /**
     * @return array
     */
    public function candidatesQualityDataProvider(): array
    {
        return [
            'no-quality-means-1.0' => [
                'en', 1.0, 'en'
            ],
            'invalid-quality-means-1.0' => [
                'en;q=0.1234', 1.0, 'en;q=0.1234'
            ],
            'too-low-quality-means-1.0' => [
                'en;q=-0.123', 1.0, 'en;q=-0.123'
            ],
            'too-high-quality-means-1.0' => [
                'en;q=2.999', 1.0, 'en;q=2.999'
            ],
            'correct-quality-1' => [
                'en;q=0.1', 0.1, 'en'
            ],
            'correct-quality-2' => [
                'en;q=0.12', 0.12, 'en'
            ],
            'correct-quality-3' => [
                'en;q=0.123', 0.123, 'en'
            ],
            'correct-quality-one-0' => [
                'en;q=1', 1.0, 'en'
            ],
            'correct-quality-one-1' => [
                'en;q=1.0', 1.0, 'en'
            ],
            'correct-quality-one-2' => [
                'en;q=1.00', 1.0, 'en'
            ],
            'correct-quality-one-3' => [
                'en;q=1.000', 1.0, 'en'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider removeCandidatesWithQualityZeroDataProvider
     */
    public function removeCandidatesWithQualityZero(string $acceptLanguageHeader)
    {
        $languageMatcherService = new LanguageMatcherService();
        $this->assertEmpty($languageMatcherService->getAcceptableLanguageCandidates($acceptLanguageHeader));
    }

    /**
     * @return array
     */
    public function removeCandidatesWithQualityZeroDataProvider(): array
    {
        return [
            'zero-0' => ['en;q=0'],
            'zero-1' => ['en;q=0.0'],
            'zero-2' => ['en;q=0.00'],
            'zero-3' => ['en;q=0.000'],
        ];
    }

    /**
     * @test
     * @dataProvider correctCandidateParsingAndSortingDataProvider
     */
    public function correctCandidateParsingAndSorting(string $acceptLanguageHeader, array $expectedResult)
    {
        $languageMatcherService = new LanguageMatcherService();
        $this->assertSame($expectedResult, $languageMatcherService->getAcceptableLanguageCandidates($acceptLanguageHeader));
    }

    /**
     * @return array
     */
    public function correctCandidateParsingAndSortingDataProvider(): array
    {
        return [
            'two-languages-without-quality' => [
                'en-US, en',
                [
                    'en-us' => 1.0,
                    'en' => 1.0,
                ]
            ],
            'two-languages-with-one-quality-1' => [
                'en-US, en;q=1',
                [
                    'en-us' => 1.0,
                    'en' => 1.0,
                ]
            ],
            'two-languages-with-one-quality-2' => [
                'en-US;q=1, en',
                [
                    'en-us' => 1.0,
                    'en' => 1.0,
                ]
            ],
            'two-languages-with-one-lower-quality-1' => [
                'en-US, en;q=0.8',
                [
                    'en-us' => 1.0,
                    'en' => .8,
                ]
            ],
            'two-languages-with-one-lower-quality-2' => [
                'en-US;q=0.8, en',
                [
                    'en' => 1.0,
                    'en-us' => .8,
                ]
            ],
            'two-languages-with-quality-1' => [
                'en-US;q=0.9, en;q=0.8',
                [
                    'en-us' => .9,
                    'en' => .8,
                ]
            ],
            'two-languages-with-quality-2' => [
                'en-US;q=0.8, en;q=0.9',
                [
                    'en' => .9,
                    'en-us' => .8,
                ]
            ],
            'two-languages-with-same-quality' => [
                'en-US;q=0.8, en;q=0.8',
                [
                    'en-us' => .8,
                    'en' => .8,
                ]
            ],
            'full-blown-1' => [
                'fr;q=0.01, en-US, en;q=0.8, de-CH, de;q=0.9, it;q=0',
                [
                    'en-us' => 1.0,
                    'de-ch' => 1.0,
                    'de' => .9,
                    'en' => .8,
                    'fr' => .01,
                ]
            ],
            'rfc-7231-example' => [
                'da, en-gb;q=0.8, en;q=0.7',
                [
                    'da' => 1.0,
                    'en-gb' => .8,
                    'en' => .7,
                ]
            ],
            'equal-languages-with-different-priorities' => [
                'en-US;q=0.8, en-US;q=1',
                [
                    'en-us' => 1.0,
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function languageMatchingTests()
    {
        $siteWithNoLanguages = $this->createMock(Site::class);
        $siteWithNoLanguages->method('getLanguages')->willReturn([]);

        $site = $this->createMock(Site::class);

        $siteLanguageEnglish = $this->createMock(SiteLanguage::class);
        $siteLanguageEnglish->method('getHreflang')->willReturn('en-US');
        $siteLanguageEnglish->method('getTwoLetterIsoCode')->willReturn('en');

        $siteLanguageGerman = $this->createMock(SiteLanguage::class);
        $siteLanguageGerman->method('getHreflang')->willReturn('de-CH');
        $siteLanguageGerman->method('getTwoLetterIsoCode')->willReturn('de');

        $site->method('getLanguages')->willReturn([
            $siteLanguageEnglish,
            $siteLanguageGerman,
        ]);

        $languageMatcherService = new LanguageMatcherService();
        $languageMatcherService->setLogger($this->createMock(Logger::class));
        $this->assertNull(
            $languageMatcherService->getMatchingLanguage($siteWithNoLanguages, []),
            'Site with no languages returns no match'
        );

        $this->assertNull(
            $languageMatcherService->getMatchingLanguage($site, []),
            'Empty candidates returns no match'
        );

        $this->assertNull(
            $languageMatcherService->getMatchingLanguage($site, ['it' => 1.0]),
            'Unknown language returns no match'
        );

        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['en-us' => 1.0]),
            'Match language by hreflang'
        );
        $this->assertSame(
            $siteLanguageGerman,
            $languageMatcherService->getMatchingLanguage($site, ['de' => 1.0]),
            'Match language by twoLetterIsoCode'
        );
        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['en-us' => 1.0, 'de-ch' => .8]),
            'Match language by hreflang over lower quality hreflang'
        );
        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['en' => 1.0, 'de' => .8]),
            'Match language by twoLetterIsoCode over lower quality twoLetterIsoCode'
        );
        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['en-us' => 1.0, 'de' => .8]),
            'Match language by hreflang over lower quality twoLetterIsoCode'
        );
        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['en' => 1.0, 'de-ch' => .8]),
            'Match language by twoLetterIsoCode over lower quality hreflang'
        );
        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['it-it' => 1.0, 'it' => .8, 'fr' => .6, 'en-us' => .1]),
            'Match least quality by hreflang when there are no other possibilities'
        );
        $this->assertSame(
            $siteLanguageEnglish,
            $languageMatcherService->getMatchingLanguage($site, ['it-it' => 1.0, 'it' => .8, 'fr' => .6, 'en' => .1]),
            'Match least quality by twoLetterIsoCode when there are no other possibilities'
        );
    }
}
