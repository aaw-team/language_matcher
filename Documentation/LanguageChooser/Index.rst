.. include:: ../Includes.txt


.. _languagechooser:

================
Language Chooser
================

This is a frontend feature that can optionally be used. It is an implementation
for the :ref:`matchedlanguageaspect`, which can be used as reference for other
implementations.

Use case
========

Normally, you would automatically redirect a user to his favourite language, as
this is the main use case for this extension. Now imagine a site, where you
dont't want this to happen automatically, but as a user's choice. Say: when a
user visits the site, and the matched language is not the one that she's
presented with, a message will be displayed that says something like "Wouldn't
you rather see this page in English?". And lets her click a button that takes
her there.

Usage instructions
==================

.. note::
   This feature is implemented in a pretty basic way and does not offer much
   customisation options. If you feel like you need more or different options,
   feel free to get in touch via the
   `Bug Tracker <https://github.com/aaw-team/language_matcher/issues>`_.

1. Configure Site
-----------------

In your :ref:`Site Configuration <configuration-site>` activate the language
matching, but disable the automatic redirection:

.. code-block:: yaml

    enableLanguageMatching: true
    enableLanguageRedirection: false


2. Include static template
--------------------------

Include the static template "Language-Chooser" in your TypoScript template.


3. Define a parent HTML object
------------------------------

Specify the HTML element id, in which the HTML of the language-chooser should be
injected:

.. code-block:: typoscript

    page.jsFooterInline.6937593.settings.parentHtmlObjectId = my-element-id

Make sure, the HTML element with this id exists in the source of the page:

.. code-block:: html

    <body>
        <div id="my-element-id"></div>
        <!-- here goes your page -->
    </body>


4. Disable the default style (optional)
---------------------------------------

A default style is registered in the :typoscript:`PAGE` object (assuming
:typoscript:`page = PAGE`). If you want to write your own CSS, you can exclude
the default style with:

.. code-block:: typoscript

    page.includeCSS.language_chooser >


5. Override the template (optional)
-----------------------------------

You can render your own template in order to change the HTML content of the
language-chooser. Just create a fluid template file :code:`LanguageChooser.html`
and add the location to the :typoscript:`templateRootPaths` array:

.. code-block:: typoscript

    languagehint.10.30.templateRootPaths.10 = path/to/your/template/
