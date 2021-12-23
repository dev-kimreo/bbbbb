<?php

namespace App\Services\ThemeBuilders;

interface ThemeBuilderInterface
{
    public function build(int $theme_id);

    public function download();

    public function ftpUpload(string $host, int $port, string $user, string $password, string $rootPath);
}

