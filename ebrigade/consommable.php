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
check_all(42);
get_session_parameters();
writehead();
test_permission_level(42);

$possibleorders= array('CC_CODE','TCO_DESCRIPTION','TC_DESCRIPTION','C_NOMBRE','S_CODE','C_DATE_PEREMPTION','C_MINIMUM','C_LIEU_STOCKAGE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='tc.CC_CODE,tc.TC_DESCRIPTION';

if ( is_numeric($type_conso)) {
   $query="select CC_CODE from type_consommable where TC_ID='".$type_conso."'";
   $result=mysqli_query($dbc,$query);
   $row=@mysqli_fetch_array($result);
   $catconso=$row["CC_CODE"];
}
else $catconso=$type_conso;


if ( $catconso == 'ALL' ) {
    $picture = "<i class='fa fa-coffee fa-lg fa-3x' style='color:saddlebrown;'></i>";
    $cmt='Tous types de consommables';
    $title=$cmt;
}
else {
    $query="select CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE 
        from categorie_consommable
        where CC_CODE='".$catconso."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $cmt=$row["CC_NAME"];
    $title=$row["CC_DESCRIPTION"];
    $picture="<i class='fa fa-".$row["CC_IMAGE"]." fa-3x' style='color:saddlebrown;' title=\"".$title."\"></i>";
}
?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.conso{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/consommable.js'></script>
<?php

$querycnt="select count(1) as NB";
$query1="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, c.C_MINIMUM, c.C_DATE_ACHAT,  
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION, C_LIEU_STOCKAGE,
        case 
        when c.C_DATE_PEREMPTION is null then 1000
        else datediff(c.C_DATE_PEREMPTION, '".date("Y-m-d")."') 
        end as NBDAYSPEREMPTION,
        c.C_MINIMUM - c.C_NOMBRE as DIFF,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE, tc.TC_PEREMPTION,
        tum.TUM_DESCRIPTION, tum.TUM_CODE,
        tco.TCO_DESCRIPTION,tco.TCO_CODE,
        cc.CC_NAME, cc.CC_IMAGE,
        s.S_CODE";
        
$queryadd=" \n    from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
where c.TC_ID = tc.TC_ID
and tc.CC_CODE = cc.CC_CODE
and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
and tc.TC_UNITE_MESURE = tum.TUM_CODE
and s.S_ID=c.S_ID";

if ( $type_conso <> 'ALL' ) $queryadd .= "\n and (c.TC_ID='".$type_conso."' or tc.CC_CODE='".$type_conso."')";

// choix section
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $queryadd .= "\nand c.S_ID in (".get_family("$filter").")";
}
else {
    $queryadd .= "\nand c.S_ID =".$filter;
}

$querycnt .= $queryadd;
if ( $order =='C_MINIMUM' ) $query1 .= $queryadd." order by DIFF desc";
else if ( $order == 'CC_CODE' ) $query1 .= $queryadd." order by tc.CC_CODE,tc.TC_ID";
else if ( $order == 'C_NOMBRE'  ) $query1 .= $queryadd." order by ". $order." desc";
else if ( $order == 'C_DATE_PEREMPTION' )  $query1 .= $queryadd." order by NBDAYSPEREMPTION asc";
else $query1 .= $queryadd." order by ". $order;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width =60>".$picture."</td><td>
      <font size=4><b> ".ucfirst($cmt)."</b></font> <span class='badge'>".$number."  articles</span></td>
      <td><a href='#'>
        <i class='far fa-file-excel fa-2x' style='color:green;' id='StartExcel' height='24' border='0' title='Exporter la liste des produits consommables dans un fichier Excel' 
        onclick=\"window.open('consommable_xls.php?filter=$filter&type_conso=$type_conso&subsections=$subsections')\" /></i>
        </a></td>
      </tr></table><p>";

echo "<table class='noBorder' >";


//filtre section
echo "<tr>";
echo "<td>".choice_section_order('consommable.php')."</td>";
echo "<td><select id='filter' name='filter' 
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$type_conso."','".$subsections."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td> ";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<td><input type='checkbox' name='sub' $checked
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value,'".$type_conso."', this)\"/>
       <font size=1>inclure les $sous_sections</td>";
}
echo "</tr>";

//filtre type_conso
echo "<tr><td>Type </td>
         <td align=left><select id='type_conso' name='type_conso' 
onchange=\"orderfilter('".$order."','".$filter."',document.getElementById('type_conso').value,'".$subsections."')\">";

