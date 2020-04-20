# Language matcher for TYPO3

This extension implements a [PSR-15](https://www.php-fig.org/psr/psr-15/>)
middleware that matches the `Accept-Language` HTTP header from a request
with available site languages.

The results of the matching can then be used to redirect a client automatically
to it's favourite language. In this case, a cookie is used to prevent
redirection loops.

Redirection can be disabled though. In this case, the results of the matching
will be made available as Aspect through the TYPO3 Context API.

While this extension is pretty zero-configuration, it offers some basic switches
to fiddle with.

## Installation

### System requirements

| Language Matcher | PHP              | TYPO3            |
| ---------------- | ---------------- | -----------------|
| 1.0              | 7.2              | 9.5 LTS, 10.3.x  |

### Extension Installation

Install with [Composer](https://getcomposer.org/):

    composer require aaw-team/language_matcher

Or, if you don't use composer, download and install the extension in TYPO3
Extension Manager.

## Documentation

https://docs.typo3.org/p/aaw-team/language_matcher/master/en-us/

## License

GNU General Public License v3.0 or later

## Copyright

2020 by Agentur am Wasser | Maeder & Partner AG (https://www.agenturamwasser.ch)
