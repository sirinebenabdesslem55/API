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

require_once ("lib/nusoap/src/nusoap.php");

// -------------------------------------------------------------------
// Define SOAP Server
// -------------------------------------------------------------------
// for test purpose, use local server
$ebrigadeserver="http://localhost/ebrigade";
// in real world write site URL, example
//$ebrigadeserver="https://franceprotectioncivile.org";

// -------------------------------------------------------------------
// provide the key for accessing webservices
// -------------------------------------------------------------------
// for test purpose, use local server $webservice_key is defined
@require_once ("config.php");
// in a real world hardcode the key here
// $webservice_key = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

// -------------------------------------------------------------------
// Connect
// -------------------------------------------------------------------
$client = new nusoap_client($ebrigadeserver."/soap.php", false);
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = TRUE;
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}

// -------------------------------------------------------------------
// Define input parameters
// -------------------------------------------------------------------
if (isset($_GET["login"])) $login = $_GET["login"];
elseif (isset($_POST["login"])) $login = $_POST["login"];
else die("ERROR: parameter login not supplied");

if (isset($_GET["password"])) $password = $_GET["password"];
elseif (isset($_POST["password"])) $password = $_POST["password"];
else die("ERROR: parameter password not supplied");

// -------------------------------------------------------------------
// call getUserInfo
// -------------------------------------------------------------------

$param =  array(
    "key" => $webservice_key, 
    "login" => $login, 
    "password" => $password);
$result = $client->call("getUserInfo", $param);

if ($client->fault) {
    echo "<h2>Fault</h2><pre>";
    print_r($result);
    echo "</pre>";
}
else {
    $error = $client->getError();
    if ($error) {
        echo "<h2>Error</h2><pre>" . $error . "</pre>";
    }
    else {
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
}

?>
