# Magic Link Login Scaffolding Package

A scaffolding package for Magic Link Authentication in Laravel Filament applications. This package installs the necessary files into your Laravel application and then removes itself, leaving you with full control over the code.

## Installation

1.  **Require the package** as a development dependency:

    ```bash
    composer require --dev nextmigrant/magic-link-login
    ```

2.  **Run the install command**:

    ```bash
    php artisan magic-link:install
    ```

    This command will interactively guide you through the process:
    -   **Copy Files**: Scaffolds Models, Controllers, Livewire components, Views, and Mailables into your application.
    -   **Configure Routes**: Automatically creates or updates `routes/admin/admin.php`.
    -   **Update User Model**: Automatically injects the `HasMagicLogin` trait into `app/Models/User.php`.
    -   **Cleanup**: Offers to remove the package dependency (`composer remove`) to keep your project dependencies clean.

3.  **Run migrations**:

    ```bash
    php artisan migrate
    ```

## Post-Installation

1.  **Disable Default Filament Login**:
    In your `app/Providers/Filament/AdminPanelProvider.php`, you must remove or comment out the `->login()` method to disable the default Filament login page. This package provides its own authentication flow.

    ```php
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            // ->login() // <--- Remove this line
            // ...
    }
    ```

2.  **Explore the Code**:
    After installation, the code belongs to you! You can find the key files here:
    -   `app/Http/Controllers/Admin/AuthController.php`
    -   `app/Livewire/Admin/Auth/Login.php`
    -   `app/Services/Auth/AuthenticationService.php`
    -   `resources/views/livewire/admin/auth/login.blade.php`

    Feel free to modify them to suit your project's needs.

## Features

-   **Magic Link Login**: Secure, passwordless login via email links.
-   **Filament Ready**: seamless integration with Filament Admin panels.
-   **Rate Limiting**: Built-in security protections.
-   **Self-Cleaning**: Designed to be installed, exhausted, and removed.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
