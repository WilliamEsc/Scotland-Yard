<?php
function afficheStatBase($connexion)
{
    $tab=getStatDB($connexion);
    echo "<p>";
    foreach($tab as $key=>$value)
    {
        echo "La table ".$key." contient ".$value." tuples!      ";
    }
    echo "</p>";
}

function afficherDivStat($connexion)
{
    echo "<div class =stat>";

    $nomJ = getNomJoueur($connexion);
    $tab=array( 'nomJ' => array('nul','nul','nul'), 'nbV' => array(0,0,0));
    foreach($nomJ as $n)
    {
        if($tab['nbV'][2]<$n['nbV'])
        {
            if($tab['nbV'][1]<$n['nbV'])
            {
                if($tab['nbV'][0]<$n['nbV'])
                {
                    $tab['nomJ'][2]=$tab['nomJ'][1];
                    $tab['nbV'][2]=$tab['nbV'][1];
                    $tab['nomJ'][1]=$tab['nomJ'][0];
                    $tab['nbV'][1]=$tab['nbV'][0];
                    $tab['nomJ'][0]=$n['nomJ'];
                    $tab['nbV'][0]=$n['nbV'];
                }
                else
                {
                    $tab['nomJ'][2]=$tab['nomJ'][1];
                    $tab['nbV'][2]=$tab['nbV'][1];
                    $tab['nomJ'][1]=$n['nomJ'];
                    $tab['nbV'][1]=$n['nbV'];
                }
            }
            else
            {
                $tab['nomJ'][2]=$n['nomJ'];
                $tab['nbV'][2]=$n['nbV'];
            }
        }
    }
    echo "<table>";
    echo "<tr>";
    echo "<th scope=col>Joueurs/Joueuses</th>";
    echo "<th scope=col>nombre partie</th>";
    echo "<th scope=col>Victoire</th>";
    echo "<th scope=col>Defaite</th>";
    echo "</tr>";
    foreach($nomJ as $n)
    {
        echo "<tr>" ;
        echo "<th scope=row>";
        echo $n['nomJ'];
        echo "</th>";
        echo "<td>";
        echo $n['nbP'];//partie joué
        echo "</td>";
        echo "<td>";
        echo $n['nbV'];
        echo "</td>";
        echo "<td>";
        $def=$n['nbP']-$n['nbV'];
        echo $def;//defaite
        echo "</td>";
        echo "</tr>" ;
    }
    echo "</table>";
    echo "<p>Meilleur Joueur ".$tab['nomJ'][0]." avec ".$tab['nbV'][0]." Victoires</p>";
    echo "<p>Deuxieme Joueur ".$tab['nomJ'][1]." avec ".$tab['nbV'][1]." Victoires</p>";
    echo "<p>Troisieme Joueur ".$tab['nomJ'][2]." avec ".$tab['nbV'][2]." Victoires</p>";
    echo "<br>";
    echo "</div>";
}
?>
