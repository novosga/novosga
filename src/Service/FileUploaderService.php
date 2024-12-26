<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Novosga\Service\FileUploaderServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileUploaderService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FileUploaderService implements FileUploaderServiceInterface
{
    private const UPLOADS_DIR = 'public/uploads';
    private const PUBLIC_UPLOADS_PATH = '/uploads';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $basePath,
    ) {
    }

    public function upload(UploadedFile $uploadedFile, string $key): string
    {
        $name = sprintf(
            "%s.%s",
            $key,
            $uploadedFile->guessExtension(),
        );
        $uploadDir = sprintf("%s/%s", $this->basePath, self::UPLOADS_DIR);
        $publicPath = sprintf("%s/%s?_=%s", self::PUBLIC_UPLOADS_PATH, $name, time());

        $uploadedFile->move($uploadDir, $name);

        return $publicPath;
    }
}
