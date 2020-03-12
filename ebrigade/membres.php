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
$mysection=$_SESSION['SES_SECTION'];

$nomenu=1;
writehead();
write_modal_header("Liste");
$groupe=intval($_GET["groupe"]);

echo "<body class='top15'><div align=center>";

// ===============================================
// membres du groupe
// ===============================================

$query="select GP_ID, GP_DESCRIPTION
        from groupe
        where GP_ID = ".$groupe;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$GP_DESCRIPTION=$row["GP_DESCRIPTION"];

$query="select p.P_NOM, p.P_PRENOM, s.S_CODE, s.S_DESCRIPTION
        from pompier p , section s
        where p.P_SECTION= s.S_ID
        and p.P_OLD_MEMBER = 0
        and ( p.GP_ID = ".$groupe." or p.GP_ID2 = ".$groupe.")
        union
        select p.P_NOM, p.P_PRENOM, s.S_CODE, s.S_DESCRIPTION
        from pompier p , section s, section_role  sr
        where sr.S_ID= s.S_ID
        and sr.GP_ID=".$groupe."
        and p.P_OLD_MEMBER = 0
        and sr.P_ID = p.P_ID
        order by P_NOM, P_PRENOM";
$result=mysqli_query($dbc,$query);
echo "<table border=0 cellspacing=0 cellpadding=0 width=460>
      <tr class=TabHeader>";
if ( $groupe < 100 ) 
echo "<td colspan=2>liste des membres du groupe $GP_DESCRIPTION</td>";
else
echo "<td>$GP_DESCRIPTION</td><td>Section</td>";
echo "</tr>";
$i=0;
while (custom_fetch_array($result)) {
    $i=$i+1;
    if ( $i%2 == 0 ) {
           $mycolor=$mylightcolor;
    }
    else {
           $mycolor="#FFFFFF";
    }
    $sec=$S_CODE." - ".$S_DESCRIPTION;
    echo "<tr bgcolor=$mycolor>
            <td>".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</td>
            <td align=left class=small>$sec</td>
          </tr>";
}

echo "</table><p>";
writefoot();

?>
