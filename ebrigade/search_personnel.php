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

include("config.php");
check_all(0);
$id=$_SESSION['id'];
check_all(56);
if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else $tab = 1;
get_session_parameters();
writehead();
forceReloadJS('js/search.js');
if ( $tab == 6 ) forceReloadJS('js/search_habilitation.js'); 
else forceReloadJS('js/search_personnel.js');
echo "</head>";
echo "<body>";
if ( $syndicate == 1 ) $t="Recherche dans la base des adhérents";
else $t="Recherche de personnel";
echo "<div><table class='noBorder'><tr><td><i class='fa fa-search fa-3x'></i></td>
     <td><font size=4><b>".$t."</b> </font> <a href='#' onclick='impression();'><i class='fa fa-print fa-2x' ></i></a></td></tr></table>";

function print_choix_section() {
    global $myothercolor,$my2darkcolor,$my2lightcolor,$mylightcolor,$id,$nbmaxlevels,$filter,$sectionorder,$nbsections ;
    
    if ( $nbsections > 0 ) {
        echo "<input type='hidden' id='choixSection' name='choixSection' value='0'>";
        return;
    }
    echo "<tr><td> section </td><td><select id='choixSection' name='choixSection'>";
    if ( check_rights($id, 40)) $local_only=false;
    else $local_only=true;
    if ( $local_only ) {
        $highestsection=get_highest_section_where_granted($id,56);
        $level=get_level($highestsection);
        if ( $level == 0 ) $mycolor=$myothercolor;
        elseif ( $level == 1 ) $mycolor=$my2darkcolor;
        elseif ( $level == 2 ) $mycolor=$my2lightcolor;
        elseif ( $level == 3 ) $mycolor=$mylightcolor;
        else $mycolor='white';
        $class="style='background: $mycolor;'";
        
        echo "<option value='$highestsection' $class >".
                get_section_code($highestsection)." - ".get_section_name($highestsection)."</option>";
            display_children2($highestsection, $level +1, $filter, $nbmaxlevels);
    }
    else
        display_children2(-1, 0, $filter, $nbmaxlevels,$sectionorder);
    echo "</select> 
        </td></tr>";
}

