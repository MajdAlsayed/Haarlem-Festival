<div id="history-media-modal" class="admin-cms-modal">
    <div class="admin-cms-modal__panel">

        <div class="admin-cms-modal__header">
            <h2 class="admin-cms-modal__title">Choose Image</h2>

            <button type="button" id="history-media-close" class="admin-cms-modal__close" aria-label="Close modal">
                ✕
            </button>
        </div>

        <div class="admin-cms-upload">
            <p>Upload new image</p>

            <div class="admin-cms-upload__row">
                <input
                    type="file"
                    id="history-upload-file"
                    aria-label="Upload image"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                >

                <button type="button" id="history-upload-btn" class="btn btn-primary">
                    Upload
                </button>
            </div>

            <div id="history-upload-status" class="admin-cms-upload__status"></div>
        </div>

        <div id="history-media-grid" class="admin-cms-media-grid"></div>

    </div>
</div>