# SilverShop Mailchimp integration

Adds integration to Mailchimp API v3 eCommerce. **(Work in progres)**

[![Latest Stable Version](https://poser.pugx.org/lundco/silvershop-mailchimp/v/stable)](https://packagist.org/packages/lundco/silvershop-mailchimp)
[![Latest Unstable Version](https://poser.pugx.org/lundco/silvershop-mailchimp/v/unstable)](https://packagist.org/packages/lundco/silvershop-mailchimp)
[![License](https://poser.pugx.org/lundco/silvershop-mailchimp/license)](https://packagist.org/packages/lundco/silvershop-mailchimp)

## Installation

composer require "lundco/silvershop-mailchimp"

In your project YML-file, create the following (replacing XXX with your own data):

```
SilverShop\Mailchimp\Connector:
  APIKey: 'XXX-XXXX'
  ListId: 'XXXXXX'
  StoreDomain: 'XXXX'
  StoreId: 'XXX'
```

After installing the module, rebuild the database and create a store in MailChimp by running the task `MailchimpStoreConnect`.

### Cronjobs
To sync orders/products from Silvershop to Mailchimp make sure you setup a cronjob to run the `MailchimpSync` task.

##Todo
* Mark orders that was created by a Mailchimp campaign
* Make it possible to signup to newsletter on checkout, and send data to Mailchimp with eCommerce data
* Update to Silverstripe 4

Feel free to contribute to the project and send PR's.
