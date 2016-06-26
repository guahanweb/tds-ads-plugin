<?php if ($type == 'success'): ?>
<div class="wrap alert active">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
    <h3 class="key-status active"><?php esc_html_e("Success"); ?></h3>
    <p class="description"><?php esc_html_e($msg); ?></p>
</div>
<?php elseif ($type == 'failure'): ?>
<div class="wrap alert failure">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
    <h3 class="key-status failure"><?php esc_html_e("Failure"); ?></h3>
    <p class="description"><?php esc_html_e($msg); ?></p>
</div>
<?php endif; ?>
