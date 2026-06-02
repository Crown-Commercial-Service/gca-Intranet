<?php
/**
 * Container health check endpoint.
 *
 * Returns HTTP 200 OK so ECS and ALB health checkers get a clean response
 * without triggering the HTTPS redirect that FORCE_SSL_ADMIN applies to all
 * other WordPress URLs. Avoids the pattern where curl inside the container
 * follows a 301 to an external HTTPS URL it cannot reach.
 */
http_response_code(200);
header('Content-Type: text/plain');
echo 'OK';
