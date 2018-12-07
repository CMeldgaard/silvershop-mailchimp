<?php

namespace SilverShop\Mailchimp\Dataextensions;

class MailchimpPageController extends \DataExtension
{
	public function OnAfterInit(){

		\Session::set('MailchimpEID',\Controller::curr()->getRequest()->getVar('mc_eid'));
		\Session::set('MailchimpCID',\Controller::curr()->getRequest()->getVar('mc_cid'));
		\Session::set('MailchimpTC',\Controller::curr()->getRequest()->getVar('mc_tc'));

	}
}
