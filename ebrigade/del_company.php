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
check_all(19);
check_all(37);

?>

<html>
<SCRIPT language=JavaScript>

function redirect() {
     url="company.php";
     self.location.href=url;
}

</SCRIPT>

<?php
$C_ID=intval($_GET["C_ID"]);

//=====================================================================
// suppression fiche
//=====================================================================

if ( $C_ID > 0 ) {
    $query="delete from company where C_ID =".$C_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update personnel set C_ID= 0 where C_ID=".$C_ID;
    $result=mysqli_query($dbc,$query);

    $query="update evenement set C_ID=null where C_ID=".$C_ID;
    $result=mysqli_query($dbc,$query);
}
echo "<body onload=redirect()>";

?>
