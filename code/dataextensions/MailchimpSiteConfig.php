<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpSiteConfig extends \DataExtension
{
	private static $db = array(
		'MailchimpLatestSync' => 'Date',
		'MailchimpStoreID'    => 'Int',
		'ContactEmail'        => 'Varchar(255)',
	);
}
