<?php

use RankMath\Paper\Blog;

class ItapPageSeo {

    function __construct() {
        $this->itap_partials_seo();
    }

    function itap_seoDisplayTab($category) {
        $allowed_html = array(
            'div' => array(
                'class' => array()
            ),
            'span' => array(
                'class' => array()
            )
        );
?>
        <tr style="background-color:<?php echo esc_html($category['color']) ?>; <?php echo esc_html($category['color']) == 'red' ? 'color : white' : '' ?>">
            <td><?php echo esc_html($category['term_id']) ?></td>
            <td><?php echo esc_html($category['name']) ?></td>
            <td><a target="_blank" href="<?php echo esc_url(site_url() . '/wp-admin/term.php?taxonomy=' . $category['taxonomie'] . '&tag_ID=' . $category['term_id'] . '&post_type=product') ?>">click</a></td>
            <td><?php echo wp_kses(($category['error']), $allowed_html) ?></td>
        </tr>
    <?php
    }

    function itap_seoDisplayTabLinks($category) {
        $allowed_html = array(
            'div' => array(
                'class' => array()
            ),
            'span' => array(
                'class' => array()
            )
        );
    ?>
        <tr style="background-color: <?php echo esc_html($category['color']) ?>;">
            <td><?php echo esc_html($category['term_id']) ?></td>
            <td><?php echo esc_html($category['name']) ?></td>
            <td><a target="_blank" href="<?php echo esc_url(site_url() . '/wp-admin/nav-menus.php?action=edit&menu=' . $category['term_id'] . '') ?>">click</a></td>
            <td><?php echo wp_kses(($category['error']), $allowed_html) ?></td>
        </tr>
<?php
    }

    function itap_seoDisplayData($result, string $problem, string $taxonomy = 'product_cat', $color = 'white') {
        return array(
            'term_id' => $result['term_id'],
            'name' => $result['name'],
            'error' => $problem,
            'taxonomie' => $taxonomy,
            'color' => $color
        );
    }

