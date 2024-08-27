<?php // phpcs:ignore

/**
 * The automation class.Responsible to display all problems that can be fixed automatically.
 *
 * @package Is_There_A_Problem
 */
class ItapPageAutomation {

	/**
	 * Ajax function to change primary category
	 *
	 * @return void
	 */
	public function itap_change_primary_category(): void {
		$product_id       = isset( $_POST['product_id'] ) ? sanitize_text_field(wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore
		$new_cat_id       = isset( $_POST['new_cat_id'] ) ? sanitize_text_field(wp_unslash( $_POST['new_cat_id'] ) ) : ''; // phpcs:ignore
		$change_or_ignore = isset( $_POST['change_or_ignore'] ) ? sanitize_text_field(wp_unslash( $_POST['change_or_ignore'] ) ) : ''; // phpcs:ignore
		if ( 'ignore' !== $change_or_ignore ) {
			update_post_meta( $product_id, 'rank_math_primary_product_cat', $new_cat_id );
		}
		update_post_meta( $product_id, 'itap_ignore_primary_cat', true );
		wp_send_json_success();
	}

	/**
	 * Function to get all product.
	 */
	public function get_all_product(): array {
		$args     = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$products = new WP_Query( $args );
		return $products->posts;
	}

	/**
	 * Display the automation page html.
	 */
	public function itap_partials_automation(): void {
		require_once plugin_dir_path( __DIR__ ) . 'partials/itap-automation-display.php';
	}

	/**
	 * Return all product that have primary category that is not the parent of the product.
	 */
	public function itap_fix_primary_cat(): array {
		$maybe_product_problem = array();
		$products              = $this->get_all_product();
		foreach ( $products as $product ) {
			$product_id_and_cat_names = array();
			$product_categories_ids   = wp_get_post_terms( $product->ID, 'product_cat', array( 'fields' => 'ids' ) );
			foreach ( $product_categories_ids as $product_cat_id ) {
				$product_id_and_cat_names[ $product_cat_id ] = get_term( $product_cat_id )->name;
			}

			if ( count( $product_categories_ids ) < 2 ) {
				continue;
			}
			$primary_product_cat          = get_post_meta( $product->ID, 'rank_math_primary_product_cat', true );
			$primary_product_cat_parents  = get_ancestors( $primary_product_cat, 'product_cat' );
			$primary_product_cat_children = get_term_children( $primary_product_cat, 'product_cat' );
			if ( ! empty( $primary_product_cat_children ) && ! get_post_meta( $product->ID, 'itap_ignore_primary_cat', true ) ) {
				$maybe_product_problem[] = array(
					'product_id'               => $product->ID,
					'product_link'             => get_edit_post_link( $product->ID ),
					'product_name'             => $product->post_title,
					'primary_product_cat'      => $primary_product_cat,
					'primary_product_cat_name' => $product_id_and_cat_names,
				);
			}
		}
		return $maybe_product_problem;
	}

	/**
	 * Return all simple products that have variations.
	 */
	public function itap_show_product_simple_that_have_variations(): array {
		$products          = $this->get_all_product();
		$problems_products = array();
		foreach ( $products as $product ) {
			$product = wc_get_product( $product->ID );
			if ( $product->is_type( 'simple' ) ) {
				$product_variable = new WC_Product_Variable( $product->get_id() );
				$variations       = $product_variable->get_available_variations();
				if ( ! empty( $variations ) ) {
					$problems_products[] = array(
						'product_id'   => $product->get_id(),
						'product_link' => get_edit_post_link( $product->get_id() ),
						'product_name' => $product->get_name(),
					);
				}
			}
		}
		return $problems_products;
	}
}
