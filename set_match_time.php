<?php
require_once 'config/database.php';
$pdo = database::Connexion();

// Assigner une heure au match 25 : il y a 3 heures (pour que +2h soit déjà passé)
$heure_match = date('Y-m-d H:i:s', strtotime('-3 hours'));

$sql = "UPDATE rencontre SET date_match = ? WHERE id_rencontre = 25";
$stmt = $pdo->prepare($sql);
$stmt->execute([$heure_match]);

$peut_etre_saisi = date('Y-m-d H:i:s', strtotime($heure_match . ' +2 hours'));

echo "✅ Match 25 : Heure assignée à $heure_match\n";
echo "Le match peut être saisi depuis : $peut_etre_saisi\n";
echo "Vérification : Le match est maintenant saisisable ✓\n";
