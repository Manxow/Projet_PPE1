<?php

// soyons fainéants, automatisons la génération de code avec le moins de répétitions possibles... et le moins de maintenance en cas d'évol DB

/////////////////
// partie SQL
//... je charge la ligne de l utilisateur dans la DB
//... disons que j obtiens :
$aLigneSQL = [
	'nom' => 'DUPONT',
	'...' =>... ,
	// et surtout :
	'niveau' => 'debutant'    // parmi les seuls valeurs tolérées en DB, disons 'debutant', 'intermediaire' et 'expert'
];




///////////////
// Génération automatique de certains champs un peu chiant pour alimenter le template html ensuite
// objectifs : à partir d'une conf des variables possibles, je réduirai au minimum les endroits à changer dans mon code pour que tout soit cohérent
//			surtout si par exemple demain j'ai un 4è niveau, 5è niveau....

// dans ce fichier ou plutot un fichier de références de constantes (genre inc.const.php)
const SQL_NIVEAU_POSSIBLE = [
	'debutant',
	'intermediaire',
	'expert',
	// 'un_jour_autre_chose_valide_dans_la_base_pour_ce_champ',
];

// et là, je sais dans mon code d'affichage de profil utilisateur (pour lecture ou modification), que je dois générer à un moment des "radio" permettant
// de choisir les 3 (plus tard 4, 5...) valeurs possibles
// MAIS sans me soucier de combien de valeurs sont possibles

$aCheckedOuPas = []; // on stockera "checked" si la case est à checked à l'affichage ; il pourrait y en avoir plusieurs si checkbox, osef
$aLignesHTMLRadioNiveau = []; // je mets exprès des noms débiles pour qu'on sache à quoi sert la variables

// on init à "pas checked" :
foreach (SQL_NIVEAU_POSSIBLE as $n)
	$aCheckedOuPas[$n] = ''; // ainsi peu importe qu'il y ait 3 ou X valeurs radio possible

// l'une d'elle est-elle checked dans ce qu'on a lu en base (dans $aLigneSQL) ?
// peu importe laquelle.... c'est $aLigneSQL['niveau']
if (!empty($aLigneSQL['niveau'])) // en théorie pas possible (selon champ vide/NULL possible par exemple), dans ce cas on peut se passer du if
	$aCheckedOuPas[$aLigneSQL['niveau']] = 'checked';    // exemple : $aCheckedOuPas['debutant'] passe de '' à 'checked'

// Maintenant qu'on sait quelle ligne radio du "niveau" est checked, on les génère toutes :
foreach (SQL_NIVEAU_POSSIBLE as $n)
{
	// 1 générer les label (les id des radio doivent être différent !!! (pas tous id="niveau"), sinon on peut pas relier un label à UNE ligne de radio plutôt qu'une autre
	$aLignesHTMLRadioNiveau[] = '<label id="label_niveau-'.$n.'" class="label_niveau" for="radio-niveau-'.$n.'">'.$n.'</label>';
		// et si on veut un libellé plus complet que juste "debutant", voir une autre idée en fin de doc noté "OPTIONNEL 1"
	// 2 une ligne radio par "niveau" possible en SQL :
	$aLignesHTMLRadioNiveau[] = '<input type="radio" id="radio-niveau-'.$n.'" name="niveau" '.$aCheckedOuPas[$n].'>';
	// avec ça, tu peux designer facilement par classe générique ou un id spécifique
	// idem si un .js doit manipuler un élément ou une classe d'éléments particulière
	// c'est cadeau bonus :)
}
// arrivé ici, on a une ligne (2 en fait, le label aussi) par élément possible radio de "niveau" à afficher, qu'il y ait 3 ou X niveaux ; on touchera plus ce code, juste la valeur de la const SQL_NIVEAU_POSSIBLE

// on transforme donc en du texte et pas un array :
// $sLi.... et pas $aLi (s, pas a :))
$sLignesHTMLRadioNiveau = implode (PHP_EOL, $aLignesHTMLRadioNiveau);
// pour info, $sLignesHTMLRadioNiveau contient :
/*
<label ...... niveau debutant...... for="..... </label>
<input radio .... debutant.... >
<label ...... niveau intermediaire......
<input radio .... intermediaire....
<label ...... niveau expert......
<input radio .... expert....
y'a plus qu'à :
*/

echo <<< MON_PAVE_HTML
.....
<form ........
	<!-- d'autres trucs (tu peux faire pareil pour générer les champs nom, prenom etc, voir "OPTIONNEL 2" en bas de ce code) -->
	<!-- arrivent les 3 (ou X) lignes de radio pour niveau : -->
	$sLignesHTMLRadioNiveau <!-- et c'est tout !!! pas de echo pas de    < ? php  partout etc   et la logique if then else est gérée avant  ; et encore, y'a même pas un "if" :) -->
	<!-- et la suite après ton niveau/radio jusqu'au "submit" -->
</form>
MON_PAVE_HTML;
// job's done

////////////////////////////////////////////////////////////////////////////////////////////////


// OPTIONNEL 1
// pour avoir des libellés plus complet.
// Rien n'empêche d'avoir une fonction de correspondance. Par extension une jour se pose la question des traductions etc. Il existe des systèmes pour ça, mais faisons simple :
// à mettre pas loin de const SQL_NIVEAU_POSSIBLE...
const SQL_LIBELLES_DES_CHAMPS = [
	// niveau :
	'debutant' => 'Niveau débutant',
	'intermediaire' => 'Niveau intermédiaire',
	'expert' => 'Niveau expert',
];
// ainsi au lieu de cette ligne (dans la boucle qui génère le code) :
	$aLignesHTMLRadioNiveau[] = '<label id="label_niveau-'.$n.'" class="label_niveau" for="radio-niveau-'.$n.'">'.$n.'</label>';
