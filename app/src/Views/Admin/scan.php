<?php

if (!isset($showCmsLinks)) {
    $showCmsLinks = true;
}
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$bannerClass = 'admin-scan-banner';
$bannerMsg = '';
if ($result !== null) {
    $t = (string) ($result['type'] ?? '');
    if ($t === 'batch') {
        $bannerClass .= ' admin-scan-banner--ok';
        $batchList = $result['results'] ?? [];
        $bannerMsg = is_array($batchList) ? ('Batch: ' . count($batchList) . ' code(s) processed.') : 'Batch processed.';
    } elseif ($t === 'success') {
        $bannerClass .= ' admin-scan-banner--ok';
        $bannerMsg = 'Admitted — ticket marked as scanned.';
    } elseif ($t === 'already_scanned') {
        $bannerClass .= ' admin-scan-banner--warn';
        $bannerMsg = 'Already scanned — do not admit again.';
    } elseif ($t === 'cancelled') {
        $bannerClass .= ' admin-scan-banner--bad';
        $bannerMsg = 'This ticket is cancelled.';
    } elseif ($t === 'not_found') {
        $bannerClass .= ' admin-scan-banner--bad';
        $bannerMsg = 'No valid ticket found for this code.';
    } elseif ($t === 'empty' || $t === 'empty_batch') {
        $bannerClass .= ' admin-scan-banner--warn';
        $bannerMsg = $t === 'empty_batch' ? 'Enter at least one code in the group box.' : 'Enter a ticket code.';
    } else {
        $bannerMsg = 'Scan result: ' . $h($t);
    }
}

$demoQrCode = '';
if ($result !== null && !empty($result['ticket_code'])) {
    $demoQrCode = (string) $result['ticket_code'];
}

