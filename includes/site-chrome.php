<?php
require_once __DIR__ . '/settings.php';

function site_header(string $active = ''): void {
    $logo = htmlspecialchars(get_setting('logo_path'), ENT_QUOTES, 'UTF-8');
    $aboutOn = get_setting_bool('about_enabled');
    $testimonialsOn = get_setting_bool('testimonials_enabled');

    $tab = function (string $key, string $href, string $label) use ($active) {
        $current = $active === $key ? ' aria-current="page"' : '';
        echo '<a href="' . $href . '"' . $current . '>' . $label . '</a>';
    };
    ?>
    <header class="site-header">
      <div class="header-inner">
        <a class="brand" href="/">
          <img src="<?= $logo ?>" alt="ConAlert" style="height:34px;width:auto;">
        </a>
        <nav class="tabs">
          <?php
            $tab('home', '/', 'Home');
            $tab('cases', '/cases.php', 'Case Wall');
            $tab('submit', '/submit.php', 'Submit a Case');
            $tab('resources', '/resources.php', 'Resources');
            if ($testimonialsOn) { $tab('testimonials', '/testimonials.php', 'Testimonials'); }
            if ($aboutOn) { $tab('about', '/about.php', 'About Us'); }
          ?>
        </nav>
      </div>
    </header>
    <?php
}

function site_footer(): void {
    $logo = htmlspecialchars(get_setting('logo_path'), ENT_QUOTES, 'UTF-8');
    $about = get_setting('footer_about');
    $email = get_setting('contact_email');
    $phone = get_setting('contact_phone');
    $socials = [
        'X / Twitter' => get_setting('social_x'),
        'Facebook'    => get_setting('social_facebook'),
        'Instagram'   => get_setting('social_instagram'),
        'TikTok'      => get_setting('social_tiktok'),
        'LinkedIn'    => get_setting('social_linkedin'),
    ];
    $aboutOn = get_setting_bool('about_enabled');
    $testimonialsOn = get_setting_bool('testimonials_enabled');
    $year = date('Y');
    ?>
    <footer class="site-footer">
      <div class="footer-grid">
        <div class="footer-col">
          <div class="footer-brand"><img src="<?= $logo ?>" alt="ConAlert"></div>
          <p><?= htmlspecialchars($about, ENT_QUOTES, 'UTF-8') ?></p>
          <?php if (array_filter($socials)): ?>
            <div class="footer-social">
              <?php foreach ($socials as $label => $url): if (empty($url)) continue; ?>
                <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="footer-col">
          <h4>Site</h4>
          <ul>
            <li><a href="/cases.php">Case wall</a></li>
            <li><a href="/submit.php">Submit a case</a></li>
            <li><a href="/resources.php">Resources</a></li>
            <?php if ($testimonialsOn): ?><li><a href="/testimonials.php">Testimonials</a></li><?php endif; ?>
            <?php if ($aboutOn): ?><li><a href="/about.php">About us</a></li><?php endif; ?>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Contact</h4>
          <ul>
            <?php if ($email): ?><li><a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></a></li><?php endif; ?>
            <?php if ($phone): ?><li><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></li><?php endif; ?>
            <li><a href="/admin/login.php">Admin login</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        &copy; <?= $year ?> ConAlert. Not a law firm, regulator, or payment processor — we cannot guarantee any outcome.
      </div>
    </footer>
    <?php
}
