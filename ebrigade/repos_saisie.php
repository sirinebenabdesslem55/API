<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE, Michel GAUTIER
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
check_all(10);
$id=$_SESSION['id'];
get_session_parameters();

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

if ( $month > 12 ) {
    $month=date('n');
    $_SESSION['month'] = $month;
}
if (isset($_GET["section"])) $section=intval($_GET["section"]);
else $section = $_SESSION['SES_SECTION'];

if (isset($_GET["person"])) $person=intval($_GET["person"]);
else $person = 0 ;

if (get_section($person)<> $section) $person =0;
if (! check_rights($id, 56) and ! check_rights($id, 10) and $_SESSION['SES_STATUT'] == 'SPP' ) $person=$id;
$moislettres=moislettres($month);

writehead();

echo "<script type='text/javascript' src='js/indispo.js'></script>";

//=====================================================================
// formulaire
//=====================================================================
$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;

echo "<body>";
echo "<form>";
if ( check_rights($id, 12) and check_rights($id,24)) {
//Si permission globale alors on affiche tout
    //filtre section
    echo "<tr><td width=50% align=right> Section </td>";
    echo "<td align=left><select id='filter' name='filter' 
        onchange=\"redirect('".$person."','".$month."','".$year."',document.getElementById('filter').value)\">";
    
  display_children2(-1, 0, $section,5, $sectionorder);
  
  echo "</select></td>";      
}
else {
// si pas de permission globale (perm 24) alors on affiche uniquement les descendants
    echo "<tr><td width=50% align=right> Section </td>";
    echo "<td align=left><select id='filter' name='filter' 
        onchange=\"redirect(document.getElementById('filtre').value,'".$month."','".$year."',document.getElementById('filter').value)\">";
    
  display_children2(get_section_parent($section), 3, $section,5, $sectionorder);
  
  echo "</select></td>";
}
echo "<div class='table-responsive' align=center>";

echo "<table  class='noBorder'><tr><td>";
echo "<font size=3><b>Repos de </b></font></td><td>";
$nb_users=count_entities('pompier', $where_clause="P_STATUT='SPP'");

//Affichage uniquement la liste des SPPs de la section choisie
if ( (check_rights($id, 56 ) or check_rights($id, 10 )))  {     
    echo "<select id='filtre' name='filtre' onchange=\"redirect(document.getElementById('filtre').value,'".$month."','".$year."','".$section."')\">";
    $query="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE 
        from pompier p, section s
        where p.P_SECTION = s.S_ID
        and P_STATUT = 'SPP'
        and p.P_OLD_MEMBER = 0 
        and p.P_STATUT <> 'EXT'";

    if ( $nbsections == 0  and ! check_rights($id, 24)) {
        $query .= " and p.P_SECTION =".$section;
    }
    else if (! check_rights($id, 40)) {
        $query .= " and p.P_SECTION=".$section;
    }
    else $query .= " and p.P_SECTION =".$section;

    $query .= " order by P_NOM";
    $result=mysqli_query($dbc,$query);

    while ($row=@mysqli_fetch_array($result)) {
        $P_NOM=$row["P_NOM"];
        $P_PRENOM=$row["P_PRENOM"];
        $P_ID=$row["P_ID"];
        if ( $person == 0 ) $person = $P_ID;
        $S_CODE=$row["S_CODE"];
        echo "<option value='".$P_ID."'";
        if ($P_ID == $person ) echo " selected ";
        $cmt=' ('.$S_CODE.')';
        echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$cmt."</option>\n";
    }
    echo "</select>";
}
else {
    echo "<font size=3><b>".my_ucfirst(get_prenom($person))." ".strtoupper(get_nom($person))."</b></font> <input type=hidden id='filtre' name='filtre' value='".$id."'>";
}
echo "</td></tr>";
echo "<tr><td><font size=3><b>Pour </font></td><td><b>Année</b></b> 
<select name='menu1' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,document.getElementById('filtre').value,'".$section."')\">";
if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
else echo "<option value='$yearnext' selected>".$yearnext."</option>";
echo  "</select>";

echo "<b> mois</b> <select name='menu2' onchange=\"fillmenu(this.form,this.form.menu1,this.form.menu2,document.getElementById('filtre').value,'".$section."')\">";
$m=1;
while ($m <=12) {
    $monmois = $mois[$m - 1 ];
    if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
    else echo  "<option value= $m >".$monmois."</option>\n";
    $m=$m+1;
}
echo  "</select>";
echo "</td></tr></table>";

echo "</select><input type=hidden id='type' name='type' value='RT'></td>";
echo "</tr>";
echo "</form>";


//=====================================================================
// calcul : quel est le mois prochain et combien de jours possède t'il
//=====================================================================
//nb de jours du mois
$d=nbjoursdumois($month, $year);

$query="select P_SECTION from pompier where P_ID=".$person;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$P_SECTION=$row["P_SECTION"];

