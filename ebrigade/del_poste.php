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

function redirect() {
     url="poste.php?order=PS_ID&filter=ALL";
     self.location.href=url;
}

</SCRIPT>

<?php
$id=$_SESSION['id'];
$PS_ID=intval($_GET["PS_ID"]);

//=====================================================================
// suppression fiche
//=====================================================================

$query="delete from qualification where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from equipage where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from personnel_formation where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from diplome_param where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from evenement_competences where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="update evenement set PS_ID=null where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="update type_vehicule_role set PS_ID=0 where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from poste where PS_ID=".$PS_ID ;
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect()>";

?>
