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
check_all(38);
$id=$_SESSION['id'];
$section=$_SESSION['SES_SECTION'];

if ( $gardes == 1 ) {
    if (! isset($_SESSION["month"])) {
        $m1=date("n");
        $y1=date("Y");
        // afficher le mois suivant
        if ( $m1 == 12 )  {
            $m1 = 1;
            $y1= $y1 +1;
        }
        else $m1 = $m1 +1;
        $_SESSION["month"]=$m1;
        $_SESSION["year"]=$y1;
    }
}
get_session_parameters();

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}

if (isset($_GET["person"])) $person=intval($_GET["person"]);
else $person=$id;

$lasection = get_section($person);
if (! check_rights($id, 56, "$lasection") and ! check_rights($id, 10, "$lasection") and $nbsections == 0 ) {
    $person=$id;
    $lasection=$section;
}

$moislettres=moislettres($month);
writehead();

?>
<script type="text/javascript" src="js/dispo.js"></script>
<STYLE type="text/css">
.counter{FONT-SIZE: 12pt; border:0px; background-color:<?php echo $mydarkcolor; ?>; color:white !important; font-weight:bold; width:27px; padding:4px; margin:8px; text-align:center;'}
</STYLE>

<?php

//=====================================================================
// formulaire
//=====================================================================
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;
$garde_id = get_garde_id($lasection);
echo "<body>";
echo "<form>";

// recherche dans tableau garde status si les dispos sont verrouillées
if ($sdis) {
    $show_his_section=" (".get_section_code("$lasection").")";
    $level_section = get_level($lasection);
    if ( $level_section == $nbmaxlevels - 1 ) $mysection = get_section_parent($lasection);
    else $mysection = $lasection;
    $cmt="pour le personnel de ".get_section_code("$mysection")." - ".get_section_name("$mysection");
}
else {
    $mysection = 0;
    $cmt="pour tout le personnel";
    $show_his_section="";
}

echo "<div class='table-responsive' align=center>";
echo "<font size=3><b>Disponibilités de ".my_ucfirst(get_prenom($person))." ".strtoupper(get_nom($person)).$show_his_section."</font></b><br>";

if ( check_rights($id, 10 ) ) {
    $nb_users=count_entities('pompier');
    if (  $nb_users < 500 ) {
        echo "choix personne <select id='filtre' name='filtre' onchange=\"redirect(document.getElementById('filtre').value,'".$month."','".$year."', 'saisie','0')\">";
        $query="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE 
            from pompier p, section s
            where p.P_SECTION = s.S_ID
            and p.P_OLD_MEMBER = 0 
            and p.P_STATUT <> 'EXT'
            and P_SECTION in (".get_family($section).")";
        $query .= " order by P_NOM";
        $result=mysqli_query($dbc,$query);

        while (custom_fetch_array($result)) {
            echo "<option value='".$P_ID."'";
            if ($P_ID == $person ) echo " selected ";
            $s=' ('.$S_CODE.')';
            echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$s."</option>\n";
        }
        echo "</select>";
    }
}

echo "<table class='noBorder'><tr><td>";
echo "année 
<select name='menu1' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,'".$person."')\">";
if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
else echo "<option value='$yearnext' selected>".$yearnext."</option>";
echo  "</select></td>";

echo "<td>mois <select name='menu2' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,'".$person."')\">";
$m=1;
while ($m <=12) {
    $monmois = $mois[$m - 1 ];
    if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
    else echo  "<option value= $m >".$monmois."</option>\n";
    $m=$m+1;
}
echo  "</select>";
echo "</td></tr></table>";

echo "</form>";

$query2="select count(1) as NB from planning_garde_status where S_ID=".$mysection." and EQ_ID = 0 and
       PGS_STATUS='READY' and PGS_MONTH  =".$month."  and PGS_YEAR=".$year;
