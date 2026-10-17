<?php
// Every hit is logged: `docker compose logs -f chaos`
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$q = $_GET;
error_log(sprintf('[%s] %s', date('H:i:s.v'), $_SERVER['REQUEST_URI']));

switch (true) {
    case $path === '/ok':
        echo '<html lang="en"><head><title>OK page</title><meta name="description" content="fine">'
            .'<meta property="og:image" content="/img.png"></head></html>';
        break;
    case $path === '/slow':
        usleep(((int) ($q['ms'] ?? 3000)) * 1000);
        echo '<title>Slow page</title>';
        break;
    case preg_match('#^/status/(\d{3})$#', $path, $m) === 1:
        http_response_code((int) $m[1]);
        break;
    case preg_match('#^/redirect/(\d+)$#', $path, $m) === 1:
        $n = (int) $m[1];
        header('Location: '.($n > 1 ? '/redirect/'.($n - 1) : '/ok'), true, 302);
        break;
    case $path === '/429':
        http_response_code(429);
        header('Retry-After: '.(int) ($q['retry_after'] ?? 10));
        break;
    case $path === '/flaky':
        if (mt_rand() / mt_getrandmax() < (float) ($q['fail_rate'] ?? 0.5)) {
            http_response_code(503);
            break;
        }
        echo '<title>Flaky but fine</title>';
        break;
    case $path === '/huge':
        echo '<title>Huge</title>';
        for ($i = 0; $i < 50_000; $i++) {
            echo str_repeat('x', 1024); // ~50 MB
        }
        break;
    case $path === '/no-title':
        echo '<html lang="en"><body>no title here</body></html>';
        break;
    case str_starts_with($path, '/title/'):
        printf('<title>%s</title>', htmlspecialchars(urldecode(substr($path, 7))));
        break;
    case $path === '/redirect-to':
        header('Location: '.($q['url'] ?? '/ok'), true, 302);
        break;
    default:
        http_response_code(404);
}
