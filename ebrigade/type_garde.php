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

$possibleorders= array('EQ_ID','S_CODE','EQ_NOM','NB_POSTES','EQ_JOUR','EQ_NUIT','EQ_VEHICULES','EQ_SPP', 'EQ_PERSONNEL1','EQ_PERSONNEL2');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='EQ_ID';

writehead();

check_all(5);

?>
<script type='text/javascript' src='js/competence.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<?php
echo "<body>";

$query1="select e.EQ_ID, e.EQ_NOM, e.EQ_JOUR, e.EQ_NUIT, 
        e.EQ_PERSONNEL1, e.EQ_PERSONNEL2, e.EQ_VEHICULES, e.EQ_SPP, e.EQ_ICON, s.S_ID, s.S_CODE
        from type_garde e, section s
        where s.S_ID = e.S_ID";

if ( $nbsections == 0 and $filter > 0 ) 
        $query1 .= " and e.S_ID in (".get_family("$filter").")";
        
$query1 .= " group by e.EQ_ID";
$query1 .= " order by ". $order;
if ( $order == 'EQ_NOM' || $order == 'EQ_ID' ) $query1 .=" asc";
else $query1 .=" desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'><font size=4><b>Types de Gardes</b></font> <span class='badge'>$number</span><br>";
echo "<p><table class='noBorder'>";
echo "<tr>";


if ( check_rights($id, 5 ) ) {
    if ( $nbsections == 0 ) {
        //filtre section
        echo "<td>Centre</td>";
        echo "<td> <select id='filter' name='filter' 
        onchange=\"orderfiltergarde('".$order."',document.getElementById('filter').value)\">";
        if ( $pompiers ) $maxL = $nbmaxlevels -1 ;
        else $maxL = $nbmaxlevels;
        display_children2(-1, 0, $filter, $maxL, $sectionorder);
        echo "</select></td> ";
    }
       echo "<td><input type='button' class='btn btn-default' value='Ajouter' name='ajouter'  title='Ajouter un type de garde' onclick=\"bouton_redirect('upd_type_garde.php?eqid=0');\"></td>";
}
echo "</tr></table>";

if ( $number == 0 ) 
    echo "<p>Aucun élément paramétré";
else {

// ===============================================
// tableau
// ===============================================

echo "<p><table cellspacing=0 border=0>";    
echo "<tr class='TabHeader'>";
echo "<td width=50 align=center class=TabHeader></td>";

echo "<td width=180 align=left class=TabHeader>Description</td>";
if ( $nbsections == 0 ) {
    echo "<td  width=120 align=center class=TabHeader>Centre</td>";
}
echo "<td  width=40 align=center class=TabHeader>Actif jour</td>
      <td  width=40 align=center class=TabHeader>Actif nuit</td>";
echo  "<td width=40 align=center class=TabHeader><span title='Nombre de personnes requises le jour'>Personnel jour</span></td>";
echo  "<td width=40 align=center class=TabHeader><span title='Nombre de personnes requises la nuit'>Personnel nuit</span></td>";
if ( $vehicules) 
  echo "<td  width=80 align=center class=TabHeader>Véhicules</td>";
echo "<td  width=40 align=center class=TabHeader><span title='Les sapeurs pompiers professionnels sont normalement engagés sur ce type de garde'>SPP</span></td>";
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
    
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; bouton_redirect('upd_type_garde.php?eqid=".$EQ_ID."')\" >";
    echo "<td align=center><img src='".$EQ_ICON."' height=25></td>";
    echo "<td align=left>$EQ_NOM</td>";
    if ( $EQ_JOUR == 1) $jour="<i class='fa fa-circle fa-lg' style='color:green;' title='actif'></i>";
    else $jour="<i class='fa fa-circle fa-lg' style='color:red;' title='pas actif'></i>";
    if ( $EQ_NUIT == 1) $nuit="<i class='fa fa-circle fa-lg' style='color:green;' title='actif'></i>";
    else $nuit="<i class='fa fa-circle fa-lg' style='color:red;' title='pas actif'></i>";

    if ( $nbsections == 0 ) 
        echo "<td align=center>".$S_CODE."</td>";
    
    echo "    
      <td align=center><B>$jour</B></td>
      <td align=center><B>$nuit</B></td>";
    echo "
        <td align=center><span class='badge' style='color:yellow;'>$EQ_PERSONNEL1</span></td>
        <td align=center><span class='badge' style='color:lightblue;'>$EQ_PERSONNEL2</span></td>";
        
    if ( $EQ_VEHICULES == 1 ) 
        $showv="<i class='fa fa-check fa-lg' title =\"Les véhicules sont par défaut automatiquement affichés\"></i>";
    else 
        $showv="";
        
    if ( $EQ_SPP == 1 ) 
        $showspp="<i class='fa fa-check fa-lg'  title = \"Les sapeurs pompiers professionnels sont par défaut automatiquement engagés sur ce type de garde\"></i>";
    else 
        $showspp="";
        
    if ( $vehicules) 
        echo "<td  align=center>".$showv."</td>";
    echo "<td  align=center>".$showspp."</td> ";  

    echo "</tr>";    
}

echo "</table>"; 
} // aucun élément paramétré

echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();

?>
