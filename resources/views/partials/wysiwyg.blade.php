@once
<style>
  .ck-editor__editable_inline { min-height: 10rem; }
  .ck.ck-editor { width: 100%; }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
  (function () {
    function initEditors() {
      if (typeof ClassicEditor === 'undefined') return;

      document.querySelectorAll('textarea[data-wysiwyg]').forEach(function (el) {
        if (el.dataset.wysiwygReady === '1') return;
        el.dataset.wysiwygReady = '1';

        ClassicEditor.create(el, {
          toolbar: [
            'heading', '|',
            'bold', 'italic', 'link', '|',
            'bulletedList', 'numberedList', 'blockQuote', '|',
            'undo', 'redo',
          ],
        }).catch(function (error) {
          console.error('CKEditor init failed:', error);
        });
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initEditors);
    } else {
      initEditors();
    }
  })();
</script>
@endonce
