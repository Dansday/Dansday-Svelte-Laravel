<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A content write was rejected for a reason the caller can act on (validation
 * failure, missing row, category still in use). The message is safe to show to
 * an API/MCP client.
 */
class ContentWriteException extends RuntimeException
{
}
