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
include('lib/phpqrcode/qrlib.php'); 
check_all(0);
$id=$_SESSION['id'];
if ( isset($_GET["pid"]) and check_rights($id, 14) ) $pid = intval($_GET["pid"]);
else $pid=$id;
if ( isset($_GET["action"]) ) $action = $_GET["action"];
else $action='data';
extract_qr_code($pid, $action);
exit;

?>