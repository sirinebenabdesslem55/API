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
include_once ("fonctions_dps.php");
check_all(0);

$evenement = (isset($_POST['evenement'])?intval($_POST['evenement']):(isset($_GET['evenement'])?intval($_GET['evenement']):""));

writehead();
$msgerr="";

if(isset($_POST['action'])){
$dimNbISActeurs = (isset($_POST['dimNbISActeurs'])?secure_input($dbc,$_POST['dimNbISActeurs']):0);
$dimNbISActeursCom = (isset($_POST['dimNbISActeursCom'])?secure_input($dbc,$_POST['dimNbISActeursCom']):"");
$dimP=(isset($_POST['P'])?secure_input($dbc,$_POST['P']):0);
$dimP1=(isset($_POST['P1'])?secure_input($dbc,$_POST['P1']):0);
$dimP2=(isset($_POST['P2'])?secure_input($dbc,$_POST['P2']):0.25);
$dimE1=(isset($_POST['E1'])?secure_input($dbc,$_POST['E1']):0.25);
$dimE2=(isset($_POST['E2'])?secure_input($dbc,$_POST['E2']):0.25);
$dimI=(isset($_POST['i'])?secure_input($dbc,$_POST['i']):0);
$dimRIS=(isset($_POST['RIS'])?secure_input($dbc,$_POST['RIS']):0);
$dimRISCalc=(isset($_POST['RISCalc'])?secure_input($dbc,$_POST['RISCalc']):0);
$dimNbIS=(isset($_POST['NbIS'])?secure_input($dbc,$_POST['NbIS']):0);
$dimTypeDPS=(isset($_POST['type'])?secure_input($dbc,$_POST['type']):0);
$dimTypeDPSCom=(isset($_POST['commentaire'])?secure_input($dbc,$_POST['commentaire']):"");
$dimSecteurs=(isset($_POST['secteurs'])?secure_input($dbc,$_POST['secteurs']):0);
$dimPostes=(isset($_POST['postes'])?secure_input($dbc,$_POST['postes']):0);
$dimEquipes=(isset($_POST['equipes'])?secure_input($dbc,$_POST['equipes']):0);
$dimBinomes=(isset($_POST['binomes'])?secure_input($dbc,$_POST['binomes']):0);
EvenementSave($_POST);
}

$row=EvenementDPS($evenement,'data');
$dimNbISActeurs=$row['dimNbISActeurs'];
$dimNbISActeursCom=stripslashes($row['dimNbISActeursCom']);
$dimI=$row['i'];
$dimP=intval($row['P']);
$dimP1=intval($row['P1']);
$dimP2=$row['P2'];
$dimE1=$row['E1'];
$dimE2=$row['E2'];
$dimRIS=$row['RIS'];
$dimRISCalc=$row['RISCalc'];
$dimNbIS=$row['NbIS'];
$dimTypeDPS=stripslashes($row['type']);
$dimTypeDPSCom=stripslashes($row['commentaire']);
$dimSecteurs=$row['secteurs'];
$dimPostes=$row['postes'];
$dimEquipes=$row['equipes'];
$dimBinomes=$row['binomes'];
$effectif=$row['effectif'];
$action=(($dimRISCalc>0)?"Modifier":"Enregistrer");// si Modifier >> affiche lien vers impression
?> 
<script type="text/javascript" src="js/jquery.min.js"></script> 
<script type="text/javascript" src="js/jquery_forms.js"></script> 
<script type="text/javascript">
$(document).ready(function(){
    calcRIS();
    var options = {
        target:     '#resultat', 
        url:        'dps_save.php',     
        success:    function() { 
            //alert("Dimensionnement enregistr�.");
        } 
    };
    $('form#frmDPS').ajaxForm(options);
    $('input#btGrille').submit(options);
    $('input').keyup(function(){
        calcRIS();
    });
    $("input[type='radio']").change(function(){
        calcRIS();
    });
});
function calcRIS(){
    $.post('dps_calc.php',
        {
            evenement:$("#evenement").fieldValue()[0],
            P1:$("input[name='P1']").fieldValue()[0],
            P2:$("input[type='radio'][name='P2']").fieldValue()[0],
            E1:$("input[type='radio'][name='E1']").fieldValue()[0],
            E2:$("input[type='radio'][name='E2']").fieldValue()[0],
            dimNbISActeurs:$("input[type='text'][name='dimNbISActeurs']").fieldValue()[0],
            dimNbISActeursCom:$("input[type='textarea'][name='dimNbISActeursCom']").fieldValue()[0],
            actionPrint:'<?php echo $action;?>'
        },
        function(data){
            $("#resultat").html(data);
        }
    );
}
function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}
</script>
<style type='text/css'>
tr{
    background-color:<?php echo $mylightcolor; ?>;
}
</style>
</head>
<body>
<?php

