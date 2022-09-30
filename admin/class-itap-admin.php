<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-seo-quantum.php';

class ItapAdmin {

    private $plugin_name;

    private $version;

    public $lines = 0;

    public $globalErrors = 0;

    function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_menu', array($this, 'itap_add_menu'));

        // ajax call
        $ItapPageSeoQuantum = new ItapPageSeoQuantum();
        add_action("wp_ajax_get_checkbox_value", array($this, "itap_send_archive_to_db"));
        add_action("wp_ajax_delete_checkbox_value", array($this, "itap_delete_archive"));
        add_action('wp_ajax_send_request_to_seo_quantum', array($ItapPageSeoQuantum, 'itap_send_request_to_seo_quantum'));
        add_action('wp_ajax_save_seo_quantum_api_key', array($ItapPageSeoQuantum, 'itap_save_seo_quantum_api_key'));
        add_action('wp_ajax_analysis_text_seo_quantum', array($ItapPageSeoQuantum, 'itap_analysis_text_seo_quantum'));
    }

    public function enqueue_styles() {
        wp_enqueue_style('isthereaproblem', plugin_dir_url(dirname(__FILE__)) . 'admin/assets/css/itap.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script('isthereaproblemJS', plugin_dir_url(dirname(__FILE__)) . 'admin/assets/js/itap.js', array('jquery'), $this->version, true);
        wp_localize_script('isthereaproblemJS', 'my_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }


    function itap_send_archive_to_db() {
        global $wpdb;

        $uniqId = $_POST['uniqId'];

        $table_name = $wpdb->prefix . 'itap_archive';
        // create table if not exist
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uniqId varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $wpdb->insert($table_name, array(
            'uniqId' => $uniqId,
        ));

        wp_die();
    }

    function itap_delete_archive() {
        global $wpdb;
        $uniqId = $_POST['uniqId'];
        $table_name = $wpdb->prefix . 'itap_archive';
        $wpdb->delete($table_name, array('uniqId' => $uniqId));
        wp_die();
    }


    function itap_add_menu() {
        // only if we are on plugin page, call all the functions
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_archive';
        $sql = "SELECT count(*) FROM $table_name";

        $archives = $wpdb->get_var($sql);
        $total_errors = get_transient('total_integration_errors') + get_transient('count_seo_errors');
        $total_integration_errors = get_transient('total_integration_errors');
        $total_seo_errors = get_transient('count_seo_errors');

        add_menu_page('Problems', sprintf("Problems <span class='awaiting-mod'>%d</span>", $total_errors), 'publish_pages', 'is_there_a_problem', array($this, 'itap_page'), 'dashicons-admin-site', 100);
        add_submenu_page('is_there_a_problem', 'Integration', sprintf("Integration <span class='awaiting-mod'>%d</span>", $total_integration_errors), 'publish_pages', 'is_there_a_problem', array($this, 'itap_page'));
        add_submenu_page('is_there_a_problem', 'SEO', sprintf("SEO <span class='awaiting-mod'>%d</span>", $total_seo_errors), 'publish_pages', 'is_there_a_problem_seo', array($this, 'ItapPageSeo'));
        add_submenu_page('is_there_a_problem', 'Archives ', 'Archives', 'publish_pages', 'is_there_a_problem_archive', array($this, 'ItapPageArchive'));
        add_submenu_page('is_there_a_problem', 'Seo-Quantum ', 'Seo-Quantum', 'publish_pages', 'Seo-Quantum', array($this, 'ItapPageSeoQuantum'));
    }

    function ItapPageSeo() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-seo.php';
        $ItapPageSeo = new ItapPageSeo();
    }

    function ItapPageArchive() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-archive.php';
        $ItapPageArchive = new ItapPageArchive();
    }

    function ItapPageSeoQuantum() {
        $ItapPageSeoQuantum = new ItapPageSeoQuantum();
        $ItapPageSeoQuantum->itap_seo_quantum_displayTab();
    }

    function itap_displayData($result, string $problem, string $codeError) {
        return array(
            'uniqId' => $result['id'] . $codeError,
            'id' => $result['id'],
            'title' => $result['title'],
            'url' => $result['url'],
            'author_name' => $result['author_name'],
            'imageUrl' => $result['imageUrl'],
            'url_edit' => get_edit_post_link($result['id']),
            'alt' => $result['alt'],
            'error' => $problem
        );
    }

    function itap_displayTab($error, string $danger = null) {
        $allowed_html = array(
            'div' => array(
                'class' => array()
            ),
            'span' => array(
                'class' => array()
            )
        ); ?>
        <tr <?php echo esc_attr($danger ? "style=background-color:$danger;color:white;" : ''); ?>>
            <td><?php echo esc_html($error['id']); ?></td>
            <td><?php echo esc_html($error['title']); ?></td>
            <td><a target="_blank" <?php echo esc_attr($danger ? "style='color:white'" : '') ?> href="<?php echo esc_url($error['url_edit']); ?>">click</a></td>
            <td><?php echo wp_kses($error['error'], $allowed_html); ?></td>
            <td><?php echo esc_html($error['author_name']); ?></td>
            <td><input type="checkbox" class="itap_checkbox" name="archiver" class="archiver" value="<?php echo esc_attr($error['uniqId']); ?>"></td>
        </tr>
<?php
    }

    function itap_getAllInfosFromProduct() {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'id',
            'order' => 'ASC'
        );
        $pages = get_posts($args);
        $pages_array = array();
        foreach ($pages as $page) {
            $image_id = get_post_thumbnail_id($page->ID);
            $args = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'url' => $page->guid,
                'author_name' => get_the_author_meta('display_name', $page->post_author),
                'imageUrl' => get_the_post_thumbnail_url($page->ID),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
            );
            array_push($pages_array, $args);
        }
        return $pages_array;
    }

    function itap_getErrorFromBaliseAlt($results) {
        $errors = [];
        foreach ($results as $result) {
            if ($result['alt'] == '') {
                $error = $this->itap_displayData($result, 'Balise alt vide', '1001');
                array_push($errors, $error);
            } elseif (strlen($result['alt']) < 10) {
                $error = $this->itap_displayData($result, 'Balise alt trop courte', '1002');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_getErrorFromVariableProducts($results) {
        $errors = [];
        $couleurs = array('rouge', 'bleu', 'vert', 'jaune', 'noir', 'blanc', 'gris', 'marron', 'orange', 'rose', 'violet', 'multicolore', 'kaki', 'fuchsia', 'doré', 'camouflage', 'camel', 'bordeaux', 'beige', 'argenté');
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            // check the attributes of the product to know if the default variation is first element of each attribute
            $attribute_names = $product->get_attributes();
            $terms = [];
            if ($attribute_names) {
                foreach ($attribute_names as $attribute_name => $attribute) {
                    if ($attribute->is_taxonomy() && $attribute['variation']) {
                        $terms[$attribute_name] = wc_get_product_terms($result['id'], $attribute['name'], array('fields' => 'slugs'))[0] ?? null;
                    }
                }
            }

            // check if the product has variations
            if ($product->is_type('variable')) {
                if (!$product->get_default_attributes()) {
                    $error = $this->itap_displayData($result, "Produit variable qui n'a pas de produit par défaut", '1003');
                    array_push($errors, $error);
                }
                $attribute_variation = [];

                foreach ($product->get_attributes() as $attribute) {
                    if ($attribute['variation']) {
                        array_push($attribute_variation, $attribute);
                    }
                }
                // check if the terms of attribute 'pa_couleur' or 'couleur are in the list of colors
                foreach ($attribute_variation as $attribute) {
                    $attribute_name = $attribute['name'];
                    if ($attribute_name == 'pa_couleur' || $attribute_name == 'couleur') {
                        if (!in_array($terms[$attribute['name']], $couleurs) &&  $attribute['variation']) {
                            $error = $this->itap_displayData($result, 'Produit variable dont la couleur définie ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode(", ", $couleurs) . '</span></div>', '1004');
                            array_push($errors, $error);
                        }
                    }
                }
                if (count($attribute_variation) != count($product->get_default_attributes())) {
                    $error = $this->itap_displayData($result, 'Produit variable ou il manque une ou plusieurs variations dans le produit par défaut', '1005');
                    array_push($errors, $error);
                }
                if (array_diff($product->get_default_attributes(), $terms)) {
                    $error = $this->itap_displayData($result, 'Produit variable dont le produit par défaut à comme variation des valeurs qui ne sont pas les premieres de leurs <div class="tooltip">catégories<span class="tooltiptext">Erreur qui signale également les attributs remplis à la volée directement sur la page du produit, merci de rentrer tous les attributs et leurs termes dans l\'onglet attribut de produit</span></div>. ', '1006');
                    array_push($errors, $error);
                }
                if (count($product->get_children()) == 0) {
                    $error = $this->itap_displayData($result, 'Produit variable qui n\'a pas de variations, ajoutez en ou passez le en produit simple', '1007');
                    array_push($errors, $error);
                }
            }
        }
        return $errors;
    }

    function itap_getErrorsFromImages($results) {
        // try ton know if wordpress create responsive images for the product
        $errors = [];
        foreach ($results as $result) {
            $image_id = get_post_thumbnail_id($result['id']);
            $errors_image = 0;
            if ($image_id == 0) {
                $error = $this->itap_displayData($result, 'Produit sans images', '1008');
                array_push($errors, $error);
            } else {
                $image_metadata = get_post_meta($image_id, '_wp_attachment_metadata', 'true');
                $upload_dir = wp_upload_dir()['basedir'];
                $base_path = substr($image_metadata['file'], 0, 8);
                $image_path = $upload_dir . '/' . $base_path;
                foreach ($image_metadata['sizes'] as $size) {
                    if (!file_exists($image_path . $size['file'])) {
                        $errors_image++;
                    }
                }
                if ($errors_image > 0) {
                    $error = $this->itap_displayData($result, 'Formats d\'images manquants/non créés par WordPress pour WooCommerce, merci de réuploader l\'image du produit', '1009');
                    array_push($errors, $error);
                }
            }
        }
        return $errors;
    }

    function itap_getErrorsFromRankMath($results) {
        $errors = [];
        foreach ($results as $result) {

            $product = wc_get_product($result['id']);
            if ($product->get_meta('rank_math_description') == '') {
                $error = $this->itap_displayData($result, 'Produit associé à Rank Math qui n\'a pas de meta description', '1010');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_getErrorsFromLinks($results) {
        $errors = [];
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            // get categories of the product
            $categories = wp_get_post_terms($result['id'], 'product_cat', array('fields' => 'slugs'));
            // get the slug of the product
            $slug = $product->get_slug();

            // check if the slug of the product is in the list of categories
            if (in_array($slug, $categories)) {
                $error = $this->itap_displayData($result, 'le slug d\'un produit ne peut pas être le même qu\'une de ses catégories', '1011');
                array_push($errors, $error);
            }
            $description1 = $product->get_meta("description-1") ?? null;
            $description2 = $product->get_meta("description-2") ?? null;
            $description3 = $product->get_meta("description-3") ?? null;
            $main_description = $product->get_description();
            $short_description = $product->get_short_description();
            // show html tags of the description

            $all_description = array($description1, $description2, $description3, $main_description, $short_description);
            $link_description = array_filter($all_description, function ($value) { // if it's a link and it's not a mailto link
                return preg_match('/href/', $value) && !preg_match('/<a href="mailto/', $value);
            });


            if (count($link_description) > 0) {
                $error = $this->itap_displayData($result, 'Description-1, description-2 ou description-3 ou description principale ou description courte du produit qui contient un lien', '1012');
                array_push($errors, $error);
            }
            // search if there are <div> or </div> in all the description
            $div_description = array_filter($all_description, function ($value) {
                return preg_match('/<div>/', $value) || preg_match('/<\/div>/', $value);
            });
            if (count($div_description) > 0) {
                $error = $this->itap_displayData($result, 'Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise div, effacez la', '1014');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_getErrorsFromDescriptions($result) {
        // check if the description of the product is less than 200 words
        $errors = [];
        foreach ($result as $result) {
            $product = wc_get_product($result['id']);
            $description1 = $product->get_meta("description-1") ?? null;
            $description2 = $product->get_meta("description-2") ?? null;
            $description3 = $product->get_meta("description-3") ?? null;
            $short_description = $product->get_short_description();
            if (str_word_count($description1) + str_word_count($description2) + str_word_count($description3) +  str_word_count($short_description)  < 200) {
                $error = $this->itap_displayData($result, 'Description-1 + description-2, descritption-3 + description courte du produit inférieures à 200 mots, mettez plus de contenu', '1013');
                array_push($errors, $error);
            }
        }
        return $errors;
    }


    /**
     * Display the results if they aren't archived
     */
    function itap_getErrors($fn, $results, $color = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_archive';
        // select uniqId from the itap_archive table
        $uniqIds = $wpdb->get_results("SELECT uniqId FROM $table_name ORDER BY id ", ARRAY_A);
        $errors = $this->$fn($results);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                if (!in_array(['uniqId' => $error['uniqId']], $uniqIds) && $this->lines < 300) {
                    $this->itap_displayTab($error, $color);
                    $this->lines++;
                }
            }
        }
    }

    function itap_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_archive';
        // count the number of lines in the itap_archive table
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $admins = get_users(array('role__in' => array('administrator', 'shop_manager')));
        if (!empty($_GET['author_name'])) {
            $results = $this->itap_getAllInfosFromProduct();
            $results = array_filter($results, function ($result) {
                return $result['author_name'] == $_GET['author_name'];
            });
        } else {
            $results = $this->itap_getAllInfosFromProduct();
        }
        $total_integration_errors = count($this->itap_getErrorsFromLinks($results)) + count($this->itap_getErrorFromBaliseAlt($results)) + count($this->itap_getErrorFromVariableProducts($results)) + count($this->itap_getErrorsFromImages($results)) + count($this->itap_getErrorsFromRankMath($results)) + count($this->itap_getErrorsFromDescriptions($results)) - $count;
        set_transient('total_integration_errors', $total_integration_errors, MONTH_IN_SECONDS);


        if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem') {
            require_once plugin_dir_path(__FILE__) . 'partials/itap-admin-display.php';
        }
    }
}