$disabled='disabled';

if ( check_rights($id, 12, $P_SECTION )) { 
$disabled="";
}
elseif ( $person == $id ) {
    // dates futures, dispos ouvertes
    if ((date("n") <= $month  and date("Y") == $year) or date("Y") < $year) $disabled="";
    // mais si les dispos sont bloquées, alors on ne peut plus modifier les dispos
    if (( $NB2 > 0 ) and ( $gardes == 1 )) $disabled='disabled';  
}

//=====================================================================
// affiche le tableau
//=====================================================================
echo "<form name=dispo action='repos_save.php' method='POST'>";

$i=1;
echo "<input type='hidden' name='nbjours' value=$d size='20'>";
echo "<input type='hidden' name='person' value=$person size='20'>";
echo "<input type='hidden' name='month' value=$month size='20'>";
echo "<input type='hidden' name='year' value=$year size='20'>";
echo "<p>
<table >
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
$garde_id = get_garde_id(get_section($person));
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
                 <table class='noBorder'
                    <tr height=30 ></tr>
                </table>
               </td>\n";
        $k=$k+1;
    }
      
    // jours de 1 à $d variable $i
    while (( $jj <= 7 ) &&  ($i <= $d)) { // boucle des jours de la semaine
        $checked = array();
        $chekednuit = array();
        $DAYDATE=$year.str_pad($month, 2, '0', STR_PAD_LEFT).str_pad($i, 2, '0', STR_PAD_LEFT);
        
        for ( $z=1; $z <= 4; $z++ ) {
            $checked[$z]='';
            if ($z == 4) $Z = 3 ;
            else $Z =$z ;
                    $query="select I_TYPE_PERIODE from indisponibilite
                where P_ID=".$person."
                and I_DEBUT='".$year."-".$month."-".$i."'
                and I_TYPE_PERIODE = '".$Z."'
                and TI_CODE = 'RT'";
                
            $result=mysqli_query($dbc,$query);
            while ( $row=@mysqli_fetch_array($result)) {
                $checked[$row["I_TYPE_PERIODE"]]='checked';
            } 
        }
    
        $_dt= mktime(0,0,0,$month,$i,$year);
        if (dateCheckFree($_dt)) $mycolor=$yellow ; else  $mycolor=$white;
        
        $s_garde_jour=get_section_pro_jour($garde_id,$year, $month, $i);
        $s_garde_nuit=get_section_pro_jour($garde_id,$year, $month, $i, 'N');
        if ($s_garde_jour <> $s_garde_nuit ) {
            if ( $s_garde_jour == $P_SECTION ) $mycolor="#00CC00";
            if ( $s_garde_nuit == $P_SECTION ) $mycolor="#6666ff";
        }
        else if ( $s_garde_jour == $P_SECTION ) $mycolor="#00CC00";
        
        if ( is_out($person, $year, $month, $i) <> 0 ) $mycolor=$orange;
        if ( $DAYDATE < $CURDATE ) $disableddate='disabled';
        else $disableddate='';
        
        echo "<td bgcolor=$mycolor>
                 <table style='border: solid 1px;'>
                <tr height=10>
                    <td align=center colspan=4><b>".$i."</b></td>
                </tr>
                <tr height=20>";

        echo "     <td width='25' class=small2 colspan=2>Jour<br><input type='checkbox' name='2_".$i."' value='1'  $disableddate $disabled $checked[2] title='ABSENCE 12h Jour'></td>
                       <td width='25' class=small2 >Nuit<br><input type='checkbox' name='4_".$i."' value='1'  $disableddate $disabled $checked[3] title='ABSENCE 12H Nuit'></td>";

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

if ( $regime > 0 ){ 
    echo "<td bgcolor=#00CC00 width=14 style='border: 1px solid;'></td>
     <td class=small width = 90> Section jour</td>";
    echo "<td bgcolor=#6666ff width=14 style='border: 1px solid;'></td>   
     <td class=small width = 90> Section nuit </td>";
}
echo "<td bgcolor=$orange width=14 style='border: 1px solid;'></td>
     <td class=small width=80> Repos <a href='indispo_choice.php?page=1' title='Liste des repos'>Liste</a> </td>";

echo "<td bgcolor=#FFFF99 width=14 style='border: 1px solid;'></td>
    <td class=small width = 50> WE/Férié </td>";

echo "<td bgcolor=#FFFFFF width=14 style='border: 1px solid;'></td>
    <td class=small width = 50> Semaine </td>";
    
echo "</tr></table>";

echo "<p><table class='noBorder'><tr>";
            
    

// la personne habilitée peut valider les dispos
if ( $disabled == "") {
           echo "<td align=center> <input type='submit' class='btn btn-default' value='Valider'> </td>";
}
echo "<td> <input type='button' class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'></td></tr></table>";

echo "</form>";
echo "</div>";
writefoot();
?>
  
