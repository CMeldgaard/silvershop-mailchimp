<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpSiteConfig extends \DataExtension
{
	private static $db = array(
		'ContactEmail'      => 'Varchar(255)',
	);
}