echo "<div class='table-responsive'><p style=\"color:red;\">$msgerr</p>";
echo  "<table cellpading=0 cellspacing=0 border=0 width=900>
    <tr class='TabHeader'><td colspan=2 >RNMSC-DPS - Dimensionnement</td></tr><tr><td>";
?>
<form action="dps_save.php?tab=2" method="POST" name="dps" id="frmDPS">
<div id="resultat"></div>
<div id="formulaire">
<table class='noBorder'>
<tr><th class="TabHeader" colspan="2">Demande pour les acteurs : </th></tr>
<tr>
<td>
Descriptif de la demande pour les acteurs
<textarea name="dimNbISActeursCom" style="width:90%;FONT-SIZE: 10pt; FONT-FAMILY: Arial;"><?php echo $dimNbISActeursCom; ?></textarea>
<br>Equivalence en nombre d'intervenants secouristes pour les acteurs :<input type="text" name="dimNbISActeurs" id="dimNbISActeurs"  value="<?php echo $dimNbISActeurs; ?>"  style="background-color:Yellow;"> <br >(Minimum = 4, si un dispositif est demand� pour les acteurs)  
</td>
<td></td>
</tr>

<tr><th colspan="2"></th></tr>

<tr><th class="TabHeader" colspan="2"><b>Dimensionnement pour le public : </b></th></tr>
<tr>
<td colspan="2">
<b>Nota :</b><br />Dans le cas o� les acteurs pr�senteraient un risque diff�rent du public, et en absence d'un dispositif sp�cifique pour les acteurs, le PAPS n'est pas un dispositif de secours suffisant.
</td>
</tr>
<tr><th class="TabHeader"><b>Effectif d�clar� du public</b></th>
<th class="TabHeader">Indicateur P1</th></tr>
<tr>
<td><input type="text" name="P1" id="P1" value="<?php echo $dimP1; ?>" style="background-color:Yellow;"></td>
<td><input type="text" name="P" id="P" value="" readonly class="result" style="border:0px;background-color: transparent;"></td>
</tr>
<tr>
<tr><th class="TabHeader">Activit� du rassemblement</th>
<th class="TabHeader">Indicateur P2</th></tr>
<tr>
<td><input type="radio" name="P2" value="0.25"  <?php echo ($dimP2==0.25?"checked=\"yes\"":""); ?>> Public assis : <small>spectacle, c�r�monie cultuelle, r�union publique, restauration, rendez-vous sportif...</small></td>
<td>0,25</td>
</tr>
<tr>
<td><input type="radio" name="P2" value="0.30" <?php echo ($dimP2==0.30?"checked=\"yes\"":""); ?>> Public debout : <small>c�r�monie cultuelle, r�union publique, restauration, exposition, foire, salon, comice agricole...</small></td>
<td>0,30</td>
</tr>
<tr>
<td><input type="radio" name="P2" value="0.35" <?php echo ($dimP2==0.35?"checked=\"yes\"":""); ?>> Public debout : <small>spectacle avec public statique, f�te foraine, rendez-vous sportif avec protection du public par rapport � l'�v�nement...</small></td>
<td>0,35</td>
<tr>
<td><input type="radio" name="P2" value="0.40" <?php echo ($dimP2==0.40?"checked=\"yes\"":""); ?>> Public debout : <small>spectacle avec public dynamique, danse, feria, f�te votive, carnaval, spectacle de rue, grande parade, rendez-vous sportif sans protection du public par rapport � l'�v�nement ...
Ev�nement se d�roulant sur plusieurs jours avec pr�sence permanente du public : h�bergement sur site ou �proximit�. </small></td>
<td>0,40</td>
</tr>
<tr>
<th class="TabHeader">Caract�ristiques de l'environnement ou de l'accessibilit� du site</th>
<th class="TabHeader">Indicateur E1</th>
</tr>
<tr>
<td><input type="radio" name="E1" value="0.25"  <?php echo ($dimE1==0.25?"checked=\"yes\"":""); ?>> Structures permanentes : B�timent, salle � en dur �,...
<br /><small>Voies publiques, rues,...avec acc�s d�gag�s. Conditions d'acc�s ais�s </small></td>
<td>0.25</td>
</tr>
<tr>
<td><input type="radio" name="E1" value="0.30"  <?php echo ($dimE1==0.30?"checked=\"yes\"":""); ?>> Structures non permanentes : gradins, tribunes, chapiteaux,...
<br /><small>Espaces naturels : surface = 2 hectares. Brancardage : 150 m < longueur = 300 m. Terrain en pente sur plus de 100 m�tres 0,30</small></td>
<td>0.30</td>
</tr>
<tr>
<td><input type="radio" name="E1" value="0.35"  <?php echo ($dimE1==0.35?"checked=\"yes\"":""); ?>> Espaces naturels : 2 ha < surface = 5 ha 
<br /><small>Brancardage : 300 m < longueur = 600 m. Terrain en pente sur plus de 150 m�tres. Autres conditions d'acc�s difficiles</small></td>
<td>0.35</td>
</tr>
<tr>
<td><input type="radio" name="E1" value="0.40"  <?php echo ($dimE1==0.40?"checked=\"yes\"":""); ?>> Espaces naturels : surface > 5 hectares
<br /><small>Brancardage : longueur > 600 m�tres. Terrain en pente sur plus de 300 m�tres. Autres conditions d'acc�s difficiles : Talus, escaliers, voies d'acc�s non carrossables,...Progression des secours rendue difficile par la pr�sence du public </small></td>
<td>0.40</td>
</tr>
<tr>
<th class="TabHeader"> D�lai d'intervention des secours publics </th>
<th class="TabHeader"> Indicateur E2</th>
</tr>
<tr>
<td><input type="radio" name="E2" value="0.25"  <?php echo ($dimE2==0.25?"checked=\"yes\"":""); ?>> <= 10 minutes </td>
<td>0.25</td>
</tr>
<tr>
<td><input type="radio" name="E2" value="0.30"  <?php echo ($dimE2==0.30?"checked=\"yes\"":""); ?>> > 10 minutes et <= 20 minutes </td>
<td>0.30</td>
</tr>
<tr>
<td><input type="radio" name="E2" value="0.35"  <?php echo ($dimE2==0.35?"checked=\"yes\"":""); ?>> > 20 minutes et <= 30 minutes</td>
<td>0.35</td>
</tr>
<tr>
<td><input type="radio" name="E2" value="0.40"  <?php echo ($dimE2==0.40?"checked=\"yes\"":""); ?>> > 30 minutes</td>
<td>0.40</td>
</tr>
</table>
<input type="hidden" name="evenement" id="evenement" value="<?php echo $evenement; ?>">
</form>
</div>
<tr>
<th colspan="2" class="TabHeader"> Rappel du RNMSC-DPS</th>
</tr>
<tr><td>
<p style="text-align:justify; padding:0 1em 0 1em;"><small>Le dimensionnement du dispositif de secours pour les <b>acteurs</b> est de la seule responsabilit� du demandeur et/ou de l'autorit� de police comp�tente. 
<br />
Le dimensionnement du dispositif de secours pour le <b>public</b> est r�git par le R�f�rentiel National des Missions de S�curit� Civile - Dispositifs Pr�visionnels de Secours
Ce calcul de dimensionnement minimal est mis a disposition pour "information".
<br />Seule une �tude personnalis�e de votre manifestation avec une association de s�curit� civile permettra de dimensionner <b>votre</b> dispositif pr�visionnel de secours
Le nombre d'intervenant correspond au nombre de Secouriste, Equipier Secouriste, Chef d'Equipe, Chef de Poste. <br />Sont exclus: l'encadrement et la logistique.

