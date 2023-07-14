<?php

use JetBrains\PhpStorm\NoReturn;

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-page-seo-quantum.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-page-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-page-automation.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-helper-function.php';

class ItapAdmin
{
    public int      $lines        = 0;
    protected array $plugin_pages = array('is_there_a_problem' , 'is_there_a_problem_seo' , 'is_there_a_problem_archive' , 'seo_quantum' , 'itap_reglages' , 'is_there_a_problem_automation' , 'help');
    private string  $plugin_name;
    private string  $version;


    public function __construct( $plugin_name , $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        add_action( 'admin_menu' , array($this , 'itap_add_menu') );

        // ajax call
        $ItapPageSeoQuantum = new ItapPageSeoQuantum();
        $ItapPageSettings   = new ItapPageSettings();
        $ItapPageAutomation = new ItapPageAutomation();

        add_action( 'wp_ajax_get_checkbox_value' , array($this , 'itap_send_archive_to_db') );
        add_action( 'wp_ajax_delete_checkbox_value' , array($this , 'itap_delete_archive') );
        add_action( 'wp_ajax_send_request_to_seo_quantum' , array($ItapPageSeoQuantum , 'itap_send_request_to_seo_quantum') );
        add_action( 'wp_ajax_save_seo_quantum_api_key' , array($ItapPageSeoQuantum , 'itap_save_seo_quantum_api_key') );
        add_action( 'wp_ajax_analysis_text_seo_quantum' , array($ItapPageSeoQuantum , 'itap_analysis_text_seo_quantum') );
        add_action( 'wp_ajax_itap_save_settings' , array($ItapPageSettings , 'itap_save_settings') );
        add_action( 'wp_ajax_fix_primary_cat' , array($ItapPageAutomation , 'itap_fix_primary_cat') );
        add_action( 'wp_ajax_change_primary_category' , array($ItapPageAutomation , 'itap_change_primary_category') );

    }

