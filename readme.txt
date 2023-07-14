=== Is there a problem ===
Contributors: ganonbraker
Donate link: https://ingenius.agency/
Requires at least: 5.4
Tested up to: 6.0.1
Stable tag: 1.0.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin is a simple plugin that help integrator to respect the standards of SEO

Fonctionnalités : 

partie intégration : 

- Balise alt vide
- Balise alt trop courte
- Produit variable qui n\'a pas de produit par défaut
- Produit variable ou il manque une ou plusieurs variations dans le produit par défaut
- Produit variable dont le produit par défaut à comme variation des valeurs qui ne sont pas les premieres de leurs catégories
- Produit variable qui n\'a pas de variations, 
- Produit variable dont la couleur définie ne fait pas partie des couleurs possibles">
- Formats d\'images manquants/non créés par WordPress pour wooCommerce
- Produit associé à Rank Math qui n\'a pas de meta description
- Description du produit qui contient un lien

Partie SEO :

-Pas de description pour cette catégorie
-pas de description de tag (produit)
-pas de d'attribut couleur sur le site
-Catégorie qui n'as de contenu dans le meta-field "Texte dessous catégorie de produits"
-Catégorie qui ne contient pas de liens dans le meta-field "Texte dessous catégorie de produits"
-Lien externe dans le meta-field "Texte dessous catégorie de produits"
-description sous catégorie produit qui contient un lien vers une catégorie inexistante
-description sous catégorie produit qui contient un lien vers une catégorie latérale dont le parent n'est pas le même
-description sous catégorie produit qui contient un lien vers une autre catégorie qui n'est pas son enfant direct
-description sous catégorie produit qui contient un lien qui n'est pas son parent direct
-Lien dans le meta-field "Texte dessous catégorie de produits" pointant vers la catégorie actuelle

== Frequently Asked Questions ==

= are there dependencies = yes, woocommmerce 

== Screenshots ==

1. /assets/screenshot-1.png

== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 1.6.5 =
* Add archive for seo.
* correct the bug for title order (miscategorization_of_title).

= 1.7.0 =
* Fix bug for seo on below category text, below tag text and below attr text.
* fix min words for short description.

= 1.7.2 =
* Don't check the short description on error : chaque champ doit avoir minimum 50 mots
* check below attr content and below tag content only if there are content on it. (part SEO)

= 1.7.4 =
* fix mistakes on :
1. min words on short description
1. 300 hundred words between title

== Upgrade Notice ==

= 1.0 =
Base plugin version.

= 1.6.5 =
Add archive for seo

