<div class="wrap is-there-a-problem-container">
    <p>Problèmes archivés</p>
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
        $archive = $this->itap_get_archive_from_database();
        foreach ( $archive as $error ) {
            echo $this->itap_archive_display_tab( $error );
        }
        ?>
        </tbody>
    </table>
</div>