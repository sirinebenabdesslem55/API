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
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
writehead();

verify_csrf('configuration');

if (isset($_POST["tab"])) $tab = $_POST["tab"];
else $tab = 'conf1';

?>

<SCRIPT>
function redirect(tab, res) {
    self.location.href = "configuration.php?saved="+res+"&tab="+tab;
}
</SCRIPT>
<?php

function save_config($confid, $value) {
    global $dbc, $error_pic, $tab;
    $value= str_replace("\"","",$value);
    // tester si les configurations sont supportées
    if ( $confid == 13 and  $value == 1 ) {
            $query="select count(1) as NB from pompier";
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $NB=$row["NB"];
            $max=1000;
            if ( $NB > $max ) {
                $msg="Il y a plus de $max utilisateurs dans votre base de données.
                <br>La sauvegarde automatique n'est plus supportée.
                <br>Vous devez mettre en place une sauvegarde avec mysqldump dans une crontab.
                <p align=center>
                <input type='button' class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
                write_msgbox("Configuration impossible auto_backup",$error_pic,$msg,30,30);
                exit;
            }
    }

    if ( $confid == 35 and  $value == 1 ) {
        if(! ini_get('allow_url_fopen') ) {
            $msg="L'activation de la géolocalisation nécessite une certaine configuration dans php.ini
            <font face='courrier'>
            <p>allow_url_fopen = On
            <br>allow_url_include = On</font>
            <p>Sur les hébergements mutualisés, on n’a pas accès à la config PHP, mais on peut au moins la compléter localement
            <br>En ajoutant un fichier php.ini avec les 2 lignes à la racine du site.
            <p align=center>
            <input type='button' class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration PHP",$error_pic,$msg,30,30);
            exit;
        }
    }

    if ( $confid == 44 and  $value == 'pbkdf2' ) {
        if (! function_exists('mcrypt_create_iv')) {
            $msg="L'utilisation de l'encryption PBKDF2 nécessite d'avoir l'extension mcrypt activée dans PHP.
            <br>Ce n'est pas le cas, seule MD5 est utilisable.
            <p align=center>
            <input type='button' class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration PHP",$error_pic,$msg,30,30);
            exit;
        }
    }
    if ( $confid == 44 and  $value == 'bcrypt' ) {
        if (! function_exists('password_hash')) {
            $msg="L'utilisation de l'encryption BCRYPT nécessite d'avoir la fonction password_hash activée dans PHP. Donc une version >= 5.5.
            <br>Ce n'est pas le cas, seule MD5 est utilisable.
            <p align=center>
            <input type='button' class='btn btn-default' value='retour' onclick='javascript:self.location.href=\"configuration.php?tab=".$tab."\";'>";
            write_msgbox("erreur configuration PHP",$error_pic,$msg,30,30);
            exit;
        }
    }

    $query="update configuration set VALUE=\"".$value."\"
            where ID=".$confid;
    $result=mysqli_query($dbc,$query);

    $query="update configuration set VALUE='1'
            where ID=-1";
    $result=mysqli_query($dbc,$query);

    if ( $confid == 2 ) {
        # config:  0=assoc ou 4=syndicat ou 3=caserne ou 2=sdis
        if ( $value == '0' ) { // assoc
            // disable gardes, syndicate, army set nbsections to 0
            $query="update configuration set VALUE='0' where ID in (2,52,29,59)";
            $result=mysqli_query($dbc,$query);
            // activate assoc
            $query="update configuration set VALUE='1' where ID = 56";
            $result=mysqli_query($dbc,$query);
        }    
        else if ( $value == '4' ) { // syndicate
            // disable gardes, assoc, sdis, army set nbsections to 0
            $query="update configuration set VALUE='0' where ID in (2,52,56,58,59)";
            $result=mysqli_query($dbc,$query);
            // activate syndicate
            $query="update configuration set VALUE='1' where ID = 29";
            $result=mysqli_query($dbc,$query);
        }
        else if ( $value == '2' ) {// sdis
            // set nbsections, externes and syndicate, army to 0
            $query="update configuration set VALUE='0' where ID in (2,29,30,56,59)";
            $result=mysqli_query($dbc,$query);
            // activate gardes and sdis
            $query="update configuration set VALUE='1' where ID in(3,52) ";
            $result=mysqli_query($dbc,$query);
        }
        else if ( $value == '5' ) {// army
            // set nbsections, externes, sdis, assoc and syndicate to 0
            $query="update configuration set VALUE='0' where ID in (2,29,30,52,56)";
            $result=mysqli_query($dbc,$query);
            // activate gardes and sdis
            $query="update configuration set VALUE='1' where ID in(59) ";
            $result=mysqli_query($dbc,$query);
        }
        else {
            // caserne pompiers, activate gardes
            $query="update configuration set VALUE='1' where ID = 3";
            $result=mysqli_query($dbc,$query);
            // disable externes and syndicate, sdis, assoc,army 
            $query="update configuration set VALUE='0' where ID in (29,30,52,56,59)";
            $result=mysqli_query($dbc,$query);
        }
    }
    # si gardes desactive, desactiver remplacements aussi
    if ( $confid == 5 ) {
        if ( $value == '0' ) {
            $query="update configuration set VALUE='0' where ID = 58";
            $result=mysqli_query($dbc,$query);
        }    
    }
    # si vehicule desactive, desactiver materiel aussi
    if ( $confid == 4 ) {
        if ( $value == '0' ) {
            $query="update configuration set VALUE='0' where ID = 18";
            $result=mysqli_query($dbc,$query);
        }    
    }
    # si materiel active, activer vehicule aussi
    if ( $confid == 18 ) {
        if ( $value == '1' ) {
            $query="update configuration set VALUE='1'
              where ID = 4";
            $result=mysqli_query($dbc,$query);
        }    
    }
    # si materiel active, activer vehicule aussi
    if ( $confid == 50 ) {
        if ( $value == '' )
            $query="update section set WEBSERVICE_KEY = null ";
        else 
             $query="update section set WEBSERVICE_KEY=md5(concat((select value from configuration where ID=50), S_ID)) where S_ID > 0";
        $result=mysqli_query($dbc,$query);
    }
    if ( $confid == 6 ) {
         $query="update section set S_CODE='".$value."'
            where S_ID=0 ";
         $result=mysqli_query($dbc,$query);
         $query="update section_flat set S_CODE='".$value."'
            where S_ID=0 ";
         $result=mysqli_query($dbc,$query);
    }
    if ( $confid == 39 ) {
         $query="update section set S_DESCRIPTION=\"".$value."\" where S_ID=0";
         $result=mysqli_query($dbc,$query);
         
         $query="update section_flat set S_DESCRIPTION=\"".$value."\" where S_ID=0";
         $result=mysqli_query($dbc,$query);
    }

    if ( $confid == 21 and $value <> "") {
        if (!is_dir($value)) {
            check_folder_permissions ( "." );
            mkdir($value, 0777);
            check_folder_permissions ( $value );
        }
        @touch($value."/index.html");
        @mkdir($value."/save", 0777);
        @touch($value."/save/index.html");
        @mkdir($value."/files", 0777);
        @touch($value."/files/index.html");
        @mkdir($value."/files_section", 0777);
        @touch($value."/files_section/index.html");
        @mkdir($value."/files_messages", 0777);
        @touch($value."/files_messages/index.html");
        @mkdir($value."/files_personnel", 0777);
        @touch($value."/files_personnel/index.html");
        @mkdir($value."/files_vehicule", 0777);
        @touch($value."/files_vehicule/index.html");
        @mkdir($value."/files_materiel", 0777);
        @touch($value."/files_materiel/index.html");
        @mkdir($value."/diplomes", 0777);
        @touch($value."/diplomes/index.html");
    }
    if ( $confid == 9 ) {
        if ( $value == 0 or $value == '4' ) {
            $query="update configuration set VALUE=null where ID=10";
            $result=mysqli_query($dbc,$query);
        }
    }
}

$query = "select ID, NAME, VALUE from configuration order by ID asc";
$result=mysqli_query($dbc,$query);
$errcode='nothing';
while ($row=@mysqli_fetch_array($result)) {
    $ID=$row["ID"];
    $NAME=$row["NAME"];
    $VALUE=$row["VALUE"];
    if (isset($_POST["f".$ID])) {
        $NEWVALUE=$_POST["f".$ID];
        $NEWVALUE=secure_input($dbc, $NEWVALUE);
        if ( $VALUE <> $NEWVALUE or $ID == 2) {
            save_config($ID, $NEWVALUE);
            //echo $ID." - ".$NAME." - ".$VALUE." - ".$NEWVALUE."<br>";
            $errcode = mysqli_errno($dbc);
        }
    }
}

echo "<body onload=\"redirect('".$tab."','".$errcode."');\">";
writefoot();
?>
