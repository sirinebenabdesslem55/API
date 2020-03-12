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
$grantedsections=get_all_sections_where_granted($id,53);
get_session_parameters();

// vérifier qu'on a les droits d'afficher pour cette section
if (! in_array($filter,$grantedsections)) {
    $list = preg_split('/,/' , get_family("$highestsection"));
    if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;
}

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'TP_DESCRIPTION','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

writehead();
$curdate=date('d-m-Y');

?>
<script type='text/javascript' src='js/cotisations.js'></script>

<?php
echo "</head>";

$querycnt="select count(*) as NB";

$query1="select p.P_ID, pc.PERIODE_CODE,  p.P_NOM , p.P_PRENOM, pc.MONTANT, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, 
        pc.COMMENTAIRE , pc.NUM_CHEQUE , pc.PC_ID,
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
        date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
        date_format(p.P_DATE_ENGAGEMENT, '%c') MONTH_ENGAGEMENT,
        YEAR(p.P_DATE_ENGAGEMENT) YEAR_ENGAGEMENT,
        date_format(p.P_FIN, '%c') MONTH_FIN,
        YEAR(p.P_FIN) YEAR_FIN,
        p.MONTANT_REGUL,
        p.P_STATUT, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PROFESSION, tp.TP_ID, tp.TP_DESCRIPTION,
        cb.ETABLISSEMENT, cb.GUICHET, cb.COMPTE, cb.CODE_BANQUE, cb.IBAN, cb.BIC,
        s.S_PARENT";

$queryadd=" from  section s, type_paiement tp,
     pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."'  and pc.REMBOURSEMENT=0)
     left join compte_bancaire cb on ( cb.CB_TYPE = 'P' and cb.CB_ID = p.P_ID )
     where p.P_SECTION=s.S_ID
     and p.TP_ID = tp.TP_ID
     and p.P_NOM <> 'admin' 
     and p.P_STATUT <> 'EXT'";
    
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
         $queryadd .= "\nand p.P_SECTION in (".get_family("$filter").")";
}
else {
      $queryadd .= "\nand p.P_SECTION =".$filter;
}

