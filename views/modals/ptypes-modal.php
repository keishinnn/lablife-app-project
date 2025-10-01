    <!-- Modal for upserting Personality Type -->
    <div id="personalityModal" class="p-ptypes-modal">
        <div class="ptypes-modal-content">
            <span class="pt-close-btn" id="p-pt-close-btn">&times;</span>
            <h2>Select Personality Type</h2>
            <form id="pt-form" method="POST" action="/u/save-personality">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div id="personalitySelect-container">
                    <select name="personality_id" id="personalitySelect" required>
                        <?php foreach ($ptypes as $ptype): ?>
                            <option value="<?php echo $ptype['id']; ?>"
                                <?php echo (isset($personalityType) && $ptype['id'] === $personalityType['id']) ? 'selected' : ''; ?>>
                                <?php echo $ptype['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <br><br>
                <div style="display: flex; justify-content: center; gap: 8rem;">
                    <button id="p-pt-cancel-btn" type="button">Cancel</button>
                    <button id="pt-save-btn" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>