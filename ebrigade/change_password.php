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
<script>
var minPasswordLength = <?php echo intval($password_length); ?>;
var passwordQuality = <?php echo intval($password_quality); ?>;
</script>
<?php
forceReloadJS('js/password.js');
echo "</head>";

$id=$_SESSION['id'];
$mysection=intval($_SESSION['SES_SECTION']);

if (isset($_GET["pid"])) $pid=intval($_GET["pid"]);
else $pid=$id;
if ( $pid == 0 ) $pid=$id;

$section = get_section_of($pid);

if ( $id <> $pid ) {
    if ( ! check_rights($id, 9)) check_rights($id, 25);
    if ( ! check_rights($id, 9, $section)) check_rights($id, 24);
}

echo "<body>";
echo "<div align=center><table class=noBorder>
      <tr><td width = 60 ><i class='fa fa-key fa-2x'></i></td><td>
      <font size=4> <b>Changement de mot de passe</b></font></td></tr></table>";
      
echo "<p>";
echo "<table cellspacing=0 border=0>";
echo "<form name='change_pwd' action='save_password.php' method='POST'>";
print insert_csrf('change_password');

if ($pid > 0 ) $msg = "Pour ".ucfirst(get_prenom("$pid"))." ".strtoupper(get_nom("$pid"));
else $msg="Choix mot de passe";

echo "<tr>
      <td width=300 colspan=2 class=TabHeader>$msg</td>
      </tr>";
echo "<input type='hidden' name='pid' id ='pid' value='$pid'>";

//=====================================================================
// ligne nouveau password
//=====================================================================

echo "<tr bgcolor=$mylightcolor height=30>
        <td width=150  align=right>Nouveau mot de passe</b></font></td>
        <td width=150 align=center><input type='password' name='new1' id='new1' size='13' autocomplete='OFF' autofocus></td>";
echo "</tr>";
echo "<tr bgcolor=$mylightcolor height=30>
        <td align=right >Répétez</font></td>
        <td align=center><input type='password' name='new2' id='new2' size='13' autocomplete='OFF'></td>";
echo "</tr>";

echo "</table>
<p><div class='' id='passwordStrength'></div><p>"; 
echo "<input id='sauver' type='submit'  class='btn btn-default' value='sauver' disabled>";
echo " <input type=button value='retour'  class='btn btn-default' onclick='javascript:history.back(1);'> </form>";

writefoot();
?>
