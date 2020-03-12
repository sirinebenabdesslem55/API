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
$nomenu=1;
writehead();
if ( $charte_active == 0) check_all(14);
$id=$_SESSION['id'];

echo "<script type='text/javascript' src='js/charte.js?version=".$version."&update=3'></script>
</head>";

if (isset($_GET["accept"])) {
    $query="update pompier set P_ACCEPT_DATE=NOW() where P_ID=".$id;
    $res = mysqli_query($dbc,$query);
    insert_log('ACCEPT', $id);
    echo "<body onload='go();'/>";
    exit;
}

if (isset($_GET["reject"])) {
    echo "<body onload='reject();'/>";
    exit;
}

if (isset($_GET["reset"])) {
    check_all(14);
    $query="update pompier set P_ACCEPT_DATE=null";
    $res = mysqli_query($dbc,$query);
    echo "<body onload='go();'/>";
    exit;
}

$accept_date=get_accept_date ($id);
 
$B="";
$C=" affect� au sein ";
echo "<body class='top15'>";
if ( $nbsections == 0 and $syndicate == 1) {
    $A="du syndicat";
    $B=" et des adh�rents ";
    $C=$B;
}
else if ( $nbsections == 0 ) $A="de l'association";
else $A="du centre d'incendie et de secours";

if ( $application_title <> "eBrigade" ) $site=$application_title;
else $site="<b>".str_replace('www.','',$cisurl)."</b>";

echo "<div align=center>";

$charte = "<div align=left><h3>CHARTE DEFINISSANT LES REGLES D�USAGE DU SITE �".$site."�</h3>

<p><h5><span class=underline>Article 1</span> : Finalit� du document</h5>
Le pr�sent document d�finit les principales r�gles d�usage du site �".$site."� mis � disposition du personnel ".$B.$A.".

<p><h5><span class=underline>Article 2</span> : Domaine d�application</h5>
Il s�applique � toutes les personnes explicitement autoris�es � utiliser le dit site et qui disposent officiellement des cl�s personnelles d�acc�s.

<p><h5><span class=underline>Article 3</span> : Cadre d�utilisation</h5>
Le site �".$site."� a pour vocation de permettre � l�ensemble du personnel".$C.$A." de:
<ul>";

if ( $disponibilites ) $charte .=" <li>saisir ses disponibilit�s ou indisponibilit�s mensuelles,</li>";
if (  $nbsections > 0 ) $charte .= "<li>consulter le tableau de gardes mensuelles,</li>";
if (  $syndicate  == 0 ) $charte .= "<li>visualiser ses comp�tences op�rationnelles,</li>";
$charte .= "<li>prendre connaissance des diff�rentes informations ou consignes</li>
<li>mettre � jour sa fiche de renseignements personnels,</li>
<li>s�informer sur la vie ".$A."</li>
</ul>
<span class=small2>Nb : Cette liste est non exhaustive ; l�administrateur du site peut � tout moment la faire �voluer.</span>

<p><h5><span class=underline>Article 4</span> : R�gles d�utilisation du site �".$site."�</h5>
<ul>
<liL�utilisateur s�engage � ne pas effectuer d�op�rations qui pourraient avoir des cons�quences n�fastes sur le bon fonctionnement du site et/ou sur l�int�grit� de ses donn�es.</li>
<li>L�utilisateur est seul responsable de sa session et s�engage � se d�connecter apr�s chaque utilisation uniquement par le biais de l�onglet � d�connexion �.</li>
<li>L�utilisateur s�engage � ne pas accepter l�enregistrement des mots de passe par le navigateur.</li>
<li>D�une mani�re g�n�rale, l�utilisateur s�engage � faire preuve d�un comportement exemplaire lors de l�usage de ce site. 
Il est de son devoir de rappeler le contenu de cette charte � toute personne qui en aurait oubli� les modalit�s.</li>
</ul>

<p><h5><span class=underline>Article 5</span> : Compte utilisateur et mot de passe</h5>
<ul>
<li>Chaque utilisateur doit d�finir un mot de passe, en respectant les r�gles de s�curit� propres au site �".$site."� (longueur, pr�sence de chiffres, lettres et caract�res sp�ciaux).</li>
<li>Un compte utilisateur est strictement personnel et confidentiel. L�utilisateur ne doit en aucun cas communiquer son mot de passe � une tierce personne.</li>
<li>Il est recommand� de ne pas utiliser le m�me mot de passe que sur d�autres applications et de le changer r�guli�rement.</li>
</ul>


