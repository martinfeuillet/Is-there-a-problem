<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-seo-quantum.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-settings.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-automation.php';

class ItapAdmin
{
    public $lines = 0;
    public $globalErrors = 0;
    protected $plugin_pages = array('is_there_a_problem', 'is_there_a_problem_seo', 'is_there_a_problem_archive', 'seo_quantum', 'itap_reglages', 'is_there_a_problem_automation');
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_menu', array($this, 'itap_add_menu'));

        // ajax call
        $ItapPageSeoQuantum = new ItapPageSeoQuantum();
        $ItapPageSettings = new ItapPageSettings();
        $ItapPageAutomation = new ItapPageAutomation();

        add_action('wp_ajax_get_checkbox_value', array($this, 'itap_send_archive_to_db'));
        add_action('wp_ajax_delete_checkbox_value', array($this, 'itap_delete_archive'));
        add_action('wp_ajax_send_request_to_seo_quantum', array($ItapPageSeoQuantum, 'itap_send_request_to_seo_quantum'));
        add_action('wp_ajax_save_seo_quantum_api_key', array($ItapPageSeoQuantum, 'itap_save_seo_quantum_api_key'));
        add_action('wp_ajax_analysis_text_seo_quantum', array($ItapPageSeoQuantum, 'itap_analysis_text_seo_quantum'));
        add_action('wp_ajax_itap_save_settings', array($ItapPageSettings, 'itap_save_settings'));
        add_action('wp_ajax_fix_primary_cat', array($ItapPageAutomation, 'itap_fix_primary_cat'));
        add_action('wp_ajax_change_primary_category', array($ItapPageAutomation, 'itap_change_primary_category'));
        add_action('wp_ajax_fix_variation_color', array($ItapPageAutomation, 'itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur'));

    }

    public function enqueue_styles()
    {
        if (isset($_GET['page']) && in_array($_GET['page'], $this->plugin_pages)) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'admin/assets/css/itap.css', array(), $this->version, 'all');
        }
    }

    public function enqueue_scripts()
    {
        if (isset($_GET['page']) && in_array($_GET['page'], $this->plugin_pages)) {
            wp_enqueue_script('isthereaproblemJS', plugin_dir_url(dirname(__FILE__)) . 'admin/assets/js/itap.js', array('jquery'), $this->version, true);
            wp_localize_script('isthereaproblemJS', 'my_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
        }
    }

    public function itap_send_archive_to_db()
    {
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

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $wpdb->insert($table_name, array(
            'uniqId' => $uniqId,
        ));

        wp_die();
    }

    public function itap_delete_archive()
    {
        global $wpdb;
        $uniqId = $_POST['uniqId'];
        $table_name = $wpdb->prefix . 'itap_archive';
        $wpdb->delete($table_name, array('uniqId' => $uniqId));
        wp_die();
    }

    // only if we are on plugin page, call all the functions
    public function itap_add_menu()
    {
        $total_integration_errors = get_option('total_integration_errors') > 0 ? get_option('total_integration_errors') : 0;
        $total_seo_errors = get_option('count_seo_errors') > 0 ? get_option('count_seo_errors') : 0;
        $total_errors = $total_integration_errors + $total_seo_errors;

        add_menu_page('Problems', sprintf("Problems <span class='awaiting-mod'>%d</span>", $total_errors), 'publish_pages', 'is_there_a_problem', array($this, 'itap_page'), 'dashicons-admin-site', 100);
        add_submenu_page('is_there_a_problem', 'Integration', sprintf("Integration <span class='awaiting-mod'>%d</span>", $total_integration_errors), 'publish_pages', 'is_there_a_problem', array($this, 'itap_page'));
        add_submenu_page('is_there_a_problem', 'SEO', sprintf("SEO <span class='awaiting-mod'>%d</span>", $total_seo_errors), 'publish_pages', 'is_there_a_problem_seo', array($this, 'ItapPageSeo'));
        add_submenu_page('is_there_a_problem', 'Automatisation', 'Automatisation', 'publish_pages', 'is_there_a_problem_automation', array($this, 'ItapPageAutomation'));
        add_submenu_page('is_there_a_problem', 'Archives ', 'Archives', 'publish_pages', 'is_there_a_problem_archive', array($this, 'ItapPageArchive'));
        add_submenu_page('is_there_a_problem', 'Seo-Quantum ', 'Seo-Quantum', 'publish_pages', 'seo_quantum', array($this, 'ItapPageSeoQuantum'));
        add_submenu_page('is_there_a_problem', 'Reglages ', 'Reglages', 'publish_pages', 'itap_reglages', array($this, 'ItapPageSettings'));
    }

    public function ItapPageSeo()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-seo.php';
        $ItapPageSeo = new ItapPageSeo();
    }

    public function ItapPageAutomation()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-automation.php';
        $ItapPageAutomation = new ItapPageAutomation();
        $ItapPageAutomation->itap_partials_automation();

    }


    public function ItapPageArchive()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-itap-page-archive.php';
        $ItapPageArchive = new ItapPageArchive();
    }

    public function ItapPageSeoQuantum()
    {
        $ItapPageSeoQuantum = new ItapPageSeoQuantum();
        $ItapPageSeoQuantum->itap_seo_quantum_displayTab();
    }

    public function ItapPageSettings()
    {
        $ItapPageSettings = new ItapPageSettings();
        $ItapPageSettings->itap_settings_displayTab();
    }

    public function itap_no_schema_product(array $results)
    {
        $errors = array();
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);

            if (!get_post_meta($product->get_id(), 'rank_math_schema_WooCommerceProduct', true)) {
                $error = $this->itap_displayData($result, 'produit qui ne contient pas de schema rank math', '1017');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    public function itap_displayData($result, string $problem, string $codeError)
    {
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

    /**
     * Display the results if they aren't archived
     * @fn type function
     */
    public function itap_getErrors($fn, array $results, string $color = null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_archive';
        // select uniqId from the itap_archive table
        $uniqIds = $wpdb->get_results("SELECT uniqId FROM $table_name ORDER BY id ", ARRAY_A);
        $errors = $this->$fn($results);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                if (!in_array(array('uniqId' => $error['uniqId']), $uniqIds) && $this->lines < 300) {
                    $this->itap_displayTab($error, $color);
                    $this->lines++;
                }
            }
        }
    }

    public function itap_displayTab($error, string $danger = null)
    {
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
            <td><a target="_blank" <?php echo esc_attr($danger ? "style='color:white'" : '') ?>
                   href="<?php echo esc_url($error['url_edit']); ?>">click</a></td>
            <td><?php echo wp_kses($error['error'], $allowed_html); ?></td>
            <td><?php echo esc_html($error['author_name']); ?></td>
            <td><input type="checkbox" class="itap_checkbox archiver" name="archiver"
                       value="<?php echo esc_attr($error['uniqId']); ?>"></td>
        </tr>
        <?php
    }

    public function itap_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_archive';
        // count the number of lines in the itap_archive table
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $admins = get_users(array('role__in' => array('administrator', 'shop_manager')));
        $results = $this->itap_getAllInfosFromProduct();
        if (!empty($_GET['author_name'])) {
            $results = array_filter($results, function ($result) {
                return $result['author_name'] == $_GET['author_name'];
            });
        }
        $total_integration_errors = count($this->itap_getErrorsFromLinks($results)) + count($this->itap_getErrorFromBaliseAlt($results)) + count($this->itap_getErrorFromVariableProducts($results)) + count($this->itap_getErrorsFromImages($results)) + count($this->itap_getErrorsFromRankMath($results)) + count($this->itap_getErrorsFromDescriptions($results)) + count($this->itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur($results)) - $count;
        update_option('total_integration_errors', $total_integration_errors);

        if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem') {
            require_once plugin_dir_path(__FILE__) . 'partials/itap-admin-display.php';
        }
    }

    /**
     * get all the product and return it
     *
     * @return array
     */
    public function itap_getAllInfosFromProduct()
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'id',
            'order' => 'ASC'
        );
        $products = wc_get_products($args);
        $products_array = array();
        foreach ($products as $product) {
            $image_id = get_post_thumbnail_id($product->get_id());
            $author_id = get_post_field('post_author', $product->get_id());

            $args = array(
                'id' => $product->get_id(),
                'title' => $product->get_title(),
                'url' => $product->get_permalink(),
                'author_name' => get_the_author_meta('display_name', $author_id),
                'imageUrl' => wp_get_attachment_url($image_id),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
            );
            array_push($products_array, $args);
        }

        return $products_array;
    }

    public function itap_getErrorsFromLinks(array $results)
    {
        $errors = array();
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            $categories = wp_get_post_terms($result['id'], 'product_cat', array('fields' => 'slugs'));
            $slug = $product->get_slug();

            // check if the slug of the product is in the list of categories
            if (in_array($slug, $categories)) {
                $error = $this->itap_displayData($result, 'le slug d\'un produit ne peut pas être le même qu\'une de ses catégories', '1011');
                array_push($errors, $error);
            }

            $description1 = $product->get_meta('description-1') ?? $product->get_meta('description1') ?? null;
            $description2 = $product->get_meta('description-2') ?? $product->get_meta('description2') ?? null;
            $description3 = $product->get_meta('description-3') ?? $product->get_meta('description3') ?? null;
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

            $h1_description = array_filter($all_description, function ($value) {
                return preg_match('/<h1>/', $value) || preg_match('/<\/h1>/', $value);
            });
            if (count($h1_description) > 0) {
                $error = $this->itap_displayData($result, "Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise h1, il ne peut y en avoir qu'une sur la page produit qui correspond au titre, effacez la", '1018');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    /**
     * if a product hasn't alt text on the image, or alt text is too short, return error
     */
    public function itap_getErrorFromBaliseAlt(array $results): array
    {
        $errors = array();
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

    public function slugify($text)
    {
        // Strip html tags
        $text = strip_tags($text);
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // Transliterate
        setlocale(LC_ALL, 'en_US.utf8');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Trim
        $text = trim($text, '-');
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // Lowercase
        $text = strtolower($text);
        // Check if it is empty
        if (empty($text)) {
            return 'n-a';
        }
        // Return result
        return $text;
    }

    /**
     * itap_getErrorFromVariableProducts
     */
    public function itap_getErrorFromVariableProducts(array $results): array
    {
        $errors = array();
        $couleurs = array('argente', 'beigne', 'blanc', 'bleu', 'bleu-fonce', 'bordeaux', 'gris', 'jaune', 'bronze', 'marron', 'multicolore', 'noir', 'dore', 'orange', 'rose', 'rose-fonce', 'rouge', 'turquoise', 'vert', 'violet');
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            // check the attributes of the product to know if the default variation is first element of each attribute
            $attribute_names = $product->get_attributes();
            $terms = array();
            if ($attribute_names) {
                foreach ($attribute_names as $attribute_name => $attribute) {
                    if ($attribute->is_taxonomy() && $attribute['variation']) {
                        $terms[$attribute_name] = wc_get_product_terms($result['id'], $attribute['name'], array('fields' => 'slugs'))[0] ?? null;
                    }
                }
            }

            if ($product->is_type('variable')) {
                if (!$product->get_default_attributes()) {
                    $error = $this->itap_displayData($result, "Produit variable qui n'a pas de produit par défaut", '1003');
                    array_push($errors, $error);
                }
                $attribute_variation = array();

                foreach ($product->get_attributes() as $attribute) {
                    if ($attribute['variation']) {
                        array_push($attribute_variation, $attribute);
                    }
                }
                // check if the terms of attribute 'pa_couleur' or 'couleur are in the list of colors
                foreach ($attribute_variation as $attribute) {
                    $product_tag = wc_get_attribute($attribute['id']);
                    $tag_name = $product_tag->name;
                    if (isset($tag_name) && strtolower($tag_name) === 'couleur') {
                        $terms = wc_get_product_terms($result['id'], $attribute['name'], array('fields' => 'slugs'));
                        foreach ($terms as $term) {
                            $term_slugify = $this->slugify($term);
                            if (!in_array($term_slugify, $couleurs) && $attribute['variation']) {
                                $error = $this->itap_displayData($result, 'Produit variable dont la couleur ' . $term_slugify . ' ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode(', ', $couleurs) . '</span></div>', '1004');
                                array_push($errors, $error);
                            }
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

    // check if the description of the product is less than 200 words

    /**
     *  try ton know if WordPress create responsive images for the product
     */
    public function itap_getErrorsFromImages(array $results): array
    {
        $errors = array();
        foreach ($results as $result) {
            $image_id = get_post_thumbnail_id($result['id']);
            $errors_image = 0;
            if ($image_id == 0) {
                $error = $this->itap_displayData($result, 'Produit sans images', '1008');
                array_push($errors, $error);
            } else {
                $image_metadata = get_post_meta($image_id, '_wp_attachment_metadata', 'true');
                if (!$image_metadata) {
                    continue;
                }
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

    /**
     * get errors from rank math product description who hasn't been filled
     */
    public function itap_getErrorsFromRankMath(array $results): array
    {
        $errors = array();
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            if (!get_post_meta($product->get_id(), 'rank_math_description', true)) {
                $error = $this->itap_displayData($result, 'Produit qui a une meta description automatique, personnalisez la', '1010');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    // display all the result on is there a problem page
    public function itap_getErrorsFromDescriptions(array $results)
    {
        $errors = array();
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            $settings = get_option('itap_settings');
            if ($settings) {
                $possible_desc = array(
                    $short_description = $settings['short_desc'] ? $product->get_short_description() : null,
                    $description1 = $settings['desc1'] ? $product->get_meta('description-1') : null,
                    $description2 = $settings['desc2'] ? $product->get_meta('description-2') : null,
                    $description3 = $settings['desc3'] ? $product->get_meta('description-3') : null,
                    $desc_seo = $settings['desc_seo'] ? get_post_field('description-seo', $product->get_meta('description-categorie')) : null,
                    // get content of a post with the id of the post
                );
                $custom_field = $settings['custom_field'] ? array(
                    $custom_field1 = $settings['custom_field_input_1'] ? $product->get_meta($settings['custom_field_input_1']) : null,
                    $custom_field2 = $settings['custom_field_input_2'] ? $product->get_meta($settings['custom_field_input_2']) : null,
                    $custom_field3 = $settings['custom_field_input_3'] ? $product->get_meta($settings['custom_field_input_3']) : null,
                ) : array();

                $possible_desc = array_merge($possible_desc, $custom_field);
                $possible_desc = array_filter($possible_desc);

                $total_words_min_page = $settings['total_words_min_page'] ?? 200;
                $total_words_min_block = $settings['total_words_min_block'] ?? 60;
                $total_count = 0;
                $error_check = false;
                foreach ($possible_desc as $field) {
                    $total_words = str_word_count(strip_tags($field));
                    if ($total_words < $total_words_min_block && $error_check == false) {
                        $error = $this->itap_displayData($result, 'Chaque champ d\'une page produit dont le nom est coché dans les paramètres du plugin doit avoir plus de ' . $total_words_min_block . ' mots, rajoutez en plus', '1015');
                        array_push($errors, $error);
                        $error_check = true;
                    }
                    $total_count += $total_words;
                }

                if ($total_count < $total_words_min_page) {
                    $error = $this->itap_displayData($result, 'La page du produit contient moins de ' . $total_words_min_page . ' mots, le compte est calculé grâce à la somme de tous les champs cochés dans les paramètres', '1016');
                    array_push($errors, $error);
                }
            } else {
                $description1 = $product->get_meta('description-1') ?? null;
                $description2 = $product->get_meta('description-2') ?? null;
                $description3 = $product->get_meta('description-3') ?? null;
                $short_description = $product->get_short_description();
                if (str_word_count($description1) + str_word_count($description2) + str_word_count($short_description) < 200) {
                    $error = $this->itap_displayData($result, 'Description-1 + description-2 + description courte du produit inférieures à 200 mots, mettez plus de contenu', '1013');
                    array_push($errors, $error);
                }
            }
        }
        return $errors;
    }

    /*
     * method to report an error if a product has a variation with only one color
     */
    public function itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur(array $results): array
    {
        $errors = array();
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            $product_attributes = $product->get_attributes();
            foreach ($product_attributes as $product_attribute) {
                $product_tag = wc_get_attribute($product_attribute['id']);
                if (isset($product_tag->name) && strtolower($product_tag->name) === 'couleur' && count($product_attribute['options']) == 1 && $product_attribute['variation'] && $product->get_type() === 'variable') {
                    $error = $this->itap_displayData($result, 'Le produit a une seule couleur,pas besoin de variations, décocher la case "utiliser pour les variations" pour la couleur', '1020');
                    array_push($errors, $error);
                }
            }
        }
        return $errors;
    }
}
