<div class="wrap is-there-a-problem-container">
    <p>Problèmes liés au référencement naturel</p>

    <table class="table-plugin">
        <thead>
        <tr class="thead-plugin">
            <th>Id</th>
            <th>Nom</th>
            <th>Url</th>
            <th>Problème remonté</th>
        </tr>
        </thead>
        <tbody class="tbody-plugin">
        <?php
        $this->get_errors_from_seo( array($this , 'itap_get_rank_math_opengraph_thumbnail') , array($this , 'itap_seo_display_tab') );
        $this->get_errors_from_seo( array($this , 'itap_get_errors_from_meta_title') , array($this , 'itap_seo_display_tab') );
        $this->get_errors_from_seo( array($this , 'itap_get_errors_nofollow_link') , array($this , 'itap_seo_display_tab_links') );
        $this->get_errors_from_seo( array($this , 'itap_get_errors_no_categories_description') , array($this , 'itap_seo_display_tab') );
        $this->get_errors_from_seo( array($this , 'itap_get_errors_below_category_content') , array($this , 'itap_seo_display_tab') );
        $this->get_errors_from_seo( array($this , 'itap_get_errors_no_tags_description') , array($this , 'itap_seo_display_tab') );
        ?>
        </tbody>
    </table>
</div>