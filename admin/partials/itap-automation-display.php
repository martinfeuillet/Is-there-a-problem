<?php
/**
 * Display the automation page html
 *
 * @package Is_There_A_Problem
 */

?>
<div class="wrap">
	<h1>Automation</h1>
	<?php
	if ( $this->itap_fix_primary_cat() ) :
		?>
		<h3>Produit appartenant à plusieurs catégories dont la catégorie primaire n'est pas la derniere
			enfant</h3>
		<div class="select_primary_textarea">
			<table class="select_primary_cat_table">
				<thead>
				<tr>
					<th>Produit</th>
					<th>Catégorie primaire</th>
					<th>Action</th>
				</tr>
				</thead>
				<?php
				foreach ( $this->itap_fix_primary_cat() as $product ) {
					?>
					<tr class="row">
						<td>
							<a target="_blank"
								href="<?php echo esc_html( $product['product_link'] ); ?>"><?php echo esc_html( $product['product_name'] ); ?></a>
						</td>
						<td>
							<select name="select_primary_cat" id="select_primary_cat">
								<?php
								foreach ( $product['primary_product_cat_name'] as $category_id => $cat_name ) {
									$selected = ( $category_id === $product['primary_product_cat'] ) ? 'selected' : '';
									printf( "<option value='%s' %s>%s</option>", esc_attr( $category_id ), esc_attr( $selected ), esc_html( $cat_name ) );
								}
								?>
							</select>
						</td>
						<td>
							<button data-choice="validate"
									data-product-id="<?php echo esc_attr( $product['product_id'] ); ?>"
									class="button-primary button_choice">Valider
							</button>
							<button data-choice="ignore" data-product-id="<?php echo esc_attr( $product['product_id'] ); ?>"
									class="button button_choice ">ignorer
							</button>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
	<?php endif; ?> <!--    end if fix primary cat-->
	<?php if ( $this->itap_show_product_simple_that_have_variations() ) : ?>
		<h3>Un produit simple ne peut pas avoir de variations existantes</h3>
		<div class="select_primary_textarea">
			<table class="select_primary_cat_table">
				<thead>
				<tr>
					<th>Produit</th>
					<th>Action</th>
				</tr>
				</thead>
				<?php
				foreach ( $this->itap_show_product_simple_that_have_variations() as $product ) {
					?>
					<tr class="row">
						<td>
							<a target="_blank"
								href="<?php echo esc_url( $product['product_link'] ); ?>"><?php echo esc_html( $product['product_name'] ); ?></a>
						</td>
						<td>
							<button data-choice="validate"
									data-product-id="<?php echo esc_attr( $product['product_id'] ); ?>"
									class="button-primary button_choice">supprimer variations
							</button>
							<button data-choice="ignore"
									data-product-id="<?php echo esc_attr( $product['product_id'] ); ?>"
									class="button-primary button_choice">Ignorer
							</button>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
	<?php endif; ?>
</div>
