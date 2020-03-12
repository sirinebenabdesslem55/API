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
include_once ("fonctions_infos.php");
check_all(0);
$id=$_SESSION['id'];
$SES_NOM=$_SESSION['SES_NOM'];
$SES_PRENOM=$_SESSION['SES_PRENOM'];
$SES_GRADE=$_SESSION['SES_GRADE'];
writehead();
include_once ("css/css.php");
$body="";

if ($geolocalize_enabled) {
    $body .= "
    <script type='text/javascript' src='".$google_maps_url."'></script> 
    <script type='text/javascript' src='js/gps.js'></script>";
}
?>
<script>
function redirect() {
     url="configuration.php";
     self.location.href=url;
}
</script>
</head>
<?php

if ($already_configured == 0 and check_rights($id, 14)) {
    $body .= "<body onload=redirect();></body>";
    print $body;
    exit;
}
else $body .= "<body>";

$body .= "<img src=".get_banner()." style='max-height:200px; max-width:100%'><p>";
$body .= write_photo_warning($id);
$body .= write_buttons();
$body .= " <a href='configure_accueil.php'><i class = 'fa fa-cog fa-lg' title=\"configurer mon écran d'accueil\"></i></a>";
$body .= write_boxes();

print $body;
writefoot();

