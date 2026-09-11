<?php
// config.php — fill these in with real values, then make sure this file is
// NOT publicly downloadable (the included .htaccess already blocks direct
// access to any file matching config.php, but double check on your host).

// From cPanel -> MySQL Databases. On most shared hosting the db name and
// user are prefixed with your cPanel username, e.g. "conalert_main" and
// "conalert_dbuser".
define('DB_HOST', 'localhost');
define('DB_NAME', 'yourcpanelusername_conalert');
define('DB_USER', 'yourcpanelusername_dbuser');
define('DB_PASS', 'your-db-password');

// A long random string, unique to this site, used to sign session cookies
// and CSRF tokens. Generate one with: php -r "echo bin2hex(random_bytes(32));"
define('APP_SECRET', 'change-this-to-a-long-random-string');

define('SITE_NAME', 'ConAlert');
define('CONTACT_EMAIL', 'cases@conalert.org');

// One-time use: lets db/seed.php run over the web on hosts with no SSH
// access. Set this to a long random value, run the seed once, then blank
// it out or delete db/seed.php entirely.
define('SEED_SECRET', 'change-this-then-remove-it-after-seeding');
