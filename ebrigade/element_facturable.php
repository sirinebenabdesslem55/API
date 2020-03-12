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
check_all(29);
get_session_parameters();
writehead();
test_permission_level(29);
if (isset($_GET["from"])) $from=$_GET["from"];
else $from="default";
$possibleorders= array('TEF_NAME','EF_NAME','EF_PRICE','S_CODE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='e.TEF_CODE, e.EF_NAME';

?>
<script type='text/javascript' src='js/element_facturable.js'></script>
<?php

$querycnt="select count(*) as NB";
$query1=" select e.EF_ID, e.TEF_CODE, t.TEF_NAME, e.S_ID, e.EF_NAME, e.EF_PRICE, s.S_CODE";
$queryadd=" from element_facturable e, type_element_facturable t, section s
            where s.S_ID=e.S_ID
            and e.TEF_CODE = t.TEF_CODE";
if ( $type_element <> 'ALL' ) $queryadd .= "\n and e.TEF_CODE='".$type_element."'";

// choix section
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $queryadd .= "\nand e.S_ID in (".get_family("$filter").")";
}
else {
    $queryadd .= "\nand e.S_ID =".$filter;
}

$querycnt .= $queryadd;
if ( $order =='EF_PRICE' ) $query1 .= $queryadd." order by EF_PRICE desc";
else $query1 .= $queryadd." order by ". $order;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width =60><i class='fa fa-euro-signfa-3x' style='color:orange;' ></i></td><td>
      <font size=4><b> Elements facturables</b> </font><span class='badge'>".$number."  </span></td></tr></table>";

echo "<table class='noBorder' >";

//filtre section
echo "<tr>";
echo "<td>".choice_section_order('element_facturable.php')."</td>";
echo "<td><select id='filter' name='filter' 
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$type_element."','".$subsections."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td> ";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<td><input type='checkbox' name='sub' $checked
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value,'".$type_element."', this)\"/>
       <font size=1>inclure les $sous_sections</td>";
}
echo "</tr>";

//filtre categorie_facturable
echo "<tr><td>Type</td>
         <td align=left><select id='type_element' name='type_element' 
onchange=\"orderfilter('".$order."','".$filter."',document.getElementById('type_element').value,'".$subsections."')\">";

$query2="select TEF_CODE, TEF_NAME from type_element_facturable order by TEF_NAME asc";
$result2=mysqli_query($dbc,$query2);
if ( $type_element == 'ALL' ) $selected="selected ";
else $selected ="";
echo "<option value='ALL' $selected>Tous les types d'éléments facturables</option>\n";
while ($row=@mysqli_fetch_array($result2)) {
    $TEF_CODE=$row["TEF_CODE"];
    $TEF_NAME=$row["TEF_NAME"]; 
    if ($TEF_CODE == $type_element ) $selected="selected ";
    else $selected ="";
    echo "<option value='".$TEF_CODE."' $selected>".$TEF_NAME."</option>\n";
}
echo "</select></td>";

if ( check_rights($_SESSION['id'], 17)) {
   echo "<td><input type='button'  class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('upd_element_facturable.php?action=insert&type_element=$type_element');\"></td>";
}
else echo "<td></td>";

echo "</tr><tr><td colspan=3>";
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
$numberrows=mysqli_num_rows($result1);

echo "</td></tr></table>";

if ( $number > 0 ) {
echo "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class='TabHeader'>";
echo "<td width=120 align=center>
        <a href=element_facturable.php?order=S_CODE class=TabHeader>Section</a></td>";
echo "<td width=170><a href=element_facturable.php?order=TEF_NAME class=TabHeader>Type</a></td>";
echo "<td width=380 align=center>
    <a href=element_facturable.php?order=EF_NAME class=TabHeader>Nom</a></td>"; 
echo "<td width=100 align=left>
    <a href=element_facturable.php?order=EF_PRICE class=TabHeader>Prix unitaire ".$default_money_symbol."</a></td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while ($row=@mysqli_fetch_array($result1)) {
    $EF_ID = $row["EF_ID"];
    $TEF_CODE = $row["TEF_CODE"];    
    $TEF_NAME = $row["TEF_NAME"];    
    $EF_NAME = $row["EF_NAME"];    
    $EF_PRICE = $row["EF_PRICE"];    
    $S_CODE = $row["S_CODE"];
    $revision=$mydarkcolor;

    $i=$i+1;
    if ( $i%2 == 0 ) {
          $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
      
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager($EF_ID)\" >";
    
    echo "<td align=center >$S_CODE</td>";  
    echo "<td  align=left>$TEF_NAME</td>";  
    echo "<td align=left >$EF_NAME</td>
            <td align=left >$EF_PRICE</td>
    </tr>"; 
}
echo "</table>";
}
else {
    echo "<span class=small>Pas d'éléments facturables configurés.</span>";
}
if ( $from == 'top' )
    echo "<p><input type='button'  class='btn btn-default' value='Retour' name='annuler' onclick='javascript:history.back(1);'>";
else {
    if ( $evenement_facture > 0 ) $url="evenement_facturation_detail.php?evenement=".$evenement_facture;
    else $url="parametrage.php";
    echo "<p><input type='button'  class='btn btn-default' value='Retour' name='annuler' onclick=\"redirect('".$url."');\">";
}

echo "</div>";
writefoot();
?>
