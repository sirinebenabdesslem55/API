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
check_all(52);
$id=$_SESSION['id'];
get_session_parameters();
writehead();
if ( ! check_rights($id,40)) {
    test_permission_level(52);
    if ( is_lowest_level($_SESSION['SES_SECTION']) ) $filter = $_SESSION['SES_PARENT'];
    else $filter = $_SESSION['SES_SECTION'];
}
if ( $nbsections > 0 ) $filter = 0;

$possibleorders= array('S_CODE','S_DESCRIPTION','S_PARENT','NIV','SECTION_PARENT','S_ID_RADIO','S_AFFILIATION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='S_CODE';

if ( $order == 'S_AFFILIATION' or  $order == 'S_ID_RADIO' ) $order .= " desc";
$disabled="disabled";

?>
<script language="JavaScript">

function displaymanager(p1){
    self.location.href="upd_section.php?S_ID="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function redirect(section, niv) {
    url = "departement.php?filter="+section+"&niv="+niv;
    self.location.href = url;
}

</script>
<?php
echo "</head>";

$querycnt="select count(*) as NB";
$query1="select distinct s.S_ID, s.S_CODE, s.S_DESCRIPTION, s.S_PARENT, s.NIV, n.S_INACTIVE, n.S_ORDER,
         concat(p.S_CODE,' - ',p.S_DESCRIPTION) as 'SECTION_PARENT', n.S_ID_RADIO, n.S_AFFILIATION";
$queryadd=" from section n, section_flat s left join section p on s.S_PARENT = p.S_ID";
$queryadd .=" where n.S_ID = s.S_ID ";
if ( intval($niv) > 0 )  $queryadd .= " and s.NIV=".$niv;
if ( $filter <> 0 ) {
    $queryadd .= " and (s.S_PARENT in (".get_family("$filter").") or s.S_ID=".$filter.")";
}
if($searchdep <> ''){
     $lower_search="%".strtolower($searchdep)."%";
    $queryadd .= "\n and (lower(s.S_CODE) like '$lower_search' or lower(s.S_DESCRIPTION) like '$lower_search')";
}
$querycnt .= $queryadd;
$query1 .= $queryadd." order by ". $order;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

if ( $nbsections == 0 ) {
    $T="Liste des sections: ".my_ucfirst(implode(", ", $levels));
    if ( $niv > 0 ) {
        $T="Liste des ".$levels[$niv];
        if ( substr($levels[$niv], -1) <> "s" ) $T .= "s";
    }
}
else $T="Liste des sections";
echo "<body>";
echo "<div align=center class='table-responsive'><font size=4><b>".$T."</b></font> <span class='badge'>$number</span>";

// choix section

echo "<form name='formf' action='departement.php'>";
echo "<table class='noBorder'><tr>";
if ( $nbsections == 0  ) {
    if ( check_rights($id,40) ) echo "<td rowspan=3 width=55><a href='section.php' title=\"Vue sous forme d'organigramme\"><i class='fa fa-sitemap fa-2x'></i></a></td>";
    echo "<td align=right>Filtre ".choice_section_order('departement.php')."</td>
        <td align=left>
        <select id='filter' name='filter' 
        title=\"Choisir un filtre géographique\"
        onchange=\"redirect( this.value, '$niv')\">";
    if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
    else $sectionorder=$defaultsectionorder;
    display_children2(-1, 0, $filter , $nbmaxlevels , $sectionorder);
    echo "</select>";
    echo "</td>";
}
if ( check_rights($id, 55)) {
    echo "<td rowspan=3 width=100 align=right>";
       $query="select count(1) as NB from section";    
       $result=mysqli_query($dbc,$query);
       $row=@mysqli_fetch_array($result);
       if ( $row["NB"] <= $nbmaxsections )
           echo "<a class='btn btn-default' href='#' onclick=\"bouton_redirect('ins_section.php');\"><i class='fa fa-plus' ></i> Ajouter</a>";
    else
           echo "<font color=red>
               <b>Vous ne pouvez plus ajouter de sections <br>(maximum atteint: $nbmaxsections)</b></font>";
    echo "</td>";
}
    
if ( $nbsections == 0  ) {
    echo "<tr><td align=right>Montrer</td>
            <td><select id='niv' name='niv' 
            title=\"Montrer un niveau géographique\"
            onchange=\"redirect( '$filter', this.value)\">
            <option value='0'>Tous les niveaux de l'organigramme</option>";
    $query2="select NIV from section_flat where S_ID=".$filter;
    $query2="select NIV from section_flat where S_ID=".$filter;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $filterniv=$row2[0];
    for ( $i = 1; $i < $nbmaxlevels ; $i++) {
        if ( $niv == $i ) $selected='selected';
        else $selected='';
        $T = $levels[$i];
        if ( substr($levels[$i], -1) <> "s" ) $T .= "s";
        if ( $i == 0 or $i >= $filterniv ) echo "<option value='".$i."' $selected>les ".$T." seulement</option>";
    }
    echo "</select></td></tr>";

    echo "<tr><td align=right >Recherche</td>";
    echo "<td align=left><input type=text name=searchdep value=\"".preg_replace("/\%/","",$searchdep)."\" size=30 title=\"Saisissez un mot recherché (dans le code ou la description)\"/>";
    echo " <input type='submit'  class='btn btn-default' name='Go' value='go'>";
    if ( $searchdep <> "" ) {      
        echo " <a href=departement.php?searchdep= title='effacer le critère de recherche'><i class='fa fa-eraser fa-lg' style='color:pink;'></i></a>";
    }
}
echo "</td></tr></table></form>";
echo "<p><table class='noBorder' ><tr><td>";
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
echo "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

if ( $syndicate == 1 ) $t="adhérents";
else $t="personnes";

echo "<tr class='TabHeader'><td width=190 align=left>
            <a href=departement.php?order=S_CODE class=TabHeader>Code</td>";
echo "     <td width=280 align=left>
            <a href=departement.php?order=S_DESCRIPTION class=TabHeader>Description</a></td>";
echo "    <td width=120 align=center>
            <a href=departement.php?order=NIV class=TabHeader>Type</td>";
echo "    <td width=130 align=center class=TabHeader>Nb ".$t."</td>";
if ( $gardes == 1 )    {
    echo "    <td width=30 align=center>
            <a href=departement.php?order=S_ORDER class=TabHeader title='Ordre pour les gardes'>Ordre</td>";
}
if ( $nbsections == 0 )
    echo "<td width=350 align=center>
            <a href=departement.php?order=SECTION_PARENT class=TabHeader>Dépend de</td>";
if ( $assoc ) {
    echo "<td width=80 align=center>
            <a href=departement.php?order=S_ID_RADIO class=TabHeader>ID Radio</td>";
    echo "<td width=80 align=center>
            <a href=departement.php?order=S_AFFILIATION class=TabHeader>Num Affiliation</td>";
}
echo " </tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result1)) {

    if ( $S_INACTIVE == 1 )
        $inac=" <i class='fa fa-exclamation-triangle' style='color:orange;' title='section inactive, pas affichée sur le site public'></i>";
    else $inac="";

    $nb=get_section_tree_nb_person($S_ID);
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
    
    echo "<tr bgcolor=$mycolor 
          onMouseover=\"this.bgColor='yellow'\" 
          onMouseout=\"this.bgColor='$mycolor'\"   
          onclick=\"this.bgColor='#33FF00'\">";
    echo "<td align=left onclick='displaymanager($S_ID)'><b>".$S_CODE."</b></td>";
    echo "<td onclick='displaymanager($S_ID)'>".$S_DESCRIPTION." ".$inac."</td>";
    echo "<td onclick='displaymanager($S_ID)' align=center>".$levels[$NIV]."</td>";
    echo "<td onclick='displaymanager($S_ID)' align=center>".$nb."</td>";
    if ( $gardes == 1 ) {
        echo "    <td onclick='displaymanager($S_ID)' align=center>".$S_ORDER."</td>";
    }
    if ( $nbsections == 0 )
        echo "<td onclick='displaymanager($S_ID)' class=small>".$SECTION_PARENT."</td>";
    if ( $assoc ) {
        echo "<td onclick='displaymanager($S_ID)' class=small align=center>".$S_ID_RADIO."</td>";
        echo "<td onclick='displaymanager($S_ID)' class=small align=center>".$S_AFFILIATION."</td>";
    }
    echo "</tr>";
      
}
echo "</table></div>";
writefoot();
?>
