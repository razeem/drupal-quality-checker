<?php

declare(strict_types=1);

namespace Razeem\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class implements a Composer plugin that copies configuration files
 * to the project root and validates commit messages against a specified pattern.
 * @psalm-suppress MissingConstructor
 */
class FileCopierPlugin implements PluginInterface, EventSubscriberInterface
{
  /**
   * @var IOInterface
   */
  private $io;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io): void
  {
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
  

  public function copyFilesToRoot(Event $event)
  {
    $filesToCopy = [
      'phpcs.xml.dist',
      'phpmd.xml.dist',
      'grumphp.yml.dist',
      'phpstan.neon.dist'
    ];

    // Path to the dist directory.
    $sourceDir = __DIR__ . '/../../dist';
    // Target directory (current working directory).
    $targetDir = getcwd();

    // Read project code from project-code.txt
    $projectCodeFile = $targetDir . '/project-code.txt';
    $projectCode = file_exists($projectCodeFile) ? trim(file_get_contents($projectCodeFile)) : null;

    foreach ($filesToCopy as $file) {
      $srcFile = $sourceDir . '/' . $file;
      $dstFile = $targetDir . '/' . $file;

      if (file_exists($srcFile)) {
        if ($file === 'grumphp.yml.dist' && $projectCode !== null) {
          // Modify the content of grumphp.yml.dist only if project-code.txt exists
          $content = file_get_contents($srcFile);
          $content = str_replace('<project-code>', $projectCode, $content);
          file_put_contents($dstFile, $content);
          $this->io->write("Modified and copied: $srcFile to $dstFile\n");
        }
        else {
          // Just copy the file if it's not grumphp.yml.dist or if project-code.txt does not exist
          copy($srcFile, $dstFile);
          $this->io->write("Copied: $srcFile to $dstFile\n");
        }
      }
      else {
        $this->io->write("File not found: $srcFile\n");
      }
    }
    $this->io->write('<fg=green>Configuration files are copied successfully.</fg=green>');
  }

}
