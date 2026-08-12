<?php

namespace Shetabit\Payment\Provider;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Shetabit\Multipay\Contracts\DriverInterface;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;
use Shetabit\Multipay\Request;
use Shetabit\Payment\Events\InvoicePurchasedEvent;
use Shetabit\Payment\Events\InvoiceVerifiedEvent;
use Shetabit\Payment\Facade\Payment as PaymentFacade;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * The namespace the views of the package are registered under.
     */
    public const string VIEW_NAMESPACE = 'shetabitPayment';

    /**
     * The name of the redirection form's view.
     */
    public const string REDIRECTION_FORM_VIEW = 'redirectForm';

    /**
     * Whether the payment events were already registered.
     *
     * The payment manager keeps its listeners in a static property, so they
     * outlive the application they were registered from. Registering them
     * again — which happens as soon as a second application is booted in the
     * same process, like the test suite does — would dispatch every event
     * twice.
     */
    protected static bool $eventsRegistered = false;

    /**
     * Perform post-registration booting of services.
     */
    public function boot() : void
    {
        $this->loadViewsFrom($this->packageViewsPath(), self::VIEW_NAMESPACE);

        /**
         * Configurations that needs to be done by user.
         */
        $this->publishes(
            [
                Payment::getDefaultConfigPath() => config_path('payment.php'),
            ],
            'payment-config'
        );

        /**
         * Views that needs to be modified by user.
         */
        $this->publishes(
            [
                $this->packageViewsPath() => $this->publishedViewsPath(),
            ],
            'payment-views'
        );
    }

    /**
     * Register any package services.
     */
    public function register() : void
    {
        // Merge default config with user's config
        $this->mergeConfigFrom(Payment::getDefaultConfigPath(), 'payment');

        // Read the gateways' callback data from Laravel's request
        Request::overwrite('input', static fn (string $key): mixed => request($key));

        /**
         * Bind to service container.
         */
        $this->app->bind(
            PaymentFacade::SERVICE_NAME,
            static fn (): Payment => new Payment((array) config('payment', []))
        );

        // Allow the manager to be resolved and injected by its class name too
        $this->app->alias(PaymentFacade::SERVICE_NAME, Payment::class);

        $this->registerEvents();
        $this->registerRedirectionFormRenderer();
    }

    /**
     * Register Laravel events.
     */
    protected function registerEvents() : void
    {
        if (static::$eventsRegistered) {
            return;
        }

        static::$eventsRegistered = true;

        Payment::addPurchaseListener(static function (DriverInterface $driver, Invoice $invoice): void {
            event(new InvoicePurchasedEvent($driver, $invoice));
        });

        Payment::addVerifyListener(
            static function (ReceiptInterface $receipt, DriverInterface $driver, Invoice $invoice): void {
                event(new InvoiceVerifiedEvent($receipt, $driver, $invoice));
            }
        );
    }

    /**
     * Render the redirection form with blade instead of plain PHP.
     */
    protected function registerRedirectionFormRenderer() : void
    {
        /**
         * The renderer is called with named arguments, so its parameters have to
         * keep the names the payment manager passes them under.
         */
        Payment::setRedirectionFormViewRenderer(
            fn (string $view, string $action, array $inputs, string $method): string => $this->renderRedirectionForm(
                $view,
                $action,
                $inputs,
                $method
            )
        );
    }

    /**
     * Render the redirection form that sends the customer to the gateway.
     *
     * @param string               $view   path of the view of the payment manager
     * @param string               $action url of the gateway
     * @param array<string, mixed> $inputs fields the gateway expects
     * @param string               $method http method the form is submitted with
     */
    protected function renderRedirectionForm(string $view, string $action, array $inputs, string $method) : string
    {
        $data = [
            'action' => $action,
            'inputs' => $inputs,
            'method' => $method,
        ];

        if ($this->existCustomRedirectFormView()) {
            return $this->loadNormalRedirectForm($data);
        }

        return Blade::render($this->addCsrfField($this->readView($view)), $data);
    }

    /**
     * Checks whether the user has customized the view file called `redirectForm.blade.php` or not
     */
    protected function existCustomRedirectFormView() : bool
    {
        return file_exists($this->publishedViewsPath().'/'.self::REDIRECTION_FORM_VIEW.'.blade.php');
    }

    /**
     * Render the redirection form's view the user has published.
     *
     * @param array<string, mixed> $data
     */
    protected function loadNormalRedirectForm(array $data) : string
    {
        return view(self::VIEW_NAMESPACE.'::'.self::REDIRECTION_FORM_VIEW)->with($data)->render();
    }

    /**
     * Path of the views that ship with the package.
     */
    protected function packageViewsPath() : string
    {
        return dirname(__DIR__, 2).'/resources/views';
    }

    /**
     * Path the views of the package are published to.
     */
    protected function publishedViewsPath() : string
    {
        return resource_path('views/vendor/'.self::VIEW_NAMESPACE);
    }

    /**
     * Add a CSRF field to every form of the given view.
     */
    private function addCsrfField(string $view) : string
    {
        return str_replace('</form>', '@csrf</form>', $view);
    }

    /**
     * Read the source of the given view.
     */
    private function readView(string $view) : string
    {
        $source = is_file($view) ? file_get_contents($view) : false;

        if ($source === false) {
            throw new RuntimeException("The redirection form's view [{$view}] could not be read.");
        }

        return $source;
    }
}
