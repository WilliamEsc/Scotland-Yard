
<main class="main">
	<div class="tableQ">
	<table>
	<?php AfficherTabQuartier($connexion);?>
	</table>
	</div>
	<?php
		if($_SESSION['Victoire'])
		{
			incrementVictoire($connexion);
		echo "<p>Victoire!</p>";
		}
		elseif($_SESSION['Defaite'])
		{
		echo "<p>Defaite....</p>";
		}
		else{
		if ($_SESSION['nomCf'] == "eco"){
			menuDeplacementJoueurEconome($connexion);}
		elseif ($_SESSION['nomCf'] == "pis"){
			menuDeplacementJoueurPistage($connexion);}
		else{
			menuDeplacementJoueurBasique($connexion);}
		}
		?>
</main>
