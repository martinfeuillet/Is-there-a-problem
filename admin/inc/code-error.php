<?php
/**
 * This file contains the code for handling error messages.
 *
 * @package Is_There_A_Problem
 */

$couleurs              = array( 'argente', 'beige', 'blanc', 'bleu', 'bleu-fonce', 'bordeaux', 'gris', 'jaune', 'bronze', 'marron', 'multicolore', 'noir', 'dore', 'orange', 'rose', 'rose-fonce', 'rouge', 'turquoise', 'vert', 'violet' );
$settings              = get_option( 'itap_settings' );
$total_words_min_page  = $settings['total_words_min_page'] ?? 200;
$total_words_min_block = $settings['total_words_min_block'] ?? 60;

$code_error_file = array(
	'1001' => 'Balise alt vide',
	'1002' => 'Balise alt trop courte',
	'1003' => "Produit variable qui n'a pas de produit par défaut",
	'1004' => 'Produit variable dont la couleur définie ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode( ', ', $couleurs ) . '</span></div>',
	'1005' => 'Produit variable ou il manque une ou plusieurs variations dans le produit par défaut',
	'1006' => 'Produit variable dont le produit par défaut à comme variation des valeurs qui ne sont pas les premieres de leurs <div class="tooltip">catégories<span class="tooltiptext">Erreur qui signale également les attributs remplis à la volée directement sur la page du produit, merci de rentrer tous les attributs et leurs termes dans l\'onglet attribut de produit</span></div>',
	'1007' => "Produit variable qui n\'a pas de variations, ajoutez en ou passez le en produit simple",
	'1008' => 'Produit sans images',
	'1009' => "Formats d'images manquants/non créés par WordPress pour WooCommerce, merci de réuploader l'image du produit",
	'1010' => "Produit qui n'a pas de meta description",
	'1011' => "le slug d'un produit ne peut pas être le même qu'une de ses catégories",
	'1012' => 'Description-1 ou description-2 ou description principale ou description courte du produit qui contient un lien',
	'1013' => 'Description-1 + description-2 + description courte du produit inférieures à 200 mots, mettez plus de contenu',
	'1014' => 'Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise <div>, effacez la',
	'1015' => 'Chaque champ d\'une page produit dont le nom est coché dans les paramètres du plugin doit avoir plus de ' . $total_words_min_block . ' mots, rajoutez en plus',
	'1016' => 'La page du produit contient moins de ' . $total_words_min_page . ' mots, le compte est calculé grâce à la somme de tous les champs cochés dans les paramètres',
	'1017' => 'produit qui ne contient pas de schema rank math',
	'1018' => 'Description-1, description-2,description-3,description principale ou description courte du produit qui contient une balise <h1>, effacez la',
	'1019' => "produit qui n'a pas de meta titre",
	'1020' => "produit qui n'a pas de meta description",
	'1021' => 'Produit variable qui dont une des variations ne contient pas de prix',
	'1022' => "Produit simple qui n'a pas de prix",
	'1023' => "Produit qui contient un lien mailto dont la valeur du href n'est pas égale à la valeur de la balise lien",
	'1024' => 'Produit ou le/les meta(s) field(s) image-description-1, 2 ou 3 est/sont vide, il doit être rempli',
	'1025' => 'La description courte du produit doit être inférieure à 50 mots, enlevez du contenu',
	'1026' => "Balise alt qui contient un format d'image (jpg , jpeg , gif, png , webp , svg), remplacer la par une vraie description de l'image",
	'1027' => 'La description principale du produit doit être inférieure à 60 mots, enlevez du contenu',
	'1028' => "Le champ X contient un contenu généré entièrement par l'IA, réécrivez le",
	'1029' => "Produit variable dont une ou plusieurs de ses variations ne possède(nt) pas d'attributs",
	'1030' => "L'image du produit n'est pas stockée sur le serveur, veuillez la télécharger et l'uploader sur le serveur",
	'1031' => 'Une ou plusieurs images de la galerie produit ne sont pas stockées sur le serveur, veuillez les télécharger et les uploader sur le serveur',
	'1032' => "L'image d'un champ metafield image n'est pas stockée sur le serveur, veuillez la télécharger et l'uploader sur le serveur",
	'1033' => 'Un des champs de ce produit se termine par un espace, merci de le supprimer',
	'1034' => "Produit variable dont au moins une des variations contient une image qui n'est pas stockée sur WordPress, veuillez la télécharger et l'uploader sur le serveur",
);