$result2=mysqli_query($dbc,$query2);
$row2=@mysqli_fetch_array($result2);
$NB2=$row2["NB"];
If ($NB2 > 2) $blockdispo = true ;
else $blockdispo = false ;
// permettre de fermer les dispos pour le mois suivant
if ( $gardes == 1 and check_rights($id,5,"$mysection")) {
    if ( $sdis == 0 or get_level("$mysection") > 2 ) {
        if ( $NB2 > 0 ) {
            echo "<p><table  class='noBorder'><TR>
                <td><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i><td>
                <td>La saisie des disponibilités pour ce mois est bloquée.";
                $blockdispo = true ;      
            echo " <input type='button'  class='btn btn-default' value='Ouvrir' name='ouvrir' 
                    onclick=\"redirect('".$person."','".$month."','".$year."','ouvrir','".$mysection."', '".str_replace("'","",$cmt)."')\"
                    title=\"Ouvrir la saisie des disponibilités $cmt pour ".moislettres($month)." ".$year."\">";
            echo "</td></tr></table>";
        }
        else if ( $NB2 == 0 ) {
          echo " <p><input type='button'  class='btn btn-default' value='Bloquer' name='fermer' 
                  onclick=\"redirect('".$person."','".$month."','".$year."','fermer','".$mysection."', '".str_replace("'","",$cmt)."')\"
                  title=\"Bloquer la saisie des disponibilités $cmt pour ".moislettres($month)." ".$year."\">";
        }
    }
}
else if ( $NB2 > 0 ) {
     echo "<table  class='noBorder'><TR>
            <td ><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i><td>
            <td >La saisie des disponibilités pour ce mois est bloquée.
            </td></tr></table>";
}

//=====================================================================
// calcul : quel est le mois prochain et combien de jours possède t'il
//=====================================================================
//nb de jours du mois
$d=nbjoursdumois($month, $year);

if ( check_rights($id, 5, $lasection )) $admin=true;
else $admin=false;

$disabled='disabled';
if ( $person == $id or check_rights($id, 10, $lasection )) {
    // dates futures, dispos ouvertes
    if ((date("n") <= $month  and date("Y") == $year) or date("Y") < $year) $disabled="";
    // mais si les dispos sont bloquées, alors on ne peut plus modifier les dispos
    if ( $NB2 > 0  and  $gardes == 1 ) $disabled='disabled';
    // le responsable du tableau de garde peut toujours changer les dispos
    if ( $admin ) $disabled='';
}

//=====================================================================
// affiche le tableau
//=====================================================================

echo "<form name=dispo action='save_dispo.php' method='POST'>";

$queryA="select DP_NAME, DP_CODE, DP_NAME, DP_ID
        from disponibilite_periode ";
if ( $dispo_periodes == 1 ) $queryA .=" where DP_ID= 1";
if ( $dispo_periodes == 2 ) $queryA .=" where DP_ID in (1,4)";
if ( $dispo_periodes == 3 ) $queryA .=" where DP_ID in (1,2,4)";
$queryA .=" group by DP_ID order by DP_ID";
$resultA=mysqli_query($dbc,$queryA);

echo "<small>Tout cocher:</small> ";
while ($rowA=@mysqli_fetch_array($resultA)) {
    $DP_ID=$rowA['DP_ID'];
    $DP_NAME=convert_period_name($DP_ID,$rowA['DP_NAME'],$dispo_periodes);
    echo "<label for='CheckAll".$DP_ID."'>".$DP_NAME."</label>
        <input type='checkbox' name='CheckAll".$DP_ID."' id='CheckAll".$DP_ID."' onclick=\"CheckAll('".$DP_ID."',this.checked);\" $disabled title=\"".$DP_NAME.": tout cocher\" /> ";
}

$i=1;
echo "<input type='hidden' name='nbjours' value=$d size='20'>";
echo "<input type='hidden' name='person' value=$person size='20'>";
echo "<input type='hidden' name='month' value=$month size='20'>";
echo "<input type='hidden' name='year' value=$year size='20'>";

echo "<p>
<table cellspacing=0>
    <tr height=10 >
      <td width='50' class=TabHeader align=center>Lu</font></td>
      <td width='50' class=TabHeader align=center>Ma</td>
      <td width='50' class=TabHeader align=center>Me</td>
      <td width='50' class=TabHeader align=center>Je</td>
      <td width='50' class=TabHeader align=center>Ve</td>
      <td width='50' class=TabHeader align=center>Sa</td>
      <td width='50' class=TabHeader align=center>Di</td>
    </tr>
";

$CURDATE=date('Y').date('m').date('d');

$l=1;
$i=1;
// le mois commence par un $jj
$jj=date("w", mktime(0, 0, 0, $month,$i,$year));
$i=1;$k=$i;
if ( $jj == 0 ) $jj=7; // on affecte 7 au dimanche, (lundi=1)

