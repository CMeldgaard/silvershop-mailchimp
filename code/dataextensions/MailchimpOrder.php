<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpOrder extends \DataExtension
{
	private static $db = array(
		'MailchimpSyncStatus' => 'Enum("Synced, Failed sync, Failed create, Initial, Waiting","Initial")',
		'MailchimpLastSync'   => 'SS_Datetime',
		'FromMailchimp'       => 'Boolean',
		'MailchimpEID'        => 'Varchar',
		'MailchimpCID'        => 'Varchar',
		'MailchimpTC'         => 'Varchar'
	);

	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		//TODO Store mc_eid and mc_cid on the order

		if (!$this->ID) {
			$this->MailchimpEID = \Session::get('MailchimpEID');
			$this->MailchimpCID = \Session::get('MailchimpCID');
			$this->MailchimpTV = \Session::get('MailchimpTC');
		}

	}
}
