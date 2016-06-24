<h2 class="branded-header"><img src="<?= \iMoneza\Drupal\App::asset('images/logo-square.jpg'); ?>" alt="logo"> <?= t('iMoneza Configuration') ?></h2>
<section class="row">
    <div>
        <div class="i-card">
            <p class="text-center">
                <?= t("Hey there!  It looks like it's your first time here.  Let's get started!") ?>
            </p>
        </div>
        <div class="i-card">
            <h3><?= t("Time to do a little configuration") ?></h3>
            <p>
                <?= t("Right now, all we need is your Resource Management API key and secret. This will help us custom tailor the rest of the plugin options for you.") ?>
                <?= t("Need to find these? ") ?>
                <a href="<?= $manageUiUrl ?>" target="_blank"><?= t('Go to iMoneza.com and log in.') ?></a>
            </p>
            <?= drupal_render($form['manage_api']) ?>
            <label for="edit-submit"></label>
            <?= drupal_render_children($form); ?>
        </div>
    </div>
    <aside>
        <div class="i-card">
            <h2 class="logo-header"><img src="<?= \iMoneza\Drupal\App::asset('images/logo-rectangle.jpg'); ?>" alt="logo"></h2>
            <h3><?= t("What is iMoneza?") ?></h3>
            <p><?= t("iMoneza is a digital micropayment paywall service. This plugin will add iMoneza's paywall to your site and allow you to manage your iMoneza resources from within WordPress.") ?></p>
            <p><strong><?= t("An iMoneza account is required.") ?></strong>  <?= sprintf(t("If you don't have one, it's simple and easy.  Just go to %s and sign up."), '<a href="https://www.imoneza.com/sign-up">iMoneza.com</a>') ?></p>
        </div>
    </aside>
</section>
