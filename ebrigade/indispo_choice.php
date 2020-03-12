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
check_all(11);
$id=$_SESSION['id'];
get_session_parameters();

$possibleorders= array('P_NOM','TI_CODE','I_STATUS','I_DEBUT','I_FIN','I_COMMENT');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='I_DEBUT';

writehead();
test_permission_level(11);
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<SCRIPT>
function redirect(statut, type, person, dtdb, dtfn, validation,section) {
    url = "indispo_choice.php?statut="+statut+"&type_indispo="+type+"&person="+person+"&dtdb="+dtdb+"&dtfn="+dtfn+"&validation="+validation+"&filter="+section;
    self.location.href = url;
}

function redirect2(statut, type, person, dtdb, dtfn, validation,section, subsection){
    if (subsection.checked) s = 1;
    else s = 0;
    url = "indispo_choice.php?statut="+statut+"&type_indispo="+type+"&person="+person+"&dtdb="+dtdb+"&dtfn="+dtfn+"&validation="+validation+"&filter="+section+"&subsections="+s;
    self.location.href = url;
    return true
}

function displaymanager(p1){
    url="indispo_display.php?code="+p1;
    self.location.href = url;
}

</SCRIPT>

<?php
include_once ("config.php");

echo "<body>";
echo "<div align=center class='table-responsive'>";

$query1="select distinct i.I_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, p.P_OLD_MEMBER, DATE_FORMAT(i.I_DEBUT, '%d-%m-%Y') as I_DEBUT, DATE_FORMAT(i.I_FIN, '%d-%m-%Y') as I_FIN, i.TI_CODE,
        ti.TI_LIBELLE, ti.TI_FLAG, i.I_COMMENT, ist.I_STATUS_LIBELLE, i.I_STATUS, date_format(i.IH_DEBUT,'%H:%i') IH_DEBUT, date_format(i.IH_FIN,'%H:%i') IH_FIN, i.I_JOUR_COMPLET, s.S_CODE
        from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist, section s
        where p.P_ID=i.P_ID
        and p.P_SECTION = s.S_ID
    and i.TI_CODE=ti.TI_CODE
    and i.I_STATUS=ist.I_STATUS";

if ( $subsections == 1 ) 
    $query1 .= "\nand p.P_SECTION in (".get_family("$filter").")";
else 
    $query1 .= "\nand  p.P_SECTION = ".$filter;
if ( $statut <> "ALL") $query1 .= "\nand  p.P_STATUT = '".$statut."'";
if ( $type_indispo <> "ALL") $query1 .= "\nand  ti.TI_CODE = '".$type_indispo."'";
if ( check_rights($id, 12 ) and intval($person) > 0 ) $query1 .= "\nand  p.P_ID = ".$person;
else if (! check_rights($id, 12 )) $query1 .= "\nand  p.P_ID = ".$id;

if ( $validation <> "ALL") $query1 .= "\nand  ist.I_STATUS = '".$validation."'";

if ( $dtdb <> "" ) {
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $query1 .="\n and i.I_FIN   >= '$year1-$month1-$day1'";
}
if ( $dtfn <> "" ) {
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $query1 .="\n and i.I_DEBUT <= '$year2-$month2-$day2'";
}

if ( $order == 'P_NOM' ) $query1 .="\norder by p.P_NOM, p.P_PRENOM, i.I_DEBUT";
else $query1 .="\norder by i.".$order;

if ( $order == 'I_COMMENT' ) $query1 .=" desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

echo "<p><table class='noBorder'><tr><td colspan=2><font size=4><b>Absences du personnel</b></font> <span class='badge'>".$number."</span> ";
if ( check_rights($id, 12 )) echo " <a href='#'><i class='far fa-file-excel fa-2x' style='color:green;' id=\"StartExcel\"
title=\"Extraire ces données dans un fichier Excel\" onclick=\"window.open('indispo_list_xls.php');\"></i></a></td>";
echo "</tr></table>";

echo "<form name=formf>";
echo "<table class='noBorder'>";

