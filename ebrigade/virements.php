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
check_all(53);
$id=$_SESSION['id'];
$highestsection=get_highest_section_where_granted($id,53);

get_session_parameters();

// vérifier qu'on a les droits d'afficher pour cette section
$list = preg_split('/,/' , get_family("$highestsection"));
if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'MONTANT','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

writehead();
$curdate=date('d-m-Y');

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script language="JavaScript">

function orderfilter2(p1,p2,p3,p4,p5){
    if (p3.checked) s = 1;
    else s = 0;
    if (p4.checked) i = 1;
    else i = 0;
    url="virements.php?order="+p1+"&filter="+p2+"&subsections="+s+"&include_old="+i+"&compte_a_debiter="+p5;
    self.location.href=url;
    return true
}

function displaymanager(p1){
    self.location.href="upd_personnel.php?from=cotisation&pompier="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

</script>
<?php
echo "</head>";

$querycnt="select count(*) as NB";

$query1 = "select p.P_ID, p.P_NOM, p.P_PRENOM, pc.PC_ID, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE,
            pc.COMMENTAIRE, pc.MONTANT, s.S_ID, s.S_CODE, p.P_PROFESSION,
            date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
            date_format(p.P_FIN,'%d-%m-%Y') P_FIN, 
            pc.ETABLISSEMENT, pc.GUICHET, pc.COMPTE, pc.CODE_BANQUE, pc.REMBOURSEMENT, pc.COMPTE_DEBITE";

$queryadd=" from personnel_cotisation pc, pompier p, section s
            where p.P_ID = pc.P_ID
            and p.P_SECTION = s.S_ID
            and pc.REMBOURSEMENT = 1
            and pc.TP_ID=2";
    
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
      $queryadd .= "\n and p.P_SECTION in (".get_family("$filter").")";
}
else {
      $queryadd .= "\n and p.P_SECTION =".$filter;
}

if ( $compte_a_debiter > 0 ) {
    $queryadd .= "\n and pc.COMPTE_DEBITE =".$compte_a_debiter;
}

if ( $include_old == 0 ) $queryadd .= "\n and p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";

if ( $dtdb <> "" ) {
    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $queryadd .="\n and pc.PC_DATE  >= '$year1-$month1-$day1'";
}
if ( $dtfn <> "" ) {
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $queryadd .="\n and pc.PC_DATE <= '$year2-$month2-$day2'";
}

$querycnt .= $queryadd;
$query1 .= $queryadd." order by ". $order;
if ( $order == "P_DATE_ENGAGEMENT" or  $order == "P_FIN" or  $order == "PC_DATE" or  $order == "MONTANT") $query1 .= " desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];


$title='Gestion des virements';
echo "<body>";
echo "<div align=center class='table-responsive'>
<form name='forme' id='forme' method=post>
<table class='noBorder'><tr><td><i class='fa fa-euro-signfa-2x' style='color:black;'></i></td>
<td><font size=4><b>$title</b> </font><span class='badge'>$number</span> virements</td></tr></table>";
echo "<p><table class='noBorder' >";

// section
echo "<tr>";
echo "<td align=right>".choice_section_order('virements.php')."</td>";
echo "<td colspan=2><select id='filter' name='filter' title='filtre par section'
        onchange=\"orderfilter2('".$order."',document.getElementById('filter').value,document.getElementById('sub'),document.getElementById('include_old'),'".$compte_a_debiter."')\">";
$level=get_level($highestsection);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";
if ( check_rights($id, 24))
    display_children2(-1, 0, "$filter", $nbmaxlevels, $sectionorder);
else {
       echo "<option value='$highestsection' $class >".
              get_section_code($highestsection)." - ".get_section_name($highestsection)."</option>";
              display_children2($highestsection, $level +1, $filter, $nbmaxlevels);
}
echo "</select></td> ";
echo "</tr>";

// inclure les sous-sections et les anciens membres
echo "<td></td><td colspan=2>";
if ($subsections == 1 ) $checked='checked';
else $checked='';
echo "<input type='checkbox' name='sub' id='sub' $checked title='cocher pour inclure le personnel des centres'
     onClick=\"orderfilter2('".$order."','".$filter."', document.getElementById('sub'),document.getElementById('include_old'),'".$compte_a_debiter."')\"/>
     <font size=1>inclure les $sous_sections ";
if ($include_old == 1 ) $checked='checked';
else $checked='';
if ( $syndicate ==1 ) $anciens="radiés et suspendus ";
else $anciens="anciens membres";
echo "<input type='checkbox' id='include_old' name='include_old' $checked title='cocher pour inclure les $anciens'
    onClick=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),document.getElementById('include_old'),'".$compte_a_debiter."')\"/>
      <font size=1>inclure les ".$anciens."</td>";
echo "</tr>";


