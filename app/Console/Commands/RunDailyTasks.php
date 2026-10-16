<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CronjobController;

class RunDailyTasks extends Command
{
    protected $signature = 'app:run-daily-tasks
                            {--invoices : Run only recurring invoices}
                            {--lease-reminder : Run only lease expiry reminders}
                            {--lease-status : Run only lease status update}
                            {--payment-reminder : Run only payment reminders}
                            {--late-fee : Run only automatic late fees}
                            {--document-reminder : Run only document expiry reminders}
                            {--lead-expiry : Run only lead expiry}
                            {--maintenance-schedule : Run only preventive maintenance}
                            {--autopay : Run only autopay charges}
                            {--rent-increase : Run only scheduled rent increases}
                            {--listing-alerts : Run only website new-listing alert emails}';

    protected $description = 'Run all daily tasks: recurring invoices, lease reminders, lease status update, payment reminders';

    public function handle()
    {
        $controller = new CronjobController();
        $runAll = !$this->option('invoices') && !$this->option('lease-reminder') && !$this->option('lease-status') && !$this->option('payment-reminder')
            && !$this->option('late-fee') && !$this->option('document-reminder') && !$this->option('lead-expiry') && !$this->option('maintenance-schedule') && !$this->option('autopay') && !$this->option('rent-increase') && !$this->option('listing-alerts');

        // rent increases first, so invoices created today already use the new rent
        if ($runAll || $this->option('rent-increase')) {
            $this->info('Applying rent increases...');
            $count = $controller->applyRentIncreases();
            $this->info('✅ Rent increases applied: ' . $count);
        }

        if ($runAll || $this->option('invoices')) {
            $this->info('Running recurring invoices...');
            $controller->runRecurringInvoices();
            $this->info('✅ Recurring invoices processed.');
        }

        if ($runAll || $this->option('lease-reminder')) {
            $this->info('Running lease expiry reminders...');
            $controller->leaseExpiryReminder();
            $this->info('✅ Lease expiry reminders sent.');
        }

        if ($runAll || $this->option('payment-reminder')) {
            $this->info('Running payment reminders...');
            $controller->paymentReminder();
            $this->info('✅ Payment reminders sent.');
        }
        if ($runAll || $this->option('lease-status')) {
            $this->info('Running lease status update...');
            $controller->leaseStatusUpdate();
            $this->info('✅ Lease status updated.');
        }

        if ($runAll || $this->option('late-fee')) {
            $this->info('Applying late fees...');
            $controller->applyLateFees();
            $this->info('✅ Late fees applied.');
        }

        if ($runAll || $this->option('document-reminder')) {
            $this->info('Running document expiry reminders...');
            $controller->documentExpiryReminder();
            $this->info('✅ Document expiry reminders sent.');
        }

        if ($runAll || $this->option('lead-expiry')) {
            $this->info('Running lead expiry...');
            $controller->leadExpiry();
            $this->info('✅ Lead expiry processed.');
        }

        if ($runAll || $this->option('maintenance-schedule')) {
            $this->info('Running preventive maintenance...');
            $controller->runMaintenanceSchedules();
            $this->info('✅ Preventive maintenance requests created.');
        }

        if ($runAll || $this->option('autopay')) {
            $this->info('Charging autopay cards...');
            $controller->chargeAutopay();
            $this->info('✅ Autopay processed.');
        }

        if ($runAll || $this->option('listing-alerts')) {
            $this->info('Sending new listing alerts...');
            $sent = \App\Support\ListingAlerts::sendDue();
            $this->info('✅ Listing alerts sent: ' . $sent);
        }

        $this->info('All daily tasks completed.');
    }
}
