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
check_all(18);

?>

<html>
<SCRIPT language=JavaScript>
function redirect(code) {
    url="upd_type_evenement.php?TE_CODE="+code;
    self.location.href=url;
}
</SCRIPT>

<?php
$TB_ID=intval($_GET["TB_ID"]);

$query="select TE_CODE, TB_NUM from type_bilan where TB_ID=".$TB_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

//=====================================================================
// suppression fiche
//=====================================================================

if ( $TB_ID > 0 and intval($TB_NUM) > 0 ) {
    $query="delete from type_bilan where TB_ID =".$TB_ID." and TE_CODE='".$TE_CODE."' and TB_NUM=".$TB_NUM;
    $result=mysqli_query($dbc,$query);

    $query="delete from bilan_evenement where TB_NUM =".$TB_NUM." and E_CODE in (select E_CODE from evenement where TE_CODE='".$TE_CODE."')";
    $result=mysqli_query($dbc,$query);
}
echo "<body onload=\"redirect('".$TE_CODE."');\">";