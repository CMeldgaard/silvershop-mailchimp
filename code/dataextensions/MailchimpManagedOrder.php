<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpManagedOrder extends \DataExtension
{

	public function onStatusChange($fromStatus, $toStatus){
		$order = $this->owner;
		if($fromStatus === 'Cart'){
			$order->MailchimpSyncStatus = 'Initial';
		}else{
			$order->MailchimpSyncStatus = 'Waiting';
		}
	}

	public function onBeforeDelete()
	{
		parent::onBeforeDelete();

		//TODO Try to delete order on Mailchimp. In case of error or service not availabe, add it to the delete que,
		//for later processing thru cronjob

	}

}
