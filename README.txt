=== LittleBot ACH for Stripe + Plaid ===
Contributors: jwind
Donate link: https://www.littlebot.io/make-a-donation/
Tags: Stripe, plaid, ACH, e-commerce, ecommerce, commerce, bank, bank account
Requires at least: 3.0.1
Tested up to: 5.0.3
Stable tag: 1.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin that allows easy ACH bank transfer payments via Stripe + Plaid

== Description ==

WordPress plugin that allows easy ACH bank transfer payments via Stripe + Plaid

== Installation ==

1. Upload `wp-stripe-plaid.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the following shortcode where you want the form to render [wp_stripe_plaid]
== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.2.5 =
Fix: compatibility with Gutenberg

= 1.2.3 =
* NEW: Allow un authenticated users to pay
* FIX: Fix edge case where charges would through an error because customer object exists in test mode

= 1.2.2 =
* Make call to Plaid production environment instead of "live"

= 1.2.1 =
* Fix call to Plaid development environment

= 1.2.0 =
* NEW: Add support for Plaid development mode

= 1.1.5 =
* Update Stripe Bindings to v6.5.0

= 1.1.4 =
* Modified plaid API call

= 1.1.3 =
* bug fixes

= 1.1.2 =
* Form output buffering

= 1.1.1 =
* Improved logic in selecting bank source for charging returning customer
* NEW: ability to add amount param to autofill form ex http://mysite.com/plugin-page?amount=100.23

= 1.1.0 =
* Updated plaid environments to sandbox/production
* Payments now create a customers prior to charge allowing you to create subscriptions in the Stripe Admin or perform and customer action you'd like.

= 1.0.1 =
* updated plaid link to v2
* plugin can now test plaid in test mode

= 1.0 =
* Initial release