<a href="<?= $manageUiUrl ?>" target="_blank" class="pull-right"><?= t('iMoneza.com Management Log In') ?></a>
<h2 class="branded-header"><img src="<?= \iMoneza\Drupal\App::asset('images/logo-square.jpg'); ?>" alt="logo"> <?= t('iMoneza Access Configuration') ?></h2>

<section class="row">
    <div class="i-card text-center">
        <h3 id="imoneza-property-title"><?= $propertyTitle ?></h3>
    </div>
</section>

<section class="row">
    <div>
        <div class="i-card">
            <h3><?= t("API Access Credentials") ?></h3>
            <?= drupal_render($form['manage_api']) ?>
            <hr>
            <?= drupal_render($form['access_api']) ?>
        </div>
    </div>
    <aside>
        <div class="i-card">
            <h4><?= t("API Access") ?></h4>
            <p>
                <?= t("Your secure iMoneza data and configuration is hosted remotely.  To identify your website and account,
                        while keeping you fully secure, we need access to specific API Keys.  Protect these like you would protect
                        your username and password on any site.") ?>
            </p>
            <h5><?= t("Resource Management API") ?></h5>
            <p><?= t("This API allows your website to modify your settings and account information with iMoneza.  It also allows us to identify you and provide you the proper level of customization.") ?></p>
            <h5><?= t("Resource Access API") ?></h5>
            <p><?= t("This API is used primarily to connect to your content and users.  This is the basis of your PayWall measurement and enforcement.") ?></p>
        </div>
    </aside>
</section>

<section class="row">
    <div>
        <div class="i-card">
            <h3><?= t("Access Control Method") ?></h3>
            <?= drupal_render($form['access_control_method']) ?>
        </div>
    </div>
    <aside>
        <div class="i-card">
            <h4><?= t("Which Method is For Me?") ?></h4>
            <p>
                <?= t("Client-side access is usually the best choice for most content.  Your content is protected quickly and easily.
                        For premium, ultra-high quality content, server-side access provides a slower, but more robust security model.") ?>
            </p>
        </div>
    </aside>
</section>

<?php if ($isDynamicallyCreateResources): ?>
<section class="row">
    <div>
        <div class="i-card">
            <h3><?= t("Dynamically Create Resources") ?></h3>
            <p>
                <?php if (!empty($postsQueuedForProcessing)) : ?>
                    <?php
                    echo t('Your resources are being added to iMoneza.');
                    echo ' ';
                    echo format_plural($postsQueuedForProcessing, 'There is 1 remaining.', 'There are @count remaining.');
                    ?>
                <?php else : ?>
                <?= t('Congratulations!  All of your content is managed by iMoneza.'); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="i-card">
    <?= drupal_render_children($form); ?>
</div>
