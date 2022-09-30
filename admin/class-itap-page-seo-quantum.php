<?php

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

class ItapPageSeoQuantum {
    function __construct() {
    }

    function itap_save_seo_quantum_api_key() {
        $apiKey = $_POST['apiKey'];
        if (strlen($apiKey) < 16) return;
        update_option('itap_seo_quantum_api_key', $apiKey);
        wp_die();
    }

    function itap_send_request_to_seo_quantum() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'seo_quantum';


        $ch = curl_init();
        $api_key = get_option('itap_seo_quantum_api_key');
        $cat_name = $_POST['cat_name'];
        // get the id with the slug of the product cat
        $cat_id = get_term_by('slug', $cat_name, 'product_cat')->term_id;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://api.seoquantum.com/api/task/analysis/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"target_keyword\": \"$cat_name\",\n  \"lang\": \"fr-FR\"\n}");

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Api-Key: ' . $api_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Errorrr:' . curl_error($ch);
        }
        curl_close($ch);
        $this->insert_seo_quantum_data($result, $cat_id, $cat_name);
        wp_die();
    }

    function insert_seo_quantum_data($result, $cat_id, $cat_name) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'seo_quantum';


        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cat_id mediumint(9) NOT NULL,
            keyword varchar(255) NOT NULL,
            analysis_id int(11) NOT NULL, 
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            score varchar(255) NULL,
            competitor_score varchar(255) NULL,
            improvement_score varchar(255) NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        // insert data
        $result = json_decode($result, true);
        // check if the data is already in the table
        $check = $wpdb->get_results("SELECT * FROM $table_name WHERE cat_id = $cat_id");

        if ($check) return;
        $try = $wpdb->insert(
            $table_name,
            array(
                'cat_id' => $cat_id,
                'keyword' => $cat_name,
                'analysis_id' => $result['analysis_id']
            )
        );
        // check if the data is inserted
        if ($try) {
            echo 'Data inserted';
        } else {
            echo 'Data not inserted';
        }
    }


    function display_all_cate_product() {
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        );
        $categories = get_categories($args);
        $results = array();
        foreach ($categories as $category) {
            $temp = array();
            $temp['id'] = $category->term_id;
            $temp['name'] = $category->name;
            $temp['link'] = get_edit_term_link($category->term_id, 'product_cat');
            array_push($results, $temp);
        }
        return $results;
    }




    function itap_seo_quantum_displayTab() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/itap-seo-quantum-display.php';
    }
}
