<div class="wrap">
    <h2><?php esc_html_e('TDS Ads :: Dashboard'); ?></h2>
    <?php if (isset($notice)) \TDS\Ads\Admin\View::render('notice', $notice); ?>
    <div class="dashboard" id="dashboard">
    </div>
</div>
