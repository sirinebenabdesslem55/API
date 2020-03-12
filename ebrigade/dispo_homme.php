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
check_all(38);
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();
writehead();
test_permission_level(38);
?>

<script>
function redirect(year,month,section) {
url = "dispo_homme.php?month="+month+"&year="+year+"&filter="+section;
self.location.href = url;
}
</script>
<script type='text/javascript' src="js/Chart.bundle.min.js"></script>
<?php
if (isset ( $_GET["year"])) $year=intval($_GET["year"]);
else $year=date("Y");
if (isset ($_GET["month"])) $month=intval($_GET["month"]);
else {
    $month=date("n");
    if ( $gardes == 1 ) {
        // afficher le mois suivant
        if ( $month == 12 )  {
              $month = 1;
              $year= $year +1;
        }
        else  $month = $month +1 ;
    }
}
   
$mycolor=$textcolor;
//nb de jours du mois
$d=nbjoursdumois($month, $year);
$moislettres=moislettres($month);
$casej=0;$casen=0;

//=====================================================================
// title
//=====================================================================

echo "<body>";
echo "<div align=center><font size=5><b>Disponibilités par personne</b></font><p>";

//=====================================================================
// choix date
//=====================================================================
$year0=$year -1;
$year1=$year +1;
echo "<form>";
echo "<table class=noBorder><tr><td>Période ";
echo " <select id='year' name='year' onchange='redirect(document.getElementById(\"year\").value,".$month.",".$filter.")'>";
echo "<option value='$year0'>".$year0."</option>";
echo "<option value='$year' selected >".$year."</option>";
echo "<option value='$year1' >".$year1."</option>";
echo  "</select>";
echo " <select id='month' name='month' onchange='redirect(".$year.",document.getElementById(\"month\").value,".$filter.")'>";
$m=1;
while ($m <=12) {
      $monmois = $mois[$m - 1 ];
      if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
      else echo  "<option value= $m >".$monmois."</option>\n";
      $m=$m+1;
}
echo  "</select>";

//=====================================================================
// choix section
//=====================================================================
echo " <select id='section' name='section' 
      onchange='redirect(".$year.",".$month.",document.getElementById(\"section\").value)'>";
      display_children2(-1, 0, $filter, $nbmaxlevels , $sectionorder);
echo "</select></td>";

echo "</tr></table></form>";

// =====================================================================
// histogram
// =====================================================================   
//echo "<p><img src=dispo_homme_pic.php?year=$year&month=$month&section=$filter><p></div>" ;

repo_dispo_homme($filter,$year,$month);
writefoot();


?>
