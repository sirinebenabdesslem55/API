<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE
  # Copyright (C) 2016 Michel GAUTIER
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
check_all(56);
$id=$_SESSION['id'];
get_session_parameters();
writehead();
test_permission_level(56);
?>
<script>
function myalert(year,month,day,section,poste) {
    self.location.href = "alerte_create.php?section="+section+"&poste="+poste+"&dispo="+year+"-"+month+"-"+day;
}
function redirect(url) {
     self.location.href = url;
}
function fillmenu(frm, menu1,menu2,menu3,menu4,menu5) {
year=frm.menu1.options[frm.menu1.selectedIndex].value;
month=frm.menu2.options[frm.menu2.selectedIndex].value;
day=frm.menu3.options[frm.menu3.selectedIndex].value;
section=frm.menu4.options[frm.menu4.selectedIndex].value;
poste=frm.menu5.options[frm.menu5.selectedIndex].value;
url = "dispo_view.php?month="+month+"&year="+year+"&day="+day+"&poste="+poste+"&filter="+section+"&print=NO";
self.location.href = url;
}
</script>
<?php

if (isset ($_GET["month"])) $month=intval($_GET["month"]);
else $month=date("m");
if (isset ( $_GET["year"])) $year=intval($_GET["year"]);
else $year=date("Y");
if (isset ($_GET["day"])) $day=intval($_GET["day"]);
else $day=date("d");
if (isset ($_GET["poste"])) $poste=intval($_GET["poste"]);
else $poste=0;
   
$mycolor=$textcolor;
//nb de jours du mois
$d=nbjoursdumois($month, $year);
$moislettres=moislettres($month);
$casej=0;$casen=0;

//=====================================================================
// title
//=====================================================================


echo "<body>";
echo "<div align=center><font size=5><b>Personnel disponible le <br>".date_fran($month, $day, $year)." $moislettres $year </b></font><p>";



//=====================================================================
// choix date
//=====================================================================

$yearnext=date("Y") +1;
$yearcurrent=date("Y");
$yearprevious = date("Y") - 1;

echo "<form>";

$number4='this.form.menu4';

echo "<table class=noBorder>
    <tr><td>année </td><td>
        <select id='menu1' name='menu1' 
        onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    if ($year > $yearprevious) echo "<option value='$yearprevious'>".$yearprevious."</option>";
    else echo "<option value='$yearprevious' selected>".$yearprevious."</option>";
    if ($year <> $yearcurrent) echo "<option value='$yearcurrent' >".$yearcurrent."</option>";
    else echo "<option value='$yearcurrent' selected>".$yearcurrent."</option>";
    if ($year < $yearnext)  echo "<option value='$yearnext' >".$yearnext."</option>";
    else echo "<option value='$yearnext' selected>".$yearnext."</option>";
    echo  "</select>";

    echo " mois <select id='menu2' name='menu2' 
    onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    $m=1;
    while ($m <=12) {
      $monmois = $mois[$m - 1 ];
      if ( $m < 10 ) $M = "0".$m ; 
      else $M=$m;
      if ( $M == $month ) echo  "<option value='$M' selected >".$monmois."</option>\n";
      else echo  "<option value= '$M' >".$monmois."</option>\n";
      $m=$m+1;
    }
    echo  "</select>";

    echo " jour <select id='menu3' name='menu3' 
    onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    $d=1;
    while ($d <= 31) {
      if ( $d < 10 ) $D = "0".$d ; 
      else $D=$d;
      if ( $D == $day ) echo  "<option value='$D' selected >".$d."</option>\n";
      else echo  "<option value= '$D' >".$d."</option>\n";
      $d=$d+1;
    }
    echo  "</select></td></tr>";
    echo "<tr><td>";
    
    echo "Filtre </td><td><select id='menu5' name='menu5' 
    onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>
      <option value='0'>toutes qualifications</option>";
        $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ORDER";
        $result2=mysqli_query($dbc,$query2);
        $prevEQ_ID=0;
        while ($row=@mysqli_fetch_array($result2)) {
              $PS_ID=$row["PS_ID"];
              $EQ_ID=$row["EQ_ID"];
              $EQ_NOM=$row["EQ_NOM"];
              if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
              $prevEQ_ID=$EQ_ID;
              $DESCRIPTION=$row["DESCRIPTION"];
              echo "<option value='".$PS_ID."'";
              if ($PS_ID == $poste ) echo " selected ";
              echo ">".$DESCRIPTION."</option>\n";
        }
    echo "</select></td></tr><tr><td>";

     echo "Choix section</td><td><select id='menu4' name='menu4' 
       onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,$number4,this.form.menu5)'>";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
    
echo "</td></tr></table></form><p>";


// ===============================================
// personnel disponible
// ===============================================