Les DPS font partie des missions de s�curit� civile d�volues uniquement aux associations agr��es de s�curit� civile.
<br />
En tout �tat de cause, il incombe � l'autorit� de police comp�tente, si elle le juge n�cessaire ou appropri�, de prendre toute disposition en mati�re de secours � personnes pour assurer la s�curit� lors d'un rassemblement de personnes, sur son territoire de comp�tences. A ce titre, elle peut imposer � l'organisateur un DPS dimensionn� selon les modalit�s du pr�sent r�f�rentiel national.
<br />
En outre, l'organisateur est libre de faire appel, en compl�ment du DPS � personnes prescrit, � tout autre moyen humain ou mat�riel, destin� � augmenter le niveau de s�curit� de la manifestation.</small></p>
<p style="text-decoration:none;font-size:0.8em;">Arr�t� du 7 novembre 2006 fixant le r�f�rentiel national relatif aux dispositifs pr�visionnels de secours<br><a href="http://www.legifrance.gouv.fr/WAspad/UnTexteDeJorf?numjo=INTE0600910A" target="_blank">NOR: INTE0600910A</a>
</p><a href="https://www.interieur.gouv.fr/content/download/94241/736020/file/RNMSC%202006_10%20-%20DPS.pdf" target="_blank" style="text-decoration:none;font-size:0.8em;">
R�ferentiel National - Missions de S�curit� Civile : Dispositifs Pr�visionnels de Secours</a>
</td></tr></table>
<p><input type='button'  class='btn btn-default' value='Retour' onclick="javascript:history.back(1);">
</div>

<?php
//writefoot();
?>

