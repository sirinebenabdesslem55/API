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
$M_ID=intval($_GET["M_ID"]);
$catmessage=$_GET["catmessage"];
if ( $catmessage == 'amicale' or $nbsections == 0 )  $numfonction=16;
else $numfonction=8;
check_all($numfonction);
verify_csrf('delmessage');
?>
<SCRIPT language=JavaScript>

function redirect(p1) {
     url="message.php?catmessage="+p1;
     self.location.href=url;
}
</SCRIPT>
<?php

//d'abord on enregistre le log
$query="select P_ID, date_format(M_DATE,'%d-%m-%Y') M_DATE, M_OBJET from message where M_ID=".$M_ID;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$P_ID=$row["P_ID"];
$M_DATE=$row["M_DATE"];
$M_OBJET=$row["M_OBJET"];
insert_log('DELMSG', $P_ID, $M_OBJET." (".$M_DATE.")");

//ensuite on supprime
$messages=$filesdir."/files_message";

if ( is_dir ($messages."/".$M_ID))
    full_rmdir($messages."/".$M_ID);
$query="delete from message where M_ID=".$M_ID;
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect('".$catmessage."')></body>";

?>
