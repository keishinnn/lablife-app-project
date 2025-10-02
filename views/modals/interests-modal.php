    <div id="interestsModal" class="p-interests-modal">
        <div class="interests-modal-content">
            <span class="interests-close-btn" id="p-interests-close-btn">&times;</span>
            <h2>Select Interests</h2>

            <form id="interests-form" method="POST" action="/u/save-interests">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div id="hobbies-container" style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($interests as $interest):
                        $isSelected = isset($userInterests) && in_array($interest['id'], array_map(fn($uh) => $uh->id, $userInterests));
                    ?>
                        <div class="interests-tag <?= $isSelected ? 'active' : '' ?>"
                            data-id="<?= $interest['id']; ?>">
                            <?= $interest['name']; ?>
                        </div>
                        <?php if ($isSelected): ?>
                            <input type="hidden" name="interests[]" value="<?= $interest['id']; ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <br>
                <div style="display: flex; justify-content: center; gap: 8rem;">
                    <button id="p-interests-cancel-btn" type="button">Cancel</button>
                    <button id="p-interests-save-btn" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>