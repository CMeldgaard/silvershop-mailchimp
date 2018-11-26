<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpPage extends \DataExtension
{
	public function init(){

		parent::init();

		//TODO Check for mc_eid in URL and store in session

		//TODO Check for mc_cid in URL and store in session
	}
}
