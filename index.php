<?php





function checkDev($user_agent)
{
    $device = "";
    $detect = new Mobile_Detect;
    $detect->setUserAgent($user_agent);
    if ($detect->isMobile()) {
        $device = "mobile";
    } else if ($detect->isTablet()) {
        $device = "tablet";
    } else {
        $device = "desktop";
    }

    return $device;
}

function checkUA($user_agent)
{
    if (empty($user_agent)) {
        return false;
    }

    $user_agent = mb_strtolower($user_agent);

    $bots = array(
        // Yandex
        'YandexBot', 'YandexBot/3.0', 'YandexAccessibilityBot', 'YandexMobileBot', 'YandexDirectDyn', 'YandexScreenshotBot',
        'YandexImages', 'YandexVideo', 'YandexVideoParser', 'YandexMedia', 'YandexBlogs', 'YandexFavicons',
        'YandexWebmaster', 'YandexPagechecker', 'YandexImageResizer', 'YandexAdNet', 'YandexDirect',
        'YaDirectFetcher', 'YandexCalendar', 'YandexSitelinks', 'YandexMetrika', 'YandexNews', 'YandexDialogs',
        'YandexNewslinks', 'YandexCatalog', 'YandexAntivirus', 'YandexMarket', 'YandexVertis',
        'YandexForDomain', 'YandexSpravBot', 'YandexSearchShop', 'YandexMedianaBot', 'YandexOntoDB',
        'YandexOntoDBAPI', 'YandexTurbo', 'YandexVerticals', 'YandexTracker',

        // Google
        'Google-InspectionTool', 'Googlebot', 'Googlebot-Image', 'Mediapartners-Google', 'AdsBot-Google', 'APIs-Google',
        'AdsBot-Google-Mobile', 'AdsBot-Google-Mobile', 'Googlebot-News', 'Googlebot-Video',
        'AdsBot-Google-Mobile-Apps', 'FeedFetcher-Google', 'Google-Read-Aloud', 'DuplexWeb-Google',
        'Google Favicon', 'googleweblight', 'Storebot-Google',

        // Other
        'Mail.RU_Bot', 'BingPreview', 'Twitterbot', 'bingbot', 'Accoona', 'ia_archiver', 'Ask Jeeves',
        'OmniExplorer_Bot', 'W3C_Validator', 'slurp',
        'WebAlta', 'YahooFeedSeeker', 'Yahoo!', 'Ezooms', 'Tourlentabot', 'MJ12bot',
        'SearchBot', 'SiteStatus', 'Nigma.ru', 'Baiduspider', 'Statsbot', 'SISTRIX', 'AcoonBot', 'findlinks',
        'proximic', 'OpenindexSpider', 'statdom.ru', 'Exabot', 'Spider', 'SeznamBot', 'oBot', 'C-T bot',
        'Updownerbot', 'Snoopy', 'heritrix', 'Yeti', 'DomainVader', 'DCPbot', 'PaperLiBot', 'StackRambler',
        'msnbot', 'msnbot-media', 'msnbot-news',
    );

    foreach ($bots as $bot) {
        $bot = mb_strtolower($bot);
        if (stristr($user_agent, $bot) !== false) {
            return $bot;
        }
    }

    return false;
}

function checkGoogle()
{
    $googlebot = 0;
    $remoteHost = gethostbyaddr(strip_tags($_SERVER["HTTP_CF_CONNECTING_IP"]));
    if (substr($remoteHost, -14) == ".googlebot.com") {
        if (gethostbyname($remoteHost) == $_SERVER['HTTP_CF_CONNECTING_IP']) {
            if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), "googlebot")) {
                if (strlen($_SERVER['HTTP_ACCEPT']) >= 36) {
                    $googlebot = 1;
                }
            }
        }
    }

    return $googlebot;
}

function fetchPageWithBase($url, $baseUrl)
{
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
    $referer = 'https://google.com';
    $cookie = 'name1=value1; name2=value2';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            echo 'HTTP Error: ' . $httpCode;
        } else {
            // Создаем объект DOMDocument и загружаем HTML-код ответа
            $doc = new DOMDocument();
            $doc->loadHTML($response);

            // Создаем объект DOMXPath для выполнения поиска по DOM
            $xpath = new DOMXPath($doc);

            // Создаем новый элемент <base> и устанавливаем его атрибут href
            $base = $doc->createElement('base');
            $base->setAttribute('href', $baseUrl);

            // Находим элемент <head> и вставляем новый элемент <base> перед ним
            $head = $xpath->query('//head')->item(0);
            $head->insertBefore($base, $head->firstChild);

            // Возвращаем измененный HTML-код
            return $doc->saveHTML();
        }
    }

    curl_close($ch);
}




function status_klo($status_check_rkn, $status_check_google, $status_check_ua, $status_check_device)
{
    $us_ag = $_SERVER['HTTP_USER_AGENT'];
    $us_ip = $_SERVER['REMOTE_ADDR'];
    $status_user = 'bot';



    if ($status_check_google == 'Y') {
        $is_check_google = checkGoogle();
        //bot
        if ($is_check_google == 1) {
            @header("Cache-Control: max-age=0");
            @header('X-Frame-Options: DENY');
            @header("X-Robots-Tag: noarchive", true);
        }

        // user
        if ($is_check_google == 0) {

            @header("Cache-Control: max-age=0");
            @header('X-Frame-Options: DENY');
            // maintenance message

            header("HTTP/1.1 503 Service Temporarily Unavailable");
            header("Status: 503 Service Temporarily Unavailable");
            http_response_code(503);

            require_once(__DIR__ . '/FOR_USER.html'); // подключение страницы для обычного пользователя !!!!
            exit;
        }
    }

    if ($status_check_ua == 'Y') {
        $is_check_ua = checkUA($us_ag);
        if ($is_check_ua === false) {
            $status_user = 'user';
        } else {
            $status_user = 'bot';
        }
    }

    if ($status_check_device == 'Y') {
        $is_check_device = checkDev($us_ag);
        if ($is_check_device == "mobile") {
            $status_user = 'user';
        } else {
            $status_user = 'bot';
        }
    }

    return $status_user;
}

$status_klo = status_klo('Y', 'N', 'Y', 'N');
// первый параметр - проверка на ркн (Y - включена, N - выключена)
// второй параметр - проверка на гуглмобалсеч (Y - включена, N - выключена)
// третий параметр - проверка юзерагента пользователя (Y - включена, N - выключена)
// четвертый параметр - проверка девайса (Y - включена, N - выключена)


if ($status_klo == 'user') {
    // for users
    require_once(__DIR__ . '/FOR_USER.html');
} else {
    // for bot
    require_once(__DIR__ . '/FOR_BOT.html');
}
