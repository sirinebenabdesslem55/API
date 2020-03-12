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
include_once ("fonctions_sms.php");
check_all(23);
writehead();
$id=intval($_SESSION['id']);
$mysection = $_SESSION['SES_SECTION'];
$pid=intval($_GET["pid"]);

$query="select D_SECRET from demande where P_ID=".$pid." and D_TYPE='gps'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row[0] == "" ) $key = generateSecretString();
else $key=$row[0];
$message="$cisname cherche � vous localiser, cliquez ".$cisurl."/localize_me.php?pid=".$pid."&key=".$key;
$query="delete from demande
            where P_ID = '".$pid."'
        and D_TYPE = 'gps'";
$result=mysqli_query($dbc,$query);
$query="insert into demande ( P_ID, D_TYPE, D_SECRET, D_DATE, D_BY)
        values ( '".$pid."' , 'gps', '".$key."', NOW() , ".$id.")";
$result=mysqli_query($dbc,$query);
$sent = send_sms ( "$id", "$pid", "$message", "$mysection", "gps.php" );
insert_log('DEMGPS', "$pid");

?>
