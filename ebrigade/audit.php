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
check_all(20);
get_session_parameters();
writehead();
test_permission_level(20);

echo "<script type='text/javascript' src='js/audit.js?version=".$version."'></script>";
echo "</head>";
echo "<body>";


$query="select p.P_SECTION , p.P_NOM , p.P_PRENOM, a.A_DEBUT, a.A_FIN, p.P_ID,
        a.A_OS, a.A_BROWSER, a.A_IP, g.GP_ID, p.GP_ID2, g.GP_DESCRIPTION, s.S_CODE, s.S_ID
        from audit a, pompier p, groupe g, section s
        where p.P_ID=a.P_ID
        and p.P_SECTION=s.S_ID
        and p.GP_ID=g.GP_ID";

if ( $subsections == 1 ) {
    $query .= " and p.P_SECTION in (".get_family("$filter").")";
}
else {
    $query .= " and p.P_SECTION =".$filter;
}
$query .= " and time_to_sec(timediff(now(),a.A_DEBUT)) < (24 * 3600 * ".$days_audit.")";
$query .= " order by a.A_DEBUT desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
$moyenne= round($number / $days_audit, 0);

echo "<div align=center class='table-responsive'><font size=4><b>Dernières connexions</b><br>";
echo "<font size=2> En moyenne <span class='badge'>$moyenne</span> connexions par jour.<br>";
echo "<font size=2> Total <span class='badge'>$number</span> connexions sur les $days_audit derniers jours.";
echo "<p><table class='noBorder'>";
echo "<tr><td>";
echo choice_section_order('audit.php');
echo "</td><td><select id='filter' name='filter' 
        onchange=\"orderfilter1('audit.php', document.getElementById('filter').value,'".$subsections."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select> ";
echo "</td></tr>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<tr><td></td><td align=left><input type='checkbox' name='sub' id='sub' $checked 
       onClick=\"orderfilter2('audit.php',document.getElementById('filter').value, this)\"/>
       <label for='sub'>inclure les $sous_sections</label></td></tr>";
}
echo "<tr><td colspan=2>";

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
    $query .= $pages->limit;
}
$result=mysqli_query($dbc,$query);


echo "</td></tr></table>";
echo "<p><table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr height=10 class=TabHeader >
            <td width=220>Nom</td>";
    echo "<td width=140 align=left>Section</td>";
    echo "<td width=120 align=center>Date Connexion</td>
            <td width=120 align=center>Dernière action</td>
            <td width=80 align=left>OS</td>
            <td width=160 align=left>Browser</td>
            <td width=100 align=left>adresse IP</td>
            <td width=140 align=left>Permission</td>
      </tr>
      ";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    if (( $GP_ID2 <> "" ) and ( $GP_ID == 0 )){
          $query2="select GP_DESCRIPTION from groupe
             where GP_ID =".$GP_ID2;
          $result2=mysqli_query($dbc,$query2);
          $row2=mysqli_fetch_array($result2);
          $GP_DESCRIPTION=$row2["GP_DESCRIPTION"];
    }

    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor="$mylightcolor";
    }
    else {
        $mycolor="#FFFFFF";
    }
      
    echo "<tr bgcolor=$mycolor>
            <td align=left><a href=upd_personnel.php?from=default&pompier=".$P_ID.">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</td>";
    echo "<td align=left><a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE."</td>";
    echo "    <td align=center class=small2>".$A_DEBUT."</td>
            <td align=center class=small2>".$A_FIN."</td>
            <td align=left class=small>".$A_OS."</td>
            <td align=left class=small>".$A_BROWSER."</td>
            <td align=left class=small>".$A_IP."</td>
            <td align=left class=small>".$GP_DESCRIPTION."</td>
      </tr>"; 
}
echo "</table></div>";  

echo " <p><input type='button' class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";
writefoot();
?>
