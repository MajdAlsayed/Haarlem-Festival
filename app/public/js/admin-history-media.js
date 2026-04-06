(function () {
    var modal = document.getElementById('history-media-modal');
    var grid = document.getElementById('history-media-grid');
    var closeBtn = document.getElementById('history-media-close');
    var activeTarget = null;

    var uploadBtn = document.getElementById('history-upload-btn');
    var uploadFile = document.getElementById('history-upload-file');
    var uploadStatus = document.getElementById('history-upload-status');

    document.querySelectorAll('.history-media-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeTarget = document.getElementById(btn.getAttribute('data-target'));
            loadImages();
            modal.classList.add('is-open');
        });
    });

    closeBtn.addEventListener('click', function () {
        modal.classList.remove('is-open');
    });

    function loadImages() {
        grid.innerHTML = '<p class="admin-cms-media-grid__status">Loading...</p>';

        fetch('/admin/api/history/images')
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data.ok) {
                    grid.innerHTML = '<p class="admin-cms-media-grid__status">Error loading images.</p>';
                    return;
                }

                grid.innerHTML = '';

                data.images.forEach(function (img) {
                    var card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'admin-cms-media-card';

                    var image = document.createElement('img');
                    image.className = 'admin-cms-media-card__image';
                    image.src = img.image_url;
                    image.alt = img.alt_text || '';

                    var caption = document.createElement('p');
                    caption.className = 'admin-cms-media-card__caption';
                    caption.textContent = img.alt_text || 'Untitled image';

                    card.appendChild(image);
                    card.appendChild(caption);

                    card.addEventListener('click', function () {
                        if (activeTarget) {
                            activeTarget.value = img.history_image_id;
                        }
                        modal.classList.remove('is-open');
                    });

                    grid.appendChild(card);
                });
            })
            .catch(function () {
                grid.innerHTML = '<p class="admin-cms-media-grid__status">Network error while loading images.</p>';
            });
    }

    uploadBtn.addEventListener('click', function () {
        var f = uploadFile.files && uploadFile.files[0];

        if (!f) {
            uploadStatus.textContent = 'Pick a file first.';
            return;
        }

        var fd = new FormData();
        fd.append('_csrf', document.body.getAttribute('data-upload-csrf'));
        fd.append('context', 'history_hero');
        fd.append('image', f);

        uploadBtn.disabled = true;
        uploadStatus.textContent = 'Uploading...';

        fetch('/admin/cms/upload', {method: 'POST', body: fd})
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                uploadBtn.disabled = false;

                if (data.ok) {
                    uploadStatus.textContent = 'Uploaded! Selecting...';

                    if (activeTarget) {
                        activeTarget.value = data.history_image_id ?? data.image_id;
                    }

                    modal.classList.remove('is-open');
                    uploadFile.value = '';
                } else {
                    uploadStatus.textContent = data.error || 'Failed.';
                }
            })
            .catch(function () {
                uploadBtn.disabled = false;
                uploadStatus.textContent = 'Network error.';
            });
    });
})();