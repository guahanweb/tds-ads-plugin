<?php add_thickbox(); ?>
<?php
class SlotTemplate {
    protected $ads;
    protected $template;
    protected $last_index = 0;

    public function __construct($ads) {
        $this->ads = $ads;
        $this->template = <<<EOT
    <div class="ad-slot">
        <div class="slot-option option-label">
            <h4 class="slot-title"><span>Slot <span class="index-holder">%s</span></span></h4>
        </div>
        <div class="slot-option">
            <p>After article: <input type="text" name="slot-position[]" size="2" value="%d"></p>
        </div>
        <div class="slot-option">
            <p>Display ad: <select name="slot-position-article[]">
                <option value="0">random</option>
                %s
            </select></p>
        </div>
    </div>
EOT;
    }

    public function getTemplate() {
        return sprintf($this->template, '{{index}}', 2, $this->getAdOptions());
    }

    public function renderSlot($data) {
        static $index = 0;
        $index++;
        $this->last_index = $index;
        return sprintf($this->template, $index, $data['position'], $this->getAdOptions($data['campaign_ad_id']));
    }

    public function getLastIndex() {
        return $this->last_index;
    }

    protected function getAdOptions($selected = null) {
        $out = '';
        foreach ($this->ads as $ad) {
            $out .= sprintf('<option value="%d"%s>%s</option>',
                $ad['id'],
                ($ad['id'] === $selected) ? ' selected="selected"' : '',
                $ad['name']
            );
        }
        return $out;
    }
}
$template = new SlotTemplate($campaign_ads);
?>
<div class="wrap">
    <?php if ($action == 'edit') { ?>
    <h2><?php esc_html_e('TDS Ads :: Update Campaign'); ?></h2>
    <?php } else { ?>
    <h2><?php esc_html_e('TDS Ads :: Create Campaign'); ?></h2>
    <?php } ?>
    <?php if (isset($notice)) \TDS\Ads\Admin\View::render('notice', $notice); ?>

    <div class="tabs">
        <ul>
            <?php if ($action == 'create') { ?>
            <li class="disabled"><a href="#">Details</a></li>
            <?php } else { ?>
            <li><a href="<?php echo $link_details; ?>">Details</a></li>
            <?php } ?>
            <li class="current"><a href="#">Display</a></li>
        </ul>
    </div>
    <div class="tab-content">
        <form name="campaign-entry" class="campaign-form" action="" method="POST">
            <input type="hidden" name="update-view" value="update-view">
            <fieldset class="campaign-details">
                <legend><?php esc_html_e($details['campaign-name']); ?></legend>
                <div class="ad-controls">
                    <div class="ad-slots-list<?php echo (count($slots) < 1) ?  ' hidden' : ''; ?>" id="campaign-view-slots">
                        <div class="list-controls">
                            <button class="btn btn-primary" id="campaign-add-slot">Add Slot</button>
                        </div>
                        <?php
                        foreach ($slots as $slot) {
                            echo $template->renderSlot($slot);
                        }
                        ?>
                    </div>
                    <?php if (count($slots) < 1): ?>
                    <div class="no-slots" id="campaign-view-splash">
                        <h3>Create New Slots</h3>
                        <p>You must have at least one ad slot to use this campaign.</p>
                        <button class="btn btn-primary" id="new-campaign-add-slot">Add Slot</button>
                    </div>
                    <?php endif; ?>
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
        <?php echo $template->getTemplate(); ?>
    </div>
</div>
<script>
var SLOTS_LAST_INDEX = <?php echo $template->getLastIndex(); ?>;
</script>
