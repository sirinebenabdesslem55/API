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
writehead();
 
echo "<body>";
echo "<div align=left style='position: absolute; left: 5%;' >";
if ($application_title <> 'eBrigade') 
    echo "<h1>".$application_title."</h1>
        <p> Est une application de $cisname, utilisant le projet opensource eBrigade";

if ( $patch_version <> '' ) $version = $patch_version;

echo "
    <p><b>eBrigade $version : application pour la gestion opérationnelle
    <br>des sapeurs pompiers et du personnel de secours
    <br>Copyright <i class='fa fa-copyright'></i> 2004-2020 Nicolas MARCHE</b> 
    
    <p>This program is free software; you can redistribute it and/or modify
    <br>it under the terms of the GNU General Public License as published by
    <br>the Free Software Foundation; either version 2 of the License, or
    <br>(at your option) any later version.

    <p>This program is distributed in the hope that it will be useful,
    <br>but WITHOUT ANY WARRANTY; without even the implied warranty of
    <br>MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    <br>GNU General Public License for more details.

    <p>You should have received a copy of the GNU General Public License along
    <br>with this program; if not, write to the Free Software Foundation, Inc.,
    <br>51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
    
    <p><b>eBrigade web site : </b><a href='http://ebrigade.net' target =_blank>http://ebrigade.net</a>
    <p><b>Project page : </b><a href='http://sourceforge.net/projects/ebrigade' target =_blank>http://sourceforge.net/projects/ebrigade</a>
    <br>Author: Nicolas MARCHE 
    <br>Contact : <a href='mailto:contact@ebrigade.net'>contact@ebrigade.net</a>
    <br>Contributors: Jean-Pierre KUNTZ, Michel GAUTIER
    
    <p><b>3rd party software used in eBrigade, a big thank to their authors <i class='far fa-smile-wink fa-lg'></i></b>
    <br>jQuery 1.12: <a href='https://jquery.com' target =_blank>https://jquery.com</a>
    <br>Bootstrap 4.3.1: <a href='http://getbootstrap.com' target =_blank>http://getbootstrap.com</a>
    <br>Datepicker for Bootstrap 1.8.0: <a href='https://github.com/uxsolutions/bootstrap-datepicker' target =_blank>https://github.com/uxsolutions/bootstrap-datepicker</a>
    <br>Font Awesome 5.11.2:  <a href='https://fortawesome.github.io/Font-Awesome' target =_blank>https://fortawesome.github.io/Font-Awesome</a>
    <br>FPDF 1.8.2: <a href='http://www.fpdf.org' target =_blank>http://fpdf.org</a>
    <br>PHPExcel 1.8.1: <a href='https://github.com/PHPOffice/PHPExcel' target =_blank>https://github.com/PHPOffice/PHPExcel</a>
    <br>PHPQRCode 1.1.4: <a href='http://phpqrcode.sourceforge.net' target =_blank>http://phpqrcode.sourceforge.net</a>
    <br>FullCalendar 3.7.0: <a href='https://fullcalendar.io' target =_blank>https://fullcalendar.io</a>
    <br>PHPMailer 5.2.6: <a href='https://github.com/PHPMailer/PHPMailer' target =_blank>https://github.com/PHPMailer/PHPMailer</a>
    <br>Nusoap 0.9.5: <a href='https://sourceforge.net/projects/nusoap/' target =_blank>https://sourceforge.net/projects/nusoap</a>
    <br>JVectorMap 2.0.3: <a href='http://jvectormap.com' target =_blank>http://jvectormap.com</a>
";

echo "<p><table class='noBorder'>";

echo "<tr><td><a href=license_fr.txt target=_blank><img src=images/french.png height=22 border=0></a></td><td><a href=license_fr.txt target=_blank> Lire la license en français </a></td></tr>";
echo "<tr><td><a href=license.txt target=_blank><img src=images/english.png height=22 border=0></a></td><td><a href=license.txt target=_blank>  Read english license </a></font></td></tr>";
echo "</table>";
?>

<p>
<img src=images/logo.jpg height=40>
<div class="fb-like" 
data-href="http://sourceforge.net/projects/ebrigade/" 
data-send="false" 
data-width="450" 
data-show-faces="false">
</div>

<p style="padding-left:100px; padding-top: 50px">
<input type='submit' class='btn btn-default' value='retour' onclick='javascript:history.back(1);'>
</div>

 
<!-- facebook like -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>


<?php
writefoot(); 
?>

