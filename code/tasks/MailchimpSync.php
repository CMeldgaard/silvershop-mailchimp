<?php

namespace SilverShop\Mailchimp\Tasks;

use SilverShop\Mailchimp\Connector;

class MailchimpSync extends \BuildTask
{
	public function run($request)
	{

		//Sync products thats new or waiting to be synced. Products are always synced first to prevent error
		//from missing order products
		$syncProducts = Connector::create()->syncProducts();

		//Sync orders thats new or waiting to be synced
		$syncOrders = Connector::create()->syncOrders();

		//Delete orders stored in deleteque
	}
}
