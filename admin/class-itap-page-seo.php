<?php

class ItapPageSeo
{
    public function __construct() {
        $this->itap_partials_seo();
    }

    /**
     * display html for seo page and count errors
     */
    public function itap_partials_seo() : void {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'is_there_a_problem_seo' ) {
            $total_problems = count( $this->itap_get_rank_math_opengraph_thumbnail() ) + count( $this->itap_get_errors_from_meta_title() ) + count( $this->itap_no_category_or_attribute_with_numbers_in_slug() ) + count( $this->itap_get_errors_nofollow_link() ) + count( $this->itap_get_errors_from_product_cat() ) + count( $this->itap_get_errors_no_tags_description() ) + count( $this->itap_get_errors_no_tags_description() ) + count( $this->itap_get_errors_from_product_attr() ) + count( $this->itap_get_errors_from_product_tag() );
            update_option( 'count_seo_errors' , $total_problems );
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/itap-seo-display.php';
        }
    }

    /**
     * Error if a category has no description
     * @param object $category that search on it
     * @return array|void
     */
    public function itap_get_errors_no_categories_description( object $category , string $taxonomy = 'product_cat' ) : array {
        $errors = array();
        if ( empty( $category->description ) && $category->name != 'Uncategorized' ) {
            $category_array = (array) $category;
            return $this->itap_seo_display_data( $category_array , 'Pas de description pour cette catégorie' , $taxonomy );
        }
        return $errors;

    }

    /**
     * @param $result array that contain the error
     * @param string $problem error message
     * @param string $taxonomy taxonomy name, generally product_cat or product_tag
     * @param $color string color when displaying the error
     */
    public function itap_seo_display_data( array $result , string $problem , string $taxonomy = 'product_cat' , string $color = 'white' ) : array {
        $problem_slugify = sanitize_title( $problem );
        return array(
            'term_id'   => $result['term_id'] ,
            'name'      => $result['name'] ,
            'error'     => $problem ,
            'taxonomie' => $taxonomy ,
            'color'     => $color ,
            'uniqId'    => $problem_slugify . $result['term_id']
        );
    }

    /**
     * IN
     * Error if a tag has no description
     */
    public function itap_get_errors_no_tags_description() : array {
        $errors = array();
        $tags   = get_terms( array(
            'taxonomy'   => 'product_tag' ,
            'hide_empty' => false ,
        ) );
        foreach ( $tags as $tag ) {
            if ( empty( $tag->description ) && $tag->name != 'Uncategorized' ) {
                $error    = $this->itap_seo_display_data( json_decode( json_encode( $tag ) , true ) , 'Pas de description pour cette étiquette' , 'product_tag' );
                $errors[] = $error;
            }
        }
        return $errors;
    }

    /**
     * IN
     * get different errors for below category content :
     * - no content and no links
     * - not only child links or direct parent links
     * - links on products that are not in the category
     * - not enough words
     *
     * @return array
     */
    public function itap_get_errors_from_product_cat() : array {
        $args          = array(
            'taxonomy'   => 'product_cat' ,
            'hide_empty' => false ,
        );
        $errors        = array();
        $categories    = get_terms( $args );
        $belowContents = array();
        foreach ( $categories as $category ) {
            if ( $this->itap_get_errors_no_categories_description( $category , 'product_cat' ) ) {
                $errors[] = $this->itap_get_errors_no_categories_description( $category );
            }
            $category_meta   = get_metadata( 'term' , $category->term_id );
            $noindex         = isset( $category_meta['rank_math_robots'] ) ? unserialize( $category_meta['rank_math_robots'][0] ) : array();
            $noindex         = ! empty( $noindex ) && in_array( 'noindex' , $noindex ) ? '1' : '0';
            $belowContents[] = array(
                'term_id'                => $category->term_id ,
                'name'                   => $category->name ,
                'slug'                   => $category->slug ,
                'parent'                 => $category->parent ,
                'description-bas'        => get_term_meta( $category->term_id , 'description-bas' , true ) ,
                'below_category_content' => get_term_meta( $category->term_id , 'below_category_content' , true ) ,
                'noindex'                => $noindex ,

            );
        }

        foreach ( $belowContents as $belowContent ) {
            if ( $belowContent['name'] != 'Uncategorized' && $belowContent['noindex'] != '1' ) {
                $content   = $belowContent['below_category_content'] ?: $belowContent['description-bas'];
                $functions = array(
                    'itap_get_errors_from_below_cat_content' ,
                    'no_h2_in_below_content' ,
                    'no_title_since_300_words' ,
                    'miscategorization_of_title' ,
                    'itap_error_if_mailto_href_dont_have_the_same_value_of_a_tag' ,
                    'no_meta_description_in_below_content' ,
                );
                foreach ( $functions as $function ) {
                    foreach ( $this->$function( $content , $belowContent ) as $error ) {
                        if ( ! empty( $error ) ) {
                            $errors[] = $error;
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * IN
     * get different errors for below tag content :
     * - no content and no links
     * - not enough words
     *
     * @return array
     */
    public function itap_get_errors_from_product_tag() {
        if ( ! is_plugin_active( 'ingenius-below-content/ingenius-below-content.php' ) ) {
            return array();
        }

        $args             = array(
            'taxonomy'   => 'product_tag' ,
            'hide_empty' => false ,
        );
        $errors           = array();
        $tags             = get_terms( $args );
        $belowTagContents = array();
        foreach ( $tags as $tag ) {
            if ( $this->itap_get_errors_no_categories_description( $tag , "product_tag" ) ) {
                $errors[] = $this->itap_get_errors_no_categories_description( $tag );
            }
            $tag_meta           = get_metadata( 'term' , $tag->term_id );
            $noindex            = isset( $tag_meta['rank_math_robots'] ) ? unserialize( $tag_meta['rank_math_robots'][0] ) : array();
            $noindex            = ! empty( $noindex ) && in_array( 'noindex' , $noindex ) ? '1' : '0';
            $belowTagContents[] = array(
                'term_id'           => $tag->term_id ,
                'name'              => $tag->name ,
                'slug'              => $tag->slug ,
                'parent'            => $tag->parent ,
                'below_tag_content' => get_term_meta( $tag->term_id , 'below_tag_content' , true ) ,
                'noindex'           => $noindex ,

            );
        }

        foreach ( $belowTagContents as $belowTagContent ) {
            if ( $belowTagContent['name'] !== 'Uncategorized' && $belowTagContent['noindex'] != '1' ) {
                $content   = $belowTagContent['below_tag_content'];
                $functions = array(
                    'itap_get_errors_from_below_cat_content' ,
                    'no_h2_in_below_content' ,
                    'no_title_since_300_words' ,
                    'miscategorization_of_title' ,
                    'itap_error_if_mailto_href_dont_have_the_same_value_of_a_tag' ,
                    'no_meta_description_in_below_content' ,
                );
                foreach ( $functions as $function ) {
                    foreach ( $this->$function( $content , $belowTagContent , "product_tag" ) as $error ) {
                        if ( ! empty( $error ) ) {
                            $errors[] = $error;
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * IN
     * get different errors for below attribute content :
     * - no content and no links
     * - not enough words
     *
     * @return array
     */
    public function itap_get_errors_from_product_attr() {
        if ( ! is_plugin_active( 'ingenius-below-content/ingenius-below-content.php' ) ) {
            return array();
        }
        $errors = array();

        // get all product attributes
        $attributes = wc_get_attribute_taxonomies();
        foreach ( $attributes as $attribute ) {
            // get all terms for each attribute
            $attribute_slug    = wc_attribute_taxonomy_name( $attribute->attribute_name );
            $terms             = get_terms( array(
                'taxonomy'   => $attribute_slug ,
                'hide_empty' => false ,
            ) );
            $belowAttrContents = array();
            foreach ( $terms as $term ) {
                $term_meta           = get_metadata( 'term' , $term->term_id );
                $noindex             = isset( $term_meta['rank_math_robots'] ) ? unserialize( $term_meta['rank_math_robots'][0] ) : array();
                $noindex             = ! empty( $noindex ) && in_array( 'noindex' , $noindex ) ? '1' : '0';
                $belowAttrContents[] = array(
                    'term_id'            => $term->term_id ,
                    'name'               => $term->name ,
                    'slug'               => $term->slug ,
                    'parent'             => $term->parent ,
                    'below_attr_content' => get_term_meta( $term->term_id , 'below_attr_content' , true ) ,
                    'noindex'            => $noindex ,

                );
            }

            foreach ( $belowAttrContents as $belowAttrContent ) {
                if ( $belowAttrContent['name'] !== 'Uncategorized' && $belowAttrContent['noindex'] != '1' ) {
                    $content   = $belowAttrContent['below_attr_content'];
                    $functions = array(
                        'itap_get_errors_from_below_cat_content' ,
                        'no_h2_in_below_content' ,
                        'no_title_since_300_words' ,
                        'miscategorization_of_title' ,
                        'itap_error_if_mailto_href_dont_have_the_same_value_of_a_tag' ,
                        'no_meta_description_in_below_content' ,
                    );
                    foreach ( $functions as $function ) {
                        foreach ( $this->$function( $content , $belowAttrContent , $attribute_slug ) as $error ) {
                            if ( ! empty( $error ) ) {
                                $errors[] = $error;
                            }
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * IN
     * return error if there isn't any meta description in the below content
     * @param $content string that contains the content of the meta field
     * @param $belowContent array that contains the error to display
     */
    public function no_meta_description_in_below_content( string $content , array $belowContent , $taxonomy = "product_cat" ) : array {
        $errors = array();
        if ( $taxonomy !== "product_cat" ) {
            return array();
        }
        $rank_math_meta = get_metadata( 'term' , $belowContent['term_id'] , 'rank_math_description' , true );
        if ( ! empty( $content ) && empty( $rank_math_meta ) ) {
            $errors[] = $this->itap_seo_display_data( $belowContent , "Chaque catégorie doit avoir une meta description, celle ci est vide" );
        }
        return $errors;
    }

    /**
     * IN
     * return error if there isn't any h2 in the below content
     * @param $content string that contains the content of the meta field
     * @param $belowContent array that contains the error to display
     */
    public function no_h2_in_below_content( string $content , array $belowContent , $taxonomy = "product_cat" ) : array {
        if ( $taxonomy !== "product_cat" && empty( $content ) ) {
            return array();
        }
        if ( $taxonomy == "product_cat" ) {
            $metafield = "Texte dessous catégorie de produits";
        } elseif ( $taxonomy == "product_tag" ) {
            $metafield = "below_tag_content";
        } else {
            $metafield = "below_attr_content";
        }
        $errors = array();
        preg_match_all( '/<h2>(.*?)<\/h2>/s' , $content , $matches );
        if ( ! empty( $content ) && count( $matches[1] ) == 0 ) {
            $errors[] = $this->itap_seo_display_data( $belowContent , "le meta-field '<i>$metafield</i>' doit contenir des balises h2 (ou Titre 2), celle ci n'en contient pas" , $taxonomy );
        }
        return $errors;
    }

    /**
     * IN
     * return errors if there isn't any title since 300 words
     * @param $content string that contains the content of the meta field
     * @param $belowContent array that contains the error to display
     */
    public function no_title_since_300_words( string $content , array $belowContent , $taxonomy = 'product_cat' ) : array {
        $errors = array();
        if ( $taxonomy == "product_cat" ) {
            $metafield = "Texte dessous catégorie de produits";
        } elseif ( $taxonomy == "product_tag" ) {
            $metafield = "below_tag_content";
        } else {
            $metafield = "below_attr_content";
        }
        $words  = explode( ' ' , $content );
        $count  = 0;
        $titles = array("<h1>" , "</h1>" , "<h2>" , "</h2>" , "<h3>" , "</h3>" , "<h4>" , "</h4>" , "<h5>" , "</h5>" , "<h6>" , "</h6>");
        foreach ( $words as $word ) {
            $is_word_contain_title = preg_match( '/<h[1-6]>(.*?)<\/h[1-6]>/s' , $word );
            if ( in_array( $word , $titles ) || $is_word_contain_title ) {
                $count = 0;
            } else {
                $count++;
            }
            if ( $count == 300 ) {
                $errors[] = $this->itap_seo_display_data( $belowContent , "le meta-field '<i>$metafield</i>' doit contenir des titres tous les 300 mots, celle ci n'en contient pas" , $taxonomy );
            }
        }
        return $errors;
    }

    /**
     * IN
     * there can't be a h2 if there is no h1, there can't be a h3 if there is no h2, etc...
     * @param $content string that contains the content of the meta field
     * @param $belowContent array that contains the error to display
     */
    public function miscategorization_of_title( string $content , array $belowContent , $taxonomy = 'product_cat' ) : array {
        $valid  = true;
        $errors = array();
        if ( $taxonomy == "product_cat" ) {
            $metafield = "Texte dessous catégorie de produits";
        } elseif ( $taxonomy == "product_tag" ) {
            $metafield = "below_tag_content";
        } else {
            $metafield = "below_attr_content";
        }
        $last_heading = 0;
        $pattern      = "/<h([1-6])>(.*?)<\/h[1-6]>/";

        preg_match_all( $pattern , $content , $matches );

        foreach ( $matches[1] as $heading ) {
            if ( $heading > $last_heading + 1 ) {
                $valid = false;
                break;
            }
            $last_heading = $heading;
        }
        if ( ! $valid ) {
            $errors[] = $this->itap_seo_display_data( $belowContent , "le meta-field '<i>$metafield</i>' doit contenir des titres (h1,h2,h3...) dans l'ordre, certaines balises ne respectent pas cette hiérarchie" , $taxonomy );
        }
        return $errors;
    }

    /**
     * IN
     * @param $content string that contains the content of the meta field
     * @param $belowContent array that contains the error to display
     * @return array
     */
    public function itap_get_errors_from_below_cat_content( string $content , array $belowContent , $taxonomy = "product_cat" ) : array {
        if ( $taxonomy !== "product_cat" && empty( $content ) ) {
            return array();
        }
        $errors = array();
        if ( $taxonomy == "product_cat" ) {
            $metafield = "Texte dessous catégorie de produits";
        } elseif ( $taxonomy == "product_tag" ) {
            $metafield = "below_tag_content";
        } else {
            $metafield = "below_attr_content";
        }
        preg_match_all( '/<a href="(.*?)">(.*?)<\/a>/' , $content , $matches );

        if ( empty( $content ) && count( $matches[1] ) == 0 ) {
            $errors[] = $this->itap_seo_display_data( $belowContent , "Catégorie qui n'as pas de contenu et de liens dans le meta-field <i>$metafield</i>" , $taxonomy );
        }
        if ( ! empty( $content ) && count( $matches[1] ) == 0 ) {
            $errors[] = $this->itap_seo_display_data( $belowContent , "Catégorie qui ne contient pas de liens dans le meta-field '<i>$metafield</i>'" , $taxonomy );
        }
        if ( str_word_count( $content ) < 800 ) {
            $errors[] = $this->itap_seo_display_data( $belowContent , "Catégorie qui contient moins de 800 mots dans le meta-field '<i>$metafield</i>'" , $taxonomy );
        }

        if ( $taxonomy !== "product_cat" ) {
            return $errors;
        }

        if ( ! empty( $content ) && count( $matches[1] ) > 0 ) {
            foreach ( $matches[1] as $match ) {
                $path           = substr( parse_url( $match , PHP_URL_PATH ) , 1 );
                $pathtab        = array_filter( explode( '/' , $path ) );
                $product_or_cat = end( $pathtab );

                $if_product  = get_page_by_path( $product_or_cat , OBJECT , 'product' );
                $if_product  = $if_product ? wc_get_product( $if_product->ID ) : false;
                $if_category = get_term_by( 'slug' , $product_or_cat , 'product_cat' );

                $parent_actual_category = $this->push_id_parent_category( array() , $belowContent['term_id'] );
                $sameSite               = parse_url( $match , PHP_URL_HOST ) == parse_url( site_url() , PHP_URL_HOST );

                if ( ! $sameSite ) {
                    $errors[] = $this->itap_seo_display_data( $belowContent , 'Lien externe dans le meta-field "<i>Texte dessous catégorie de produits</i>"' , $taxonomy );
                }

                if ( $sameSite ) {
                    if ( $if_product && ! $if_category ) {
                        $product_cat        = get_the_terms( $if_product->get_id() , 'product_cat' );
                        $all_id_product_cat = array();
                        foreach ( $product_cat as $cat ) {
                            $all_id_product_cat[] = $cat->term_id;
                            if ( $cat->parent != 0 ) {
                                $all_id_product_cat[] = $cat->parent;
                                $newterm              = get_term( $cat->parent , 'product_cat' );
                                if ( $newterm->parent != 0 ) {
                                    $all_id_product_cat[] = $newterm->parent;
                                }
                            }
                        }

                        $all_id_product_cat = array_unique( $all_id_product_cat );
                        if ( ! in_array( $belowContent['term_id'] , $all_id_product_cat ) && count( array_intersect( $all_id_product_cat , $parent_actual_category ) ) == 0 ) {
                            $errors[] = $this->itap_seo_display_data( $belowContent , sprintf( "description sous catégorie %s qui contient un lien vers le produit %s qui n'est pas dans la catégorie,sous-catégorie ou sur-catégorie actuelle" , $belowContent['name'] , $product_or_cat ) );
                        }
                    }

                    if ( $if_category && ( ! $if_product || $if_product->get_id() == 0 ) ) {
                        $parent_category = $this->push_id_parent_category( array() , $if_category->term_id );

                        if (
                            ! in_array( $belowContent['term_id'] , $parent_category ) &&
                            count( array_intersect( $parent_category , $parent_actual_category ) ) != 0 &&
                            count( $parent_category ) == count( $parent_actual_category ) &&
                            $belowContent['parent'] != $if_category->parent
                        ) {

                            $errors[] = $this->itap_seo_display_data( $belowContent , sprintf( "description sous catégorie %s qui contient un lien vers une catégorie latérale %s dont le parent n'est pas le même" , $belowContent['slug'] , $if_category->slug ) );
                        }

                        if (
                            ! in_array( $belowContent['term_id'] , $parent_category ) &&
                            count( array_intersect( $parent_category , $parent_actual_category ) ) == 0 &&
                            $belowContent['parent'] != $if_category->term_id
                        ) {

                            $errors[] = $this->itap_seo_display_data( $belowContent , sprintf( "description sous catégorie %s qui contient un lien vers une autre catégorie %s qui n'est pas son enfant, ni son parent direct" , $belowContent['slug'] , $if_category->slug ) );;
                        }
                    }
                }
            }
        }
        return $errors;
    }


    /**
     * IN
     * @param $content string that contains the content of the meta field
     * @param $belowContent array that contains the error to display
     * @return void|array
     */
    public function itap_error_if_mailto_href_dont_have_the_same_value_of_a_tag( string $content , array $belowContent , $taxonomy = 'product_cat' ) {
        $errors = array();
        preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>(?<content>.+?)<\/a>/i' , $content , $matches );
        foreach ( $matches['href'] as $key => $match ) {
            if ( str_contains( $match , 'mailto' ) ) {
                $after_mailto = substr( $match , 7 );
                if ( $after_mailto != $matches['content'][ $key ] ) {
                    return $this->itap_seo_display_data( $belowContent , "Lien mailto avec un contenu différent de l'adresse mail" , $taxonomy );
                }
            }
        }
        return $errors;
    }


    /**
     * IN
     * Error if category or attribute slug contains numbers
     */
    public function itap_no_category_or_attribute_with_numbers_in_slug() : array {
        $errors             = array();
        $product_categories = get_terms( 'product_cat' , array('hide_empty' => false) );
        foreach ( $product_categories as $category ) {
            $category_array = (array) $category;
            if ( preg_match( '/[0-9]/' , $category->slug ) ) {
                $errors[] = $this->itap_seo_display_data( $category_array , sprintf( "Le slug de la catégorie %s ne doit pas contenir de chiffre" , $category->name ) );
            }
        }
        $attributes = wc_get_attribute_taxonomies();
        foreach ( $attributes as $attribute ) {
            if ( preg_match( '/[0-9]/' , $attribute->attribute_name ) ) {
                $error    = array(
                    'name'    => $attribute->attribute_name ,
                    'term_id' => $attribute->attribute_id ,
                );
                $errors[] = $this->itap_seo_display_data( $error , sprintf( "Le slug de l'attribut %s ne doit pas contenir de chiffre" , $attribute->attribute_name ) , 'attribute' );
            }
        }
        return $errors;
    }

    /**
     * search for nofollow attributes on particular menu links
     * search if categories are in the menu
     * search if empty categories are not in the menu
     */
    public function itap_get_errors_nofollow_link() : array {
        // search all link in the front page and check if they have a nofollow attribute
        $errors               = array();
        $specials_links_slugs = array('conditions-generales-de-vente' , 'mentions-legales' , 'politique-de-confidentialite' , 'politique-de-livraison' , 'avis-clients' , 'mon-compte');
        $menu                 = wp_get_nav_menus();
        $menus_id             = array();
        foreach ( $menu as $key ) {
            $menus_id[] = $key->term_id;
        }

        $product_cats     = get_terms( 'product_cat' , array('hide_empty' => false) );
        $menu_product_cat = array();
        foreach ( $menus_id as $id ) {
            $menu = wp_get_nav_menu_items( $id );
            foreach ( $menu as $item ) {
                $slug = explode( '/' , $item->url );
                $slug = end( $slug );
                if ( $item->object == 'product_cat' ) {
                    $menu_product_cat[] = $item->object_id;
                }
                if ( $item->xfn !== 'nofollow' && $slug != 'Uncategorized' ) {
                    $category = array('term_id' => $id , 'name' => $item->title);
                    if ( in_array( $slug , $specials_links_slugs ) ) {
                        $error    = $this->itap_seo_display_data( $category , sprintf( "%s doit avoir un attribut nofollow. quand vous êtes sur la page menu, allez sur options d'ecran en haut à droite, cocher xfn, puis inscrivez 'nofollow' sur le champ xfn du lien du menu %s" , $slug , $slug ) , 'menu' );
                        $errors[] = $error;
                    }
                }
            }
        }

        $product_cats_that_arent_in_menu = array_diff( array_column( $product_cats , 'term_id' ) , $menu_product_cat );
        $product_cats_that_are_in_menu   = array_intersect( array_column( $product_cats , 'term_id' ) , $menu_product_cat );

        foreach ( $product_cats as $category ) {
            $noindex = get_term_meta( $category->term_id , 'rank_math_robots' , true );
            if ( $category->name != 'Uncategorized' && isset( $noindex[0] ) != 'noindex' && in_array( $category->term_id , $product_cats_that_arent_in_menu ) && $category->count > 0 ) {
                $data     = array('term_id' => $category->term_id , 'name' => $category->name);
                $error    = $this->itap_seo_display_data( $data , sprintf( "La catégorie %s n'est pas présente dans le menu principal" , $category->slug ) , 'menu' , 'orange' );
                $errors[] = $error;
            }
        }
        foreach ( $product_cats_that_are_in_menu as $cat_id ) {
            $category = get_term( $cat_id );
            if ( $category->count == 0 ) {
                $data     = array('term_id' => $category->term_id , 'name' => $category->name);
                $error    = $this->itap_seo_display_data( $data , sprintf( "La catégorie %s est présente dans le menu principal mais celle ci est vide, enlevez la ou rajoutez lui des produits " , $category->slug ) , 'menu' , 'orange' );
                $errors[] = $error;
            }
        }

        return $errors;
    }


    /**
     * @param $category array that contains the error to display
     * @return false|string
     */
    public function itap_seo_display_tab( array $category ) {
        $allowed_html = array(
            'div'  => array(
                'class' => array()
            ) ,
            'span' => array(
                'class' => array()
            )
        );
        ob_start();
        ?>
        <tr style="background-color:<?php echo esc_html( $category['color'] ) ?>; <?php echo esc_html( $category['color'] ) == 'red' ? 'color : white' : '' ?>">
            <td><?php echo esc_html( $category['term_id'] ) ?></td>
            <td><?php echo esc_html( $category['name'] ) ?></td>
            <?php if ( $category['taxonomie'] === "attribute" ) : ?>
                <td><a target="_blank"
                       href="<?php echo esc_url( site_url() . '/wp-admin/edit.php?page=product_attributes&edit=' . $category['term_id'] . '&post_type=product' ) ?>">click</a>
                </td>
            <?php elseif ( $category['taxonomie'] === "menu" ) : ?>
                <td><a target="_blank"
                       href="<?php echo esc_url( site_url() . '/wp-admin/nav-menus.php' ) ?>">click</a>
                </td>
            <?php else : ?>
                <td><a target="_blank"
                       href="<?php echo esc_url( site_url() . '/wp-admin/term.php?taxonomy=' . $category['taxonomie'] . '&tag_ID=' . $category['term_id'] . '&post_type=product' ) ?>">click</a>
                </td>
            <?php endif; ?>
            <td><?php echo wp_kses( ( $category['error'] ) , $allowed_html ) ?></td>
            <td><input type="checkbox" class="itap_checkbox archiver" data-archive="seo" name="archiver"
                       value="<?php echo esc_attr( $category['uniqId'] ); ?>"></td>
        </tr>
        <?php
        return ob_get_clean();
    }


    /**
     * IN
     * Error if category meta title has the word "archive" in it
     */
    public function itap_get_errors_from_meta_title() : array {
        $errors     = array();
        $args       = array(
            'taxonomy'   => 'product_cat' ,
            'hide_empty' => false ,
        );
        $categories = get_terms( $args );

        foreach ( $categories as $category ) {
            $seo_title = get_term_meta( $category->term_id , 'rank_math_title' , true );
            if ( preg_match( '/archive/i' , $seo_title ) && $category->name != 'Uncategorized' ) {
                $error    = $this->itap_seo_display_data( json_decode( json_encode( $category ) , true ) , 'Le mot Archive est présent dans le meta titre de la page de la catégorie, supprimer le' , 'product_cat' , 'red' );
                $errors[] = $error;
            }
        }
        return $errors;
    }

    /**
     * IN
     * Error if website don't have opengraph image
     */
    public function itap_get_rank_math_opengraph_thumbnail() : array {
        $errors  = array();
        $context = stream_context_create( array('http' => array('header' => 'Connection: close\r\n')) );
        $html    = file_get_contents( site_url() , false , $context );
        if ( ! $html ) {
            return $errors;
        }
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        @$dom->loadHTML( $html );
        libxml_use_internal_errors( false );
        $xpath   = new DOMXPath( $dom );
        $metas   = $xpath->query( '//meta[@property="og:image"]' );
        $ogImage = array();
        foreach ( $metas as $meta ) {
            $ogImage[] = $meta->getAttribute( 'content' );
        }
        if ( $ogImage && ! $ogImage[0] ) {
            $data     = array('term_id' => 0 , 'name' => get_bloginfo()( 'name' ));
            $error    = $this->itap_seo_display_data( $data , "le site actuel ne contient pas d'image opengraph dans rank math section 'titre et meta'" , '' , 'red' );
            $errors[] = $error;
        }
        return $errors;
    }

    /**
     * @param $fn_errors callable reference to the class for call the function that return the errors
     * @param $fn_display callable name of the function that return the errors
     * @return void
     */
    public function get_errors_from_seo( callable $fn_errors , callable $fn_display ) : void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_seo_archive';
        $uniqIds    = $wpdb->get_results( "SELECT uniqId FROM $table_name ORDER BY uniqId " , ARRAY_A ) ?? array();
        $uniqIds    = array_column( $uniqIds , 'uniqId' );
        $errors     = $fn_errors();
        foreach ( $errors as $error ) {
            if ( ! in_array( $error['uniqId'] , $uniqIds ) ) {
                echo $fn_display( $error );
            }
        }
    }

    /**
     * helper function to get all parent categories
     * @param array $array
     * @param int $actual_id_cat
     * @return array
     */
    public function push_id_parent_category( array $array , int $actual_id_cat ) : array {
        $parent = get_term( $actual_id_cat , 'product_cat' )->parent;
        if ( $parent != 0 ) {
            $array[] = $parent;
            $array   = $this->push_id_parent_category( $array , $parent );
        }
        return $array;
    }
}
