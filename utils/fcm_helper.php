<?php

/**
 * FCM (Firebase Cloud Messaging) Helper — pure PHP, no composer required.
 *
 * Sends push notifications to all registered app devices using the
 * FCM HTTP v1 API with a Google service-account key file.
 *
 * Key file location:  <project_root>/google_service.json
 * (The file is the Firebase Admin SDK service account key you download
 *  from Firebase Console → Project Settings → Service accounts.)
 *
 * Flow:
 *   1. Build & sign a JWT (RS256) with the service-account private key
 *   2. Exchange JWT for an OAuth2 access token
 *   3. POST the message to https://fcm.googleapis.com/v1/projects/{project}/messages:send
 *
 * Usage:
 *   require_once __DIR__ . "/fcm_helper.php";
 *   send_fcm_broadcast($pdo, "New Test", "A new test is live!", "test", 1);
 */

// Path to the Google service account JSON (project root)
define('FCM_CREDENTIALS_FILE', __DIR__ . '/../google_service.json');

// OAuth2 endpoints
define('FCM_OAUTH_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('FCM_API_URL', 'https://fcm.googleapis.com/v1/projects/%s/messages:send');

// Access-token cache file (avoids re-fetching OAuth token on every notification)
define('FCM_TOKEN_CACHE_FILE', __DIR__ . '/.fcm_token_cache');

// Per-device HTTP timeout (seconds) — keep low so admin add-APIs never hang
// while waiting for a broadcast to many devices.
define('FCM_HTTP_TIMEOUT', 5);

/**
 * Load and decode the Google service account key file.
 *
 * @return array|null Credentials array, or null if file missing/invalid
 */
function fcm_get_credentials()
{
    if (!file_exists(FCM_CREDENTIALS_FILE)) {
        error_log("FCM: credentials file not found at " . FCM_CREDENTIALS_FILE);
        return null;
    }

    $json = file_get_contents(FCM_CREDENTIALS_FILE);
    $creds = json_decode($json, true);

    if (
        !$creds ||
        empty($creds['client_email']) ||
        empty($creds['private_key']) ||
        empty($creds['project_id'])
    ) {
        error_log("FCM: credentials file is invalid (missing client_email/private_key/project_id)");
        return null;
    }

    return $creds;
}

/**
 * Base64url encode.
 */
function fcm_base64url($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Create a signed JWT (RS256) for the service account.
 */
function fcm_create_jwt($creds)
{
    if (!$creds || empty($creds['private_key']) || empty($creds['client_email'])) {
        error_log("FCM: cannot create JWT — credentials missing");
        return null;
    }

    $now = time();

    $header = json_encode([
        "alg" => "RS256",
        "typ" => "JWT"
    ]);

    $claims = json_encode([
        "iss"   => $creds['client_email'],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud"   => FCM_OAUTH_TOKEN_URL,
        "iat"   => $now,
        "exp"   => $now + 3600
    ]);

    $signingInput = fcm_base64url($header) . "." . fcm_base64url($claims);

    $privateKey = openssl_pkey_get_private($creds['private_key']);
    if (!$privateKey) {
        error_log("FCM: unable to load private key");
        return null;
    }

    $signature = '';
    $ok = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    openssl_free_key($privateKey);

    if (!$ok) {
        error_log("FCM: failed to sign JWT");
        return null;
    }

    return $signingInput . "." . fcm_base64url($signature);
}

/**
 * Exchange the JWT for an OAuth2 access token.
 *
 * @return string|null Access token, or null on failure
 */
/**
 * Get a valid OAuth2 access token, using the file cache when possible.
 *
 * @param array $creds Service account credentials
 * @return string|null Access token, or null on failure
 */
function fcm_get_access_token($creds)
{
    if (!$creds) {
        return null;
    }

    // Use cached token if still valid (Google tokens live ~1 hour)
    if (file_exists(FCM_TOKEN_CACHE_FILE)) {
        $cache = json_decode(file_get_contents(FCM_TOKEN_CACHE_FILE), true);
        if ($cache && !empty($cache['token']) && !empty($cache['expires_at'])) {
            if ((int)$cache['expires_at'] > time() + 60) {
                return $cache['token'];
            }
        }
    }

    $jwt = fcm_create_jwt($creds);
    if (!$jwt) {
        return null;
    }

    $postData = http_build_query([
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion"  => $jwt
    ]);

    $ch = curl_init(FCM_OAUTH_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => FCM_HTTP_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("FCM: OAuth curl error: " . $curlError);
        return null;
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200 || empty($data['access_token'])) {
        error_log("FCM: OAuth token failed (HTTP $httpCode): " . substr($response, 0, 500));
        return null;
    }

    // Cache the token for ~55 minutes
    $expiresIn = max(60, (int)($data['expires_in'] ?? 3600));
    @file_put_contents(
        FCM_TOKEN_CACHE_FILE,
        json_encode([
            'token'      => $data['access_token'],
            'expires_at' => time() + $expiresIn
        ])
    );

    return $data['access_token'];
}

/**
 * Send a single FCM message to one device token.
 *
 * @param string $accessToken OAuth2 access token
 * @param string $projectId   Firebase project id
 * @param string $token       Device registration token
 * @param string $title       Notification title
 * @param string $body        Notification body
 * @param string $type        'test' | 'solution' | 'custom'
 * @param int    $notifId     Notification id (sent in data payload)
 * @return array{ok: bool, http_code: int, message: string}
 */
function fcm_send_to_token($accessToken, $projectId, $token, $title, $body, $type, $notifId)
{
    $payload = [
        "message" => [
            "token" => $token,
            "notification" => [
                "title" => (string)$title,
                "body"  => (string)$body
            ],
            "data" => [
                "notification_id" => (string)$notifId,
                "type"            => (string)$type,
                "title"           => (string)$title,
                "message"         => (string)$body
            ]
        ]
    ];

    $url = sprintf(FCM_API_URL, $projectId);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_TIMEOUT        => FCM_HTTP_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['ok' => false, 'http_code' => 0, 'message' => $curlError];
    }

    // FCM returns 200 for success, 404 for invalid token
    $decoded = json_decode($response, true);
    $msg = $decoded['error']['message'] ?? 'success';

    return [
        'ok'        => ($httpCode === 200),
        'http_code' => $httpCode,
        'message'   => $msg
    ];
}

/**
 * Send a push notification to ALL registered devices.
 * Best-effort: never throws, never breaks the calling flow.
 *
 * @param PDO    $pdo     Database connection
 * @param string $title   Notification title
 * @param string $body    Notification body
 * @param string $type    'test' | 'solution' | 'custom'
 * @param int    $notifId Notification id
 * @return array{status: bool, sent: int, failed: int, message: string}
 */
function send_fcm_broadcast($pdo, $title, $body = '', $type = 'custom', $notifId = 0)
{
    // Default result when FCM is not configured
    $result = ['status' => false, 'sent' => 0, 'failed' => 0, 'message' => 'FCM not configured'];

    try {
        $creds = fcm_get_credentials();
        if (!$creds) {
            return $result;
        }

        $accessToken = fcm_get_access_token($creds);
        if (!$accessToken) {
            $result['message'] = 'Could not obtain OAuth access token';
            return $result;
        }

        // Fetch all registered device tokens
        $stmt = $pdo->query("SELECT id, token FROM device_tokens");
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($devices)) {
            $result['message'] = 'No registered devices';
            return $result;
        }

        $sent = 0;
        $failed = 0;
        $invalidTokenIds = [];

        foreach ($devices as $device) {
            $res = fcm_send_to_token(
                $accessToken,
                $creds['project_id'],
                $device['token'],
                $title,
                $body,
                $type,
                $notifId
            );

            if ($res['ok']) {
                $sent++;
            } else {
                $failed++;
                // 404 = UNREGISTERED / invalid token → clean it up
                if ($res['http_code'] === 404) {
                    $invalidTokenIds[] = (int)$device['id'];
                }
            }
        }

        // Remove stale tokens
        if (!empty($invalidTokenIds)) {
            $in = implode(',', array_fill(0, count($invalidTokenIds), '?'));
            $del = $pdo->prepare("DELETE FROM device_tokens WHERE id IN ($in)");
            $del->execute($invalidTokenIds);
        }

        $result = [
            'status'  => ($sent > 0),
            'sent'    => $sent,
            'failed'  => $failed,
            'message' => "Push sent to $sent device(s), $failed failed"
        ];

    } catch (Exception $e) {
        $result['message'] = 'FCM error: ' . $e->getMessage();
        error_log("FCM broadcast failed: " . $e->getMessage());
    }

    return $result;
}
