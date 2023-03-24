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
            $total_problems = count( $this->itap_get_errors_no_categories_description() ) + count( $this->itap_get_errors_no_tags_description() ) + count( $this->itap_get_errors_below_category_content() ) + count( $this->itap_get_errors_nofollow_link() );
            update_option( 'count_seo_errors' , $total_problems );
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/itap-seo-display.php';
        }
    }

    /**
     * Error if a category has no description
     */
    public function itap_get_errors_no_categories_description() : array {
        $errors     = array();
        $args       = array(
            'taxonomy'   => 'product_cat' ,
            'hide_empty' => false ,
        );
        $categories = get_terms( $args );
        foreach ( $categories as $category ) {
            if ( empty( $category->description ) && $category->name != 'Uncategorized' ) {
                $error    = $this->itap_seo_display_data( json_decode( json_encode( $category ) , true ) , 'Pas de description pour cette catégorie' );
                $errors[] = $error;
            }
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
        return array(
            'term_id'   => $result['term_id'] ,
            'name'      => $result['name'] ,
            'error'     => $problem ,
            'taxonomie' => $taxonomy ,
            'color'     => $color
        );
    }

    /**
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
     * get different errors for below category content :
     * - no content and no links
     * - not only child links or direct parent links
     * - links on products that are not in the category
     * - not enough words
     *
     * @return array
     */
    public function itap_get_errors_below_category_content() : array {
        $args          = array(
            'taxonomy'   => 'product_cat' ,
            'hide_empty' => false ,
        );
        $categories    = get_terms( $args );
        $belowContents = array();
        foreach ( $categories as $category ) {
            $category_meta = get_metadata( 'term' , $category->term_id );
            $noindex       = isset( $category_meta['rank_math_robots'] ) ? unserialize( $category_meta['rank_math_robots'][0] ) : array();
            $noindex       = ! empty( $noindex ) && in_array( 'noindex' , $noindex ) ? '1' : '0';

            $temptab                           = array();
            $temptab['term_id']                = $category->term_id;
            $temptab['name']                   = $category->name;
            $temptab['slug']                   = $category->slug;
            $temptab['parent']                 = $category->parent;
            $temptab['description-bas']        = get_term_meta( $category->term_id , 'description-bas' , true );
            $temptab['below_category_content'] = get_term_meta( $category->term_id , 'below_category_content' , true );
            $temptab['noindex']                = $noindex;
            $belowContents[]                   = $temptab;
        }

        $errors = array();
        foreach ( $belowContents as $belowContent ) {

            if ( $belowContent['name'] != 'Uncategorized' && $belowContent['noindex'] != '1' ) {
                $content = $belowContent['below_category_content'] ? $belowContent['below_category_content'] : $belowContent['description-bas'];
                preg_match_all( '/<a href="(.*?)">(.*?)<\/a>/' , $content , $matches );

                if ( empty( $content ) && count( $matches[1] ) == 0 ) {
                    $error    = $this->itap_seo_display_data( $belowContent , "Catégorie qui n'as pas de contenu et de liens dans le meta-field <i>Texte dessous catégorie de produits</i>" );
                    $errors[] = $error;
                }
                if ( ! empty( $content ) && count( $matches[1] ) == 0 ) {
                    $error    = $this->itap_seo_display_data( $belowContent , 'Catégorie qui ne contient pas de liens dans le meta-field "<i>Texte dessous catégorie de produits</i>"' );
                    $errors[] = $error;
                }
                if ( str_word_count( $content ) < 800 ) {
                    $error    = $this->itap_seo_display_data( $belowContent , 'Catégorie qui contient moins de 800 mots dans le meta-field "<i>Texte dessous catégorie de produits</i>"' );
                    $errors[] = $error;
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
                            $error    = $this->itap_seo_display_data( $belowContent , 'Lien externe dans le meta-field "<i>Texte dessous catégorie de produits</i>"' );
                            $errors[] = $error;
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
                                    $error    = $this->itap_seo_display_data( $belowContent , sprintf( "description sous catégorie %s qui contient un lien vers le produit %s qui n'est pas dans la catégorie,sous-catégorie ou sur-catégorie actuelle" , $belowContent['name'] , $product_or_cat ) );
                                    $errors[] = $error;
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
                                    $error    = $this->itap_seo_display_data( $belowContent , sprintf( "description sous catégorie %s qui contient un lien vers une catégorie latérale %s dont le parent n'est pas le même" , $belowContent['slug'] , $if_category->slug ) );
                                    $errors[] = $error;
                                }

                                if (
                                    ! in_array( $belowContent['term_id'] , $parent_category ) &&
                                    count( array_intersect( $parent_category , $parent_actual_category ) ) == 0 &&
                                    $belowContent['parent'] != $if_category->term_id
                                ) {
                                    $error    = $this->itap_seo_display_data( $belowContent , sprintf( "description sous catégorie %s qui contient un lien vers une autre catégorie %s qui n'est pas son enfant, ni son parent direct" , $belowContent['slug'] , $if_category->slug ) );
                                    $errors[] = $error;
                                }
                            }
                        }
                    }
                }

                preg_match_all( '/<h2>(.*?)<\/h2>/s' , $content , $matches );
                if ( ! empty( $content ) && count( $matches[1] ) == 0 ) {
                    $error    = $this->itap_seo_display_data( $belowContent , "le meta-field '<i>Texte dessous catégorie de produits</i>' doit contenir des balises h2 (ou Titre 2), celle ci n'en contient pas" );
                    $errors[] = $error;
                }

                // every 300 words, there must be a title,
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
                        $error    = $this->itap_seo_display_data( $belowContent , "le meta-field '<i>Texte dessous catégorie de produits</i>' doit contenir des titres tous les 300 mots, celle ci n'en contient pas" );
                        $errors[] = $error;
                    }
                }
            }
        }
        return $errors;
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
                        $error    = $this->itap_seo_display_data( $category , sprintf( "%s doit avoir un attribut nofollow. quand vous êtes sur la page menu, allez sur options d'ecran en haut à droite, cocher xfn, puis inscrivez 'nofollow' sur le champ xfn du lien du menu %s" , $slug , $slug ) );
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
                $error    = $this->itap_seo_display_data( $data , sprintf( "La catégorie %s n'est pas présente dans le menu principal" , $category->slug ) , '' , 'orange' );
                $errors[] = $error;
            }
        }
        foreach ( $product_cats_that_are_in_menu as $cat_id ) {
            $category = get_term( $cat_id );
            if ( $category->count == 0 ) {
                $data     = array('term_id' => $category->term_id , 'name' => $category->name);
                $error    = $this->itap_seo_display_data( $data , sprintf( "La catégorie %s est présente dans le menu principal mais celle ci est vide, enlevez la ou rajoutez lui des produits " , $category->slug ) , '' , 'orange' );
                $errors[] = $error;
            }
        }

        return $errors;
    }


    /**
     * @param $category array that contains the error to display
     * @return false|string
     */
    public function itap_seo_display_tab( $category ) {
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
            <td><a target="_blank"
                   href="<?php echo esc_url( site_url() . '/wp-admin/term.php?taxonomy=' . $category['taxonomie'] . '&tag_ID=' . $category['term_id'] . '&post_type=product' ) ?>">click</a>
            </td>
            <td><?php echo wp_kses( ( $category['error'] ) , $allowed_html ) ?></td>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * @param $category array that contains the error to display, duplicate function itap_seo_display_tab for have the link on different category
     * @return void
     */
    public function itap_seo_display_tab_links( $category ) {
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
        <tr style="background-color: <?php echo esc_html( $category['color'] ) ?>;<?php echo esc_html( $category['color'] ) == 'red' ? 'color : white' : '' ?>">
            <td><?php echo esc_html( $category['term_id'] ) ?></td>
            <td><?php echo esc_html( $category['name'] ) ?></td>
            <td><a target="_blank"
                   href="<?php echo esc_url( site_url() . '/wp-admin/nav-menus.php?action=edit&menu=' . $category['term_id'] ) ?>">click</a>
            </td>
            <td><?php echo wp_kses( ( $category['error'] ) , $allowed_html ) ?></td>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * Error if category meta title has the word "archive" in it
     */
    public function itap_get_errors_from_meta_title() : array {
        $errors     = array();
        $args       = array(
            'taxonomy'   => 'product_cat' ,
            'hide_empty' => false ,
        );
        $categories = get_terms( $args );
        $urls       = array();
        foreach ( $categories as $category ) {
            $urls[ $category->term_id ] = get_term_link( $category->term_id , 'product_cat' );
        }

        $mh       = curl_multi_init();
        $channels = array();
        $titles   = array();

        foreach ( $urls as $key => $url ) {
            $channels[ $key ] = curl_init();
            curl_setopt( $channels[ $key ] , CURLOPT_URL , $url );
            curl_setopt( $channels[ $key ] , CURLOPT_HEADER , 0 );
            curl_setopt( $channels[ $key ] , CURLOPT_RETURNTRANSFER , 1 );
            // curl_setopt($channels[$key], CURLOPT_NOBODY, true);
            curl_multi_add_handle( $mh , $channels[ $key ] );
        }

        $running = null;
        do {
            curl_multi_exec( $mh , $running );
        } while ( $running );

        $result = array();
        foreach ( $channels as $id => $c ) {
            $result[ $id ] = curl_multi_getcontent( $c );
            $doc           = new DOMDocument();
            libxml_use_internal_errors( true );
            @$doc->loadHTML( $result[ $id ] );
            libxml_use_internal_errors( false );
            $titles[ $id ] = $doc->getElementsByTagName( 'title' )->item( 0 )->nodeValue;
            curl_multi_remove_handle( $mh , $c );
        }
        curl_multi_close( $mh );
        foreach ( $titles as $id => $title ) {
            $category = get_term( $id , 'product_cat' );
            if ( preg_match( '/archive/i' , $title ) && $category->name != 'Uncategorized' ) {
                $error    = $this->itap_seo_display_data( json_decode( json_encode( $category ) , true ) , 'Le mot Archive est présent dans le meta titre de la page de la catégorie, supprimer le' , 'product_cat' , 'red' );
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
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
            $data     = array('term_id' => 0 , 'name' => bloginfo( 'name' ));
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
        $errors = $fn_errors();
        if ( is_array( $errors ) && count( $errors ) > 0 ) {
            foreach ( $errors as $error ) {
                echo $fn_display( $error );
            }
        }
    }
}
