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
$nomenu=1;
writehead();
cookie_test_js();
include_once("css/css.php");
$html =  "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";

require_once('browscap.php');
$b=get_browser_ebrigade();
$OS = $b -> platform;

$html .= "
<script src='".$basedir."/js/jquery.min.12.js'></script>
<script type='text/javascript'>
function autoclick(form) {
    if ( form.id.value )
        form.submit();
}
</script>
</head>";

if ( get_splash() <> '' ) {
    $show_banner=false;
    $class="class='body_splash'";
}
else {
    $show_banner=true;
    $class="";
}

$html .= "<body ".$class." >";
$html .= "<div class='container' align='center'>";

if ( $show_banner ) {
    $banner = get_banner();
    $html .= "<img src='".$banner."'  style='max-height:160px;max-width:100%'>";
}
else {
    echo "<p style='padding-top:130px;'>";
}

$html .= "<p>
        <div class='row'>
            <div class='col-8 col-md-6 col-lg-4 offset-md-4 offset-sm-3 offset-2'>
                <div class='card' style='background-color:".$mydarkcolor.";'>
                    <div class='card-header'> 
                        <strong>".$cisname." - identifiez vous</strong>
                    </div>
                    <div class='card-body'>
                        <form role='form' name='form1' if='form1' action='check_login.php' method='POST'>
                            <fieldset>
                                <div class='row'>
                                    <div class='col-md-10 col-lg-10 offset-sm-1 offset-md-1'>
                                    
                                    <div class='input-group mb-3'>
                                      <div class='input-group-prepend'>
                                        <span class='input-group-text' id='basic-addon1'><i class='fa fa-user'></i></span>
                                      </div>
                                      <input type='text' name='id' class='form-control no-round-left' placeholder='Identifiant' aria-describedby='basic-addon1'>
                                    </div>
                                    
                                    <div class='input-group mb-3'>
                                      <div class='input-group-prepend'>
                                        <span class='input-group-text' id='basic-addon1'><i class='fa fa-lock'></i></span>
                                      </div>
                                      <input type='password' name='pwd' class='form-control no-round-left' placeholder='Mot de passe'  autocomplete='OFF'";
                                      
if ( strstr($OS, 'Android') or  strstr($OS, 'iOS') )
    $html .= "                           onchange='autoclick(this.form);'>";
else
    $html .= "                           >";
$html .= "                           </div>
                                    
                                    <div class='form-group' id='login-btn'>
                                        <button class='btn btn-ebrigade btn-lg' type='submit'> <i class='fa fa-sign-in-alt fa-lg'></i> Entrer</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <div class='card-footer '> <a href='lost_password.php'>Mot de passe perdu </a>
                    </div>
                </div>
            </div>
        </div>
    </p>
</div>";

print $html; 
  
writefoot();
?>
