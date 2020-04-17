.. include:: ../Includes.txt


.. _matchedlanguageaspect:

======================
MatchedLanguage Aspect
======================

The results of the language matching in the middleware is stored in an Aspect,
using the :ref:`TYPO3 Context API <t3coreapi:context-api>`. It contains the
matched language as :php:`SiteLanguage` object (see
:ref:`t3coreapi:sitehandling-php-api`), or :php:`null` if there was no match.

The MatchedLanguage Aspect accepts two kinds of properties. The one kind
accesses the properties of the :php:`SiteLanguage` object. That's why a matched
language must be present (as long as the properties are accessed through the
official API [:php:`$context->getPropertyFromAspect()`], there are no errors to
expect if no :php:`SiteLanguage` object exists).

The other kind of accepted property is more descriptive for the result of the
language matching process.

=========================  =====================================================  ================
Property                   Description                                            Matched language
                                                                                  required
=========================  =====================================================  ================
``exists``                 Is :php:`true` when a matched language exists          No
``equalsCurrentLanguage``  Is :php:`true` when the matched language equals the    No
                           current SiteLanguage
``canBeRequested``         Is :php:`true` when the current page can be requested  No
                           in the matchedLanguage
``id``                     :php:`SiteLanguage::getLanguageId()`                   Yes
``hreflang``               :php:`SiteLanguage::getHreflang()`                     Yes
``twoLetterIsoCode``       :php:`SiteLanguage::getTwoLetterIsoCode()`             Yes
``title``                  :php:`SiteLanguage::getTitle()`                        Yes
``navigationTitle``        :php:`SiteLanguage::getNavigationTitle()`              Yes
=========================  =====================================================  ================


PHP Example
-----------

.. code-block:: php

    $context = GeneralUtility::makeInstance(Context::class);

    // Check if a link to another language version of the page should be presented
    $presentLinkToOtherLanguage = $context->getPropertyFromAspect('matchedLanguage', 'canBeRequested')
      && !$context->getPropertyFromAspect('matchedLanguage', 'equalsCurrentLanguage');

    if ($presentLinkToOtherLanguage) {
        // Retrieve the navigationTitle of the matched language
        $linkText = $context->getPropertyFromAspect('matchedLanguage', 'navigationTitle');
    }


.. note::

    When the extension is not enabled for a site, the aspect does not exist.
    Trying to access it would in that case lead to a
    :php:`TYPO3\CMS\Core\Context\Exception\AspectNotFoundException`.

