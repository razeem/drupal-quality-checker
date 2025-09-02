<?php

declare(strict_types=1);

namespace Razeem\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

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
    // Soruce path to the git directory.
    $sourcePreCommitFile = $sourceDir . '/git/pre-commit';
    if (file_exists($dstPreCommitFile)) {
      $currentGrumphp = "| exec 'vendor/bin/grumphp' 'git:pre-commit' '--skip-success-output')";
      $newGrumphp = "| exec 'vendor/bin/grumphp' 'git:pre-commit' '--skip-success-output' < /dev/tty)";
      // Copy the pre-commit file to the target directory.
      $preCommitContent = file_get_contents($dstPreCommitFile);
      $preCommitContent = str_replace($currentGrumphp, $newGrumphp, $preCommitContent);
      $this->fileCheckSum(
        $sourcePreCommitFile,
        $dstPreCommitFile,
        $preCommitContent,
        file_get_contents($dstPreCommitFile)
      );
    }
    else {
      if (!file_exists(dirname($dstPreCommitFile))) {
        mkdir(dirname($dstPreCommitFile), 0755, TRUE);
      }
      // If the pre-commit file does not exist, copy it to the target directory.
      $sourcePreCommitFile = $sourceDir . '/git/pre-commit';
      $content = file_get_contents($sourcePreCommitFile);
      file_put_contents($dstPreCommitFile, $content);
      $this->io->write("Copied: $sourcePreCommitFile to $dstPreCommitFile\n");
    }

    // Read project code from project-details.yml
    $projectCodeFile = $targetDir . '/project-details.yml';
    try {
      if (file_exists($projectCodeFile)) {
        $project_content = file_get_contents($projectCodeFile);
      }
      else {
        // Prompt user for project name if file does not exist
        $projectName = trim($event->getIO()->ask('<question>Enter your project code (JIRA project ID):</question>'));
        $project_content = "projectcode:\n  $projectName\nmultisite:\n  - default\n";
        file_put_contents($projectCodeFile, $project_content);
      }
      $project_details = Yaml::parse($project_content);
    }
    catch (ParseException $e) {
      // Log the YAML parsing error using the injected logger service.
      echo "YAML parsing error: \n" . $e->getMessage();
      // Return an empty array in case of parsing error.
    }
    $projectCode = trim($project_details['projectcode']);
    foreach ($filesToCopy as $file) {
      $srcFile = $sourceDir . '/' . $file;
      $dstFile = $targetDir . '/' . $file;
      if (file_exists($srcFile)) {
        if ($file === 'grumphp.yml.dist' && $projectCode !== NULL) {
          // Modify the content of grumphp.yml.dist only if project-code.txt exists
          $content = file_get_contents($srcFile);
          $content = str_replace('<project-code>', $projectCode, $content);
          $this->fileCheckSum(
            $srcFile,
            $dstFile,
            $content,
            file_get_contents($dstFile)
          );
          // Check if grumphp.yml exists in the current working directory
          $grumphpFile = $targetDir . '/grumphp.yml';
          if (!file_exists($grumphpFile)) {
            file_put_contents($grumphpFile, $content);
            $this->io->write("Created: $grumphpFile with content from grumphp.yml.dist\n");
          }
        }
        else {
          if (!file_exists($dstFile)) {
          // Just copy the file if it's not grumphp.yml.dist or if project-code.txt does not exist
            copy($srcFile, $dstFile);
            $this->io->write("Copied: $srcFile to $dstFile\n");
          }
          else {
            $srcFileContent = file_get_contents($srcFile);
            $this->fileCheckSum(
              $srcFile,
              $dstFile,
              $srcFileContent,
              file_get_contents($dstFile)
            );
          }
        }
      }
      else {
        $this->io->write("File not found: $srcFile\n");
      }
    }
    $this->io->write('<fg=green>Configuration files are processed successfully.</fg=green>');
  }

  /**
   * Checks the checksum of the source and destination files.
   *
   * If the checksums differ, it copies the source file to the destination file.
   *
   * @param string $srcFile
   *   The destination file path.
   * @param string $dstFile
   *   The source file path.
   * @param string $srcFileContent
   *   The content of the source file.
   * @param string $dstFileContent
   *   The content of the destination file.
   *
   * @return bool
   *   Returns true if the checksums are equal, false otherwise.
   */
  public function fileCheckSum($srcFile, $dstFile, $srcFileContent, $dstFileContent) {
    if (md5($srcFileContent) !== md5($dstFileContent)) {
      file_put_contents($dstFile, $srcFileContent);
      $this->io->write("Modified and copied: $srcFile to $dstFile\n");
    }
  }

}
