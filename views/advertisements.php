<?php add_thickbox(); ?>
<div class="wrap">
    <h2><?php esc_html_e('TDS Ads :: Advertisements'); ?></h2>
    <?php if (isset($notice)) \TDS\Ads\Admin\View::render('notice', $notice); ?>
    <div class="dashboard">
        <div class="widget-row">
            <div class="region ads-advertisers" id="tds-advertisers">
                <header>
                    <h3><span class="title-text">Advertisers</span></h3>
                    <div class="toolbar">
                        <button class="fa fa-plus-circle add" data-tds="modal" data-action="add-advertiser"></button>
                    </div>
                </header>
                <section class="content">
                    <!-- Hidden form to handle deletion -->
                    <form name="tds-advertiser-delete" id="tds-advertiser-delete" action="" method="POST">
                        <input type="hidden" name="tds-advertiser-delete-id" id="tds-advertiser-delete-id" value="">
                    </form>
<?php
if (count($advertisers) > 0) {
    echo "<ul class=\"listing advertiser-list\">\n";
    foreach ($advertisers as $advertiser) {
        echo "<li class=\"advrtiser\">" . $advertiser['name'] . "<button type=\"button\" class=\"remove\" data-action=\"remove\" data-tds=\"advertiser\" data-id=\"${advertiser['id']}\">&times;</button></li>";
    }
    echo "</ul>\n";
} else {
    echo "<p class=\"no-results\">No advertisers</p>\n";
}
?>
                </section>
            </div>
            <div class="region ads-advertisements" id="tds-advertisements">
                <header>
                    <h3>Advertisements</h3>
                    <div class="toolbar">
                        <button class="fa fa-plus-circle add" data-tds="modal" data-action="add-advertisement"></button>
                    </div>
                </header>
                <section class="content">
                    List of advertisements goes here
                </section>
            </div>
        </div>
    </div>
</div>
<div id="modal-tds-plugin" style="display: none;">
    <div class="modal-content add-advertiser">
        <form name="tds-advertiser-form" action="" method="POST">
            <input type="hidden" name="action" value="tds-add-advertiser">
            <div class="input-group">
                <label for="advertiser-name">Name</label>
                <input type="text" name="name" id="advertiser-name">
            </div>
            <div class="input-group">
                <label for="advertiser-description">Description</label>
                <textarea name="description" id="advertiser-description"></textarea>
            </div>
            <div class="input-submission">
                <input type="submit" name="submit" value="Submit">
            </div>
        </form>
    </div>
    <div class="modal-content add-advertisement">
        <p>Some advertisement form goes here</p>
    </div>
</div>
