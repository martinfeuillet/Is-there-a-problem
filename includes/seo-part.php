<?php
// function that get all catégories of products

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
    <tr>
        <td><?php echo esc_html($category['term_id']) ?></td>
        <td><?php echo esc_html($category['name']) ?></td>
        <td><a target="_blank" href="<?php echo esc_url(site_url() . '/wp-admin/term.php?taxonomy=' . $category['taxonomie'] . '&tag_ID=' . $category['term_id'] . '&post_type=product') ?>">click</a></td>
        <td><?php echo wp_kses(($category['error']), $allowed_html) ?></td>
    </tr>
<?php
}

function itap_seoDisplayData($result, string $problem, string $taxonomy = 'product_cat') {
    return array(
        'term_id' => $result['term_id'],
        'name' => $result['name'],
        'error' => $problem,
        'taxonomie' => $taxonomy
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
            $error = itap_seoDisplayData(json_decode(json_encode($category), true), 'Pas de description pour cette catégorie');
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
            $error = itap_seoDisplayData(json_decode(json_encode($tag), true), 'Pas de description pour cette étiquette', 'product_tag');
            array_push($errors, $error);
        }
    }
    if (!in_array('couleur', $attributes) && !in_array('pa_couleur', $attributes)) {
        $error = itap_seoDisplayData(['term_id' => 'global', 'name' => 'global', 'error' => 'global'], 'pas d\'attribut couleur sur le site');
        array_push($errors, $error);
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
            $categoryTab = array_filter(explode('/', $categoryPath));
            $content = $category['meta']['below_category_content'][0];
            preg_match_all('/<a href="(.*?)">(.*?)<\/a>/', $content, $matches);
            if (empty($content)) {
                $error = itap_seoDisplayData($category, 'Catégorie qui n\'as de contenu dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                array_push($errors, $error);
            }
            if (!empty($content) && count($matches[0]) == 0) {
                $error = itap_seoDisplayData($category, 'Catégorie qui ne contient pas de liens dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                array_push($errors, $error);
            }
            // check if content have less than 800 words
            if (str_word_count($content) < 800) {
                $error = itap_seoDisplayData($category, 'Catégorie qui contient moins de 800 mots dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                array_push($errors, $error);
            }
            //check if links in description below category are directed to equal or child category
            if (!empty($content) && count($matches[0]) > 0) {
                foreach ($matches[0] as $match) {
                    $link = $matches[1][0];
                    $path = substr(parse_url($link, PHP_URL_PATH), 1);
                    $pathtab = explode('/', $path);
                    $pathtab = array_filter($pathtab);
                    $linkCategory = get_term_by('slug', end($pathtab), 'product_cat');
                    $termActualCategory = get_term_by('slug', end($categoryTab), 'product_cat');
                    $sameSite = parse_url($link, PHP_URL_HOST) == parse_url(site_url(), PHP_URL_HOST);
                    // check if link is not external
                    if (!$sameSite) {
                        $error = itap_seoDisplayData($category, 'Lien externe dans le meta-field "<i>Texte dessous catégorie de produits</i>"');
                        array_push($errors, $error);
                    }
                    if ($sameSite) {
                        if (!$linkCategory) {
                            $error = itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers une catégorie inexistante');
                            array_push($errors, $error);
                        }
                        if (count($categoryTab) == count($pathtab) && $termActualCategory->parent != $linkCategory->parent) {
                            $error = itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers une catégorie latérale dont le parent n\'est pas le même ');
                            array_push($errors, $error);
                        }
                        if (count($categoryTab) < count($pathtab) && $category['term_id'] != $linkCategory->parent) {
                            $error = itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien vers une autre catégorie qui n\'est pas son enfant direct ');
                            array_push($errors, $error);
                        }
                        if (count($categoryTab) > count($pathtab) && $termActualCategory->parent != $linkCategory->term_id) {
                            $error = itap_seoDisplayData($category, 'description sous catégorie produit qui contient un lien qui n\'est pas son parent direct');
                            array_push($errors, $error);
                        }
                        if (count($categoryTab) == count($pathtab) && $linkCategory->term_id == $category['term_id']) {
                            $error = itap_seoDisplayData($category, 'Lien dans le meta-field "<i>Texte dessous catégorie de produits</i>" pointant vers la catégorie actuelle');
                            array_push($errors, $error);
                        }
                    }
                }
            }
        }
    }
    return $errors;
}

if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem_seo') :
?>
    <div class="wrap is-there-a-problem-container">
        <p>Problèmes liés au référencement naturel</p>
        <p class="problems_number">nombre de problèmes : <?php echo count(itap_get_errors_no_categories_description()) + count(itap_get_errors_no_tags_description()) + count(itap_get_errors_below_category_content()) ?></p>

        <table class="table-plugin">
            <thead>
                <tr class="thead-plugin">
                    <th>Id </th>
                    <th>Nom</th>
                    <th>Url</th>
                    <th>Problème remonté</th>
                </tr>
            </thead>
            <tbody class="tbody-plugin">
                <?php
                $noDescription = itap_get_errors_no_categories_description();
                if (count($noDescription) > 0) {
                    foreach ($noDescription as $category) {
                        itap_seoDisplayTab($category);
                    }
                }
                $noBelowContent = itap_get_errors_below_category_content();
                if (count($noBelowContent) > 0) {
                    foreach ($noBelowContent as $category) {
                        itap_seoDisplayTab($category);
                    }
                }
                $noAttributesDescription = itap_get_errors_no_tags_description();
                if (count($noAttributesDescription) > 0) {
                    foreach ($noAttributesDescription as $category) {
                        itap_seoDisplayTab($category);
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
endif;
?>