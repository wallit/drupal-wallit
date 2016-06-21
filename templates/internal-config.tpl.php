<h3><?= t('Internal Configuration') ?></h3>
<p>
    <?= t("Here you can override the URLs built into the plugin.  Do not use this section unless you know what you're doing.") ?>
    <strong><?= t('Leave them blank') ?></strong> <?= t("to use the default production URLs.") ?>
</p>

<?= drupal_render_children($form); ?>