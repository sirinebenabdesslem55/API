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
$S_ID=intval($_GET["section"]);

get_session_parameters();

if ( isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if ( isset($_GET["pompier"])) $pompier=intval($_GET["pompier"]);
else $pompier=0;
if ( isset($_GET["vehicule"])) $vehicule=intval($_GET["vehicule"]);
else $vehicule=0;
if ( isset($_GET["materiel"])) $materiel=intval($_GET["materiel"]);
else $materiel=0;
if ( isset($_GET["note"])) $note=intval($_GET["note"]);
else $note=0;
if ( isset($_SESSION['dossier'])) $dossier =intval($_SESSION['dossier']);
else $dossier=0;

if ($evenement > 0 ) {
    if (! check_rights($id, 47, "$S_ID") and ! is_chef_evenement($id, $evenement) )
    check_all(15);
}
else if ($pompier > 0 ) {
    $statut=get_statut($pompier);
    if ( $statut == 'EXT') $perm = 37;
    else if ( $pompier == $id ) $perm=0;
    else $perm = 2;
    
    if (! check_rights($id, $perm, "$S_ID")) {
        check_all($perm);
        check_all(24);
    }
}
else if ($vehicule > 0 ) {
    if (! check_rights($id, 17, "$S_ID")) {
        check_all(17);
        check_all(24);
    }
}
else if ($materiel > 0 ) {
    if (! check_rights($id, 70, "$S_ID")) {
        check_all(70);
        check_all(24);
    }
}
else if ($note > 0 ) {
    if (! check_rights($id, 73, "$S_ID") and ! check_rights($id, 74, "$S_ID") and ! check_rights($id, 75, "$S_ID") and get_beneficiaire_note($note) <> $id) {
        check_all(73);
        check_all(24);
    }
}
else {
    if (! check_rights($id, 47, "$S_ID"))
    check_all(24);
}

writehead(); 
?>
<script type="text/javascript">
    $(function(){
        var submitbutton=document.getElementById('submitbutton');
        var max = <?php echo $MAX_SIZE; ?>;
        var max_mb = <?php echo $MAX_FILE_SIZE_MB; ?>;
        
        $(document).ready(function () {
           submitbutton.disabled=true;
        });
        
        $('#userfile').change(function(){
            var f=this.files[0];
            if ( f.size > max || f.fileSize > max ) {
                alert("Le fichier choisi est trop gros, maximum permis "+ max_mb+ "M");
                this.value='';
                submitbutton.disabled=true;
            }
            else {
                submitbutton.disabled=false;
            }
        })
    })
</script>
<?php
echo "</head><body style='padding:60px;'>";

if ( $evenement == 0 and $pompier == 0 and $vehicule == 0 and $note == 0 and $materiel == 0) {
    // section
    echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".get_section_code("$S_ID")." - ".get_section_name("$S_ID")."</b></font></td></tr>
      </table>";
    echo "<form action='save_documents.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
}
else if ( $pompier > 0 ) {
    // personnel
    $query="select P_NOM, P_PRENOM, P_SEXE from pompier where P_ID=".$pompier;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $prenom_nom=my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]);
    echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".$prenom_nom."</b></font></td></tr>
      </table>";
    echo "<form action='save_personnel.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='P_ID' value='$pompier'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $vehicule > 0 ) {
    // vehicule
    $query="select TV_CODE, V_IMMATRICULATION from vehicule where V_ID=".$vehicule;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $immatriculation=$row["V_IMMATRICULATION"];
    $type=$row["TV_CODE"];
    echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".$type." ".$immatriculation."</b></font></td></tr>
      </table>";
    echo "<form action='save_vehicule.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='vehicule' value='$vehicule'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $materiel > 0 ) {
    // materiel
    $query="select m.TM_ID, tm.TM_CODE, tm.TM_DESCRIPTION, m.MA_MODELE from materiel m, type_materiel tm where m.TM_ID = tm.TM_ID and m.MA_ID=".$materiel;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $TM_CODE=$row["TM_CODE"];
    $TM_DESCRIPTION=$row["TM_DESCRIPTION"];
    $MA_MODELE=$row["MA_MODELE"];
    echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".$TM_CODE." ".$TM_DESCRIPTION." ".$MA_MODELE."</b></font></td></tr>
      </table>";
    echo "<form action='save_materiel.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='materiel' value='$materiel'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else if ( $note > 0 ) {
    // note de frais
    $query="select NF_ID, P_ID from note_de_frais where NF_ID=".$note;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $note_number=$row["NF_ID"];
    echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>Justificatif pour Note de frais ".$note_number."</b></font></td></tr>
      </table>";
    echo "<form action='note_frais_save.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='operation' value='document'>";
    echo "<input type='hidden' name='person' value='".$row["P_ID"]."'>";
    echo "<input type='hidden' name='nfid' value='$note'>";
    echo "<input type='hidden' name='status' value='documents'>";
}
else { 
    // evenement
    $query="select TE_CODE, E_LIBELLE from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $event_name=$row["E_LIBELLE"];
    $type=$row["TE_CODE"];
    echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b>".$event_name."</b></font></td></tr>
      </table>";
    echo "<form action='evenement_save.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='action' value='document'>";
    echo "<input type='hidden' name='section' value='$S_ID'>";
    echo "<input type='hidden' name='evenement' value='$evenement'>";
    echo "<input type='hidden' name='status' value='documents'>";
}

