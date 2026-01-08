<?php

namespace NextMigrant\MagicLinkLogin\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    protected $signature = 'magic-link:install';

    protected $description = 'Install the Magic Link Login package files into the application';

    public function handle()
    {
        \Laravel\Prompts\intro('Magic Link Login Installer');

        \Laravel\Prompts\warning('IMPORTANT: Ensure your application can boot before running this.');

        if (! \Laravel\Prompts\confirm('Do you wish to continue?', default: true)) {
            \Laravel\Prompts\warning('Installation cancelled.');
            return;
        }

        \Laravel\Prompts\spin(
            fn () => $this->copyFiles(),
            'Copying files...'
        );

        \Laravel\Prompts\spin(
            fn () => $this->copyViews(),
            'Copying views...'
        );

        \Laravel\Prompts\spin(
            fn () => $this->copyMigration(),
            'Copying migration...'
        );
        
        \Laravel\Prompts\spin(
            fn () => $this->appendRoutes(),
            'Configuring routes...'
        );

        \Laravel\Prompts\spin(
            fn () => $this->appendWebRoute(),
            'Registering routes in web.php...'
        );

        \Laravel\Prompts\spin(
            fn () => $this->updateUserModel(),
            'Updating User model...'
        );

        \Laravel\Prompts\info('Magic Link Login installed successfully.');
        \Laravel\Prompts\info('NOTE: routes/admin/admin.php has been created/updated. Please check your routes.');

        if (file_exists(base_path('packages/magic-link-login'))) {
            if (\Laravel\Prompts\confirm('Do you want to delete the local "packages/magic-link-login" folder?', default: false)) {
                $this->deleteSourceFolder();
            }
        }

        if (\Laravel\Prompts\confirm('Do you want to remove this scaffolding package dependency now?', default: true)) {
            $this->removePackage();
        } else {
            \Laravel\Prompts\note('You can remove this package later by running: composer remove --dev nextmigrant/magic-link-login');
        }
    }

    protected function updateUserModel()
    {
        $userModelPath = base_path('app/Models/User.php');

        if (! file_exists($userModelPath)) {
            \Laravel\Prompts\warning('Could not find User model at app/Models/User.php. Please add "use App\Traits\HasMagicLogin;" manually.');
            return;
        }

        $content = file_get_contents($userModelPath);

        if (str_contains($content, 'use App\Traits\HasMagicLogin;')) {
            return;
        }

        // Add import
        if (str_contains($content, 'namespace App\Models;')) {
            $content = str_replace(
                'namespace App\Models;',
                "namespace App\Models;\n\nuse App\Traits\HasMagicLogin;",
                $content
            );
        } else {
             // Fallback for simple files
            $content = preg_replace(
                '/^<\?php\s*/',
                "<?php\n\nuse App\Traits\HasMagicLogin;\n",
                $content
            );
        }

        // Add Trait usage
        // Look for "class User extends ..." or similar
        // We will just look for the opening brace of the class
        if (preg_match('/class\s+User\s+extends\s+[^{]+{/', $content, $matches)) {
            // Find the first occurrence of '{' after "class User"
            // And insert strict use statement after it
            $classDefinition = $matches[0];
            $content = str_replace(
                $classDefinition,
                $classDefinition . "\n    use HasMagicLogin;\n",
                $content
            );
        } else {
             \Laravel\Prompts\warning('Could not determine where to add "use HasMagicLogin;" in User model. Please add it manually.');
             return;
        }

        file_put_contents($userModelPath, $content);
    }

    protected function deleteSourceFolder()
    {
        $path = base_path('packages/magic-link-login');
        if (is_dir($path)) {
            (new Filesystem)->deleteDirectory($path);
            \Laravel\Prompts\info('Local package folder deleted.');
        } else {
            \Laravel\Prompts\warning('Could not find local package folder at: ' . $path);
        }
    }

    protected function copyFiles()
    {
        $files = [
            'Models/LoginToken.php' => 'app/Models/LoginToken.php',
            'Traits/HasMagicLogin.php' => 'app/Traits/HasMagicLogin.php',
            'Services/Auth/AuthenticationService.php' => 'app/Services/Auth/AuthenticationService.php',
            'Http/Controllers/Admin/AuthController.php' => 'app/Http/Controllers/Admin/AuthController.php',
            'Livewire/Admin/Auth/Login.php' => 'app/Livewire/Admin/Auth/Login.php',
            'Mail/Admin/MagicLoginLink.php' => 'app/Mail/Admin/MagicLoginLink.php',
            '../tests/Feature/Admin/Auth/LoginTest.php' => 'tests/Feature/Admin/Auth/LoginTest.php',
        ];

        foreach ($files as $source => $destination) {
            $this->publishFile(__DIR__ . '/../' . $source, base_path($destination));
        }
    }

    protected function copyViews()
    {
        $views = [
            '../resources/views/livewire/admin/auth/login.blade.php' => 'resources/views/livewire/admin/auth/login.blade.php',
            '../resources/views/mail/admin/magic-login-link.blade.php' => 'resources/views/mail/admin/magic-login-link.blade.php',
        ];

        foreach ($views as $source => $destination) {
            $this->publishFile(__DIR__ . '/../' . $source, base_path($destination), false); // Views don't have namespaces to replace usually, but we check content for 'magic-link-login::'
        }
    }

    protected function copyMigration()
    {
        $migrationFiles = (new Filesystem)->glob(__DIR__ . '/../../database/migrations/*.stub');
        foreach ($migrationFiles as $migrationFile) {
            $fileName = date('Y_m_d_His') . '_' . basename($migrationFile, '.stub') . '.php';
            $this->publishFile($migrationFile, database_path('migrations/' . $fileName), false);
        }
    }

    protected function appendRoutes()
    {
        $routeContent = file_get_contents(__DIR__ . '/../../routes/admin.php');
        // Remove <?php and imports that we already handle in the destination or don't need
        $routeContent = str_replace('<?php', '', $routeContent);
        // Replace namespaces in the route content
        $routeContent = str_replace('NextMigrant\\MagicLinkLogin\\', 'App\\', $routeContent);

        $targetRouteFile = base_path('routes/admin/admin.php');
        $this->ensureDirectoryExists(dirname($targetRouteFile));

        if (! file_exists($targetRouteFile)) {
             $this->info("Creating routes/admin/admin.php...");
             file_put_contents($targetRouteFile, "<?php\n\n" . $routeContent);
        } else {
             $this->info("Appending routes to {$targetRouteFile}...");
             file_put_contents($targetRouteFile, "\n" . $routeContent, FILE_APPEND);
        }
    }

    protected function appendWebRoute()
    {
        $webRoutePath = base_path('routes/web.php');

        if (! file_exists($webRoutePath)) {
            \Laravel\Prompts\warning('Could not find routes/web.php.');
            return;
        }

        $content = file_get_contents($webRoutePath);

        // Check if already registered (crudely)
        if (str_contains($content, 'admin/admin.php')) {
            return;
        }

        file_put_contents($webRoutePath, "\n\nrequire __DIR__.'/admin/admin.php';\n", FILE_APPEND);
    }

    protected function ensureDirectoryExists($path)
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    protected function publishFile($source, $destination, $replaceNamespace = true)
    {
        $filesystem = new Filesystem;

        $filesystem->ensureDirectoryExists(dirname($destination));

        $content = $filesystem->get($source);

        if ($replaceNamespace) {
            $content = str_replace('NextMigrant\\MagicLinkLogin\\', 'App\\', $content);
            $content = str_replace('magic-link-login::', '', $content);
        } else {
             // Even for views, we might want to replace the view namespace
             $content = str_replace('magic-link-login::', '', $content);
        }

        $filesystem->put($destination, $content);

        $this->line("Published: " . str_replace(base_path() . '/', '', $destination));
    }

    protected function removePackage()
    {
        $this->info('Removing package...');
        shell_exec('composer remove --dev nextmigrant/magic-link-login');
        $this->info('Package removed.');
    }
}
