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

$query="select distinct p.P_PHOTO, p.P_ID, p.P_NOM, p.P_PRENOM, s.S_CODE, s.S_ID,
        date_format(a.A_DEBUT,'%H:%i') A_DEBUT, date_format(a.A_FIN,'%H:%i') A_FIN,
        a.A_OS, a.A_BROWSER, a.A_IP, p.P_SEXE
        from pompier p, section s, audit a
        where p.P_ID = a.P_ID
        and p.P_SECTION =  s.S_ID
        and ( a.A_DEBUT > DATE_SUB(now(), INTERVAL 10 MINUTE) 
              or a.A_FIN > DATE_SUB(now(), INTERVAL 3 MINUTE)
            )";

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


echo "<div align=center class='table-responsive'>
    <font size=4><b>Utilisateurs en ligne</b> <span class='badge'>$number</span>
    <a href='gps.php'><i class='fas fa-map-marker-alt fa-lg' title='Localiser le personnel connecté avec leur position GPS'></i></a>";
echo "<p><table class='noBorder'>";
echo "<tr><td>";
echo choice_section_order('connected_users.php');
echo "</td><td><select id='filter' name='filter' 
        onchange=\"orderfilter1('connected_users.php',document.getElementById('filter').value,'".$subsections."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select> ";
echo "</td></tr>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<tr><td></td><td align=left><input type='checkbox' name='sub' id='sub' $checked 
       onClick=\"orderfilter2('connected_users.php',document.getElementById('filter').value, this)\"/>
       <label for='sub'>inclure les $sous_sections</label></td></tr>";
}
echo "<tr><td colspan=2>";

$result=mysqli_query($dbc,$query);

echo "</td></tr></table>";
echo "<p><table class='noBorder'>";


// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while (custom_fetch_array($result)) {
    
    if ( strstr($A_OS, 'Android') or  strstr($A_OS, 'iOS') ) {
        $icon = 'fas fa-mobile-alt fa-lg';
        $color='black';
    }
    else {
        $icon = 'fas fa-desktop fa-lg';
        $color='#808080';
    }
    if ( strstr($A_BROWSER, 'Chrome')) {
        $icon2 = 'fab fa-chrome fa-lg';
        $color2='green';
    }
    else if ( strstr($A_BROWSER, 'Firefox'))  {
        $icon2 = 'fab fa-firefox fa-lg';
        $color2='#ff6600';
    }
    else if ( strstr($A_BROWSER, 'Edge')) {
        $icon2 = 'fab fa-edge fa-lg';
        $color2='#3333ff';
    }
    else if ( strstr($A_BROWSER, 'Safari')) {
        $icon2 = 'fab fa-safari fa-lg';
        $color2='#b3b3ff';
    }
    else {
        $icon2='fab fa-internet-explorer fa-lg';
        $color2='#3399ff';
    }
    if ( $P_PHOTO <> '' and file_exists($trombidir."/".$P_PHOTO)) {
        $img=$trombidir."/".$P_PHOTO;
        $class="class='rounded'";
        $h=40;
    }
    else {
        $class="";
        if ( $P_SEXE == 'M' )   $img = 'images/boy.png';
        else $img = 'images/girl.png';
        $h=30;
    }

    echo "<tr >
            <td><img src='".$img."' $class border=0 height=$h onclick='displaymanager(".$P_ID.");'></td>
            <td align=left style='min-width:230px;'>
                <a href=upd_personnel.php?from=default&pompier=".$P_ID.">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</a>
                (<a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE."</a>)
            </td>";
    echo "  <td align=center width=15><i class='".$icon."' style='color:".$color.";' title ='".$A_OS."' ></i><td>
            <td align=center width=15><i class='".$icon2."' style='color:".$color2.";' title ='".$A_BROWSER."' ></i><td>
            <td align=left style='min-width:100px;'><span title='Heures de début de la connexion et de la dernière action'>".$A_DEBUT." - ".$A_FIN."</span>
            </td>
            <td align=left>".$A_IP."</td>
      </tr>"; 
}
echo "</table></div>";

echo " <p><input type='button' class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";
writefoot();
?>

