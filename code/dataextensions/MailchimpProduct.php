<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpProduct extends \DataExtension
{
	private static $db = array(
		'LastSynced' => 'DateTime'
	);
}
