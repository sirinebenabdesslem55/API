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
echo "<div align=center><font size=4><b>Param�rage de l'application<br></b></font>";
echo "<p><table cellspacing=0 border=0>";

echo "<tr class=TabHeader>";
echo "<td width=60 align=center class=TabHeader></td>
    <td width=200 align=center class=TabHeader>Param�trage pour:</td>
    <td width=30 align=center class=TabHeader>Nombre</td>";  

if ( check_rights($id, 18)) {
    if ( $vehicules ) {
        write_param_links( "V�hicules", "truck", $mydarkcolor, "Param�trer les types de v�hicules", "type_vehicule.php?page=1", "type_vehicule");
        if ( $evenements) 
            write_param_links( "Fonctions des v�hicules", "truck", "#e64d00", "Param�trer les fonctions des v�hicules sur les �v�nements", "paramfnv.php", "type_fonction_vehicule");
    }
    if ( $materiel ) 
        write_param_links( "Mat�riel et tenues", "cog" , "purple", "Param�trer les types de mat�riel et les tenues du personnel", "type_materiel.php?page=1" , "type_materiel");
    if ( $consommables ) 
        write_param_links( "Consommable", "coffee", "saddlebrown ", "Param�trer les types de consommables", "type_consommable.php?page=1", "type_consommable");
    if ( $evenements ) {
        write_param_links( "Ev�nements", "info-circle","blue", "Param�trer les �v�nements", "type_evenement.php?page=1", "type_evenement");
        write_param_links( "Fonctions du personnel", "user-md", "#ff0066", "Param�trer les fonctions du personnel sur les �v�nements", "paramfn.php?page=1" , "type_participation");
    }
    if ( $competences ) {
        write_param_links( "Comp�tences", "star", "orange", "Param�trer les comp�tences", "poste.php?page=1&order=PS_ID&typequalif=ALL", "poste");
        write_param_links( "Types de Comp�tences", "certificate", "green", "Param�trer les types comp�tences", "equipe.php", "equipe" );
        write_param_links( "Hi�rarchies de Comp�tences", "sitemap","black", "Param�trer les hi�rarchies de comp�tences", "hierarchie_competence.php", "poste_hierarchie");
    }
}

if ( $gardes ) {
    if ( check_rights($id, 5))
        write_param_links( "Gardes", "ambulance", "red", "Param�trer les gardes", "type_garde.php", "type_garde");
}    
if ( check_rights($id,54) ) {
    if ( $competences ) write_param_links( "Dipl�mes", "certificate", "purple", "Param�trer l'impression des dipl�mes", "diplome_edit.php", "poste where PS_PRINTABLE = 1");
}

if ( check_rights($id,29) ) {
    write_param_links( "El�ments facturables", "euro","orange", "Param�trer les �l�ments facturables", "element_facturable.php?from=parametrage", "element_facturable");
}

echo "</table>";

echo "<p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"><p>";
writefoot();
?>

    