// définir depuis quel compte part le virement
echo "<tr><td>Virement depuis</td>";
echo "<td colspan=2>";
$query2="select cb.CB_ID, cb.ETABLISSEMENT, cb.GUICHET, cb.COMPTE, cb.CODE_BANQUE, cb.BIC, cb.IBAN, s.S_CODE, s.S_DESCRIPTION
        from section s, compte_bancaire cb
        where cb.CB_TYPE='S'
        and cb.CB_ID = s.S_ID";
$result2=mysqli_query($dbc,$query2);
echo "<select name='compte_a_debiter'  id='compte_a_debiter'
            onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),document.getElementById('include_old'),this.value);\">";
while ($row2=@mysqli_fetch_array($result2)) {
    $_CB_ID=$row2["CB_ID"];
    $_S_CODE=$row2["S_CODE"];
    $_BIC=$row2["BIC"];
    $_IBAN=$row2["IBAN"];
    if ( $_CB_ID <= 1 ) $_S_DESCRIPTION = "";
    else $_S_DESCRIPTION="- ".$row2["S_DESCRIPTION"];
    if ( $compte_a_debiter == $_CB_ID ) $selected='selected';
    else $selected='';
    if ( $_IBAN <> "" )
        echo "<option value='$_CB_ID' $selected>$_S_CODE $_S_DESCRIPTION : $_BIC $_IBAN </option>";
}
echo "</select>";
echo "</td></tr>";

// Choix Dates
echo "</form><form name='formf' id='formf'><tr><td align=right >Virements entre le </td>
<td align=left><input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtdb)
            style='width:100px;'>";
echo "</td>";
echo "</tr>";

echo "<tr><td align=right > et le </td>
<td align=left><input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtfn)
            style='width:100px;'>";
echo " <input type='submit' class='btn btn-default' value='go'>";

echo "</td></tr>";
        
echo "<tr><td colspan=3>";
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

echo "</td></tr>";
echo "</table>";

echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class='TabHeader'>";
echo "     <td width=180 align=center>
            <a href=virements.php?order=P_NOM class=TabHeader>Bénéficiaire</a></td>";
if ( $syndicate == 1 ) {          
    echo "<td width=40 align=center >
         <a href=virements.php?order=P_PROFESSION class=TabHeader>Prof.</a></td>";
}    
echo "<td class=TabHeader align=center width=100><a href=virements.php?order=P_SECTION class=TabHeader>Section</a></td>";
echo "<td class=TabHeader align=center width=50><a href=virements.php?order=P_DATE_ENGAGEMENT class=TabHeader>Entrée</a></td>";  
echo "<td class=TabHeader align=center width=50><a href=virements.php?order=P_FIN class=TabHeader>Sortie</a></td>";    
echo "<td class=TabHeader align=center width=80><a href=virements.php?order=MONTANT class=TabHeader>Montant</a></td>";
echo "<td class=TabHeader align=center width=100><a href=virements.php?order=PC_DATE class=TabHeader>Date virement</a></td>"; 
echo "<td class=TabHeader align=center width=130><a href=virements.php?order=COMMENTAIRE class=TabHeader>Commentaire</a></td>";
     
echo " </tr>";
// ===============================================
// le corps du tableau
// ===============================================

$fraction=get_fraction($periode);

$i=0;
$people="";

while (custom_fetch_array($result1)) {

    $i++;
    if ( $i%2 == 0 ) {
       $mycolor="$mylightcolor";
    }
    else {
          $mycolor="#FFFFFF";
    }
    
    echo "<tr bgcolor=$mycolor 
         onMouseover=\"this.bgColor='yellow'\" 
         onMouseout=\"this.bgColor='$mycolor'\"   
         onclick=\"this.bgColor='#33FF00'\">";

    echo "<td><a href=upd_personnel.php?from=cotisation&pompier=".$P_ID.">".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a></td>";

    if ( $syndicate == 1 ) { 
        echo "<td align=center onclick='displaymanager($P_ID)'>$P_PROFESSION</td>";
    }

    echo "<td align=center onclick='displaymanager($P_ID)'><a href=upd_section.php?S_ID=$S_ID>$S_CODE</a></td>";    
    echo "<td align=center onclick='displaymanager($P_ID)' class=small2>$P_DATE_ENGAGEMENT</td>";        
    echo "<td align=center onclick='displaymanager($P_ID)' class=small2>$P_FIN</td>";
    echo "<td align=center><a href=cotisation_edit.php?from=V&paiement_id=".$PC_ID."&pid=".$P_ID."&action=update&rembourse=1> $MONTANT $default_money_symbol</td>";
    echo "<td align=center>$PC_DATE</td>";        
    echo "<td align=center class=small2>$COMMENTAIRE</td>";
    echo "</tr>";
}
echo "</table>";
echo "</form></div>";
writefoot();

?>
