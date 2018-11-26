<?php

namespace SilverShop\Mailchimp\Tasks;

use SilverShop\Mailchimp\Connector;

class MailchimpStoreConnect extends \BuildTask
{
	public function run($request)
	{
		//Create store
		$connect = Connector::create()->connectStore();

		if ($connect) {
			//Sync products
			$syncProducts = Connector::create()->syncProducts();

			//Sync orders
			$syncOrders = Connector::create()->syncOrders();
		}
	}
}
