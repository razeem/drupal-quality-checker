<?php

declare(strict_types=1);

namespace Razeem\DrupalQualityChecker\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class FileCopierPlugin implements PluginInterface, EventSubscriberInterface
{

  /**
   * This method is called when the plugin is activated. It allows the plugin
   * to perform any necessary setup or initialization.
   * 
   * {@inheritDoc}
   */
  public function activate(Composer $composer, IOInterface $io)
  {
    // Nothing to do here.
  }

  /**
   * Attach package installation events.
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    return [
      ScriptEvents::POST_INSTALL_CMD => ['copyFiles', 10],
      ScriptEvents::POST_UPDATE_CMD => ['copyFiles', 10],
    ];
  }
  

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
