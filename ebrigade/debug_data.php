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
$data=urldecode($_GET["data"]);
$modal=true;
$nomenu=1;
write_modal_header("<i class='fa fa-bug fa-lg noprint' style='color:red;' aria-hidden='true' title='cliquer pour voir le message de debug'></i> Debug data");
writehead();
print "<div align=left style='margin: 25px; font-size: 9pt; font-family: monospace;'>".$data."</div><p style='margin-bottom:150px;'>";
writefoot();
?>