echo "<p><table cellspacing=0 border=0>";
echo "<tr class='TabHeader'>
             <td colspan=2 >Ajout de document</td>
      </tr>";

if ( $evenement == 0 and $pompier == 0 and $vehicule == 0 and $note == 0 and $dossier == 0 and $materiel == 0) {
    //type
    $query="select TD_CODE, TD_LIBELLE, TD_SYNDICATE, TD_SECURITY  from type_document where TD_SYNDICATE = ".$syndicate;
    $query .=" order by TD_LIBELLE";
    $result=mysqli_query($dbc,$query);
    
    echo "<tr><td bgcolor=$mylightcolor align=right>Type:</td>
        <td bgcolor=$mylightcolor> 
        <select id='type' name='type'>\n";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
        $TD_CODE=$row["TD_CODE"];
        $TD_LIBELLE=$row["TD_LIBELLE"];
        $TD_SECURITY=intval($row["TD_SECURITY"]);
        if ( check_rights($id, $TD_SECURITY)) {
            $selected='';
            if ( isset($_SESSION['td'])) {
                if ($_SESSION['td'] == $TD_CODE) $selected='selected';
            }
            echo "<option value='".$TD_CODE."' $selected>".$TD_LIBELLE."</option>\n";
        }
    }
    echo "</select></td></tr>";
    
    $parent="A la racine";
}
else if ( isset($_SESSION['dossier']) and $_SESSION['dossier'] > 0 and $evenement == 0 and $pompier == 0 and $vehicule == 0 and $note == 0 and $materiel == 0) {
    
    echo "<input type='hidden' name='dossier' value='".$_SESSION['dossier']."'>";
    echo "<input type='hidden' name='type' value='".$_SESSION['td']."'>";
    // dossier supérieur
    $parent="<b>".get_folder_name($_SESSION['dossier'])."</b>";
    $query="select td.TD_CODE, td.TD_LIBELLE from type_document td, document_folder df 
            where df.TD_CODE = td.TD_CODE
            and df.DF_ID=".$_SESSION['dossier'];
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $parent .= " <br><font size=1>(".$row["TD_LIBELLE"].")</font>";
    echo "<input type='hidden' name='type' value='".$row["TD_CODE"]."'>";
    echo "<tr>
             <td bgcolor=$mylightcolor align=right >Emplacement: </td>
           <td bgcolor=$mylightcolor align=left>".$parent."</td>
      </tr>";
}


//security sauf pour note de frais
if ( $note == 0 and $document_security == 1) {
    $query="select DS_ID, DS_LIBELLE,F_ID from document_security";
    echo "<tr><td bgcolor=$mylightcolor align=right>Sécurité: </td>
        <td bgcolor=$mylightcolor>
        <select id='security' name='security'>\n";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
      $DS_ID=$row["DS_ID"];
      $DS_LIBELLE=$row["DS_LIBELLE"]; 
      if ( $DS_ID == 0 ) $selected='selected';
      else $selected='';
      echo "<option value='".$DS_ID."' $selected>".$DS_LIBELLE."</option>\n";
    }
    echo "</select></td></tr>";
}
else echo "<input type='hidden' name='security' value='1'>";

// Document
echo "<tr><td bgcolor=$mylightcolor align=right>Document (max ".$MAX_FILE_SIZE_MB."M):</td>
    <td bgcolor=$mylightcolor>
    <input type='file' name='userfile' id='userfile'></td></tr>";
echo "</table>";

if ( $note > 0 ) $onclick="javascript:self.location.href='note_frais_edit.php?nfid=".$note."&action=update'";
else $onclick="javascript:history.back(1)";
echo "<p><input type='submit' class='btn btn-default' id='submitbutton' value='Envoyer' > 
      <input type='button' class='btn btn-default' value='Annuler' onclick=\"".$onclick.";\">";
echo "</form>";
if ( $pompier > 0 ) {
 echo "<p ><table class=noBorder><tr><td width=30><i class='far fa-lightbulb fa-2x' title='Ajouter une signature'></td>
    <td class=small width=400>Il est possible d'ajouter une signature personnelle, qui sera automatiquement ajoutée sur certains documents PDF générés (notes de frais). 
    <br>Pour cela un fichier <b>signature.png</b> ou <b>signature.jpg</b> doit être ajouté. Pour un bon résultat, la taille de l'image doit être de environ 4cm de haut sur 8cm de large.
    <br>Et l'accès à ce fichier doit idéalement être protégé.</td></tr></table>";
    
}
echo "</div>";

writefoot();
?>
