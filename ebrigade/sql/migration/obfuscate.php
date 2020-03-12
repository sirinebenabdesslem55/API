<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 4.4

  # Copyright (C) 2004, 2018 Nicolas MARCHE
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

include_once ("../../config.php");
check_all(14);

$nomenu=1;
writehead();

// utiliser pour faire l'obfuscation d'une base de donnees

$names= array(
"Martin",
"Bernard",
"Thomas",
"Petit",
"Robert",
"Richard",
"Durand",
"Dubois",
"Moreau",
"Laurent",
"Simon",
"Michel",
"Lefebvre",
"Leroy",
"Roux",
"David",
"Bertrand",
"Morel",
"Fournier",
"Girard",
"Bonnet",
"Dupont",
"Lambert",
"Fontaine",
"Rousseau",
"Vincent",
"Muller",
"Lefevre",
"Faure",
"Andre",
"Mercier",
"Blanc",
"Guerin",
"Boyer",
"Garnier",
"Chevalier",
"Francois",
"Legrand",
"Gauthier",
"Garcia",
"Perrin",
"Robin",
"Clement",
"Morin",
"Nicolas",
"Henry",
"Roussel",
"Mathieu",
"Gautier",
"Masson",
"Marchand",
"Duval",
"Denis",
"Dumont",
"Marie",
"Lemaire",
"Noel",
"Meyer",
"Dufour",
"Meunier",
"Brun",
"Blanchard",
"Giraud",
"Joly",
"Riviere",
"Lucas",
"Brunet",
"Gaillard",
"Barbier",
"Arnaud",
"Martinez",
"Gerard",
"Roche",
"Renard",
"Schmitt",
"Roy",
"Leroux",
"Colin",
"Vidal",
"Caron",
"Picard",
"Roger",
"Fabre",
"Aubert",
"Lemoine",
"Renaud",
"Dumas",
"Lacroix",
"Olivier",
"Philippe",
"Bourgeois",
"Pierre",
"Benoit",
"Rey",
"Leclerc",
"Payet",
"Rolland",
"Leclercq",
"Guillaume",
"Lecomte",
"Lopez",
"Jean",
"Dupuy",
"Guillot",
"Hubert",
"Berger",
"Carpentier",
"Sanchez",
"Dupuis",
"Moulin",
"Louis",
"Deschamps",
"Huet",
"Vasseur",
"Perez",
"Boucher",
"Fleury",
"Royer",
"Klein",
"Jacquet",
"Adam",
"Paris",
"Poirier",
"Marty",
"Aubry",
"Guyot",
"Carre",
"Charles",
"Renault",
"Charpentier",
"Menard",
"Maillard",
"Baron",
"Bertin",
"Bailly",
"Herve",
"Schneider",
"Fernandez",
"Le Bras",
"Collet",
"Leger",
"Bouvier",
"Julien",
"Prevost",
"Millet",
"Perrot",
"Daniel",
"Le Tan",
"Cousin",
"Germain",
"Breton",
"Besson",
"Langlois",
"Remy",
"Le Guen",
"Pelletier",
"Leveque",
"Perrier",
"Leblanc",
"Barre",
"Lebrun",
"Marchal",
"Weber",
"Mallet",
"Hamon",
"Boulanger",
"Jacob",
"Monnier",
"Michaud",
"Rodriguez",
"Guichard",
"Gillet",
"Etienne",
"Grondin",
"Poulain",
"Tessier",
"Chevallier",
"Collin",
"Chauvin",
"Da Silva",
"Bouchet",
"Gay",
"Lemaitre",
"Benard",
"Marechal",
"Humbert",
"Reynaud",
"Antoine",
"Hoarau",
"Perret",
"Barthelemy",
"Cordier",
"Pichon",
"Lejeune",
"Gilbert",
"Lamy",
"Delaunay",
"Pasquier",
"Carlier",
"Laporte",
"Garfinkel",
"Holzman",
"Waldman",
"Kaufman",
"Rokeach",
"Salzman",
"Seid",
"Tabachnik",
"Wechsler",
"Halphan",
"Wollman",
"Zucker",
"Pechkowsky",
"Fleisher",
"Goldstein",
"Rossi",
"Russo",
"Ferrari",
"Esposito",
"Bianchi",
"Romano",
"Colombo",
"Ricci",
"Marino",
"Greco",
"Bruno",
"Gallo",
"Conti",
"De Luca",
"Costa",
"Giordano",
"Mancini",
"Rizzo",
"Lombardi",
"Moretti");

shuffle($names);
$names_count=count($names);

function generate_number() {
    $possible = "0123456789";
    $i = 0;
    $number="06";
    // add random characters to $number until $length is reached
    while ($i < 8) { 
        // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        $number .= $char;
        $i++;
    }
    return $number;
}

$query="select P_NOM, P_PRENOM, P_ID, P_CODE from pompier order by P_ID";
$result=mysqli_query($dbc,$query);
$k=0;
while ( custom_fetch_array($result) and $k < $names_count) {
    $newname=strtolower($names[$k]);
    $newnumber=generate_number();
    $newnumber2=generate_number();
    $newnumber3=generate_number();
    $newcode = intval($P_CODE) + 260;
    $query2="update pompier set
        P_NOM=\"".$newname."\",
        P_MDP=md5('1234'),
        P_CODE = \"".$newcode."\",
        P_RELATION_NOM=\"".$newname."\",
        P_PHONE=\"".$newnumber."\",
        P_PHONE2=\"".$newnumber2."\",
        P_RELATION_PHONE=\"".$newnumber3."\",
        P_BIRTHDATE=DATE_ADD(P_BIRTHDATE, interval 400 day),
        P_EMAIL =concat(P_PRENOM,'.".$newname."@gmail.com'),
        P_RELATION_MAIL=null
        where P_ID=".$P_ID;
        
        echo $query2."<p>";
    $result2=mysqli_query($dbc,$query2);
    $k++;
    if ( $k == $names_count ) {
        $k=0;
        shuffle($names);
    }
    echo $P_PRENOM." ".$P_NOM." => ".$newname." ".$newnumber."<br>";
}

$query="update configuration ";

?>

