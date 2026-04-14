<?php

$id_equipe_utilisateur = $_SESSION['idTeam'] ?? null;

?>

<div class="carte-action w-m">
    <h1 class="titre-centre titre-sans-marge-haut">
        Saisir le résultat du match
    </h1>

    <div class="match-detail-saisie">
        <div class="equipes-match">
            <div class="equipe-match equipe1">
                <h3><?= htmlspecialchars($match['nom_equipe1']) ?></h3>
                <?php if ($id_equipe_utilisateur == $match['id_equipe1']): ?>
                    <span class="badge-mon-equipe">Mon équipe</span>
                <?php endif; ?>
            </div>

            <div class="vs-sepa">VS</div>

            <div class="equipe-match equipe2">
                <h3><?= htmlspecialchars($match['nom_equipe2']) ?></h3>
                <?php if ($id_equipe_utilisateur == $match['id_equipe2']): ?>
                    <span class="badge-mon-equipe">Mon équipe</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($match['statut'] === 'erreur'): ?>
            <div class="alerte alerte-erreur">
                ⚠️ Erreur de saisie : Les deux capitaines ont donné des résultats différents !<br>
                <strong>Première saisie :</strong> <?= $match['buts_equipe1_saisie1'] ?> - <?= $match['buts_equipe2_saisie1'] ?><br>
                <strong>Deuxième saisie :</strong> <?= $match['buts_equipe1_saisie2'] ?> - <?= $match['buts_equipe2_saisie2'] ?><br>
                <em>Contactez l'administrateur pour résoudre ce conflit.</em>
            </div>
        <?php elseif ($match['statut'] === 'terminé'): ?>
            <div class="alerte alerte-succes">
                ✅ Match validé ! Résultat final : <strong><?= $match['buts_equipe1_final'] ?> - <?= $match['buts_equipe2_final'] ?></strong>
            </div>
        <?php else: ?>
            <form action="index.php?action=traiter_saisir_resultat" method="POST" class="formulaire-match">
                <input type="hidden" name="id_match" value="<?= $match['id_rencontre'] ?>">

                <div class="inputs-score">
                    <div class="groupe-input">
                        <label for="buts_equipe1">Buts de <?= htmlspecialchars($match['nom_equipe1']) ?> :</label>
                        <input
                            type="number"
                            id="buts_equipe1"
                            name="buts_equipe1"
                            min="0"
                            max="20"
                            value="<?= $match['buts_equipe1_saisie1'] !== null ? $match['buts_equipe1_saisie1'] : '' ?>"
                            required>
                    </div>

                    <div class="groupe-input">
                        <label for="buts_equipe2">Buts de <?= htmlspecialchars($match['nom_equipe2']) ?> :</label>
                        <input
                            type="number"
                            id="buts_equipe2"
                            name="buts_equipe2"
                            min="0"
                            max="20"
                            value="<?= $match['buts_equipe2_saisie1'] !== null ? $match['buts_equipe2_saisie1'] : '' ?>"
                            required>
                    </div>
                </div>

                <?php if ($match['buts_equipe1_saisie1'] !== null): ?>
                    <div class="info-saisie">
                        <p>Résultat déjà saisi : <strong><?= $match['buts_equipe1_saisie1'] ?> - <?= $match['buts_equipe2_saisie1'] ?></strong></p>
                        <p>Confirme ou corrige le résultat et réenvoie. L'autre équipe doit saisir le même résultat.</p>
                    </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primaire btn-large">
                        📤 Envoyer le résultat
                    </button>
                    <a href="index.php?action=tournoi" class="btn btn-secondaire btn-large">
                        ← Retour
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>