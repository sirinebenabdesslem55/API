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
check_all(18);
writehead();
forceReloadJS('js/competence.js');
echo "<body>";

$query1="select PH_CODE, PH_NAME, PH_HIDE_LOWER, PH_UPDATE_LOWER_EXPIRY, PH_UPDATE_MANDATORY from poste_hierarchie order by PH_CODE asc";
$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);
$title="Hiérarchies de Compétences";
echo "<div align=center clas='table-responsive'><font size=4><b>$title </b></font> <span class='badge'>$number</span><br>";
echo "<p><table class='noBorder'>";
echo "<tr>";
echo "<td><input type='button' class='btn btn-default' value='Compétences' title='Voir les compétence' onclick=\"bouton_redirect('poste.php');\"></td>";
echo "<td ><input type='button' class='btn btn-default' value='Types' title='Voir les types de compétences ' onclick=\"bouton_redirect('equipe.php');\"></td>";
echo "<td><input type='button' class='btn btn-default' value='Ajouter' name='ajouter'  title='Ajouter une hiérarfchie' onclick=\"bouton_redirect('upd_hierarchie_competence.php');\"></td>";
echo "</tr></table>";

echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

$t1="\"Montrer seulement la compétence la plus haute de la hiérarchie pour une personne sur les événements, masquer les autres\"";
$t2="\"En cas de mise à jour de la date d'expiration sur une compétence de la hiérarchie, la mise à jour automatique des dates des compétences inférieures est possible.\"";
$t3="\"La validation des compétences inférieures est obligatoire, si non cochée elle reste facultative sur les événements formations.\"";

echo "<tr class=TabHeader >";
echo "<td width=100 align=left >Code</td>";
echo "<td width=200 align=left >Description</td>";
echo "<td width=60 align=center title=$t1>Masquer</td>";
echo "<td width=60 align=center title=$t2>Date</td>";
echo "<td width=60 align=center title=$t3>Obligatoire</td>";
echo "<td width=320 align=left >Compétences de la hiérarchie (niveau croissant)</td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {
     
    if ( $PH_HIDE_LOWER == 1 ) $hide = "<i class='fa fa-check fa-lg' title=".$t1."></i>";
    else $hide="";

    if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) $update = "<i class='fa fa-check fa-lg' title=".$t2."></i>";
    else $update="";

    if ( $PH_UPDATE_MANDATORY == 1 ) $mandatory = "<i class='fa fa-check fa-lg' title=".$t3."></i>";
    else $mandatory="";      
    
    $i=$i+1;
    if ( $i%2 == 0 ) {
          $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }

    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager3('$PH_CODE')\" >";
    echo "<td align=left>$PH_CODE</td>";
    echo "<td align=left>$PH_NAME</td>";
    echo "<td align=center>$hide</td>";
    echo "<td align=center>$update</td>";
    echo "<td align=center>$mandatory</td>";
    
    // compétences
    $query2="select PS_ID, TYPE, PH_LEVEL from poste where PH_CODE='".$PH_CODE."' order by PH_LEVEL asc";
    $result2=mysqli_query($dbc,$query2);
    $string = "";
    while (custom_fetch_array($result2)) {
        $string .= " ".$TYPE.",";
    }
    if ( $string <> "" ) $string =rtrim($string,',');     
    echo  "<td align=left>".$string."</td>";
    echo "</tr>";    
}

echo "</table>";  
echo "<p><input type='button' class='btn btn-default'  value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();
?>
