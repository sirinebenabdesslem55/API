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
$section=$_SESSION['SES_SECTION'];
$parent=$_SESSION['SES_PARENT'];
echo "<body>";

$title=$application_title;

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width =60><i class='fa fa-info-circle fa-4x'></i></td><td>
      <font size=4><b> Documentation $title </b></font></td></tr></table><p>";

echo "<p><table  class='noBorder'>";

echo "<tr><td align=center><a href=$wikiurl target=_blank><i class='fa fa-book fa-2x'></i></a></td>";
        echo "<td><a href=$wikiurl target=_blank>Aide en ligne $title</a>";
        echo "</td><td>";
        echo "</td></tr>";

if ( is_file($userguide) ||  is_file($adminguide)) {
    if ( is_file($userguide)) {
         echo "<tr><td align=center><i class='fa fa-book fa-2x'></i></td>";
        echo "<td><a href=$userguide>Aide en ligne habilitation public</a>";
        echo "</td><td>";
        echo "<font size=1><i>modifié le ".date("d-m-Y H:i",filemtime($userguide))."</font></i>";
        echo "</td></tr>";
    }

    if ( is_file($adminguide)) {
        echo "<tr><td align=center><i class='fa fa-book fa-2x'></i></td>";
        echo "<td><a href=$adminguide>Aide en ligne autres habilitations</a>";
        echo "</td><td>";
        echo "<font size=1><i>modifié le ".date("d-m-Y H:i",filemtime($adminguide))."</font></i>";
        echo "</td></tr>";
    }
}

print_specific_doc();

echo "<tr>
    <td align=center><a href='http://www.teamviewer.com' target='_blank'><i class='fa fa-download fa-2x'></i></a></td>
    <td colspan=2><a href='http://www.teamviewer.com/download' target=_blank>Installer TeamViewer</a></td>
    </tr>";
    
// get webmaster email
$query="select P_EMAIL , NIV from pompier p, section_role sr, section_flat sf, groupe g
        where sr.P_ID = p.P_ID
        and sr.S_ID = sf.S_ID
        and sr.GP_ID = g.GP_ID
        and upper(g.GP_DESCRIPTION) like 'WEB%MASTER'
        and sr.S_ID in(".$section.",".$parent.")
        order by NIV desc";    
       
$result= mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["P_EMAIL"] <> "" ) $display_mail = $row["P_EMAIL"];        
else $display_mail = $admin_email;    
    
echo "<tr>
    <td align=center><a href='mailto:".$display_mail."'><i class='fa fa-at fa-2x'></a></td>
    <td colspan=2><a href='mailto:".$display_mail."' title='contacter pour le support'>".$display_mail."</a></td>
    </tr>";
    

    
echo "</table>";

echo "<p align=center><input type='button'  class='btn btn-default' value='retour' onclick='javascript:history.back(1);'>
<div>";

writefoot();
?>
    
