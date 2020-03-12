function orderfilter(p1,p2,p3,p4,p5){
	var mad = document.getElementById('mad');
	if (mad.checked) m = 1;
	else m=0;
	self.location.href="materiel.php?order="+p1+"&filter="+p2+"&type="+p3+"&subsections="+p4+"&old="+p5+"&mad="+m;
	return true
}

function orderfilter2(p1,p2,p3,p4,p5){
 	if (p4.checked) s = 1;
 	else s = 0;
	var mad = document.getElementById('mad');
	if (mad.checked) m = 1;
	else m=0;
	self.location.href="materiel.php?order="+p1+"&filter="+p2+"&type="+p3+"&subsections="+s+"&old="+p5+"&mad="+m;
	return true
}

function orderfilter3(p1,p2,p3,p4,p5){
 	if (p5.checked) s = 1;
 	else s = 0;
	var mad = document.getElementById('mad');
	if (mad.checked) m = 1;
	else m=0;
	self.location.href="materiel.php?order="+p1+"&filter="+p2+"&type="+p3+"&subsections="+p4+"&old="+s+"&mad="+m;
	return true
}

function displaymanager(p1){
	self.location.href="upd_materiel.php?mid="+p1;
	return true
}

function bouton_redirect(cible) {
	self.location.href = cible;
}
