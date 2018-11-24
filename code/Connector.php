<?php

namespace SilverShop\Mailchimp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class Connector extends \DataObject
{

	protected $headers;
	protected $client;
	protected $APIKey;
	protected $dataCenter;
	protected $storeID;
	protected $storeCurrency;
	protected $storeDomain;
	protected $listID;

	//Generate Guzzle client with correct authorization method
	public function __construct()
	{
		parent::__construct();

		$token = self::config()->APIKey;

		//Get datacenter from API-key
		list($apikey, $dataCenter) = explode('-', $token);

		$this->client = new Client([
			'base_uri' => 'https://' . $dataCenter . '.api.mailchimp.com/3.0/',
			'auth'     => ['apikey', $apikey],
		]);

		$this->headers = [
			'content-type' => 'application/json',
		];

		//Set base settings
		$this->storeID = 'Silvershop-' . \SiteTree::create()->generateURLSegment(\SiteConfig::get()->first()->Title);
		$this->listID = self::config()->ListId;
		$this->storeCurrency = \ShopConfig::config()->base_currency;
	}

	public function decodeResponse($response)
	{
		return \GuzzleHttp\json_decode($response);
	}

	/* STORES */
	public function connectStore()
	{
		$siteConfig = \SiteConfig::get();

		//Set store to syncing, to avoid triggering automations etc. Only set to syncing the first time, to transfer old orders
		try{
			$result = $this->client->request('POST', 'ecommerce/stores', [
				$this->headers,
				'json' => [
					'id'            => $this->storeID,
					'list_id'       => $this->listID,
					'name'          => $siteConfig->first()->Title,
					'domain'        => '',
					'email_address' => $siteConfig->first()->ContactEmail,
					'currency_code' => $this->storeCurrency,
					'platform'      => 'Silverstripe - Silvershop'
				]
			]);
		}catch (BadResponseException $e){
			//TODO - Log exception to error log
			exit;
		}

		return $this->decodeResponse($result->getBody()->getContents());
	}

	public function getAllStores()
	{
		try{
			$result = $this->client->request('GET', 'ecommerce/stores', [
				$this->headers
			]);
		}catch (BadResponseException $e){
			//TODO - Log exception to error log
			exit;
		}

		return $this->decodeResponse($result->getBody()->getContents());
	}

	public function getStore()
	{
		$result = $this->client->request('GET', 'ecommerce/stores/' . $this->storeID);
	}

	public function syncProducts()
	{
		$productList = \Product::get();
		foreach ($productList as $product) {
			try{
				$this->client->request('POST', 'ecommerce/stores/' . $this->storeID . '/products', [
					'json' => [
						'id'       => $product->internalItemID,
						'title'    => $product->Title,
						'variants' => $this->getVariants($product)
					]
				]);
			}catch (BadResponseException $e){
				//TODO - Log exception to error log
				continue;
			}
		}
	}

	public function getVariants($product)
	{
		$variants = [];

		if (is_string($product->has_many('Variations')) && $product->Variations()->exists()) {
			foreach ($product->Variations() as $variation) {
				$variants[] = [
					'id'    => $variation->internalItemID,
					'title' => $product->Title . ' - ' . $variation->Title
				];
			}
		}else {
			$variants[] = [
				'id'    => $product->internalItemID,
				'title' => $product->Title
			];
		}

		return $variants;
	}

}
