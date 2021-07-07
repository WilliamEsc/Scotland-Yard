
<main class="main">
	<div class="Accueil">
		<form method="POST" action="#">
					<p> nom du joueur : <INPUT type="text" name="Player" value="nom" size="10"> </p> 
					<p> 
						Vous êtes : 
						<select name="nbBot">
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
						</select>
					</p>
					<p>
						<select name="nomCf">
							<option value="bas">basique</option>
							<option value="eco">econome</option>
							<option value="pis">pistage</option>
						</select>
					</p>
					<input type="hidden" name="NewGame" value="init" />
					<input type="hidden" name="PAGE" value="Game" />
					<input type="submit" value="Demarrer" />
		</form>
	</div>
</main>
