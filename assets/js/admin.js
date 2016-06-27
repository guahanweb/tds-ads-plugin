(function ($) {
    var $modal;

    $(document).ready(function () {
        init();
    });

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

                default:
                    console.info('unknown action');
            }
        });

        $(document).on('click', '[data-tds="advertiser"]', handleAdvertiserClick);
    }

    function switchModalContent(action) {
        $modal.find('> .modal-content').hide();
        $modal.find('> .modal-content.'+ action).show();
    }

    function showModal(title, id, footer) {
        title = title || '';
        id = id || 'modal-tds-plugin';
        footer = footer || '';

        tb_show(title, "#TB_inline?height=280&width=620&inlineId=" + id, footer);
    }

    function showAddAdvertiser() {
        showModal('Add Advertiser', 'modal-tds-plugin', 'foobar');
        $.post(ajaxurl, { action: 'add_advertiser' }).then(function (res) {
            var data = JSON.parse(res);
            console.log(data);
        }, function (err) {
            console.error(err);
        });
    }

    function handleAdvertiserClick(e) {
        var $input;
        var $el = $(this);
        if ($el.data('action') == 'remove') {
            if (confirm('Are you sure you wish to permanently delete\nthis advertiser from your listing?')) {
                $input = $('#tds-advertiser-delete-id');
                $input.val($(this).data('id'));
                $input.closest('form').submit();
            }
        }
    }

    function showAddAdvertisement() {
        showModal('Add Advertisement', 'modal-tds-plugin', 'blargh');
        $.post(ajaxurl, { action: 'add_advertisement' }).then(function (res) {
            var data = JSON.parse(res);
            console.log(data);
        }, function (err) {
            console.error(err);
        });

    }

    function sendAjax(data, cb) {
        return $.post(ajaxurl, data, function (res) {
            cb(JSON.parse(res));
        });
    }
})(jQuery);
