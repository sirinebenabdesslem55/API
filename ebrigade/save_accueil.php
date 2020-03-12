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
$id=intval($_SESSION['id']);

// supprimer param�trage
if ( isset($_GET["supprimer"]) ) {
    $query = "delete from widget_user where P_ID = ".$id;
    $result=mysqli_query($dbc,$query);
    writehead();
    echo "<body onload=\"javascript:self.location.href='index_d.php';\">";
    writefoot();
    exit;
}

// modification
else if ( isset($_POST["wid"] ) ) {
    $wid=intval($_POST["wid"]);

    if ( isset($_POST["zone"] ) ) {
        $query = "delete from widget_user where P_ID = ".$id." and W_ID=".$wid;
        $result=mysqli_query($dbc,$query);
        $zone=intval($_POST["zone"]);
        $position=intval($_POST["position"]);
        $query = "insert into widget_user (P_ID, W_ID, WU_VISIBLE, WU_COLUMN, WU_ORDER) values (".$id.", ".$wid.", 1,".$zone.", ".$position." )";
        $result=mysqli_query($dbc,$query);
    }
    if ( isset($_POST["show"] ) ) {
        $show=intval($_POST["show"]);
        $query = "update widget_user set WU_VISIBLE = ".$show." where P_ID = ".$id." and W_ID=".$wid;
        $result=mysqli_query($dbc,$query);
        if ( mysqli_num_rows($result ) == 0 ) {
            $query = "insert into widget_user (P_ID, W_ID, WU_VISIBLE, WU_COLUMN, WU_ORDER) select ".$id.", ".$wid.", ".$show.",W_COLUMN, W_ORDER
            from widget where W_ID=".$wid;
            $result=mysqli_query($dbc,$query);
        }
    }
}