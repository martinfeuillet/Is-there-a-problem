<?php // phpcs:ignore

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ingenius.agency/
 * @since      1.0.0
 *
 * @package    is-there-a-problem
 * @subpackage is-there-a-problem/admin
 */
class ItapAdmin extends ItapHelperFunction {

	/**
	 * The name of the author.
	 *
	 * @var string
	 */
	public string $author_name = '';

	/**
	 * The name of the plugin.
	 *
	 * @var array
	 */
	protected array $plugin_pages = array( 'is_there_a_problem', 'is_there_a_problem_seo', 'is_there_a_problem_archive', 'seo_quantum', 'itap_reglages', 'is_there_a_problem_automation', 'help' );

	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * The version of the plugin.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * The settings part of the plugin.
	 *
	 * @var ItapPageSettings
	 */
	private ItapPageSettings $itap_page_settings;

	/**
	 * Class constructor.
	 *
	 * @param string           $plugin_name The name of the plugin.
	 * @param string           $version The version of the plugin.
	 * @param ItapPageSettings $itap_page_settings The settings part of the plugin.
	 */
	public function __construct( string $plugin_name, string $version, ItapPageSettings $itap_page_settings ) {
		$this->plugin_name        = $plugin_name;
		$this->version            = $version;
		$this->itap_page_settings = $itap_page_settings;

		add_action( 'admin_menu', array( $this, 'itap_add_menu' ) );

		$this->ajax_hooks();
	}

	/**
	 * Ajax hooks for the admin part of the plugin.
	 */
	public function ajax_hooks() {
		$itap_page_automation = new ItapPageAutomation();
		add_action( 'wp_ajax_get_checkbox_value', array( $this, 'itap_send_archive_to_db' ) );
		add_action( 'wp_ajax_delete_checkbox_value', array( $this, 'itap_delete_archive' ) );
		add_action( 'wp_ajax_itap_save_settings', array( $this->itap_page_settings, 'itap_save_settings' ) );
		add_action( 'wp_ajax_fix_primary_cat', array( $itap_page_automation, 'itap_fix_primary_cat' ) );
		add_action( 'wp_ajax_change_primary_category', array( $itap_page_automation, 'itap_change_primary_category' ) );
		add_action( 'wp_ajax_get_data_in_page_is_there_a_problem', array( $this, 'itap_get_errors' ) );
	}

	/**
	 *  Ajax function that send the archive to the database
	 *
	 * @return void
	 */
	public function itap_send_archive_to_db() {
		global $wpdb;
		$uniq_id = isset( $_POST['uniqId'] ) ? sanitize_text_field(wp_unslash( $_POST['uniqId'] ) ) : ''; // phpcs:ignore
		$seo     = isset( $_POST['seo'] ) ? sanitize_text_field(wp_unslash( $_POST['seo'] ) ) : ''; // phpcs:ignore
		if ( 'true' === $seo ) {
			$table_name = $wpdb->prefix . 'itap_seo_archive';
		} else {
			$table_name = $wpdb->prefix . 'itap_archive';
		}
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            uniqId varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$wpdb->insert(
			$table_name,
			array(
				'uniqId' => $uniq_id,
			)
		);

		wp_die();
	}

	/**
	 * Ajax function that delete the archive from the database
	 *
	 * @return void
	 */
	public function itap_delete_archive() {
		global $wpdb;
		$uniq_id    = isset( $_POST['uniqId'] ) ? sanitize_text_field(wp_unslash( $_POST['uniqId'] ) ) : ''; // phpcs:ignore
		$table_name = $wpdb->prefix . 'itap_archive';
		$wpdb->delete( $table_name, array( 'uniqId' => $uniq_id ) );
		wp_die();
	}


