<?php add_thickbox(); ?>
<div class="wrap">
    <?php if ($action == 'edit') { ?>
    <h2><?php esc_html_e('TDS Ads :: Update Campaign'); ?></h2>
    <?php } else { ?>
    <h2><?php esc_html_e('TDS Ads :: Create Campaign'); ?></h2>
    <?php } ?>
    <?php if (isset($notice)) \TDS\Ads\Admin\View::render('notice', $notice); ?>

    <div class="tabs">
        <ul>
            <li class="current"><a href="#">Details</a></li>
    <?php if ($action == 'edit') { ?>
    <li><a href="#">Display</a></li>
    <?php } else { ?>
        <li class="disabled"><a href="#">Display</a></li>
    <?php } ?>
        </ul>
    </div>
    <div class="tab-content">
        <form name="campaign-entry" class="campaign-form" action="" method="POST">
            <fieldset class="campaign-details">
                <legend>Campaign Details</legend>
                <p class="legend-help">Descriptive and organizational information for this campaign</p>
                <div class="field">
                    <label for="campaign-form-name">Name</label>
                    <input type="text" name="campaign-name" id="campaign-form-name" value="<?php esc_html_e($campaign_name); ?>">
                </div>
                <div class="field">
                    <label for="campaign-form-description">Description</label>
                    <textarea name="campaign-description" id="campaign-form-description" cols="80" rows="4"><?php esc_html_e($campaign_description); ?></textarea>
                </div>
            </fieldset>
            <fieldset class="campaign-ads">
                <legend>Campaign Ads</legend>
                <p class="legend-help">Select all the ads you wish to include in this campaign</p>
                <div class="field">
<?php
$split = floor(count($ads) / 2);
$tpl = <<<EOA
<div class="ad-details">
    <label><input type="checkbox" name="campaign-ads[]" value="%d">
    <span class="name">%s</span></label>
    <p class="advertiser">by <span>%s</span></p>
</div>
EOA;
for ($i = 0; $i < 2; $i++) {
    echo '<div class="ad-column">';
    $start = $split * $i;
    $end = $start == 0 ? $split : count($ads);
    for ($x = $start; $x < $end; $x++) {
        printf($tpl, $ads[$x]['id'], esc_html($ads[$x]['name']), esc_html($ads[$x]['advertiser']));
    }
    echo '</div>';
}
?>
                </div>
            </fieldset>
            <fieldset>
            <?php if ($action == 'edit') { ?>
                <input type="submit" name="campaign-update" value="Save Campaign" class="btn btn-action">
            <?php } else { ?>
                <input type="submit" name="campaign-create" value="Create Campaign" class="btn btn-action">
            <?php } ?>
            </fieldset>
        </form>
    </div>
</div>
