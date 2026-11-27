<?php

namespace App\Services\Ai;

use RuntimeException;

/**
 * The AI provider could not produce a usable answer (not configured, network
 * error, rate limit, safety block, truncated or malformed output).
 * The message is safe to show to end users.
 */
class AiUnavailableException extends RuntimeException {}