if ($bank_accounts == 1 and  $type_paiement <> 'ALL' ) {
     $queryadd .= "\nand p.TP_ID =".$type_paiement;
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
if ( $paid == 1 ) $queryadd .= "\nand pc.PC_DATE is not null";
else if ( $paid == 0 ) $queryadd .= "\nand pc.PC_DATE is  null";

if ( $include_old == 0 ) $queryadd .= "\nand p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";

$querycnt .= $queryadd;
$query1 .= $queryadd." order by ". $order;
if ( $order == "P_DATE_ENGAGEMENT" or  $order == "P_FIN" or  $order == "PC_DATE") $query1 .= " desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

// DEBUG TRACES
/*
echo "<pre>";
print_r($_GET);
print_r($_POST);
echo $query1;
echo "</pre>";
*/

if ( $syndicate == 1 ) $title='Cotisations des adhérents';
else $title='Cotisations du personnel';

echo "<body><div class='table-responsive' align=center>";
echo "<form name='frmPersonnel' id='frmPersonnel' method='post' action='save_cotisations.php'>";
echo "<table class='noBorder'>
<tr>
<td><i class='fa fa-euro-signfa-3x'></i></td>
<td><font size=4><b>$title</b></font> <span class='badge'>$number</span> personnes</td></tr></table>";
echo "<table class='noBorder' >";

// section
echo "<tr>";
echo "<td>".choice_section_order('cotisations.php')."</td>";
echo "<td><select id='filter' name='filter' title='filtre par section'
        onchange=\"orderfilter2('".$order."',document.getElementById('filter').value,document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."','".$paid."',document.getElementById('include_old'))\">";
$level=get_level($highestsection);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";
if ( check_rights($_SESSION['id'], 24))
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
else {
    if ( $filter == $highestsection ) $selected='selected';
    else $selected='';
       echo "<option value='$highestsection' $class $selected>".get_section_code($highestsection)." - ".get_section_name($highestsection)."</option>";
    display_children2($highestsection, $level +1, $filter, $nbmaxlevels);
    // afficher les autres sections autorisées
    if ( count($grantedsections) > 1  and ! check_rights($id, 24)) {
        $list = preg_split('/,/' , get_family("$highestsection")); 
        foreach ($grantedsections as $i => $_section) {
            if (! in_array($_section,$list) and $_section <> $highestsection) {
                $level=get_level($_section);
                if ( $level == 0 ) $mycolor=$myothercolor;
                elseif ( $level == 1 ) $mycolor=$my2darkcolor;
                elseif ( $level == 2 ) $mycolor=$my2lightcolor;
                elseif ( $level == 3 ) $mycolor=$mylightcolor;
                else $mycolor='white';
                if ( $filter == $_section ) $selected='selected';
                else $selected='';
                echo "<option value='$_section' $class $selected>".get_section_code($_section)." - ".get_section_name($_section)."</option>";
                display_children2($_section, $level +1, $filter, $nbmaxlevels);
            }
        }
    }
}
echo "</select></td> ";
echo "</tr>";
echo "<tr> ";
echo " <td  align=left> <a href='#'><i class='far fa-file-excel fa-2x' style='color:green;' title='Exporter la liste dans un fichier Excel' 
        onclick=\"window.open('cotisations_xls.php?filter=$filter&subsections=$subsections&position=$position&type_paiement=$type_paiement&periode=$periode&year=$year&paid=$paid&include_old=$include_old');\" /></i></a></td>";

if ($subsections == 1 ) $checked='checked';
else $checked='';
echo "<td><input type='checkbox' name='sub' id='sub' $checked
      onClick=\"orderfilter2('".$order."','".$filter."', document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."','".$paid."',document.getElementById('include_old'))\"/>
      <label for='sub' class=small2>inclure les $sous_sections </label>";



// inclure les anciens membres
if ($include_old == 1 ) $checked='checked';
else $checked='';
if ( $syndicate ==1 ) $anciens="radiés et suspendus ";
else $anciens="anciens membres";
echo " <input type='checkbox' id='include_old' name='include_old' $checked
    onClick=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."','".$paid."',document.getElementById('include_old'))\"/>
       <label for='include_old' class=small2>inclure les ".$anciens."</label></td>";
echo "</tr>";


// type de paiement
if ( $bank_accounts == 1 ) {
    echo "<tr><td>Mode de paiement</td>";
    $query2="select TP_ID, TP_DESCRIPTION from type_paiement";
    $result2=mysqli_query($dbc,$query2);
    echo "<td><select id='type_paiement' name='type_paiement' title='filtre par mode de paiement'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."',document.getElementById('type_paiement').value,'".$periode."','".$year."','".$paid."',    document.getElementById('include_old'))\">";
    if ( $type_paiement == 'ALL' ) $selected="selected";
    else $selected="";
    echo "<option value='ALL' $selected>Tous types</option>";
    while ($row2=@mysqli_fetch_array($result2)) {
        if ( $row2[0] == $type_paiement ) $selected="selected";
        else $selected="";
        echo "<option value=".$row2[0]." $selected>".$row2[1]."</option>";
    }
    $extract="";
    echo "</select></td><td align=left rowspan=3>".$extract."</td></tr>";
}

// période
echo "<tr><td>Période cotisation</td>";
$query2="select P_CODE, P_DESCRIPTION from periode order by P_ORDER";
$result2=mysqli_query($dbc,$query2);
echo "<td><select id='periode' name='periode' title='Choisir la période de cotisation'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."',document.getElementById('periode').value,'".$year."','".$paid."',document.getElementById('include_old'))\">";
while ($row2=@mysqli_fetch_array($result2)) {
    if ( $row2[0] == $periode ) $selected="selected";
    else $selected="";
    echo "<option value=".$row2[0]." $selected>".$row2[1]."</option>";
}
echo "</select>";
$curyear=date('Y');
$minyear=$curyear - 2;

echo " <select id='year' name='year' title='année de cotisation'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."',document.getElementById('year').value,'".$paid."',document.getElementById('include_old'))\">";

for ( $i=0; $i < 6; $i++) {
    $optionyear=$minyear + $i;
    if ( $optionyear == $year ) $selected='selected';
    else  $selected='';
    echo "<option value=".$optionyear." $selected>".$optionyear."</option>";
} 
echo "</select>";
echo "</td></tr>";

// payé?
echo "<tr><td>Etat paiement</td>";
echo "<td><select id='paid' name='paid' title='Filtrer lespersonnes selon que la cotisation a déjà été payée (ou prélevée) ou pas'
        onchange=\"orderfilter2('".$order."','".$filter."',document.getElementById('sub'),'".$position."','".$type_paiement."','".$periode."','".$year."',document.getElementById('paid').value,document.getElementById('include_old'))\">";
        if ( $paid == 0 ) $selected='selected';
        else  $selected='';
        echo "<option value=0 $selected>Pas encore payé</option>";
        if ( $paid == 1 ) $selected='selected';
        else  $selected='';
        echo "<option value=1 $selected>Paiement enregistré</option>";
        if ( $paid == 2 ) $selected='selected';
        else  $selected='';
        echo "<option value=2 $selected>Tout afficher</option>";
echo "</select>";
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

$nb=0;
if ( $paid == 0 and $check_all == 1 ) $nb=min($pages->items_per_page,$pages->items_total);

echo "<tr><td colspan=2 ><input type=submit  class='btn btn-default' value='sauver'> 
    <input type='text' size=8 id=numberPaid name=numberPaid value='".$nb."' onFocus=this.blur()> Nouveaux paiements enregistrés </td>";

// cocher toutes les cases
if ( $paid  == 0 ) {
    if ($check_all == 1) $checked='checked';
    else $checked='';
    echo "<td class=small align=left> 
    <input type='checkbox' id='check_all_box' name='check_all_box' $checked onClick=\"check_all();\" title=\"cliquer pour pré-cocher toutes les cases 'payé'\"> 
    Tout cocher";
    echo "</td>";
}
else echo "<td></td>";

echo "</tr></table>";
echo "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr height=10 class=TabHeader>";
echo "      <td width=180 align=center>    <a href=cotisations.php?order=P_NOM class=TabHeader>Nom Prénom</a></td>";
if ( $syndicate == 1 ) {            
    echo "
          <td width=40 align=center >  <a href=cotisations.php?order=P_PROFESSION class=TabHeader>Prof.</a></td>";
}
if ( $bank_accounts == 1 ) {
    echo "
      <td align=center width=100><a href=cotisations.php?order=TP_DESCRIPTION class=TabHeader title='Mode de paiement choisi pour la personne' class=TabHeader >Mode paiement</a></td>";
}

if ( $nbsections > 0 ) {           
    echo "          <td align=center width=60>    <a href=cotisations.php?order=P_STATUT class=TabHeader>Statut</a></td>";
}
    
echo "<td align=center width=140><a href=cotisations.php?order=P_SECTION class=TabHeader>Section</a></td>";

echo "
      <td align=center width=80><a href=cotisations.php?order=P_DATE_ENGAGEMENT class=TabHeader>Entrée</a></td>";
      
echo "
      <td align=center width=80><a href=cotisations.php?order=P_FIN class=TabHeader>Sortie</a></td>";

echo "
      <td align=center width=30><a href=cotisations.php?order=PC_ID class=TabHeader>Payé</a></td>";
echo "
      <td align=center width=100>Montant</td>";

echo "
      <td align=center width=100><a href=cotisations.php?order=PC_DATE class=TabHeader>Date payé</a></td>";
      
echo "
      <td align=center width=130><a href=cotisations.php?order=COMMENTAIRE class=TabHeader>Commentaire</a></td>";
      
echo " </tr>";
// ===============================================
// le corps du tableau
// ===============================================

$fraction=get_fraction($periode);

$i=0;
$people="";

while (custom_fetch_array($result1)) {
    $EXPECTED_MONTANT= get_montant($P_SECTION,$S_PARENT,$P_PROFESSION);
    
    if ( $periode == 'A' and  ($YEAR_ENGAGEMENT == $year or $YEAR_FIN == $year)) {
        // éventuellement demander cotisation pour année incomplète
        $number_months_to_pay = 12;
        if ( $MONTH_ENGAGEMENT <> "" and $YEAR_ENGAGEMENT == $year) $number_months_to_pay =  $number_months_to_pay - $MONTH_ENGAGEMENT + 1;
        else if ( $MONTH_FIN <> "" )  $number_months_to_pay = $number_months_to_pay - ( 12 - $MONTH_FIN );
        $coeff= $number_months_to_pay / 12 ;
        $EXPECTED_MONTANT= round($coeff * $EXPECTED_MONTANT , 2);
    }
    else {
        $EXPECTED_MONTANT = round($EXPECTED_MONTANT / $fraction , 2);
        $number_months_to_pay = 12 / $fraction;
    }
    if ( $EXPECTED_MONTANT < 0 ) $EXPECTED_MONTANT = 0;
    
    if ( $MONTANT == "" ) $MONTANT=$EXPECTED_MONTANT;
    $people .= $P_ID.",";
    $i=$i+1;
    if ( $i%2 == 0 ) $mycolor=$mylightcolor;
    else $mycolor="#FFFFFF";
    
    if ( $check_all == 1 ) {
         $PC_DATE=$curdate;
         $MONTANT=$EXPECTED_MONTANT;
    }
       
    echo "<tr bgcolor=$mycolor 
          onMouseover=\"this.bgColor='yellow'\" 
          onMouseout=\"this.bgColor='$mycolor'\"   
          onclick=\"this.bgColor='#33FF00'\">";

    echo "<td onclick='displaymanager($P_ID)'>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</td>";

    if ( $syndicate == 1 ) { 
        echo " <td align=center onclick='displaymanager($P_ID)'>$P_PROFESSION</td>";
    }
    if ( $bank_accounts == 1 ) {
        echo "<td align=center onclick='displaymanager($P_ID)'>$TP_DESCRIPTION</td>";     
    }
    if ( $nbsections > 0 ) {
        echo " <td align=center onclick='displaymanager($P_ID)'>$P_STATUT</td>";
    }

    echo "<td align=center onclick='displaymanager($P_ID)'><a href=upd_section.php?S_ID=$P_SECTION>$S_CODE</a></td>";
    
    echo "<td align=center onclick='displaymanager($P_ID)' class=small2>$P_DATE_ENGAGEMENT</td>";
        
    echo "<td align=center onclick='displaymanager($P_ID)' class=small2>$P_FIN</td>";

    if ( $PC_DATE <> '' ) $checked='checked';
    else $checked='';
    
    echo "<input type=hidden id='type_paiement' name='type_paiement' value='".$TP_ID."'>";
    echo "<input type=hidden id='year' name='year' value='".$year."'>";
    echo "<input type=hidden id='etablissement_".$P_ID."' name='etablissement_".$P_ID."' value=\"".$ETABLISSEMENT."\">";
    echo "<input type=hidden id='guichet_".$P_ID."' name='guichet_".$P_ID."' value=\"".$GUICHET."\">";
    echo "<input type=hidden id='compte_".$P_ID."' name='compte_".$P_ID."' value=\"".$COMPTE."\">";
    echo "<input type=hidden id='code_banque_".$P_ID."' name='code_banque_".$P_ID."' value=\"".$CODE_BANQUE."\">";
    echo "<input type=hidden id='iban_".$P_ID."' name='iban_".$P_ID."' value=\"".$IBAN."\">";
    echo "<input type=hidden id='bic_".$P_ID."' name='bic_".$P_ID."' value=\"".$BIC."\">";
    echo "
        <td align=center>
        <input type=checkbox name='paid_".$P_ID."' id='paid_".$P_ID."' value=\"1\" $checked
        onclick=\"updateCheckbox(frmPersonnel.paid_".$P_ID.",frmPersonnel.date_".$P_ID.",frmPersonnel.montant_".$P_ID.",'".$curdate."');\"/></td>";
    
    if ( $PC_DATE <> '' )  {
         if ( $check_all == 1 ) {
              if ( $COMMENTAIRE == "" and  $number_months_to_pay <> "" and $bank_accounts == 1 ) $COMMENTAIRE = $number_months_to_pay." mois";
            if ( $MONTANT >= $EXPECTED_MONTANT ) $montant_style="color: Green;";
            else $montant_style="color: Orange;";
        }
        else $montant_style="color: Black;";
    }
    else  {
        if ( $COMMENTAIRE == "" and  $number_months_to_pay <> "" and $bank_accounts == 1 ) $COMMENTAIRE = $number_months_to_pay." mois";
        $montant_style="color: Grey;";
    }
    
    // si on prèlève, ajouter la régul. Cas pas encore payé seulement.
    if ( $TP_ID == 1 and $MONTANT_REGUL <> 0 and $PC_DATE == '' ) {
        $COMMENTAIRE = $COMMENTAIRE." et régul de ".$MONTANT_REGUL." ".$default_money_symbol;
        $MONTANT = $MONTANT + $MONTANT_REGUL;
    }
    
    echo "
        <td align=center>
        <input type=text size=5 name='montant_".$P_ID."' id='montant_".$P_ID."' value='".$MONTANT."' style='".$montant_style."'
        onchange=\"checkFloat(frmPersonnel.montant_".$P_ID.",'".$MONTANT."');updateMontant(frmPersonnel.montant_".$P_ID.",'".$EXPECTED_MONTANT."')\"> ".$default_money_symbol."</td>";

    echo "
        <td align=center>
        <input type=text size=10 name='date_".$P_ID."' id='date_".$P_ID."' value=\"".$PC_DATE."\"
        placeholder='JJ-MM-AAAA' class='datepicker' data-provide='datepicker'
        onchange='checkDate2(frmPersonnel.date_".$P_ID.",\"$PC_DATE\");'></td>";
        
    echo "
        <td align=center>
        <input type=text size=20 name='commentaire_".$P_ID."' id='commentaire_".$P_ID."' value=\"".$COMMENTAIRE."\"
        title='commentaire lié au paiement'
        onchange='isvalid3(frmPersonnel.commentaire_".$P_ID.",\"$COMMENTAIRE\");'></td>";

    echo "</tr>";
}
echo "<input type=hidden id='people' name='people' value='".$people."'>";
echo "</table>";
echo "</form></div>";
writefoot();
?>
