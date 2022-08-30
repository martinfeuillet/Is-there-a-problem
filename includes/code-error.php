<?php
$couleurs = array('rouge', 'bleu', 'vert', 'jaune', 'noir', 'blanc', 'gris', 'marron', 'orange', 'rose', 'violet', 'multicolore', 'kaki', 'fuchsia', 'doré', 'camouflage', 'camel', 'bordeaux', 'beige', 'argenté');


array(
    1001 => "Balise alt vide",
    1002 => "Balise alt trop courte",
    1003 => "Produit variable qui n'a pas de produit par défaut",
    1004 => 'Produit variable dont la couleur définie ne fait pas partie des <div class="tooltip">couleurs possibles<span class="tooltiptext">' . implode(", ", $couleurs) . '</span></div>',
);
