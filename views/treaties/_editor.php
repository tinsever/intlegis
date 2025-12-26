<?php 
$editorId = 'editor-' . uniqid();
$initialContent = $content_html ?? '';
?>

<div id="<?= $editorId ?>-container" class="prose-treaty">
    <div id="<?= $editorId ?>"><?= $initialContent ?></div>
    <input type="hidden" name="content_html" id="<?= $editorId ?>-input">
    <input type="hidden" name="content_json" value="{}">
</div>

<script>
(function() {
    if (typeof Quill === 'undefined') {
        console.error('Quill not found. Make sure Quill is loaded.');
        return;
    }

    const toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link'],
        ['clean']
    ];

    const quill = new Quill('#<?= $editorId ?>', {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions
        }
    });

    // Sync content to hidden input on every change
    quill.on('text-change', function() {
        document.getElementById('<?= $editorId ?>-input').value = quill.root.innerHTML;
    });

    // Initialize the hidden input with current content
    document.getElementById('<?= $editorId ?>-input').value = quill.root.innerHTML;

    console.log('Quill editor initialized successfully');
})();
</script>
