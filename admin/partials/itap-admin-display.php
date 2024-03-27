<?php
global $wpdb;
$admins      = get_users( array('role__in' => array('administrator' , 'shop_manager')) );
$author_name = isset( $_GET['author_name'] ) ? urldecode( $_GET['author_name'] ) : '';
?>

<div class="wrap is-there-a-problem-container">
    <p>Problèmes d'intégration</p>
    <div class="form-tri">
        <label for="author_name">Trier par intégrateur</label>
        <form action="?page=is_there_a_problem" method="GET">
            <div class="bloc-form">
                <div>
                    <input type="hidden" name="page" value="is_there_a_problem">
                    <select name="author_name" id="author_name">
                        <option value="">choisissez votre nom d'utilisateur</option>
                        <?php
                        foreach ( $admins as $user ) {
                            $selected = $author_name === $user->display_name ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $user->display_name ); ?>"
                                    name="" <?php echo $selected ?>><?php echo esc_attr( $user->display_name ); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <button type="submit" class="itap_button" value="Rechercher">Rechercher</button>
                </div>
            </div>
        </form>
    </div>
    <div class="loader-div">
        <span class="loader"></span>
        <p class="search-error" style="margin-right: 1rem">Recherche d'erreurs...</p>
    </div>
    <p class="nbr-error">Nombre d'erreurs : <span class="nbr">0</span></p>
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
        <tbody class="tbody-plugin" id="display-errors">
        </tbody>
    </table>
</div>