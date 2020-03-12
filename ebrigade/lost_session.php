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
@session_start();
$dbc=connect();
$nomenu=1;
writehead();
@setcookie ("PHPSESSID", $_COOKIE['PHPSESSID'], time() - 864000, '/');
echo  "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<body><div align=center>";
write_msgbox("erreur connexion",$error_pic,$error_4."<p align=center><input type=button class='btn btn-default' value=\"s'identifier\" onclick=\"javascript:self.location.href='".$identpage."';\">",30,30);
writefoot();
?>