while ( $l <= 6 ) { // boucle des semaines
    echo "\n    <tr height=20 >\n";
    // cases vides en début de mois
    while ( $k < $jj ) {
          echo "<td width='50' bgcolor=$mylightcolor >
                 <table  class='noBorder' 
                    <tr height=30 ></tr>
                </table>
               </td>\n";
        $k=$k+1;
    }
      
    // jours de 1 à $d variable $i
    while (( $jj <= 7 ) &&  ($i <= $d)) { // boucle des jours de la semaine
        $checked = array();
        
        $DAYDATE=$year.str_pad($month, 2, '0', STR_PAD_LEFT).str_pad($i, 2, '0', STR_PAD_LEFT);
        
        for ( $z=1; $z <= 4; $z++ ) {
            $checked[$z]='';
        }
    
        $query="select PERIOD_ID from disponibilite
              where P_ID=".$person."
              and D_DATE='".$year."-".$month."-".$i."'";
        $result=mysqli_query($dbc,$query);
         while ( $row=@mysqli_fetch_array($result)) {
            $checked[$row[0]]='checked';
        }
        
        $_dt= mktime(0,0,0,$month,$i,$year);
        if (dateCheckFree($_dt)) $mycolor=$yellow ; else  $mycolor=$white;
        
        $s_garde_jour=get_section_pro_jour($garde_id,$year, $month, $i);
        $s_garde_nuit=get_section_pro_jour($garde_id,$year, $month, $i, 'N');
        if ($s_garde_jour <> $s_garde_nuit ) {
            if ( $s_garde_jour == $lasection ) $mycolor="#00CC00";
            if ( $s_garde_nuit == $lasection ) $mycolor="#6666ff";
        }
        else if ( $s_garde_jour == $lasection ) $mycolor="#00CC00";
        if ( is_out($person, $year, $month, $i) <> 0 ) $mycolor=$orange;
        if ( $DAYDATE < $CURDATE ) $disableddate='disabled';
        else $disableddate='';
        // teste l'inscription à un événement ce jour là si garde est active
        // si inscrit alors on verouille la dispo
        if ( $blockdispo and ! $admin ) $disabled_cell ='disabled';
        else $disabled_cell='';
        if ( $pompiers == 1 and ! check_rights($id, 10, $lasection )) {
            $isinscritJ = get_nb_inscriptions($person,$year,$month,$i,$year,$month,$i,1);
            $isinscritN = get_nb_inscriptions($person,$year,$month,$i,$year,$month,$i,2);
            $isinscrit = $isinscritJ +  $isinscritN;
            if ($isinscrit > 0 ) $disabled_cell = 'disabled'; 
        }
        echo "<td bgcolor=$mycolor>
                 <table style='border: solid 1px;'>
                <tr height=10>
                    <td align=center colspan=4><b>".$i."</b></td>
                </tr>
                <tr height=20>";
        if ( $dispo_periodes == 1 ){
            echo "     <td width='50' align=center colspan=4><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo 24h '>";
            if ($disabled_cell == 'disabled' and $checked[1])  echo "<input type=hidden name='save_1".$i."' id='save_1' value='$checked[1]'></td>";       
        }
        if ( $dispo_periodes == 2 ){
            echo "     <td width='25' class=small2 colspan=2>J<br><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo jour'></td>
                      <td width='25' class=small2 colspan=2>N<br><input type='checkbox' name='4_".$i."' value='1' onClick=\"updateTotal(this,total4)\" $disableddate  $disabled $disabled_cell $checked[4] title='dispo nuit'></td>";
                      if ($disabled_cell == 'disabled' and $checked[1] )  echo "<input type=hidden name='save1_".$i."' id='save_1' value='$checked[1]'>";
                      if ($disabled_cell == 'disabled' and $checked[4] )  echo "<input type=hidden name='save4_".$i."' id='save_4' value='$checked[4]'>";
                      echo "</td>";
        }
        if ( $dispo_periodes == 3 ){
            echo "     <td width='15' class=small2 colspan=2>M<br><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo matin'></td>
                    <td width='15' class=small2 >AM<br><input type='checkbox' name='2_".$i."' value='1' onClick=\"updateTotal(this,total2)\" $disableddate $disabled $disabled_cell $checked[2] title='dispo après-midi'></td>
                      <td width='15' class=small2 >N<br><input type='checkbox' name='4_".$i."' value='1' onClick=\"updateTotal(this,total4)\" $disableddate $disabled $disabled_cell $checked[4] title='dispo nuit'>";
                      if ($disabled_cell == 'disabled' and $checked[1] )  echo "<input type=hidden name='save1_".$i."' id='save_1' value='$checked[1]'>";
                      if ($disabled_cell == 'disabled' and $checked[2] )  echo "<input type=hidden name='save2_".$i."' id='save_2' value='$checked[2]'>";
                      if ($disabled_cell == 'disabled' and $checked[4] )  echo "<input type=hidden name='save4_".$i."' id='save_4' value='$checked[4]'>";
                      echo "</td>" ;
        }
        if ( $dispo_periodes == 4 ) {
            echo "     <td width='12' class=small2 colspan=2>M<br><input type='checkbox' name='1_".$i."' value='1' onClick=\"updateTotal(this,total1)\" $disableddate $disabled $disabled_cell $checked[1] title='dispo matin'></td>
                    <td width='12' class=small2 >AM<br><input type='checkbox' name='2_".$i."' value='1' onClick=\"updateTotal(this,total2)\" $disableddate $disabled $disabled_cell $checked[2] title='dispo après-midi'></td>
                    <td width='12' class=small2 >S<br><input type='checkbox' name='3_".$i."' value='1' onClick=\"updateTotal(this,total3)\" $disableddate $disabled $disabled_cell $checked[3] title='dispo soir'></td>
                      <td width='12' class=small2 colspan=2>N<br><input type='checkbox' name='4_".$i."' value='1' onClick=\"updateTotal(this,total4)\" $disableddate $disabled_cell $disabled $checked[4] title='dispo nuit'>";
                      if ($disabled_cell == 'disabled' and $checked[1] )  echo "<input type=hidden name='save1_".$i."' id='save_1' value='$checked[1]'>";
                      if ($disabled_cell == 'disabled' and $checked[2] )  echo "<input type=hidden name='save2_".$i."' id='save_2' value='$checked[2]'>";
                      if ($disabled_cell == 'disabled' and $checked[3] )  echo "<input type=hidden name='save3_".$i."' id='save_3' value='$checked[3]'>";
                      if ($disabled_cell == 'disabled' and $checked[4] )  echo "<input type=hidden name='save4_".$i."' id='save_4' value='$checked[4]'>";
                      echo "</td>" ;
        }
        echo "</tr>
            </table>
             </td>";
        $jj=$jj+1;
        $i=$i+1;
    }
    // cases vides en fin de tableau
    while (( $i <= ( 7 * $l +1 ) - $k ) && ( $i > $d )) {
          echo "<td width='50' bgcolor=$mylightcolor >
                  <table  class='noBorder'>
                    <tr height=30></tr>
                 </table>
               </td>\n";
        $i=$i+1;
    }

    echo "    </tr>\n";
    if ( $i > $d ) $l=7;
    else $l=$l+1;
    $jj=1;
}

