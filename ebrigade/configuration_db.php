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
    
if ( isset ($_GET["ask"]) or  isset ($_POST["save"]) or  isset ($_GET["err"])) {
    $identpage='index.php';
    $noconnect=1;
}

include_once ("config.php");
include_once ("fonctions_sql.php");

$nomenu=1;
writehead();
?>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript'>
function redirect() {
     cible="index.php";
     self.location.href=cible;
}
</script>
<?php
echo "</head>";


if ( isset ($_POST["save"])) {
    $server=$_POST["server"];
    $user=$_POST["user"];
    $password=$_POST["password"];
    $database=$_POST["database"];
}
else {
    if ( file_exists($config_file)) 
        include_once ($config_file);
    else {
        $server='';
        $user='';
        $password='';
        $database='';
    }
}

echo "<body>";

$err=0;$errmsg="";
if (isset($_GET["ask"])) $err=1;
else if ($server == "") $err=2;
else if ($database == "") $err=3;
else if ($user == "") $err=4;
else {
    $dbc=@mysqli_connect("$server","$user", "$password", "$database") or $err=1;
    if ( $err == 1 ) $errmsg=mysqli_connect_error();
    mysqli_query($dbc,"SET sql_mode = '';");
    mysqli_query($dbc,"SET NAMES 'latin1'");
}
if ( isset($_POST["save"])) { 
    if ( $err > 0 ) {
        if ( $err == 2 ) $msg = "Erreur le paramètre <b>serveur</b> n'est pas renseigné.</b>";
        else if ( $err == 3 ) $msg = "Erreur le paramètre <b>database</b> n'est pas renseigné.</b>";
        else if ( $err == 4 ) $msg = "Erreur le paramètre <b>user</b> n'est pas renseigné.</b>";
        else $msg = "Erreur de connection à la base de données avec les paramètres choisis:<p><b>".$errmsg."</b>";
        echo "<div align='center'><div class='alert alert-danger' role='alert'>".$msg."</div></div><p>";
    }
    else {
        $ret = write_db_config($server,$user,$password,$database);
        if ( $ret == 1 ) {
            echo "<div align=center><div class='alert alert-danger' role='alert'>Erreur d'écriture du fichier de configuration conf/sql.php.</div></div><p>";
           }
    }
}

// load reference schema if needed
if (( $err == 0 ) and ( check_ebrigade() == 0 )) {
    create_sql_functions();
    load_reference_schema();
    load_zipcodes();
    echo "<p>";
    exit;
}

if ( $err == 0 ) {
        unset ($noconnect);
           include_once ("config.php");
           check_all(14);
}

if (! file_exists($config_file)) {

echo "<div align=center><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-database fa-2x'></i></td><td>
      <font size=4><b>Configuration Base de données</b></font></td></tr></table>";

echo "<form method='POST' name='config' action='configuration_db.php' >";
echo "<p>";
echo "<table cellspacing=0 border=0>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr class=TabHeader>
      <td colspan=2>Paramètres de connexion à la base de données</td>
      </tr>";

// ===============================================
// le corps du tableau
// ===============================================

$m=$mylightcolor;
echo "<tr>
      <td bgcolor=$m align=right>Server Name</td>
      <td bgcolor=$m align=left valign=middle>
      <input name='server' type=text value='$server' size=25 title='Server de bases de données, par exemple localhost ou db811501582.hosting-data.io'
        onchange='isValid2(config.server,\"$server\")' autocomplete='OFF'>"; 
echo "</tr><tr>
      <td bgcolor=$m align=right>User</td>
      <td bgcolor=$m align=left valign=middle> 
      <input name='user' type=text value='$user' size=25  title='Par exemple dbo811501582'
      onchange='isValid2(config.user,\"$user\")' autocomplete='OFF'>"; 
echo "</tr><tr>
      <td bgcolor=$m align=right>Password</td>
      <td bgcolor=$m align=left valign=middle >
      <input name='password' type=text value='$password' size=25 autocomplete='OFF' >"; 
echo "</tr><tr>
      <td bgcolor=$m align=right>Database name</td>
      <td bgcolor=$m align=left valign=middle>
      <input name='database' type=text value='$database' size=25 title='Par exemple db811501582'
      onchange='isValid2(config.database,\"$database\")' 
      onMouseOut='isValid2(config.database,\"$database\")' autocomplete='OFF'></td></tr>
</table>
<input type='hidden' name='save' value='yes'><p>
<input type=submit value='valider'  class='btn btn-default' onClick=\"this.disabled=true;this.value='attendez...';document.config.submit()\"/>
</form></div>";
}
else {
   write_msgbox("Application indisponible",$error_pic,"<p>La base de données n'est pas accessible.<p>Vérifiez que la base soit bien démarrée.<p>Puis vérifiez les paramètres de configuration dans le fichier ".$config_file."<p><input type=submit  class='btn btn-default'  value='retour' onclick='javascript:redirect();'></p>",30,30);
 
}
writefoot();
?>
