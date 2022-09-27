<?php
$couleurs = array('rouge', 'bleu', 'vert', 'jaune', 'noir', 'blanc', 'gris', 'marron', 'orange', 'rose', 'violet', 'multicolore', 'kaki', 'fuchsia', 'doré', 'camouflage', 'camel', 'bordeaux', 'beige', 'argenté');


$codeErrorFile = array(
    '1001' => "Balise alt vide",
    '1002' => "Balise alt trop courte",
    '1003' => "Produit variable qui n'a pas de produit par défaut",
    '1004' => 'Produit variable dont la couleur définie ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode(", ", $couleurs) . '</span></div>',
    '1005' => "Produit variable ou il manque une ou plusieurs variations dans le produit par défaut",
    '1006' => 'Produit variable dont le produit par défaut à comme variation des valeurs qui ne sont pas les premieres de leurs <div class="tooltip">catégories<span class="tooltiptext">Erreur qui signale également les attributs remplis à la volée directement sur la page du produit, merci de rentrer tous les attributs et leurs termes dans l\'onglet attribut de produit</span></div>',
    '1007' => "Produit variable qui n\'a pas de variations, ajoutez en ou passez le en produit simple",
    '1008' => "Produit sans images",
    '1009' => "Formats d'images manquants/non créés par WordPress pour WooCommerce, merci de réuploader l'image du produit",
    '1010' => "Produit associé à Rank Math qui n'a pas de meta description",
    '1011' => "le slug d'un produit ne peut pas être le même qu'une de ses catégories",
    '1012' => "Description-1 ou description-2 ou description principale ou description courte du produit qui contient un lien",
    '1013' => "Description-1 + description-2 + description courte du produit inférieures à 200 mots, mettez plus de contenu",
);
