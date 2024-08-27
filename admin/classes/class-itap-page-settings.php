<?php // phpcs:ignore

/**
 * The class in charge of the settings page.
 *
 * @package Is_There_A_Problem
 */
class ItapPageSettings {


	/**
	 * Ajax function to save settings
	 *
	 * @return void
	 */
	public function itap_save_settings() {
		$itap_settings = array(
			'short_desc'                     => isset( $_POST['short_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['short_desc'] ) ) : '', // phpcs:ignore
            'desc1'                          => isset( $_POST['desc1'] ) ? sanitize_text_field( wp_unslash( $_POST['desc1'] ) ) : '', // phpcs:ignore
            'desc2'                          => isset( $_POST['desc2'] ) ? sanitize_text_field( wp_unslash( $_POST['desc2'] ) ) : '', // phpcs:ignore
            'desc3'                          => isset( $_POST['desc3'] ) ? sanitize_text_field( wp_unslash( $_POST['desc3'] ) ) : '', // phpcs:ignore
            'itap_img_1'                     => isset( $_POST['itap_img_1'] ) ? sanitize_text_field( wp_unslash( $_POST['itap_img_1'] ) ) : '', // phpcs:ignore
            'itap_img_2'                     => isset( $_POST['itap_img_2'] ) ? sanitize_text_field( wp_unslash( $_POST['itap_img_2'] ) ) : '', // phpcs:ignore
            'itap_img_3'                     => isset( $_POST['itap_img_3'] ) ? sanitize_text_field( wp_unslash( $_POST['itap_img_3'] ) ) : '', // phpcs:ignore
            'itap_img_1_label'               => isset( $_POST['itap_img_1_label'] ) ? sanitize_text_field( wp_unslash( $_POST['itap_img_1_label'] ) ) : '', // phpcs:ignore
            'itap_img_2_label'               => isset( $_POST['itap_img_2_label'] ) ? sanitize_text_field( wp_unslash( $_POST['itap_img_2_label'] ) ) : '', // phpcs:ignore
            'itap_img_3_label'               => isset( $_POST['itap_img_3_label'] ) ? sanitize_text_field( wp_unslash( $_POST['itap_img_3_label'] ) ) : '', // phpcs:ignore
            'desc_seo'                       => isset( $_POST['desc_seo'] ) ? sanitize_text_field( wp_unslash( $_POST['desc_seo'] ) ) : '', // phpcs:ignore
            'custom_field'                   => isset( $_POST['custom_field'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_field'] ) ) : '', // phpcs:ignore
            'custom_field_input_1'           => isset( $_POST['custom_field_input_1'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_field_input_1'] ) ) : '', // phpcs:ignore
            'custom_field_input_2'           => isset( $_POST['custom_field_input_2'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_field_input_2'] ) ) : '', // phpcs:ignore
            'custom_field_input_3'           => isset( $_POST['custom_field_input_3'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_field_input_3'] ) ) : '', // phpcs:ignore
            'total_words_min_page'           => isset( $_POST['total_words_min_page'] ) ? sanitize_text_field( wp_unslash( $_POST['total_words_min_page'] ) ) : '', // phpcs:ignore
            'total_words_min_block'          => isset( $_POST['total_words_min_block'] ) ? sanitize_text_field( wp_unslash( $_POST['total_words_min_block'] ) ) : '', // phpcs:ignore
            'total_words_min_by_cat'         => isset( $_POST['total_words_min_by_cat'] ) ? sanitize_text_field( wp_unslash( $_POST['total_words_min_by_cat'] ) ) : '', // phpcs:ignore
            'total_words_min_short_desc'     => isset( $_POST['total_words_min_short_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['total_words_min_short_desc'] ) ) : '', // phpcs:ignore
            'total_words_min_principal_desc' => isset( $_POST['total_words_min_principal_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['total_words_min_principal_desc'] ) ) : '', // phpcs:ignore
            'colors'                         => isset( $_POST['colors'] ) ? sanitize_text_field( wp_unslash( $_POST['colors'] ) ) : '', // phpcs:ignore

		);
		update_option( 'itap_settings', $itap_settings );
		echo wp_json_encode( 'ok' );
		wp_die();
	}

	/**
	 * Display the settings page html
	 */
	public function itap_partials_settings(): void {
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/itap-settings-display.php';
	}
}
