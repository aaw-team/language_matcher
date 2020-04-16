.. include:: ../Includes.txt


.. _introduction:

============
Introduction
============

.. _what-it-does:

What does it do?
================

This extension implements a mechanism that matches the :code:`Accept-Language`
HTTP header in a request with available site languages and then subsequently
redirects to the page in the chosen language.

Once, a client is viewing the page in his matched language (or no matching
language could be found), a cookie will be set. As long as the cookie is sent by
the client, no further matching or redirecting will be done.

While this extension is pretty zero-configuration, it offers some basic switches
to fiddle with (see :ref:`configuration`).


.. _screenshots:

Screenshots
===========

.. figure:: ../Images/SiteConfiguration.png
   :class: with-shadow
   :alt: Extension of the built-in Site Configuration

   Extension of the built-in Site Configuration
