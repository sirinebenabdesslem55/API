<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE
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

include_once ("config.php");
check_all(0);
$id = $_SESSION['id'];
if (! check_rights($id, 18)  and ! check_rights($id,54) and ! check_rights($id,29)) check_all(18);
writehead();
 

function write_param_links( $name, $image, $color, $title, $link, $table) {
    global $dbc,$mylightcolor, $mydarkcolor;
    $query="select count(1) from ".$table;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $number=$row[0];
    
    echo "<tr  bgcolor=$mylightcolor>
    <td align=center>
        <a href=\"".$link."\" title=\"".$title."\" class='s'>
        <i class='fa fa-".$image." fa-2x' style='color:".$color.";'></i>
        </a>
    </td>
    <td>
        <a href=\"".$link."\" title=\"".$title."\" class='s'> ".$name."</a>
    </td>
    <td align=center>".$number."</td>
    </tr>";
}

echo "</head><body>";
echo "<div align=center><font size=4><b>Paramérage de l'application<br></b></font>";
echo "<p><table cellspacing=0 border=0>";

echo "<tr class=TabHeader>";
echo "<td width=60 align=center class=TabHeader></td>
    <td width=200 align=center class=TabHeader>Paramétrage pour:</td>
    <td width=30 align=center class=TabHeader>Nombre</td>";  

if ( check_rights($id, 18)) {
    if ( $vehicules ) {
        write_param_links( "Véhicules", "truck", $mydarkcolor, "Paramétrer les types de véhicules", "type_vehicule.php?page=1", "type_vehicule");
        if ( $evenements) 
            write_param_links( "Fonctions des véhicules", "truck", "#e64d00", "Paramétrer les fonctions des véhicules sur les événements", "paramfnv.php", "type_fonction_vehicule");
    }
    if ( $materiel ) 
        write_param_links( "Matériel et tenues", "cog" , "purple", "Paramétrer les types de matériel et les tenues du personnel", "type_materiel.php?page=1" , "type_materiel");
    if ( $consommables ) 
        write_param_links( "Consommable", "coffee", "saddlebrown ", "Paramétrer les types de consommables", "type_consommable.php?page=1", "type_consommable");
    if ( $evenements ) {
        write_param_links( "Evénements", "info-circle","blue", "Paramétrer les événements", "type_evenement.php?page=1", "type_evenement");
        write_param_links( "Fonctions du personnel", "user-md", "#ff0066", "Paramétrer les fonctions du personnel sur les événements", "paramfn.php?page=1" , "type_participation");
    }
    if ( $competences ) {
        write_param_links( "Compétences", "star", "orange", "Paramétrer les compétences", "poste.php?page=1&order=PS_ID&typequalif=ALL", "poste");
        write_param_links( "Types de Compétences", "certificate", "green", "Paramétrer les types compétences", "equipe.php", "equipe" );
        write_param_links( "Hiérarchies de Compétences", "sitemap","black", "Paramétrer les hiérarchies de compétences", "hierarchie_competence.php", "poste_hierarchie");
    }
}

if ( $gardes ) {
    if ( check_rights($id, 5))
        write_param_links( "Gardes", "ambulance", "red", "Paramétrer les gardes", "type_garde.php", "type_garde");
}    
if ( check_rights($id,54) ) {
    if ( $competences ) write_param_links( "Diplômes", "certificate", "purple", "Paramétrer l'impression des diplômes", "diplome_edit.php", "poste where PS_PRINTABLE = 1");
}

if ( check_rights($id,29) ) {
    write_param_links( "Eléments facturables", "euro","orange", "Paramétrer les éléments facturables", "element_facturable.php?from=parametrage", "element_facturable");
}

echo "</table>";

echo "<p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"><p>";
writefoot();
?>

    
