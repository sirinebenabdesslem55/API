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
check_all(0);
writehead();
check_all(52);
get_session_parameters();

$number=get_section_nb();
?>

<script language="JavaScript">
function displaymanager(p1){
     self.location.href="upd_section.php?S_ID="+p1;
     return true
}
function bouton_redirect(cible) {
     self.location.href = cible;
}

function appear(id) {
    var d = document.getElementById(id);
    if (d.style.display!="none") {
        d.style.display ="none";
    } else {
        d.style.display ="";
    }
}

var imageURL = "images/tree_empty.png";
var te = new Image();
te.src = "images/tree_expand.png";
var tc = new Image();
tc.src = "images/tree_collapse.png";
var tec = new Image();
tec.src = "images/tree_expand_corner.png";
var tcc = new Image();
tcc.src = "images/tree_collapse_corner.png";

function changeImage(id) {
    var i = document.getElementById(id);
    if (i.src == te.src ) i.src = tc.src;
    else if (i.src == tc.src) i.src = te.src;
    else if (i.src == tec.src) i.src = tcc.src;
    else if (i.src == tcc.src) i.src = tec.src;
}



</script>

<?php
echo "</head>";
echo "<body>";
 
if ( $nbsections == 0 ) {
    $comment= my_ucfirst(implode(", ", $levels));
}
else $comment="Sections";

echo "<div align=center><table class='noBorder'>
      <tr><td ><font size=4><b>Organigramme</b></font> <span class='badge'>$number sections</span><br>$comment</td></tr>
      </table>";


echo "<p><table class='noBorder'><tr>";

echo "<td width=100 align=center><a href='departement.php' title=\"Vue sous forme de liste\"><i class='fa fa-list-ol fa-2x'></i></a></td>";
    
if ($expand == 'true') {
    $checked_e='checked';
    $checked_c='';
}
else {
    $checked_c='checked';
    $checked_e='';
}

if ( $nbsections == 0 ) {
    echo "<td width=100 align=center><input type='radio' value='expand' ".$checked_e." 
        name='displaytype' id='expand' onclick=\"bouton_redirect('section.php?expand=true')\"> <label for='expand'>Tout déplier</label></td>";

    echo "<td width=100 align=center><input type='radio' value='collapse' ".$checked_c." 
        name='displaytype' id='collapse' onclick=\"bouton_redirect('section.php?expand=false')\"> <label for='collapse'>Tout replier</label></td>";
}
if ( check_rights($_SESSION['id'], 55)) {
    $query="select count(1) as NB from section";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if ( $row["NB"] <= $nbmaxsections )
        echo "<td align=center width=150><input type='button' class='btn btn-default' value='Ajouter' name='ajouter' 
           onclick=\"bouton_redirect('ins_section.php');\"></td>";
    else
        echo "<td width=150><font color=red>
               <b>Vous ne pouvez plus ajouter de sections <br>(maximum atteint: $nbmaxsections)</b></font></td>";
}
else echo "<td width=150></td>";
echo "</tr></table>";


echo "<p><table cellspacing=0 cellpadding=0 border=0>";


// ===============================================
// le corps du tableau
// ===============================================
$End = array();
for ( $k=0; $k < $nbmaxlevels; $k++ ) {
    $End[$k] = 0;
    if ( $k == 10) return;
}

echo "<tr class=TabHeader><td width=500>Sections</td></tr>";
echo "<tr bgcolor=white><td>";

display_children0(-1, 0, $nbmaxlevels, $expand, 'hierarchique');

echo "</td></tr>";
echo "</table><p>";
writefoot();

?>
