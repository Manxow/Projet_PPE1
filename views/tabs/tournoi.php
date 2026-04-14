<div class="carte-action w-g">
    <div class="hub-equipe-entete">
        <h1 class="titre-sans-marge-haut">🏆 Tournois & Compétitions</h1>
        <p>Rejoignez un tournoi pour affronter les meilleures équipes de la M2L.</p>
    </div>
</div>

<?php if (isset($_SESSION['flash_message_succes'])): ?>
    <div class="alerte alerte-succes w-m"><?= htmlspecialchars($_SESSION['flash_message_succes']) ?></div>
    <?php unset($_SESSION['flash_message_succes']); ?>
<?php endif; ?>

<div class="carte-action w-g">
    <?php if (!empty($tournois)): ?>
        <?php foreach ($tournois as $t): ?>
            <?php
            $enModeEdition = (isset($_GET['edit_id']) && $_GET['edit_id'] == $t['id_tournoi'] && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);
            // On détermine la classe de couleur de bordure si besoin
            $classeStatut = ($t['nb_inscrits'] < 16) ? 'carte-rejoindre' : '';
            ?>

            <div id="tournoi-<?= (int)$t['id_tournoi'] ?>"></div>

            <?php if ($enModeEdition): ?>
                <div class="carte-action carte-edition w-m">
                    <form action="index.php?action=traiter_editer_tournoi" method="POST" class="form-sans-marge">
                        <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">

                        <div class="form-groupe-vertical">
                            <label>Nom du tournoi :</label>
                            <input type="text" name="nom_tournoi" value="<?= htmlspecialchars($t['nom']) ?>" required>
                        </div>

                        <div class="form-groupe-horizontal">
                            <div class="form-groupe-vertical flex-1">
                                <label>Date de début :</label>
                                <input type="date" name="date_debut" value="<?= htmlspecialchars($t['date_debut']) ?>" required>
                            </div>
                            <div class="form-groupe-vertical flex-1">
                                <label>Date de fin :</label>
                                <input type="date" name="date_fin" value="<?= htmlspecialchars($t['date_fin']) ?>" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primaire btn-petit">💾 Enregistrer</button>
                            <a href="index.php?action=tournoi" class="btn btn-rouge btn-petit ml-10">❌ Annuler</a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <div class="carte-action <?= $classeStatut ?> w-m">
                    <div class="tournoi-entete-ligne">
                        <div class="info-tournoi">
                            <div class="tournoi-titre-ligne">
                                <h2 class="titre-sans-marge-haut"><?= htmlspecialchars(preg_replace('/\s-\s\d{2}\/\d{2}\/\d{4}(\s\d{2}:\d{2}:\d{2})?$/', '', $t['nom'])) ?></h2>
                                <p class="places-tournoi">Inscrits : <strong><?= $t['nb_inscrits'] ?> / 16</strong></p>
                            </div>
                            <p class="date-tournoi">📅 Du <?= date('d/m', strtotime($t['date_debut'])) ?> au <?= date('d/m/Y', strtotime($t['date_fin'])) ?></p>
                        </div>

                        <div class="actions-tournoi">
                            <?php if ($estCapitaine): ?>
                                <?php if (ModelTournoi::estDejaInscrit($_SESSION['idTeam'], $t['id_tournoi'])): ?>
                                    <span class="badge-inscrit">Inscrit ✅</span>
                                <?php elseif ($t['nb_inscrits'] < 16): ?>
                                    <form action="index.php?action=inscription_tournoi" method="POST" class="form-sans-marge">
                                        <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">
                                        <button type="submit" class="btn btn-vert-admin">Inscrire l'équipe</button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn btn-rouge btn-desactive">Complet</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                <div class="actions-admin-equipe">
                                    <a href="index.php?action=tournoi&edit_id=<?= $t['id_tournoi'] ?>" class="btn btn-orange btn-petit" title="Modifier">✏️</a>
                                    <form action="index.php?action=admin_supprimer_tournoi" method="POST" class="form-sans-marge"
                                        onsubmit="return confirm('Supprimer définitivement ce tournoi ?');">
                                        <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">
                                        <button type="submit" class="btn btn-rouge btn-petit" title="Supprimer">🗑️</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($t['statut'] === 'en_cours' && isset($t['poules'])): ?>
                        <?php
                        $matchsTournoi = ModelMatch::getMatchsTournoi($t['id_tournoi']);
                        $matchsParPoule = ['A' => [], 'B' => [], 'C' => [], 'D' => []];
                        foreach ($matchsTournoi as $match) {
                            if ($match['id_poule'] && isset($matchsParPoule[$match['id_poule']])) {
                                $matchsParPoule[$match['id_poule']][] = $match;
                            }
                        }
                        ?>

                        <details class="accordéon-poules mt-10">
                            <summary class="accordéon-titre">
                                Phase de poules <span class="fleche">▼</span>
                            </summary>

                            <div class="matchs-par-poule mt-10">
                                <?php foreach ($matchsParPoule as $poule => $matchs): ?>
                                    <div class="poule-matchs">
                                        <h4>Poule <?= $poule ?></h4>

                                        <div class="poule-contenu">
                                            <div class="classement-poule">
                                                <h5>Classement</h5>
                                                <?php $classementPoule = $t['classements'][$poule] ?? []; ?>
                                                <?php if (!empty($classementPoule)): ?>
                                                    <table class="table-classement-poule">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Équipe</th>
                                                                <th>Pts</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($classementPoule as $ligne): ?>
                                                                <tr class="<?= $ligne['qualifie'] ? 'qualifie' : '' ?>">
                                                                    <td><?= (int)$ligne['rang'] ?></td>
                                                                    <td><?= htmlspecialchars($ligne['nom']) ?></td>
                                                                    <td><strong><?= (int)$ligne['points'] ?></strong></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                    <p class="note-qualification">Les 2 premiers sont qualifiés.</p>
                                                <?php else: ?>
                                                    <p class="texte-centre">Classement indisponible</p>
                                                <?php endif; ?>
                                            </div>

                                            <div class="liste-matchs">
                                                <?php if (!empty($matchs)): ?>
                                                    <?php foreach ($matchs as $match): ?>
                                                        <div class="ligne-match <?= $match['statut'] ?>">
                                                            <div class="match-equipes">
                                                                <span class="equipe1"><?= htmlspecialchars($match['nom_equipe1']) ?></span>
                                                                <span class="vs">VS</span>
                                                                <span class="equipe2"><?= htmlspecialchars($match['nom_equipe2']) ?></span>
                                                            </div>

                                                            <?php if ($match['statut'] === 'terminé' && $match['buts_equipe1_final'] !== null): ?>
                                                                <div class="match-resultat resultat-valide">
                                                                    <span class="score"><?= $match['buts_equipe1_final'] ?> - <?= $match['buts_equipe2_final'] ?></span>
                                                                </div>
                                                            <?php elseif ($match['statut'] === 'erreur'): ?>
                                                                <div class="match-resultat resultat-erreur">
                                                                    <span>❌ Erreur résultat</span>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="match-resultat resultat-pending">
                                                                    <?php
                                                                    $estCapitaineDansMatch = (
                                                                        $estCapitaine &&
                                                                        isset($_SESSION['idTeam']) &&
                                                                        ($_SESSION['idTeam'] == $match['id_equipe1'] || $_SESSION['idTeam'] == $match['id_equipe2'])
                                                                    );

                                                                    $peutSaisir = false;
                                                                    $messageAttente = "À jouer";

                                                                    if ($match['date_match']) {
                                                                        $heure_debut_saisie = strtotime($match['date_match']) + (2 * 3600);
                                                                        $heure_maintenant = time();

                                                                        if ($heure_maintenant >= $heure_debut_saisie) {
                                                                            $peutSaisir = true;
                                                                        } else {
                                                                            $temps_attente_sec = $heure_debut_saisie - $heure_maintenant;
                                                                            $temps_attente = ceil($temps_attente_sec / 60);
                                                                            $messageAttente = "Saisie dans " . $temps_attente . "min";
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <?php if ($estCapitaineDansMatch && $peutSaisir): ?>
                                                                        <a href="index.php?action=saisir_resultat&id_match=<?= $match['id_rencontre'] ?>" class="btn btn-primaire btn-petit">
                                                                            Saisir
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span><?= $messageAttente ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if ($match['date_match']): ?>
                                                                <div class="match-date"><?= date('d/m H:i', strtotime($match['date_match'])) ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p>Aucun match pour cette poule</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>

                        <details class="accordéon-poules mt-10">
                            <summary class="accordéon-titre">
                                🏟️ Phases finales <span class="fleche">▼</span>
                            </summary>

                            <div class="phases-finales mt-10">
                                <?php
                                $matchsFinalesTous = $t['phases_finales'] ?? [];
                                $finalesParPhase = [
                                    'quart'  => ['label' => '⚡ Quarts de finale', 'matchs' => []],
                                    'demi'   => ['label' => '🔥 Demi-finales',     'matchs' => []],
                                    'finale'     => ['label' => '🏆 Finale',            'matchs' => []],
                                ];
                                foreach ($matchsFinalesTous as $mf) {
                                    if (isset($finalesParPhase[$mf['phase']])) {
                                        $finalesParPhase[$mf['phase']]['matchs'][] = $mf;
                                    }
                                }
                                ?>

                                <?php if (!empty($matchsFinalesTous)): ?>
                                    <?php foreach ($finalesParPhase as $phaseKey => $phaseData): ?>
                                        <?php if (!empty($phaseData['matchs'])): ?>
                                            <div class="groupe-phase-finale">
                                                <h4 class="titre-phase-finale"><?= $phaseData['label'] ?></h4>

                                                <?php foreach ($phaseData['matchs'] as $match): ?>
                                                    <div class="ligne-match <?= $match['statut'] ?>">
                                                        <div class="match-equipes">
                                                            <span class="equipe1"><?= htmlspecialchars($match['nom_equipe1']) ?></span>
                                                            <span class="vs">VS</span>
                                                            <span class="equipe2"><?= htmlspecialchars($match['nom_equipe2']) ?></span>
                                                        </div>

                                                        <?php if ($match['statut'] === 'terminé' && $match['buts_equipe1_final'] !== null): ?>
                                                            <div class="match-resultat resultat-valide">
                                                                <span class="score"><?= $match['buts_equipe1_final'] ?> - <?= $match['buts_equipe2_final'] ?></span>
                                                            </div>
                                                        <?php elseif ($match['statut'] === 'erreur'): ?>
                                                            <div class="match-resultat resultat-erreur">
                                                                <span>❌ Erreur résultat</span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="match-resultat resultat-pending">
                                                                <?php
                                                                $estCapitaineDansMatch = (
                                                                    $estCapitaine &&
                                                                    isset($_SESSION['idTeam']) &&
                                                                    ($_SESSION['idTeam'] == $match['id_equipe1'] || $_SESSION['idTeam'] == $match['id_equipe2'])
                                                                );
                                                                $peutSaisir    = false;
                                                                $messageAttente = "À jouer";
                                                                if ($match['date_match']) {
                                                                    $heure_debut_saisie = strtotime($match['date_match']) + (2 * 3600);
                                                                    if (time() >= $heure_debut_saisie) {
                                                                        $peutSaisir = true;
                                                                    } else {
                                                                        $messageAttente = "Saisie dans " . ceil(($heure_debut_saisie - time()) / 60) . "min";
                                                                    }
                                                                }
                                                                ?>
                                                                <?php if ($estCapitaineDansMatch && $peutSaisir): ?>
                                                                    <a href="index.php?action=saisir_resultat&id_match=<?= $match['id_rencontre'] ?>" class="btn btn-primaire btn-petit">Saisir</a>
                                                                <?php else: ?>
                                                                    <span><?= $messageAttente ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($match['date_match']): ?>
                                                            <div class="match-date"><?= date('d/m H:i', strtotime($match['date_match'])) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="texte-centre">Les phases finales seront générées automatiquement quand tous les matchs de poule seront validés.</p>
                                <?php endif; ?>
                            </div>
                        </details>
                    <?php endif; ?>

                    <?php if ($t['statut'] !== 'en_cours' && $t['statut'] !== 'termine'): ?>
                        <div class="participants-tournoi mt-10">
                            <h4>Équipes participantes</h4>
                            <?php if (!empty($t['participants'])): ?>
                                <ul class="liste-participants-tournoi">
                                    <?php foreach ($t['participants'] as $participant): ?>
                                        <li><?= htmlspecialchars($participant['nom']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="texte-centre">Aucune équipe inscrite pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 && $t['statut'] === 'complet'): ?>
                        <div class="encart-action-admin mt-10">
                            <p class="texte-action-admin">⚠️ Le tournoi est complet !</p>
                            <form action="index.php?action=admin_generer_poules" method="POST" class="form-sans-marge">
                                <input type="hidden" name="id_tournoi" value="<?= $t['id_tournoi'] ?>">
                                <button type="submit" class="btn btn-primaire btn-large" onclick="return confirm('Lancer le tirage ?');">
                                    🎲 Lancer le tirage
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="carte-action w-m">
            <p class="titre-centre">Aucun tournoi n'est ouvert pour le moment.</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($tournoisTermines)): ?>
    <div class="carte-action w-g">
        <div class="hub-equipe-entete">
            <h2 class="titre-sans-marge-haut">🏅 Tournois terminés</h2>
        </div>

        <?php foreach ($tournoisTermines as $t): ?>
            <div class="carte-action w-m">
                <div class="info-tournoi">
                    <div class="tournoi-titre-ligne">
                        <h2 class="titre-sans-marge-haut"><?= htmlspecialchars(preg_replace('/\s-\s\d{2}\/\d{2}\/\d{4}(\s\d{2}:\d{2}:\d{2})?$/', '', $t['nom'])) ?></h2>
                        <span class="badge-statut statut-termine">Terminé</span>
                    </div>
                    <p class="date-tournoi">📅 Du <?= date('d/m', strtotime($t['date_debut'])) ?> au <?= date('d/m/Y', strtotime($t['date_fin'])) ?></p>
                </div>

                <?php
                $matchsTermine = ModelMatch::getMatchsTournoi($t['id_tournoi']);
                $matchsParPouleTermine = ['A' => [], 'B' => [], 'C' => [], 'D' => []];
                foreach ($matchsTermine as $match) {
                    if ($match['id_poule'] && isset($matchsParPouleTermine[$match['id_poule']])) {
                        $matchsParPouleTermine[$match['id_poule']][] = $match;
                    }
                }
                ?>

                <details class="accordéon-poules mt-10">
                    <summary class="accordéon-titre">
                        Phase de poules <span class="fleche">▼</span>
                    </summary>
                    <div class="matchs-par-poule mt-10">
                        <?php foreach ($matchsParPouleTermine as $poule => $matchs): ?>
                            <div class="poule-matchs">
                                <h4>Poule <?= $poule ?></h4>
                                <div class="poule-contenu">
                                    <div class="classement-poule">
                                        <h5>Classement</h5>
                                        <?php $classementPoule = $t['classements'][$poule] ?? []; ?>
                                        <?php if (!empty($classementPoule)): ?>
                                            <table class="table-classement-poule">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Équipe</th>
                                                        <th>Pts</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($classementPoule as $ligne): ?>
                                                        <tr class="<?= $ligne['qualifie'] ? 'qualifie' : '' ?>">
                                                            <td><?= (int)$ligne['rang'] ?></td>
                                                            <td><?= htmlspecialchars($ligne['nom']) ?></td>
                                                            <td><strong><?= (int)$ligne['points'] ?></strong></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <p class="texte-centre">Classement indisponible</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="liste-matchs">
                                        <?php if (!empty($matchs)): ?>
                                            <?php foreach ($matchs as $match): ?>
                                                <div class="ligne-match <?= $match['statut'] ?>">
                                                    <div class="match-equipes">
                                                        <span class="equipe1"><?= htmlspecialchars($match['nom_equipe1']) ?></span>
                                                        <span class="vs">VS</span>
                                                        <span class="equipe2"><?= htmlspecialchars($match['nom_equipe2']) ?></span>
                                                    </div>
                                                    <?php if ($match['statut'] === 'terminé' && $match['buts_equipe1_final'] !== null): ?>
                                                        <div class="match-resultat resultat-valide">
                                                            <span class="score"><?= $match['buts_equipe1_final'] ?> - <?= $match['buts_equipe2_final'] ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($match['date_match']): ?>
                                                        <div class="match-date"><?= date('d/m H:i', strtotime($match['date_match'])) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>Aucun match pour cette poule</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>

                <details class="accordéon-poules mt-10">
                    <summary class="accordéon-titre">
                        🏟️ Phases finales <span class="fleche">▼</span>
                    </summary>
                    <div class="phases-finales mt-10">
                        <?php
                        $matchsFinalesTermine = $t['phases_finales'] ?? [];
                        $finalesParPhaseTermine = [
                            'quart'  => ['label' => '⚡ Quarts de finale', 'matchs' => []],
                            'demi'   => ['label' => '🔥 Demi-finales',     'matchs' => []],
                            'finale' => ['label' => '🏆 Finale',            'matchs' => []],
                        ];
                        foreach ($matchsFinalesTermine as $mf) {
                            if (isset($finalesParPhaseTermine[$mf['phase']])) {
                                $finalesParPhaseTermine[$mf['phase']]['matchs'][] = $mf;
                            }
                        }
                        ?>
                        <?php foreach ($finalesParPhaseTermine as $phaseData): ?>
                            <?php if (!empty($phaseData['matchs'])): ?>
                                <div class="groupe-phase-finale">
                                    <h4 class="titre-phase-finale"><?= $phaseData['label'] ?></h4>
                                    <?php foreach ($phaseData['matchs'] as $match): ?>
                                        <div class="ligne-match terminé">
                                            <div class="match-equipes">
                                                <span class="equipe1"><?= htmlspecialchars($match['nom_equipe1']) ?></span>
                                                <span class="vs">VS</span>
                                                <span class="equipe2"><?= htmlspecialchars($match['nom_equipe2']) ?></span>
                                            </div>
                                            <?php if ($match['buts_equipe1_final'] !== null): ?>
                                                <div class="match-resultat resultat-valide">
                                                    <span class="score"><?= $match['buts_equipe1_final'] ?> - <?= $match['buts_equipe2_final'] ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($match['date_match']): ?>
                                                <div class="match-date"><?= date('d/m H:i', strtotime($match['date_match'])) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </details>

                <?php $cf = $t['classement_final']; ?>
                <?php if ($cf['vainqueur']): ?>
                    <details class="accordéon-poules mt-10" open>
                        <summary class="accordéon-titre">
                            📊 Classement final <span class="fleche">▼</span>
                        </summary>
                        <div class="mt-10">
                            <table class="table-classement-final">
                                <thead>
                                    <tr>
                                        <th class="col-rang">#</th>
                                        <th>Équipe</th>
                                        <th class="col-rang">Récompense</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="rang-1">
                                        <td>1</td>
                                        <td><?= htmlspecialchars($cf['vainqueur']['nom']) ?></td>
                                        <td>🥇 Vainqueur</td>
                                    </tr>
                                    <tr class="rang-2">
                                        <td>2</td>
                                        <td><?= htmlspecialchars($cf['finaliste']['nom']) ?></td>
                                        <td>🥈 Finaliste</td>
                                    </tr>
                                    <?php foreach ($cf['demi_finalistes'] as $equipe): ?>
                                        <tr class="rang-3">
                                            <td>3</td>
                                            <td><?= htmlspecialchars($equipe['nom']) ?></td>
                                            <td>🥉 Demi-finaliste</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php foreach ($cf['quart_finalistes'] as $equipe): ?>
                                        <tr class="rang-5">
                                            <td>5</td>
                                            <td><?= htmlspecialchars($equipe['nom']) ?></td>
                                            <td>⚡ Quart de finaliste</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php foreach ($cf['phase_poule'] as $equipe): ?>
                                        <tr class="rang-9">
                                            <td>9</td>
                                            <td><?= htmlspecialchars($equipe['nom']) ?></td>
                                            <td>📋 Phase de poule</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>