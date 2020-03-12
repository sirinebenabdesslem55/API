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

$possibleorders= array('CC_CODE','TCO_DESCRIPTION','TC_DESCRIPTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='CC_CODE';
writehead();
echo "<script type='text/javascript' src='js/type_consommable.js'></script>";
echo "</head>";
echo "<body>";

if ( $catconso == 'ALL' ) {
	$picture = "<i class='fa fa-coffee fa-lg fa-2x' style='color:saddlebrown;'></i>";
	$cmt='Toutes';
}
else {
	$query="select CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE, CC_ORDER  
		from categorie_consommable
		where CC_CODE='".$catconso."'";
	$result=mysqli_query($dbc,$query);
	$row=@mysqli_fetch_array($result);
	$cmt=$row["CC_DESCRIPTION"];
	$picture="<i class='fa fa-".$row["CC_IMAGE"]." fa-2x' style='color:saddlebrown;'></i>";
}

$query1="select tc.TC_ID, tc.CC_CODE, tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE,
		cc.CC_NAME, cc.CC_DESCRIPTION, cc.CC_IMAGE, cc.CC_ORDER,
		tco.TCO_CODE, tco.TCO_DESCRIPTION, 
		tc.TC_QUANTITE_PAR_UNITE, tc.TC_UNITE_MESURE,
		tum.TUM_DESCRIPTION
        from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
		where tc.CC_CODE=cc.CC_CODE
		and tum.TUM_CODE = tc.TC_UNITE_MESURE
		and tco.TCO_CODE = tc.TC_CONDITIONNEMENT";
if ( $catconso <> 'ALL' ) $query1 .= "\nand cc.CC_CODE='".$catconso."'";
if ( $order == 'TCO_DESCRIPTION' ) $query1 .="\norder by tco.". $order;
else $query1 .="\norder by tc.". $order;

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center class='table-responsive'>
    <table class='noBorder'>
      <tr><td width = 60 >".$picture."</td><td>
      <font size=4><b> Catégorie: ".$cmt."</b></font></td></tr></table>";

echo "<table class='noBorder' >";
echo "<tr>";

//filtre type
echo "<td align=center><select id='usage' name='usage' 
	onchange=\"orderfilter('".$order."',document.getElementById('usage').value)\">";

$query2="select CC_CODE, CC_NAME from categorie_consommable order by CC_CODE asc";
$result2=mysqli_query($dbc,$query2);
if ( $catconso == 'ALL' ) $selected="selected ";
else $selected ="";
echo "<option value='ALL' $selected>Toutes les catégories de consommables</option>\n";
while ($row=@mysqli_fetch_array($result2)) {
      $CC_CODE=$row["CC_CODE"];
      $CC_NAME=$row["CC_NAME"];
      if ($CC_CODE == $catconso ) $selected="selected ";
	  else $selected ="";
      echo "<option value='".$CC_CODE."' $selected>".$CC_CODE." - ".$CC_NAME."</option>\n";
}
echo "</select></td> ";
echo "<td align=center><span class='badge'>$number </span></td>";
echo "<td><input type='button' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('upd_type_consommable.php?id=0');\"></td>
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
    <td width=130 align=center><a href=type_consommable.php?order=CC_CODE class=TabHeader>Catégorie</a></td>
    <td width=300 align=center><a href=type_consommable.php?order=TC_DESCRIPTION class=TabHeader>Description</a></td>
    <td width=200 align=center><a href=type_consommable.php?order=TCO_DESCRIPTION class=TabHeader>Conditionnement</a></td>
    <td width=200 align=center><a href=type_consommable.php?order=TC_QUANTITE_PAR_UNITE class=TabHeader>Contenance</a></td>
</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while ($row=@mysqli_fetch_array($result1)) {
 	$TC_ID=$row["TC_ID"];
	$CC_CODE=$row["CC_CODE"];
	$TC_DESCRIPTION=ucfirst($row["TC_DESCRIPTION"]);
	$TC_CONDITIONNEMENT=$row["TC_CONDITIONNEMENT"];
	$TC_UNITE_MESURE=$row["TC_UNITE_MESURE"];
	$CC_NAME=$row["CC_NAME"];
	$CC_DESCRIPTION=$row["CC_DESCRIPTION"];
	$CC_IMAGE=$row["CC_IMAGE"];
	$TCO_CODE=$row["TCO_CODE"];
	$TCO_DESCRIPTION=$row["TCO_DESCRIPTION"];
	$TUM_DESCRIPTION=$row["TUM_DESCRIPTION"];
	$TC_QUANTITE_PAR_UNITE=$row["TC_QUANTITE_PAR_UNITE"];
	if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .="s";
    $i=$i+1;
    if ( $i%2 == 0 ) {
      	$mycolor=$mylightcolor;
    }
    else {
		$mycolor="#FFFFFF";
    }
      
	echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
	onclick=\"this.bgColor='#33FF00'; displaymanager('$TC_ID')\" >
      	  <td align=left><i class='fa fa-".$CC_IMAGE." fa-lg' style='color:saddlebrown;'></i> $CC_CODE</td>
      	  <td align=center>".$TC_DESCRIPTION."</td>
		  <td align=center>".$TCO_DESCRIPTION."</td>
		  <td align=center>".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION."</td>
      </tr>";
      
}
echo "</table>";
}

echo "<p><input type='button' class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'></div>";
writefoot();
?>
