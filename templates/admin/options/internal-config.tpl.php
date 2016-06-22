<h2 class="branded-header"><img src="<?= \iMoneza\Drupal\App::asset('images/logo-square.jpg'); ?>" alt="logo"> <?= t('Internal Configuration') ?></h2>
<section class="row">
    <div>
        <div class="i-card">
            <h3><?= t('URLs') ?></h3>
            <p>
                <?= t("Here you can override the URLs built into the plugin.  Do not use this section unless you know what you're doing.") ?>
                <strong><?= t('Leave them blank') ?></strong> <?= t("to use the default production URLs.") ?>
            </p>
            <?= drupal_render($form['urls']) ?> 
        </div>
    </div>
</section>
<section class="row">
    <div>
        <div class="i-card">
            <?= drupal_render_children($form) ?>
        </div>
    </div>
</section>


