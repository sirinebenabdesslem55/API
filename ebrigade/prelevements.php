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
$highestsection=get_highest_section_where_granted($_SESSION['id'],53);

get_session_parameters();

if ( ! isset($_GET["periode"]) and $periode =='A' ) {
    $query3="select P_CODE, P_DATE from periode where P_DATE=".date('m');
    $result3=mysqli_query($dbc,$query3);
    $row3=@mysqli_fetch_array($result3);
    if ( $row3[0] <> '' ) $periode=$row3[0];
    else $periode='A';
}

// vérifier qu'on a les droits d'afficher pour cette section
$list = preg_split('/,/' , get_family("$highestsection"));
if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;

writehead();
$curdate=date('d-m-Y');

if ( $bank_accounts == 0 ) {
    echo "Fonction non supportée par votre configuration (bank_accounts désactivé)";
    exit;
}
?>

<script type='text/javascript' src='js/checkForm.js'></script>
<script language="JavaScript">
function bouton_redirect(cible) {
     self.location.href = cible;
}

function orderfilter2(p1,p2,p3,p4){
      if (p2.checked) s = 1;
      else s = 0;
     url="prelevements.php?filter="+p1+"&subsections="+s+"&periode="+p3+"&year="+p4;
     self.location.href=url;
     return true
}

</script>
<?php
echo "</head>";

$querycnt="select count(*) as NB";
$query="select p.P_ID, pc.PERIODE_CODE,  p.P_PROFESSION, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, pc.PC_ID, pc.MONTANT, 
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
        date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
        p.P_SECTION, p.MONTANT_REGUL, s.S_PARENT";
$queryadd=" from  section s,
     pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."' )
     where p.P_SECTION=s.S_ID
     and p.P_NOM <> 'admin'
     and p.P_STATUT <> 'EXT'
     and p.TP_ID  = 1";
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
      $queryadd .= "\nand p.P_SECTION in (".get_family("$filter").")";
}
else {
      $queryadd .= "\nand p.P_SECTION =".$filter;
}
$period_month=get_month_from_period($periode);
if ( $period_month <> "0" ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-".$period_month."-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-".$period_month."-01' or p.P_FIN is null )";
}
else if ( $periode == 'T1' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-04-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-01-01' or p.P_FIN is null )";
}
else if ( $periode == 'T2' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-04-01' or p.P_FIN is null )";
}
else if ( $periode == 'T3' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-10-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-07-01' or p.P_FIN is null )";
}
else if ( $periode == 'T4' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-10-01' or p.P_FIN is null )";
}
else if ( $periode == 'S1' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-01-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'S2' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'A' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN >= '".$year."-01-01' or p.P_FIN is null )";
}

$queryadd_paid = "\nand pc.PC_DATE is not null";
$queryadd_notpaid = "\nand pc.PC_DATE is null";
$queryadd .= "\nand p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";
$querycnt1 = $querycnt.$queryadd.$queryadd_notpaid;
$query1 = $query.$queryadd.$queryadd_notpaid;
$querycnt2 = $querycnt.$queryadd.$queryadd_paid;
$query2 = $query.$queryadd.$queryadd_paid;

$resultcnt=mysqli_query($dbc,$querycnt1);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number1 = $rowcnt[0];

$resultcnt=mysqli_query($dbc,$querycnt2);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number2 = $rowcnt[0];

$nbp = $number1 + $number2;

if ( $syndicate == 1 ) $title='Cotisations par prélèvement des adhérents';
else $title='Cotisations par prélèvement du personnel';

echo "<body>";
echo "<div align=center class='table-responsive'>
<table class='noBorder'><tr><td><i class='fa fa-euro-signfa-2x' style='color:blue;'></i></td>
<td><font size=4><b>$title</b></font> <span class='badge'>$nbp</span> personnes</td></tr></table>";
echo "<p><table class='noBorder' >";

// section
echo "<tr>";
echo "<td>".choice_section_order('prelevements.php')."</td>";
echo "<td><select id='filter' name='filter' title='filtre par section'
        onchange=\"orderfilter2(document.getElementById('filter').value,document.getElementById('sub'),'".$periode."','".$year."')\">";
$level=get_level($highestsection);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td> ";
echo "</tr>";
    
