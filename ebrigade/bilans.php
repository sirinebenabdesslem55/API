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
check_all(27);
get_session_parameters();
writehead();
test_permission_level(27);

?>
<script language="JavaScript">
function orderfilter(){
    section=document.getElementById('filter').value;
    year=document.getElementById('year').value;   
    self.location.href="bilans.php?filter="+section+"&year="+year;
    return true
}
</script>
<?php
echo "</head>";
echo "<body>";

//=====================================================================
// formulaire
//=====================================================================

echo "<div align=center class='table-responsive'><font size=4><b>Bilans annuels</b><br>";
echo "<p><table class='noBorder'>";
echo "<tr><td>";
echo choice_section_order('bilans.php');
echo "</td><td><select id='filter' name='filter' 
        onchange=\"orderfilter();\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select> ";

echo "</td></tr>";

$yearnext=date("Y") +1;
$yearfirst = date("Y") - 8;

echo "<tr><td>année</td><td>
<select name='year' id='year' onchange=\"orderfilter();\">";
for ( $y = $yearfirst; $y <= $yearnext; $y ++ ) {
    if ( $year == $y ) $selected = 'selected';
    else $selected='';
    echo "<option value='$y' $selected>".$y."</option>";
}
echo  "</select></td></tr>";
echo "</table>";

//=====================================================================
// write links
//=====================================================================

function write_link($num,$title) {
    global $filter, $year;
    $icon="<i class=' far fa-file-pdf  fa-2x' style='color:red;'>";
    $link="<a href=pdf_bilans.php?filter=".$filter."&year=".$year."&type=".$num." title='Générer le PDF pour ".$title."' target='_blank'>";
    echo "<tr>
        <td width=30>".$link.$icon."</a></td>
        <td>".$link.$title."</a></td>
    </tr>";
}

echo "<p><table class=noBorder>";

write_link(1,"Généralités, personnel, moyens");
write_link(2,"Activités opérationnelles");
write_link(3,"Formations");

echo "</table>
    <p>
    <input type='button' class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'>";
writefoot();
?>
