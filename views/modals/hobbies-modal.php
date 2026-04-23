    <div id="hobbiesModal" class="p-hb-modal" data-open-on-load="<?= ($profileModalToOpen ?? '') === 'hobbies' ? 'true' : 'false' ?>">
        <div class="hb-modal-content">
            <span class="hb-close-btn" id="p-hb-close-btn">&times;</span>
            <h2>Select Hobbies</h2>

            <form id="hb-form" method="POST" action="/u/save-hobbies">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <?php if (($profileModalToOpen ?? '') === 'hobbies' && !empty($profileModalError ?? '')): ?>
                    <div class="profile-flash error" style="margin-bottom: 1rem;"><?= htmlspecialchars($profileModalError) ?></div>
                <?php endif; ?>

                <div id="hobbies-container" style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($hobbies ?? [] as $hobby):
                        $isSelected = isset($userHobbies) && in_array($hobby->id, array_map(fn($uh) => $uh->id, $userHobbies ?? []));
                    ?>
                        <div class="hobby-tag <?= $isSelected ? 'active' : '' ?>"
                            data-id="<?= $hobby->id; ?>">
                            <?= $hobby->name; ?>
                        </div>
                        <?php if ($isSelected): ?>
                            <input type="hidden" name="hobbies[]" value="<?= $hobby->id; ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <br>
                <div style="display: flex; justify-content: center; gap: 8rem;">
                    <button id="p-hb-cancel-btn" type="button">Cancel</button>
                    <button id="p-hb-save-btn" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
