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
include_once ("./fonctions_chart.php");
check_all(27);
writehead();
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();
test_permission_level(27);

?>
<script>
function redirect(year,month,section,mode_garde,groupJN,c1,c2,c3,c4, gardes) {
    if (groupJN.checked) g = 1;
    else g = 0;
    if ( mode_garde == 1 || gardes == 1) {
        p1 = 1;
        p2 = 1;
        p3 = 1;
        p4 = 1;
    }
    else {
        if (c1.checked) p1 = 1;
        else p1 = 0;
        if (c2.checked) p2 = 1;
        else p2 = 0;
        if (c3.checked) p3 = 1;
        else p3 = 0;
        if (c4.checked) p4 = 1;
        else p4 = 0;
    }
    url = "bilan_participation.php?month="+month+"&year="+year+"&filter="+section+"&mode_garde="+mode_garde+"&groupJN="+g+"&c1="+p1+"&c2="+p2+"&c3="+p3+"&c4="+p4;
    self.location.href = url;
}
</script>
<script type='text/javascript' src="js/Chart.bundle.min.js"></script>
<?php

if (isset ($_GET["mode_garde"])) $mode_garde=intval($_GET["mode_garde"]);
else $mode_garde=0;

if ( $mode_garde == 0 ) $groupJN=0;
else {
    $query="select count(1) as NB from evenement_participation where EP_ASTREINTE = 1
        and E_CODE in (select E_CODE from evenement_horaire where EH_DATE_DEBUT >= '".$year."-".$month."-01' and EH_DATE_DEBUT <= '".$year."-".$month."-31')";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    if ( $row["NB"] > 0 ) $groupJN=1;
    else $groupJN=0;
}
if (isset ($_GET["groupJN"])) $groupJN=intval($_GET["groupJN"]);
if (isset ($_GET["c1"])) $c1=intval($_GET["c1"]); else $c1=1;
if (isset ($_GET["c2"])) $c2=intval($_GET["c2"]); else $c2=1;
if (isset ($_GET["c3"])) $c3=intval($_GET["c3"]); else $c3=1;
if (isset ($_GET["c4"])) $c4=intval($_GET["c4"]); else $c4=1;

//=====================================================================
// title
//=====================================================================
if ( $mode_garde == 1 ) $t="gardes";
else $t="événements";
echo "<body>";
echo "<div align=center class='table-responsive'><font size=5><b>Participations aux ".$t."</b></font><p>";

//=====================================================================
// choix date
//=====================================================================
$year0=$year -1;
$year1=$year +1;
echo "<form>";
echo "<table class='noBorder'><tr><td>Période ";
echo " <select id='year' name='year' onchange='redirect(document.getElementById(\"year\").value,".$month.",".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,c4,".$gardes.")'>";
echo "<option value='$year0'>".$year0."</option>";
echo "<option value='$year' selected >".$year."</option>";
echo "<option value='$year1' >".$year1."</option>";
echo  "</select>";
echo " <select id='month' name='month' onchange='redirect(".$year.",document.getElementById(\"month\").value,".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,c4,".$gardes.")'>";
$m=1;
while ($m <=12) {
      $monmois = $mois[$m - 1 ];
      if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
      else echo  "<option value= $m >".$monmois."</option>\n";
      $m=$m+1;
}
if ( $month == 100 ) echo  "<option value='100' selected >bilan annuel</option>\n";
      else echo  "<option value='100'>bilan annuel</option>\n";
echo  "</select>";

//=====================================================================
// choix section
//=====================================================================
echo " <select id='section' name='section' 
      onchange='redirect(".$year.",".$month.",document.getElementById(\"section\").value,".$mode_garde.",".$groupJN.",c1,c2,c3,c4,".$gardes.")'>";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";

// grouper jours et nuits
if ( $mode_garde == 1 ) {
    if ( $groupJN == 1 ) $checked ='checked';
    else $checked="";
    echo " <input type='checkbox' name='groupJN' id='groupJN' value='1' $checked title='cocher pour regrouper jour et nuit sur le même segment'
           onClick=\"redirect(".$year.",".$month.",".$filter.",".$mode_garde.",this,c1,c2,c3,c4,".$gardes.")\"/>
           <label for='groupJN'>grouper Gardes J/N</label>";
}

echo "</td></tr>";

// choix catégories événements
if ( $mode_garde == 0 and $gardes == 0 ) {
    echo "<tr><td>Catégorie événements";
    $query="select CEV_CODE, CEV_DESCRIPTION from categorie_evenement order by CEV_CODE desc";
    $result=mysqli_query($dbc,$query);
    $i=1;
    while ( custom_fetch_array($result)) {
        $k="c".$i;
        if ( $$k == 1 ) $checked='checked';
        else $checked='';
        echo " <input type='checkbox' name='c".$i."' id='c".$i."' value='1' $checked title=\"".$CEV_DESCRIPTION."\"
           onClick=\"redirect(".$year.",".$month.",".$filter.",".$mode_garde.",".$groupJN.",c1,c2,c3,c4,".$gardes.")\"/>
           <label for='c".$i."'>".$CEV_DESCRIPTION."</label>";
        $i++;
    }
    echo "</td></tr>";
}
else {
    echo "<input type='hidden' name='c1' id='c1' value=1>
          <input type='hidden' name='c2' id='c2' value=1>
          <input type='hidden' name='c3' id='c3' value=1>
          <input type='hidden' name='c4' id='c4' value=1>";
}

echo "</table></form>";


// =====================================================================
// histogram
// =====================================================================
if ( $gardes == 0 and $mode_garde == 0 ) $legend_not_clickable=1;
print repo_bilan_participations($filter,$year,$month,$mode_garde,$groupJN,$c1,$c2,$c3,$c4);

writefoot();

?>