$query2="select tc.TC_ID, tc.CC_CODE, cc.CC_NAME,tc.TC_DESCRIPTION,tc.TC_CONDITIONNEMENT,tc.TC_UNITE_MESURE,
            tc.TC_QUANTITE_PAR_UNITE , tum.TUM_CODE, tum.TUM_DESCRIPTION, tco.TCO_DESCRIPTION, tco.TCO_CODE
            from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
            where cc.CC_CODE = tc.CC_CODE
            and tco.TCO_CODE = tc.TC_CONDITIONNEMENT
            and tum.TUM_CODE = tc.TC_UNITE_MESURE
            order by tc.CC_CODE,tc.TC_DESCRIPTION asc";
$result2=mysqli_query($dbc,$query2);
if ( $catconso == 'ALL' ) $selected="selected ";
else $selected ="";
$prevCat='';
echo "<option value='ALL' $selected>Tous les types de consommables</option>\n";
while (custom_fetch_array($result2)) {
    $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
    if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .="s";
    if ( $prevCat <> $CC_CODE ){
           echo "<option class='categorie' value='".$CC_CODE."'";
           if ($CC_CODE == $type_conso ) echo " selected ";
        echo ">".$CC_NAME."</option>\n";
        $prevCat=$CC_CODE;
    }
    if ($TC_ID == $type_conso ) $selected="selected ";
    else $selected ="";
    if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s)";
    else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.")";
    else $label = $TC_DESCRIPTION;
    echo "<option class='conso' value='".$TC_ID."' $selected>".$label."</option>\n";
}
echo "</select></td>";

if ( check_rights($_SESSION['id'], 71)) {
   echo "<td><input type='button' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('upd_consommable.php?action=insert&type_conso=$type_conso');\"></td>";
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

//echo $query1;

$result1=mysqli_query($dbc,$query1);
$numberrows=mysqli_num_rows($result1);

echo "</td></tr></table>";

if ( $number > 0 ) {
echo "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class='TabHeader'>";
if ( $type == 'ALL' ) {
echo "<td width=280 align=left class=TabHeader>
    <a href=consommable.php?order=CC_CODE class=TabHeader>Catégorie</a>";
}
echo "<td width=230 align=left>
    <a href=consommable.php?order=TC_ID class=TabHeader>Type</a>"; 
echo "<td width=40 align=left>
    <a href=consommable.php?order=C_NOMBRE class=TabHeader>Stock</a></td>";
echo "<td width=40 align=center>
    <a href=consommable.php?order=C_MINIMUM class=TabHeader title='Stock minimum, commander si le stock est inférieur' >Min.</a></td>";
echo "<td width=160 align=left>
    <a href=consommable.php?order=TCO_DESCRIPTION class=TabHeader>Conditionnement</a></td>";
echo "<td width=130 align=left>
        <a href=consommable.php?order=S_CODE class=TabHeader>Section</a></td>";
echo "<td width=120 align=left><a href=consommable.php?order=TC_DESCRIPTION class=TabHeader>Description</a></td>
    <td width=80 align=left><a href=consommable.php?order=C_DATE_PEREMPTION class=TabHeader>Date limite.</a></td>";
echo "<td width=150 align=left>
    <a href=consommable.php?order=C_LIEU_STOCKAGE class=TabHeader>Lieu stockage</a></td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {
    if ( $C_MINIMUM == 0 ) $C_MINIMUM ="";
    $TC_DESCRIPTION = ucfirst($TC_DESCRIPTION);
    if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .='s';

    $revision=$mydarkcolor;

    $class='blue12';
    if ( $C_DATE_PEREMPTION <> '' ) {
        if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 0 ) $class='red12';
        else if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 30 ) $class='orange12';
        else $class='green12';
    }
    
    if ( $TCO_CODE == 'PE' ) $conditionnement =  $TUM_DESCRIPTION."s";
    else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $conditionnement = $TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION;
    else $conditionnement = "";
    
    $i=$i+1;
    if ( $i%2 == 0 ) {
          $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
    if ( $C_NOMBRE < $C_MINIMUM ) $class2="red12";
    else $class2="green12";
      
    echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager($C_ID)\" >";
    if ( $type == 'ALL' ) {    
        echo "<td  align=left><i class='fa fa-".$CC_IMAGE." fa-lg' style='color:saddlebrown;'></i> $CC_NAME</td>";
    }   
    echo "<td class=$class><B>$TC_DESCRIPTION</B></td>
        <td class=$class2>$C_NOMBRE</td>
        <td >$C_MINIMUM</td>
        <td >$conditionnement</td>
        <td >$S_CODE</td>
        <td class=small2>$C_DESCRIPTION</td>
        <td class=$class>$C_DATE_PEREMPTION</td>
        <td >$C_LIEU_STOCKAGE</td>
    </tr>"; 
}
echo "</table>";
} // if $number > 0
else {
    echo "<span class=small>Pas de produits consommables.</span>";
}
echo "</div>";

writefoot();
?>
