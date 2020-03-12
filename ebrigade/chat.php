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
check_all(51,'chat');
$id=$_SESSION['id'];

writehead();
echo "<script type='text/javascript' src='js/chat.js'></script></head>";
echo "<body onload='UpdateTimer();'>";

if (isset($_GET['del'])) {
    check_all(14,'chat');
    $todelete=intval($_GET['del']);
    $query="delete from chat where C_ID=".$todelete;
    $result=mysqli_query($dbc,$query);
}

$links="";
if ($geolocalize_enabled and check_rights($id,76))
    $links = " <a href='gps.php'><i class='fa fa-map-marker-alt fa-2x' title='Localiser le personnel connecté avec leur position GPS'></i></a>";
if (check_rights($id,20))
    $links .= " <a href='connected_users.php'><i class='fa fa-users fa-2x' title='Voir la liste des utilisateurs connecté'></i></a>";

echo "<div align=center><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-comment fa-3x'></i></td><td>
      <font size=4><b>Messagerie instantanée $cisname<br>Aide en direct</b></font> ".$links."</td>
      </tr></table>";
 if ( is_iphone()) $w=300;
 else $w=800;

echo "<div id='Chat' align=center >
    <div id='result' style='width:90%;max-width:800px;' ></div>";
echo "<p><div id='sender'>
        <small>Ajout message</small> <input type='text' name='msg' id='msg' style='width:50%;max-width:500px;' />
         <a class='btn btn-default' href='#' onclick='doWork();' title='Envoyer'><i class='fas fa-paper-plane'></i></a>
      </div>";
echo "</div>";
writefoot();
   
