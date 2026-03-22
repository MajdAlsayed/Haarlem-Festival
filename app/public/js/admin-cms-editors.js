/* TinyMCE + upload buttons for admin CMS pages */
(function () {
  var body = document.body;
  if (!body) return;

  var uploadUrl = body.getAttribute('data-upload-url') || '/admin/cms/upload';
  var uploadCsrf = body.getAttribute('data-upload-csrf') || '';

  function bindUpload(buttonId, fileInputId, context, onSuccess) {
    var btn = document.getElementById(buttonId);
    var fileEl = document.getElementById(fileInputId);
    if (!btn || !fileEl) return;

    btn.addEventListener('click', function () {
      var f = fileEl.files && fileEl.files[0];
      if (!f) {
        window.alert('Pick a file first.');
        return;
      }
      var fd = new FormData();
      fd.append('_csrf', uploadCsrf);
      fd.append('context', context);
      fd.append('image', f);

      btn.disabled = true;
      fetch(uploadUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) {
          return r.json().then(function (data) {
            return { ok: r.ok, data: data };
          });
        })
        .then(function (res) {
          btn.disabled = false;
          if (res.data && res.data.ok) {
            onSuccess(res.data);
            fileEl.value = '';
            window.alert('OK');
          } else {
            window.alert((res.data && res.data.error) || 'Failed');
          }
        })
        .catch(function () {
          btn.disabled = false;
          window.alert('Network error');
        });
    });
  }

  bindUpload('cms-upload-home-btn', 'cms-upload-home-file', 'home_about', function (data) {
    var input = document.getElementById('cms_about_image_src');
    if (input && data.url) input.value = data.url;
  });

  bindUpload('cms-upload-dance-hero-btn', 'cms-upload-dance-hero-file', 'dance_hero', function (data) {
    var input = document.getElementById('hero_image');
    if (input && data.filename) input.value = data.filename;
  });

  if (typeof tinymce === 'undefined') return;

  tinymce.init({
    selector: 'textarea.cms-wysiwyg',
    height: 320,
    menubar: false,
    branding: false,
    plugins: 'link lists autoresize',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link unlink | removeformat',
    link_default_target: '_blank',
    link_assume_external_targets: true,
    relative_urls: false,
    remove_trailing_brs: true,
    entity_encoding: 'raw',
    content_style:
      'body { font-family: system-ui, sans-serif; font-size: 15px; } p { margin: 0 0 0.75em; }',
  });
})();