if ( check_rights($id, 12 )) {
    //filtre section
    echo "<tr><td width=50% align=right> ".choice_section_order('indispo_choice.php')." </td>";
    echo "<td align=left><select id='filter' name='filter' 
        onchange=\"redirect( '$statut' ,'$type_indispo', '$person', '$dtdb','$dtfn', '$validation',document.formf.filter.options[document.formf.filter.selectedIndex].value)\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select></td> ";
      
    if ( get_children("$filter") <> '' ) {
        if ($subsections == 1 ) $checked='checked';
        else $checked='';
        echo "<tr><td width=50% align=right> Inclure les $sous_sections </td>";
        echo "<td align=left><input type='checkbox' name='sub' $checked 
        onClick=\"redirect2('$statut' ,'$type_indispo', '$person', '$dtdb','$dtfn', '$validation', '$filter',this)\"/></td></tr>";
    }  

    // choix catégorie personnel
    echo "<tr><td width=50% align=right> Catégorie de personnel </td>";
    echo "<td width=50% align=left><select id='menu1' name='menu1' 
    onchange=\"redirect(document.formf.menu1.options[document.formf.menu1.selectedIndex].value, '$type_indispo', '$person', '$dtdb','$dtfn', '$validation','$filter')\">";
    echo "<option value='ALL'>Toutes catégories de personnel </option>\n";
    if ($sdis == 1) {
        $query="select S_STATUT, S_DESCRIPTION from statut 
        where S_STATUT <> 'EXT' and S_CONTEXT = 3";
    }
    else {
        $query="select S_STATUT, S_DESCRIPTION from statut 
        where S_STATUT <> 'EXT' and S_CONTEXT =".$nbsections;
    }
    if ( $army ) $query .= " and S_STATUT not in ('BEN','SAL','ADH','FONC')";
    else if ( $syndicate ) $query .= " and S_STATUT not in ('BEN','ACT','RES','CIV')";
    else $query .= " and S_STATUT not in ('ADH','FONC','ACT','RES','CIV')";
    $query .= " order by S_DESCRIPTION";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if ( $statut == $S_STATUT ) {
            echo "<option value='".$S_STATUT."' selected>".$S_DESCRIPTION."</option>\n";
        }
        else {
            echo "<option value='".$S_STATUT."'>".$S_DESCRIPTION."</option>\n";
        }
    }
    echo "</select><td></tr>";

    // choix personne
    echo "<tr><td width=50% align=right> Nom </td>";
    echo "<td width=50% align=left><select id='menu3' name='menu3' 
    onchange=\"redirect( '$statut','$type_indispo' ,document.formf.menu3.options[document.formf.menu3.selectedIndex].value, '$dtdb','$dtfn', '$validation','$filter')\">";
    echo "<option value='ALL' selected>Toutes les personnes </option>\n";
    $query="select distinct P_ID, P_NOM, P_PRENOM, P_OLD_MEMBER from pompier";
    if ( $subsections == 1 ) 
    $query .= " where  P_SECTION in (".get_family("$filter").")";
    else $query .= " where  P_SECTION = ".$filter;
    $query .=" and P_STATUT <> 'EXT' and P_STATUT <> 'ADH' and P_OLD_MEMBER= 0";
    if ( $statut <> "ALL" ) $query .=" and P_STATUT ='".$statut."'";
    $query .=" order by P_NOM";
    echo "\n<OPTGROUP LABEL=\"Personnel actif\" style=\"background-color:$mylightcolor\">";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if ( $person == $P_ID ) $selected='selected';
        else $selected='';
        echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</option>\n";
    }
    $query="select distinct P_ID, P_NOM, P_PRENOM, P_OLD_MEMBER from pompier";
    if ( $subsections == 1 ) 
        $query .= " where  P_SECTION in (".get_family("$filter").")";
    else $query .= " where  P_SECTION = ".$filter;
    $query .=" and P_STATUT <> 'EXT' and P_STATUT <> 'ADH' and P_OLD_MEMBER> 0";
    if ( $statut <> "ALL" ) $query .=" and P_STATUT ='".$statut."'";
    $query .=" order by P_NOM";
    echo "\n<OPTGROUP LABEL=\"Anciens membres\" style=\"background-color:$mygreycolor\">";
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if ( $person == $P_ID ) $selected='selected';
        else $selected='';
        echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</option>\n";
    }
    echo "</select></td></tr>";
}
else {
    echo "<tr><td width=50% align=right> Nom </td>";
    echo "<td><font size=3><b>".strtoupper($_SESSION['SES_NOM'])." ".ucfirst($_SESSION['SES_PRENOM'])."</b></font> <input type=hidden id='menu3' name='menu3' value='".$id."'></td></tr>";
}

// choix type absence
echo "<tr><td width=50% align=right> Type d'absence </td>";
echo "<td width=50% align=left><select id='menu2' name='menu2' 
onchange=\"redirect( '$statut' ,document.formf.menu2.options[document.formf.menu2.selectedIndex].value, '$person', '$dtdb','$dtfn', '$validation','$filter')\">";
echo "<option value='ALL' selected>Toutes absences </option>\n";
$query="select TI_CODE as _TI_CODE, TI_LIBELLE as _TI_LIBELLE, TI_FLAG as _TI_FLAG
        from type_indisponibilite
        where TI_CODE <> ''";
