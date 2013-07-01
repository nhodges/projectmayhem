<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	function __construct() {

		parent::__construct();

		$this->config->load( 'shopify' );
		$this->load->database();
		$this->load->library( 'curl' ); 		

	}

	public function products( $id = null ) {

		header('content-type: application/json');

		$response = array();

		if ( $id && is_numeric( $id ) ) {

			$query = $this->db->select( 'products.id, products.shopify_id, products.title' )
			                  ->select( 'COUNT(variants.product_id) AS variants')
			                  ->from( 'products' )
			                  ->where( 'products.id', $id )
			                  ->join( 'variants', 'products.id = variants.product_id' )
			                  ->group_by( 'variants.product_id' )
			                  ->get();

		} else {

			$query = $this->db->select( 'products.id, products.shopify_id, products.title' )
			                  ->select( 'COUNT(variants.product_id) AS variants')
			                  ->from( 'products' )
			                  ->join( 'variants', 'products.id = variants.product_id' )
			                  ->group_by( 'variants.product_id' )
			                  ->get();

		}

		if ( $query->num_rows() > 0 ) {

			$products = $query->result();

			foreach ( $products as $product ) {

				$response[] = $product;

			}

			echo json_encode($response);

		}

	}

	public function variants( $id = null ) {

		header('content-type: application/json');

		$response = array();

		if ( $id && is_numeric( $id ) ) {

			$query = $this->db->select( '*' )
			                  ->from( 'variants' )
			                  ->where( 'product_id', $id )
			                  ->get();


			if ( $query->num_rows() > 0 ) {

				$variants = $query->result();

				foreach ( $variants as $variant ) {

					$response[] = $variant;

				}

				echo json_encode($response);

			}

		} else {

			echo json_encode( array(
				'error'         => true,
				'error_message' => 'Product ID incorrectly provided.'
			));

		}

	}

	public function images( $id = null ) {

		header('content-type: application/json');

		$response = array();

		if ( $id && is_numeric( $id ) ) {

			$query = $this->db->select( '*' )
			                  ->from( 'images' )
			                  ->where( 'product_id', $id )
			                  ->get();


			if ( $query->num_rows() > 0 ) {

				$images = $query->result();

				foreach ( $images as $image ) {

					$response[] = $image->src;

				}

				echo json_encode($response);

			}

		} else {

			echo json_encode( array(
				'error'         => true,
				'error_message' => 'Product ID incorrectly provided.'
			));

		}

	}

}

/* End of file api.php */
/* Location: ./application/controllers/api.php */