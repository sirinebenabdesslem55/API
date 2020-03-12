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
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

// sur un evenement 
if (isset ($_POST["evenement"])) 
    $evenement=intval($_POST["evenement"]);
else if (isset ($_GET["evenement"]))
    $evenement=intval($_GET["evenement"]);
else $evenement = 0;

// ou sur un type de garde
if (isset ($_POST["garde"])) 
    $garde=intval($_POST["garde"]);
else if (isset ($_GET["garde"]))
    $garde=intval($_GET["garde"]);
else $garde = 0;

if (isset ($_POST["partie"])) $partie=intval($_POST["partie"]);
else if (isset ($_GET["partie"]))
    $partie=intval($_GET["partie"]);
else $partie=1;

writehead();

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
</STYLE>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript'>

function updateGlobal(newvalue,field)
{   
    var nv = parseInt(newvalue);
    var cur = parseInt(field.value);
    if ( nv > cur) field.value = nv;
    return true;
}

function redirect(evenement) {
    url="evenement_display.php?tab=1&evenement="+evenement;
    self.location.href=url;
    return true; 
}

function redirect_garde(garde) {
    url="upd_type_garde.php?eqid="+garde;
    self.location.href=url;
    return true; 
}

<?php
echo "</script>";
echo "</head>";
echo "<body class='top30'>";

//=====================================================================
// recup�rer infos evenement ou garde
//=====================================================================
if ( $evenement > 0 ) {
    check_all(41);
    $query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON
            from evenement e, type_evenement te
            where te.TE_CODE = e.TE_CODE
            and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);

    if ( ! is_chef_evenement($id, $evenement) ) {
        check_all(15);
        if (! check_rights($id, 15, "$S_ID")) check_all(24);
    }
    
    if ( $partie > 1 ) $E_LIBELLE .= ", partie n�".$partie;
    
    echo "<div align=center><table class='noBorder'>
          <tr><td>
          <font size=4><b><img src=images/evenements/".$TE_ICON." height=30> ".$E_LIBELLE."</b></font></td></tr>
          </table>";
}

if ( $garde > 0  ) {
    check_all(5);
    $query="select EQ_ID, EQ_JOUR, EQ_NUIT , EQ_NOM, S_ID, ASSURE_PAR1, ASSURE_PAR2, ASSURE_PAR_DATE,
        EQ_DUREE1, EQ_DUREE2, EQ_REGIME_TRAVAIL,
        TIME_FORMAT(EQ_DEBUT1, '%k:%i') EQ_DEBUT1,
        TIME_FORMAT(EQ_DEBUT2, '%k:%i') EQ_DEBUT2,
        TIME_FORMAT(EQ_FIN1, '%k:%i') EQ_FIN1,
        TIME_FORMAT(EQ_FIN2, '%k:%i') EQ_FIN2,
        EQ_PERSONNEL1,EQ_PERSONNEL2, EQ_VEHICULES, EQ_SPP, EQ_ICON, EQ_ADDRESS
        from type_garde
        where EQ_ID=".$garde;
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    
    if ( $sdis == 1 ) {
        if (! check_rights($id, 5, "$S_ID")) 
            check_all(24);
    }
    
    if ( $partie > 1 ) $EQ_NOM .= " partie n�".$partie;
    echo "<div align=center><table class='noBorder'>
          <tr><td>
          <font size=4><b><img src=".$EQ_ICON." height=30> ".$EQ_NOM."</b></font></td></tr>
          </table>";

}

echo "<form name='evenement_competence' method='post' action='evenement_competences.php'>";
echo "<p><table cellspacing=0 border=0>";

//=====================================================================
// sauver informations globales ou nouvelles
//=====================================================================
if (isset($_POST["new_competence"])) {
    $nc=intval($_POST["new_competence"]);
    if ( $nc > 0) {
        if ( $evenement > 0 ) 
            $query="insert into evenement_competences(E_CODE, EH_ID, PS_ID, NB)
            values (".$evenement.",".$partie.",".$nc.",".intval($_POST["new_nb"]).")";
        if ( $garde > 0 ) 
            $query="insert into garde_competences(EQ_ID, EH_ID, PS_ID, NB)
            values (".$garde.",".$partie.",".$nc.",".intval($_POST["new_nb"]).")";
            
        $result=mysqli_query($dbc,$query);
    }
}