    /**
     * Enqueue the stylesheets for the admin area.
     */
    public function enqueue_styles() : void {
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'] , $this->plugin_pages ) ) {
            wp_enqueue_style( $this->plugin_name , plugin_dir_url( dirname( __FILE__ ) ) . 'admin/assets/css/itap.css' , array() , $this->version , 'all' );
        }
    }

    /**
     * Enqueue the Script for the admin area.
     */
    public function enqueue_scripts() : void {
        if ( isset( $_GET['page'] ) && in_array( $_GET['page'] , $this->plugin_pages ) ) {
            wp_enqueue_script( 'isthereaproblemJS' , plugin_dir_url( dirname( __FILE__ ) ) . 'admin/assets/js/itap.js' , array('jquery') , $this->version , true );
            wp_localize_script( 'isthereaproblemJS' , 'my_ajax_object' , array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
        }
    }

    /**
     *  Ajax function that send the archive to the database
     * @return no-return
     */
    public function itap_send_archive_to_db() {
        global $wpdb;
        $uniqId = $_POST['uniqId'];
        $seo    = $_POST['seo'];
        if ( $seo == 'true' ) {
            $table_name = $wpdb->prefix . 'itap_seo_archive';
        } else {
            $table_name = $wpdb->prefix . 'itap_archive';
        }
        // create table if not exist
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uniqId varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        $wpdb->insert( $table_name , array(
            'uniqId' => $uniqId ,
        ) );

        wp_die();
    }

    /**
     * Ajax function that delete the archive from the database
     * @return no-return
     */
    public function itap_delete_archive() {
        global $wpdb;
        $uniqId     = $_POST['uniqId'];
        $table_name = $wpdb->prefix . 'itap_archive';
        $wpdb->delete( $table_name , array('uniqId' => $uniqId) );
        wp_die();
    }


    /**
     * Add menu to the admin area
     */
    public function itap_add_menu() : void {
        global $wpdb;
        $total_integration_errors        = get_option( 'total_integration_errors' ) > 0 ? get_option( 'total_integration_errors' ) : 0;
        $total_seo_errors                = get_option( 'count_seo_errors' ) ? get_option( 'count_seo_errors' ) : 0;
        $total_errors                    = $total_integration_errors + $total_seo_errors;
        $total_integration_errors_string = $total_integration_errors == 300 ? '300+' : $total_integration_errors;
        $total_seo_errors_string         = $total_seo_errors == 300 ? '300+' : $total_seo_errors;
        $total_errors_string             = $total_errors == 600 ? '600+' : $total_errors;

        add_menu_page( 'Problems' , sprintf( "Problems <span class='awaiting-mod'>%s</span>" , $total_errors_string ) , 'publish_pages' , 'is_there_a_problem' , array($this , 'itap_page') , 'dashicons-admin-site' , 100 );
        add_submenu_page( 'is_there_a_problem' , 'Integration' , sprintf( "Integration <span class='awaiting-mod'>%s</span>" , $total_integration_errors_string ) , 'publish_pages' , 'is_there_a_problem' , array($this , 'itap_page') );
        add_submenu_page( 'is_there_a_problem' , 'SEO' , sprintf( "SEO <span class='awaiting-mod'>%s</span>" , $total_seo_errors_string ) , 'publish_pages' , 'is_there_a_problem_seo' , array($this , 'itap_page_seo') );
        add_submenu_page( 'is_there_a_problem' , 'Automatisation' , 'Automatisation' , 'publish_pages' , 'is_there_a_problem_automation' , array($this , 'itap_page_automation') );
        add_submenu_page( 'is_there_a_problem' , 'Archives' , "Archives" , 'publish_pages' , 'is_there_a_problem_archive' , array($this , 'itap_page_archive') );
//        add_submenu_page( 'is_there_a_problem' , 'Seo-Quantum ' , 'Seo-Quantum' , 'publish_pages' , 'seo_quantum' , array($this , 'itap_page_seoquantum') );
        add_submenu_page( 'is_there_a_problem' , 'Reglages ' , 'Reglages' , 'publish_pages' , 'itap_reglages' , array($this , 'itap_page_settings') );
        add_submenu_page( 'is_there_a_problem' , 'Help' , 'Help' , 'publish_pages' , 'help' , array($this , 'itap_page_help') );

    }

    /**
     * Display the SEO page
     */
    public function itap_page_seo() : void {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-page-seo.php';
        $ItapPageSeo = new ItapPageSeo();
    }

    /**
     * Display the Automation page
     */
    public function itap_page_automation() : void {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-page-automation.php';
        $ItapPageAutomation = new ItapPageAutomation();
        $ItapPageAutomation->itap_partials_automation();

    }


    /**
     * Display the Archive page
     */
    public function itap_page_archive() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-itap-page-archive.php';
        $ItapPageArchive = new ItapPageArchive();
    }

    /**
     * Display the SEO Quantum page
     */
