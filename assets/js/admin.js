(function ($) {
    var $modal, $editor;

    $(document).ready(function () {
        init();
    });

    var handleAdvertiserClick = (function () {
        var $input, $form;
        $(document).ready(function () {
            $input = $('#tds-advertiser-delete-id');
            $form = $input.closest('form');
        });

        return function (e) {
            var $el = $(this);
            if ($el.data('action') == 'remove') {
                if (confirm('Are you sure you wish to permanently delete\nthis advertiser from your listing?')) {
                    $input.val($(this).data('id'));
                    $form.submit();
                }
            }
        }
    })();

    var handleAdvertisementClick = (function () {
        var $input, $form;
        $(document).ready(function () {
            $input = $('#tds-advertisement-delete-id');
            $form = $input.closest('form');
        });

        return function (e) {
            var $el = $(this);
            if ($el.data('action') == 'remove') {
                if (confirm('Are you sure you wish to permanently delete\nthis advertisement from your listing?')) {
                    $input.val($el.data('id'));
                    $form.submit();
                }
            }
        }
    })();

    function init() {
        // Take care of closing our custom alert boxes
        $(document).on('click', '.alert > .close', function (e) {
            $(this).closest('.alert').fadeOut();
        });

        $modal = $(document.getElementById('modal-tds-plugin'));
        $(document).on('click', '[data-tds="modal"]', function (e) {
            var action = $(this).data('action');
            switchModalContent(action);

            switch (action) {
                case 'add-advertiser':
                    showAddAdvertiser();
                    break;

                case 'add-advertisement':
                    showAddAdvertisement();
                    break;

                case 'edit-advertisement':
                    launchEditAdvertisement($(this).data('id'));
                    break;

                default:
                    console.info('unknown action');
            }
        });

        $(document).on('click', '[data-tds="advertiser"]', handleAdvertiserClick);
        $(document).on('click', '[data-tds="advertisement"]', handleAdvertisementClick);
        setupEditor();
    }

    function setupEditor() {
        $editor = ace.edit('editor');
        $editor.setTheme('ace/theme/monokai');
        $editor.getSession().setMode('ace/mode/html');

        // We need to be sure to move the editor content into the text field before submission
        var $content = $('#advertisement-content');
        $('#tds-advertisement-form').on('submit', function () {
            $content.val($editor.getValue());
            return true; // continue processing
        });
    }

    function switchModalContent(action) {
        $modal.find('> .modal-content').hide();
        $modal.find('> .modal-content.'+ action).show();
    }

    function showModal(title, id, footer) {
        title = title || '';
        id = id || 'modal-tds-plugin';
        footer = footer || '';

        tb_show(title, "#TB_inline?height=420&width=620&inlineId=" + id, footer);
    }

    function showAddAdvertiser() {
        showModal('Add Advertiser', 'modal-tds-plugin', 'foobar');
    }

    function showAddAdvertisement() {
        var $form = $('#tds-advertisement-form');
        var $idfield = $form.find('input[name="id"]');
        if ($idfield) $idfield.remove();

        $form.find('#advertisement-advertiser').val(null);
        $form.find('input[name="action"]').val('tds-add-advertisement');
        $form.find('#advertisement-name').val(null);
        $form.find('#advertisement-description').val(null);

        $editor.setValue('');
        showModal('Add Advertisement', 'modal-tds-plugin', 'blargh');
    }

    function launchEditAdvertisement(id) {
        $.post(ajaxurl, { action: 'load_advertisement', id: id }).then(function (res) {
            var data = JSON.parse(res);
            var $form = $('#tds-advertisement-form');
            var $idfield = $('<input type="hidden" name="id">').val(data.id);
            $form.prepend($idfield);

            $form.find('#advertisement-advertiser').val(data.advertiser_id||null);
            $form.find('input[name="action"]').val('tds-edit-advertisement');
            $form.find('#advertisement-name').val(data.name||null);
            $form.find('#advertisement-description').val(data.description||null);

            $editor.setValue(data.content||null);
            switchModalContent('add-advertisement');
            showModal('Edit Advertisement', 'modal-tds-plugin', '');
        });
    }

    function sendAjax(data, cb) {
        return $.post(ajaxurl, data, function (res) {
            cb(JSON.parse(res));
        });
    }
})(jQuery);