	/**
	 * Add menu to the admin area
	 */
	public function itap_add_menu(): void {
		global $wpdb;
		$total_seo_errors        = get_option( 'count_seo_errors' ) ? get_option( 'count_seo_errors' ) : 0;
		$total_seo_errors_string = 300 === $total_seo_errors ? '300+' : $total_seo_errors;

		add_menu_page( 'Problems', 'Problems', 'publish_pages', 'is_there_a_problem', array( $this, 'itap_page' ), 'dashicons-admin-site', 100 );
		add_submenu_page( 'is_there_a_problem', 'Integration', 'Integration', 'publish_pages', 'is_there_a_problem', array( $this, 'itap_page' ) );
		add_submenu_page( 'is_there_a_problem', 'SEO', sprintf( "SEO <span class='awaiting-mod'>%s</span>", $total_seo_errors_string ), 'publish_pages', 'is_there_a_problem_seo', array( $this, 'itap_page_seo' ) );
		add_submenu_page( 'is_there_a_problem', 'Automatisation', 'Automatisation', 'publish_pages', 'is_there_a_problem_automation', array( $this, 'itap_page_automation' ) );
		add_submenu_page( 'is_there_a_problem', 'Archives', 'Archives', 'publish_pages', 'is_there_a_problem_archive', array( $this, 'itap_page_archive' ) );
		add_submenu_page( 'is_there_a_problem', 'Reglages ', 'Reglages', 'publish_pages', 'itap_reglages', array( $this, 'itap_page_settings' ) );
		add_submenu_page( 'is_there_a_problem', 'Help', 'Help', 'publish_pages', 'help', array( $this, 'itap_page_help' ) );
	}

	/**
	 * Display the SEO page
	 */
	public function itap_page_seo(): void {
		$itap_page_seo = new ItapPageSeo();
		$itap_page_seo->itap_partials_seo();
	}

	/**
	 * Display the Automation page
	 */
	public function itap_page_automation(): void {
		$itap_page_automation = new ItapPageAutomation();
		$itap_page_automation->itap_partials_automation();
	}


	/**
	 * Display the Archive page
	 */
	public function itap_page_archive() {
		$itap_page_archive = new ItapPageArchive();
		$itap_page_archive->itap_partials_archive();
	}

	/**
	 * Display the help page
	 */
	public function itap_page_help(): void {
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/itap-help-display.php';
	}

	/**
	 * Display the Settings page
	 */
	public function itap_page_settings(): void {
		$this->itap_page_settings->itap_partials_settings();
	}

	/**
	 * Display Is there a problem main page
	 */
	public function itap_page(): void {
		if ( isset( $_GET['page'] ) && 'is_there_a_problem' === $_GET['page'] ) {
			require_once plugin_dir_path( __FILE__ ) . 'partials/itap-admin-display.php';
		}
	}

	/**
	 * Array of data that will be pass to each error to construct the final table of errors.
	 *
	 * @param array  $result array that represents a product that has a problem.
	 * @param string $problem the problem.
	 * @param string $code_error the code of the problem.
	 * @param string $color color of the error.
	 */
	public function itap_display_data( array $result, string $problem, string $code_error, string $color = '' ): array {
		$author_name = $this->author_name;
		if ( $author_name && $author_name !== $result['author_name'] ) {
			return array();
		}
		return array(
			'uniqId'      => $result['id'] . $code_error,
			'id'          => $result['id'],
			'title'       => $result['title'],
			'url'         => $result['url'],
			'author_name' => $result['author_name'],
			'imageUrl'    => $result['imageUrl'],
			'url_edit'    => get_edit_post_link( $result['id'] ),
			'alt'         => $result['alt'],
			'error'       => $problem,
			'color'       => $color,
		);
	}

