<?php // phpcs:ignore


require_once plugin_dir_path( __DIR__ ) . 'inc/code-error.php';

/**
 * This file is used to show all the problems that have been archived
 *
 * @package Is_There_A_Problem
 */
class ItapPageArchive {

	/**
	 * Display the archive html
	 */
	public function itap_partials_archive(): void {
		if ( isset( $_GET['page'] ) && 'is_there_a_problem_archive' === $_GET['page'] ) {
			?>
			<div class="wrap is-there-a-problem-container">
				<p>Problèmes archivés</p>
				<table class="table-plugin">
					<thead>
					<tr class="thead-plugin">
						<th class="thead-plugin-little">Id Produit</th>
						<th class="thead-plugin-middle">Nom Produit</th>
						<th class="thead-plugin-little">Url Produit</th>
						<th class="thead-plugin-big">Problème remonté</th>
						<th class="thead-plugin-little">Nom intégrateur</th>
						<th class="thead-plugin-little">archiver</th>
					</tr>
					</thead>
					<tbody class="tbody-plugin">
						<?php
						$archive = $this->itap_get_archive_from_database();
						foreach ( $archive as $error ) {
							echo wp_kses_post( $this->itap_archive_display_tab( $error ) );
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}

	/**
	 * Display the archive html.
	 *
	 * @param array $error  that contain the error.
	 */
	public function itap_archive_display_tab( array $error ): string {
		$allowed_html = array(
			'div'  => array(
				'class' => array(),
			),
			'span' => array(
				'class' => array(),
			),
		);
		ob_start();
		?>
		<tr>
			<td><?php echo esc_html( $error['productId'] ); ?></td>
			<td><?php echo esc_html( $error['product_title'] ); ?></td>
			<td><a target="_blank" href="<?php echo esc_url( $error['product_url'] ); ?>">click</a></td>
			<td><?php echo wp_kses( $error['problem'], $allowed_html ); ?></td>
			<td><?php echo esc_html( $error['product_author'] ); ?></td>
			<td><input type="checkbox" class="itap_checkbox" checked="true" name="archiver" class="archiver"
						value="<?php echo esc_attr( $error['uniqId'] ); ?>"></td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get all archive from database.
	 */
	public function itap_get_archive_from_database(): array {
		global $wpdb;
		include plugin_dir_path( __FILE__ ) . 'inc/code-error.php';
		$table_name   = $wpdb->prefix . 'itap_archive';
		$results      = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A); // phpcs:ignore
		$info_product = array();
		foreach ( $results as $result => $value ) {
			$uniq_id    = $value['uniqId'];
			$code_error = substr( $uniq_id, -4 );
			$product_id = substr( $uniq_id, 0, -4 );
			$product    = wc_get_product( $product_id );
			if ( $product ) {
				$info_product[] = array(
					'uniqId'         => $uniq_id,
					'productId'      => $product->get_id(),
					'product_title'  => $product->get_title(),
					'product_url'    => get_edit_post_link( $product_id ),
					'product_author' => get_the_author_meta( 'display_name', get_post_field( 'post_author', $product->get_id() ) ),
					'problem'        => $code_error_file[ $code_error ],
				);
			}
		}
		return $info_product;
	}
}
