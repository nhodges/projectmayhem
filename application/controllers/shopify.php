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

			$this->db->query( "INSERT INTO products (shopify_id, title, last_sync) VALUES ({$product->id}, '{$product->title}', CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE last_sync = CURRENT_TIMESTAMP" );
			$product_id = $this->db->insert_id();

			foreach ( $product->variants as $variant ) {

				$query = $this->db->select( 'id' )
				                  ->from( 'variants' )
				                  ->where( 'shopify_id', $variant->id )
				                  ->get();

				if ( $query->num_rows() == 0 ) {

					$dbvariant = array(
						'product_id' => $product_id,
						'shopify_id' => $variant->id,
						'sku'        => $variant->sku,
						'title'      => $variant->title,
						'quantity'   => $variant->inventory_quantity
					);

					$this->db->ignore()
					         ->insert( 'variants', $dbvariant );

				} else {

					$this->db->set( 'quantity', $variant->inventory_quantity )
					         ->set( 'last_sync', 'CURRENT_TIMESTAMP', FALSE)
					         ->where( 'shopify_id', $variant->id )
					         ->update( 'variants' );

				}


			}

		}

	}
}

/* End of file shopify.php */
/* Location: ./application/controllers/shopify.php */