if (isset($_POST["global"])) {
    $query="insert into evenement_competences(E_CODE, EH_ID, PS_ID, NB)
            values (".$evenement.",".$partie.",'0',".intval($_POST["global"]).")
            where not exists (select 1 from evenement_competences t
                        where t.PS_ID=0
                        and t.E_CODE=".$evenement."
                        and t.EH_ID=".$partie.")";
    $result=mysqli_query($dbc,$query);
    $query="update evenement_competences set NB=".intval($_POST["global"])."
            where E_CODE=".$evenement."
            and EH_ID=".$partie."
            and PS_ID=0";
    $result=mysqli_query($dbc,$query);
    $query="update evenement set E_NB=(select max(NB) from evenement_competences where E_CODE=".$evenement." and PS_ID=0)
            where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// afficher  comp�tences
//=====================================================================

if ( $evenement > 0 ) 
    $querym="select ec.EH_ID, ec.PS_ID 'poste', ec.NB 'nb', p.TYPE 'type', p.DESCRIPTION 'desc', p.EQ_ID 
        from evenement_competences ec
        left join poste p on ec.PS_ID = p.PS_ID
        where ec.E_CODE=".$evenement."
        and ec.EH_ID=".$partie."
        order by ec.EH_ID, p.EQ_ID, p.TYPE";
else     // type_garde
    $querym="select gc.EH_ID, gc.PS_ID 'poste', gc.NB 'nb', p.TYPE 'type', p.DESCRIPTION 'desc', p.EQ_ID 
        from garde_competences gc
        left join poste p on gc.PS_ID = p.PS_ID
        where gc.EQ_ID=".$garde."
        and gc.EH_ID=".$partie."
        order by gc.EH_ID, p.EQ_ID, p.TYPE";    
$resultm=mysqli_query($dbc,$querym);
$nbrows=mysqli_num_rows($resultm);

if ( $nbrows > 0 ) {
    echo  "<tr class=TabHeader>
        <td>Nombre de personnes requises</td>";
    if ( $evenement > 0 ) 
        echo "<td align=center>Inscrits</td>";
    echo "<td align=center>Demand�s</td></tr>";
}
while ( custom_fetch_array($resultm) ) {
    // GLOBAL - seulement pour evenements
    if ( $poste == 0 ) {
        $inscrits=get_nb_competences($evenement,$partie);
        if ( $nb == 0 ) $pic="<i class='fa fa-check-circle fa-lg' style='color:green;' title='Pas de limite sur le nombre de personnel inscrit'></i>";
        else if ( $inscrits > $nb ) {
            if ( $TE_CODE == 'GAR' ) $pic="<i class='fa fa-check-circle fa-lg' style='color:orange;' title='Trop de personnel inscrit'></i>";
            else $pic="<i class='fa fa-check-circle fa-lg' style='color:blue;' title='Plus de personnel inscrit que n�cessaire '></i>";
        }
        else if ( $inscrits == $nb ) $pic="<i class='fa fa-check-circle fa-lg' style='color:green;' title='Nombre suffisant de personnel inscrit'></i>";
        else $pic="<i class='fa fa-exclamation-circle fa-lg' style='color:red;' title='Pas assez de personnel inscrit'></i>";
        echo  "<tr bgcolor=$mylightcolor>
        <td ><b>Nombre total</b></td>";
        if ( $evenement > 0 ) 
            echo "<td align=center><b>".$inscrits."</b> ".$pic."</td>";
        echo "<td align=center>
        <input name='global'
            type=text 
            title='Nombre global de personnes' 
            size=1 
            value='$nb' 
            onchange='checkNumber(form.global,\"$nb\");'
        >
       </td></tr>";
    }
    // DETAIL PAR COMPETENCE
    else {
        if ( isset($_POST["P".$poste])){
            $nb=intval($_POST["P".$poste]);
            if ($nb == 0) {
                if ( $garde > 0 )
                    $query="delete from garde_competences
                    where PS_ID=".$poste."  and EQ_ID=".$garde." and EH_ID=".$partie;
                else if ( $evenement > 0 ) 
                    $query="delete from evenement_competences
                    where PS_ID=".$poste." and E_CODE=".$evenement." and EH_ID=".$partie;                   
                $result=mysqli_query($dbc,$query);
            }
            else {
                if ( $garde > 0 ) {
                    $query="insert into garde_competences(EQ_ID, EH_ID, PS_ID, NB)
                    values (".$garde.",".$partie.",'0',".$nb.")
                    where not exists (select 1 from garde_competences t
                            where t.PS_ID=".$poste."
                            and t.EQ_ID=".$garde."
                            and t.EH_ID=".$partie.")";
                    $result=mysqli_query($dbc,$query);
                    $query="update garde_competences set NB=".$nb."
                        where EQ_ID=".$garde."
                        and EH_ID=".$partie."
                        and PS_ID=".$poste;
                    $result=mysqli_query($dbc,$query);                    
                }
                else if ( $evenement > 0 )  {
                    $query="insert into evenement_competences(E_CODE, EH_ID, PS_ID, NB)
                    values (".$evenement.",".$partie.",'0',".$nb.")
                    where not exists (select 1 from evenement_competences t
                            where t.PS_ID=".$poste."
                            and t.E_CODE=".$evenement."
                            and t.EH_ID=".$partie.")";
                    $result=mysqli_query($dbc,$query);
                    $query="update evenement_competences set NB=".$nb."
                        where E_CODE=".$evenement."
                        and EH_ID=".$partie."
                        and PS_ID=".$poste;
                    $result=mysqli_query($dbc,$query);
                }
            }
        }
        if ( $nb > 0 ) {
            if ( $evenement > 0 ) {
                $inscrits=get_nb_competences($evenement,$partie,$poste);
                if ( $inscrits > $nb + 2 ) $pic="<i class='fa fa-check-circle fa-lg' style='color:blue;' title='Plus de personnel inscrit que n�cessaire pour cette comp�tence'></i>";
                else if ( $inscrits >= $nb ) $pic="<i class='fa fa-check-circle fa-lg' style='color:green;' title='Nombre suffisant de personnel inscrit pour cette comp�tence'></i>";
                else $pic="<i class='fa fa-exclamation-circle fa-lg' style='color:red;'  title='Pas assez de personnel inscrit pour cette comp�tence'></i>";
            }
            else {
                $inscrits="";
                $pic="";
            }
            echo  "<tr bgcolor=$mylightcolor><td ><div style='margin-left:5px;'> ".$type." - ".$desc."</div></td>";
            if ( $evenement > 0 ) 
                echo "<td align=center><b>".$inscrits."</b> ".$pic."</td>";
            echo "<td align=center>
            <input name='P".$poste."'
                type=text 
                title='Nombre requis' 
                size=1 
                value='$nb' 
                onchange='checkNumber(form.P".$poste.",\"$nb\");updateGlobal(form.P".$poste.".value,form.global);'
            >
            </td></tr>";
        }
    }
}

if ( $competences == 1 ) {
    if ($evenement > 0 ) $colspan=3;
    else $colspan=2;
    echo "<tr class=TabHeader><td colspan='".$colspan."'>Ajouter une comp�tence requise</td></tr>";
    if ($evenement > 0 ) $colspan=2;
    else $colspan=1;
    echo "<tr bgcolor=$mylightcolor><td colspan='".$colspan."'>";
    echo "<select name=new_competence>";
    echo "<option value='-1'>Choix comp�tence</option>";

    $querym="select e.EQ_NOM, e.EQ_ID, p.TYPE, p.DESCRIPTION, p.PS_ID 
            from poste p, equipe e
            where e.EQ_ID=p.EQ_ID";
     
    if ( $evenement > 0 ) 
        $querym .=" and not exists (select 1 from evenement_competences t
                            where t.PS_ID=p.PS_ID
                            and t.E_CODE=".$evenement."
                            and t.EH_ID=".$partie.")";
    if ( $garde > 0 ) 
        $querym .=" and not exists (select 1 from garde_competences t
                            where t.PS_ID=p.PS_ID
                            and t.EQ_ID=".$garde."
                            and t.EH_ID=".$partie.")";
    $querym .=" order by e.EQ_ID, p.TYPE";
    

    $resultm=mysqli_query($dbc,$querym);
    $prevEQ=0;
    while ( custom_fetch_array($resultm) ) {
        if ( $prevEQ <> $EQ_ID ){
            echo "<option class='categorie' value='".$EQ_ID."'>".$EQ_NOM."</option>\n";
            $prevEQ =$EQ_ID;
        }
        echo "<option value='".$PS_ID."'>".$TYPE." - ".$DESCRIPTION."</option>";
    }
    echo "</select></td>";
    echo  "<td align=center>
        <input name='new_nb'
        type=text 
        title='Nombre requis' 
        size=1 
        value='1' 
        onchange='checkNumber(form.new_nb,\"1\");updateGlobal(form.new_nb.value);'
        >
    </td></tr>";
}
echo "</table>";

echo "<div align=center><p>
<input type=hidden name='evenement' value='".$evenement."'>
<input type=hidden name='garde' value='".$garde."'>
<input type=hidden name='partie' value='".$partie."'>";
if ( $evenement > 0 ) 
    echo " <input type=button class='btn btn-default' value='Retour' onclick=\"redirect('".$evenement."');\">";
if ( $garde > 0 ) 
    echo " <input type=button class='btn btn-default' value='Retour' onclick=\"redirect_garde('".$garde."');\">";  
echo " <input type=submit class='btn btn-default' value='Sauver' onclick='submit();'>";

echo "</form></div>";
writefoot();
?>
