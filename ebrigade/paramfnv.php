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

$possibleorders= array('TFV_ORDER','TFV_NAME','TFV_DESCRIPTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TFV_ORDER';
writehead();
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php
echo "<body>";

$query1="select TFV_ID,TFV_ORDER,TFV_NAME,TFV_DESCRIPTION from type_fonction_vehicule";
$query1 .=" order by ". $order;


$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'><font size=4><b>Fonctions des véhicules</b></font> <span class='badge'>".$number."</span>";
echo "<form name=r>"; 

echo "<table class='noBorder'>";
echo "<tr>";
echo "<td align = center> <input type='button' class='btn btn-default' value='Ajouter' name='ajouter' 
       onclick=\"bouton_redirect('paramfnv_edit.php');\"></td>";
echo "</tr></table></form><p>";

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
            <td width=200 align=center>
            <a href=paramfnv.php?order=TFV_NAME class=TabHeader>Nom</a></td>
          <td width=10 align=center>
            <a href=paramfnv.php?order=TFV_ORDER class=TabHeader>Ordre</a></td>
            <td width=200 align=center>
            <a href=paramfnv.php?order=TFV_DESCRIPTION class=TabHeader>Description</a></td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while ($row=@mysqli_fetch_array($result1)) {
    $TFV_ID=$row["TFV_ID"];
    $TFV_NAME=$row["TFV_NAME"];
    $TFV_ORDER=$row["TFV_ORDER"];
    $TFV_DESCRIPTION=$row["TFV_DESCRIPTION"];    
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager2('".$TFV_ID."')\" >
            <td align=left>".$TFV_NAME."</td>
          <td align=center>".$TFV_ORDER."</td>
          <td align=center>".$TFV_DESCRIPTION."</td>";
    echo "</tr>";
      
}

echo "</table>";
echo "<p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:self.location.href='parametrage.php'\";></div>";
writefoot();
?>
