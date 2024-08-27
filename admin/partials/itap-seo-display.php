<?php
/**
 * Display the seo tab html
 *
 * @package Is_There_A_Problem
 */

global $wpdb;
$table_archive  = $wpdb->prefix . 'itap_seo_archive';
$count_archives = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %s', $table_archive ) );
?>

<div class="wrap is-there-a-problem-container">
	<p>Problèmes liés au référencement naturel</p>

	<table class="table-plugin">
		<thead>
		<tr class="thead-plugin">
			<th>Id</th>
			<th>Nom</th>
			<th>Url</th>
			<th>Problème remonté</th>
			<th>Archiver</th>
		</tr>
		</thead>
		<tbody class="tbody-plugin">
		<?php
		$this->get_errors_from_seo( array( $this, 'itap_get_errors_from_meta_title' ), array( $this, 'itap_seo_display_tab' ) );
		$this->get_errors_from_seo( array( $this, 'itap_no_category_or_attribute_with_numbers_in_slug' ), array( $this, 'itap_seo_display_tab' ) );
		$this->get_errors_from_seo( array( $this, 'itap_get_errors_nofollow_link' ), array( $this, 'itap_seo_display_tab' ) );
		$this->get_errors_from_seo( array( $this, 'itap_get_errors_from_product_cat' ), array( $this, 'itap_seo_display_tab' ) );
		$this->get_errors_from_seo( array( $this, 'itap_get_errors_from_product_tag' ), array( $this, 'itap_seo_display_tab' ) );
		$this->get_errors_from_seo( array( $this, 'itap_get_errors_from_product_attr' ), array( $this, 'itap_seo_display_tab' ) );
		$this->get_errors_from_seo( array( $this, 'itap_get_errors_no_tags_description' ), array( $this, 'itap_seo_display_tab' ) );
		?>
		</tbody>
	</table>
	<p>Nombre d'erreurs : <?php echo esc_html( $this->nb_errors ); ?>
</p>
</div>
