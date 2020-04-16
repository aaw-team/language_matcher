.. include:: ../Includes.txt



.. _installation:

============
Installation
============

System Requirements
===================

+------------------+------------------+------------------+
| Language Matcher | PHP              | TYPO3            | 
+==================+==================+==================+
| 1.0              | 7.2              | 9.5 LTS, 10.3.x  |
+------------------+------------------+------------------+

Extension Installation
======================

Install the extension :ref:`the regular way in TYPO3
<t3coreapi:extension-install>`.

For `composer <https://getcomposer.org/>`_ users:

.. code-block:: bash

    composer require aaw-team/language_matcher

.. attention::

   At the moment it is required to install the extension via
   `composer <https://getcomposer.org/>`_ to make sure the dependent software is
   present. In a later version (and if there is the need), support for
   non-composer based installation can be added.

Load the code from git
======================

.. code-block:: bash

    git clone https://github.com/aaw-team/language_matcher.git
