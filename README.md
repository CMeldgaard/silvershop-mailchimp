# Silvershop Mailchimp

**(Work in progres)**

Syncs Silvershop orders with Mailchimp.

On first initialization run the task MailchimpStoreConnect to create a Store in Mailchimp. Also set up a cron-job that runs the task MailchimpSync.

```
SilverShop\Mailchimp\Connector:
  APIKey: 'XXX-XXXX'
  ListId: 'XXXXXX'
  StoreDomain: 'XXXX'
  StoreId: 'XXX'
```

Feel free to contribute the project and send PR's.

##Todo
* Mark orders that was created by a Mailchimp campaign
* Make it possible to signup to newsletter on checkout, and send data to Mailchimp with eCommerce data