=== Plugin Framework ===
Contributors: MemberBuddy
Tags: Wordpress
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable Tag: 0.0.2
Defines an extensible framework for use by other plugins.

== Description ==
*** This Plugin Is In Alpha Development, Not For Production Use Yet **
Defines an extensible framework for use by other plugins, by keeping the generic code in one place allows for easier maintenance across a large set of complex plugins.

== Installation ==
1. Verify that you have PHP5, which is required for this plugin.
2. Upload the whole `framework-api` directory to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Another plugin is asking me to install this one too, what's happening here? =

Don't worry, everything is all good. The 'Plugin Framework API' keeps all the generic code for defining a plugin and creating options pages in once place where it is far simpler to maintain. This means that the plugins that use this framework then only have to worry about the logic required to provide specific features to that plugin.

= I have the framework plugin installed,but when I updated another plugin an error message now appears? =

To avoid this happening again, always upgrade the Plugin Framework API before updating any plugins that use it. This means that the framework will always provide the latest features to any new plugin that needs it.

If you need to you can upload the new version of the framework via FTP, if you have upgraded out of order.

== Upgrade Notice ==
= 0.0.1 =
Remember to always upgrade the framework-api plugin before any other plugins that use it.

== Screenshots ==

== Changelog ==

= 0.0.1 =
* Alpha Version