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

include_once ("./config.php");
include_once ("./fonctions_chart.php");

check_all(38);
get_session_parameters();
writehead();
test_permission_level(38);
?>
<script>
function redirect(url) {
    self.location.href = url;
}
function fillmenu(frm, menu1,menu2,menu3,menu4) { 
    year=frm.menu1.options[frm.menu1.selectedIndex].value;
    month=frm.menu2.options[frm.menu2.selectedIndex].value;
    section=frm.menu4.options[frm.menu4.selectedIndex].value;
    url = 'dispo_month.php?month='+month+'&year='+year+'&filter='+section;
    self.location.href = url;
}
</script>
<script type='text/javascript' src="js/Chart.bundle.min.js"></script>
<?php

if (isset ( $_GET["year"])) $year=secure_input($dbc,$_GET["year"]);
else $year=date("Y");
if (isset ($_GET["month"])) $month=intval($_GET["month"]);
else {
    $month=date("n");
    if ( $gardes == 1 ) {
        // afficher le mois suivant
        if ( $month == 12 )  {
              $month = 1;
              $year = $year +1;
        }
        else  $month = $month +1 ;
    }
}

//=====================================================================
// title
//=====================================================================

echo "<body>";
echo "<div align=center ><font size=5><b>Personnel disponible </b></font><p>";

//=====================================================================
// choix date
//=====================================================================

$year0=$year -1;
$year1=$year +1;
echo "<form>";
echo "<table class=noBorder><tr><td>année </td><td>
        <select id='menu1' name='menu1'";
echo "onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,this.form.menu4)'>";

echo "<option value='$year0'>".$year0."</option>";
echo "<option value='$year' selected >".$year."</option>";
echo "<option value='$year1' >".$year1."</option>";
echo  "</select>";

echo " mois <select id='menu2' name='menu2'"; 
echo "onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,this.form.menu4)'>";

$m=1;
while ($m <=12) {
    $monmois = $mois[$m - 1 ];
    if ( $m == $month ) echo  "<option value='$m' selected >".$monmois."</option>\n";
    else echo  "<option value= $m >".$monmois."</option>\n";
    $m=$m+1;
}
echo  "</select>";
echo  "</td>";
echo "";

//=====================================================================
// choix section
//=====================================================================

echo "</tr><tr><td>Section</td><td colspan=3>";
echo " <select id='menu4' name='menu4' 
    onchange='fillmenu(this.form,this.form.menu1,this.form.menu2,this.form.menu3,this.form.menu4)'>";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td>";
echo "</tr></table></form>";

// =====================================================================
// histogram
// =====================================================================   

//echo "<img src=dispo_view_pic.php?section=$filter&year=$year&month=$month&type=$type><p></div>" ;

print repo_dispo_view($filter,$year,$month,$type);
writefoot();
?>
