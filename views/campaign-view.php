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
            <li class="disabled"><a href="#">Details</a></li>
            <li class="current"><a href="#">Display</a></li>
        </ul>
    </div>
    <div class="tab-content">
        <form name="campaign-entry" class="campaign-form" action="" method="POST">
            <fieldset class="campaign-details">
                <legend>View Details</legend>
                <div class="ad-controls">
                    <div class="ad-slots-list hidden" id="campaign-view-slots">
                        <div class="list-controls">
                            <button class="btn btn-primary" id="campaign-add-slot">Add Slot</button>
                        </div>
                    </div>
                    <div class="no-slots" id="campaign-view-splash">
                        <h3>Create New Slots</h3>
                        <p>You must have at least one ad slot to use this campaign.</p>
                        <button class="btn btn-primary" id="new-campaign-add-slot">Add Slot</button>
                    </div>
                </div>
            </fieldset>
            <fieldset>
            <?php if ($action == 'edit') { ?>
                <input type="submit" name="campaign-update-view" value="Save Campaign" class="btn btn-action">
            <?php } else { ?>
                <input type="submit" name="campaign-create-view" value="Finalize Campaign" class="btn btn-action">
            <?php } ?>
            </fieldset>
        </form>
    </div>
</div>
<div class="hidden">
    <div id="tpl-ad-slot">
        <div class="ad-slot">
            <div class="slot-option option-label">
                <h4 class="slot-title"><span>Slot {{index}}</span></h4>
            </div>
            <div class="slot-option">
                <span>After article: <input type="text" name="slot-position-{{index}}" size="2"></span>
            </div>
        </div>
    </div>
</div>
