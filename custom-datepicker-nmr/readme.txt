=== Custom Datepicker NMR ===
Contributors: mirceatm
Donate link: https://paypal.me/mirceatm
Tags: cf7, contact, form, contact form, date filed, datetime, format date, contact form 7, custom date, datepicker, jquery ui
Requires at least: 4.9
Tested up to: 6.4.2
Stable tag: 1.0.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Use date format like dd.mm.yy in date fields (jquery ui datepicker) for Contact Form 7. 

== Description ==

Contact Form 7 offers users standard HTML5 controls to input data, like text input and [input type="date"](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/date).
The browser will [not format the date text in a date format you might choose](https://stackoverflow.com/questions/7372038/is-there-any-way-to-change-input-type-date-format)
This plugin allows Contact Form 7 to use [jQuery UI Datepicker](https://jqueryui.com/datepicker/) where date format can be set or changed.
This plugin requires jquery , jquery ui and contact form 7.
Use `format`, `min` or `max` to configure it.
`
[datepicker* myFirstDatepicker id:myFirstDatepicker format:dd.mm.yy min:2022-06-06 max:2022-08-08] 
`

= Docs & Support =

Check the [support forum](https://wordpress.org/support/plugin/custom-datepicker-nmr/) on WordPress.org. If you can't locate any topics that pertain to your particular issue, post a new topic for it.

= Custom Datepicker NMR Needs Your Support =

It is hard to continue development and support for this free plugin without contributions from users like you. If you enjoy using Custom Datepicker NMR and find it useful, please consider [__making a donation__](https://paypal.me/mirceatm). Your donation will help encourage and support the plugin's continued development and better user support.

= Privacy Notices =

With the default configuration, this plugin, in itself, does not:

* track users by stealth;
* write any user personal data to the database;
* send any data to external servers;
* use cookies.

== Installation ==

1. Upload the entire  folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

Basic usage:
`[datepicker myFirstDatepicker id:myFirstDatepicker format:dd.mm.yy]`
or
`[datepicker* myFirstDatepicker id:myFirstDatepicker format:dd.mm.yy]` - for required field.


== Screenshots ==

1. screenshot-1.jpg

== Changelog ==

= 1.0.8 =

* Allow change of month and year

= 1.0.7 =

* Show `The field is required.`

= 1.0.6 =

* Fixed a bug on max date validation

= 1.0.5 =

* Fixed a bug on min / max date validation

= 1.0.4 =

* Add support for jQuery Datepicker `minDate`, `maxDate` settings by using `min` and `max` properties of `datepicker`

= 1.0.3 =

* Fixed validation

= 1.0.2 =

* Fixed validation for required datepicker

= 1.0.1 =

* Changed plugin meta-data: wordpress user

= 1.0.0 =

* First version.
