<?php

namespace SilverShop\Mailchimp\Tasks;

use SilverShop\Mailchimp\Connector;

class MailchimpStoreConnect extends \BuildTask
{
	public function run($request)
	{
		// TODO: Implement run() method.

		//Create store
		$connect = Connector::create()->connectStore();

		echo '<pre>';
		print_r($connect);
		echo '</pre>';

		//Sync products


		//Sync orders


		//Set is_syncing to false
	}
}
