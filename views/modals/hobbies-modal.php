    <div id="hobbiesModal" class="p-ptypes-modal">
        <div class="ptypes-modal-content">
            <span class="pt-close-btn" id="p-hb-close-btn">&times;</span>
            <h2>Select Hobbies</h2>

            <form id="hb-form" method="POST" action="/u/save-hobbies">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div id="hobbies-container" style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($hobbies as $hobby):
                        $isSelected = isset($userHobbies) && in_array($hobby['id'], array_map(fn($uh) => $uh->id, $userHobbies));
                    ?>
                        <div class="hobby-tag <?= $isSelected ? 'active' : '' ?>"
                            data-id="<?= $hobby['id']; ?>">
                            <?= $hobby['name']; ?>
                        </div>
                        <?php if ($isSelected): ?>
                            <input type="hidden" name="hobbies[]" value="<?= $hobby['id']; ?>">
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