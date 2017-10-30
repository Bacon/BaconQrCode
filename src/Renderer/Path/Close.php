<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Path;

final class Close
{
    /**
     * @var self|null
     */
    private static $instance;

    private function __construct()
    {
    }

    public static function instance() : self
    {
        return self::$instance ?: self::$instance = new self();
    }
}