if ( $gardes == 0 ) {
    $query .= " and TI_CODE <> 'RT' ";
}
$query .= " order by TI_FLAG, TI_CODE ";

echo "<optgroup class='categorie' label=\"Pas de validation\" />\n";
$prev=0;
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $_TI_FLAG == 1 and $prev == 0) {
        echo "<optgroup class='categorie' label=\"Validation nécessaire\" />\n";
        $prev=$_TI_FLAG;
    }
    if ( $type_indispo == $_TI_CODE ) $selected='selected';
    else $selected='';
    echo "<option value='".$_TI_CODE."' $selected>".$_TI_CODE." - ".$_TI_LIBELLE."</option>\n";
}
echo "</select></td></tr>";


// choix etat de la demande
echo "<tr><td width=50% align=right> Etat de la demande </td>";
echo "<td width=50% align=left><select id='menu5' name='menu5' 
onchange=\"redirect( '$statut' ,'$type_indispo', '$person', '$dtdb','$dtfn', document.formf.menu5.options[document.formf.menu5.selectedIndex].value,'$filter')\">";
echo "<option value='ALL' selected>Tous </option>\n";
$query="select distinct I_STATUS, I_STATUS_LIBELLE
        from indisponibilite_status";
$result=mysqli_query($dbc,$query);
while (custom_fetch_array($result)) {
    if ( $validation == $I_STATUS ) {
        echo "<option value='".$I_STATUS."' selected>".$I_STATUS_LIBELLE."</option>\n";
    }
    else {
        echo "<option value='".$I_STATUS."'>".$I_STATUS_LIBELLE."</option>\n";
    }
}
echo "</select></td></tr>";


// Choix Dates
echo "<tr><td align=right >Début:</td><td align=left>
        <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtdb)
            style='width:100px;'>";
echo "</td></tr>";


echo "<tr><td align=right >Fin :</td><td align=left>
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtfn)
            style='width:100px;'>";

echo " <input type='submit' class='btn btn-default' value='go'>";

echo "</td></tr><tr><td></td><td>";
if ( check_rights($id, 11)) {
   echo "<a class='btn btn-default' href='indispo.php?person=$person&section=$filter' ><i class='fa fa-plus' ></i> Ajouter</a></td>";
}
echo "</td></tr>";

echo "</table></form>";


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
$result=mysqli_query($dbc,$query1);

$totalcp=0;

