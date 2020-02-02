﻿﻿=== WooCommerce Przelewy24 Payment Gateway ===
Contributors: przelewy24
Donate link: http://www.przelewy24.pl
Tags: woocommerce, Przelewy24, payment, payment gateway, platnosci
Requires at least: 3.5.1
Tested up to: 4.9
Stable tag: 1.0.8
License: GPLv3

== Description ==

Przelewy24 Payment Gateway supports:

* Polish online transfers and installments

= Features =

The Przelewy24 Payment Gateway for WooCommerce adds the Przelewy24 payment option and enables you to process the following operations in your shop:

* Creating a payment order
* Updating order status (canceling/completing an order will simultaneously update payment's status)

= Usage =

Przelewy24 Payment Gateway is visible for your customers as a single "Buy and Pay" button during checkout. After clicking the button customer is redirected to the Payment Summary page to choose payment method. After successful payment customer is redirected back to your shop.

== Installation ==

If you do not already have Przelewy24 merchant account [please register](https://www.przelewy24.pl/rejestracja).

In the Wordpress administration panel:

1. Go to **WooCommerce** -> **Settings section**
1. Choose **Checkout** tab and scroll down to the **"Payment Gateways"** section
1. Choose **Settings** option next to the **Przelewy24** name
1. Enable and configure the plugin


== Changelog ==

##[1.0.8]  - 2019-10-07
- fix bug in admin panel - the editing of menus was broken

##[1.0.7]  - 2019-09-03
- add payment method 218 and option to accept p24 terms in shop

##[1.0.6]  - 2019-03-13
- add multi currency

##[1.0.5]  - 2018-08-21
- fixed payment 3ds

##[1.0.4]  - 2018-06-21
- Display error messages if soap, curl extensions are missing or WooCommerce is not installed and active

##[1.0.3]  - 2018-02-14
- add oneclick

##[1.0.2]  - 2018-02-14

- add wait for result
- add e-mail to finish payment from admin panel
- add WooCommerce version compatibility check.

##[1.0.1]  - 2018-02-09

- add payment methods inside shop


## [1.0.0] - 2018-01-02

- create plugin