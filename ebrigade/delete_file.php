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
check_all(14);
$nomenu=1;
writehead();

$file=$_GET["file"];

$array = explode('.', $file);
$extension = end($array);

if (strpos($file, '..') !== false or ( $extension <> 'sql' and $extension <> 'save')) {
    write_msgbox("Erreur", $error_pic, "Les param�tres fournis sont incorrects.<p><a href='restore.php'><input type='submit' class='btn btn-default' value='Retour'></a>", 10,0);
    exit;
}

$path=$filesdir."/save/";
unlink($path."/".$file);
echo "<body onload=\"javascript:self.location.href='restore.php';\">";


?>
