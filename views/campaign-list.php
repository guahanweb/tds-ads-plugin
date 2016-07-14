<?php add_thickbox(); ?>
<div class="wrap">
    <h2><?php esc_html_e('TDS Ads :: Campaigns'); ?></h2>
    <?php if (isset($notice)) \TDS\Ads\Admin\View::render('notice', $notice); ?>
    <div class="dashboard">
        <div class="widget widget-list">
            <div class="controls">
            <a class="btn btn-action" href="<?php echo $new_link; ?>">New Campaign</a>
            </div>
            <table class="campaigns campaign-list" border="0" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Hook</th>
                        <th>Created</td>
                        <th>Ads</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php
$tpl = <<<EOT
<tr class="%s">
    <td class="name">%s</td>
    <td class="hook">%s</td>
    <td class="created">%s</td>
    <td class="ads">%s</td>
    <td class="actions">%s</td>
</tr>
EOT;

$row = 'even';
foreach ($campaigns as $i => $campaign) {
    $ads = array();
    foreach ($campaign['ads'] as $ad) {
        $ads[] = $ad['name'];
    }

    $row = ($row === 'even') ? 'odd' : 'even';
    printf($tpl, $row,
        $campaign['name'],
        $campaign['hook'],
        date('F j, Y, h:i:s a', strtotime($campaign['created'])),
        implode('<br>', $ads),
        'actions'
    );
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>