// tu mets :
	$aLignesHTMLRadioNiveau[] = '<label id="label_niveau-'.$n.'" class="label_niveau" for="radio-niveau-'.$n.'">'.SQL_LIBELLES_DES_CHAMPS[$n].'</label>';
	// ou une fonction de traduction, bref un truc plus propre que l'élément de SQL_LIBELLES_DES_CHAMPS au pif s'il existe bel et bien :)


////////////////////////////////////////////////////////////////////////////////////////////////


// OPTIONNEL 2
// au final, pour rendre un max de trucs dynamiques, on peut pousser le concept et faire une méga-structure multi-dimensionnelle décrivant la DB et tout ce dont on a besoin pour :
// valider certaines injections de données dans un form
// savoir comment afficher automatiquement tel champ, grisé ou pas, checked ou pas, son libellé etc
// genre :
const SQL_POUR_FORMULAIRES = [ // on décrit tout ce qu'il y a à savoir sur notre base :
	// liste des tables
	'utilisateur' => [
		// pour chaque table, les champs importants qui peuvent figurer dans un form php
		'id' => [
			'valeur_possible' => ENTIER_POSITIF,
			'type_form' => 'text', // en PHP, on décide que ce sera du texte, ça pourrait être un 'number' aussi
			'modifiable' => false, // pour que le champ, s'il est affiché, soit généré par le code comme "disabled" (et ne passe même pas dans le $_POST à la validation)
			'autre chose ?' => ...,
		],
		'nom' => [
			'valeur_possible' => TEXTE,
			'type_form' => 'text', // en PHP, on décide que ce sera du texte, ça pourrait être un 'number' aussi
			'modifiable' => true, // pour que le champ, s'il est affiché, soit généré par le code comme "disabled" (et ne passe même pas dans le $_POST à la validation)
			'autre chose ?' => ...,
		],
		// etc tous les champs susceptibles d'être dans un FORM
	],
	// 2è table :
	'joueur' => [
		...
	],
	// 3è table :
	'xxx' =>....
];

// évidemment, ENTIER_POSITIF et TEXTE (et les autres) renvoient vers des constantes définies pour guider le comportement du code PHP ensuite
// genre :
	// 1) si tu veux pas te faire chier :
	const ENTIER_POSITIF = 'ENTIER_POSITIF';
	const TEXTE = 'TEXTE';
	...
	// 2) si tu veux anticiper et contrôler les injections en validant des expressions régulières (demandez à chatgpt, c'est un incontournable à connaitre un jour les regexp)
	const ENTIER_POSITIF = '/^[1-9][0-9]*$/'; // je vous laisse vous renseigner :)
	const TEXTE = '/^[a-zA-Z\' -]{4,}$/'; // là aussi :) et bienvenue ensuite dans le monde des ennuis avec UTF-8, les caractères étrangers etc. Il faut faire ça bien :)
	
// exemple :
function checkInjectionDansUnForm ($sTable, $sChamp, $sValeurDuFormAControler)
{
	if (!isset(SQL_POUR_FORMULAIRES[$sTable][$sChamp]))
	{
		echo ('oh le dév !!! tu me fais checker un champ pour lequel je ne sais pas quoi faire !!!');
		return (false);
	}
	// cas 1) ci-dessus :
	$bEstValide = false;
	switch(SQL_POUR_FORMULAIRES[$sTable][$sChamp]['valeurs_possibles'])
	{
		case ENTIER_POSITIF:
			// teste ce que tu veux
			if (balbabalblabla la valeur $sValeurDuFormAControler a l air propre)
				$bEstValide = true;
			else
				ça reste false
		break;
		case TEXTE:
			// même genre
		break;
		default:
			echo ('tiens le dév t\'as oublié de me dire comment contrôler le type '.SQL_POUR_FORMULAIRES[$sTable][$sChamp]);
	}
	// enfin :
	return ($bEstValide);
	
	// sinon cas 2) avec regexp :
	if (!isset(SQL_POUR_FORMULAIRES[$sTable][$sChamp]))
	{
		engueulerLeDevCommeCiDessus();
		return (false);
	}
	return (preg_match(SQL_POUR_FORMULAIRES[$sTable][$sChamp]['valeurs_possibles'], $sValeurDuFormAControler));
	// merveilleux les regexp, ça tient en une ligne ! :)
	// et ton code t'auto-engueules si tu as fait de la merde et oublié de définir des choses :)
}
// et dans une fonction afficherFormulaire :
function afficherFormulaire ($sTypeFormulaire, $aLigneDBLue) // $aLigneDBLue c'est comme notre $aLigneSQL en tout début de ce document
{
	// selon le $sTypeFormulaire
	// blabablala
	// ...
	// et à un moment :
	foreach ($aLigneDBLue as $sNomChamp => $mValeurLueEnDB)
	{
		if (!checkInjectionDansUnForm (le nom de la table, $sNomChamp, $mValeurLueEnDB))
		{
			echo('donnée douteuse ! injection de code ?');
			// et plus tard, si t'es sur de ton coup => bannir l'IP du mec en question, ou autre mesure préventive
			// mais là ça demande d'être sur de son coup et de savoir faire ce genre de truc (applicativement ou au niveau système)
			
			// bloquer le traitement ou ignorer la valeur ou autre...
		}
		else
		{
			afficheLeChamp(....);
			// et cette fonction générique, selon les informations dans SQL_POUR_FORMULAIRES, sait si quel code HTML générer : input type="bidule", checked ou pas, disabled ou pas etc etc etc
			// y'a pas de limite :)
		}
}

// have fun