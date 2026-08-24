<?php

namespace App\Providers;

use App\Services\Ai\TextGenerator;
use App\Services\Ai\TextGeneratorFactory;
use App\Support\CompanyProfile;
use App\Support\Translation\MergingFileLoader;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerTranslationLoader();

        $this->app->bind(TextGenerator::class, fn (): TextGenerator => TextGeneratorFactory::make());
    }

    /**
     * Swap the framework translation loader for one that merges group
     * subdirectories (e.g. lang/pt_BR/app/auth.php → app.auth.*) so dot
     * notation keeps working across split files.
     */
    protected function registerTranslationLoader(): void
    {
        $this->app->extend('translation.loader', function (FileLoader $loader, $app): MergingFileLoader {
            $merging = new MergingFileLoader($app['files'], $loader->paths());

            foreach ($loader->jsonPaths() as $jsonPath) {
                $merging->addJsonPath($jsonPath);
            }

            foreach ($loader->namespaces() as $namespace => $hint) {
                $merging->addNamespace($namespace, $hint);
            }

            return $merging;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->bootMailFromName();
        $this->bootAuthMailables();
    }

    /**
     * Use the company name from the site settings as the sender display name.
     * Resolved at send time (event listener) so it never touches the database during boot.
     */
    protected function bootMailFromName(): void
    {
        Event::listen(function (MessageSending $event): void {
            $address = config('mail.from.address');

            if (is_string($address) && $address !== '') {
                $event->message->from(new Address($address, CompanyProfile::name()));
            }
        });
    }

    /**
     * Brand and translate the Fortify auth notifications (email verification
     * and password reset) with the company profile from the site settings.
     */
    protected function bootAuthMailables(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $company = CompanyProfile::name();

            return (new MailMessage)
                ->subject(__('app.mail.verify.subject', ['company' => $company]))
                ->greeting(__('app.mail.greeting', ['name' => $notifiable->name]))
                ->line(__('app.mail.verify.intro', ['company' => $company]))
                ->action(__('app.mail.verify.action'), $url)
                ->line(__('app.mail.verify.outro'))
                ->salutation(__('app.mail.salutation', ['company' => $company]));
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $company = CompanyProfile::name();
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject(__('app.mail.reset.subject', ['company' => $company]))
                ->greeting(__('app.mail.greeting', ['name' => $notifiable->name]))
                ->line(__('app.mail.reset.intro', ['company' => $company]))
                ->action(__('app.mail.reset.action'), $url)
                ->line(__('app.mail.reset.expire', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
                ->line(__('app.mail.reset.outro'))
                ->salutation(__('app.mail.salutation', ['company' => $company]));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Model::preventLazyLoading(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
