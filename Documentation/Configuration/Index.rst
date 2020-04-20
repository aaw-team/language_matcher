.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

.. _configuration-site:

Site Configuration
==================

The extension behaviour can be controlled per site. Which is why the
configuration resides in the TYPO3 Site Configuration (see
:ref:`t3coreapi:sitehandling`). The available options can be set in the site's
:code:`config.yml` file, or in the "Sites" module.

.. figure:: ../Images/SiteConfiguration.png
   :class: with-shadow
   :alt: Extension of the built-in Site Configuration

   Extension of the built-in Site Configuration


enableLanguageMatching
----------------------

:aspect:`Title`
    Enable language matching

:aspect:`Datatype`
    bool

:aspect:`Default`
    :code:`false`

:aspect:`Description`
    En- or disables the language matching.


failQuietOnSystemErrors
-----------------------

:aspect:`Title`
    Fail quiet on system errors

:aspect:`Datatype`
    bool

:aspect:`Default`
    :code:`false`

:aspect:`Description`
    Setting this option to :code:`true` will suppress :php:`\RuntimeException`
    being thrown when the system is not in the expected state. The errors will
    be logged anyway.

    .. note::

        Be sure to closely monitor your logs when this option is turned on.
        Otherwise, you might miss important information.


enableLanguageRedirection
-------------------------

:aspect:`Title`
    Enable language redirection

:aspect:`Datatype`
    bool

:aspect:`Default`
    :code:`false`

:aspect:`Description`
    En- or disables the language redirection. This is only effective, when
    :code:`enableLanguageMatching` is enabled.


languageRedirectionStatus
-------------------------

:aspect:`Title`
    HTTP status code of a language redirection

:aspect:`Datatype`
    int

:aspect:`Default`
    :code:`303`

:aspect:`Description`
    The HTTP status code, which will be sent with the language redirect. The
    default value :code:`303` is suited for
    `HTTP/1.1 <https://tools.ietf.org/html/rfc7231#section-6.4>`_ communication.
    If clients that only know about
    `HTTP/1.0 <https://tools.ietf.org/html/rfc1945#section-9.3>`_ should be
    supported, :code:`302` can be used.

    This is only effective, when :code:`enableLanguageRedirection` is enabled.


.. _configuration-extension:

Global Extension Configuration
==============================

The extension configuration is found in the "Settings" module under "Extension
Configuration". 

basic.logLevel
--------------

:aspect:`Title`
    PSR-3 Log Level

:aspect:`Datatype`
    string

:aspect:`Default`
    :code:`empty`

:aspect:`Description`
    The `PSR-3 <https://www.php-fig.org/psr/psr-3/>`_ log level for the separate
    logfile (:code:`$TYPO3_LOG_DIR/typo3_lm_xxxxxxxxxx.log`). If disabled (set
    :code:`empty`), no separate logfile will be written, global log
    configuration applies.

    .. note::
    
        This makes only sense when precise log information is needed. For
        example for debugging or alike.
