<?php
/*
  Plugin Name: Is there a problem
  Description: tell you if there are integration's problem with your website
  Version: 1.0.8
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
    function __construct() {
        add_action('admin_menu', array($this, 'itap_add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'itap_enqueue'));
    }

    function itap_enqueue() {
        wp_enqueue_style('isthereaproblem', plugins_url('admin/isthereaproblem.css', __FILE__));
        // wp_enqueue_script('isthereaproblem', plugins_url('isthereaproblem.js', __FILE__), array('jquery'));
    }

    function itap_add_menu() {
        $results = $this->itap_getAllInfosFromProduct();
        $errorsAlt = $this->itap_getErrorFromBaliseAlt($results);
        $errorsVariable = $this->itap_getErrorFromVariableProducts($results);
        $errorsImage = $this->itap_getErrorsFromImages($results);
        $errorsRankMath = $this->itap_getErrorsFromRankMath($results);
        $errorsLink = $this->itap_getErrorsFromLinks($results);
        $notification_count = count($errorsAlt) + count($errorsVariable) + count($errorsImage) + count($errorsRankMath) + count($errorsLink);
        add_menu_page('Problems', $notification_count ? sprintf("Problems <span class='awaiting-mod'>%d</span>", $notification_count) : 'Problems', 'manage_options', 'is_there_a_problem', array($this, 'page'), 'dashicons-admin-site', 100);
        add_submenu_page('is_there_a_problem', 'Integration', 'Integration', 'manage_options', 'is_there_a_problem', array($this, 'page'));
        add_submenu_page('is_there_a_problem', 'SEO ', 'SEO', 'manage_options', 'is_there_a_problem_seo', function () {
            include "includes/seo-part.php";
        });
    }

    function itap_displayData($result, string $problem) {
        return array(
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
        );
?>
        <tr <?php echo esc_attr($danger ? "style=background-color:$danger; color:white;" : '') ?>>
            <td><?php echo esc_html($error['id']) ?></td>
            <td><?php echo esc_html($error['title']) ?></td>
            <td><a target="_blank" <?php echo esc_attr($danger ? "style='color:white'" : '') ?> href="<?php echo esc_url($error['url_edit']) ?>">click</a></td>
            <td><?php echo wp_kses($error['error'], $allowed_html) ?></td>
            <td><?php echo esc_html($error['author_name']) ?></td>
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
                $error = $this->itap_displayData($result, 'Balise alt vide');
                array_push($errors, $error);
            } elseif (strlen($result['alt']) < 10) {
                $error = $this->itap_displayData($result, 'Balise alt trop courte');
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
            $color_attributes = explode(', ', strtolower($product->get_attribute('couleur')));
            // check the attributes of the product to know if the default variation is first element of each attribute
            $attribute_names = $product->get_attributes();

            $terms = [];
            foreach ($attribute_names as $attribute_name => $attribute) {
                if ($attribute->is_taxonomy() && $attribute['variation']) {
                    $terms[$attribute_name] = wc_get_product_terms($result['id'], $attribute['name'], array('fields' => 'slugs'))[0];
                }
            }

            // check if the product has variations
            if ($product->is_type('variable')) {
                if (!$product->default_attributes) {
                    $error = $this->itap_displayData($result, 'Produit variable qui n\'a pas de produit par défaut');
                    array_push($errors, $error);
                }
                $attribute_variation = [];
                foreach ($product->attributes as $attribute) {
                    if ($attribute['variation']) {
                        array_push($attribute_variation, $attribute);
                    }
                }
                // check if the terms of attribute 'pa_couleur' are in the list of colors
                foreach ($attribute_variation as $attribute) {
                    $attribute_name = $attribute['name'];
                    $attribute_value = $terms[$attribute_name];
                    if (!in_array($attribute_value, $couleurs) && ($attribute_name == 'pa_couleur' || $attribute_name == 'couleur') && $attribute['variation']) {
                        $error = $this->itap_displayData($result, 'Produit variable dont la couleur définie ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode(", ", $couleurs) . '</span></div>');
                        array_push($errors, $error);
                    }
                }
                if (count($attribute_variation) != count($product->default_attributes)) {
                    $error = $this->itap_displayData($result, 'Produit variable ou il manque une ou plusieurs variations dans le produit par défaut');
                    array_push($errors, $error);
                }
                if (array_diff($product->default_attributes, $terms)) {
                    $error = $this->itap_displayData($result, 'Produit variable dont le produit par défaut à comme variation des valeurs qui ne sont pas les premieres de leurs <div class="tooltip">catégories<span class="tooltiptext">Erreur qui signale également les attributs remplis à la volée directement sur la page du produit, merci de rentrer tous les attributs et leurs termes dans l\'onglet attribut de produit</span></div>. ');
                    array_push($errors, $error);
                }
                if (count($product->get_children()) == 0) {
                    $error = $this->itap_displayData($result, 'Produit variable qui n\'a pas de variations, ajoutez en ou passez le en produit simple');
                    array_push($errors, $error);
                }
                // if (array_intersect($color_attributes, $couleurs) != $color_attributes) {
                //     $error = $this->itap_displayData($result, 'Produit variable dont la couleur définie ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode(", ", $couleurs) . '</span></div>');
                //     array_push($errors, $error);
                // }
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
                $error = $this->itap_displayData($result, 'Produit sans images');
                array_push($errors, $error);
            } else {
                $image_metadata = get_post_meta($image_id, '_wp_attachment_metadata', true);
                $upload_dir = wp_upload_dir()['basedir'];
                $base_path = substr($image_metadata['file'], 0, 8);
                $image_path = $upload_dir . '/' . $base_path;
                foreach ($image_metadata['sizes'] as $size) {
                    if (!file_exists($image_path . $size['file'])) {
                        $errors_image++;
                    }
                }
                if ($errors_image > 0) {
                    $error = $this->itap_displayData($result, 'Formats d\'images manquants/non créés par WordPress pour WooCommerce, merci de réuploader l\'image du produit');
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
                $error = $this->itap_displayData($result, 'Produit associé à Rank Math qui n\'a pas de meta description');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_getErrorsFromLinks($results) {
        $errors = [];
        foreach ($results as $result) {
            $product = wc_get_product($result['id']);
            //trouver tous les liens dans la description produit
            preg_match_all('/<a href="(.*?)">(.*?)<\/a>/', $product->get_description(), $matches);
            $check = 0;
            foreach ($matches[1] as $match) {
                if ($match != null) {
                    $check++;
                }
            }
            preg_match_all('/<a href="(.*?)">(.*?)<\/a>/', $product->get_short_description(), $matches);
            foreach ($matches[1] as $match) {
                if ($match != null) {
                    $check++;
                }
            }
            if ($check != 0) {
                $error = $this->itap_displayData($result, 'Description du produit qui contient un lien');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_getErrors($fn, $results, $color = null) {
        $errors = $this->$fn($results);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->itap_displayTab($error, $color);
            }
        }
    }
    // '#DC3444'
    function page() {
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
                                <option value="">Erreurs générales</option>
                                <?php
                                $users = get_users();
                                foreach ($users as $user) {
                                ?>
                                    <option value="<?php echo esc_attr($user->display_name); ?>" name=""><?php echo esc_attr($user->display_name); ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            <button type="submit" value="Rechercher">Rechercher</button>
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
                    // echo errors from all rules
                    $this->itap_getErrors('itap_getErrorsFromLinks', $results, '#DC3444');
                    $this->itap_getErrors('itap_getErrorFromBaliseAlt', $results);
                    $this->itap_getErrors('itap_getErrorFromVariableProducts', $results);
                    $this->itap_getErrors('itap_getErrorsFromImages', $results);
                    $this->itap_getErrors('itap_getErrorsFromRankMath', $results);
                    if (count($this->itap_getErrorsFromLinks($results)) == 0 && count($this->itap_getErrorFromBaliseAlt($results)) == 0 && count($this->itap_getErrorFromVariableProducts($results)) == 0 && count($this->itap_getErrorsFromImages($results)) == 0 && count($this->itap_getErrorsFromRankMath($results)) == 0) {
                        echo esc_html("<tr><td colspan='5' class='congrats-plugin'>Aucune erreur détéctée , félicitations</td></tr>");
                    }
                    ?>

                </tbody>
            </table>
        </div>
<?php
    }
}

$isThereAProblem = new itap_IsThereAProblem();

require 'plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/martinfeuillet/Is-there-a-problem',
    __FILE__, //Full path to the main plugin file or functions.php.
    'is_there_a_problem'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');
