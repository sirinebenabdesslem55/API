eBrigade 5.1 : Application web des pompiers et des secouristes
Copyright (c) 2004-2020 Nicolas MARCHE
License GPL V2.0 ou plus r�cente, voir le fichier license.txt
Site web: https://ebrigade.net
Project page: https://sourceforge.net/projects/ebrigade

==============================================
=   Documentation compl�te
==============================================

http://ebrigade.sourceforge.net/wiki

==============================================
=   INSTALLATION INITIALE
==============================================

1 - installer APACHE / MYSQL / PHP
    exemple utiliser WAMP Server sur Windows
        https://sourceforge.net/projects/wampserver 
    ou LAMP sur linux. 
    Les derni�res versions des composants sont recommand�es.
    PHP 5.6, 7.0, 7.1, 7.2, 7.3 et 7.4 sont support�s.

2 - Transf�rer les fichiers de eBrigade 
    * sur le serveur avec SFTP (utiliser Filezilla de pr�f�rence, conserver l'encodage ANSI des fichiers)
    * ou sur le disque local(exemples C:\www ou /var/www

3 - creer la base de donnees MYSQL 
    * utiliser par exemple phpmyadmin (fourni avec WAMP)
    * definir un user et un password autre que root avec tous les droits
    * creer la base de donnees avec un character set supportant les accents, de pr�f�rence latin1_swedish_ci
    * CREATE DATABASE ebrigade DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
        * Donner toutes les permissions sur la base � l'utilisateur cr�e.

4 - definir un alias dans le fichier de configuration de apache httpd.conf
    pointant sur C:\www\ebrigade ou /var/www/ebrigade dont le nom peut etre par exemple "ebrigade"

5 - se connecter via l'interface web exemple http://127.0.0.1/ebrigade

6 - renseigner les informations permettant d'acceder a la base de donnees.
    * si les informations sont correctes, alors les tables sont automatiquement creees
    * remarque: sur free.fr seul le password est requis.

7 - s'identifier avec le login admin et le mot de passe g�n�r� � l'installation
    * Conserver pr�cieusement le mot de passe admin g�n�r�, ou le changer � la premi�re connexion
    * changer l'adresse email de admin (Session / Mes infos)

8 - configurer dans l'ordre:
    * les parametres de configuration (Administration / Configuration)
    ** pour pouvoir utiliser un service SMS, il faut cr�er un compte (de preference clickatell central API -  SMS Gateway )
    * les groupes et leurs habilitations ( Param�trage / Habilitations )
    * les sections ou l'organigramme (Informations / Sections )
    * les types de gardes ( Parametrage /Gardes )
    * les competences ( Parametrage / Comp�tences )
        * les fonctions pour chaque type d'�v�nement ( Parametrage / Fonctions )
    * les v�hicules ( Inventaire /Vehicules)
    * le materiel ( Param�trage / Types de materiel)
    * le personnel
    * les qualifications du personnel ( Param�trage / Affectations )

==============================================
=   MISE A JOUR DE L'APPLICATION
==============================================

Par exemple pour migrer l'application eBrigade de 5.0 ou 5.0.3 vers 5.1.0
Suivre les �tapes suivantes:

1 - Faire une nouvelle sauvegarde de la Base (menu Administration / Base de donn�es ou avec mysqldump sur le serveur)
2 - �ventuellement faire une copie de l'ensemble des fichiers du site web vers un r�pertoire d'archive (�a sera une sauvegarde si il faut revenir en arri�re)
3 - D�zipper le package sur le disque local
4 - Transf�rer par SFTP l�ensemble des fichiers de la nouvelle version eBrigade vers le serveur web, y compris les sous r�pertoires, en �crasant les fichiers existants.
5 - Se connecter avec le compte administrateur, la base de donn�es sera automatiquement mise � jour.
6 - En cas d'erreurs, recharger la sauvegarde et ex�cuter le fichier pas � pas dans phpMyadmin. Il y a certainement des incoh�rences dans vos donn�es, qui doivent �tre corrig�es.
7 - La nouvelle version est install�e.
8 - Purger le cache du navigateur (CTRL+F5)
    ATTENTION : il est important de purger le cache du navigateur pour que tout le code javascript fonctionne bien imm�diatement

Il est possible de faire un upgrade de plusieurs versions � la fois, exemple de 4.2 � 5.1.0.
La m�thode est la m�me.

==============================================
=   release note 5.1 - 2020
==============================================
- Am�lioration des fiches v�hicule et mat�riel, affichage des infirmations par onglets
- Support SMSEagle pour envoyer des SMS (https://www.smseagle.eu)
- Sapeurs Pompiers Professionnels - ajout r�gime de travail (12h,24h,SHR)
- Les tableaux de garde mensuels sont maintenant possibles aussi pour les associations
- Notes de frais, nouvelle permission n�77 pour permettre de les cr�er au lieu de n�11 (saisir ses absences)
- Nouveau type salari�, service national universel
- Nouvelles cartes de France jVectorMap, en remplacement de franceMap
- Gestion des licenses des adh�rents (num�ro, date et expiration)
- Am�lioration de la fiche personnel, au lieu d'�tre d�sactiv�s, les champs ne sont plus �ditables du tout
- Possibilit� de bloquer les changements sur les fiches personnel (param�tre de configuration block_personnel)
- Upgrade FPDF 1.8.2
- Am�lioration de la page configuration avec boutons bootstrap-toggle
- La section choisie pour l'affichage des donn�es est conserv�e apr�s deconnexion
- Statistiques transport de victimes possible selon qui a transport� sur le param�trage du type d'�v�nement
- Lien Waze sur la fiche �v�nement et personnel si l'adresse est renseign�e et la �olocalisation activ�e
- Nouvelle page utilisateurs en ligne d�coupl�e du chat
- Pour les associations, il est possible de bloquer la cr�ation de certains types d'�v�nements voire tous pour une plage de dates donn�es
- courbe de charge d'activit� - graphique nombre de participations par jour des b�n�voles selon le type d'activit�
- am�liorations de s�curit� diverses

==============================================
=   release note 5.0.3 - d�cembre 2019
==============================================
- correction du format des fichiers sql, Unix au lieu de Windows, corrige des erreurs d'installations.
- corrections bugs li�s aux sauvegardes manuelles, et icones sur la page de reload

==============================================
=   release note 5.0.2 - d�cembre 2019
==============================================
- correction d'un bug de collation avec MySQL 5.7 lors de la cr�ation de la base, mauvais affichage des accents

==============================================
=   release note 5.0.1 - septembre 2019
==============================================
- pas de mise � jour de la base de donn�es
- corrections de bugs mineurs, en particulier affichage ou upgrade depuis version 4.4 ou inf�rieur
- am�lioration de la page planning, ajout de filtre par type d'�v�nement et de tris par date, et d�tail des participations

==============================================
=   release note 5.0 - juillet 2019
==============================================
principales nouveaut�s
----------------------
- Ajout de graphiques avec chart.js (remplacement des anciens graphiques optionnels ChartDirector)
- Nouvelles ic�nes: Font Awesome upgrade to 4.7.0
- Am�liorations graphiques: Bootstrap upgrade to 4.3.1
- Remplacement des popups datepickers, bootstrap-datepicker est maintenant utilis�
- Ajout de documents sur le mat�riel
- Ajout du lieu de stockage pour les consommables
- Le statut ancien devient possible pour les externes
- Gestion des homonymes, possibilit� de fusionner les doublons
- Sur la recherche par nom, ajout d'un filtre par section
- Gestion des gardes pour un SDIS
- R�les de l'organigramme, nouvelle propri�t� "Membre de n'importe quelle section"
- Statistiques sur les �v�nements, le nombre possible augmente de 4 � illimit� pour chaque type d'�v�nements
- Ajout des identifiants des outils de communication (Skype, Whatsapp, Zello)
- Associations: possibilit� de bloquer temporairement la cr�ation de certains types d'�v�nements
- V�hicules: ajout de propri�t�s Attelage et Public Address
- Cartes Google Maps: nouvelle permission permettant de voir les cartes
- Reporting: Liste des changements de r�les (nouveaux �lus)
- Am�liorations de s�curit�s suite � penetration tests

==============================================
=   release note 4.5.1 - f�vrier 2019
==============================================
- Am�liorations de s�curit� diverses sans mise � jour de la base de donn�es

==============================================
=   release note 4.5 - 2019
==============================================
principales nouveaut�s
----------------------
- Les widgets de la page d'accueil sont maintenant configurables
- Am�liorations graphiques sur la page �v�nement, modal responsive bootstrap remplace popup boxes 
- Am�liorations de s�curit� CSRF
- Nouvelle m�thode d'encryption propos�e pour les mots de passe, bcrypt
- Nouvelle API gratuite de g�olocatisation OSM (data.gouv.fr) en compl�ment de Google
- Am�liorations sur les notes de frais (ajout d'une double validation, am�liorations affichage, choix section)
- Am�liorations de la fiche victime, nouvel onglet secours psychologiques
- Formations individuelles: ajout du d�tail du paiement
- Feuille garde SP, les piquets configur�s sont imprim�s
- L'application peut maintenant �tre utilis�e par des organisations militaires

==============================================
=   release note 4.4 - 2018
==============================================
principales nouveaut�s
----------------------
- Nouveau calendrier
- Page d'accueil, nouveaux widgets:
    - Horaires de travail � valider
    - Notes de frais � valider ou rembourser
    - Statistiques manquantes sur des �v�nements
- Grades
    - Ajout de tous les grades pompiers du SSSM et des ic�nes correspondantes
- Garde Sapeurs-Pompiers
    - Possibilit� de cr�er des �quipes et de les voir sur la carte (si l'adresse de la garde est renseign�e et la g�olocalisation activ�e)
    - Possibilit� d'activer ou d�sactiver la fonctionnalit� remplacements
    - Ajout des comp�tences requises sur les gardes
    - Remplissage automatique du tableau de garde avec les SPV disponibles ( n�cessite de param�trer les comp�tences requises)
    - Nouvel onglet piquets sur la feuille de garde, avec comp�tence requise pour chaque r�le dans un v�hicule
    Documentation d�taill�e ici: http://ebrigade.sourceforge.net/wiki/index.php/Tableau_de_garde
- Fiche personnel
    - Am�lioration graphique du premier onglet maintenant en 3 blocs, responsive
    - ajout mail de la personne � contacter d'urgence
- Ev�nements
    - Rapport sur un �v�nement: 
        - possibilit� de choisir les �l�ments � imprimer sur le PDF
        - am�lioration graphiques sur les boutons
    - Ajout du t�l�phone de contact sur les �v�nements
    - Ajout d�un document PDF listant les produits consommables utilis�s 
    - Ajout des options d'inscriptions sur les �v�nements
        D�tail ici http://ebrigade.sourceforge.net/wiki/index.php/Options
- Associations
    - Ajout des identifiants radio sur les d�partements et antennes
    - Possibilit� de cr�er un renfort pour chaque d�partement (en plus de un par antenne)
    - Notification des responsables des renforts quand on valide l'�v�nement principal
    - Ajout de conventions sur les formations
    - Inscription du personnel, possibilit� de filtrer avec ou sans les sous-sections le personnel � inscrire
- Horaires du personnel salari� 
    - Ajout d'un champ commentaire pour chaque jour
    - Ajout d'une vue des horaires travaill�s jour par jour en plus de la vue par semaine
- Notes de frais
   - Ajout d'un champ commentaire
   - Ajout d'un num�ro comptable, par d�faut ann�e / mois / num�ro incr�ment� depuis le d�but du mois.
   - Permettre une double validation (par exemple Pr�sident et Tr�sorier)
   - Am�liorations ergonomiques
   - Nouveau widget notes de frais � valider sur la page d'accueil
   - Envoi de la note de frais au format PDF lors de la demande de validation
   - Commentaire en cas de rejet
- SMS
   - support de la nouvelle API v4 de SMSGateway.me, le password doit �tre remplac� par le token
- Possibilit� de scanner des QR Codes de victimes
    D�tail ici http://ebrigade.sourceforge.net/wiki/index.php/CAV


==============================================
=   release note 4.3 - octobre 2017
==============================================
principales nouveaut�s
----------------------
- Nouvelle page d'accueil avec des widgets dont prochaines participations et alertes
- Support d'une nouvelle plateforme de SMS gratuite SMSGateway.me
- Ajout heure d'arriv�e � l'h�pital pour les victimes
- Fonctionnalit� "�v�nements cach�s" pour le personnel ayant la permission n�9
- Ajout d'une propri�t� "Formation possible" sur les comp�tences
- Ajout des d�corations collectives
- Notes de frais:
   - Possibilit� de faire don � l'association
   - Warnings si les justificatifs n'ont pas �t� attach�s
- Gardes Sapeurs-pompiers:
   - Support de r�gime de travail complexes par exemple 12-24/12-48, ou 5 �quipes
   - possibilit� d'avoir une �quipe SPP de garde diff�rente la nuit du jour
   - La configuration est maintenant faite sur le param�trage de chaque type de garde
   - Feuille d'inscription des SP sur les gardes
         - Ajout possibilit� de choisir directement jour et/ou nuit
         - Ajout filtre pour afficher ou pas les SPP
- Ajout Nationalit� et autres champs sur la page des photos du personnel
- La date d'expiration est maintenant obligatoire et forc�e sur les comp�tences expirables
- Nouvelles icones pour les types d'�v�nements
- Am�lioration des QR Codes: ils donnent maintenant un lien URL vers les infos de la fiche personnel
- Mise � jour Nusoap pour les webservices, compatible PHP7
- Ev�nement renforts: possibilit� de cr�er un renfort par antenne au niveau des d�partements


==============================================
=   release note 4.2 - mai 2017
==============================================

principales nouveaut�s
----------------------
1 - Possibilit� de g�o localiser une personne (ou un num�ro de t�l�phone) en lui envoyant un SMS
2 - Enregistrement Ajax sur la page �v�nement pour les fonctions et �quipes du personnel, kilom�trage des v�hicules, nombre de mat�riel et consommables
3 - Sapeurs-pompiers: fonction de remplacement du personnel sur une garde:
    - la demande de remplacement peut �tre cr��e par
    - il peut proposer un rempla�ant
    - le rempla�ant propos� peut accepter ou refuser
    - le responsable de garde refuse ou valide le remplacement ce qui met � jour le tableau automatiquement
    - un nouveau menu Gardes > Remplacements permet de lister et rechercher tous les remplacements
4 - Impression des dipl�mes, possibilit� d'imprimer le num�ro d'�v�nement
5 - Ajout d'un deuxi�me pr�nom sur les fiches personnel
6 - Sur le trombinoscope, possibilit� d'affichages suppl�mentaires (2�me pr�nom, date et lieu de naissance, comp�tences, section, fonction)
7 - Possibilit� de choisir directement l'entreprise � l'engagement d'un externe sur une formation
8 - Possibilit�s d'engager une ou plusieurs �quipes sur les interventions
9 - Support MySQL 5.7
10 - Support de la nouvelle API SMS clickatell, tout en conservant le support de l'ancienne
11 - Am�liorations graphiques (boutons bootstrap)
12 - Am�liorations de s�curit� (Cross Frame scripting, http headers)
13 - Modification de l'�diteur d'�v�nements, choix du type de formation
14 - Param�trage sp�cifique de l'impression des dipl�mes possible pour chaque d�partement
15 - Historiques des modifications sur les sections
16 - Ajout de la nationalit� sur les fiches personnel
17 - Possibilit� de voir/modifier l'�quipe sur les renforts
18 - Ajout du d�tail victimes adultes/enfants dans les rapport/bulletins PDF g�n�r�s sur les �v�nements
19 - Possibilit� de trier le personnel inscrit sur la page �v�nement (par Nom, Renfort, Fonction, Equipe ou Statut)
20 - Nouvelle page d'inscription du personnel sur les gardes sapeurs-pompiers, avec des indicateurs et des warnings


==============================================
=   release note 4.1 - d�cembre 2016
==============================================

principales nouveaut�s
----------------------
1 - Am�liorations graphiques (suppressions des popups, nouvelles confirmations d'inscriptions)
2 - Possibilit� de choisir une image de fond sur l'�cran de login
    - mettre l'image dans images/user-specific/splash.jpg
    - quelques exemples dans images (splash1.jpg, splash2.jpg, splash3.jpg)
3 - Possibilit� d'avoir plusieurs responsables par �v�nement
4 - Ajout heure et lieu de rendez-vous sur les �v�nements
5 - Ajout d'un libell� court pour la civilit� (M., Mme. ...)
6 - Possibilit� de d�finir les besoins en v�hicules et mat�riel sur les �v�nements, impression d'un PDF (demande de personnel et de moyens).
7 - Pour les SPP, nouvel �cran (calendrier) pour saisir les repos li�s au r�gime de travail
8 - Possibilit� de choisir un statut pour les b�n�voles inscrits sur les DPS (engag�, dispo base, dispo domicile, repos)
9 - Tableau de garde SP: possibilit� d'afficher ou masquer les horaires
10 - Modification des permissions, pour voir tout le personnel les �v�nements et les v�hicules, il faut maintenant avoir la permission n�40
11 - Disponibilit� pour les SP: possibilit� d'ajouter un commentaire associ� aux disponibilit�s du mois
12 - Marquer NPAI les fiches des personnes pour lesquels l'adresse postale est p�rim�e.
13 - Possibilit� d'inscrire ou pas les chiens de recherche et v�hicules associ�s � un participant en m�me temps que lui m�me
14 - Affichage des SPP de la section du jour sur le graphique du personnel disponible par jour
15 - Bilans annuels PDF (personnel, moyens, activit�) pour les associations
16 - Support d'un nouveau fournisseur de SMS - SMS Mode
17 - Icone alerte dans la barre de menu si il y a des CP � valider
18 - Extraction Excel possible du tableau de garde SP
19 - Support PHP 7.1
20 - Possibilit� de saisir une Google API key pour la g�olocalisation
21 - G�n�ration d'un QR Code contenant les informations de la fiche du b�n�vole


==============================================
=   release note 4.0 - mai 2016
==============================================

principales nouveaut�s
----------------------
1 - Nouveau design responsive bas� sur bootstrap et font-awesome
2 - Support de PHP 7.0
3 - Notification par mail possible si un nouveau message est post� sur le chat
4 - Pour No�l, on peut activer des flocons de neige qui tombent sur la page
5 - Nouvelle permission pour voir le tableau de garde pompiers
6 - Param�trage de l'ordre pour les sections de gardes pompiers

==============================================
=   release note 3.5 - d�cembre 2015
==============================================

principales nouveaut�s
----------------------
1 - El�ments de facture configurables
2 - Passeport du b�n�vole: c'est un document PDF qui peut �tre t�l�charge depuis la fiche personnel et r�capitule les comp�tences et participations.
3 - Horaires du personnel salari�: nouveau champ pour saisir les heures travaill�es hors pointage
4 - Comptes bancaires: am�lioration de la fonction de saisie IBAN avec des contr�les de validit� ajout�s, �limination des RIB
5 - V�hicules: choix d'ic�nes pouvant �tre associ� � chaque type de v�hicule
6 - Bulletin de renseignement quotidien pour les associations de protection civiles
7 - Chiens de recherche et autres animaux: choix d'un ma�tre
8 - Possibilit�s de poster des messages sans limitation de dur�e d'affichage
9 - Nouvelle propri�t� "colonne de renfort" sur les types d'�v�nements, permettant de rattacher les �v�nements par num�ros.
10 - Attestation fiscale au format PDF sur la fiche personnel pour les cotisations des adh�rents (mode gestion des adh�rents).
11 - Possibilit� de marquer les interventions importantes (par d�faut pour DCD et d�tresses vitales)
12 - R�organisation de la page Configuration en 3 onglets (base, avanc�e, s�curit�)
13 - Page �v�nement, possibilit� de filtrer le personnel par jour et de masquer les comp�tences pour une meilleure lisibilit�
14 - Page garde Sapeurs pompiers, possibilit� de filtrer le personnel du jour et de la nuit
15 - Centre d'accueil des victimes
16 - Possibilit� de dupliquer les habilitations
17 - Possibilit� de marquer une garde en anomalie en cas de probl�me de personnel, la ligne appara�t en rouge dans le tableau
18 - Possibilit� d'ajouter un sujet aux messages envoy�s par mail

bugs r�solus
--------------------
- 95 bugs corrig�s

==============================================
=   release note 3.4 - f�vrier 2015
==============================================

principales nouveaut�s
----------------------
1 - S�curit�: contr�le renforc� des permissions
2 - Possibilit� de cr�er ou modifier les types d'�v�nements, en d�finissant en particulier
  * nom du type d'�v�nement et cat�gorie
  * ic�ne
  * onglets � afficher
  * documents � g�n�rer
  * statistiques � renseigner
3 - Possibilit� d'envoyer un mail quand un nouveau message d'information est post�
  * avec pi�ces jointes int�gr�es dans le mail
  * par mailing de masses (supporte plusieurs dizaines de milliers de destinataires)
  * envoi asynchrone via crontab
4 - Notification suppl�mentaire lorsque les disponibilit�s d'un utilisateur ont �t� saisies
5 - Support de 2 nouveaux fournisseurs de SMS: envoyersmspro.com et SMS Gateway Android 
  * Doc pour envoyersmspro.com ici http://ebrigade.sourceforge.net/wiki/index.php/SMS#envoyersmspro
  * SMS Gateway est gratuit et permet d'envoyer jusqu'� 100 SMS par heure, ou plus on configurant Android avec root.
  * Cette solution n�cessite d'avoir:
    - un smartphone Android d�di� 
    - un forfait SMS illimit�
    - une box ADSL configur�e pour accepter les connections http entrantes et les rediriger sur le smartphone
    - tout est document� ici http://ebrigade.sourceforge.net/wiki/index.php/SMS#SMS_Gateway_Android
6 - Possibilit� d'affecter le mat�riel � une �quipe sur les �v�nements
7 - Fonction Mains courantes
  * acc�s en lecture restreint aux personnes inscrites
  * acc�s en �criture limit� aux personnes inscrites avec la fonction "R�dacteur"
8 - Nouveau menu de param�trage
9 - Identification de la commune par le zipcode sur la page section, comme pour le personnel. N�cessite de charger le fichier sql/zipcode.sql
10 - Reporting activ� pour les casernes SP.
11 - Notification aux personnels SP quand le tableau de garde est pr�t.
12 - Ic�ne de pays � c�t� des num�ros de t�l�phone. 
13 - Nouvelle page Documents / t�l�chargements


==============================================
=   release note 3.3 - d�cembre 2014
==============================================

principales nouveaut�s
----------------------
1 - S�curit� accrue (protection upload et XSS)
2 - Changements graphiques (rounded corner tables, 12 themes de couleurs suppl�mentaires, logo eBrigade)
3 - Ajout de Web services (user, sections, formations), voir soap.php
4 - Notifications param�trables par chaque utilisateur
5 - Hi�rarchies de comp�tences
6 - Gestion des Jeunes Sapeurs-Pompiers (nouveaux grades sp�cifiques)
7 - Expiration param�trable des sessions utilisateurs
8 - Stock minimum sur les consommables
9 - Gestion des fonctions pour les v�hicules  engag�s sur les �v�nements
10 - Mode astreinte pour les Sapeurs-Pompiers de garde
11 - Ajout du responsable l�gal et de la date d'envoi sur les conventions
12 - Optimisation de la sauvegarde permettant une recharge plus rapide des backups
13 � G�n�ration d�un PDF pour chaque intervention comme on avait d�j� pour chaque victime
14 � Re�u d�adh�sion
15 � Ajout d�un lien vers la documentation dans le mail de cr�ation de compte.
16 - Ajout du responsable l�gal et de la date d'envoi sur les conventions

bugs r�solus
--------------------
- 43 bugs corrig�s


=============================================
=   release note 3.2 - ao�t 2014
==============================================

principales nouveaut�s
----------------------
1 - Base de donn�es optionnelle des codes postaux (pour activer, charger sql/zipcode.sql)
2 - Note de frais d�partementale
3 - Comptes SMS par d�partement
4 - Conditions d'utilisation � accepter
5 - Gestion de la dotation vestimentaire (habillement)
6 - Dates sp�cifiques sur la convention
7 - Support PHP 5.6: remplacement de mysql par mysqli
8 - Documentation wiki http://ebrigade.sourceforge.net/wiki
9 - Upgrade FPDI
10 - Acomptes sur devis et factures

bugs r�solus
--------------------
- 28 bugs corrig�s

==============================================
=   release note 3.1 - f�vrier 2014
==============================================

principales nouveaut�s
----------------------
1 - Ajout d'ic�nes pour les grades Sapeurs Pompiers
2 - Nouveau tableau de gardes Sapeurs Pompier
   * chaque garde est un �v�nement, avec toutes les possibilit�s associ�es
   * param�trage am�lior� des types de gardes, avec ic�nes sp�cifiques, horaires pr�cis
   * bouton cr�er/ supprimer le tableau de garde du mois
   * affichage du tableau par mois ou par semaine
   * possibilit� d'enregistrer les interventions et victimes sur les gardes
   * les fonctions (piquets) sont devenus facultatifs
   * icones d'erreur si une personne de garde est indisponible, dej� engag�e ou non qualifi�e pour sa fonction
   * le personnel SPP peut �tre automatiquement engag� sur le tableau au moment de la cr�ation
   * l'engagement du personnel SPV sur chaque garde est simplifi� et plus rapide
3 - Disponibilit�s du personnel en 1, 2, 3 ou 4 tranches horaires. Par d�faut, 2 tranches jour et nuit.
4 - Ajout pagination sur l'onglet cotisations de la fiche personnel
5 - R�organisation des dossiers contenant les fichiers
   * Par d�faut tout est maintenant stock� dans le r�pertoire user-data au lieu d'�tre � la racine du site
   * diplomes, documents,files, files_message, files_section, files_vehicule, save
6 - Possibilit� d'enregistrer plusieurs trac�s de couleurs sur la carte SITAC Google Maps.

bugs r�solus
--------------------
- 42 bugs corrig�s   


==============================================
=   release note 3.0 - d�cembre 2013
==============================================

principales nouveaut�s
----------------------
1 - Carte Google maps avec la situation tactique (SITAC)
2 - Fiche bilan secouriste / m�dical compl�te pour les victimes
3 - Historique d�taill� de tous les changements r�alis�s sur une fiche personnel
4 - Gestion des virements vers les comptes bancaires des adh�rents
5 - Mise � jour des types d'interventions
6 - Enregistrement des BIC / IBAN en plus des RIB
7 - Rejets de pr�l�vements en cours de r�gularisation (repr�sentation du montant le mois suivant)
8 - S�curit� accrue:
  * Stockage des adresses IP dans l'audit des connexions
  * Obligation d'avoir un caract�re sp�cial dans les mots de passe
  * V�rification javascript de la qualit� des mots de passes
  * Encryption des mots de passe au choix MD5 ou PBKDF2 (plus s�curis�)
9 - Possibilit� d'imprimer un PDF de la fiche intervention et de la fiche victime
10 - Localisation GPS des utilisateurs connect�s
12 - Recherche du personnel par comp�tence sur les carets Google Maps
13 - Meilleure gestion des homonymes lors de la cr�ation d'une fiche personnel
14 - Gestion des produits consommables (param�trage, stock, consommation sur les �v�nements)
15 - Param�trage des types de v�hicules
16 - Nombreux nouveaux reportings
17 - Reorganisation du code javascript, Prototype supprim�, tout est en jQuery
18 - Caserne SPP : gestion du temps de travail en 24/48 ou 24/72

bugs r�solus
--------------------
- 265 bugs corrig�s 

==============================================
=   release note 2.9 - mai 2013
==============================================

principales nouveaut�s
----------------------
1 - Gestion des notes de frais pour le personnel
2 - Gestion des horaires du personnel salari�
3 - Gestion des remboursements (comme les cotisations)
4 - Gestion des types d'interventions sur la main courante
5 - Possibilit� de cr�er des dossiers pour classer les documents
6 - Mode maintenancce de l'application (seul admin peut se connecter)
7 - Ajout du nombre de places stagiaires sur les formations
8 - Possibilit� de cr�er des fiches pour les animaux
9 - Nouvelles statistiques sur les �v�nements
10 - Audit des changements de permissions du personnel
11 - Utilisation de PHPExcel pour g�n�rer tous les documents Excel
12 - Modifications de l'affichage de l'organigramme (mode liste et mode organigramme)
13 - Ajout de la signature du pr�sident, qui appara�t sur les PDF g�n�r�s.
14 - Possibilit� de champs personnalis�s sur la fiche personnel (ins�rer dans la table custom_fields)
15 - Test sur la taille des fichiers a uploader

bugs r�solus
--------------------
- 115 bugs corrig�s 


==============================================
=   release note 2.8 - ao�t 2012
==============================================

principales nouveaut�s
----------------------
1 - Gestion des cotisations du personnel, avec param�trage de la cotisation par d�faut par d�partement.
2 - Gestion des comptes bancaires du personnel (RIB) pour les cotisations par pr�l�vement
3 - Gestion de la main courante, des interventions et des victimes, avec statistiques g�n�r�es
    * Un rapport PDF de main courante peut �tre imprim� pour �tre remis � l'organisateur ou � la pr�fecture
    * Attention: l'enregistrement du dossier m�dical est soumis � des contraintes CNIL
    http://www.cnil.fr/la-cnil/actualite/article/article/securiser-les-donnees-de-sante-dans-les-applications-en-reseau
    * C'est pourquoi un nouveau param�tre de configuration "store_confidential_data" a �t� ajout�, qui interdit par d�faut de stocker l'identit� compl�te des victimes
    * une dizaine de rapports donnent des statistiques sur les victimes (�ge, nationalit�, transports , type d'intervention ... )
    * les anciennes statistiques sur op�rations de secours existent toujours mais sont automatiquement renseign�es � partir des interventions saisies.
4 - Nouvelle possibilit�s pour formater les messages d'information ou consignes (couleurs, polices de caract�res ...), gr�ce � tinymce
5 - Possibilit� d'ajouter des documents sur les v�hicules
6 - Nouvelles donn�es sur la fiche personnel: en particulier civilit�
7 - Factures individuelles sur les formations
8 - Possibilit� de bloquer les modifications sur les �v�nements du pass�, au-del� d'un nombre de jour configurable
9 - Gestion des organisations de type syndicat
10 - Historisations suppl�mentaires d'actions r�alis�es
11 - Liste des �v�nements: enregistrements des favoris � plusieurs niveaux diff�rents de l'organigramme
12 - Carte Google maps du personnel et des �v�nements
13 - duplication multiple des �v�nements (par exemple tous les mercredis pendant 8 semaines)
14 - Utilisation de la classe PHPMailer pour l'envoi de mails.

bugs r�solus
--------------------
- 61 bugs corrig�s 

==============================================
=   release note 2.7 - janvier 2012
==============================================

principales nouveaut�s
----------------------
1 - Possibilit� de changer le th�me de couleurs
2 - G�n�ration des conventions DPS (format PDF)
3 - Notifications de rappel la veille de l'�v�nement
4 - Gestion des astreintes
   * Pour chaque r�le de l'organigramme, on peut d�finir si une gestion d'astreinte est associ�e
   * Nouvelle page 'Astreintes' o� on peut d�finir un tableau d'astreintes, dates libres.
   * chaque matin un t�che cron change l'organigramme en fonction du tableau d'astreintes.
5 - Les fonctionnalit�s 3 et 4 n�cessitent d'avoir acc�s � la crontab du serveur (Linux)
   * configuration du param�tre eBrigade cron_allowed = 1
   * les scripts shells astreintes_updates.sh et reminder.sh doivent �tre ajout�s dans la crontab
   * exemple: envoi d'une notification � 18h15 aux participants la veille de l'�v�nement
     15 18 * * * /var/www/vhosts/mydomain.org/httpdocs/reminder.sh
6 - Nouvelle carte 'Alerte des b�n�voles'
7 - Les mails de notifications sur les �v�nements ou demandes de cong�s contiennent un lien URL vers la bonne page
8 - Possibilit� d'ajouter des documents sur la fiche personnel.
9 - Formats de dates uniformis�s JJ-MM-AAAA
10 _ Ajout graphiques ChartDirector
    * statistiques d�taill�es sur les DPS
    * Nombre de formations par an
11 - FPDF upgrade 1.7
12 - Ajout fonction de Recherche de documents par nom
13 - Affichage des anniversaires � souhaiter
14 - Fonction de recherche dans le texte des messages post�s
15 - Possibilit� de dupliquer du mat�riel
16 - Ajout planning du personnel
17 - iPhone: Envoi vcards par email


bugs r�solus
--------------------
- 38 bugs corrig�s



==============================================
=   release note 2.6 - juillet 2011
==============================================

principales nouveaut�s
----------------------
1 - Cartes de France montrant l'activit� op�rationnelle, utilisation France Map (v3.0). 
2 - Optimisation des performances de l'application, stockage des permissions dans la session
3 - Pagination
4 - D�coupage des �v�nements � jours multiples
  Ceci permet notamment de cr�er un �v�nement et de le d�couper en plusieurs parties. ( Formation, DPS, R�union etc....) 
  Il est possible d' inscrire le personnel, en partie ou en totalit� sur les parties.('Petite horloge' de couleur modifiable)
  Pour chaque b�n�vole il est possible de personnaliser les horaires. 
  Les documents g�n�r�s y compris la facturation et les reports prennent en compte le d�coupage. 
5 - Propri�t� 'DPS inter-associatif :' 'Coche' � activer dans la page de cr�ation du DPS ainsi que 
6 - Deux possibilit�s suppl�mentaires de duplications d'�v�nements (personnel seulement, et mat�riel/v�hicules seulement)
7 - Lien Google Maps sur les �v�nements et les fiches personnel
8 - Date d'expiration des comp�tences
  - sur l'onglet dipl�mes de la page �v�nement formation, ajout de dates d'expirations pour chaque personne 
  - ceci permet de mettre des dates de validit� diff�rentes selon les participants 
  - ces dates sont affich�es sur l'attestation de formation 
  - la validit� de la comp�tence est prolong�e si la nouvelle date est post�rieure � la date de fin de validit� actuelle
9 - Ev�nement public et flux RSS
  Il est possible de cocher un �v�nement et de le rendre 'public'. 
  Cette case a cocher permet d'alimenter un flux rss auquel il est possible de s'abonner avec un lecteur de flux rss, 
  mais aussi que chaque webmestre peut r�cup�rer et int�grer dans son site internet. Voir documentation
  http://ebrigade.sourceforge.net/wiki
10 - Historique des actions r�alis�es
  - pour les personnes habilit�es, une nouvelle ic�ne apparait � droite en forme de loupe sur les fiches du personnel 
  - en cliquant sur l'ic�ne, on ouvre la page historique pour la personne concern�e 
  - On peut ainsi savoir qui a fait les modifications suivantes et quand: 
   . Ajout d'une fiche personnel 
   � Modification de fiche personnel 
   � Suppression d'une fiche personnel 
   � Modification de mot de passe 
   � Reg�n�ration de mot de passe 
   � Changement de section 
   � Changement de position 
   � Inscription 
   � D�sinscription 
   � Commentaire sur inscription 
   � Modification Fonction 
   � Ajout comp�tence 
   � Modification comp�tence 
   � Suppression comp�tence
   . Modification de disponibilit�s
11 - Lien Skype
12 - Km v�hicules personnels 
13 - D�finition des comp�tences demand�es sur l'�v�nement.
  Sur la page �v�nement, on peut maintenant d�finir le nombre de personnes demand�es au global et pour chaque comp�tence. 
  Et ceci pour chaque partie de l'�v�nement. Il est possible de modifier les informations en cliquant sur l�ic�ne blanche en fin de ligne.
  Lorsqu'il y a assez de personnes inscrites, alors l'info apparait en vert. Sinon en rouge.
14 - Gestion des �quipes
  On peut maintenant d�finir sur chaque �v�nement (ou renfort) des �quipes, puis affecter le personnel inscrit sur ces �quipes. 
  En cliquant sur l'onglet �quipes de la page information d'un �v�nement, une nouvelle fen�tre s'ouvre qui permet d'ajouter, modifier ou supprimer des �quipes. 
  Le nombre indique combien de personnes ont �t� affect�es � chaque �quipe. 
  Puis sur l'onglet personnel on peut maintenant affecter le personnel � une des �quipes d�finies. 
  Vous pouvez utiliser cette fonctionnalit� seulement sur l'�v�nement principal ou renfort ou sont inscrits les personnels.
15 - Lot de mat�riel
  Cette fonctionnalit� permet, avec votre mat�riel existant, de l'associer dans un lot unique, lui m�me pouvant �tre associ� � un v�hicule, 
  et enfin l'ensemble �tant engag� sur l'�v�nement : soit en engageant le lot, soit en engageant le v�hicule

bugs r�solus
--------------------
- 65 bugs corrig�s 


==============================================
=   release note 2.5 - d�cembre 2010
==============================================

principales nouveaut�s
----------------------
1 - gestion d'une pr�sence partielle du personnel sur les �v�nements
2 - impression des dipl�mes et duplicata de dipl�mes
3 - impression des attestations de formations
4 - impression des ordres de mission
5 - Messagerie instantan�e (chat) + liste des utilisateurs connect�s
6 - Support IPhone / IPad am�lior�
7 - gestion du personnel externe (qui peut suivre des formations de secourisme)
8 - gestion des entreprise clientes
9 - attribution de fonctions au personnel sur les �v�nements et param�trage des fonctions possibles
10 - tra�abilit� accrue des actions importantes des utilisateurs
11 - performances am�lior�es sur les pages �v�nement, sections
12 - v�hicule et mat�riel affect�s � une personne
13 - nombreuses statistiques dans les graphiques Chartdirector, dont l'installation est document�e ici
http://ebrigade.sourceforge.net/wiki/index.php/Graphiques
14 - certaines comp�tences peuvent �tre modifiables par chaque utilisateur (exemple vaccinations)
15 - ajout de description sur les fonctionnalit�s
16 - Nouveau d�tecteur de navigateurs et OS
17 - gestion des absences possible par heures
18 - duplication des �v�nements (simple ou compl�te avec le personnel, les v�hicules et le mat�riel)
19 - choix de type de contrat et horaire pour le personnel salari�
20 - participation du personnel salari� en tant que b�n�vole ou salari� sur les �v�nements
21 - gestion s�curis�e des documents avec cat�gories et permissions d'acc�s
22 - possibilit� de bloquer la saisie des disponibilit�s pour un mois donn�
23 - cat�gories de messages (normal, informatique, urgent)
24 - gestion des photos d'identit�, fonction de recadrage
25 - possibilit� d'acc�s en lecture seule � l'application ebrigade
26 - gestion du mail secr�tariat pour chaque section, qui re�oit toutes les notoifications
27 - choix des comp�tences devant �tre affich�es sur les �v�nements
28 - cat�gories d'�v�nement (classification)
29 - D�finition du type de DPS (PAPS, PE,ME,GE) Contr�le des agr�ments DPS
30 - am�lioration de l'impression des badges.
31 - possibilit� d'activer/d�sactiver presque toutes les fonctionnalit�s dans le menu Configuration
32 - cartes de france interactive pour localiser le personnel et l'activit� en cours
33 - Nouvelle documentation en ligne

bugs r�solus
--------------------
- 192 bugs corrig�s 


==============================================
=   release note 2.4 - d�cembre 2009
==============================================

principales nouveautes
----------------------
1- changements graphiques divers, remplacement d'icones et utilisation d'onglets sur les pages personnel, evenement et section.
2- nouvelle fonction de recherche (par nom, par ville, par tel, par competences, par habilitation)
3- gestion du materiel 
 * configurer les types de materiel (parametrage / type de materiel)
 * saisir le materiel dans l'inventaire (inventaire / materiel)
 * ajouter du materiel sur les evenements
4 - comptabilite / facturation
 * cliquer sur l'icone en forme de calculatrice sur la page de chaque evenement pour acceder � ces fonctions
 * edition des devis et factures en format pdf
5 - Gestion des badges
6 - Ajout des membres dans les contacts Windows (cliquer sur l'icone carte de visite sur la fiche personnel)
7 - Export du personnel vers fichier CSV
8 - Export de la fiche evenement sous Excel
9 - Gestion des formations du personnel
10 - Evenements renforts
11 - Export des calendriers au format ical (clique sur l'icone calendrier de la page d'un evenement)
12 - Organigramme de la section modifie avec de nouveaux roles
13 - ajout information sur le sexe masculin/feminin des membres
14 - Photos d'identite
15 - securite accrue (contre injection sql, meilleure confidentialite des informations techniques du systeme)
16 - Gestion des agr�ments de s�curit� civile
17 - Gestion des competences dans le cadre des casernes de sapeurs pompiers
18 - Gestion des roles dans l'organigramme, avec des permissions associees

changements mineurs
------------------
- plus de distinction prioritaire/non prioritaire pour le personnel inscrit a un evenement, mais couleur specifique pour les externes.
- affichage de la documentation a partir du menu aide, date des documents
- support de PHP 5.3.0

principaux bugs resolus
--------------------
- permissions parfois incorrectes sur la gestion des evenements

==============================================
=   release note 2.3 - d�cembre 2008
==============================================

principales nouveautes
----------------------
1 - gestion de plusieurs niveaux hierarchiques de sections dans la meme base de donnees. 
   * exemple: plusieurs centres de secours, plusieurs ADPC.
   * L'application est maintenant utilisable pour un nombre de membres tres important.
   * Gestion d'un organigramme de sections (exemple: national, zonal, regional, departemental, local)
   * les droits d'un utilisateurs sur l'application sont maintenant restreints a sa section (+ ses sous-sections)
   * exemple: un utilisateur ayant le droit de modifier le personnel peut le faire pour le personnel de sa section
    et de ses sous-sections mais pas des autres.
   * exemple 2: un utilisateur ayant le droit 'gestion des vehicules' peut ajouter nu vehicule dans sa section mais 
    pas dans une section d'un niveau superieur.
   * un evenement est visible dans le calendrier par les membres de la section organisatrice et ceux des sous-sections
   * la fonctionnalite "evenement exterieur" a ete ajoutee et permet a celui qui est authorise de creer 
    des evenements en dehors de sa section.
   * un message cree par un agent est visible par les membres de sa section et de ses sous-sections
   * les messages sont tries en fonction des destinataires

2 - l'identifiant ou matricule peut etre maintenant une combinaison de chiffres et lettres au lieu de numerique seulement.

3 - chaque utilisateur peut soi-meme modifier son identifiant (menu Session / Mes Infos).

4 - un responsable peut etre designe pour chaque evenement, il a alors tous les droits sur l'evenement.

5 - securite: les informations personnelles ne sont visibles que par les chefs de sections
    ou par les personnes habilitees a modifier le personnel.

6 - notion de cadre de permanence pour chaque section. Il recoit des notifications pour son secteur. 
    Des droits particuliers et temporaires peuvent etre automatiquement ajoutes.

7 - ajout d'une interface de configuration de la connexion a la base de donnees

8 - possibilite de definir des dates d'expiration des competences et un audit des changements de competences.

9 - fonctionnalite 'Securite locale' permettant de changer les groupes des utilisateurs (sauf admin) et de changer les mots de passe pour sa section.

10 - installation simplifiee: chargement automatique du schema de reference.

11 - upgrade automatique de la base de donnees.

12 - fonction mot de passe perdu, avec regeneration et envoi d'un mail.

13 - securite accrue configurable (longueur et qualite des mots de passes, lock apres X erreurs)

14 - Reporting et statistiques

14 - Gestion des heures de participation effective.
Si un evenement se deroule sur plusieurs jours, il faut pouvoir indiquer le nombre d'heures effectives de presence / activite.
La fiche evenement permet de definir la valeur par defaut pour l'evenement.
(ex : la formation PSC1 est proposee du Samedi 08h00 au Dimanche 12h00, mais le temps reel de formation et de 10 heures.)
La fiche participation permet de modifier le temps de presence des participants

changements mineurs
------------------
- ajout d'information (section et groupe) sur la page 'Mes Infos'
- stockage des fichiers attaches aux messages dans des sous repertoires specifiques.
- ajout d'un identifiant unique M_ID dans la table message.
- ne pas creer un repertoire pour les fichiers attaches des evenements tant qu'il n'y a pas de pieces jointes
- suppression d'une personne, les messages sont reaffectes a ADMIN au lieu d'etre perdus.
- ajout d'un lien vers chaque personne ou chaque vehicule dans la fiche d'un evenement.
- jusqu'a 99 soins / evacuations par DPS
- ajout des parametres de configuration auto_backup et auto_optimize
- sur la page configuration, grades et gardes sont incompatibles avec nbsections 0
- affichage de la liste des membres d'un groupe d'habilitations
- ajout de confirmations avant envoi de mais, d'alertes ou d'inscriptions a des evenements.
- affichage des destinataires d'un mail d'alerte.
- boutons d'envois d'email a partir des fiches personnel ou evenements
- affichage des prochaines inscriptions dans la fiche personnel
- selection de tous les jours ou toutes les nuits dansla page dispos
- calendrier: seules les inscriptions sont affichees
- calendrier: affichage du calendrier des autres utilisateurs possible

bugs resolus
------------
- bug sur PHP 5 (definition du type de fichier avec la balise <?php )
- bug MySQL 5 ( changements lies a la longueur de la cha�ne encrytee pour le mot de passe)
- suppression des personnes avec un espace dans le nom ou le prenom
- format urlencoded dans les emails ( en particulier mauvais affichage des apostrophes)
- identifiant=0 ne doit pas etre autorise, ajout d'un contr�le sur la valeur identifiant ou matricule
- modification d'un evenement : perte des infos sur le nombre de soins et d'evacuations
- permissions 'modifier' et 'supprimer' inversees dans la gestion du personnel
- ajouts de checks sur les adresses email
- impossible de supprimer un vehicule si il y a des espaces dans l'immatruculation
- limitation du nombre de destinataires des emails pour eviter les erreurs sur la fonction mail limitee.
- support des apostrophes dans certains champs de saisie.

==============================================
=   release note 2.2
==============================================

liste des nouveautes
----------------------
1 - Envois de sms ( utilisation d'un compte au choix )
    * parametrage du numero de compte dans la page configuration
    * habilitation configurable pour "envoyer des SMS"
    * 3 fournisseurs de SMS sont utilisables ( clickatell est vivement recommande, le meilleur et le moins cher).

2 - Envoi d'alertes a une categorie de personnel selon qualification et / ou section d'appartenance.


changements mineurs
------------------
- affichage page personnel

bugs resolus
------------
- affichage evenement si habilite 17 mais pas 15
- ajout de contr�les sur l'identifiant (ou matricule)
- filtrage du texte des evenements ( suppression des balises HTML et des ")

==============================================
=   release note 2.1
==============================================

liste des nouveautes
----------------------
1 - ajout type d'evenement: Divers
2 - ajout du type d'evenement "Instructeur pour une formation"
    ces evenements ne sont visibles que par  le personnel 
    ayant une qualification de type 'PAE*'
3 - ajout d'un filtre par jour sur Vehicules/Engagements


==============================================
=   release note 2.0
==============================================

liste des nouveautes
----------------------

1 - evenements
-- possibilite pour l'administrateur d'ajouter et enlever du personnel
-- on ne peut plus se desinscrire
-- ajouter des vehicules sur des evenements
-- possibilite de cloturer les inscriptions ( ou d'ouvrir les inscriptions)
-- ajout d'informations (section organisatrice, numero) 
-- gestion de la riorite du personnel et de l'order d'inscription
-- filtre par section sur les evenements
-- ajout d'une date de fin dans le cas d'un evenement sur plusieurs jours (date de fin facultative)

2 - disponibilites
-- disponibilites par jour: ajout d'un filtre de tri par section et par qualification
-- disponibilites par jour: amelioration ergonomie du tableau
-- disponibilites jour/mois: split dispos mois et jour en 2 pages            
-- disponibilites du mois : ajout liste deroulante choix le jour ou la nuit.

3 - grille de depart par defaut modifiable. Cette grille conditionne les piquets dans "Garde du jour".
4 - configuration possible des sections (ajout, modif, suppression)
5 - parametrage possible des habilitations
6 - notifications par email ( lies aux evenements et aux demandes de CP)
7 - amelioration de l'affichage du calendrier
8 - remplacement des titres de la page d'accueil
9 - ajout de l'ecran engagement des vehicules
10 - purge glissante disponibilite et planning_garde
11 - lien dans mon calendrier vers garde du jour
12 - possibilite pour un agent de modifier son email et son numero de telephone
13 - masquage des grades parametrable
14 - Au lieu de poste de garde, on utilisera dans le cas ou il n'y a pas de garde le terme competence.
16 - configuration dans la base de donnees ( suppression du fichier config_param)


liste des principaux bugs resolus
---------------------------------
1 - crash si ajout de pieces jointes de plus de 5M 
2 - corrections des bugs firefox ( envoi email et graphiques)
3 - correction d'un bug dans le texte des emails pour les apostrophes ou apparaissait "\'" au lieu de '
4 - impossible de creer le premier evenement 

==============================================
=   release note 1.7
==============================================
1 - affichage des parametres de configuration
2 - configuration des equipes


==============================================
=   release note 1.6
==============================================
1 - possibilite de desactiver les menus vehicules, gardes.
2 - configuration des postes

==============================================
=   release note 1.5
==============================================
1 - histogrammes des disponibilites

==============================================
=   release note 1.4
==============================================
1 - possibilite de gestion sans sections ou en 3 sections (parametrable)
2 - sauvegarde automatique declenchee a la premiere connexion du jour
3 - correction de bug : page modification manuelle, lorsque la personne de garde supprime sa disponibilite.
4 - generation d'un script pour creer une nouvelle base (configuration par defaut)
5 - possibilite de decouper la journee en matin et apres midi ou pas (parametrable)

