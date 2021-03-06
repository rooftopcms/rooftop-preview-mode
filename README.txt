=== Plugin Name ===
Contributors: rooftopcms
Tags: rooftop, api, headless, content
Requires at least: 4.3
Tested up to: 4.3
Stable tag: 4.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

rooftop-preview-mode allows API clients to specify whether they are fetching content in preview mode,
using a PREVIEW header, which will return content even if it is in draft state.

== Description ==

This plugin is loaded earlier than others and defines a global ROOFTOP_PREVIEW_MODE variable. Other plugins
or your custom endpoints can check this variable and alter the response or WP_Query as required.

Track progress, raise issues and contribute at http://github.com/rooftopcms/rooftop-preview-mode

== Installation ==

rooftop-preview-mode is a Composer plugin, so you can include it in your Composer.json.

Otherwise you can install manually:

1. Upload the `rooftop-preview-mode` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. There is no step 3 :-)

== Frequently Asked Questions ==

= Can this be used without Rooftop CMS? =

Yes, it's a Wordpress plugin you're welcome to use outside the context of Rooftop CMS. We haven't tested it, though.

== Changelog ==

= 0.0.1 =
* Initial release

== What's Rooftop CMS? ==

Rooftop CMS is a hosted, API-first WordPress CMS for developers and content creators. Use WordPress as your content management system, and build your website or application in the language best suited to the job.

https://www.rooftopcms.com