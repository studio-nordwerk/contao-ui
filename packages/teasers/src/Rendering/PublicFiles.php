<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Rendering;

use Contao\Config;
use Contao\File;
use Contao\FilesModel;
use Contao\Validator;

final class PublicFiles
{
    public function resolve(string $identifier): FilesModel|null
    {
        $file = Validator::isUuid($identifier) ? FilesModel::findByUuid($identifier) : FilesModel::findByPath($identifier);
        $uploadPath = (string) (Config::get('uploadPath') ?: 'files');

        if (!$file || 'file' !== $file->type || !str_starts_with($file->path, $uploadPath.'/') || str_contains($file->path, '..') || !is_file($file->path) || !(new File($file->path))->isUnprotected()) {
            return null;
        }

        return $file;
    }
}