if ( $number > 0 ) {
    echo "<p><table cellspacing=0 border=0>";
    echo "<tr class=TabHeader>
            <td width=200 align=left><a href=indispo_choice.php?order=P_NOM class=TabHeader>Nom</a></td>";
    echo "<td width=100 align=center><a href=indispo_choice.php?order=S_CODE class=TabHeader>Section</a></td>";
    echo "<td width=180 align=center><a href=indispo_choice.php?order=TI_CODE class=TabHeader>Absence</a></td>
            <td width=120 align=center><a href=indispo_choice.php?order=I_DEBUT class=TabHeader>début</a></td>
            <td width=120 align=center><a href=indispo_choice.php?order=I_FIN class=TabHeader>fin</a></td>
            <td width=60 align=center>Durée</td>";
    if ( $type_indispo == 'CP' ||     $type_indispo == 'RTT' ) {
        echo "<td width=60 align=center>Jours ".$type_indispo."</td>";
    }      
    echo " <td width=100 align=center><a href=indispo_choice.php?order=I_STATUS class=TabHeader>Etat demande</a></td>
            <td width=160 align=center><a href=indispo_choice.php?order=I_COMMENT class=TabHeader>Commentaire</a></td>
      </tr>
      ";

    $i=0;
    while (custom_fetch_array($result)) { 
        if ( $P_OLD_MEMBER > 0 ) {
             $cmt="<font color=black title='Attention: Ancien membre'>";
        }
        else $cmt="";

        $i=$i+1;
        if ( $i%2 == 0 ) {
           $mycolor="$mylightcolor";
        }
        else {
           $mycolor="#FFFFFF";
        }
        if ( $I_STATUS == 'VAL' ) $mytxtcolor='green';
        if ( $I_STATUS == 'ANN'  or  $I_STATUS == 'REF' ) $mytxtcolor='red';
        if ( $I_STATUS == 'ATT' or  $I_STATUS == 'PRE' )  $mytxtcolor='orange';
        $abs=my_date_diff($I_DEBUT,$I_FIN) + 1;
        if ( $TI_FLAG == 0 ) $label="<span class=small title='voir détail'>".$I_STATUS_LIBELLE."</span>";
        else $label="<span style='color:$mytxtcolor;' title='voir détail'><b>".$I_STATUS_LIBELLE."</b></span>";
      
        if ( $I_JOUR_COMPLET == 0 ) {
              if ( $abs == 1 ) {
                   if ( substr($IH_FIN,0,1) == '0' ) $fin = substr($IH_FIN,1,1);
                   else  $fin = substr($IH_FIN,0,2);
                   if ( substr($IH_DEBUT,0,1) == '0' ) $debut = substr($IH_DEBUT,1,1);
                   else  $debut = substr($IH_DEBUT,0,2);                   
                   $abs = $fin - $debut;
                   $abs .= ' heures';
              }
              else $abs .= ' jours';
              
              $I_DEBUT=$I_DEBUT." ".$IH_DEBUT;
            $I_FIN=$I_FIN." ".$IH_FIN;
        }
        else if ( $I_JOUR_COMPLET == 2 ) {
            $abs = '1/2 journée';
        }
        else if ( $abs == 1 ) $abs .= ' jour';
        else $abs .= ' jours';
      
        echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" 
        onclick=\"this.bgColor='#33FF00';\" >
            <td onclick=\"displaymanager('$I_CODE')\"><a href='upd_personnel.php?pompier=".$P_ID."' title='voir la fiche personnel'>".$cmt.strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</a></td>
            <td onclick=\"displaymanager('$I_CODE')\" class=small2>".$S_CODE."</td>
            <td onclick=\"displaymanager('$I_CODE')\" align=left><small>".$TI_CODE." - ".$TI_LIBELLE."</small></td>
            <td onclick=\"displaymanager('$I_CODE')\" class=small2>".$I_DEBUT."</td>
            <td onclick=\"displaymanager('$I_CODE')\" class=small2>".$I_FIN."</td>
            <td onclick=\"displaymanager('$I_CODE')\" class=small2>".$abs."</td>";
        if ( $type_indispo == 'CP' || $type_indispo == 'RTT' ) {
            //compteur de jours de CP utilisés
            $tmp=explode ( "-",$I_DEBUT); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
            $tmp=explode ( "-",$I_FIN); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
            $nbcp=countNonFreeDaysBetweenTwoDates(mktime(0,0,0,$month1,$day1,$year1),mktime(0,0,0,$month2,$day2,$year2));
            if ( $nbcp == 1 and $I_JOUR_COMPLET == 2 ) $nbcp = "0.5";
            if ( $nbcp < 1 ) $d = $nbcp." jour";
            else $d = $nbcp." jours";
        
            $totalcp = $totalcp + $nbcp;
        
            echo "<td onclick=\"displaymanager('$I_CODE')\" class=small2><b>".$d."</b></td>";
        }           
        echo "<td onclick=\"displaymanager('$I_CODE')\" align=center>".$label."</td>
            <td onclick=\"displaymanager('$I_CODE')\" class=small2>".$I_COMMENT."</td>
         </tr>";
    }
   
    echo "</table>";
   
    if ( $type_indispo == 'CP' || $type_indispo == 'RTT' ) {
        if ( $totalcp > 1 ) $j = 'jours';
        else $j = 'jour';
        echo "<p><b>Nombre total de $type_indispo pris sur la période: ".$totalcp." ".$j."</b>";
        if ( $type_indispo == 'CP' and ( intval($person) > 0 )) {
            // droits CP / an
            $queryb="select TS_JOURS_CP_PAR_AN from pompier where P_ID=".intval($person);
            $resultb=mysqli_query($dbc,$queryb);
            $rowb=@mysqli_fetch_array($resultb);
            $droits=intval($rowb[0]);
            if ( $droits > 0 ) {
                echo "<b> (droits annuels $droits jours).";
            }
        }
        else if ( $type_indispo == 'RTT' and ( intval($person) > 0 )) {
            // droits CP / an
            $queryb="select TS_HEURES_A_RECUPERER from pompier where P_ID=".intval($person);
            $resultb=mysqli_query($dbc,$queryb);
            $rowb=@mysqli_fetch_array($resultb);
            $heures=intval($rowb[0]);
            if ( $heures > 0 ) {
                echo "<b> (compteur d'heures à récupérer: $heures heures).";
            }
        }
    }
}
else {
     echo "<p><b>Aucune absence ne correspond aux critères choisis</b>";
}

echo "<p><table class='noBorder'><tr><td><input type='button' class='btn btn-default' value='Retour' onclick='javascript:history.back(1)'></td></tr></table>";
writefoot();
?>