    function itap_get_errors_no_categories_description() {
        $errors = array();
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        );
        $categories = get_terms($args);
        foreach ($categories as $category) {
            if (empty($category->description) && $category->name != 'Uncategorized') {
                $error = $this->itap_seoDisplayData(json_decode(json_encode($category), true), 'Pas de description pour cette catégorie');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_get_errors_from_meta_title() {
        $errors = array();
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        );
        $categories = get_terms($args);


        foreach ($categories as $category) {
            $meta_title = get_term_meta($category->term_id, 'rank_math_title', true);

            if (preg_match('/archive/i', $meta_title) && $category->name != 'Uncategorized') {
                $error = $this->itap_seoDisplayData(json_decode(json_encode($category), true), 'Le mot Archive est présent dans le meta titre de la page de la catégorie, supprimer le', '', 'red');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_get_errors_no_tags_description() {
        $errors = array();
        $attributes = wc_get_attribute_taxonomy_names();
        //get all tags
        $tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
        ));

        foreach ($tags as $tag) {
            if (empty($tag->description) && $tag->name != 'Uncategorized') {
                $error = $this->itap_seoDisplayData(json_decode(json_encode($tag), true), 'Pas de description pour cette étiquette', 'product_tag');
                array_push($errors, $error);
            }
        }
        return $errors;
    }

    function itap_get_errors_below_category_content() {
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'fields' => 'ids',
        );
        $categories = get_terms($args);
        $belowContent = array();
        //construction of an array who got meta value, id and name of category
        foreach ($categories as $category) {
            $args = array(
                'meta' => get_term_meta($category),
                'term_id' => $category,
                'name' => get_term($category)->name
            );
            $belowContent[] = $args;
        }
        $errors = array();
        //check the meta value of below content
        foreach ($belowContent as $category) {
            if ($category['name'] != 'Uncategorized') {
                $categoryLink = get_term_link($category['term_id']);
                $categoryPath = substr(parse_url($categoryLink, PHP_URL_PATH), 1);
                $categoryTab = array_filter(explode('/', $categoryPath)); // actual category
                $content = $category['meta']['below_category_content'][0];
                preg_match_all('/<a href="(.*?)">(.*?)<\/a>/', $content, $matches);
                if (empty($content) && count($matches[1]) == 0) {
                    $error = $this->itap_seoDisplayData($category, 'Catégorie qui n\'as de contenu et de liens dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                    array_push($errors, $error);
                }
                if (!empty($content) && count($matches[1]) == 0) {
                    $error = $this->itap_seoDisplayData($category, 'Catégorie qui ne contient pas de liens dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                    array_push($errors, $error);
                }
                // check if content have less than 800 words
                if (str_word_count($content) < 800) {
                    $error = $this->itap_seoDisplayData($category, 'Catégorie qui contient moins de 800 mots dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                    array_push($errors, $error);
                }
                //check if links in description below category are directed to equal or child category
                if (!empty($content) && count($matches[0]) > 0) {
                    foreach ($matches[0] as $match) {
                        $link = $matches[1][0];
                        $path = substr(parse_url($link, PHP_URL_PATH), 1);
                        $pathtab = array_filter(explode('/', $path)); //Array([0] => produit,[1] => shirt,[2] => shirt-rouge,[3] => shirt-rouge-bariole)
                        $last_element_in_url = end($pathtab);
                        $if_product = get_page_by_path($last_element_in_url, OBJECT, 'product');
                        $if_category = get_term_by('slug', $last_element_in_url, 'product_cat');
                        $termActualCategory = get_term_by('slug', end($categoryTab), 'product_cat');  // term_id , name, slug,taxonomy(=product_cat) of the actual category
                        $sameSite = parse_url($link, PHP_URL_HOST) == parse_url(site_url(), PHP_URL_HOST);
                        // check if link is not external
                        if (!$sameSite) {
                            $error = $this->itap_seoDisplayData($category, 'Lien externe dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                            array_push($errors, $error);
                        }
                        if ($sameSite) {
                            // check if link is on a product
                            if ($if_product) {
                                if (count($categoryTab) < count($pathtab) && !in_array($termActualCategory->slug, $pathtab)) {
                                    $error = $this->itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers un produit qui n\'est pas dans la même catégorie ou dans une catégorie enfante de la catégorie actuelle ');
                                    array_push($errors, $error);
                                }
                                if (count($categoryTab) == count($pathtab)) {
                                    $error = $this->itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers un produit qui n\'est pas dans la catégorie actuelle ');
                                    array_push($errors, $error);
                                }
                            }
                            // check if link is on a category
                            if ($if_category) {
                                if (count($categoryTab) == count($pathtab) && $termActualCategory->parent != $if_category->parent) {
                                    $error = $this->itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers une catégorie latérale dont le parent n\'est pas le même ');
                                    array_push($errors, $error);
                                }
                                if (count($categoryTab) < count($pathtab) && !in_array($termActualCategory->slug, $pathtab)) {
                                    $error = $this->itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers une autre catégorie qui n\'est pas son enfant');
                                    array_push($errors, $error);
                                }
                                if (count($categoryTab) > count($pathtab) && $termActualCategory->parent != $if_category->term_id) {
                                    $error = $this->itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien qui n\'est pas son parent direct');
                                    array_push($errors, $error);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * search for nofollow attributes on particular menu links
     *
     * @return array
     */
    function itap_get_errors_nofollow_link() {
        // search all link in the front page and check if they have a nofollow attribute
        $errors = array();
        $specials_links_slugs = array('conditions-generales-de-vente', 'mentions-legales', 'politique-de-confidentialite', 'politique-de-livraison', 'avis-clients', 'mon-compte');
        $menu = wp_get_nav_menus(); // term_id, name, slug of the menu
        foreach ($menu as $key) {
            $menus_id[] = $key->term_id;
        }

        $categories = get_terms('product_cat', array('hide_empty' => false)); // term_id, name, description, parent of the category
        // rank math noindex

        // get all slugs of links in menus
        $slugs = array();
        foreach ($menus_id as $id) {
            $menu = wp_get_nav_menu_items($id);
            foreach ($menu as $item) {
                $slug = explode('/', $item->url);
                $slug = end($slug);
                $slugs[] = $slug;
                if ($item->xfn !== 'nofollow' && $slug != 'Uncategorized') {
                    $category = array('term_id' => $id, 'name' => $item->title);
                    if (in_array($slug, $specials_links_slugs)) {
                        $error = $this->itap_seoDisplayData($category, $slug . ' doit avoir un attribut nofollow. quand vous êtes sur la page menu, allez sur options d\'ecran en haut à droite, cocher xfn, puis inscrivez "nofollow" sur le champ xfn du lien du menu ' . $slug . '');
                        array_push($errors, $error);
                    }
                }
            }
        }
        $slugs = array_unique($slugs);

        // filter categories array and keep only categories that aren't slugs array
        $categories = array_filter($categories, function ($category) use ($slugs) {
            return !in_array($category->slug, $slugs);
        });

        foreach ($categories as $category) {
            // if category don't have noindex
            $noindex = get_term_meta($category->term_id, 'rank_math_robots', true);


            if ($category->name != 'Uncategorized' && $noindex[0] != 'noindex') {
                $data = array('term_id' => $menus_id[0], 'name' => $category->name);
                $error = $this->itap_seoDisplayData($data, 'La catégorie ' . $category->slug . ' n\'est pas présente dans le menu principal', '', 'orange');
                array_push($errors, $error);
            }
        }

        return $errors;
    }

    function itap_get_rank_math_opengraph_thumbnail() {
        $errors = array();
        $html = file_get_contents(site_url());
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $metas = $xpath->query('//meta[@property="og:image"]');
        $ogImage = array();
        foreach ($metas as $meta) {
            $ogImage[] = $meta->getAttribute('content');
        }
        if (!$ogImage[0]) {
            $data = array('term_id' => 0, 'name' => bloginfo('name'));
            $error = $this->itap_seoDisplayData($data, 'le site actuel ne contient pas d\'image opengraph dans rank math section "titre et meta"', '', 'red');
            array_push($errors, $error);
        }
        return $errors;
    }

    function get_errors_from_seo($fn_errors, $fn_display) {
        $errors = $fn_errors();
        if (is_array($errors) && count($errors) > 0) {
            foreach ($errors as $error) {
                $fn_display($error);
            }
        }
    }

    function itap_partials_seo() {
        if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem_seo') {
            $total_problems = count($this->itap_get_errors_no_categories_description()) + count($this->itap_get_errors_no_tags_description()) + count($this->itap_get_errors_below_category_content()) + count($this->itap_get_errors_nofollow_link());
            update_option('count_seo_errors', $total_problems);
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/itap-seo-display.php';
        }
    }
}
