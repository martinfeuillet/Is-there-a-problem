<?php
global $wpdb;
$table_name = $wpdb->prefix . 'itap_archive';
$count      = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
$admins     = get_users( array('role__in' => array('administrator' , 'shop_manager')) );
$results    = $this->itap_get_all_infos_from_product();
if ( ! empty( $_GET['author_name'] ) ) {
    $results = array_filter( $results , function ( $result ) {
        return $result['author_name'] == $_GET['author_name'];
    } );
}
$total_integration_errors = count( $this->itap_get_errors_from_links( $results ) ) + count( $this->itap_get_errors_from_alt_descriptions( $results ) ) + count( $this->itap_get_errors_from_variable_products( $results ) ) + count( $this->itap_get_errors_from_images( $results ) ) + count( $this->itap_get_errors_from_rank_math( $results ) ) + count( $this->itap_get_errors_from_descriptions( $results ) ) + count( $this->itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur( $results ) ) - $count;
update_option( 'total_integration_errors' , $total_integration_errors );
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
                        //get all admin and shop manager
                        foreach ( $admins as $user ) {
                            ?>
                            <option value="<?php echo esc_attr( $user->display_name ); ?>"
                                    name=""><?php echo esc_attr( $user->display_name ); ?></option>
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
        $this->itap_get_errors( 'itap_get_errors_from_links' , $results , '#DC3444' );
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_get_errors_from_alt_descriptions' , $results );
        }
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_get_errors_from_variable_products' , $results );
        }
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_get_errors_from_images' , $results );
        }
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_get_errors_from_rank_math' , $results );
        }
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur' , $results );
        }
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_get_errors_from_descriptions' , $results );
        }
        if ( $this->lines < 290 ) {
            $this->itap_get_errors( 'itap_dont_allow_variation_if_only_one_attr_is_set_on_couleur' , $results );
        }
        if ( ! $total_integration_errors ) {
            echo wp_kses( "<tr><td colspan='5' class='congrats-plugin'>Aucune erreur détéctée , félicitations</td></tr>" , array('td' => array('colspan' => array()) , 'tr' => array('class' => array())) );
        }
        ?>

        </tbody>
    </table>
</div>