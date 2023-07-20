<div class="wrap">
    <div class="wrap-help">
        <h1>Explication Problèmes</h1>
        <ul>
            <li>
                <input type="checkbox" checked>
                <i></i>
                <h2>Partie Integration</h2>
                <div class="accordeon">
                    <p class="itap-error">- Produit qui contient un lien mailto dont la valeur du href n'est pas égale à
                        la
                        valeur de la balise lien</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, sur chaque éditeur, activer la vue texte, vous devez avoir un
                        lien
                        du type <code>&lt;a
                            href="mailto:monmail@pt.fr"&gt;Contactez-nous&lt;/a&gt;</code> dans l'un d'eux.
                        Dans cet exemple, remplacer "contactez-nous" par monmail@pt.fr.
                    </p>
                    <p class="itap-error">- Description-1, description-2 ou description-3 ou description principale ou
                        description courte du produit qui contient un lien</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, sur chaque éditeur, activer la vue texte, vous devez avoir un
                        lien
                        du type <code>&lt;a href=""&gt;&lt;/a&gt;</code>
                        dans l'un d'eux. Supprimer le lien.
                    </p>
                    <p class="itap-error">- Description-1, description-2,description-3, description principale ou
                        description
                        courte du produit qui contient une balise div, effacez la.</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, sur chaque éditeur, activer la vue texte, vous devez avoir
                        une balise du type <code>&lt;div&gt;&lt;/div&gt;</code>
                        dans l'un d'eux. Supprimer la balise ouvrante et fermante, ainsi que le texte à l'intérieur, si
                        celui ci n'aide pas à la description du produit. L'erreur peut aussi venir du
                        fait que
                        vous ayez des espaces en dessous de votre titre principal. Pour régler l'erreur, cliquer dans
                        l'editeur en dessous du titre et supprimer les espaces jusqu'a atteindre le titre
                    </p>
                    <p class="itap-error">- Description-1, description-2,description-3,description principale ou
                        description
                        courte du produit qui contient une balise h1, il ne peut y en avoir qu'une sur la page produit
                        qui
                        correspond au titre, effacer la</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, sur chaque éditeur, activer la vue texte, vous devez avoir
                        une balise du type <code>&lt;h1&gt;&lt;/h1&gt;</code>
                        dans l'un d'eux. Supprimer la balise ou modifiez celle ci en h2 ou h3.
                    </p>
                    <p class="itap-error">- Produit variable dont une des variations ne contient pas de prix</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, aller dans l'onglet variation, vous devez avoir une variation
                        qui ne
                        contient pas de prix. Ajouter un prix.
                    </p>
                    <p class="itap-error">- Produit simple qui n'a pas de prix</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, aller dans les données du produit onglet général, ajouter un
                        prix.
                    </p>
                    <p class="itap-error">- Balise alt vide</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, cliquer sur image produit à droite, celle ci n'a pas de texte
                        alternatif, ajouter en un.
                    </p>
                    <p class="itap-error">- Balise alt trop courte</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, cliquer sur image produit à droite, celle ci a un texte
                        alternatif
                        en dessous de 10 charactères, allonger la.
                    </p>
                    <p class="itap-error">- Produit variable qui n'a pas de produit par défaut</p>
                    <p class="itap-error">- Produit variable ou il manque une ou plusieurs variations dans le produit
                        par
                        défaut</p>
                    <p class="itap-error">- Produit variable dont le produit par défaut à comme variation des valeurs
                        qui ne
                        sont pas les premieres de leurs catégories</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, aller dans l'onglet variation, vous devez avoir une valeur de
                        formulaire par défaut qui n'est pas définie / pas la premiere de sa liste. Selectionner la
                        premiere
                        valeur de chaque attribut dans
                        celle ci.
                    </p>
                    <p class="itap-error">- Produit variable qui n'a pas de variations, ajoutez en ou passez le en
                        produit
                        simple</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, aller dans l'onglet attributs. Vous devez avoir au moins un
                        attribut
                        qui est utilisé en variation et malheureusement aucune variation n'est définie. Vous pouvez soit
                        ajouter des variations soit passer le produit en produit simple.
                    </p>
                    <p class="itap-error">- Produit sans images</p>
                    <p class="itap-answer">
                        Quand vous êtes sur le produit, cliquer sur image produit à droite, celle ci n'a pas d'image,
                        ajouter
                        en une.
                    </p>
                    <p class="itap-error">- Formats d'images manquants/non créés par WordPress pour WooCommerce, merci
                        de
                        réuploader l'image du produit</p>
                    <p class="itap-answer">
                        Certains formats d'images n'ont pas été créés par WordPress pour WooCommerce, surement du à une
                        image trop petite ou de mauvaise qualité. Cela risque de poser des problèmes d'affichage sur le
                        site. Pour régler ce problème, il faut réuploader une image en bonne qualité du produit.
                    </p>
                    <p class="itap-error">- le slug d'un produit ne peut pas être le même qu'une de ses catégories</p>
                    <p class="itap-answer">
                        Un produit "t-shirt" ne peut pas être dans une catégorie "t-shirt". Il faut changer le nom/slug
                        du
                        produit ou le mettre dans une autre catégorie.
                    </p>
                    <p class="itap-error">- Produit qui a une meta description automatique, personnalisez la</p>
                    <p class="itap-answer">
                        Sur la fiche produit, en haut à droite de votre écran, à coté du bouton "mettre à jour", vous
                        avez
                        un bouton "Rank Math". Cliquer ensuite sur "modifier extrait" et personnalisez la meta
                        description.Celle ci doit décrire en quelques mots le produit et contenir le mot clé principal.
                        un bouton
                    </p>
                    <p class="itap-error">- Le produit a une seule couleur,pas besoin de variations, décocher la case
                        "utiliser pour les variations" pour la couleur</p>
                    <p class="itap-answer">
                        Sur la fiche produit, dans l'onglet "attributs", vous avez un attribut "couleur" qui est utilisé
                        pour les variations. Si le produit n'a qu'une seule couleur, il n'est pas nécessaire d'utiliser
                        les
                        variations. Décocher la case "utiliser pour les variations" pour l'attribut couleur.
                    </p>
                    <p class="itap-error">- La page du produit contient moins de x mots, le compte est calculé grâce à
                        la
                        somme de tous les champs cochés dans les paramètres</p>
                    <p class="itap-answer">
                        Sur la fiche produit, tous les editeurs doivent être remplis. La somme de tous les mots de ces
                        éditeurs doit être supérieure au nombre de mots défini dans les paramètres du plugin. Il y a une
                        marge
                        d'erreur de un ou 2 mots, si jamais vous avez atteint le nombre de mots défini dans les
                        paramètres,
                        et que vous avez quand même l'erreur, essayez de rajouter un ou deux mots en plus.
                    </p>
                    <p class="itap-error">- La description courte du produit doit être inférieure à 50 mots, enlevez du
                        contenu</p>
                    <p class="itap-answer">
                        A cause d'une erreur de visualisation sur portable qui fait sortir le bouton d'achat de la zone
                        d'écran, nous limitons désormais la description courte à 50 mots. Si vous avez plus de 50 mots,
                        enlevez en.
                    </p>
                    <p class="itap-error">- Balise alt qui contient un format d'image (jpg , jpeg , png , webp ,
                        svg), remplacer le par une vraie description de l'image</p>
                    <p class="itap-answer">
                        Nous avons remarqués que certaines balises alt correspondent à leur nom de fichier (ex :
                        image22.jpg). Il faut remplacer ces balises alt par une vraie description de l'image.
                    </p>
                </div>
            </li>
            <li>
                <input type="checkbox" checked>
                <i></i>
                <h2>Partie SEO</h2>
                <div class="accordeon">
                    <p class="itap-error">- Catégorie qui n'as pas de contenu et de liens dans le meta-field "Texte
                        dessous catégorie de produits</p>
                    <p class="itap-error">- Catégorie qui ne contient pas de liens dans le meta-field "Texte dessous
                        catégorie de produits</p>
                    <p class="itap-error">- Catégorie qui contient moins de 800 mots dans le meta-field "Texte dessous
                        catégorie de produits</p>
                    <p class="itap-answer">
                        Dans la catégorie, en dessous de la description, vous avez un champ "Texte dessous catégorie de
                        produits". Il faut mettre du contenu dans ce champ (minimum 800 mots) et ajouter des liens vers
                        d'autres catégories
                        ou vers des produits de la catégorie.
                    </p>
                    <p class="itap-error">- Lien externe dans le meta-field "Texte dessous catégorie de produits</p>
                    <p class="itap-answer">
                        Dans la catégorie, en dessous de la description, vous avez un champ "Texte dessous catégorie de
                        produits". Vous devez avoir un lien qui redirige vers un site externe. Il faut supprimer ce
                        lien.
                    </p>
                    <p class="itap-error">- description sous catégorie produit qui contient un lien vers un produit qui
                        n'est pas dans la catégorie,sous-catégorie ou sur-catégorie actuelle</p>
                    <p class="itap-answer">
                        Vous devez avoir un lien qui redirige vers un produit qui est dans la catégorie, sous-catégorie
                        ou sur-catégorie actuelle. Par exemple, si vous êtes sur la catégorie "t-shirt", vous ne pouvez
                        pas avoir un lien qui redirige vers un produit qui est dans la catégorie "pantalon". Il faut
                        supprimer ce lien.
                    </p>
                    <p class="itap-error">- description sous catégorie produit qui contient un lien vers une catégorie
                        latérale dont le parent n'est pas le même</p>
                    <p class="itap-answer">
                        Par exemple, si vous êtes sur une categorie "t-shirt rouge" qui a une catégorie parente
                        "t-shirt", vous pouvez
                        faire un lien vers une catégorie "tee-shirt-jaune", elle même enfant de la catégorie
                        "tee-shirt". Par contre, vous ne pouvez pas faire un lien vers une catégorie "pantalon" ou
                        "pantalon-jaune", qui n'est pas un enfant de la catégorie "tee-shirt".
                    </p>
                    <p class="itap-error">- description sous catégorie produit qui contient un lien vers une autre
                        catégorie produit qui n'est pas son enfant, ni son parent direct</p>
                    <p class="itap-answer">
                        Par exemple, si vous êtes sur une categorie "t-shirt rouge" qui a une catégorie parente
                        "t-shirt", vous pouvez faire un lien vers la catégorie parent ou vers une catégorie
                        "tee-shirt-rouge-carreaux", elle même enfant de la catégorie "tee-shirt-rouge". Par contre, vous
                        ne pouvez pas faire un lien vers une catégorie "pantalon" ou "pantalon-jaune", qui n'est pas un
                        enfant ou un parent direct de la catégorie "tee-shirt rouge".
                    </p>
                    <p class="itap-error">- le meta-field 'Texte dessous catégorie de produits' doit contenir des
                        balises h2 (ou Titre 2), celle ci n'en contient pas</p>
                    <p class="itap-answer">
                        Dans la catégorie, en dessous de la description, vous avez un champ "Texte dessous catégorie de
                        produits". Il faut mettre des balises h2 (ou Titre 2) dans ce champ. Ces titres sont les plus
                        importants après le h1 (ou Titre 1). Il faut donc les utiliser pour structurer votre texte.
                    </p>
                    <p class="itap-error">- le meta-field 'Texte dessous catégorie de produits' doit contenir des titres
                        tous les 300 mots, celle ci n'en contient pas</p>
                    <p class="itap-error">- le meta-field 'Texte dessous catégorie de produits' doit contenir des titres
                        (h1,h2,h3...) dans l'ordre, certaines balises ne respectent pas cette hiérarchie</p>
                    <p class="itap-answer">
                        Dans la catégorie, en dessous de la description, vous avez un champ "Texte dessous catégorie de
                        produits". Dans les règles de référencement naturel, il faut structurer son texte avec des
                        titres tous les 300 mots. Il faut donc ajouter des titres dans votre texte.
                        De plus il faut respecter la hiérarchie des titres. Le titre le plus important est le h1 (ou
                        Titre 1), ensuite le h2 (ou Titre 2), puis le h3 (ou Titre 3) etc... . Vous ne pouvez pas avoir
                        de h3 juste après un h1, ou de h4 après un h2 etc... . Vous devez respecter cette hiérarchie
                        lorsque
                        vous rédigez vos textes.
                    </p>
                    <p class="itap-error">- Lien mailto avec un contenu différent de l'adresse mail</p>
                    <p class="itap-answer">
                        Dans la catégorie, en dessous de la description, vous avez un champ "Texte dessous catégorie de
                        produits". Quand vous passez en mode texte, vous devez avoir un lien mailto <code>&lt;a
                            href="mailto:monmail@pt.fr"&gt;Contactez-nous&lt;/a&gt;</code>. Vous devez toujours avoir la
                        même valeur dans le href et dans le contenu du lien.
                        Dans cet exemple, remplacer "contactez-nous" par monmail@pt.fr.
                    </p>
                    <p class="itap-error">- La catégorie x n'est pas présente dans le menu principal</p>
                    <p class="itap-error">- La catégorie x est présente dans le menu principal mais celle ci est vide,
                        enlevez la ou rajoutez lui des produits</p>
                    <p class="itap-answer">
                        Toutes les catégories de produit doivent être présentes dans le menu principal (sauf si celle ci
                        est vide). Direction
                        apparence -> menu pour ajouter la catégorie au menu principal.
                    </p>
                    <p class="itap-error">- Chaque catégorie doit avoir une meta description</p>
                    <p class="itap-answer">
                        Dans la catégorie, en dessous de la description, vous avez un champ "Rank Math". Cliquer sur
                        "modifier l'extrait" et ajouter une description de la catégorie.
                    </p>
                    <p class="itap-error">- Le slug de la categorie ne doit pas contenir de chiffre</p>
                    <p class="itap-answer">
                        Ici c'est une erreur pour éviter la nomenclature d'Alidropship dans l'url.Pour ce qui est des
                        attributs de type (df696df198707592f832b1), archiver l'erreur. De même pour les slug de type
                        "trottinette-à-2-roues", vous archivez.
                    </p>
                    <p class="itap-error">- Le champ X contient un contenu généré entièrement par l'IA, réécrivez le</p>
                    <p class="itap-answer">
                        Avec l'apparition de l'IA, trop de contenu sont générés automatiquement. Google ne va pas tarder
                        à sanctionner les sites qui utilisent ce genre de contenu. Il faut donc réécrire le contenu.
                    </p>
                </div>
            </li>
        </ul>
    </div>
</div>
