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
check_all(37);
$id=$_SESSION['id'];
writehead();
get_session_parameters();
test_permission_level(37);

$possibleorders= array('TC_LIBELLE','C_NAME','S_CODE','C_DESCRIPTION','C_DESCRIPTION','C_PARENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TC_LIBELLE';

// Libellé événement
$lib=((isset ($_GET["lib"]))?"%".secure_input($dbc,$_GET["lib"])."%":"%");

?>
<script type='text/javascript' src='js/company.js'></script>
<?php

$query1="select C_ID, company.TC_CODE, C_NAME, company.S_ID, C_DESCRIPTION, C_ADDRESS, C_ZIP_CODE, C_CITY, C_EMAIL, C_PHONE, C_FAX, C_CONTACT_NAME, TC_LIBELLE, S_CODE, C_PARENT
        FROM company, type_company, section 
        where section.S_ID= company.S_ID
        and company.TC_CODE=type_company.TC_CODE";
if ( $typecompany <> 'ALL' ) $query1 .=    " AND type_company.TC_CODE='".$typecompany."'";

if ( $subsections == 1 ) {
      $query1 .= "\nand company.S_ID in (".get_family("$filter").")";
}
else {
      $query1 .= "\nand company.S_ID =".$filter;
}

if($lib<>'%'){
    $query1 .= "\n and company.C_NAME like '$lib'";
}
    
$query1 .=" order by ". $order;
if ( $order == 'C_PARENT' ) $query1 .=" desc";

if ( $order <> 'C_NAME') $query1 .=" ,C_NAME asc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<div align=center><table class='noBorder'>
      <font size=4><b> Entreprises Clientes </b></font> <span class='badge'>$number</span></td></tr></table>";

echo "<form name='formf' action='company.php'>";
echo "<table class='noBorder' >";
echo "<tr height=60>";


//filtre section
echo "<td width=120 align=right>".choice_section_order('company.php')."</td>";
echo "<td><select id='filter' name='filter' title='filtre par section'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$subsections."','".$typecompany."')\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
    
//filtre type
echo "<br><br><select id='typecompany' name='typecompany' 
    onchange=\"orderfilter('".$order."','".$filter."','".$subsections."',document.getElementById('typecompany').value)\">";
echo "<option value='ALL'";    
if ($typecompany == 'ALL' ) echo " selected ";    
echo ">Tous types</option>";
$query2="select TC_CODE,TC_LIBELLE from type_company order by TC_CODE asc";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
    $TC_CODE=$row["TC_CODE"];
    $TC_LIBELLE=$row["TC_LIBELLE"];
    echo "<option value='".$TC_CODE."'";
    if ($TC_CODE == $typecompany ) echo " selected ";
    echo ">".$TC_LIBELLE."</option>\n";
}
echo "</select>";
    
echo "</td>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<td align=center width=100><input type='checkbox' name='sub' $checked
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value, this,'".$typecompany."')\"/>
       <font size=1>inclure les<br>$sous_sections</td>";
}

echo "<td align=center></td>";
echo "<td><input type='button' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('ins_company.php?type=$typecompany');\"></td>";

echo "</tr><tr>
<td align=right>Recherche par nom</td><td colspan=3 >";
echo "<input type=\"text\" name=\"lib\" value=\"".preg_replace("/\%/","",$lib)."\" size=\"30\" alt=\"\" title=\"Utilisez le signe % pour remplacer des caractères\"/>";
echo " <input type='submit' class='btn btn-default' value='go'></td>";


echo "</tr><tr><td colspan=4>";
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
echo "</form>";

echo "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class=TabHeader>
    <td width=150 align=center><a href=company.php?order=TC_LIBELLE class=TabHeader>Type</a></td>
    <td width=250 align=center><a href=company.php?order=C_NAME class=TabHeader>Nom</a></td>
    <td width=200 align=center><a href=company.php?order=C_DESCRIPTION class=TabHeader>Description</a></td>
    <td width=100 align=center><a href=company.php?order=S_CODE class=TabHeader>Section</a></td>
    <td width=150 align=center><a href=company.php?order=C_PARENT class=TabHeader>Etablissement principal</a></td>
    <td width=20 align=center class=TabHeader>Nombre</td>
</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while ($row=@mysqli_fetch_array($result1)) {
         $C_NAME=$row["C_NAME"];
        $C_ID=$row["C_ID"];
        $TC_LIBELLE=$row["TC_LIBELLE"];
        $C_DESCRIPTION=$row["C_DESCRIPTION"];
        $S_ID=$row["S_ID"];
        $S_CODE=$row["S_CODE"];
        $C_PARENT=$row["C_PARENT"];
      $i=$i+1;
      if ( $i%2 == 0 ) {
           $mycolor=$mylightcolor;
      }
      else {
           $mycolor="#FFFFFF";
      }
      
    $query2="select count(*) as NB from pompier where C_ID = ".$C_ID;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
      
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
        onclick=\"this.bgColor='#33FF00'; displaymanager('$C_ID')\" >
            <td align=left>$TC_LIBELLE</td>
            <td align=left><B>$C_NAME</B></td>
            <td align=center>$C_DESCRIPTION</td>
          <td align=center>$S_CODE</td>
          <td align=center><a href=upd_company.php?C_ID=".$C_PARENT.">".get_company_name($C_PARENT)."</a></td>
          <td align=center>".$row2["NB"]."</td>
      </tr>";
      
}
echo "</table>";
writefoot();
?>
