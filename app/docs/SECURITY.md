# Security (what it means + what i did)

Short notes so we remember why this stuff exists.

SQL injection

If you build SQL by sticking user input into a string, someone can type SQL and mess with your database. We avoid that by using PDO prepared statements in the repos — the query shape is fixed and values are bound separately, so input is treated as data not as SQL code.

XSS

If you print whatever the user typed straight into the HTML, they can inject script tags and run code in other peoples browsers. Normal text in views goes through htmlspecialchars so angle brackets become safe text.

The CMS uses TinyMCE, which saves HTML. That HTML is not trusted: we run it through HtmlSanitizer (HTML Purifier) before saving and when showing. You need composer install in the app folder so ezyang/htmlpurifier is installed.

CSRF

A random site could trick your browser into sending a POST to our site while youre logged in (change password, add to cart). I put a secret token in forms / JS that only our server knows; Csrf::validate checks it with hash_equals. Used on login/register/reset, admin export, cart API, CMS forms. Cart token is in the footer as window.__CSRF_CART__.

CAPTCHA

Bots could spam registrations. I use a simple math question stored in the session (AuthService) so the answer has to match on the server.

Ticket / QR

Ticket codes should be random and not guessable. SecureToken generates codes; we can sign payloads with HMAC so a fake QR is harder to forge. Secret comes from app.php ticket_signing_secret or env HAARLEM_TICKET_SECRET in production. TicketRepository inserts unique codes when creating tickets.

HTTP headers

SecurityHeaders::send() in public/index.php adds a few headers browsers understand: dont sniff MIME types as something else, dont embed the site in random iframes, basic referrer and permissions policy. Not a magic fix but raises the bar.

Production

Use a long random HAARLEM_TICKET_SECRET. Use HTTPS in production. Cookie flags are in Session.php.
