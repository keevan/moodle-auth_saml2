100% Moodle SAML fast, simple, secure
=====================================

![Churchill quote](/pix/churchill.jpg?raw=true)

What is this?
-------------

This plugin does authentication and that's it.

What isn't it?
--------------

It does not do any user creation and assumes users are already provisioned
via some other means such as ldap sync or a custom enrolment plugin. In our
experience this is how almost all large uni's and corp's want it and it makes
the most sense architecturally because students need to be in the class lists
and groups well before they may ever log in, if ever.

Why is it better?
-----------------

* 100% configured in the Moodle GUI (no installation of a whole separate app)
* Minimal configuration needed, in most cases just copy the IdP metadata in
  and then give the SP metadata to your IdP admin and that's it.
* Fast! - 3 redirects instead of 7
* Supports back channel Single Logout which most big organisations require (unlike OneLogin)

How does it work?
-----------------

It completely embeds a SimpleSamlPhp instance as an internal dependancy which
is dynamically configured the way it should be and inherits almost all of it's
configuration from moodle configuration. In the future we should be able to
swap to a different internal SAML implementation and the plugin GUI shouldn't
need to change at all.

Features
--------

* Dual login VS forced login for all as an option, with ?saml=off on the login page, and ?saml=on supported everywhere
* user attribute to moodle mapping
* Automatic certificate creation

Features excluded on purpose:

* Auto create users - this should not be in an auth plugin
* Enrolment - this should be an enrol plugin and not in an auth plugin
* Profile field mapping - see above
* Role mapping - see above

That said, if there is desire for this then we're open to adding it in.

Other SAML plugins
------------------

The diversity and variable quality and features of SAML moodle plugins is a
reflection of a great need for a solid SAML plugin, but the neglect to do
it properly in core. SAML2 is by far the most robust and supported protocol
across the internet and should be fully integrated into moodle core as both
a Service Provider and as an Identity Provider, and without any external
dependencies to manage.

Here is a quick run down of the alternatives:

**Core:**

* /auth/shibboleth - This requires a separately installed and configured
  Shibbolleth install

One big issue with this, and the category below, is as there is a whole extra
application between moodle and the IdP, so the login and logout processes have
more latency due to extra redirects. Latency on potentially slow mobile
networks is by far the biggest bottle neck for login speed and the biggest
complaint by end users in our experience.

**Plugins that require SimpleSamlPHP**

These are all forks of each other, and unfortunately have diverged quite early
or have no common git history making it difficult to cross port features or
fixes between them.

* https://moodle.org/plugins/view/auth_saml

* https://moodle.org/plugins/view/auth_zilink_saml

* https://github.com/piersharding/moodle-auth_saml

**Plugins which embed a SAML client lib:**

These are generally much easier to manage and configure as they are standalone.

* https://moodle.org/plugins/view/auth_onelogin_saml - This one uses it's own
  embedded saml library which is great and promising, however it doesn't support
  'back channel logout' which is critical for security in any large organisation.

* This plugin, with an embedded and dynamically configured SimpleSamlPHP
  instance under the hood

Testing
-------

This plugin has been tested against:

* SimpleSamlPHP set up as an IdP
* openidp.feide.no
* testshib.org
* An AAF instance of Shibboleth

