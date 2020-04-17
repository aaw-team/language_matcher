<?php
declare(strict_types=1);
namespace AawTeam\LanguageMatcher\Context;
/*
 * Copyright by Agentur am Wasser | Maeder & Partner AG
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * MatchedLanguageAspect
 */
class MatchedLanguageAspect implements AspectInterface
{
    /**
     * @var SiteLanguage
     */
    protected $matchedLanguage;

    /**
     * @var bool
     */
    protected $canBeRequested = false;

    /**
     * @var bool
     */
    protected $equalsCurrentLanguage = false;

    /**
     * @param SiteLanguage $matchedLanguage
     * @param bool $equalsCurrentLanguage
     * @param bool $canBeRequested
     */
    public function __construct(SiteLanguage $matchedLanguage = null, bool $equalsCurrentLanguage = false, bool $canBeRequested = false)
    {
        $this->matchedLanguage = $matchedLanguage;
        $this->equalsCurrentLanguage = $equalsCurrentLanguage;
        $this->canBeRequested = $canBeRequested;
    }

    /**
     * {@inheritDoc}
     * @see \TYPO3\CMS\Core\Context\AspectInterface::get()
     */
    public function get(string $name)
    {
        switch ($name) {
            case 'exists':
                return $this->matchedLanguage !== null;
            case 'equalsCurrentLanguage':
                return $this->equalsCurrentLanguage;
            case 'canBeRequested':
                return $this->canBeRequested;
        }

        // For the other properties, $this->matchedLanguage must not be null
        if ($this->matchedLanguage === null) {
            throw new AspectPropertyNotFoundException('There was no matching language to read properties from', 1587121860);
        }

        switch ($name) {
            case 'id':
                return $this->matchedLanguage->getLanguageId();
            case 'hreflang':
                return $this->matchedLanguage->getHreflang();
            case 'twoLetterIsoCode':
                return $this->matchedLanguage->getTwoLetterIsoCode();
            case 'title':
                return $this->matchedLanguage->getTitle();
            case 'navigationTitle':
                return $this->matchedLanguage->getNavigationTitle();
        }

        throw new AspectPropertyNotFoundException('Property "' . $name . '" not found in Aspect "' . __CLASS__ . '".', 1587118976);
    }
}
