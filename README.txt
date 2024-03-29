
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Frequently Asked Questions (FAQ)
 * Known Issues
 * How Can You Contribute?


INTRODUCTION
------------

Maintainer: Narno <http://drupal.org/user/141690>
Maintainer: Dave Reid <http://drupal.org/user/53892>
Contributer: madler <http://drupal.org/user/123779>
Project Page: http://drupal.org/project/gravatar

This module integrates the Gravatar service with Drupal user pictures.


INSTALLATION
------------

See http://drupal.org/getting-started/install-contrib for instructions on
how to install or update Drupal modules.

User picture support (admin/config/people/settings) must be enabled for Gravatar
to work. Once Gravatar is installed and enabled, you can configure the module
at admin/config/people/gravatar.

You will also want to make sure user pictures are enabled for your theme at
admin/appearance/settings and the approprate user roles have the 'use gravatar'
permission assigned to them at admin/people/permissions.

Gravatar support is implemented as a field formatter for the user profile image, 
so you must select the Gravatar formatter for the user profile field at 
admin/config/people/accounts/display/. You must also ensure that the image style 
is set to "None (Original Image)".


FREQUENTLY ASKED QUESTIONS
--------------------------

Q: Is Gravatar support enabled by default for my users?
A: If their user role has the 'use gravatar' permission, yes Gravatar is enabled
   by default.


KNOWN ISSUES
------------

There are no known issues at this time.

To report new bug reports, feature requests, and support requests, visit
http://drupal.org/project/issues/gravatar.


HOW CAN YOU CONTRIBUTE?
---------------------

- Write a review for this module at drupalmodules.com.
  http://drupalmodules.com/module/gravatar

- Help translate this module on launchpad.net.
  http://localize.drupal.org/translate/projects/gravatar

- Report any bugs, feature requests, etc. in the issue tracker.
  http://drupal.org/project/issues/gravatar
