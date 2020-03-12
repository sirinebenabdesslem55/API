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
check_all(76);

// pr�f�reence affichage carte
if ( isset ($_POST['zoomlevel'])) {
    $_SESSION['zoomlevel'] = intval($_POST['zoomlevel']);
}
else if ( isset ($_POST['maptypeid'])) {
    // MapTypeId : HYBRID, ROADMAP, SATELLITE, TERRAIN
    $_SESSION['maptypeid'] = strtoupper($_POST['maptypeid']);
}
else if ( isset ($_POST['centerlat'])) {
    $_SESSION['centerlat'] = (float)($_POST['centerlat']);
    $_SESSION['centerlng'] = (float)($_POST['centerlng']);
}


?>
