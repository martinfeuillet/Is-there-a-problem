<?php
/*
  Plugin Name: Is there a problem
  Description: tell you if there are integration's problem with your website
  Version: 1.1.6
  author URI: https://ingenius.agency/
  Text Domain: is-there-a-problem
  Author: MartinDev
  License: GPL v2 or later

*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//check if woocommerce and rankmath are installed
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function () {
        //deactivate plugin
        deactivate_plugins(plugin_basename(__FILE__));
    });
    exit('woocommerce is not installed, please install it and activate it first');
}

class itap_IsThereAProblem {
    public $lines = 0;

    public $globalErrors = 0;

    function __construct() {
        add_action('admin_menu', array($this, 'itap_add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'itap_enqueue'));
        // ajax call
        add_action("wp_ajax_get_checkbox_value", array($this, "itap_send_archive_to_db"));
        add_action("wp_ajax_delete_checkbox_value", array($this, "itap_delete_archive"));
    }

    function itap_enqueue() {
        wp_enqueue_style('isthereaproblem', plugins_url('admin/isthereaproblem.css', __FILE__));
        wp_enqueue_script('isthereaproblemJS', plugins_url('admin/isThereAProblem.js', __FILE__), array('jquery'), false, true);
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
        if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem') {
            $results = $this->itap_getAllInfosFromProduct();
            $errorsAlt = $this->itap_getErrorFromBaliseAlt($results);
            $errorsVariable = $this->itap_getErrorFromVariableProducts($results);
            $errorsImage = $this->itap_getErrorsFromImages($results);
            $errorsRankMath = $this->itap_getErrorsFromRankMath($results);
            $errorsLink = $this->itap_getErrorsFromLinks($results);
            $errorsDescriptions = $this->itap_getErrorsFromDescriptions($results);
            $countErrors = count($errorsAlt) + count($errorsVariable) + count($errorsImage) + count($errorsRankMath) + count($errorsLink) + count($errorsDescriptions) - $archives;
        }
        $notification_count = $countErrors ?? null;
        add_menu_page('Problems', $notification_count ? sprintf("Problems <span class='awaiting-mod'>%d</span>", $notification_count) : 'Problems', 'manage_options', 'is_there_a_problem', array($this, 'itap_page'), 'dashicons-admin-site', 100);
        add_submenu_page('is_there_a_problem', 'Integration', 'Integration', 'manage_options', 'is_there_a_problem', array($this, 'itap_page'));
        add_submenu_page('is_there_a_problem', 'SEO ', 'SEO', 'manage_options', 'is_there_a_problem_seo', function () {
            include "includes/seo-part.php";
        });
        add_submenu_page('is_there_a_problem', 'Archive ', 'Archive', 'manage_options', 'is_there_a_problem_archive', function () {
            include "includes/archive-part.php";
        });
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
                if (!$product->default_attributes) {
                    $error = $this->itap_displayData($result, "Produit variable qui n'a pas de produit par défaut", '1003');
                    array_push($errors, $error);
                }
                $attribute_variation = [];
                foreach ($product->attributes as $attribute) {
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
                if (count($attribute_variation) != count($product->default_attributes)) {
                    $error = $this->itap_displayData($result, 'Produit variable ou il manque une ou plusieurs variations dans le produit par défaut', '1005');
                    array_push($errors, $error);
                }
                if (array_diff($product->default_attributes, $terms)) {
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
            $main_description = $product->get_description();
            $short_description = $product->get_short_description();
            $all_description = array($description1, $description2, $main_description, $short_description);
            $link_description = array_filter($all_description, function ($value) {
                preg_match_all('/<a href="(.*?)">(.*?)<\/a>/', $value, $matches);
                return count($matches[1]) > 0;
            });

            if (count($link_description) > 0) {
                $error = $this->itap_displayData($result, 'Description-1 ou description-2 ou description principale ou description courte du produit qui contient un lien', '1012');
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
            $short_description = $product->get_short_description();
            if (str_word_count($description1) + str_word_count($description2) + str_word_count($short_description) < 200) {
                $error = $this->itap_displayData($result, 'Description-1 + description-2 + description courte du produit inférieures à 200 mots, mettez plus de contenu', '1013');
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
        if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem') {
        ?>
            <div class="wrap is-there-a-problem-container">
                <p>Problèmes d'intégration</p>

                <div class="form-tri">
                    <p>Trier par intégrateur</p>
                    <form action="?page=is_there_a_problem" method="GET">
                        <div class="bloc-form">
                            <div>
                                <input type="hidden" name="page" value="is_there_a_problem">
                                <select name="author_name" id="author_name">
                                    <option value="">choisissez votre nom d'utilisateur</option>
                                    <?php
                                    //get all admin
                                    $admins = get_users(array('role' => 'administrator'));
                                    foreach ($admins as $user) {
                                    ?>
                                        <option value="<?php echo esc_attr($user->display_name); ?>" name=""><?php echo esc_attr($user->display_name); ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                <button type="submit" class="itap_button" value="Rechercher">Rechercher</button>
                            </div>
                        </div>
                    </form>
                </div>
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

                        // filter data by integrator
                        if (!empty($_GET['author_name'])) {
                            $results = $this->itap_getAllInfosFromProduct();
                            $results = array_filter($results, function ($result) {
                                return $result['author_name'] == $_GET['author_name'];
                            });
                        } else {
                            $results = $this->itap_getAllInfosFromProduct();
                        }

                        $this->itap_getErrors('itap_getErrorsFromLinks', $results, '#DC3444');
                        if ($this->lines < 290) $this->itap_getErrors('itap_getErrorFromBaliseAlt', $results);
                        if ($this->lines < 290) $this->itap_getErrors('itap_getErrorFromVariableProducts', $results);
                        if ($this->lines < 290) $this->itap_getErrors('itap_getErrorsFromImages', $results);
                        if ($this->lines < 290) $this->itap_getErrors('itap_getErrorsFromRankMath', $results);
                        if ($this->lines < 290) $this->itap_getErrors('itap_getErrorsFromDescriptions', $results);

                        if (count($this->itap_getErrorsFromLinks($results)) + count($this->itap_getErrorFromBaliseAlt($results)) + count($this->itap_getErrorFromVariableProducts($results)) + count($this->itap_getErrorsFromImages($results)) + count($this->itap_getErrorsFromRankMath($results)) + count($this->itap_getErrorsFromDescriptions($results)) == 0) {
                            echo wp_kses("<tr><td colspan='5' class='congrats-plugin'>Aucune erreur détéctée , félicitations</td></tr>", array('td' => array('colspan' => array()), 'tr' => array('class' => array())));
                        }
                        ?>

                    </tbody>
                </table>
            </div>
<?php
        }
    }
}

if (is_admin()) {
    $isThereAProblem = new itap_IsThereAProblem();
}

require 'plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/martinfeuillet/Is-there-a-problem',
    __FILE__, //Full path to the main plugin file or functions.php.
    'is_there_a_problem'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');
