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

		// sync products from api to db
		// $this->curl->create()

		header('content-type: text/plain');
		$this->curl->create( $base_url . "admin/products.json" );
		$this->curl->option( CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem' );

		$response = json_decode($this->curl->execute());

		foreach ( $response->products as $product ) {

			$dbproduct = array(
				'shopify_id' => $product->id,
				'title'      => $product->title
			);

			$this->db->ignore()
			         ->insert( 'products', $dbproduct );
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

					$this->db->where( 'shopify_id', $variant->id )
					         ->update( 'variants', array( 'quantity' => $variant->inventory_quantity ) );

				}


			}

		}

	}
}

/* End of file shopify.php */
/* Location: ./application/controllers/shopify.php */