<?php

namespace SilverShop\Mailchimp\Dataobjects;

class MailchimpErrorLog extends \DataObject
{
	private static $db = array(
		'ErrorTime'     => 'Date',
		'ErrorMessage'  => 'Text',
		'ErrorFunction' => 'Varchar'
	);
}