<p><h5><span class=underline>Article 6</span> : Confidentialit�</h5>
<ul>
<li>Les donn�es du site �".$site."� ne doivent en aucun cas �tre utilis�es en dehors du cadre pour lequel elles sont destin�es. </li>
<li>La divulgation des donn�es du site �".$site."� � des tiers est STRICTEMENT INTERDITE.</li>";
if (  $nbsections > 0 ) {
$charte .= "
<li>L�article 226-13/14 du code de proc�dure p�nale soumet tout sapeur-pompier au secret professionnel et m�dical. 
De ce fait il a interdiction de divulguer � quiconque toute information inh�rente � l�exercice de ses missions.</li>
<li>Pour rappel, conform�ment � la loi 83634 du 13 Juillet 1983 sur les droits et obligations des fonctionnaires :
<br>- Le sapeur-pompier est soumis � l�obligation de discr�tion professionnelle. Il ne doit pas divulguer � des tiers toute information relative au fonctionnement du service.
<br>- Le sapeur-pompier est soumis � l�obligation du devoir de r�serve. Il ne doit pas prof�rer en public des propos, des jugements mettant en cause le fonctionnement du service ou de la hi�rarchie.
</li>";
}
$charte .= "
<li>Compte-tenu de ces obligations, toute transmission d�information relative au service dans sa globalit� au travers des r�seaux sociaux est strictement interdite.</li>
<li>Tout contrevenant s�expose � des poursuites en corr�lation avec l�article et la loi sus cit�s.</li>
<li>D�une mani�re g�n�rale, l�utilisateur doit s�imposer le respect des lois et notamment celles relatives aux publications � caract�re injurieux, raciste, pornographique, diffamatoire, sur le harc�lement sexuel et/ou moral.</li>
</ul>

<p><h5><span class=underline>Article 7</span> : Informatique et libert�</h5>
<ul>
<li>Conform�ment � la Loi Informatique et Libert�s du 6 Janvier 1978, l�utilisateur dispose d'un droit d'acc�s, de modification et de suppression des donn�es personnelles le concernant, qu�il peut exercer � tout moment.</li>
<li>Les connexions des utilisateurs avec leur adresse IP, ainsi que les diff�rentes actions effectu�es sur le site �".$site."� sont trac�es et peuvent �tre exploit�es afin d�analyser tout changement suspect.</li>
</ul>";


$file = "charte_RGPD.pdf";
if ( $accept_date == "" ) {
    
    $charte .= "<p><table class='noBorder'>
    <tr><td><input type='checkbox' name='checkme1' id='checkme1' value='1' title='Cocher pour accepter la charte'
    onchange=\"change_checkboxes();\"></td> 
    <td><label for='checkme1'>Accepter les conditions d'utilisation</label></td></tr>";
    
    $charte .= "<tr><td colspan=2 class=small>En cochant cette case, je reconnais avoir lu et compris ces conditions d'utilisations et je m'engage � les respecter scrupuleusement.
    Le non respect de cette charte peut m'exposer � des poursuites au civil voire au p�nal.</td></tr>";
    
    if ( file_exists($filesdir."/charte/".$file)) {
        $charte .= "<tr><td colspan=2><br><h6>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'>Consulter le R�glement g�n�ral sur la protection des donn�es (RGPD).</a></h6><br></td></tr>";
        
        $charte .= "<tr><td><input type='checkbox' name='checkme2' id='checkme2' value='1' title='Cocher pour accepter le r�glement g�n�ral sur la protection des donn�es (RGPD)'
        onchange=\"change_checkboxes();\"></td> 
        <td><label for='checkme2'>Accepter le r�glement r�glement g�n�ral sur la protection des donn�es (RGPD)</label><br></td></tr>";

    }
    $charte .= "</table>";
    $charte .= "<p><input type='submit'  class='btn btn-default' value='Continuer' id='continue' title='Accepter les conditions pour pouvoir continuer' onclick=\"accept1();\"  disabled />";
    $charte .= "<p><input type='submit'  class='btn btn-default' value='Refuser' id='reject' title='Refuser et se d�connecter.' onclick=\"reject();\"/>";
}
else {
    $charte .= "<p><span class=small> J'ai accept� ces conditions d'utilisations ".$accept_date."</span>";
    if ( file_exists($filesdir."/charte/".$file)) {
        $charte .= "<tr><td colspan=2><h6>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'><i class='far fa-file-pdf fa-lg' style='color:red;'></i></a>
        <a href=showfile.php?charte=1&file=".$file." target=_blank title='consulter le RGPD'>Consulter le R�glement g�n�ral sur la protection des donn�es (RGPD).</a></h6></td></tr>";
        $charte .= "<p><span class=small> J'ai accept� le RGPD ".$accept_date."</span> <p>";
    }
    else 
        $charte .= "<br>";
    
    $charte .= "<input type='button' class='btn btn-default' value='retour'  onclick=\"javascript:history.back(1);\"/>";
    if ( check_rights($id, 14)) {
        $charte .= "<p><input type='submit'  class='btn btn-default' value='Forcer tous les utilisateurs � approuver de nouveau' id='reset' title='Forcer chaque utilisateur � accepter la charte � nouveau' onclick=\"reset();\"/>";
    }

}
$charte .= "</div>";
write_msgbox("Conditions d'utilisation", "", $charte, 30,30, 850);
echo "</div>";
writefoot($loadjs=false);
?>
