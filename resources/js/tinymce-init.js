import tinymce from 'tinymce';
import 'tinymce/themes/silver';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/help';
import 'tinymce/plugins/wordcount';

export function initTinyMCE(selector = '.tinymce-editor') {
    tinymce.init({
        selector: selector,
        license_key: 'gpl',
        theme: 'silver',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
            'fullscreen', 'insertdatetime', 'media', 'table', 'paste', 'help',
            'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image media | code fullscreen help',
        menubar: 'file edit view insert format tools table help',
        branding: false,
        height: 300,
        body_class: 'mce-content-body',
        content_style: 'body { font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif; font-size:14px; line-height:1.6; }',
        paste_as_text: false,
        setup: function(editor) {
            // Optional: Add custom setup if needed
        }
    });
}

// Reinitialize TinyMCE when needed (useful for dynamically added elements)
export function reinitTinyMCE(selector = '.tinymce-editor') {
    tinymce.remove();
    initTinyMCE(selector);
}

// Get editor content
export function getTinyMCEContent(editorId) {
    const editor = tinymce.get(editorId);
    return editor ? editor.getContent() : '';
}

// Set editor content
export function setTinyMCEContent(editorId, content) {
    const editor = tinymce.get(editorId);
    if (editor) {
        editor.setContent(content);
    }
}
