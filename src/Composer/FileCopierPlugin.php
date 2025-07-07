<?php

declare(strict_types=1);

namespace Razeem\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * This class implements a Composer plugin that copies configuration files
 * to the project root and validates commit messages against a specified pattern.
 * @psalm-suppress MissingConstructor
 */
class FileCopierPlugin implements PluginInterface, EventSubscriberInterface {
  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io): void {
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
  public static function getSubscribedEvents(): array {
    return [
      ScriptEvents::POST_INSTALL_CMD => ['copyFilesToRoot', 10],
      ScriptEvents::POST_UPDATE_CMD => ['copyFilesToRoot', 10],
    ];
  }

  /**
   * Copies configuration and hook files to the project root on Composer events.
   *
   * @param \Composer\Script\Event $event
   *   The Composer event object.
   */
  public function copyFilesToRoot(Event $event) {
    $filesToCopy = [
      'phpcs.xml.dist',
      'phpmd.xml.dist',
      'phpstan.neon.dist',
      'grumphp.yml.dist',
    ];

    // Path to the dist directory.
    $sourceDir = __DIR__ . '/../../dist';

    // Target directory (current working directory).
    $targetDir = getcwd();

    // Destination path to the git directory.
    $dstPreCommitFile = $targetDir . '/.git/hooks/pre-commit';
    if (file_exists($dstPreCommitFile)) {
      $currentGrumphp = "| exec 'vendor/bin/grumphp' 'git:pre-commit' '--skip-success-output')";
      $newGrumphp = "| exec 'vendor/bin/grumphp' 'git:pre-commit' '--skip-success-output' < /dev/tty)";
      // Copy the pre-commit file to the target directory.
      $preCommitContent = file_get_contents($dstPreCommitFile);
      $preCommitContent = str_replace($currentGrumphp, $newGrumphp, $preCommitContent);
      file_put_contents($dstPreCommitFile, $preCommitContent);
      $this->io->write("Modified $dstPreCommitFile\n");
    }
    else {
      // Soruce path to the git directory.
      $sourcePreCommitFile = $sourceDir . '/git/pre-commit';

      // If the pre-commit file does not exist, copy it to the target directory.
      copy($sourcePreCommitFile, $dstPreCommitFile);
      $this->io->write("Copied: $sourcePreCommitFile to $dstPreCommitFile\n");
    }

    // Read project code from project-code.txt
    $projectCodeFile = $targetDir . '/project-code.txt';
    $projectCode = file_exists($projectCodeFile) ? trim(file_get_contents($projectCodeFile)) : "ABC";
    foreach ($filesToCopy as $file) {
      $srcFile = $sourceDir . '/' . $file;
      $dstFile = $targetDir . '/' . $file;
      if (file_exists($srcFile)) {
        if ($file === 'grumphp.yml.dist' && $projectCode !== NULL) {
          // Modify the content of grumphp.yml.dist only if project-code.txt exists
          $content = file_get_contents($srcFile);
          $content = str_replace('<project-code>', $projectCode, $content);
          file_put_contents($dstFile, $content);
          $this->io->write("Modified and copied: $srcFile to $dstFile\n");
          // Check if grumphp.yml exists in the current working directory
          $grumphpFile = $targetDir . '/grumphp.yml';
          if (!file_exists($grumphpFile)) {
            file_put_contents($grumphpFile, $content);
            $this->io->write("Created: $grumphpFile with content from grumphp.yml.dist\n");
          }
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
    $this->io->write('<fg=green>Configuration files are processed successfully.</fg=green>');
  }

}
