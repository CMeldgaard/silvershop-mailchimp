<?php

namespace SilverShop\Mailchimp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class Connector extends \Object
{

	protected $headers;
	protected $client;
	protected $APIKey;
	protected $dataCenter;
	protected $storeID;
	protected $storeCurrency;
	protected $storeDomain;
	protected $listID;

	/**
	 * Connector constructor.
	 */
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
		$this->storeID = self::config()->StoreId;
		$this->listID = self::config()->ListId;
		$this->StoreDomain = self::config()->StoreDomain;
		$this->storeCurrency = \ShopConfig::config()->base_currency;
	}

	/**
	 * @param $response
	 * @return mixed
	 */
	public function decodeResponse($response)
	{
		return \GuzzleHttp\json_decode($response);
	}

	/**
	 * @return mixed
	 */
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
					'domain'        => $this->storeDomain,
					'email_address' => $siteConfig->first()->ContactEmail,
					'currency_code' => $this->storeCurrency,
					'platform'      => 'Silverstripe - Silvershop'
				]
			]);
		}catch (BadResponseException $e){
			//TODO - Log exception to error log
			return false;
		}

		return $this->decodeResponse($result->getBody()->getContents());
	}

	public function syncProducts()
	{
		$productList = \Product::get()->exclude('MailchimpSyncStatus', 'Synced');

		foreach ($productList as $product) {

			if ($product->MailchimpSyncStatus === 'Initial' OR $product->MailchimpSyncStatus === 'Failed create') {
				$method = 'POST';
				$uri = 'ecommerce/stores/' . $this->storeID . '/products';
				$syncFail = 'Failed create';
			}else {
				$method = 'PATCH';
				$uri = 'ecommerce/stores/' . $this->storeID . '/products/' . $product->ID;
				$syncFail = 'Failed sync';
			}

			try{
				$this->client->request($method, $uri, [
					'json' => [
						'id'        => (string)$product->ID,
						'title'     => $product->Title,
						'url'       => $product->AbsoluteLink(),
						'image_url' => $product->Image()->AbsoluteURL,
						'variants'  => $this->getVariants($product)
					]
				]);
			}catch (BadResponseException $e){
				//TODO - Log exception to error log
				$product->MailchimpSyncStatus = $syncFail;
				$product->MailchimpLastSync = date('Y-m-d H:i:s');
				$product->write();
				continue;
			}

			$product->MailchimpSyncStatus = 'Synced';
			$product->MailchimpLastSync = date('Y-m-d H:i:s');
			$product->write();
		}
	}

	public function syncOrders()
	{
		$orders = \Order::get()->exclude('Status', 'Cart')->exclude('MailchimpSyncStatus', 'Synced');
		//TODO extend point to be able to specify what statuses should be synced

		foreach ($orders as $order) {

			if ($order->MailchimpSyncStatus === 'Initial' OR $order->MailchimpSyncStatus === 'Failed create') {
				$method = 'POST';
				$uri = 'ecommerce/stores/' . $this->storeID . '/orders';
				$syncFail = 'Failed create';
			}else {
				$method = 'PATCH';
				$uri = 'ecommerce/stores/' . $this->storeID . '/orders/' . $order->ID;
				$syncFail = 'Failed sync';
			}

			$ordertime = new \DateTime($order->Placed);
			$ordertime = $ordertime->format(\DateTime::ATOM);

			$updated = new \DateTime($order->LastEdited);
			$updated = $updated->format(\DateTime::ATOM);

			$cancelled = '';

			if ($order->Status === 'AdminCancelled' OR $order->Status === 'MemberCancelled') {
				$cancelled = new \DateTime($order->LastEdited);
				$cancelled = $cancelled->format(\DateTime::ATOM);
			}

			try{
				$this->client->request($method, $uri, [
					'json' => [
						'id'                   => (string)$order->ID,
						'customer'             => [
							'id'            => $this->getCustomerID($order),
							'email_address' => $order->Email,
							'company'       => (string)$order->CompanyName,
							'first_name'    => $order->FirstName,
							'last_name'     => $order->Surname,
							'opt_in_status' => false //TODO - add orderfield to specify if newsletter signup on checkout
						],
						'currency_code'        => $this->storeCurrency,
						'order_total'          => $order->Total,
						'shipping_total'       => $order->ShippingTotal,
						'lines'                => $this->getOrderLines($order),
						'processed_at_foreign' => $ordertime,
						'updated_at_foreign'   => $updated,
						'cancelled_at_foreihn' => $cancelled,
						'tracking_code'        => (string)$order->MailchimpTC,
						'outreach'             => [
							'id' => (string)$order->MailchimpCID
						]
					]
				]);
			}catch (BadResponseException $e){
				//TODO - Log exception to error log
				$order->MailchimpSyncStatus = $syncFail;
				$order->MailchimpLastSync = date('Y-m-d H:i:s');
				$order->write();
				continue;
			}

			$order->MailchimpSyncStatus = 'Synced';
			$order->MailchimpLastSync = date('Y-m-d H:i:s');
			$order->write();

		}
	}

	/**
	 * @param $product
	 * @return array
	 */
	public function getVariants($product)
	{
		$variants = [];

		if (is_string($product->has_many('Variations')) && $product->Variations()->exists()) {
			foreach ($product->Variations() as $variation) {
				$variants[] = [
					'id'    => $product->ID . '-' . $variation->ID,
					'title' => $product->Title . ' - ' . $variation->Title
				];
			}
		}else {
			$variants[] = [
				'id'    => (string)$product->ID,
				'title' => $product->Title
			];
		}

		return $variants;
	}

	/**
	 * @param $order
	 * @return array
	 */
	public function getOrderLines($order)
	{
		$items = $order->Items();

		$lines = [];

		foreach ($items as $item) {

			if ($item->ClassName === 'ProductVariation_OrderItem') {
				$id = $item->Product()->ID;
				$vId = $id . '-' . $item->ProductVariationID;
			}else {
				$id = $item->Product()->ID;
				$vId = $id;
			}

			$lines[] = [
				'id'                 => (string)$item->ID,
				'product_id'         => (string)$id,
				'product_variant_id' => (string)$vId,
				'quantity'           => $item->Quantity,
				'price'              => $item->UnitPrice,
				'className'          => $item->ClassName
			];
		}

		return $lines;
	}

	public function getCustomerID($order)
	{
		if ($order->MailchimpEID) {
			try{
				$request = $this->client->request('GET',
					'lists/' . $this->listID . '/members?unique_email_id=' . $order->MailchimpEID);
			}catch (BadResponseException $e){

				return md5(strtolower($order->Email));

			}

			$decoded = \GuzzleHttp\json_decode($request->getBody()->getContents());

			return $decoded['id'];

		}else {
			return md5(strtolower($order->Email));
		}
	}

}
