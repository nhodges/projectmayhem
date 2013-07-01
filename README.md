projectmayhem
=============

* GET routes
	* /api/products
		* Returns array of all products.
	* /api/products/(:num)
		* Returns product info of specific product.
	* /api/variants/(:num)
		* Returns array of all variants IN DATABASE of a particular product.
	* /api/images/(:num)
		* Returns array of all images of a particular product.
	* /api/permutations/(:num)
		* Returns all possible permutations of a particular product based on unique options.

* POST routes
	* /shopify/sync
		* Synchronizes local database with data via Shopify API.