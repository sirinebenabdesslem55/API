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
get_session_parameters();
$page=secure_input($dbc,$_GET["page"]);

$modal=true;
$nomenu=1;
writehead();
write_modal_header("Choix de l'ordre dans la liste d�roulante");

$html = "<div align=center>";
if ( $sectionorder == 'alphabetique') $checked='checked';
else $checked='';
$html .= "<label>Alphab�tique <input id='sectionorder' name='sectionorder' type='radio' value='alphabetique' 
                onclick=\"changeSectionOrder('".$page."','alphabetique')\"; $checked /></label><br>"; 

if ( $sectionorder == 'hierarchique') $checked='checked';
else $checked='';
$html .= "<label>Hi�rarchique <input id='sectionorder' name='sectionorder' type='radio' value='hierarchique' 
                onclick=\"changeSectionOrder('".$page."','hierarchique')\"; $checked /></label>";
$html .= "</div>";

print $html;
writefoot();
?>