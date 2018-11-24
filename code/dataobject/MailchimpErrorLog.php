<?php

class MailchimpErrorLog extends DataObject
{
	private static $db = array(
		'ErrorTime'     => 'DateTime',
		'ErrorMessage'  => 'Text',
		'ErrorFunction' => 'Varchar'
	);
}
