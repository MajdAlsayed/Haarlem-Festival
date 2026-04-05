<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Management — CMS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #0f1117;
            --surface:     #181c27;
            --surface2:    #1f2436;
            --border:      rgba(255,255,255,0.07);
            --accent:      #f59e0b;
            --accent-dim:  rgba(245,158,11,0.12);
            --danger:      #ef4444;
            --danger-dim:  rgba(239,68,68,0.12);
            --success:     #22c55e;
            --success-dim: rgba(34,197,94,0.12);
            --warning:     #f59e0b;
            --warning-dim: rgba(245,158,11,0.12);
            --info:        #6366f1;
            --info-dim:    rgba(99,102,241,0.12);
            --text:        #f1f5f9;
            --muted:       #64748b;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 60px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-logo { font-size: 13px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); }
        .topbar-sep  { color: var(--border); font-size: 20px; }
        .topbar-title { font-size: 13px; color: var(--muted); }

        .topbar-back {
            margin-left: auto;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .06em;
            text-transform: uppercase;
            transition: color .15s;
        }
        .topbar-back:hover { color: var(--text); }

        /* ── Page ── */
        .page {
            max-width: 1320px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .page-header h1 { font-size: 28px; font-weight: 900; letter-spacing: -.02em; }
        .page-header p  { margin-top: 6px; font-size: 14px; color: var(--muted); }

        .count-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-dim);
            color: var(--accent);
            border: 1px solid rgba(245,158,11,.25);
            border-radius: 999px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 800;
            margin-top: 10px;
        }

        /* ── Table ── */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
        }

        .table-card table { width: 100%; border-collapse: collapse; }

        .table-card thead tr {
            background: var(--surface2);
            border-bottom: 1px solid var(--border);
        }

        .table-card th {
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .table-card tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .table-card tbody tr:last-child { border-bottom: none; }
        .table-card tbody tr:hover { background: rgba(255,255,255,0.025); }

        .table-card td {
            padding: 16px 20px;
            vertical-align: middle;
            font-size: 14px;
        }

        /* ── Thumb ── */
        .thumb {
            width: 56px; height: 56px;
            border-radius: 10px;
            object-fit: cover;
            display: block;
            background: var(--surface2);
            border: 1px solid var(--border);
        }

        .thumb-placeholder {
            width: 56px; height: 56px;
            border-radius: 10px;
            background: var(--surface2);
            border: 1px dashed var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        .story-name  { font-weight: 700; font-size: 14px; color: var(--text); line-height: 1.3; }
        .story-slug  { font-size: 11px; color: var(--muted); margin-top: 3px; font-family: 'Courier New', monospace; }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700; letter-spacing: .04em; white-space: nowrap;
        }

        .badge-type    { background: var(--info-dim);    color: #a5b4fc; border: 1px solid rgba(99,102,241,.25); }
        .badge-age     { background: var(--accent-dim);  color: var(--accent); border: 1px solid rgba(245,158,11,.25); }
        .badge-has     { background: var(--success-dim); color: var(--success); border: 1px solid rgba(34,197,94,.25); }
        .badge-empty   { background: var(--warning-dim); color: var(--warning); border: 1px solid rgba(245,158,11,.3); }
        .badge-none    { background: rgba(100,116,139,.08); color: var(--muted); border: 1px solid rgba(100,116,139,.18); font-size: 10px; }

        /* ── Action buttons ── */
        .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .btn-action {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 13px; border-radius: 8px;
            font-size: 12px; font-weight: 700; letter-spacing: .03em;
            text-decoration: none; border: none; cursor: pointer;
            transition: transform .15s, opacity .15s; white-space: nowrap;
        }
        .btn-action:hover { transform: translateY(-1px); opacity: .9; }

        .btn-edit        { background: var(--info-dim);    color: #a5b4fc; border: 1px solid rgba(99,102,241,.3); }
        .btn-detail-edit { background: var(--success-dim); color: var(--success); border: 1px solid rgba(34,197,94,.3); }
        .btn-detail-add  { background: var(--warning-dim); color: var(--warning); border: 1px solid rgba(245,158,11,.35); }
        .btn-delete      { background: var(--danger-dim);  color: var(--danger);  border: 1px solid rgba(239,68,68,.3); }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
        .empty-state-icon { font-size: 40px; margin-bottom: 12px; }
        .empty-state h3 { font-size: 16px; color: var(--text); margin-bottom: 6px; }

        @media (max-width: 900px) {
            .col-type, .col-age { display: none; }
            .actions { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <div class="topbar">
        <span class="topbar-logo">CMS</span>
        <span class="topbar-sep">/</span>
        <span class="topbar-title">Stories</span>
        <a href="/" class="topbar-back">← Back to site</a>
    </div>

    <div class="page">

        <div class="page-header">
            <div>
                <h1>Story Management</h1>
                <p>Edit story cards and manage detail pages for all stories.</p>
                <div class="count-pill">
                    <span>📚</span>
                    <span><?= count($stories) ?> stories</span>
                </div>
            </div>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width:72px;">Image</th>
                        <th>Story</th>
                        <th class="col-type">Type</th>
                        <th class="col-age">Age</th>
                        <th style="width:120px;">Detail Page</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stories)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📭</div>
                                    <h3>No stories found</h3>
                                    <p>Run your StoriesSeeder to populate the database.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stories as $story): ?>
                            <?php $detailExists = !empty($story['has_detail_page']); ?>
                            <tr>

                                <!-- Thumbnail -->
                                <td>
                                    <?php if (!empty($story['image_path'])): ?>
                                        <img class="thumb"
                                             src="<?= h($story['image_path']) ?>"
                                             alt="<?= h($story['name'] ?? '') ?>">
                                    <?php else: ?>
                                        <div class="thumb-placeholder">📖</div>
                                    <?php endif; ?>
                                </td>

                                <!-- Name + slug -->
                                <td>
                                    <div class="story-name"><?= h($story['name'] ?? '—') ?></div>
                                    <div class="story-slug">/<?= h($story['slug'] ?? '') ?></div>
                                </td>

                                <!-- Type -->
                                <td class="col-type">
                                    <?php if (!empty($story['story_type'])): ?>
                                        <span class="badge badge-type"><?= h($story['story_type']) ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--muted);font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Age -->
                                <td class="col-age">
                                    <?php if (!empty($story['age'])): ?>
                                        <span class="badge badge-age"><?= h($story['age']) ?></span>
                                    <?php else: ?>
                                        <span style="color:var(--muted);font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Detail page status -->
                                <td>
                                    <?php if ($detailExists): ?>
                                        <span class="badge badge-has">✓ Done</span>
                                    <?php else: ?>
                                        <span class="badge badge-none">— None</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="actions">

                                        <!-- Edit story card — always -->
                                        <a class="btn-action btn-edit"
                                           href="/cms/stories/edit?id=<?= (int)$story['story_id'] ?>">
                                            ✏️ Edit story
                                        </a>

                                        <!-- Detail page: edit if exists, add if not -->
                                        <?php if ($detailExists): ?>
                                            <a class="btn-action btn-detail-edit"
                                               href="/cms/stories/detail-page?slug=<?= h($story['slug'] ?? '') ?>">
                                                📄 Edit detail
                                            </a>
                                        <?php else: ?>
                                            <a class="btn-action btn-detail-add"
                                               href="/cms/stories/detail-page?slug=<?= h($story['slug'] ?? '') ?>">
                                                ➕ Add detail
                                            </a>
                                        <?php endif; ?>

                                        <!-- Delete -->
                                        <a class="btn-action btn-delete"
                                           href="/cms/stories/delete?id=<?= (int)$story['story_id'] ?>"
                                           onclick="return confirm('Delete «<?= h(addslashes($story['name'] ?? 'this story')) ?>»? This cannot be undone.')">
                                            🗑 Remove
                                        </a>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>