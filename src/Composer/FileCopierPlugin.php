<?php

namespace Razeem\DrupalQualityChecker\Plugin;

use Composer\Script\Event;

class FileCopierPlugin
{
  public static function copyFiles(Event $event)
  {
    $filesToCopy = [
      'phpcs.xml.dist',
      'phpmd.xml.dist',
      'grumphp.yml.dist',
      'phpstan.neon.dist'
    ];

    // Path to the dist directory.
    $sourceDir = __DIR__ . '/../../dist';
    echo "Source directory: $sourceDir\n";
    echo "DIR: " . __DIR__ . "\n";
    // Target directory (current working directory).
    $targetDir = getcwd();

    foreach ($filesToCopy as $file) {
      $srcFile = $sourceDir . '/' . $file;
      $dstFile = $targetDir . '/' . $file;

      if (file_exists($srcFile)) {
        copy($srcFile, $dstFile);
        echo "Copied: $srcFile to $dstFile\n";
      }
      else {
        echo "File not found: $srcFile\n";
      }
    }
  }
}
