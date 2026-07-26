<?php

use PHPUnit\Framework\TestCase;

/**
 * Regression test: every environment variable actually read via getenv()/
 * $_ENV in the application code (excluding vendor/ and tests/) must have a
 * corresponding entry in .env.example, so new deployments/onboarding don't
 * silently break on missing config (this happened before - MAIL_HOST,
 * MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM, MAIL_FROM_NAME and ADMIN_EMAIL
 * were all missing from .env.example despite being required by the mailer
 * scripts).
 */
class EnvExampleCompletenessTest extends TestCase
{
    private const ROOT = __DIR__ . '/..';

    public function testEnvExampleContainsAllReferencedEnvVars(): void
    {
        $envExamplePath = self::ROOT . '/.env.example';
        $this->assertFileExists($envExamplePath, '.env.example must exist.');

        $envExampleContent = file_get_contents($envExamplePath);
        $declaredVars = [];
        if (preg_match_all('/^([A-Z][A-Z0-9_]*)\s*=/m', $envExampleContent, $matches)) {
            $declaredVars = array_flip($matches[1]);
        }

        $usedVars = $this->findEnvVarsUsedInCode();

        $missing = [];
        foreach ($usedVars as $var => $files) {
            if (!isset($declaredVars[$var])) {
                $missing[] = $var . ' (used in ' . implode(', ', $files) . ')';
            }
        }

        $this->assertEmpty(
            $missing,
            "The following environment variables are used in code but missing from .env.example:\n" . implode("\n", $missing)
        );
    }

    /**
     * @return array<string, string[]> map of env var name => list of relative file paths referencing it
     */
    private function findEnvVarsUsedInCode(): array
    {
        $usedVars = [];
        $excludedDirs = ['vendor', 'tests', '.git', 'node_modules'];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::ROOT, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = ltrim(str_replace(self::ROOT, '', $file->getPathname()), '/');
            $topLevelDir = explode('/', $relativePath)[0];
            if (in_array($topLevelDir, $excludedDirs, true)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (preg_match_all('/getenv\(\s*[\'"]([A-Z][A-Z0-9_]*)[\'"]\s*\)/', $content, $matches)) {
                foreach ($matches[1] as $var) {
                    $usedVars[$var][$relativePath] = $relativePath;
                }
            }
            if (preg_match_all('/\$_ENV\[\s*[\'"]([A-Z][A-Z0-9_]*)[\'"]\s*\]/', $content, $matches)) {
                foreach ($matches[1] as $var) {
                    $usedVars[$var][$relativePath] = $relativePath;
                }
            }
        }

        return array_map('array_values', $usedVars);
    }
}
