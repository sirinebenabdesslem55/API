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
check_php();


// if param evenement,absence or note provided, keep it 1 minute as a cookie
if (isset($_GET["evenement"])){
  $evenement=intval($_GET["evenement"]);
  setcookie("evenement", $evenement, time()+60); 
}
if (isset($_GET["absence"])){
  $absence=intval($_GET["absence"]); 
  setcookie("absence", $absence, time()+60); 
}
if (isset($_GET["note"])){
  $note=intval($_GET["note"]); 
  setcookie("note", $note, time()+60); 
}

// connexion SSO
if (isset($_GET["id"])) $id=intval($_GET["id"]);
else $id=0;
if (isset($_GET["ticket"])) $ticket=secure_input($dbc,$_GET["ticket"]);
else $ticket = "no-ticket-found";
$sso_connect=false;

if ( $id > 0 ) {
    // y a t'il un ticket créé depuis moins de 15 minutes?
    $query="select D_DATE from demande
        where P_ID=".$id."
        and D_TYPE='sso'
        and D_SECRET = '".$ticket."'
        and TIMESTAMPDIFF(MINUTE,D_DATE,NOW()) < 15 ";
    $result=mysqli_query($dbc,$query);
     
    if ( mysqli_num_rows($result) > 0 ) {
        $query="delete from demande where P_ID=".$id;
        $result=mysqli_query($dbc,$query);
        create_session($id);
        $sso_connect=true;
    }
}

if ( ! isset($_SESSION['id']) and ! $sso_connect) {
    include ("identification.php");
}
else {
    $accept_date=get_accept_date ($_SESSION['id']);
    if ( $accept_date == '' and $charte_active) {
        echo "<body onload=\"javascript:self.location.href='charte.php';\">";
        exit;
    }
    
    if         ( isset($_GET["evenement"]))  $page='evenement_display.php?evenement='.intval($_GET['evenement']);
    else if ( isset($_GET["absence"])) $page='indispo_display.php?code='.intval($_GET['absence']);
    else if ( isset($_GET["note"])) $page='note_frais_edit.php?nfid='.intval($_GET['note']);
    else if ( isset($_COOKIE['evenement'])) $page='evenement_display.php?evenement='.intval($_COOKIE['evenement']);
    else if ( isset($_COOKIE['absence'])) $page='indispo_display.php?code='.intval($_COOKIE['absence']);
    else if ( isset($_COOKIE['note'])) $page='note_frais_edit.php?nfid='.intval($_COOKIE['note']);
    else $page='index_d.php';

    echo "<body onload=\"javascript:self.location.href='".$page."';\">";
}
?>
  


