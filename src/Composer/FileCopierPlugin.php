<?php

declare(strict_types=1);

namespace Razeem\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class FileCopierPlugin implements PluginInterface, EventSubscriberInterface
{
  /**
   * @var Composer
   */
  private $composer;

  /**
   * @var IOInterface
   */
  private $io;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io): void
  {
    $this->composer = $composer;
    $this->io = $io;
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io): void {}

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io): void {}

  /**
   * Attach package installation events.
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array
  {
    return [
      ScriptEvents::POST_INSTALL_CMD => ['copyFilesToRoot', 10],
      ScriptEvents::POST_UPDATE_CMD => ['copyFilesToRoot', 10],
    ];
  }
  

  public static function copyFilesToRoot(Event $event)
  {
    $filesToCopy = [
      'phpcs.xml.dist',
      'phpmd.xml.dist',
      'grumphp.yml.dist',
      'phpstan.neon.dist'
    ];

    // Path to the dist directory.
    $sourceDir = __DIR__ . '/../../dist';
    $this->io->write("Source directory: $sourceDir\n");
    $this->io->write("DIR: " . __DIR__ . "\n");
    // Target directory (current working directory).
    $targetDir = getcwd();

    foreach ($filesToCopy as $file) {
      $srcFile = $sourceDir . '/' . $file;
      $dstFile = $targetDir . '/' . $file;

      if (file_exists($srcFile)) {
        copy($srcFile, $dstFile);
        $this->io->write("Copied: $srcFile to $dstFile\n");
      }
      else {
        $this->io->write("File not found: $srcFile\n");
      }
    }
    $this->io->write('<fg=green>Configuration files are copied successfully.</fg=green>');

  }

}
