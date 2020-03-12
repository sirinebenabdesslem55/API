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


//=====================================================================
// create database tables
//=====================================================================

function load_reference_schema(){
    global $dbc, $database, $star_pic, $mytimelimit;
    @set_time_limit($mytimelimit);

    $nomenu=1;
    writehead();
    
    mysqli_query($dbc,"ALTER DATABASE ".$database." CHARACTER SET latin1 COLLATE 'latin1_swedish_ci'") or die(mysqli_error($dbc));

    $filename = "sql/reference.sql";
    $handle = fopen($filename, "r");
    $contents = fread($handle, filesize($filename));
    fclose($handle);
    // convert to unix
    $contents = str_replace("\r\n", "\n", $contents);
    $contents = str_replace("\r", "\n", $contents);
    $query = explode(";\n", $contents);
    
    for ($i=0;$i < count($query)-1;$i++) {
        mysqli_query($dbc,$query[$i]) or print mysqli_error($dbc);
    }
    echo "<div align=center><p>";
    
    $mylength=8;
    $mypass=generatePassword($mylength);
    $hash = my_create_hash($mypass);
    $query="update pompier set P_CODE='admin', P_MDP='".$hash."' where P_ID=1";
    mysqli_query($dbc,$query);
    
    write_msgbox("initialisation réussie", $star_pic, 
            "<p><font face=arial>Schéma de base de données importé avec succès!
             Vous pouvez maintenant vous connecter en utilisant le compte admin.<br>
             <b>Identifiant:</b> admin<br>
             <b>Mot de passe:</b> ".$mypass."<br>
             <span style='color:red;font-weight: bold;text-decoration:italic''>Notez bien ce mot de passe</span><br>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-default' value='Se connecter'>",10,0);
    echo "<p></div>";
    writefoot();
    feedback();

}

//=====================================================================
// Create SQL functions
//=====================================================================
function load_zipcodes($verbose=false){
    global $dbc, $star_pic, $mytimelimit;
    @set_time_limit($mytimelimit);

    $nomenu=1;
    writehead();
    
    $filename = "sql/zipcode.sql";
    $handle = fopen($filename, "r");
    $contents = fread($handle, filesize($filename));
    fclose($handle);
    // convert to unix
    $contents = str_replace("\r\n", "\n", $contents);
    $contents = str_replace("\r", "\n", $contents);
    $query = explode(";\n", $contents);
    for ($i=0;$i < count($query)-1;$i++) {
        mysqli_query($dbc,$query[$i]) or die(mysqli_error($dbc));
    }
    echo "<div align=center><p>";
    
    if ( $verbose ) 
        write_msgbox("import terminé", $star_pic, 
            "<p><font face=arial>Les code postaux ont été importés.
            <p align=center><a href='configuration.php?tab=conf2'><input type='submit' class='btn btn-default' value='Retour'>",10,0);
    echo "<p></div>";
    writefoot();
}
//=====================================================================
// Create SQL functions
//=====================================================================
function create_sql_functions(){
global $dbc;

$query="DROP FUNCTION IF EXISTS CAP_FIRST";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="CREATE FUNCTION CAP_FIRST (INPUT VARCHAR(255))
RETURNS VARCHAR(255) CHARSET latin1
DETERMINISTIC
BEGIN
    DECLARE len INT;
    DECLARE i INT;
    SET len   = CHAR_LENGTH(INPUT);
    SET INPUT = LOWER(INPUT);
    SET i = 0;
    WHILE (i < len) DO
        IF (MID(INPUT,i,1) in (' ','-') OR i = 0) THEN
            IF (i < len) THEN
                SET INPUT = CONCAT(
                    LEFT(INPUT,i),
                    UPPER(MID(INPUT,i + 1,1)),
                    RIGHT(INPUT,len - i - 1)
                );
            END IF;
        END IF;
        SET i = i + 1;
    END WHILE;
    RETURN INPUT;
END;";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="DROP FUNCTION IF EXISTS DEP_DISPLAY";
mysqli_query($dbc,$query);

$query="CREATE FUNCTION DEP_DISPLAY (DCODE VARCHAR(25), DDESC VARCHAR(50))
RETURNS VARCHAR(75) CHARSET latin1
DETERMINISTIC
BEGIN
    DECLARE DEPNUM VARCHAR(25);
    SET DEPNUM = SUBSTRING_INDEX(DCODE, ' ', 1);
    IF ( DEPNUM REGEXP '[0-9]' ) THEN
        SET DDESC = CONCAT(DEPNUM,' ',DDESC);
    END IF;
    RETURN DDESC;
END;";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="DROP FUNCTION IF EXISTS ANTENA_DISPLAY";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

$query="CREATE FUNCTION ANTENA_DISPLAY (ACODE VARCHAR(25))
RETURNS VARCHAR(25) CHARSET latin1
DETERMINISTIC
BEGIN
    DECLARE L INT;
    DECLARE DEPNUM VARCHAR(25);
    SET L = 1;
    SET DEPNUM = SUBSTRING_INDEX(ACODE, ' ', 1);
    IF ( DEPNUM REGEXP '[0-9]' ) THEN
        SET L = 2 + CHAR_LENGTH(DEPNUM);
    END IF;
    SET ACODE = MID(ACODE,L,25);
    RETURN ACODE;
END;";
mysqli_query($dbc,$query) or die(mysqli_error($dbc));

}

//=====================================================================
// feedback
//=====================================================================
  
function feedback() {
    global $dbc;
    $nb_users=count_entities('pompier');
    $message = "Il y a ".$nb_users." personnes enregistrées.\n";
    $query="select id, name, value from configuration where id not in (11,21,50,57) order by id "; 
    $result=mysqli_query($dbc,$query);
    while ( $row=mysqli_fetch_array($result) ) {
        $message .= $row["name"]." : ".$row["value"]."\n";
    }
    $subject =  "Feedback from ".$_SERVER['SERVER_NAME'];
    mysendmail2("feedback@ebrigade.net","$subject","<pre>".$message."</pre>","feedback","donotreply");
}
  
//=====================================================================
// upgrade database
//=====================================================================

function upgrade_database($dbversion,$version){
    global $star_pic, $error_pic, $database, $nbsections, $dbc, $nomenu;

    $nomenu=1;
    writehead();
    
    $filename='sql/upgrade_'.$dbversion.'_'.$version.'.sql';
    $logname ='sql/upgrade_log_'.$dbversion.'_'.$version.'.txt'; 
    $myupgrade = 'sql/upgrade_generated_'.$dbversion.'_'.$version.'.txt';
    
    $tmpdbversion=$dbversion;
    $no_start=true;
    $no_end=true;
   
    $path='./sql';
    $sqldir = opendir($path);

    if (! @is_file($filename)) {
        if ( @is_file($myupgrade)) unlink($myupgrade);
        $fh = fopen($myupgrade, 'w');
        $i = 0;
        while ($f1 = readdir($sqldir)){
            if ($f1 != "." && $f1 != ".." && $f1 != "reference.sql" && $f1 != "functions.sql" && $f1 != "migration" && $f1 != "zipcode.sql") {
                if (!is_dir($path.$f1)) {
                    $path_parts = pathinfo("$f1");
                    if ( $path_parts["extension"] == "sql" ) {
                        $filearray[$i] = $f1;
                        $i++;
                    }
                }
            }
        }
        sort($filearray);

        for ($i=0; $i<sizeof($filearray); $i++){
            $f1 = $filearray[$i];
            $start = get_file_from_version($f1);
            $end = get_file_to_version($f1);
            if ( $tmpdbversion == $start ) {
                $no_start=false;
                $tmpdbversion = $end;
                $file=fread(fopen($path.'/'.$f1, "rb"), 10485760);
                $query=explode(";",$file);
                for ($k=0;$k < count($query)-1; $k++) {
                   fwrite($fh, $query[$k].';');
                }
                fwrite($fh, $query[$k].'
');
            }
            if ( $version == $end) $no_end=false;
        }
        fclose($fh);
        closedir($sqldir);
        if ( $no_start || $no_end ) unlink($myupgrade);
    }
    $upgerr=0;
    if ((! @is_file($filename)) && (@is_file($myupgrade)))
        $filename = $myupgrade;
    if (is_file($filename)) {
        if ( @is_file($logname)) unlink($logname);
        $fh = fopen($logname, 'w');
        fwrite($fh,"upgrade de la base ".$database." de la version ".$dbversion." vers ".$version."\r\n");
        fwrite($fh, "START :".date("D M j G:i:s T Y")."\r\n"); 
        @set_time_limit($mytimelimit);
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        // convert to unix
        $contents = str_replace("\r\n", "\n", $contents);
        $contents = str_replace("\r", "\n", $contents);
        $query = explode(";\n", $contents);

        for ($i=0; $i < count($query)-1; $i++) {
            fwrite($fh, $query[$i]."\r\n"); 
            if (! mysqli_query($dbc,$query[$i])) {
                fwrite($fh, "***********************************"."\r\n"."ERROR - ".mysqli_error($dbc)."\r\n"."***********************************"."\r\n");
                $upgerr=1;
            }
            else if ( mysqli_affected_rows($dbc) <> 0 )
                fwrite($fh,"--> Lignes modifiées : ".mysqli_affected_rows($dbc)."\r\n");
        }
        migrate_data($dbversion, $version, $fh);
        fwrite($fh, "END :".date("D M j G:i:s T Y")."\r\n"); 
        fclose($fh);
        echo "<p>";

        if ( $upgerr == 0 ) 
            write_msgbox("upgrade réussi", $star_pic, 
            "<p><font face=arial>La base de données à été upgradée<br> 
            de la version <b>$dbversion</b><br>
            à la version <b>$version</b><br>
            sans erreurs. <a href=$logname target=_blank>voir le log d'upgrade</a><br>
            <b>Pensez à purger le cache du navigateur (CTRL + F5)</b>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-default' value='Se connecter'>",10,0);
        else 
            write_msgbox("erreur sql", $error_pic, 
            "<p><font face=arial>L'upgrade de la base de données <br> 
            de la version <b>$dbversion</b><br>
            à la version <b>$version</b><br>
            à généré des erreurs. 
            <a href=$logname target=_blank>voir le log d'upgrade</a><br>
            Corrigez les erreurs rencontrées dans la base de données
            avant de vous connecter.<br>
            <b>Pensez aussi à purger le cache du navigateur (CTRL + F5)</b>
            <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-default' value='Se connecter'>",10,0);
        feedback();
    }
    else {
        write_msgbox("version des composants incompatible", $error_pic, 
        "<p><font face=arial>La base de données est incompatible avec le code de l'application web<br> 
         version de la base de données:<b>$dbversion</b><br>
         version de l'application web:<b>$version</b><br>
         Vous devez manuellement exécuter les fichiers d'upgrade sur la base(voir répertoire sql)<br>
        <p align=center><a href=index.php target=_top><input type='submit' class='btn btn-default' value='Se connecter'>",10,0);
    }
    echo "</div><p>";
    writefoot();
}


//=====================================================================
// Migration of data
//=====================================================================
function migrate_data($version_start, $version_end, $logfilehandle) {
    global $gardes, $nbsections;
    $major_version_start=substr($version_start,0,1);
    if ( $version_start == '3.0' or $major_version_start < 3 ) {
        if ( $gardes == 1 ) migrate_tableau_garde_3_1($logfilehandle);
        drop_obsolete_tables_3_1($logfilehandle);
    }
}

//=====================================================================
// specific migration functions
//=====================================================================

function migrate_tableau_garde_3_1($logfilehandle) {
    global $mytimelimit, $gardes, $dbc;
    // Ce script vi migrer les donnees de planning_garde dans les tables evenemenents
    // seuls les 300 dernières gardes sont migrées.
    // Si besoin de plus changer cette constante
    @set_time_limit($mytimelimit);
    $maxevents=300;

    fwrite($logfilehandle,"Migration tableau de garde \r\n");
    fwrite($logfilehandle, "START :".date("D M j G:i:s T Y")."\r\n"); 
    fwrite($logfilehandle," 1 - Nettoyage des vieux postes de garde \r\n");
    $query="delete type_participation where TE_CODE='GAR' and EQ_ID=0";
    $result=mysqli_query($dbc,$query);

    $query="update evenement_participation set TP_ID=0
            where E_CODE in ( select E_CODE from evenement where TE_CODE='GAR' and E_EQUIPE=0)";
    $result=mysqli_query($dbc,$query);
    
    fwrite($logfilehandle," 2 - Migration des données du tableau de garde \r\n");

    $query="select pg.S_ID, pg.PG_DATE, pg.TYPE, pg.PS_ID, pg.EQ_ID, pg.P_ID, pg.PG_STATUT from 
        planning_garde pg, pompier p
        where pg.P_ID = p.P_ID
        order by pg.EQ_ID, pg.PG_DATE desc, pg.TYPE";
    $result=mysqli_query($dbc,$query);

    $i=0;
    $E_CODE=0;
    while ( $row=mysqli_fetch_array($result) and $i < $maxevents ) {
        $S_ID=intval($row["S_ID"]);
        $PG_DATE=$row["PG_DATE"];
        $TYPE=$row["TYPE"];
        $PS_ID=$row["PS_ID"];
        $EQ_ID=$row["EQ_ID"];
        $P_ID=$row["P_ID"];
        $PG_STATUT=$row["PG_STATUT"];
    
        $query_test="select count(1) from evenement e, evenement_horaire eh 
                where e.TE_CODE='GAR'
                and S_ID=".$S_ID."
                and eh.E_CODE = e.E_CODE
                and eh.EH_ID=1
                and eh.EH_DATE_DEBUT='".$PG_DATE."'";
        $result_test=mysqli_query($dbc,$query_test);
        $row_test=@mysqli_fetch_array($result_test);
    
        // creation evenement?
        if ( $row_test[0] == 0 ) {
            $i++;
            $query2="select EQ_NOM, EQ_JOUR, EQ_NUIT , EQ_DEBUT1, EQ_FIN1, EQ_DUREE1, EQ_DEBUT2, EQ_FIN2, EQ_DUREE2, EQ_PERSONNEL1, EQ_PERSONNEL2
                from equipe where EQ_ID=".$EQ_ID;
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $EQ_NOM=$row2["EQ_NOM"];
            $EQ_JOUR=intval($row2["EQ_JOUR"]);
            $EQ_NUIT=intval($row2["EQ_NUIT"]);
            $EQ_DEBUT1=$row2["EQ_DEBUT1"];
            $EQ_DEBUT2=$row2["EQ_DEBUT2"];
            $EQ_FIN1=$row2["EQ_FIN1"];
            $EQ_FIN2=$row2["EQ_FIN2"];
            $EQ_DUREE1=$row2["EQ_DUREE1"];
            $EQ_DUREE2=$row2["EQ_DUREE2"];
            $EQ_PERSONNEL1=$row2["EQ_PERSONNEL1"];
            $EQ_PERSONNEL2=$row2["EQ_PERSONNEL2"];
            $nbparties=$EQ_JOUR + $EQ_NUIT;
    
            $E_CODE = generate_evenement_number();
            fwrite($logfilehandle," - Conversion garde ".$PG_DATE." en evenement ".$E_CODE."\r\n");
            $queryk="insert into evenement (E_CODE, TE_CODE, S_ID, E_LIBELLE, E_LIEU, E_OPEN_TO_EXT, E_CREATE_DATE,  E_PARTIES, E_EQUIPE, E_CLOSED, E_NB)
                                values( ".$E_CODE.", 'GAR', ".$S_ID.", \"".$EQ_NOM."\", 'CIS', 0, NOW(), ".$nbparties.", ".$EQ_ID.", 0, ".$EQ_PERSONNEL1.")";
            mysqli_query($dbc,$queryk);
        
            if ( $EQ_JOUR == 1 ) {
                $EH_ID=1;
                $queryk="insert into evenement_horaire ( E_CODE, EH_ID, EH_DATE_DEBUT, EH_DATE_FIN, EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION)
                    select ".$E_CODE.", ".$EH_ID.", '".$PG_DATE."', '".$PG_DATE."','".$EQ_DEBUT1."','".$EQ_FIN1."','".$EQ_DUREE1."', 'Jour'";
                mysqli_query($dbc,$queryk);
                // inscrire véhicules
                $queryk="insert into evenement_vehicule (E_CODE, EH_ID, V_ID)
                    select ".$E_CODE.",".$EH_ID.", V_ID
                    from vehicule where VP_ID='OP' and EQ_ID = ".$EQ_ID;
                mysqli_query($dbc,$queryk);
            }
            if ( $EQ_NUIT == 1 ) {
                $EH_ID=2;
                $queryk="insert into evenement_horaire ( E_CODE, EH_ID, EH_DATE_DEBUT, EH_DATE_FIN, EH_DEBUT, EH_FIN, EH_DUREE, EH_DESCRIPTION)
                    select ".$E_CODE.", ".$EH_ID.", '".$PG_DATE."', DATE_ADD('".$PG_DATE."', INTERVAL 1 DAY),'".$EQ_DEBUT2."','".$EQ_FIN2."','".$EQ_DUREE2."','Nuit'";
                mysqli_query($dbc,$queryk);
                // inscrire véhicules
                $queryk="insert into evenement_vehicule (E_CODE, EH_ID, V_ID)
                    select ".$E_CODE.",".$EH_ID.", V_ID
                    from vehicule where VP_ID='OP' and EQ_ID = ".$EQ_ID;
                mysqli_query($dbc,$queryk);
            }
        }
    
        // inscrire personnel
        if ( $E_CODE > 0 ) {
            if ( $TYPE == 'J' ) $EH_ID=1;
            else $EH_ID=2;    
            if ( $PG_STATUT == 1 ) $EP_FLAG1=1;
            else $EP_FLAG1=0;
    
            $queryk="insert into evenement_participation (E_CODE, EH_ID, P_ID, TP_ID, EP_FLAG1, EP_DATE)
                select ".$E_CODE.", ".$EH_ID.", ".$P_ID.", 0, ".$EP_FLAG1.", NOW()";
            mysqli_query($dbc,$queryk);
        }
    }
}

function drop_obsolete_tables_3_1($logfilehandle) {
    global $dbc;
    fwrite($logfilehandle,"Suppression des tables obsoletes \r\n");
    $query="drop table planning_garde";
    $result=mysqli_query($dbc,$query);
    
    $query="drop table priorite";
    $result=mysqli_query($dbc,$query);
}

?>
