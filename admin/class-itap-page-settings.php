<?php


class ItapPageSettings
{

    /**
     * Ajax function to save settings
     * @return no-return
     */
    function itap_save_settings() {
        $itap_settings = array(
            'short_desc'                 => $_POST['short_desc'] ,
            'desc1'                      => $_POST['desc1'] ,
            'desc2'                      => $_POST['desc2'] ,
            'desc3'                      => $_POST['desc3'] ,
            'itap_img_1'                 => $_POST['itap_img_1'] ,
            'itap_img_2'                 => $_POST['itap_img_2'] ,
            'itap_img_3'                 => $_POST['itap_img_3'] ,
            'itap_img_1_label'           => $_POST['itap_img_1_label'] ,
            'itap_img_2_label'           => $_POST['itap_img_2_label'] ,
            'itap_img_3_label'           => $_POST['itap_img_3_label'] ,
            'desc_seo'                   => $_POST['desc_seo'] ,
            'custom_field'               => $_POST['custom_field'] ,
            'custom_field_input_1'       => $_POST['custom_field_input_1'] ,
            'custom_field_input_2'       => $_POST['custom_field_input_2'] ,
            'custom_field_input_3'       => $_POST['custom_field_input_3'] ,
            'total_words_min_page'       => $_POST['total_words_min_page'] ,
            'total_words_min_block'      => $_POST['total_words_min_block'] ,
            'total_words_min_by_cat'     => $_POST['total_words_min_by_cat'] ,
            'total_words_min_short_desc' => $_POST['total_words_min_short_desc'] ,
            'colors'                     => $_POST['colors'] ,
        );
        update_option( 'itap_settings' , $itap_settings );
        echo json_encode( 'ok' );
        wp_die();
    }

    /**
     * Display the settings page html
     */
    function itap_settings_displayTab() : void {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/itap-settings-display.php';
    }
}
