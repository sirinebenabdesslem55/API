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
$id=$_SESSION['id'];
get_session_parameters();

writehead();
check_all(18);

?>
<script type='text/javascript' src='js/competence.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<?php
echo "<body>";

$query1="select e.EQ_ID, e.EQ_NOM, e.EQ_ORDER,
		count(1) as NB_POSTES
	    from equipe e left join poste p on p.EQ_ID = e.EQ_ID";
$query1 .= " group by e.EQ_ID";
$query1 .= " order by e.EQ_ORDER";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'><font size=4><b>Types de Compétences</b></font> <span class='badge'>$number</span><br>";
echo "<p><table class='noBorder'>";
echo "<tr>";
echo "<td><input type='button' class='btn btn-default' value='Hiérarchies' title='Voir les hiérarchies de compétence' onclick=\"bouton_redirect('hierarchie_competence.php');\"></td>";
echo "<td><input type='button' class='btn btn-default' value='Compétences' title='Voir les compétence' onclick=\"bouton_redirect('poste.php');\"></td>";
echo "<td><input type='button' class='btn btn-default' value='Ajouter' name='ajouter'  title='Ajouter un type de compétence' onclick=\"bouton_redirect('upd_equipe.php?eqid=0');\"></td>";echo "</tr></table>";

if ( $number == 0 ) 
	echo "<p>Aucun élément paramétré";
else {
    echo "<p><table cellspacing=0 border=0>";	
    echo "<tr class='TabHeader'>";
    echo "<td width=160 align=left >Description</td>"; 
    echo "<td width=60 align=center ><span title='Nombre de compétences pour ce type'>Compétences</span></td>";
    $query2="select distinct CEV_CODE, CEV_DESCRIPTION from categorie_evenement";
    $result2=mysqli_query($dbc,$query2);
    while (custom_fetch_array($result2)) {
        echo   	 "<td align=center class=TabHeader title=\"".$CEV_DESCRIPTION."\">
                     Afficher<br>".str_replace("C_","",$CEV_CODE)."</td>";
    }
    echo "<td width=40 align=center >Ordre</td>"; 
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result1)) {  
        $i=$i+1;
        if ( $i%2 == 0 ) {
            $mycolor=$mylightcolor;
        }
        else {
            $mycolor="#FFFFFF";
        }
        
        if ( $NB_POSTES == 1 ) {
            $query2="select count(1) as NB from poste where EQ_ID=".$EQ_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $NB_POSTES=$row2[0];
        }

        echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager2($EQ_ID)\" >";
        echo "<td align=left>$EQ_NOM</td>
              <td align=center><span class='badge' title=\"il y a $NB_POSTES compétences de type $EQ_NOM\">$NB_POSTES</span></td>";
        
        $query2="select distinct ce.CEV_CODE, ce.CEV_DESCRIPTION, cea.FLAG1 
            from categorie_evenement ce, categorie_evenement_affichage cea
            where ce.CEV_CODE=cea.CEV_CODE
            and cea.EQ_ID=".$EQ_ID;
        $result2=mysqli_query($dbc,$query2);
        while (custom_fetch_array($result2)) {
            if ( $FLAG1 == 1 ) $show="<i class='fa fa-check fa-lg' title = \"Les compétences de la catégorie ".$CEV_DESCRIPTION." sont visibles sur la page des événements\"></i>";
            else $show="";
            echo  "<td align=center>".$show."</td>";
        }
        echo  "<td align=center>$EQ_ORDER</td>";
        echo "</tr>";    
    }
    echo "</table>"; 
}

echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();

?>
