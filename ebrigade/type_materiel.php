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

$possibleorders= array('TM_USAGE','TM_CODE','TM_DESCRIPTION','TM_LOT', 'TT_NAME');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TM_USAGE';
writehead();
?>

<script language="JavaScript">
function orderfilter(p1,p2){
     self.location.href="type_materiel.php?order="+p1+"&catmateriel="+p2;
     return true
}
function displaymanager(p1){
     self.location.href="upd_type_materiel.php?id="+p1;
     return true
}

function bouton_redirect(cible) {
     self.location.href = cible;
}

</script>
<?php


$query="select CM_DESCRIPTION,PICTURE from categorie_materiel
        where TM_USAGE='".$catmateriel."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$cmt=$row["CM_DESCRIPTION"];
$picture=$row["PICTURE"];

$query1="select tm.TM_ID,tm.TM_CODE,tm.TM_DESCRIPTION,tm.TM_USAGE,tm.TM_LOT,cm.PICTURE, tt.TT_CODE, tt.TT_NAME
        from type_materiel tm left join type_taille tt on tt.TT_CODE = tm.TT_CODE,
        categorie_materiel cm
        where cm.TM_USAGE=tm.TM_USAGE ";

if ( $catmateriel <> 'ALL' ) $query1 .= "\nand tm.TM_USAGE='".$catmateriel."'";
if ( $order == 'TT_NAME' ) $query1 .="\norder by tt.". $order;
else $query1 .="\norder by tm.". $order;

if ( $order == 'TM_LOT' ) $query1 .=" desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width = 30 ><i class='fa fa-".$picture." fa-2x' style='color:purple;'></i></td><td>
      <font size=4><b> Catégorie: ".$cmt."</b></font></td></tr></table>";

echo "<p><table class='noBorder' >";
echo "<tr>";

//filtre type
echo "<td align=center><select id='usage' name='usage' 
    onchange=\"orderfilter('".$order."',document.getElementById('usage').value)\">";

$query2="select TM_USAGE,CM_DESCRIPTION from categorie_materiel order by TM_USAGE asc";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
      $TM_USAGE=$row["TM_USAGE"];
      $CM_DESCRIPTION=$row["CM_DESCRIPTION"];
      echo "<option value='".$TM_USAGE."'";
      if ($TM_USAGE == $catmateriel ) echo " selected ";
      echo ">".$TM_USAGE." - ".$CM_DESCRIPTION."</option>\n";
}
echo "</select></td> ";
echo "<td align=center><span class='badge'>$number types</span></td>";
echo "<td><input type='button' class='btn btn-default' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('ins_type_materiel.php?catmateriel=$catmateriel');\"></td>
</tr><tr><td colspan=3>";


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

echo "</td></tr></table>";

if ( $number > 0 ) {
echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class='TabHeader'>
    <td width=120 align=center><a href=type_materiel.php?order=TM_USAGE class=TabHeader>Catégorie</a></td>
    <td width=40 align=center><a href=type_materiel.php?order=TM_LOT class=TabHeader>Lot</a></td>
    <td width=200 align=left><a href=type_materiel.php?order=TM_CODE class=TabHeader>Code</a></td>
    <td width=300 align=left><a href=type_materiel.php?order=TM_DESCRIPTION class=TabHeader>Description</a></td>";
    
if ($catmateriel == 'Habillement')
    echo "<td width=150 align=center><a href=type_materiel.php?order=TT_NAME class=TabHeader>Mesures</a></td>";
    
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
    if ( $TM_LOT == 1 ) $img1="<i class='fa fa-check fa-lg' title='Lot de matériel'></i>";
    else $img1='';
      
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
        onclick=\"this.bgColor='#33FF00'; displaymanager('$TM_ID')\" >
            <td align=left><i class='fa fa-".$PICTURE." fa-lg' style='color:purple;'></i> <B>".$TM_USAGE."</B></td>
          <td align=center>".$img1."</td>
            <td align=left>".$TM_CODE."</td>
          <td align=left>".$TM_DESCRIPTION."</td>";
    
    if ($catmateriel == 'Habillement')
        echo "<td align=center class=small2>".$TT_NAME."</td>";
          
    echo " </tr>";
      
}
echo "</table>";
}
echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();
?>
