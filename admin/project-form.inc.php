<?php
if (!isset($mode)) { $mode = 'new'; }
$defaults = [
    'title'=>'', 'excerpt'=>'', 'tech'=>'', 'live_url'=>'', 'status'=>'concept', 'hero_image'=>''
];
$data = array_merge($defaults, $data ?? []);
?>
<div class="card" style="background:#0f1a2a;border-radius:16px;padding:20px;max-width:900px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
            <label>Titel</label>
            <input required name="title" value="<?= e($data['title']) ?>" class="input">
        </div>
        <div>
            <label>Status</label>
            <select name="status" class="input">
                <option value="concept" <?= $data['status']==='concept'?'selected':'' ?>>Concept</option>
                <option value="live" <?= $data['status']==='live'?'selected':'' ?>>Live</option>
            </select>
        </div>
        <div style="grid-column:1/-1">
            <label>Korte omschrijving</label>
            <textarea name="excerpt" rows="3" class="input"><?= e($data['excerpt']) ?></textarea>
        </div>
        <div>
            <label>Tech (komma-gescheiden)</label>
            <input name="tech" value="<?= e($data['tech']) ?>" class="input" placeholder="HTML,CSS,JavaScript">
        </div>
        <div>
            <label>Live URL</label>
            <input name="live_url" value="<?= e($data['live_url']) ?>" class="input" placeholder="https://...">
        </div>
        <div style="grid-column:1/-1">
            <label>Hero-afbeelding (jpg/png/webp, max 3 MB)</label>
            <input type="file" name="hero" accept=".jpg,.jpeg,.png,.webp" class="input">
            <?php if ($mode==='edit' && $data['hero_image']): ?>
                <div style="margin-top:8px">Huidig: <code><?= e($data['hero_image']) ?></code></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .input{width:100%;background:#0b1530;border:1px solid #132340;border-radius:12px;padding:10px 12px;color:#cbd5e1}
    label{display:block;margin:6px 0 6px 2px;color:#94a3b8;font-size:14px}
    .actions{display:flex;gap:10px;margin-top:16px}
</style>
