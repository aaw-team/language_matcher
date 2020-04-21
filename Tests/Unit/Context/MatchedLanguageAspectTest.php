<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Tests\Unit\Context\Context;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use AawTeam\LanguageMatcher\Context\MatchedLanguageAspect;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * MatchedLanguageAspect
 */
class MatchedLanguageAspectTest extends UnitTestCase
{
    /**
     * @test
     */
    public function defaultObjectValuesTest()
    {
        $aspect = new MatchedLanguageAspect();
        $this->assertFalse($aspect->get('exists'));
        $this->assertFalse($aspect->get('equalsCurrentLanguage'));
        $this->assertFalse($aspect->get('canBeRequested'));
    }

    /**
     * @test
     */
    public function allPropertiesWorkCorrectlyTest()
    {
        $languageId = 1;
        $hreflang = 'de-CH';
        $twoLetterIsoCode = 'de';
        $title = 'Deutsch (Schweiz)';
        $navigationTitle = 'DE';

        $equalsCurrentLanguage = true;
        $canBeRequested = true;

        $matchedLanguageMock = $this->createMock(SiteLanguage::class);
        $matchedLanguageMock->expects(self::once())->method('getLanguageid')->willReturn($languageId);
        $matchedLanguageMock->expects(self::once())->method('getHreflang')->willReturn($hreflang);
        $matchedLanguageMock->expects(self::once())->method('getTwoLetterIsoCode')->willReturn($twoLetterIsoCode);
        $matchedLanguageMock->expects(self::once())->method('getTitle')->willReturn($title);
        $matchedLanguageMock->expects(self::once())->method('getNavigationTitle')->willReturn($navigationTitle);

        $aspect = new MatchedLanguageAspect($matchedLanguageMock, $equalsCurrentLanguage, $canBeRequested);

        $this->assertTrue($aspect->get('exists'));
        $this->assertEquals($equalsCurrentLanguage, $aspect->get('equalsCurrentLanguage'));
        $this->assertEquals($canBeRequested, $aspect->get('canBeRequested'));
        $this->assertEquals($languageId, $aspect->get('id'));
        $this->assertEquals($hreflang, $aspect->get('hreflang'));
        $this->assertEquals($twoLetterIsoCode, $aspect->get('twoLetterIsoCode'));
        $this->assertEquals($title, $aspect->get('title'));
        $this->assertEquals($navigationTitle, $aspect->get('navigationTitle'));
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
     * @expectedExceptionCode 1587121860
     */
    public function accessingExistingPropertyWithoutSiteLanguageThrowsAspectPropertyNotFoundExceptionTest()
    {
        $aspect = new MatchedLanguageAspect();
        $aspect->get('id');
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
     * @expectedExceptionCode 1587118976
     */
    public function accessingInvalidPropertyThrowsAspectPropertyNotFoundExceptionTest()
    {
        $aspect = new MatchedLanguageAspect($this->createMock(SiteLanguage::class));
        $aspect->get('some-inexistent-property');
    }
}