$query2="select DP_ID, DP_NAME from disponibilite_periode order by DP_ID";
$result2=mysqli_query($dbc,$query2);
$periodes=array();
$names=array();
$sections=array();

while (custom_fetch_array($result2)) {
    $periodes[$DP_ID]= $DP_NAME;
}

$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, s.S_CODE
        from pompier p, disponibilite d, section s";
        if ( $poste <> 0) $query .=" , qualification q";
        $query .=" where p.P_ID=d.P_ID
        and p.P_SECTION=s.S_ID
        and p.P_OLD_MEMBER=0
        and p.P_STATUT <> 'EXT'
        and d.D_DATE='".$year."-".$month."-".$day."'";
if ( $filter <> 0) $query .=" and p.P_SECTION in (".get_family("$filter").")";
if ( $poste <> 0) $query .=" and q.P_ID=p.P_ID and q.PS_ID=$poste";
$query .=" order by p.P_NOM, p.P_PRENOM, p.P_ID";
$result=mysqli_query($dbc,$query);

$dispospers=array();
$query1="select p.P_ID, d.PERIOD_ID
        from pompier p, disponibilite d";
        if ( $poste <> 0) $query1 .=" , qualification q";
        $query1 .=" where p.P_ID=d.P_ID 
        and d.D_DATE='".$year."-".$month."-".$day."'
        and p.P_OLD_MEMBER=0
        and p.P_STATUT <> 'EXT'";
if ( $filter <> 0) $query1 .=" and p.P_SECTION in (".get_family("$filter").")";
if ( $poste <> 0) $query1 .=" and q.P_ID=p.P_ID and q.PS_ID=$poste";
$query1 .=" order by d.PERIOD_ID, p.P_ID";
$result1=mysqli_query($dbc,$query1);
while (custom_fetch_array($result1)) {
    $dispos[$PERIOD_ID][$P_ID]=1;
}

if ( mysqli_num_rows($result1) == 0 ) {
    echo "Aucune personne disponible";
}
else {
    echo "<table cellspacing=0 cellpading=0 class=noBorder>
      <tr class=TabHeader>";
    echo "<td width=180>Nom prénom</td>"; 
    echo "<td width=180 align=center>Section</td>";
    foreach ($periodes as $period => $DP_NAME){
        echo "<td width=80 align=center style='border:solid #191970 1px;border-collapse: separate;'>".$DP_NAME."</td>";
    }
    echo "</tr>";

    while (custom_fetch_array($result)) {
        $sections[$P_ID]=$S_CODE;
        $names[$P_ID] = strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        // initialise dispos pour chaquer periode de la personne
        foreach ($periodes as $period => $DP_NAME){
            if (! isset ($dispos[$period][$P_ID])) $dispos[$period][$P_ID]=0;
        }
        if ( $gardes ) {
            $nb1 = get_nb_inscriptions($P_ID, $year, $month, $day,$year, $month, $day, 1, 0);
            $nb2 = get_nb_inscriptions($P_ID, $year, $month, $day,$year, $month, $day, 2, 0);
        }
        else {
            $nb1 = get_nb_inscriptions($P_ID, $year, $month, $day,$year, $month, $day, 0, 0);
            $nb2 = $nb1;
        }
        if ( $nb1 > 0 ) {
            $dispos[1][$P_ID]=2; $dispos[2][$P_ID]=2;
        }
        if ( $nb2 > 0 ) {
            $dispos[3][$P_ID]=2;
            $dispos[4][$P_ID]=2;
        }
    }
    
    foreach ($names as $pid => $value) {
        echo "<tr bgcolor=$mylightcolor >
            <td style='border:solid #191970 1px;border-collapse: separate;'><a href='upd_personnel.php?pompier=".$pid."' title='Voir fiche personnel'>".$value."</a></td>";
        echo "<td align=center   style='border:solid #191970 1px;border-collapse: separate;'>".$sections[$pid]."</td>";
        $result2=mysqli_query($dbc,$query2);
        foreach ($periodes as $period => $DP_NAME){
            if ( $dispos[$period][$pid] == 1 ) {
                $c='lightgreen';
                $t='personne disponible sur cette période';
            }
            else if ( $dispos[$period][$pid] == 2 ) {
                $c='orange';
                $t='personne disponible sur cette période mais déjà engagée';
                if (! $gardes ) $t .= ' ce jour';
            }
            else {
                $c='grey';
                $t='personne non disponible sur cette période';
            }
            echo "<td bgcolor='$c' style='border:solid #191970 1px;border-collapse: separate;' title='".$t."'></td>";
        }
    }

    echo "</table>";

    if ( check_rights ($id,43,"$filter"))
        echo "<p><input  class='btn btn-default' type=button value=\"alerter\" 
        onclick=\"myalert('".$year."','".$month."','".$day."','".$filter."','".$poste."');\">";
}

writefoot();
?>