//    public function itap_page_seoquantum() : void {
//        $ItapPageSeoQuantum = new ItapPageSeoQuantum();
//        $ItapPageSeoQuantum->itap_seo_quantum_displayTab();
//    }
    /**
     * Display the SEO Quantum page
     */
    public function itap_page_help() : void {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/itap-help-display.php';
    }

    /**
     * Display the Settings page
     */
    public function itap_page_settings() : void {
        $ItapPageSettings = new ItapPageSettings();
        $ItapPageSettings->itap_settings_displayTab();
    }

    /**
     * Display Is there a problem main page
     */
    public function itap_page() : void {
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'is_there_a_problem' ) {
            require_once plugin_dir_path( __FILE__ ) . 'partials/itap-admin-display.php';
        }
    }

    /**
     * @param array $result array that represents a product that has a problem
     * @param string $problem the problem
     * @param string $codeError the code of the problem
     * @param string $color color of the error
     */
    public function itap_display_data( array $result , string $problem , string $codeError , string $color = "" ) : array {
        return array(
            'uniqId'      => $result['id'] . $codeError ,
            'id'          => $result['id'] ,
            'title'       => $result['title'] ,
            'url'         => $result['url'] ,
            'author_name' => $result['author_name'] ,
            'imageUrl'    => $result['imageUrl'] ,
            'url_edit'    => get_edit_post_link( $result['id'] ) ,
            'alt'         => $result['alt'] ,
            'error'       => $problem ,
            "color"       => $color ,
        );
    }


    /**
     * get all the product and return it
     */
    public function itap_get_all_infos_from_product() : array {
        $args           = array(
            'post_type'      => 'product' ,
            'post_status'    => 'publish' ,
            'posts_per_page' => -1 ,
            'orderby'        => 'id' ,
            'order'          => 'ASC'
        );
        $products       = get_posts( $args );
        $products_array = array();
        foreach ( $products as $product ) {
            $product          = wc_get_product( $product->ID );
            $image_id         = get_post_thumbnail_id( $product->get_id() );
            $author_id        = get_post_field( 'post_author' , $product->get_id() );
            $products_array[] = array(
                'id'          => $product->get_id() ,
                'title'       => $product->get_title() ,
                'url'         => $product->get_permalink() ,
                'author_name' => get_the_author_meta( 'display_name' , $author_id ) ,
                'imageUrl'    => wp_get_attachment_url( $image_id ) ?? "" ,
                'alt'         => get_post_meta( $image_id , '_wp_attachment_image_alt' , true ) ?? ""
            );
        }
        return $products_array;
    }


    public function itap_get_errors_from_products( array $results ) : array {
        $errors = array();
        foreach ( $results as $result ) {
            $functions = array(
                'itap_get_errors_from_links' ,
                'itap_get_errors_from_product_or_variations_without_price' ,
                'itap_get_errors_from_alt_descriptions' ,
                'itap_get_errors_from_variable_products' ,
                'itap_get_errors_from_images' ,
                'itap_get_errors_from_product_that_have_same_slug_of_his_category' ,
                'itap_get_errors_from_rank_math' ,
                "itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur" ,
                "itap_get_errors_from_descriptions" ,
                "itap_get_errors_from_missing_meta_fields_images"
            );
            foreach ( $functions as $function ) {
                foreach ( $this->$function( $result ) as $occurence ) {
                    if ( ! empty( $occurence ) ) {
                        $errors[] = $occurence;
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * IN
     * List all the products that have a problem :
     * - product that has a link in the description
     * - product that has a div in the description
     * - product that has a h1 in the description
     * @param array $result list of all the products
     * @return array|void list of all the products that have a problem
     */
    public function itap_get_errors_from_links( array $result ) {
        $errors            = array();
        $product           = wc_get_product( $result['id'] );
        $description1      = $product->get_meta( 'description-1' ) ?? $product->get_meta( 'description1' ) ?? null;
        $description2      = $product->get_meta( 'description-2' ) ?? $product->get_meta( 'description2' ) ?? null;
        $description3      = $product->get_meta( 'description-3' ) ?? $product->get_meta( 'description3' ) ?? null;
        $main_description  = $product->get_description();
        $short_description = $product->get_short_description();

        $all_description  = array($description1 , $description2 , $description3 , $main_description , $short_description);
        $link_description = array_filter( $all_description , function ( $value ) {
            return str_contains( $value , 'href' );
        } );
        if ( count( $link_description ) > 0 ) {
            foreach ( $link_description as $link ) {
                preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>(?<content>.+?)<\/a>/i' , $link , $matches );
                foreach ( $matches["href"] as $i => $match ) {
                    if ( str_contains( $match , 'mailto' ) ) {
                        $after_mailto = substr( $match , 7 );
                        if ( $after_mailto !== $matches["content"][ $i ] ) {
                            $errors[] = $this->itap_display_data( $result , "Produit qui contient un lien mailto dont la valeur du href n'est pas égale à la valeur de la balise lien" , '1023' , "#ff0e0e" );
                            break;
                        }
                        continue;
                    }
                    $errors[] = $this->itap_display_data( $result , 'Description-1, description-2 ou description-3 ou description principale ou description courte du produit qui contient un lien' , '1012' , "#ff0e0e" );
                    break;
                }
            }
        }


        $div_description = array_filter( $all_description , function ( $value ) {
            return str_contains( $value , '<div>' ) || str_contains( $value , '</div>' );
        } );
        if ( count( $div_description ) > 0 ) {
            $errors[] = $this->itap_display_data( $result , 'Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise div, effacez la' , '1014' , "#ff0e0e" );
        }

        $h1_description = array_filter( $all_description , function ( $value ) {
            return str_contains( $value , '<h1>' ) || str_contains( $value , '</h1>' );
        } );
        if ( count( $h1_description ) > 0 ) {
            $errors[] = $this->itap_display_data( $result , "Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise h1, il ne peut y en avoir qu'une sur la page produit qui correspond au titre, effacez la" , '1018' , "#ff0e0e" );
        }

        return $errors;
    }

    /**
     * IN
     * Return an error if a product has no alt text on the image, or alt text is too short
     * @param array $result product that possibly has a problem
     */
    public function itap_get_errors_from_alt_descriptions( array $result ) : array {
        $errors = array();
        if ( $result['alt'] == '' ) {
            $errors[] = $this->itap_display_data( $result , 'Balise alt vide' , '1001' );
        } elseif ( strlen( $result['alt'] ) < 10 ) {
            $errors[] = $this->itap_display_data( $result , 'Balise alt trop courte' , '1002' );
        }
        $image_format = array('.jpg' , '.jpeg' , '.png' , '.gif' , '.webp' , '.svg');
        // if you find one of those formats in the $result['alt'], display an error
        foreach ( $image_format as $format ) {
            if ( str_contains( $result['alt'] , $format ) ) {
                $errors[] = $this->itap_display_data( $result , "Balise alt qui contient un format d'image (jpg , jpeg , gif, png , webp , svg), remplacer le par une vraie description de l'image" , '1026' );
                break;
            }
        }
        return $errors;
    }

    /**
     * IN
     * Return an error if a product has no price
     * @param array $result product that possibly has a problem
     */
    public function itap_get_errors_from_product_or_variations_without_price( array $result ) : array {
        $errors  = array();
        $product = wc_get_product( $result['id'] );
        if ( $product->is_type( 'variable' ) ) {
            $variations = $product->get_children();
            foreach ( $variations as $variation ) {
                $product = wc_get_product( $variation );
                if ( ! $product ) {
                    continue;
                }
                if ( $product->get_regular_price() == '' ) {
                    $errors[] = $this->itap_display_data( $result , "Produit variable dont une des variations ne contient pas de prix " , '1020' );
                    break;
                }
            }
        } else {
            if ( $product->get_regular_price() == '' ) {
                $errors[] = $this->itap_display_data( $result , "Produit simple qui n'a pas de prix" , '1021' );
            }
        }
        return $errors;
    }


    /**
     * IN
     * Return an error if :
     * - variable product has no default product
     * - variable product has an attribute that is not in the list of colors
     * - variable product that don't have variation
     * - variable product that don't have enough variation
     * @param array $result product that possibly has a problem
     * @return array|void
     */
    public function itap_get_errors_from_variable_products( array $result ) {
        $errors  = array();
        $product = wc_get_product( $result['id'] );
        if ( ! $product ) {
            return array();
        }
        if ( $product->is_type( 'variable' ) ) {
            if ( ! $product->get_default_attributes() ) {
                $errors[] = $this->itap_display_data( $result , "Produit variable qui n'a pas de produit par défaut" , '1003' );
            }

            // check if the terms of attribute 'pa_couleur' or 'couleur are in the list of colors
            $attribute_variation = array();
            foreach ( $product->get_attributes() as $attribute ) {
                if ( $attribute['variation'] ) {
                    $attribute_variation[] = $attribute;
                }
            }

            $couleurs = array('argente' , 'beige' , 'blanc' , 'bleu' , 'bleu-fonce' , 'bordeaux' , 'gris' , 'jaune' , 'bronze' , 'marron' , 'multicolore' , 'noir' , 'dore' , 'orange' , 'rose' , 'rose-fonce' , 'rouge' , 'turquoise' , 'vert' , 'violet');
            $couleurs = array_merge( $couleurs , $this->get_colors_from_settings() );
            foreach ( $attribute_variation as $attribute ) {
                $product_tag = wc_get_attribute( $attribute['id'] );
                $tag_name    = $product_tag->name;
                if ( isset( $tag_name ) && strtolower( $tag_name ) === 'couleur' ) {
                    $terms = wc_get_product_terms( $result['id'] , $attribute['name'] , array('fields' => 'slugs') );
                    foreach ( $terms as $term ) {
                        $term_slugify = $this->slugify( $term );
                        if ( ! in_array( $term_slugify , $couleurs ) && $attribute['variation'] ) {
                            $errors[] = $this->itap_display_data( $result , sprintf( "Produit variable dont la couleur %s ne fait pas partie des <div class='tooltip'>couleurs possibles<span class='tooltiptext'>%s</span></div>" , esc_html( $term_slugify ) , implode( ', ' , $couleurs ) ) , '1004' );
                        }
                    }
                }
            }

            if ( count( $attribute_variation ) != count( $product->get_default_attributes() ) ) {
                $errors[] = $this->itap_display_data( $result , 'Produit variable ou il manque une ou plusieurs variations dans le produit par défaut' , '1005' );
            }

            $default_attributes = $product->get_default_attributes();
//            foreach ( $default_attributes as $key => $value ) {
//                $product_attributes = wc_get_product_terms( $result['id'] , $key , array('fields' => 'slugs') );
//                if ( $value !== $product_attributes[0] ) {
//                    $errors[] = $this->itap_display_data( $result , "Produit variable dont le produit par défaut à comme variation des valeurs qui ne sont pas les premieres de leurs <div class='tooltip'>catégories<span class='tooltiptext'>Erreur qui signale également les attributs remplis à la volée directement sur la page du produit, merci de rentrer tous les attributs et leurs termes dans l'onglet attribut de produit</span></div>. " , '1006' );
//
//                }
//            }

            if ( count( $product->get_children() ) == 0 ) {
                $errors[] = $this->itap_display_data( $result , "Produit variable qui n'a pas de variations, ajoutez en ou passez le en produit simple" , '1007' );
            }
        }
        return $errors;
    }


    /**
     * IN
     * Check if WordPress create enough images sizes
     * @param array $result the product
     */
    public function itap_get_errors_from_images( array $result ) : array {
        $errors       = array();
        $image_id     = get_post_thumbnail_id( $result['id'] );
        $errors_image = 0;
        if ( $image_id == 0 ) {
            $errors[] = $this->itap_display_data( $result , 'Produit sans images' , '1008' );
        } else {
            $image_metadata = get_post_meta( $image_id , '_wp_attachment_metadata' , 'true' );
            if ( ! $image_metadata ) {
                return $errors;
            }
            $upload_dir = wp_upload_dir()['basedir'];
            $base_path  = substr( $image_metadata['file'] , 0 , 8 );
            $image_path = $upload_dir . '/' . $base_path;
            foreach ( $image_metadata['sizes'] as $size ) {
                if ( ! file_exists( $image_path . $size['file'] ) ) {
                    $errors_image++;
                }
            }
            if ( $errors_image > 0 ) {
                $errors[] = $this->itap_display_data( $result , "Formats d'images manquants/non créés par WordPress pour WooCommerce, merci de réuploader l'image du produit" , '1009' );
            }
        }
        return $errors;
    }

    /**
     * IN
     * get errors from rank math product description who hasn't been filled
     * @param array $results product that we want to check
     */
    public function itap_get_errors_from_rank_math( array $result ) : array {
        $errors  = array();
        $product = wc_get_product( $result['id'] );
        if ( ! get_post_meta( $product->get_id() , 'rank_math_description' , true ) ) {
            $errors[] = $this->itap_display_data( $result , 'Produit qui a une meta description automatique, personnalisez la' , '1010' );
        }
        return $errors;
    }

    /**
     * IN
     * check if the product have meta fields images filled
     * @param $result array the product that we want to check
     * @return array|void
     */
    public function itap_get_errors_from_missing_meta_fields_images( array $result ) : array {
        $errors             = array();
        $images_meta_fields = array();
        $itap_settings      = get_option( 'itap_settings' );
        if ( ! $itap_settings ) {
            return $errors;
        }
        if ( isset( $itap_settings['itap_img_1'] ) ) {
            $images_meta_fields[] = $itap_settings['itap_img_1_label'];
        }
        if ( isset( $itap_settings['itap_img_2'] ) ) {
            $images_meta_fields[] = $itap_settings['itap_img_2_label'];
        }
        if ( isset( $itap_settings['itap_img_3'] ) ) {
            $images_meta_fields[] = $itap_settings['itap_img_3_label'];
        }
        $images_meta_fields = array_filter( $images_meta_fields );
        if ( empty( $images_meta_fields ) ) {
            return $errors;
        }

        $product = wc_get_product( $result['id'] );
        if ( ! $product ) {
            return $errors;
        }
        foreach ( $images_meta_fields as $label ) {
            if ( ! get_post_meta( $product->get_id() , $label , true ) ) {
                $errors[] = $this->itap_display_data( $result , sprintf( "Produit ou le meta field %s est vide, il doit être rempli" , $label ) , '1024' );
            }
        }
        return $errors;

    }


    /**
     * IN
     * Check in settings fields to search for errors :
     * - if the product does not have enough words (total and per field)
     * @param array $result product that we want to check
     */
    public function itap_get_errors_from_descriptions( array $result ) : array {
        $errors                     = array();
        $product                    = wc_get_product( $result['id'] );
        $settings                   = get_option( 'itap_settings' );
        $total_words_min_short_desc = $settings['total_words_min_short_desc'] ?? 50;
        if ( $settings ) {
            $possible_desc = array(
                $settings['desc1'] ? $product->get_meta( 'description-1' ) : null ,
                $settings['desc2'] ? $product->get_meta( 'description-2' ) : null ,
                $settings['desc3'] ? $product->get_meta( 'description-3' ) : null ,
                $settings['desc_seo'] ? get_post_field( 'description-seo' , $product->get_meta( 'description-categorie' ) ) : null ,
            );
            $custom_field  = $settings['custom_field'] ? array(
                $settings['custom_field_input_1'] ? $product->get_meta( $settings['custom_field_input_1'] ) : null ,
                $settings['custom_field_input_2'] ? $product->get_meta( $settings['custom_field_input_2'] ) : null ,
                $settings['custom_field_input_3'] ? $product->get_meta( $settings['custom_field_input_3'] ) : null ,
            ) : array();

            $possible_desc = array_merge( $possible_desc , $custom_field );
            $possible_desc = array_filter( $possible_desc );

            $total_words_min_page  = $settings['total_words_min_page'] ?? 200;
            $total_words_min_block = $settings['total_words_min_block'] ?? 60;
            $total_count           = 0;
            $error_check           = false;

            foreach ( $possible_desc as $field ) {
                $total_words = Itap_Helper_Function::utf8_word_count( strip_tags( $field ) );
                if ( $total_words < $total_words_min_block && ! $error_check ) {
                    $errors[]    = $this->itap_display_data( $result , sprintf( "Chaque champ d'une page produit dont le nom est coché dans les paramètres du plugin doit avoir plus de %s mots, rajoutez en plus" , $total_words_min_block ) , '1015' );
                    $error_check = true;
                }
                $total_count += $total_words;
            }

            // add the short description
            $total_count += Itap_Helper_Function::utf8_word_count( strip_tags( $product->get_short_description() ) );

            if ( $total_count < $total_words_min_page ) {
                $errors[] = $this->itap_display_data( $result , sprintf( 'La page du produit contient moins de %s mots, le compte est calculé grâce à la somme de tous les champs cochés dans les paramètres + description courte' , $total_words_min_page ) , '1016' );
            }
            $product_short_desc = strip_tags( $product->get_short_description() );
            $product_short_desc = str_replace( array("\n" , "\r") , '' , $product_short_desc );
            if ( Itap_Helper_Function::utf8_word_count( $product_short_desc ) > $total_words_min_short_desc ) {
                $errors[] = $this->itap_display_data( $result , 'La description courte du produit doit être inférieure à ' . $total_words_min_short_desc . ' mots, enlevez du contenu' , '1025' );
            }

        } else {
            $description1 = $product->get_meta( 'description-1' ) ?? null;
            $description2 = $product->get_meta( 'description-2' ) ?? null;
            if ( Itap_Helper_Function::utf8_word_count( $description1 ) + Itap_Helper_Function::utf8_word_count( $description2 ) + Itap_Helper_Function::utf8_word_count( $product->get_short_description() ) < 200 ) {
                $errors[] = $this->itap_display_data( $result , 'Description-1 + description-2 + description courte du produit inférieures à 200 mots, mettez plus de contenu' , '1013' );
            }
        }
        return $errors;
    }

    /**
     * IN
     * Error if the product has a variation with only one color
     * @param array $result product that we want to check
     */
    public function itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur( array $result ) : array {
        $errors  = array();
        $product = wc_get_product( $result['id'] );
        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return $errors;
        }
        foreach ( $product->get_attributes() as $product_attribute ) {
            $product_tag = wc_get_attribute( $product_attribute['id'] );
            if ( isset( $product_tag->name ) && strtolower( $product_tag->name ) === 'couleur' && count( $product_attribute['options'] ) == 1 && $product_attribute['variation'] ) {
                $errors[] = $this->itap_display_data( $result , 'Le produit a une seule couleur,pas besoin de variations, décocher la case "utiliser pour les variations" pour la couleur' , '1020' );
                break;
            }
        }
        return $errors;
    }


    /**
     * Get the errors from all the functions that check the problems
     * @param string $fn the function that check the problem
     * @param array $results the array that contains all the products
     */
    public function itap_get_errors( string $fn , array $results ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'itap_archive';
        $uniqIds    = $wpdb->get_results( "SELECT uniqId FROM $table_name ORDER BY id " , ARRAY_A );
        $errors     = $this->$fn( $results );
        if ( count( $errors ) > 0 ) {
            foreach ( $errors as $error ) {
                if ( ! in_array( array('uniqId' => $error['uniqId']) , $uniqIds ) && $this->lines <= 300 ) {
                    echo $this->itap_display_tab( $error );
                    $this->lines++;
                }
            }
        }
        update_option( 'total_integration_errors' , $this->lines );
    }


    /**
     * Display one table row every time there is an error
     * @param $error array that represents a product that has a problem
     * @param string $danger the color when displaying the error
     */
    public function itap_display_tab( array $error ) : string {
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
        <tr <?php printf( 'style="%s"' , esc_attr( $error['color'] ? "background-color:" . $error['color'] . ";color:white;" : '' ) ); ?>>
            <td><?php echo esc_html( $error['id'] ); ?></td>
            <td><?php echo esc_html( $error['title'] ); ?></td>
            <td><a target="_blank" <?php printf( 'style="%s"' , esc_attr( $error['color'] ? 'color:white' : '' ) ); ?>
                   href="<?php echo esc_url( $error['url_edit'] ); ?>">click</a></td>
            <td><?php echo wp_kses( $error['error'] , $allowed_html ); ?></td>
            <td><?php echo esc_html( $error['author_name'] ); ?></td>
            <td><input type="checkbox" class="itap_checkbox archiver" data-archive="integration" name="archiver"
                       value="<?php echo esc_attr( $error['uniqId'] ); ?>"></td>
        </tr>
        <?php
        return ob_get_clean();
    }


    /**
     * IN
     * Check if product has same slug of his category
     * @param array $results list of all the products
     */
    function itap_get_errors_from_product_that_have_same_slug_of_his_category( array $result ) : array {
        $errors     = array();
        $product    = wc_get_product( $result['id'] );
        $categories = wp_get_post_terms( $result['id'] , 'product_cat' , array('fields' => 'slugs') );
        $slug       = $product->get_slug();

        if ( in_array( $slug , $categories ) ) {
            $errors[] = $this->itap_display_data( $result , "le slug d'un produit ne peut pas être le même qu'une de ses catégories" , '1011' );
        }
        return $errors;
    }

    /**
     * slugify a text
     * @param $text string to slugify
     */
    public function slugify( string $text ) : string {
        // Strip html tags
        $text = strip_tags( $text );
        // Replace non letter or digits by -
        $text = preg_replace( '~[^\pL\d]+~u' , '-' , $text );
        // Transliterate
        setlocale( LC_ALL , 'en_US.utf8' );
        $text = iconv( 'utf-8' , 'us-ascii//TRANSLIT' , $text );
        // Remove unwanted characters
        $text = preg_replace( '~[^-\w]+~' , '' , $text );
        // Trim
        $text = trim( $text , '-' );
        // Remove duplicate -
        $text = preg_replace( '~-+~' , '-' , $text );
        // Lowercase
        $text = strtolower( $text );
        // Check if it is empty
        if ( empty( $text ) ) {
            return 'n-a';
        }
        // Return result
        return $text;
    }

    public function get_colors_from_settings() {
        $colors = get_option( 'itap_settings' )['colors'];
        if ( empty( $colors ) ) {
            return array();
        }
        $colors = explode( '/' , $colors );
        return array_map( 'trim' , $colors );
    }
}

