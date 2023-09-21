<?php
global $wpdb;
$table_archive  = $wpdb->prefix . 'itap_archive';
$count_archives = $wpdb->get_var( "SELECT COUNT(*) FROM $table_archive" );
$admins         = get_users( array('role__in' => array('administrator' , 'shop_manager')) );
$results        = $this->itap_get_all_infos_from_product();
if ( ! empty( $_GET['author_name'] ) ) {
    $results = array_filter( $results , function ( $result ) {
        return $result['author_name'] == $_GET['author_name'];
    } );
}
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
        $this->itap_get_errors( 'itap_get_errors_from_products' , $results );
        ?>
        </tbody>
    </table>
    <p>Nombre d'erreurs : <?php echo $this->nb_errors; ?></p>
</div>