if ($subsections == 1 ) $checked='checked';
else $checked='';
echo "<tr><td></td><td><input type='checkbox' name='sub' id='sub' $checked
      onClick=\"orderfilter2('".$filter."', document.getElementById('sub'),'".$periode."','".$year."')\"/>
      <font size=1>inclure les $sous_sections</td></tr>";

// période
echo "<tr><td>Période cotisation</td>";
$query3="select P_CODE, P_DESCRIPTION from periode order by P_ORDER";
$result3=mysqli_query($dbc,$query3);
echo "<td><select id='periode' name='periode' title='Choisir la période de cotisation'
        onchange=\"orderfilter2('".$filter."',document.getElementById('sub'),document.getElementById('periode').value,'".$year."')\">";
while ($row3=@mysqli_fetch_array($result3)) {
    if ( $row3[0] == $periode ) $selected="selected";
    else $selected="";
    echo "<option value=".$row3[0]." $selected>".$row3[1]."</option>";
}
echo "</select>";
$curyear=date('Y');
$minyear=$curyear - 2;

echo " <select id='year' name='year' title='année de cotisation'
        onchange=\"orderfilter2('".$filter."',document.getElementById('sub'),'".$periode."',document.getElementById('year').value)\">";

for ( $i=0; $i < 6; $i++) {
    $optionyear=$minyear + $i;
    if ( $optionyear == $year ) $selected='selected';
    else  $selected='';
    echo "<option value=".$optionyear." $selected>".$optionyear."</option>";
} 
echo "</select>";
echo "</td></tr>";

// type de paiement
echo "<tr><td>Mode de paiement</td>";
echo "<td><b>Prélèvement</b></td></tr>";

// type de paiement
echo "<tr><td>Personnel concerné</td>";
echo "<td><b>Actifs (radiés et suspendus exclus)</b></td></tr>
</table>";

// ===============================================
// le corps du tableau
// ===============================================

$fraction=get_fraction($periode);
$default_date=date('d-m-Y');

$total = 0;
$total2 = 0;
$reguls = 0;

$result1=mysqli_query($dbc,$query1);
while ($row=@mysqli_fetch_array($result1)) {
    $P_PROFESSION=$row["P_PROFESSION"];
    $P_SECTION=$row["P_SECTION"];
    $S_PARENT=$row["S_PARENT"];
    $MONTANT_REGUL=$row["MONTANT_REGUL"];
    $EXPECTED_MONTANT= get_montant($P_SECTION,$S_PARENT,$P_PROFESSION);
    
    $EXPECTED_MONTANT = round($EXPECTED_MONTANT / $fraction , 2);
    $reguls = $MONTANT_REGUL + $reguls; 
    $total  = $EXPECTED_MONTANT + $MONTANT_REGUL + $total;
}

echo "<p><form name='form' method='post' action='save_prelevements.php'><table class='noBorder'>
 <tr><td colspan=2 width=400><h3>Enregistrer les cotisations par prélèvement:</h3></td></tr>
 <tr ><td><i class='fa fa-circle'></i></td><td><b>".$number1."</b> cotisations doivent encore être enregistrées</td></tr>
 <tr height=20><td><i class='fa fa-circle'></i></td><td>montant total <b>".$total." ".$default_money_symbol."</b> </td></tr>
 <tr height=20><td><i class='fa fa-circle'></i></td><td><b>dont ".$reguls." ".$default_money_symbol."</b> de régularisations</td></tr>
 <tr height=20><td><i class='fa fa-circle'></i></td><td>Date du prélèvement:
 <input type='text' name='date_prelev' id='date_prelev' size=8 onchange='checkDate2(form.date_prelev);' value='".$default_date."'
 title='Saisissez une date au format JJ-MM-AAAA'/></td></tr>
 <input type='hidden' name='filter' value='$filter' >
 <input type='hidden' name='year' value='$year' >
 <input type='hidden' name='periode' value='$periode' >
 <input type='hidden' name='subsections' value='$subsections' >
 <tr height=20><td colspan=2>
 <input type='button' class='btn btn-default' value='Sauver' id='sauver' title='Enregistrer les $number1 cotisations' 
 onclick=\"this.disabled=true; this.value='attendez ...';document.form.submit();\"></td></tr>
 </table></form>";
 
/* $result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
    $MONTANT=$row["MONTANT"];
    $total2  = $MONTANT + $total2;
}
 
$IBAN=get_IBAN('S',"$filter"); */
echo "</div>";
writefoot();
?>