// =======================================================
// tabs
// =======================================================
echo "<p><ul class='nav nav-tabs  noprint' id='myTab' role='tablist'>";
if ( $syndicate == 1 ) $i="Numéro d'adhérent";
else $i="Numéro";
if ( $tab == 1 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=1' title='Par NOM ou $i' role='tab' aria-controls='tab1' href='#tab1' >
    <i class='fa fa-user'></i> Par NOM ou $i</a></li>";
    
if ( $tab == 2 ) $class='active'; else $class='';
if ( $syndicate == 0 )
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=2' title='Par Ville ou Département' role='tab' aria-controls='tab2' href='#tab2' >
    <i class='fa fa-home'></i> Par Ville ou Département</a></li>";
if ( $tab == 3 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=3' title='Par e-mail' role='tab' aria-controls='tab3' href='#tab3' >
    <i class='fa fa-at'></i> Par e-mail</a></li>";
if ( $tab == 4 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=4' title='Par téléphone' role='tab' aria-controls='tab4' href='#tab4' >
    <i class='fa fa-phone'></i> Par Téléphone</a></li>";

if ( $tab == 5 ) $class='active'; else $class='';
if ( $competences == 1 )
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=5' title='Par compétences' role='tab' aria-controls='tab5' href='#tab5' >
    <i class='fa fa-certificate'></i> Par Compétences</a></li>";

if ( $tab == 6 ) $class='active'; else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=6' title='Par habilitations' role='tab' aria-controls='tab6' href='#tab6' >
    <i class='fa fa-certificate'></i> Par Habilitations</a></li>";

if ( $tab == 7 ) $class='active'; else $class='';
if ( $bank_accounts == 1 and check_rights($id, 53))
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='search_personnel.php?tab=7' title='Par Compte bancaire' role='tab' aria-controls='tab7' href='#tab7' >
    <i class='fa fa-money'></i> Par Compte bancaire</a></li>";
echo "</ul>
<p>";

// =======================================================
// nom
// =======================================================
if ( $tab == 1 ) {
    // choix section 
    echo "<table class='noBorder'><tr>";
    print_choix_section();
    echo "</table><p>";
    $c=$application_title;
    $frm = "Tapez les premières lettres du nom de famille ou Numéro $c</p>";
    $frm .= "<input type='text' name='trouve' id='trouveNom' value='' autofocus='autofocus'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='nom'><p>";
    echo $frm;
}
// =======================================================
// ville
// =======================================================
if ( $tab == 2 ) {
    $frm = "Tapez les premiers lettre de la ville ou chiffres du code postal</p>";
    $frm .= "<input type='text' name='trouve' id='trouveVille' value='' autofocus='autofocus'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='ville'><p>";
    echo $frm;
}
// =======================================================
// mail
// =======================================================
if ( $tab == 3 ) {
    $frm = "Tapez l'adresse e-mail, au moins le 4 premiers caractères</p>";
    $frm .= "<input type='text' name='trouve' id='trouveMail' value='' autofocus='autofocus'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='mail'><p>";
    echo $frm; 
}
// =======================================================
// tel
// =======================================================
if ( $tab == 4 ) {
    $frm = "Tapez les premiers chiffres du numéro de téléphone</p>";
    $frm .= "<input type='text' name='trouve' id='trouveTel' value='' autofocus='autofocus'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='tel'><p>";
    echo $frm;
}

// =======================================================
// poste
// =======================================================
if ( $tab == 5 ) {
    // choix section 
    echo "<table class='noBorder'><tr>";
    print_choix_section();
        
    // choix statut 
    if ( $syndicate == 0 ) {
        $sql="select S_STATUT, S_DESCRIPTION from statut";
        if ( $sdis  ) $sql .= " where S_CONTEXT = 3";
        else $sql .= " where S_CONTEXT =".$nbsections;
        if ( $army ) $sql .= " and S_STATUT not in ('BEN','SAL','ADH','FONC')";
        else if ( $syndicate ) $sql .= " and S_STATUT not in ('BEN','ACT','RES','CIV')";
        else $sql .= " and S_STATUT not in ('ADH','FONC','ACT','RES','CIV')";
        if ( $externes == 0 ) $sql .= " and S_STATUT <> 'EXT'";
        $sql .= " order by S_DESCRIPTION";
        $res = mysqli_query($dbc,$sql);

        $choixStatut = (isset($_POST['choixStatut'])?$_POST['choixStatut']:"ALL");
        
        echo "<tr><td> statut </td><td><select id='choixStatut' name='choixStatut' onchange=''>
           <option value='ALL' ".(($choixStatut=="ALL")?" selected":"").">Tous</option>";
        if ( $assoc ) {
            if ( $choixStatut == 'BENSAL' ) $selected = 'selected';
            else $selected = '';
            echo "<option value='BENSAL' $selected>Personnel bénévole et salarié</option>";
        }
        while($row=mysqli_fetch_array($res)) {
            $s=$row[0];
            $d=$row[1];
            echo "<option value=\"".$s."\" ".(($choixStatut==$s)?" selected":"").">".$d."</option>";
        }
        echo "</select> 
        </td></tr>";
    }
    // toutes /au moins 1 
    $CurTri = (isset($_POST['typetri'])?$_POST['typetri']:"et");
    echo  "<td> avec </td>
        <td><select name='typeTri' id='typeTri' onchange=''>
          <option value='et' ".(($CurTri=="ET")?" selected":"").">Toutes les compétences</option>
          <option value='ou'".(($CurTri=="OU")?" selected":"").">au moins une des compétences sélectionnées</option>
          </select>
        </td></tr>";    
    echo "</table>";

    // compétences 
    $sql = "select e.eq_nom, p.eq_id, p.ps_id, type, p.description 
    from poste p, equipe e
    where e.eq_id = p.eq_id
    order by p.eq_id, p.type";
    $res = mysqli_query($dbc,$sql);
    if (mysqli_num_rows($res)>0){
            $curEq="-1";
            echo "<table class='noBorder'>\n";
            while($row=mysqli_fetch_array($res)){
                if($curEq!=$row['eq_id']){
                    if ( $curEq > 0) echo "</td></tr>";
                    echo "<tr><td width=200><b>";
                    echo $row['eq_nom'];
                    echo "</b></td><td>";
                    $curEq=$row['eq_id'];
                    $nb=1;
                }
                else $nb++;
                if ( $nb%24 == 0 )echo "<br>";
                echo "\n<input type='checkbox' 
                name='qualif' 
                value='".$row['ps_id']."' 
                id='cb".$row['ps_id']."' 
                title=\"".$row['description']."\" ".(in_array($row['ps_id'],(isset($_GET['qualif'])?array($_GET['qualif']):array()))?" checked":"")."/>
                <label 
                for='cb".$row['ps_id']."' 
                title=\"".$row['description']."\"
                id='cb".$row['ps_id']."' 
                ><small>".$row['type']."</small></label>";
            }
            echo "</td></tr></table><p>";
    }
    else {
            echo "Aucune qualification";
    }
}

// =======================================================
// habilitation
// =======================================================
if ( $tab == 6 ) {
    echo "<script type='text/javascript' src='js/search_habilitation.js'></script>";
    
    // choix section 
    echo "<table class='noBorder'><tr>";
    print_choix_section();

    // habilitations 
    $sql1 = "select gp_id, gp_description 
           from groupe where gp_id < 100 order by gp_id ";
    $res1 = mysqli_query($dbc,$sql1);

    $sql2 = "select gp_id, gp_description 
           from groupe where gp_id >= 100 order by gp_id ";
    $res2 = mysqli_query($dbc,$sql2);

    $nb=0;
    if (mysqli_num_rows($res1)>0){
            echo "<table class='noBorder' width=1000>";
            echo "<tr><td><span class='badge'>Droits d'accès</span></td></tr>"; 
            echo "\n"."<tr><td>\n";
            while($row=mysqli_fetch_array($res1)){
                $nb++;
                if ( $nb%7 == 0 )echo "<br>";
                echo "\n <input type='radio' 
                name='habilitation' 
                value='".$row['gp_id']."' 
                id='r".$row['gp_id']."' 
                title=\"".$row['gp_description']."\" ".(in_array($row['gp_id'],(isset($_GET['habilitation'])?array($_GET['habilitation']):array()))?" checked":"")."/>
                <label for='r".$row['gp_id']."'>".$row['gp_description']." </label>";
            }
            echo "</td></tr></table>\n";
    }
    $nb=0;
    if (mysqli_num_rows($res2)>0){
            echo "<table class='noBorder' width=1000>";
            echo "<tr><td><span class='badge'>Rôles de l'organigramme</span></td></tr>"; 
            echo "\n"."<tr><td >\n";
            while($row=mysqli_fetch_array($res2)){            
                $nb++;
                if ( $row['gp_description'] == 'Président (e)') $gp_description = "Président / Responsable d'antenne";
                else $gp_description=$row['gp_description'];
                if ( $nb%7 == 0 )echo "<br>";
                echo "\n <input type='radio' 
                name='habilitation' 
                value='".$row['gp_id']."'
                id='r".$row['gp_id']."' 
                title=\"".$gp_description."\" ".(in_array($row['gp_id'],(isset($_GET['habilitation'])?array($_GET['habilitation']):array()))?" checked":"")."/>
                <label for='r".$row['gp_id']."'>".$gp_description." </label>";
            }
            echo "</td></tr></table><p>\n";
    }

    if ((mysqli_num_rows($res1)==0) and (mysqli_num_rows($res2)==0)) {
            echo "Aucune habilitation ou rôles trouvés";
    }
}

// =======================================================
// comptes
// =======================================================
if ( $tab == 7 ) {
    $frm .= "Tapez les premières lettres et chiffres du numéro du compte bancaire IBAN (exemple FR7620)</p>";
    $frm .= "<input type='text' name='trouve' id='trouveCpt' value='' autofocus='autofocus'>";
    $frm .= "<input type='hidden' name='typetri' id='typetri' value='compte'><p>";
    echo $frm;
}
// =======================================================
// result
// =======================================================
echo "<div id='export'></div>
</div>";
writefoot();
?>