echo "</table>";

// légende
echo "<p><table class='noBorder'><tr height=12>";

$regime=get_regime($section);

if ( $regime > 0 ) {
    echo "<td bgcolor=#00CC00 width=14 style='border: 1px solid;'></td>
     <td class=small width = 90> Section jour</td>";
    echo "<td bgcolor=#6666ff width=14 style='border: 1px solid;'></td>
     <td class=small width = 90> Section nuit </td>";
}     
echo "<td bgcolor=orange width=14 style='border: 1px solid;'></td>
     <td class=small width = 50> Absent </td>";

echo "<td bgcolor=#FFFF99 width=14 style='border: 1px solid;'></td>
    <td class=small width = 50> WE/Férié </td>";

echo "<td bgcolor=#FFFFFF width=14 style='border: 1px solid;'></td>
    <td class=small width = 50> Semaine </td>";
    
echo "</tr></table>";

echo "<p><table class='noBorder'><tr>";
    
$resultA=mysqli_query($dbc,$queryA);
while ($rowA=@mysqli_fetch_array($resultA)) {
    $DP_ID=$rowA['DP_ID'];
    $DP_NAME=convert_period_name($DP_ID,$rowA['DP_NAME'],$dispo_periodes);

    $query2="select count(1) as NB from disponibilite
        where P_ID=".$person."
        and D_DATE >='".$year."-".$month."-01'
        and D_DATE <='".$year."-".$month."-".$d."'
        and PERIOD_ID =".$DP_ID;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    echo "<td><b>".$DP_NAME."</b> 
        <input id='total".$DP_ID."' name='total".$DP_ID."' 
            value='".$row2['NB']."' readonly class='counter'>
        </td>";
}

// la personne habilitée peut valider les dispos
if ( (! $blockdispo or $admin ) and $disabled == '') {
    echo "<td align=center> <input type='submit'  class='btn btn-default' value='Valider'> </td>";
}
echo "<td> <input type='button'  class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'></td></tr></table>";

$query="select DC_COMMENT from disponibilite_comment 
            where P_ID=".$person." and DC_MONTH=".intval($month)." and DC_YEAR=".intval($year);
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
    
echo"<br><span style='font-size:10px;font-style:italic;text-align:left;'>Votre commentaire concernant vos disponibilités du mois:</span><br>
    <textarea name='msg' style='width:400px;height:70px' title=\"Ce texte sera ajouté au mail de notification envoyé quand vous sauvez les disponibilités\">".$row['DC_COMMENT']."</textarea>";

echo "</form>";
echo "</div>";

writefoot();
?>