$pageTitle = 'Ticket scanner — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main admin-scan-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Scan tickets</span>
        </nav>

        <h1 class="admin-title">Ticket scanner</h1>
        <p class="admin-lead">Use the camera for QR codes, paste one code, or enter up to four codes (one per line) for a group.</p>

        <?php if ($error !== null && $error !== ''): ?>
            <p class="admin-scan-error" role="alert"><?= $h($error) ?></p>
        <?php endif; ?>

        <?php if ($bannerMsg !== ''): ?>
            <div class="<?= $h($bannerClass) ?>" role="status">
                <strong><?= $h($bannerMsg) ?></strong>
                <?php if ($result !== null && ($result['type'] ?? '') === 'batch' && !empty($result['results']) && is_array($result['results'])): ?>
                    <ol class="admin-scan-batch-list">
                        <?php foreach ($result['results'] as $br): ?>
                            <?php
                            if (!is_array($br)) {
                                continue;
                            }
                            $bt = (string) ($br['type'] ?? '');
                            $bl = $bt === 'success' ? 'OK' : ($bt === 'already_scanned' ? 'Duplicate' : ($bt === 'not_found' ? 'Not found' : ($bt === 'cancelled' ? 'Cancelled' : $bt)));
                            ?>
                            <li>
                                <span class="admin-scan-batch-status admin-scan-batch-status--<?= $h($bt === 'success' ? 'ok' : ($bt === 'already_scanned' ? 'warn' : 'bad')) ?>"><?= $h($bl) ?></span>
                                <code class="admin-scan-batch-code"><?= $h((string) ($br['ticket_code'] ?? '')) ?></code>
                                <?php if (!empty($br['ticket_name'])): ?>
                                    <span class="admin-scan-muted"> — <?= $h((string) $br['ticket_name']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <?php if ($result !== null && ($result['type'] ?? '') === 'success'): ?>
                    <div class="admin-scan-detail">
                        <span><?= $h((string) ($result['ticket_name'] ?? '')) ?></span>
                        <?php if (!empty($result['event_title'])): ?>
                            <span class="admin-scan-muted"> · <?= $h((string) $result['event_title']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($result['event_day'])): ?>
                            <span class="admin-scan-muted"> · <?= $h(ucfirst((string) $result['event_day'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($result['ticket_code'])): ?>
                        <p class="admin-scan-code-label">Full code (copy for testing)</p>
                        <code class="admin-scan-full-code"><?= $h((string) $result['ticket_code']) ?></code>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($result !== null && ($result['type'] ?? '') === 'already_scanned'): ?>
                    <div class="admin-scan-detail">
                        <span><?= $h((string) ($result['ticket_name'] ?? '')) ?></span>
                        <?php if (!empty($result['event_title'])): ?>
                            <span class="admin-scan-muted"> · <?= $h((string) $result['event_title']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($result['ticket_code'])): ?>
                        <p class="admin-scan-code-label">Full code</p>
                        <code class="admin-scan-full-code"><?= $h((string) $result['ticket_code']) ?></code>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="admin-scan-layout">
        <div class="admin-scan-qr-block">
            <p class="admin-scan-label admin-scan-label--tight">Camera (QR)</p>
            <div class="admin-scan-qr-wrap" id="qr-reader-wrap">
                <div id="qr-reader-idle" class="admin-scan-qr-idle" aria-hidden="false">
                    <p class="admin-scan-qr-idle-title">Camera preview</p>
                    <p class="admin-scan-qr-idle-text">Tap <strong>Start camera</strong> and allow access — the live feed appears here.</p>
                </div>
                <div id="qr-reader" class="admin-scan-qr-reader" aria-label="QR scanner live view"></div>
            </div>
            <p id="qr-camera-error" class="admin-scan-error" style="display:none" role="alert"></p>
            <div class="admin-scan-qr-actions">
                <button type="button" class="admin-scan-submit admin-scan-submit--secondary" id="qr-start">Start camera</button>
                <button type="button" class="admin-scan-submit admin-scan-submit--secondary" id="qr-stop" hidden>Stop camera</button>
            </div>
            <p class="admin-scan-hint">When a QR is recognized, the value is copied into <strong>Ticket code (single)</strong> below. You can submit the form to admit.</p>
        </div>

        <div class="admin-scan-test-qr-block">
            <p class="admin-scan-label">Test QR (for this device’s camera)</p>
            <p class="admin-scan-hint admin-scan-hint--flush">Scan this image with the camera above, or use a second phone. The QR encodes the same text as a ticket code.</p>
            <div id="scan-test-qr-host" class="admin-scan-test-qr-host" data-initial-code="<?= $h($demoQrCode) ?>"></div>
            <div class="admin-scan-test-qr-actions">
                <button type="button" class="admin-scan-submit admin-scan-submit--secondary" id="scan-test-qr-refresh">Make QR from “Ticket code” field</button>
            </div>
        </div>
        </div>

        <form method="post" action="/admin/scan" class="admin-scan-form" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <label class="admin-scan-label" for="ticket_code">Ticket code (single)</label>
            <input
                class="admin-scan-input"
                type="text"
                name="ticket_code"
                id="ticket_code"
                inputmode="text"
                autocapitalize="off"
                autocorrect="off"
                spellcheck="false"
                placeholder="Paste or type code"
            >
            <label class="admin-scan-label" for="group_codes">Group codes (max 4 lines)</label>
            <textarea
                class="admin-scan-textarea"
                name="group_codes"
                id="group_codes"
                rows="4"
                placeholder="One code per line&#10;Up to 4 codes"
                spellcheck="false"
            ></textarea>
            <button type="submit" class="admin-scan-submit">Check &amp; admit</button>
            <p class="admin-scan-hint">If the group box has any text, only those lines are processed (single field is ignored).</p>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function () {
    var input = document.getElementById('ticket_code');
    var start = document.getElementById('qr-start');
    var stop = document.getElementById('qr-stop');
    var wrap = document.getElementById('qr-reader-wrap');
    var idle = document.getElementById('qr-reader-idle');
    var errEl = document.getElementById('qr-camera-error');
    var testHost = document.getElementById('scan-test-qr-host');
    var testRefresh = document.getElementById('scan-test-qr-refresh');

    function showCameraError(msg) {
        if (!errEl) return;
        errEl.textContent = msg;
        errEl.style.display = msg ? 'block' : 'none';
    }

    function setLiveUi(isLive) {
        if (wrap) wrap.classList.toggle('admin-scan-qr-wrap--live', isLive);
        if (idle) {
            idle.hidden = isLive;
            idle.setAttribute('aria-hidden', isLive ? 'true' : 'false');
        }
    }

    function renderTestQr(text) {
        if (!testHost || typeof QRCode === 'undefined') return;
        var t = (text || '').trim();
        testHost.innerHTML = '';
        if (!t) {
            testHost.innerHTML = '<p class="admin-scan-hint">Enter a code above (or scan a ticket first), then click “Make QR from Ticket code field”.</p>';
            return;
        }
        var level = (QRCode.CorrectLevel && QRCode.CorrectLevel.M) ? QRCode.CorrectLevel.M : 0;
        new QRCode(testHost, { text: t, width: 200, height: 200, correctLevel: level });
    }

    if (testHost && testRefresh) {
        var initial = (testHost.getAttribute('data-initial-code') || '').trim();
        if (initial) {
            renderTestQr(initial);
        } else {
            renderTestQr('');
        }
        testRefresh.addEventListener('click', function () {
            renderTestQr(input ? input.value : '');
        });
    }

    if (!input || !start || !stop || typeof Html5Qrcode === 'undefined') {
        if (errEl) {
            errEl.textContent = 'QR scanner script failed to load. Check your network or try manual entry.';
            errEl.style.display = 'block';
        }
        return;
    }

    var readerId = 'qr-reader';
    var html5Qr = new Html5Qrcode(readerId);
    var running = false;

    function onDecoded(text) {
        input.value = (text || '').trim();
        input.focus();
        showCameraError('');
        stopScanner();
    }

    function stopScanner() {
        if (!running) return;
        html5Qr.stop().then(function () {
            running = false;
            start.hidden = false;
            stop.hidden = true;
            setLiveUi(false);
        }).catch(function () {
            running = false;
            start.hidden = false;
            stop.hidden = true;
            setLiveUi(false);
        });
    }

    function tryStartCamera(facingOrConfig) {
        showCameraError('');
        var config = { fps: 10, qrbox: { width: 240, height: 240 } };
        return html5Qr.start(facingOrConfig, config, onDecoded, function () {});
    }

    start.addEventListener('click', function () {
        if (running) return;
        tryStartCamera({ facingMode: 'environment' }).then(function () {
            running = true;
            start.hidden = true;
            stop.hidden = false;
            setLiveUi(true);
        }).catch(function () {
            tryStartCamera({ facingMode: 'user' }).then(function () {
                running = true;
                start.hidden = true;
                stop.hidden = false;
                setLiveUi(true);
            }).catch(function (e2) {
                var msg = (e2 && e2.message) ? String(e2.message) : 'Unknown error';
                showCameraError('Could not start camera (' + msg + '). Try HTTPS/localhost, allow permissions, or use the test QR + same device / manual code.');
            });
        });
    });

    stop.addEventListener('click', stopScanner);
})();
</script>

</body>
</html>
