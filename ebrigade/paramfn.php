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
get_session_parameters();

$possibleorders= array('TE_CODE','TP_NUM','TP_LIBELLE','INSTRUCTOR','DESCRIPTION','DESCRIPTION2');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TE_CODE';

writehead();
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php
echo "</head><body>";

$query1="select tp.TE_CODE, tp.TP_ID, tp.TP_LIBELLE, tp.TP_NUM, tp.EQ_ID, tg.EQ_NOM, tg.EQ_ICON, te.TE_ICON,
        tp.PS_ID, tp.PS_ID2, tp.INSTRUCTOR, p.TYPE, p.DESCRIPTION, p2.TYPE TYPE2, p2.DESCRIPTION DESCRIPTION2, te.TE_LIBELLE
          from type_participation tp
        left join type_garde tg on tg.EQ_ID = tp.EQ_ID
          left join poste p on p.PS_ID=tp.PS_ID
        left join poste p2 on p2.PS_ID=tp.PS_ID2
        join type_evenement te on te.TE_CODE=tp.TE_CODE
        where 1=1 ";


if ( $type_evenement <> 'ALL' and $type_evenement <> 'ALLBUTGARDE' ) $query1 .= "\nand tp.TE_CODE='".$type_evenement."'";
if ( $gardes == 0 ) $query1 .= "\nand tp.EQ_ID=0";
if ( $order == 'TE_CODE' ) $query1 .= " order by tp.TE_CODE, tp.EQ_ID, tp.TP_NUM";
else $query1 .=" order by ". $order;
if ( $order == 'DESCRIPTION' or $order == 'DESCRIPTION2' or $order == 'INSTRUCTOR') 
$query1 .= " desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'><font size=4><b>Fonctions du personnel</b></font> <span class='badge'>".$number."</span>";
echo "<form name=r>"; 

echo "<table class='noBorder'>";
echo "<tr>";
echo "<td><font size=1>fonctions pour événements de type:</font></td>
      <td ><select id='type_evenement' name='type_evenement' 
          onchange=\"orderfilter('".$order."',document.getElementById('type_evenement').value)\">
      <option value='ALL'>tous types</option>";

$query2="select distinct TE_CODE, TE_LIBELLE from type_evenement order by TE_LIBELLE asc";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
      $TE_CODE=$row["TE_CODE"];
      $TE_LIBELLE=$row["TE_LIBELLE"];
      echo "<option value='".$TE_CODE."'";
      if ($TE_CODE == $type_evenement ) echo " selected ";
      echo ">".$TE_LIBELLE."</option>\n";
}
echo "</select></td> ";
echo "<td> <input type='button' class='btn btn-default' value='Ajouter' name='ajouter' 
       onclick=\"bouton_redirect('paramfn_edit.php?type=".$type_evenement."');\"></td>";

echo "</tr></table></form>";

// ====================================
// pagination
// ====================================
require_once('paginator.class.php');
$pages = new Paginator;  
$pages->items_total = $number;  
$pages->mid_range = 9;  
$pages->paginate();  
if ( $number > 10 ) {
    echo $pages->display_pages();
    echo $pages->display_jump_menu(); 
    echo $pages->display_items_per_page(); 
    $query1 .= $pages->limit;
}
$result1=mysqli_query($dbc,$query1);

echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class=TabHeader>
            <td width=20></td>
          <td width=200 align=center >
            <a href=paramfn.php?order=TE_CODE class=TabHeader>Type</a></td>
            <td width=20 align=center>
            <a href=paramfn.php?order=TP_NUM class=TabHeader>Ordre</a></td>
            <td width=200 align=center>
            <a href=paramfn.php?order=TP_LIBELLE class=TabHeader>Fonction</a></td>
          <td width=50 align=center>
            <a href=paramfn.php?order=INSTRUCTOR class=TabHeader>Moniteur.</a></td>";
if ( $competences == 1 ) {
    echo "<td width=200 align=left>
            <a href=paramfn.php?order=DESCRIPTION class=TabHeader>Compétence requise</a></td>";
    echo "<td width=200 align=left>
            <a href=paramfn.php?order=DESCRIPTION2 class=TabHeader>Ou</a></td>";
}
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while ($row=@mysqli_fetch_array($result1)) {
    $PS_ID=$row["PS_ID"];
    $PS_ID2=$row["PS_ID2"];
    $INSTRUCTOR=$row["INSTRUCTOR"];
    $TYPE=$row["TYPE"];
    $DESCRIPTION=strip_tags($row["DESCRIPTION"]);
    $TYPE2=$row["TYPE2"];
    $DESCRIPTION2=strip_tags($row["DESCRIPTION2"]);
    $TE_CODE=$row["TE_CODE"];
    $TE_ICON=$row["TE_ICON"];
    $TE_LIBELLE=$row["TE_LIBELLE"];
    $TP_ID=$row["TP_ID"];
    $TP_LIBELLE=$row["TP_LIBELLE"];
    $TP_NUM=$row["TP_NUM"];
    $EQ_NOM=$row["EQ_NOM"];
    $EQ_ICON=$row["EQ_ICON"];
     
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
    if ( $EQ_ICON == '' ) 
        $type_cell="<td align=center><img src=images/evenements/".$TE_ICON."  title=\"".$TE_LIBELLE."\" class='img-max-20'></td>
                <td align=left> ".$TE_LIBELLE."</td>";
    else 
        $type_cell="<td align=center><img src=".$EQ_ICON." class='img-max-20' title=\"".$EQ_NOM."\"></td>
        <td  align=left>".$EQ_NOM."</td>";
    
    if ( $INSTRUCTOR == 1 ) $ins="<i class='fa fa-check-square fa-lg' style='color:green;' title='Instructeur ou moniteur'></i>";
    else $ins="";
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager('".$TP_ID."','".$type_evenement."')\" >
            ".$type_cell."
            <td align=center>$TP_NUM</td>
            <td align=left>$TP_LIBELLE</td>
          <td align=center>$ins</td>";
    if ( $competences == 1 ) {
        echo "<td align=left><font size=1>".$TYPE." - ".$DESCRIPTION."</font></td>";
        echo "<td align=left><font size=1>".$TYPE2." - ".$DESCRIPTION2."</font></td>";
    }
    echo "</tr>";
      
}

echo "</table>"; 
echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();
?>
