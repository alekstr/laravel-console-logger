<?php

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class CommandWithEmailNotificationsDeduplication extends Command
{
    use Loggable;

    protected $signature = 'command-with-email-notifications-deduplication';

    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ];
    }

    protected function useEmailNotificationsDeduplication()
    {
        return true;
    }

    public function handle()
    {
        $this->logInfo('Done!');
    }

    public function mailerHandler()
    {
        return last($this->icLogger()->getHandlers());
    }
}