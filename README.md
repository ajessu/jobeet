Jobeet with Symfony2
====================

This is a derivative work of the original <a href="http://http://www.symfony-project.org/jobeet/1_4/Doctrine/en/">Jobeet tutorial</a> by Fabien Potencier and Jonathan Wage for the Symfony2 framework.

This is a project to follow along the Jobeet Tutorial on Symfony2.

It's an attempt to port the Jobeet Tutorial to Symfony2, and it's not considered
official in any way.

I'm not related to Sensio Labs, and this tutorial is not part of any official
documentation (at least, not yet :) ).

It's not finished, far from it. It's still a work in progress. It's based on
Symfony Standard Edition PR8, and I will be updating vendors as new releases come
out.

What's inside?
--------------

Symfony Standard Edition comes pre-configured with the following bundles:

 * FrameworkBundle
 * SensioFrameworkExtraBundle
 * DoctrineBundle
 * TwigBundle
 * SwiftmailerBundle
 * ZendBundle
 * AsseticBundle
 * WebProfilerBundle (in dev/test env)
 * SymfonyWebConfiguratorBundle (in dev/test env)
 * AcmeDemoBundle (in dev/test env)

Added by SymfonyTuts
--------------------
 * AcmeJobeetBundle to follow the tutorial on symfonytuts.com

Installation
------------

clone this repo, and place it somewhere your web server root directory.

 * `app/console assets:install web/`

Configuration
-------------

Check that everything is working fine by going to the `web/config.php` page in a
browser and follow the instructions.

The distribution is configured with the following defaults:

 * Twig is the only configured template engine;
 * Doctrine ORM/DBAL is configured;
 * Swiftmailer is configured;
 * Annotations for everything are enabled.

A default bundle, `AcmeDemoBundle`, shows you Symfony2 in action. After
playing with it, you can remove it by deleting the `src/Acme` directory and
removing the routing entry in `app/config/routing.yml`.

Configure the distribution by editing `app/config/parameters.ini` or by
accessing `web/config.php` in a browser.

A simple controller is configured at `/hello/{name}`. Access it via
`web/app_dev.php/demo/hello/Fabien`.

If you want to use the CLI, a console application is available at
`app/console`. Check first that your PHP is correctly configured for the CLI
by running `app/check.php`.

Enjoy!