	/**
	 * Get products with pagination and return them.
	 *
	 * @param int $page_number The page number for pagination, starting from 1.
	 * @return array The array of products.
	 */
	public function itap_get_all_infos_from_product( int $page_number ): array {
		$products_per_page = 100;
		$args              = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $products_per_page,
			'paged'          => $page_number,
			'orderby'        => 'id',
			'order'          => 'ASC',
		);
		$products          = get_posts( $args );
		$products_array    = array();
		foreach ( $products as $product ) {
			$product          = wc_get_product( $product->ID );
			$image_id         = get_post_thumbnail_id( $product->get_id() );
			$author_id        = get_post_field( 'post_author', $product->get_id() );
			$products_array[] = array(
				'id'          => $product->get_id(),
				'title'       => $product->get_title(),
				'url'         => $product->get_permalink(),
				'author_name' => get_the_author_meta( 'display_name', $author_id ),
				'imageUrl'    => wp_get_attachment_url( $image_id ) ?? '',
				'alt'         => get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ?? '',
			);
		}
		return $products_array;
	}


	/**
	 * Group all the functions from products error and loop on them to get all the errors.
	 *
	 * @param array $results list of all the products.
	 * @return array
	 */
	public function itap_get_errors_from_products( array $results ): array {
		$errors = array();
		foreach ( $results as $result ) {
			$functions = array(
				'itap_get_errors_from_links',
				'itap_get_errors_from_product_or_variations_without_price',
				'itap_get_errors_from_alt_descriptions',
				'itap_get_errors_from_variable_products',
				'itap_get_errors_from_images',
				'itap_get_errors_from_product_that_have_same_slug_of_his_category',
				'itap_get_errors_from_rank_math',
				'itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur',
				'itap_get_errors_from_descriptions',
				'itap_get_errors_from_missing_meta_fields_images',
				'get_errors_from_too_much_ia_in_content',
				'itap_get_errors_from_images_that_arent_stocked_in_wordpress',
				'itap_get_errors_if_there_are_spaces_after_the_block_text',
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
	 * If the product has a description that contain a forbidden expression that is used too often by the IA, return an error.
	 *
	 * @param array $result list of all the products.
	 * @return array
	 */
	public function get_errors_from_too_much_ia_in_content( array $result ): array {
		$errors            = array();
		$product           = wc_get_product( $result['id'] );
		$content           = $product->get_description();
		$short_description = $product->get_short_description();
		$description1      = $product->get_meta( 'description-1' ) ?? $product->get_meta( 'description1' ) ?? null;
		$description2      = $product->get_meta( 'description-2' ) ?? $product->get_meta( 'description2' ) ?? null;
		$description3      = $product->get_meta( 'description-3' ) ?? $product->get_meta( 'description3' ) ?? null;
		$all_description   = array(
			'description-1'          => $description1,
			'description-2'          => $description2,
			'description-3'          => $description3,
			'description principale' => $content,
			'description courte'     => $short_description,
		);

		$forbidden_expression = array( 'en outre', 'par conséquent', 'de surcroît', 'il convient de noter que', 'il est à noter que', 'il est impératif', 'en vertu de', 'au sein de', 'à la lumière de' );
		foreach ( $all_description as $description_name => $description ) {
			if ( ! $description ) {
				continue;
			}
			foreach ( $forbidden_expression as $expression ) {
				if ( str_contains( strtolower( $description ), $expression ) ) {
					$errors[] = $this->itap_display_data( $result, "Le champ '" . $description_name . "' du produit contient un contenu généré entièrement par l'IA, réécrivez le", '1027' );
					break;
				}
			}
		}
		return $errors;
	}

	/**
	 *
	 * List all the products that have a problem :
	 * - product that has a link in the description
	 * - product that has a div in the description
	 * - product that has a h1 in the description
	 *
	 * @param array $result list of all the products.
	 * @return array|void list of all the products that have a problem.
	 */
	public function itap_get_errors_from_links( array $result ) {
		$errors            = array();
		$product           = wc_get_product( $result['id'] );
		$description1      = $product->get_meta( 'description-1' ) ?? $product->get_meta( 'description1' ) ?? null;
		$description2      = $product->get_meta( 'description-2' ) ?? $product->get_meta( 'description2' ) ?? null;
		$description3      = $product->get_meta( 'description-3' ) ?? $product->get_meta( 'description3' ) ?? null;
		$main_description  = $product->get_description();
		$short_description = $product->get_short_description();

		$all_description  = array( $description1, $description2, $description3, $main_description, $short_description );
		$link_description = array_filter(
			$all_description,
			function ( $value ) {
				return str_contains( $value, 'href' );
			}
		);
		if ( count( $link_description ) > 0 ) {
			foreach ( $link_description as $link ) {
				preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>(?<content>.+?)<\/a>/i', $link, $matches );
				foreach ( $matches['href'] as $i => $match ) {
					if ( str_contains( $match, 'mailto' ) ) {
						$after_mailto = substr( $match, 7 );
						if ( $after_mailto !== $matches['content'][ $i ] ) {
							$errors[] = $this->itap_display_data( $result, "Produit qui contient un lien mailto dont la valeur du href n'est pas égale à la valeur de la balise lien", '1023', '#ff0e0e' );
							break;
						}
						continue;
					}
					$errors[] = $this->itap_display_data( $result, 'Description-1, description-2 ou description-3 ou description principale ou description courte du produit qui contient un lien', '1012', '#ff0e0e' );
					break;
				}
			}
		}

		$div_description = array_filter(
			$all_description,
			function ( $value ) {
				return str_contains( $value, '<div>' ) || str_contains( $value, '</div>' );
			}
		);
		if ( count( $div_description ) > 0 ) {
			$errors[] = $this->itap_display_data( $result, 'Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise div, effacez la', '1014', '#ff0e0e' );
		}

		$h1_description = array_filter(
			$all_description,
			function ( $value ) {
				return str_contains( $value, '<h1>' ) || str_contains( $value, '</h1>' );
			}
		);
		if ( count( $h1_description ) > 0 ) {
			$errors[] = $this->itap_display_data( $result, "Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise h1, il ne peut y en avoir qu'une sur la page produit qui correspond au titre, effacez la", '1018', '#ff0e0e' );
		}

		return $errors;
	}

	/**
	 * Return an error if a product has no alt text on the image, or alt text is too short.
	 *
	 * @param array $result product that possibly has a problem.
	 */
	public function itap_get_errors_from_alt_descriptions( array $result ): array {
		$errors = array();
		if ( '' === $result['alt'] ) {
			$errors[] = $this->itap_display_data( $result, 'Balise alt vide', '1001' );
		} elseif ( strlen( $result['alt'] ) < 10 ) {
			$errors[] = $this->itap_display_data( $result, 'Balise alt trop courte', '1002' );
		}
		$image_format = array( '.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg' );
		foreach ( $image_format as $format ) {
			if ( str_contains( $result['alt'], $format ) ) {
				$errors[] = $this->itap_display_data( $result, "Balise alt qui contient un format d'image (jpg , jpeg , gif, png , webp , svg), remplacer le par une vraie description de l'image", '1026' );
				break;
			}
		}
		return $errors;
	}

	/**
	 * Return an error if a product has no price.
	 *
	 * @param array $result product that possibly has a problem.
	 */
	public function itap_get_errors_from_product_or_variations_without_price( array $result ): array {
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
					$errors[] = $this->itap_display_data( $result, 'Produit variable dont une des variations ne contient pas de prix ', '1020' );
					break;
				}
			}
		} elseif ( $product->get_regular_price() == '' ) {
				$errors[] = $this->itap_display_data( $result, "Produit simple qui n'a pas de prix", '1021' );
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
	 *
	 * @param array $result product that possibly has a problem.
	 * @return array|void
	 */
	public function itap_get_errors_from_variable_products( array $result ) {
		$errors  = array();
		$product = wc_get_product( $result['id'] );
		if ( ! $product ) {
			return array();
		}
		if ( $product->is_type( 'variable' ) ) {
			$has_all_defaults = true;
			$attributes       = $product->get_variation_attributes();

			foreach ( $attributes as $attribute => $values ) {
				if ( ! array_key_exists( $attribute, $product->get_default_attributes() ) ) {
					$has_all_defaults = false;
					break;
				}
			}

			if ( ! $has_all_defaults ) {
				$errors[] = $this->itap_display_data( $result, "Produit variable qui n'a pas de produit par défaut", '1003' );
			}

			$attribute_variation = array();
			foreach ( $product->get_attributes() as $attribute ) {
				if ( $attribute['variation'] ) {
					$attribute_variation[] = $attribute;
				}
			}

			$couleurs = array( 'argente', 'beige', 'blanc', 'bleu', 'bleu-fonce', 'bordeaux', 'gris', 'jaune', 'bronze', 'marron', 'multicolore', 'noir', 'dore', 'orange', 'rose', 'rose-fonce', 'rouge', 'turquoise', 'vert', 'violet' );
			$couleurs = array_merge( $couleurs, $this->get_colors_from_settings() );
			foreach ( $attribute_variation as $attribute ) {
				$product_tag = wc_get_attribute( $attribute['id'] );
				if ( isset( $product_tag->name ) && strtolower( $product_tag->name ) === 'couleur' ) {
					$terms = wc_get_product_terms( $result['id'], $attribute['name'], array( 'fields' => 'slugs' ) );
					foreach ( $terms as $term ) {
						$term_slugify = $this->slugify( $term );
						if ( ! in_array( $term_slugify, $couleurs ) && $attribute['variation'] ) {
							$errors[] = $this->itap_display_data( $result, sprintf( "Produit variable dont la couleur %s ne fait pas partie des <div class='tooltip'>couleurs possibles<span class='tooltiptext'>%s</span></div>", esc_html( $term_slugify ), implode( ', ', $couleurs ) ), '1004' );
						}
					}
				}
			}

			$variations = $product->get_children();
			foreach ( $variations as $variation ) {
				$product_var          = wc_get_product( $variation );
				$variation_attributes = $product_var->get_attributes();
				$false_var            = 0;
				foreach ( $variation_attributes as $variation_attribute => $value ) {
					if ( ! $value ) {
						++$false_var;
					}
				}
				if ( $false_var ) {
					$errors[] = $this->itap_display_data( $result, "Produit variable dont une ou plusieurs de ses variations ne possède(nt) pas d'attributs", '1029', 'bm' );
					break;
				}
			}

			if ( count( $attribute_variation ) != count( $product->get_default_attributes() ) ) {
				$errors[] = $this->itap_display_data( $result, 'Produit variable ou il manque une ou plusieurs variations dans le produit par défaut', '1005' );
			}

			if ( count( $product->get_children() ) == 0 ) {
				$errors[] = $this->itap_display_data( $result, "Produit variable qui n'a pas de variations, ajoutez en ou passez le en produit simple", '1007' );
			}
		}
		return $errors;
	}


	/**
	 * Check if WordPress create enough images sizes
	 *
	 * @param array $result the product.
	 */
	public function itap_get_errors_from_images( array $result ): array {
		$errors       = array();
		$image_id     = get_post_thumbnail_id( $result['id'] );
		$errors_image = 0;
		if ( 0 === $image_id ) {
			$errors[] = $this->itap_display_data( $result, 'Produit sans images', '1008' );
		} else {
			$image_metadata = get_post_meta( $image_id, '_wp_attachment_metadata', 'true' );
			if ( ! $image_metadata || ! isset( $image_metadata['file'] ) || ! isset( $image_metadata['sizes'] ) ) {
				return $errors;
			}
			$upload_dir = wp_upload_dir()['basedir'];
			$base_path  = substr( $image_metadata['file'], 0, 8 );
			$image_path = $upload_dir . '/' . $base_path;
			foreach ( $image_metadata['sizes'] as $size ) {
				if ( ! file_exists( $image_path . $size['file'] ) ) {
					++$errors_image;
				}
			}
			if ( $errors_image > 0 ) {
				$errors[] = $this->itap_display_data( $result, "Formats d'images manquants/non créés par WordPress pour WooCommerce, merci de réuploader l'image du produit", '1009' );
			}
		}
		return $errors;
	}

	/**
	 * Check if there are images that comes from url.
	 *
	 * @param array $result the product.
	 */
	public function itap_get_errors_from_images_that_arent_stocked_in_wordpress( array $result ) {
		$errors    = array();
		$image_id  = get_post_thumbnail_id( $result['id'] );
		$image_url = wp_get_attachment_url( $image_id );

		if ( $image_url && ! $this->is_internal_url( $image_url ) ) {
			$errors[] = $this->itap_display_data( $result, "L'image du produit n'est pas stockée sur le serveur, veuillez la télécharger et l'uploader sur le serveur", '1030', '#ff0e0e' );
		}

		$gallery_images = get_post_meta( $result['id'], '_product_image_gallery', true );
		if ( ! empty( $gallery_images ) ) {
			$gallery_image_ids = explode( ',', $gallery_images );
			foreach ( $gallery_image_ids as $gallery_image_id ) {
				$gallery_image_url = wp_get_attachment_url( $gallery_image_id );
				if ( $image_url && ! $this->is_internal_url( $gallery_image_url ) ) {
					$errors[] = $this->itap_display_data( $result, "une image de la galerie du produit n'est pas stockée sur le serveur, veuillez la télécharger et l'uploader sur le serveur", '1031', '#ff0e0e' );
					break;
				}
			}
		}

		$itap_settings      = get_option( 'itap_settings' );
		$images_meta_fields = array();
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
		foreach ( $images_meta_fields as $label ) {
			$image_id  = get_post_meta( $result['id'], $label, true );
			$image_url = wp_get_attachment_url( $image_id );
			if ( $image_url && ! $this->is_internal_url( $image_url ) ) {
				$errors[] = $this->itap_display_data( $result, "L'image du champ meta field $label n'est pas stockée sur le serveur, veuillez la télécharger et l'uploader sur le serveur", '1032', '#ff0e0e' );
				break;
			}
		}

		$product = wc_get_product( $result['id'] );
		if ( $product->is_type( 'variable' ) ) {
			$variations = $product->get_children();
			foreach ( $variations as $variation ) {
				$variation_product   = wc_get_product( $variation );
				$variation_image_id  = get_post_thumbnail_id( $variation_product->get_id() );
				$variation_image_url = wp_get_attachment_url( $variation_image_id );
				if ( $variation_image_url && ! $this->is_internal_url( $variation_image_url ) ) {
					$errors[] = $this->itap_display_data( $result, "Produit variable dont au moins une des variations contient une image qui n'est pas stockée sur WordPress, veuillez la télécharger et l'uploader sur le serveur", '1034', '#ff0e0e' );
					break;
				}
			}
		}

		return $errors;
	}

	/**
	 * Get errors from rank math product description who hasn't been filled.
	 *
	 * @param array $result product that we want to check.
	 */
	public function itap_get_errors_from_rank_math( array $result ): array {
		$errors  = array();
		$product = wc_get_product( $result['id'] );
		if ( ! get_post_meta( $product->get_id(), 'rank_math_description', true ) ) {
			$errors[] = $this->itap_display_data( $result, 'Produit qui a une meta description automatique, personnalisez la', '1010' );
		}
		return $errors;
	}

	/**
	 * Check if the product have meta fields images filled.
	 *
	 * @param array $result the product that we want to check.
	 * @return array|void
	 */
	public function itap_get_errors_from_missing_meta_fields_images( array $result ): array {
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
			if ( ! get_post_meta( $product->get_id(), $label, true ) ) {
				$errors[] = $this->itap_display_data( $result, sprintf( 'Produit ou le meta field %s est vide, il doit être rempli', $label ), '1024' );
			}
		}
		return $errors;
	}


	/**
	 * Check in settings fields to search for errors :
	 * - if the product does not have enough words (total and per field)
	 *
	 * @param array $result product that we want to check.
	 */
	public function itap_get_errors_from_descriptions( array $result ): array {
		$errors                         = array();
		$product                        = wc_get_product( $result['id'] );
		$settings                       = get_option( 'itap_settings' );
		$total_words_min_short_desc     = $settings['total_words_min_short_desc'] ?? 50;
		$total_words_min_principal_desc = $settings['total_words_min_principal_desc'] ?? 60;
		$possible_desc                  = array(
			isset( $settings['desc1'] ) && $settings['desc1'] ? $product->get_meta( 'description-1' ) : ( $product->get_meta( 'description1' ) ?? null ),
			isset( $settings['desc2'] ) && $settings['desc2'] ? $product->get_meta( 'description-2' ) : ( $product->get_meta( 'description2' ) ?? null ),
			isset( $settings['desc3'] ) && $settings['desc3'] ? $product->get_meta( 'description-3' ) : ( $product->get_meta( 'description3' ) ?? null ),
			isset( $settings['desc_seo'] ) && $settings['desc_seo'] ? get_post_field( 'description-seo', $product->get_meta( 'description-categorie' ) ) : null,
		);

		$custom_field = isset( $settings['custom_field'] ) && $settings['custom_field'] ? array(
			isset( $settings['custom_field_input_1'] ) && $settings['custom_field_input_1'] ? $product->get_meta( $settings['custom_field_input_1'] ) : null,
			isset( $settings['custom_field_input_2'] ) && $settings['custom_field_input_2'] ? $product->get_meta( $settings['custom_field_input_2'] ) : null,
			isset( $settings['custom_field_input_3'] ) && $settings['custom_field_input_3'] ? $product->get_meta( $settings['custom_field_input_3'] ) : null,
		) : array();

		$possible_desc = array_merge( $possible_desc, $custom_field );
		$possible_desc = array_filter( $possible_desc );

		$total_words_min_page  = $settings['total_words_min_page'] ?? 200;
		$total_words_min_block = $settings['total_words_min_block'] ?? 60;
		$total_count           = 0;
		$error_check           = false;

		foreach ( $possible_desc as $field ) {
			$total_words = $this->utf8_word_count( wp_strip_all_tags( $field ) );
			if ( $total_words < $total_words_min_block && ! $error_check ) {
				$errors[]    = $this->itap_display_data( $result, sprintf( "Chaque champ d'une page produit dont le nom est coché dans les paramètres du plugin doit avoir plus de %s mots, rajoutez en plus", $total_words_min_block ), '1015' );
				$error_check = true;
			}
			$total_count += $total_words;
		}

		$total_count += $this->utf8_word_count( wp_strip_all_tags( $product->get_short_description() ) );

		if ( $total_count < $total_words_min_page ) {
			$errors[] = $this->itap_display_data( $result, sprintf( 'La page du produit contient moins de %s mots, le compte est calculé grâce à la somme de tous les champs cochés dans les paramètres + description courte', $total_words_min_page ), '1016' );
		}
		$product_short_desc = wp_strip_all_tags( $product->get_short_description() );
		$product_short_desc = str_replace( array( "\n", "\r" ), '', $product_short_desc );
		if ( $this->utf8_word_count( $product_short_desc ) > $total_words_min_short_desc ) {
			$errors[] = $this->itap_display_data( $result, 'La description courte du produit doit être inférieure à ' . $total_words_min_short_desc . ' mots, enlevez du contenu', '1025' );
		}

		$product_desc = wp_strip_all_tags( $product->get_description() );
		$product_desc = str_replace( array( "\n", "\r" ), '', $product_desc );
		if ( $this->utf8_word_count( $product_desc ) > $total_words_min_principal_desc ) {
			$errors[] = $this->itap_display_data( $result, 'La description principale du produit (sous le titre) doit être inférieure à ' . $total_words_min_principal_desc . ' mots, enlevez du contenu', '1027' );
		}

		return $errors;
	}

	/**
	 * Return an error if a product has spaces after the block text.
	 *
	 * @param array $result product that we want to check.
	 * @return array  of all errors
	 */
	public function itap_get_errors_if_there_are_spaces_after_the_block_text( array $result ) {
		$errors            = array();
		$product           = wc_get_product( $result['id'] );
		$description1      = $product->get_meta( 'description-1' ) ?? $product->get_meta( 'description1' ) ?? null;
		$description2      = $product->get_meta( 'description-2' ) ?? $product->get_meta( 'description2' ) ?? null;
		$description3      = $product->get_meta( 'description-3' ) ?? $product->get_meta( 'description3' ) ?? null;
		$main_description  = $product->get_description();
		$short_description = $product->get_short_description();
		$all_description   = array(
			'description-1'          => $description1,
			'description-2'          => $description2,
			'description-3'          => $description3,
			'description principale' => $main_description,
			'description courte'     => $short_description,
		);
		foreach ( $all_description as $description_name => $description ) {
			if ( ! $description ) {
				continue;
			}
			$regex               = '/<p>\s*<\/p>/';
			$clean_content       = preg_replace( $regex, '', $description );
			$regex               = '/<\/p>\s*<\/p>\s*/';
			$super_clean_content = preg_replace( $regex, '', $clean_content );

			$description_text = html_entity_decode( rtrim( wp_strip_all_tags( $super_clean_content ), "\n" ) );
			$last_character   = mb_substr( $description_text, -1 );

			if ( ' ' === $last_character || "\xC2\xA0" === $last_character || "\xA0" === $last_character ) {
				$errors[] = $this->itap_display_data( $result, "Le champ $description_name de ce produit se termine par un espace, merci de le supprimer", '1033' );
			}
		}

		return $errors;
	}


	/**
	 * Error if the product has a variation with only one color.
	 *
	 * @param array $result product that we want to check.
	 */
	public function itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur( array $result ): array {
		$errors  = array();
		$product = wc_get_product( $result['id'] );
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			return $errors;
		}
		foreach ( $product->get_attributes() as $product_attribute ) {
			$product_tag = wc_get_attribute( $product_attribute['id'] );
			if ( isset( $product_tag->name ) && strtolower( $product_tag->name ) === 'couleur' && count( $product_attribute['options'] ) == 1 && $product_attribute['variation'] ) {
				$errors[] = $this->itap_display_data( $result, 'Le produit a une seule couleur,pas besoin de variations, décocher la case "utiliser pour les variations" pour la couleur', '1020' );
				break;
			}
		}
		return $errors;
	}


	/**
	 * Get the errors from all the functions that check the problems.
	 */
	public function itap_get_errors() {
		global $wpdb;
		set_time_limit( 300 );
		$page_number = isset( $_POST['pageNumber'] ) ? sanitize_text_field(wp_unslash( $_POST['pageNumber'] ) ) : ''; // phpcs:ignore
		$author_name = isset( $_POST['authorName'] ) ? sanitize_text_field(wp_unslash( $_POST['authorName'] ) ) : ''; // phpcs:ignore
		if ( $author_name ) {
			$this->author_name = $author_name;
		}
		if ( ! $page_number ) {
			wp_send_json_error( 'No page number provided' );
		}

		$results = $this->itap_get_all_infos_from_product( $page_number );

		if ( empty( $results ) ) {
			wp_send_json_success(
				array(
					'errors'   => array(),
					'continue' => false,
				)
			);
			wp_die();
		}

		$errors = $this->itap_get_errors_from_products( $results );

		$errors_filter = array_filter(
			$errors,
			function ( $error ) {
				return ! empty( $error );
			}
		);

		usort(
			$errors_filter,
			function ( $a, $b ) {
				return $a['color'] ? -1 : 1;
			}
		);

		$table_name      = $wpdb->prefix . 'itap_archive';
		$uniq_ids        = $wpdb->get_results( "SELECT uniqId FROM $table_name ORDER BY id" ); // phpcs:ignore
		$error_displayed = array();

		foreach ( $errors_filter as $error ) {
			if ( ! in_array( array( 'uniqId' => $error['uniqId'] ), $uniq_ids ) ) {
				$error_displayed[] = $this->itap_display_tab( $error );
			}
		}
		wp_send_json_success(
			array(
				'errors'   => $error_displayed,
				'continue' => true,
			)
		);
		wp_die();
	}


	/**
	 * Display one table row every time there is an error.
	 *
	 * @param array $error  that represents a product that has a problem.
	 */
	public function itap_display_tab( array $error ) {
		$allowed_html   = array(
			'div'  => array(
				'class' => array(),
			),
			'span' => array(
				'class' => array(),
			),
		);
		$big_mistake    = 'bm' === $error['color'];
		$middle_mistake = ! empty( $error['color'] ) ? 'mistake' : '';
		ob_start();
		?>
		<tr <?php printf( 'style="%s"', esc_attr( $error['color'] ? 'background-color:' . $error['color'] . ';color:white;' : '' ) ); ?> class="<?php echo $big_mistake ? 'bm_animation' : ''; ?> <?php echo esc_attr( $middle_mistake ); ?>">
			<td><?php echo esc_html( $error['id'] ); ?></td>
			<td><?php echo esc_html( $error['title'] ); ?></td>
			<td><a target="_blank" <?php printf( 'style="%s"', esc_attr( $error['color'] ? 'color:white' : '' ) ); ?>
					href="<?php echo esc_url( $error['url_edit'] ); ?>">click</a></td>
			<td><?php echo wp_kses( $error['error'], $allowed_html ); ?></td>
			<td><?php echo esc_html( $error['author_name'] ); ?></td>
			<td><input type="checkbox" class="itap_checkbox archiver" data-archive="integration" name="archiver"
						value="<?php echo esc_attr( $error['uniqId'] ); ?>"></td>
		</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Check if product has same slug of his category.
	 *
	 * @param array $result list of all the products.
	 */
	public function itap_get_errors_from_product_that_have_same_slug_of_his_category( array $result ): array {
		$errors     = array();
		$product    = wc_get_product( $result['id'] );
		$categories = wp_get_post_terms( $result['id'], 'product_cat', array( 'fields' => 'slugs' ) );
		$slug       = $product->get_slug();

		if ( in_array( $slug, $categories ) ) {
			$errors[] = $this->itap_display_data( $result, "le slug d'un produit ne peut pas être le même qu'une de ses catégories", '1011' );
		}
		return $errors;
	}



	/**
	 * Check in the settings if there are additionnal colors to check for the errors.
	 */
	public function get_colors_from_settings() {
		$options = get_option( 'itap_settings' );
		if ( ! empty( $options['colors'] ) ) {
			$colors = $options['colors'];
			$colors = explode( '/', $colors );
			return array_map( 'trim', $colors );
		} else {
			return array();
		}
	}


	/**
	 * Enqueue the stylesheets for the admin area.
	 */
	public function enqueue_styles(): void {
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $this->plugin_pages ) ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __DIR__ ) . 'admin/assets/css/itap.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Enqueue the Script for the admin area.
	 */
	public function enqueue_scripts(): void {
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $this->plugin_pages ) ) {
			wp_enqueue_script( 'isthereaproblemJS', plugin_dir_url( __DIR__ ) . 'admin/assets/js/itap.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( 'isthereaproblemJS', 'my_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}
	}
}
