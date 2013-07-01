<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shopify extends CI_Controller {

	function __construct() {

		parent::__construct();

		$this->config->load( 'shopify' );
		$this->load->database();
		$this->load->library( 'curl' ); 		

	}

	public function sync() {
		
		$base_url = 'https://' . $this->config->item( 'shopify_apikey' ) . ':' . $this->config->item( 'shopify_password' ) . '@' . $this->config->item( 'shopify_domain' ) . '.myshopify.com/';

		$this->curl->create( $base_url . "admin/products.json" );
		$this->curl->option( CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem' );

		$response = json_decode($this->curl->execute());

		foreach ( $response->products as $product ) {

			$this->db->query( "INSERT INTO products (shopify_id, title) VALUES ({$product->id}, '{$product->title}') ON DUPLICATE KEY UPDATE title = '{$product->title}', last_sync = CURRENT_TIMESTAMP" );
			$product_id = $this->db->insert_id();

			foreach ( $product->variants as $variant ) {

				$this->db->query( "INSERT INTO variants (product_id, shopify_id, sku, title, quantity) VALUES ({$product_id}, {$variant->id}, '{$variant->sku}', '{$variant->title}', {$variant->inventory_quantity}) ON DUPLICATE KEY UPDATE sku = '{$variant->sku}', title = '{$variant->title}', quantity = {$variant->inventory_quantity}, last_sync = CURRENT_TIMESTAMP" );

			}

			foreach ( $product->images as $image ) {

				$this->db->query( "INSERT INTO images (shopify_id, product_id, src) VALUES ({$image->id}, {$product_id}, '{$image->src}') ON DUPLICATE KEY UPDATE src = '{$image->src}', last_sync = CURRENT_TIMESTAMP" );

			}

		}

	}
}

/* End of file shopify.php */
/* Location: ./application/controllers/shopify.php */