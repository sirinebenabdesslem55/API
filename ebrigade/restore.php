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

if ( isset($_GET["file"])) $file= secure_input($dbc,$_GET["file"]);
else $file="";
writehead();

?>
<script language="JavaScript">

function restore(where, what) {
   if ( confirm ("Vous allez appliquer sur la base de données avec le contenu du fichier " + what +  "?" )) {
         self.location = "restore.php?file=" + what;
         
   }
}
function deletefile(what) {
   if ( confirm ("Etes vous certain de vouloir supprimer ce fichier " + what +  "?" )) {
         self.location = "delete_file.php?file=" + what;
   }
}
function redirect(url) {
     self.location.href=url;
}
</script>
</head>
<?php

echo "<body>";

//====================================================
// restore
//====================================================

if ($file!="") {
    // avoid unexpected parameters, hackers attack
    $filename = str_replace('..','',str_replace('/','',$file));
    @set_time_limit($mytimelimit);
    $fullpath = $filesdir."/save/".$filename;
    
    if (! is_readable($fullpath)) {
        write_msgbox("Erreur fichier", $error_pic, "<p align=center>$filename n'est pas trouvé ou pas accessible <br><p align=center><a href=index_d.php><input type='submit' class='btn btn-default' value='Retour'></a>",10,0);
        exit;
    }
    
    $file=fread(fopen($fullpath,"r"),10485760);
    $query=explode(";
",$file);
    for ($i=0;$i < count($query)-1;$i++) {
        mysqli_query($dbc,$query[$i]) or print(mysqli_error($dbc));
    }
    echo "<p>";
    write_msgbox("opération réussie", $star_pic, "<p align=center>$filename rechargé avec succès! <br><p align=center><a href=index_d.php><input type='submit' class='btn btn-default' value='Retour'></a>",10,0);
    exit;
}

//====================================================
// backups
//====================================================
if (!is_dir($filesdir)) mkdir($filesdir, 0777);
$path=$filesdir."/save/";
if ( ! is_dir ($path)) mkdir($path, 0777);

if ($file=="") {
    echo "<div align=center><table class='noBorder'>
          <tr><td width = 60 ><i class='fa fa-database fa-3x'></i></td><td>
          <font size=4><b>Sauvegardes de la base de données</b></font></td></tr></table>";
    echo "<br><input type=submit class='btn btn-default' value='nouvelle sauvegarde' onclick='redirect(\"backup.php?mode=interactif\");'><p>";

    $f_arr = array(); $f = 0;
    $dir=opendir($path); 
    while ($file = readdir ($dir)) { 
        if ($file != "." && $file != ".." && file_extension($file) == 'save' ) {
            $f_arr[$f++] = $file;
        }
    }
    closedir($dir);

    $f2_arr = array(); $f = 0;
    $dir=opendir($path);
    while ($file = readdir ($dir)) {
        if ($file != "." && $file != ".." && file_extension($file) == 'sql') {
            $f2_arr[$f++] = $file;
        }
    }
    closedir($dir);

    if ( count( $f_arr )+count( $f2_arr )  > 0 ) {
        echo "<p><table cellspacing=0 border=0>";
    }

    if ( count( $f_arr ) > 0 ) {
        echo "<tr class=TabHeader >
          <td width=250>Fin de mois</td>
          <td width=80>Version</td>
          <td width=100>Size (kB)</td>
          <td width=130>Date</td>
          <td width=100>Actions</td>
        </tr>";

        sort( $f_arr ); reset( $f_arr );
        for( $i=0; $i < count( $f_arr ); $i++ ) {
            if ( $i%2 == 0 ) {
                $mycolor="$mylightcolor";
            }
            else {
                $mycolor="#FFFFFF";
            }
            echo "<tr bgcolor=$mycolor>
                 <td>".$f_arr[$i]."</td>
                 <td align=center>".get_file_version($f_arr[$i])."</td>
                <td align=center>
                  ".round(filesize($path.$f_arr[$i])/1024,1)."    
                </td>
               <td align=center>
                   ".date("Y-m-d H:i",filemtime($path.$f_arr[$i]))."
                   </td>
               <td align=center>
                   <a href=\"javascript:restore('save','".$f_arr[$i]."')\"> 
                   <i class='fa fa-file-import fa-lg' title='recharger la base'></i></a> 
                   <a href=\"javascript:deletefile('".$f_arr[$i]."')\"> 
                   <i class='fa fa-trash fa-lg' title='supprimer ce fichier'></i></a>
               </td>
               </tr>";
        }
    }

    if ( count( $f2_arr ) > 0 ) {
        echo "<tr class=TabHeader >
          <td width=250>Récentes</td>
          <td width=80>Version</td>
          <td width=100>Size (kB)</td>
          <td width=130>Date</td>
          <td width=100>Actions</td>
        </tr>";

        sort( $f2_arr ); reset( $f2_arr );
        for( $i=0; $i < count( $f2_arr ); $i++ ) {
            if ( $i%2 == 0 ) {
                $mycolor="$mylightcolor";
            }
            else {
                $mycolor="#FFFFFF";
            }
            if (date("d-m-Y",filemtime($path.$f2_arr[$i])) == getnow()) $bold="<b>"; 
            else  $bold="";
            echo "<tr bgcolor=$mycolor>
             <td>$bold ".$f2_arr[$i]."</td>
             <td align=center>$bold ".get_file_version($f2_arr[$i])."</td>
             <td align=center>
                  ". round(filesize($path.$f2_arr[$i])/1024,1)."    
                </td>
               <td align=center>
                   ".date("Y-m-d H:i",filemtime($path.$f2_arr[$i]))."
                   </td>
               <td align=center>
                   <a href=\"javascript:restore('save','".$f2_arr[$i]."')\"> 
                   <i class='fa fa-file-import fa-lg' title='recharger la base'></i></a> 
                   <a href=\"javascript:deletefile('".$f2_arr[$i]."')\"> 
                   <i class='fa fa-trash fa-lg' title='supprimer ce fichier'></i></a>
               </td>
               </tr>";
        }
    }
    echo "  </table>";
}
writefoot();
?>
