<?php

namespace App\Console;

use Symfony\Component\Finder\SplFileInfo;
use Spatie\DirectoryCleanup\Policies\CleanupPolicy;

class DirectoryCleanupPolicy implements CleanupPolicy
{
    public function shouldDelete(SplFileInfo $file) : bool
    {
        $filesToKeep = ['.gitignore'];
        
        return ! in_array($file->getFilename(), $filesToKeep);
    }
}