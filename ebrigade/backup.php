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
?>
<script language="JavaScript">
function redirect1(){
self.location.href="restore.php";
return true
}
function redirect2(){
self.location.href="index.php";
return true
}
</script>

<?php


if (! isset($_GET['mode']) ) $mode="auto";
else $mode=$_GET["mode"];

if ( $mode == "auto" ) check_all(0);
else check_all(14);

flush();

$path=$filesdir."/save/";
if (!is_dir($path)) mkdir($path, 0777);

$name=str_replace("_","",strtolower(fixcharset($cisname)));
$name=str_replace(".","",$name);
$name=str_replace(" ","",$name);
$name=str_replace("/","",$name);

if ($mode == "auto") {
    $cur_datetime=date("Y-m-d");
    $backupfile=$path.$name."_".$cur_datetime."_".$dbversion.".sql";
    echo "<p>";
    if (is_file($backupfile)) {
         write_msgbox("Error", $error_pic, "<p align=center><font face=arial>Il existe d�ja une sauvegarde.",10,0);
        exit;
    }
    else {
        write_msgbox("backup", $star_pic, "<p align=center><font face=arial>Une sauvegarde de la base de donn�es a �t� r�alis�e.",10,0);
        echo "<p>";
    }
    
}
// -----------------------------------------------------------
// Functions
//------------------------------------------------------------

function get_def($table) {
    global $dbc;
    $def = "";
    $def .= "DROP TABLE IF EXISTS $table ;
";
    $def .= "CREATE TABLE $table (
";
    $result = mysqli_query($dbc, "SHOW FIELDS FROM $table");
    $l = mysqli_num_rows($result); $k=1;
    while($row = mysqli_fetch_array($result)) {
        $def .= "$row[Field] $row[Type]";
        if ($row["Default"] != "") $def .= " DEFAULT '$row[Default]'";
        if ($row["Null"] != "YES") $def .= " NOT NULL";
        if ($row["Extra"] != "") $def .= " $row[Extra]";
        if ( $k < $l ) $def .= ",
";
        $k++;
    }
    $result = mysqli_query($dbc,"SHOW KEYS FROM $table");
    while($row = mysqli_fetch_array($result)) {
        $kname=$row["Key_name"];
          if(($kname != "PRIMARY") && ($row["Non_unique"] == 0)) $kname="UNIQUE|$kname";
          if(!isset($index[$kname])) $index[$kname] = array();
          $index[$kname][] = $row["Column_name"];
    }
    while(list($x, $columns) = @each($index)) {
          $def .= ",
";
        if($x == "PRIMARY") $def .= "PRIMARY KEY (" . implode($columns, ", ") . ")";
        else if (substr($x,0,6) == "UNIQUE") $def .= "   UNIQUE ".substr($x,7)." (" . implode($columns, ", ") . ")";
        else $def .= "KEY $x (" . implode($columns, ", ") . ")";
    }

    $def .= "
);";
    return (stripslashes($def));
}

function get_fields( $table ) {
    global $dbc;
    $fields="";
    $result = mysqli_query($dbc, "SHOW FIELDS FROM $table");
    while($row = mysqli_fetch_array($result)) {
        $fields .= "$row[Field],";
    }
    return rtrim($fields,',');
}

function get_content( $table ) {
    global $dbc;
    $content="";
    $result = mysqli_query($dbc, "SELECT * FROM $table");
    $i=0; $blocksize=200;
    if ( mysqli_num_rows($result) > 0 ) {
        while($row = mysqli_fetch_array($result)) {
            if ( $i == 0 or $i % $blocksize == 0 ) {
                if ( $i > 0 ) $content = rtrim($content,',').";
";
                $content .= "INSERT INTO $table (".get_fields( $table ).") VALUES";
            }
            $insert = "
(";
            for($j=0; $j<mysqli_num_fields($result);$j++) {
                if(!isset($row[$j])) $insert .= "NULL,";
                else if($row[$j] != "") $insert .= "'".addslashes($row[$j])."',";
                else $insert .= "'',";
            }
            $insert = rtrim($insert,',')."),";
            $content .= $insert;
            $i++;
        }
        $content = rtrim($content,',').";";
    }
    return $content;
}

// -----------------------------------------------------------
// Make backup
//------------------------------------------------------------

$cur_datetime=date("Y-m-d_H-i");
$cur_datetime_auto=date("Y-m-d");
$cur_date=date("d M Y");
$cur_time=date("H:i");
$dumphost=getenv('COMPUTERNAME');
$dbversion=get_conf(1);
@set_time_limit($mytimelimit);

$newfile="# ----------------------------------------------------------
# MYSQL Database dump
# Server : $server
# Database : $database
# Db version : $dbversion
# Date : $cur_date at $cur_time
# Dump Host : $dumphost
# ----------------------------------------------------------

SET sql_mode = '';
";

$tables = array();$i=0;
$query="show tables";
$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    $tables[$i]=$row[0];
    $i++;
}

$num_tables = $i;
$i = 0;
while($i < $num_tables) {
    $table = $tables[$i];
    $newfile .= "

# ------------------------------------
# structure for table '$table'
# ------------------------------------

";
    $newfile .= get_def($table);
    $newfile .= "
# ------------------------------------
# data for table '$table'
# ------------------------------------

";
    $newfile .= get_content($table);
    $newfile .= "";
    $i++;
}

if ( $mode == 'interactif' ) $newbackupfile=$name."_".$cur_datetime."_".$dbversion;
if ( $mode == 'auto' ) $newbackupfile=$name."_".$cur_datetime_auto."_".$dbversion;
$fp = fopen ($path.$newbackupfile.".sql","w");
fwrite ($fp,$newfile);
fclose ($fp);

// -----------------------------------------------------------
// Manage Backup files
//------------------------------------------------------------

$nbjourmois=date("t");

// comptage des fichiers sql : archives r�centes
$f_arr = array(); $f = 0;
$backupdir = opendir($path);
$nb=0;
while ($filename = readdir($backupdir)){
    if ($filename != "." && $filename != ".."){
        if (!is_dir($path.$filename)) {
            $path_parts = pathinfo("$filename");
            if ( @$path_parts["extension"] == "sql" ) {
                $f_arr[$f++] = $filename;
                $nb = $nb +1;
            }
        }
    }
}
closedir($backupdir);

// si dernier jour du mois : archivage
if ( date("j") == $nbjourmois) {
    copy ($path.$newbackupfile.".sql",$path.$newbackupfile.".save");
}

// supprimer les plus vieux pour n'en conserver que le nombre requis
$backupdir = opendir($path);
sort( $f_arr ); reset( $f_arr );
if ( $nb > $nbfiles ) {
    for( $i=0; $i < count( $f_arr ); $i++ ) {
        if ( $nb > $nbfiles ) {
                   unlink($path."/".$f_arr[$i]);
                $nb = $nb -1;
        }
    }
}

closedir($backupdir);

if ( $mode == 'interactif' ) {
    echo "<body onload=redirect1()>";
}
else echo "<body onload=redirect2()>";

writefoot();
?>
