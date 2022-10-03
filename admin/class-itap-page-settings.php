<?php


class ItapPageSettings {

    function itap_save_settings() {
        $short_desc = $_POST['short_desc'];
        $desc1 = $_POST['desc1'];
        $desc2 = $_POST['desc2'];
        $desc3 = $_POST['desc3'];
        $desc_seo = $_POST['desc_seo'];
        $custom_field = $_POST['custom_field'];
        $custom_field_input_1 = $_POST['custom_field_input_1'];
        $custom_field_input_2 = $_POST['custom_field_input_2'];
        $custom_field_input_3 = $_POST['custom_field_input_3'];
        $total_words_min_page = $_POST['total_words_min_page'];
        $total_words_min_block = $_POST['total_words_min_block'];
        $itap_settings = array(
            'short_desc' => $short_desc,
            'desc1' => $desc1,
            'desc2' => $desc2,
            'desc3' => $desc3,
            'desc_seo' => $desc_seo,
            'custom_field' => $custom_field,
            'custom_field_input_1' => $custom_field_input_1,
            'custom_field_input_2' => $custom_field_input_2,
            'custom_field_input_3' => $custom_field_input_3,
            'total_words_min_page' => $total_words_min_page,
            'total_words_min_block' => $total_words_min_block
        );
        update_option('itap_settings', $itap_settings);
        echo json_encode('ok');
        wp_die();
    }

    function itap_settings_displayTab() {
        $itap_settings = get_option('itap_settings');
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/itap-settings-display.php';
    }
}
